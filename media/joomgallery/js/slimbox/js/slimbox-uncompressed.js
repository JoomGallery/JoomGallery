/*!
  Slimbox v1.8 - The ultimate lightweight Lightbox clone
  (c) 2007-2011 Christophe Beyls <http://www.digitalia.be>
  MIT-style license.
  modified by JoomGallery team
  - automatic resizing
  - use of language constants
  - dynamically ignore of doublets
*/

var Slimbox = (function() {

  // Global variables, accessible to Slimbox only
  var win = window, ie6 = Browser.ie6, options, images, activeImage = -1, activeURL, prevImage, nextImage, compatibleOverlay, middle, centerWidth, centerHeight,

  // Preload images
  preload = {}, preloadPrev = new Image(), preloadNext = new Image(),

  // DOM elements
  overlay, center, image, sizer, prevLink, nextLink, bottomContainer, bottom, caption, number,

  // Effects
  fxOverlay, fxResize, fxImage, fxBottom;

  /*
    Initialization
  */

  win.addEvent("domready", function() {
    // Append the Slimbox HTML code at the bottom of the document
    $(document.body).adopt(
      $$(
        overlay = new Element("div#lbOverlay", {events: {click: close}}),
        center = new Element("div#lbCenter"),
        bottomContainer = new Element("div#lbBottomContainer")
      ).setStyle("display", "none")
    );

    image = new Element("div#lbImage").inject(center).adopt(
      sizer = new Element("div", {styles: {position: "relative"}}).adopt(
        prevLink = new Element("a#lbPrevLink[href=#]", {events: {click: previous}}),
        nextLink = new Element("a#lbNextLink[href=#]", {events: {click: next}})
      )
    );

    bottom = new Element("div#lbBottom").inject(bottomContainer).adopt(
      new Element("a#lbCloseLink[href=#]", {events: {click: close}}),
        caption = new Element("div#lbCaption"),
        number = new Element("div#lbNumber"),
        new Element("div", {styles: {clear: "both"}})
    );
  });


  /*
    Internal functions
  */

  function position() {
    var scroll = win.getScroll(), size = win.getSize();
    $$(center, bottomContainer).setStyle("left", scroll.x + (size.x / 2));
    if (compatibleOverlay) overlay.setStyles({left: scroll.x, top: scroll.y, width: size.x, height: size.y});
  }

  function setup(open) {
    ["object", ie6 ? "select" : "embed"].forEach(function(tag) {
      Array.forEach(document.getElementsByTagName(tag), function(el) {
        if (open) el._slimbox = el.style.visibility;
        el.style.visibility = open ? "hidden" : el._slimbox;
      });
    });

    overlay.style.display = open ? "" : "none";

    var fn = open ? "addEvent" : "removeEvent";
    win[fn]("scroll", position)[fn]("resize", position);
    document[fn]("keydown", keyDown);
  }

  function keyDown(event) {
    var code = event.code;
    // Prevent default keyboard action (like navigating inside the page)
    return options.closeKeys.contains(code) ? close()
      : options.nextKeys.contains(code) ? next()
      : options.previousKeys.contains(code) ? previous()
      : false;
  }

  function previous() {
    return changeImage(prevImage);
  }

  function next() {
    return changeImage(nextImage);
  }

  function changeImage(imageIndex) {
    if (imageIndex >= 0) {
      activeImage = imageIndex;
      activeURL = images[imageIndex][0];
      prevImage = (activeImage || (options.loop ? images.length : 0)) - 1;
      nextImage = ((activeImage + 1) % images.length) || (options.loop ? 0 : -1);

      stop();
      center.className = "lbLoading";

      preload = new Image();
      preload.onload = animateBox;
      preload.src = activeURL;
    }

    return false;
  }

  // Internal functions for JoomGallery
  // needed to avoid displaying the same image multiple
  // and the right counter in the slimbox
  // JoomGallery team October 2010, adapted from code of JoomGallery

  // analyzes the images array and construct
  // an array with unique numbers
  function joomcheckmulti(images)
  {
    o = {};
    ilength = images.length;
    for(i = 0; i < ilength; i++)
    {
      // Create an array with unique URL
      // and number of object in images
      o[images[i]["0"]] = i;
    }
    // Create an array with the object numbers from o
    p = new Array();
    for (i in o)
    {
      p[o[i]] = true;
    }
    return p;
  }

  // Returns the count of all unique images
  function joomuniquelength(uniarr)
  {
    arrlength    = uniarr.length;
    uniquelength = arrlength;
    for (i=0; i < arrlength; i++)
    {
      if(!uniarr[i])
      {
        uniquelength--;
      }
    }
    return uniquelength;
  }

  // Returns the max. object id of image in the array
  function joomidmax(uniarr,imlength)
  {
    maxid = 0;
    for (i=0; i<=imlength; i++)
    {
      if(uniarr[i])
      {
        maxid=Math.max(maxid,i);
      }
    }
    return maxid;
  }

  // Returns the count of current image showing in the box
  function joomgetactcount (uniarr, imlength, aktcounter)
  {
    actcount=0;
    for (i=0; i<=imlength; i++)
    {
      if(uniarr[i])
      {
        actcount++;
        if (i==aktcounter)
        {
          break;
        }
      }
    }
    return actcount;
  }
  // End of internal functions for JoomGallery

  function animateBox() {
    center.className = "";
    fxImage.set(0);

    // Edit JoomGallery team resize adopted from modified v1.41
    if(resizeJsImage == 1)
    {
      if(preload.width > (options.winWidth * 0.99))
      {
        preload.height = (preload.height * (options.winWidth * 0.99)) / preload.width;
        preload.width  = options.winWidth * 0.99;
      }
      if(preload.height>(options.winHeight * 0.88))
      {
        preload.width  = (preload.width * (options.winHeight * 0.88)) / preload.height;
        preload.height = options.winHeight * 0.88;
      }
      // If image not exists create it
      imgelem = image.getElementById("sbimg");
      if(!imgelem)
      {
        imgelem  = new Element('img',{id: 'sbimg'});
        imgelem.inject(image);
      }
      imgelem.setProperty('src', images[activeImage][0]);
      imgelem.setProperty('width', preload.width);
      imgelem.setProperty('height', preload.height);
    }
    else
    {
      image.style.backgroundImage = 'url('+images[activeImage][0]+')';
    }
    //End Edit JoomGallery team resize

    image.setStyles({width: preload.width, display: ""});
    sizer.setStyle("width", preload.width);
    image.setStyle("height", preload.height);
    $$(prevLink, nextLink).setStyle("height", preload.height);


    caption.set("html", images[activeImage][1] || "");

    // Edit JoomGallery team
    // check multiple links for correction of the counter
    // return an array with unique object keys
    uniquearr   = new Array();
    uniquearr   = joomcheckmulti(images);
    uniquecount = joomuniquelength(uniquearr);
    uniquemaxid = joomidmax(uniquearr, images.length);

    // Check if a double deleted image and jump to the right one
    changed = false;
    while(!uniquearr[activeImage])
    {
      activeImage++;
      changed=true;
      nextImage++;
    }
    while(!uniquearr[prevImage] && prevImage >= 0)
    {
      prevImage--;
    }
    if(changed)
    {
      while(!uniquearr[nextImage] && nextImage <= uniquemaxid)
      {
        nextImage++;
      }
      if (nextImage > uniquemaxid)
      {
        nextImage = -1;
      }
    }
    // Get the right counter of current image
    if(prevImage < 0)
    {
      imageactcounter = 1;
    }
    else
    {
      imageactcounter=joomgetactcount(uniquearr,images.length,activeImage);
    }

    number.set("html",(options.showCounter && (images.length > 1)) ? options.counterText.replace(/{x}/,imageactcounter).replace(/{y}/, uniquecount) : "");
    // End edit JoomGalleryteam

    if (prevImage >= 0) preloadPrev.src = images[prevImage][0];
    if (nextImage >= 0) preloadNext.src = images[nextImage][0];

    centerWidth = image.offsetWidth;
    centerHeight = image.offsetHeight;
	  
    var top = Math.max(0, middle - (centerHeight / 2)), check = 0, fn;
    
    if (center.offsetHeight != centerHeight) {
      check = fxResize.start({height: centerHeight, top: top});
    }
    if (center.offsetWidth != centerWidth) {
      check = fxResize.start({width: centerWidth, marginLeft: -centerWidth/2});
    }
    fn = function() {
      bottomContainer.setStyles({width: centerWidth, top: top + centerHeight, marginLeft: -centerWidth/2, visibility: "hidden", display: ""});
      fxImage.start(1);
    };
    if (check) {
      fxResize.chain(fn);
    }
    else {
      fn();
    }
  }

  function animateCaption() {
    if (prevImage >= 0) prevLink.style.display = "";
    if (nextImage >= 0) nextLink.style.display = "";
    fxBottom.set(-bottom.offsetHeight).start(0);
    bottomContainer.style.visibility = "";
  }

  function stop() {
                preload.onload = null;
    preload.src = preloadPrev.src = preloadNext.src = activeURL;
    fxResize.cancel();
    fxImage.cancel();
    fxBottom.cancel();
    $$(prevLink, nextLink, image, bottomContainer).setStyle("display", "none");
  }

  function close() {
    if (activeImage >= 0) {
      stop();
      activeImage = prevImage = nextImage = -1;
      center.style.display = "none";
      fxOverlay.cancel().chain(setup).start(0);
    }

    return false;
  }


  /*
    API
  */

  Element.implement({
    slimbox: function(_options, linkMapper) {
      // The processing of a single element is similar to the processing of a collection with a single element
      $$(this).slimbox(_options, linkMapper);

      return this;
    }
  });

  Elements.implement({
    /*
      options:  Optional options object, see Slimbox.open()
      linkMapper: Optional function taking a link DOM element and an index as arguments and returning an array containing 2 elements:
          the image URL and the image caption (may contain HTML)
      linksFilter:  Optional function taking a link DOM element and an index as arguments and returning true if the element is part of
          the image collection that will be shown on click, false if not. "this" refers to the element that was clicked.
          This function must always return true when the DOM element argument is "this".
    */
    slimbox: function(_options, linkMapper, linksFilter) {
      linkMapper = linkMapper || function(el) {
        return [el.href, el.title];
      };

      linksFilter = linksFilter || function() {
        return true;
      };

      var links = this;

      links.removeEvents("click").addEvent("click", function() {
        // Build the list of images that will be displayed
        var filteredLinks = links.filter(linksFilter, this);
        return Slimbox.open(filteredLinks.map(linkMapper), filteredLinks.indexOf(this), _options);
      });

      return links;
    }
  });

  return {
    open: function(_images, startImage, _options) {
      // Edit JoomGallery team flexible resize duration
      resizeduration = (11 - resizeSpeed) * 150;
      // Edit JoomGallery team

      options = Object.append({
        loop: false,        // Allows to navigate between first and last images
        overlayOpacity: 0.8,      // 1 is opaque, 0 is completely transparent (change the color in the CSS file)
        overlayFadeDuration: 400,   // Duration of the overlay fade-in and fade-out animations (in milliseconds)
        //resizeDuration: 400,      // Duration of each of the box resize animations (in milliseconds)
        resizeDuration: resizeduration,
        resizeTransition: false,    // false uses the mootools default transition
        initialWidth: 250,      // Initial width of the box (in pixels)
        initialHeight: 250,     // Initial height of the box (in pixels)
        imageFadeDuration: 400,     // Duration of the image fade-in animation (in milliseconds)
        captionAnimationDuration: 400,    // Duration of the caption animation (in milliseconds)
        //counterText: "Image {x} of {y}",  // Translate or change as you wish
        // Edit JoomGallery team flexible language
        counterText: joomgallery_image+" {x} "+joomgallery_of+ "  {y}",
        // Resize adopted from modified v1.41
        closeKeys: [27, 88, 67],    // Array of keycodes to close Slimbox, default: Esc (27), 'x' (88), 'c' (67)
        previousKeys: [37, 80],     // Array of keycodes to navigate to the previous image, default: Left arrow (37), 'p' (80)
        nextKeys: [39, 78],     // Array of keycodes to navigate to the next image, default: Right arrow (39), 'n' (78)
        // Edit Joomgallery team, get viewport of browser
        winWidth: (getWidth() > 0) ? getWidth() : 1024,
        winHeight: (getHeight() > 0) ? getHeight() : 800,
        showCounter: true
        // End edit JoomGallery team
      }, _options || {});

      // Setup effects
      fxOverlay = new Fx.Tween(overlay, {property: "opacity", duration: options.overlayFadeDuration});
                        fxResize = new Fx.Morph(center, Object.append({duration: options.resizeDuration, link: "chain"}, options.resizeTransition ? {transition: options.resizeTransition} : {}));
      fxImage = new Fx.Tween(image, {property: "opacity", duration: options.imageFadeDuration, onComplete: animateCaption});
      fxBottom = new Fx.Tween(bottom, {property: "margin-top", duration: options.captionAnimationDuration});

      // The function is called for a single image, with URL and Title as first two arguments
      if (typeof _images == "string") {
        _images = [[_images, startImage]];
        startImage = 0;
      }

      middle = win.getScrollTop() + (win.getHeight() / 2);
      centerWidth = options.initialWidth;
      centerHeight = options.initialHeight;
      center.setStyles({top: Math.max(0, middle - (centerHeight / 2)), width: centerWidth, height: centerHeight, marginLeft: -centerWidth/2, display: ""});
      compatibleOverlay = ie6 || (overlay.currentStyle && (overlay.currentStyle.position != "fixed"));
      if (compatibleOverlay) overlay.style.position = "absolute";
      fxOverlay.set(0).start(options.overlayOpacity);
      position();
      setup(1);

      images = _images;
      options.loop = options.loop && (images.length > 1);
      return changeImage(startImage);
    }
  };

})();
//AUTOLOAD CODE BLOCK (MAY BE CHANGED OR REMOVED)
Slimbox.scanPage = function() {
  $$("a").filter(function(el) {
    return el.rel && el.rel.test(/^lightbox/i);
  }).slimbox({/* Put custom options here */}, null, function(el) {
    return (this == el) || ((this.rel.length > 8) && (this.rel == el.rel));
  });
};
if (!/android|iphone|ipod|series60|symbian|windows ce|blackberry/i.test(navigator.userAgent)) {
  window.addEvent("domready", Slimbox.scanPage);
}