/*
 * Thickbox 3 - One Box To Rule Them All.
 * By Cody Lindley (http://www.codylindley.com)
 * Copyright (c) 2007 cody lindley
 * Licensed under the MIT License: http://www.opensource.org/licenses/mit-license.php
 */

/*!!!!!!!!!!!!!!!!! edit below this line at your own risk !!!!!!!!!!!!!!!!!!!!!!!*/
jQuery.noConflict();

// for saving the onkey event from JoomGallery
var joom_onkeydownsave;

// on page load call tb_init
jQuery(document).ready(function()
{
  // modify all thickbox links having a rel tag 'thickbox.<group>'
  var sstr    = 'thickbox';

  jQuery("a[rel^=" + sstr + "]").each(function(){
    jQuery(this).addClass(sstr);
    this.rel = this.rel.substr(9);
  });

  // save the initial state of onkey event
  joom_onkeydownsave = document.onkeydown;
  tb_init('a.thickbox, area.thickbox, input.thickbox');// pass where to apply
  // thickbox
  imgLoader = new Image();// preload image
  imgLoader.src = tb_pathToImage;
});

// add thickbox to href & area elements that have a class of .thickbox
function tb_init(domChunk)
{
  jQuery(domChunk).click(function()
  {
    var t = this.title || this.name || null;
    var a = this.href || this.alt;
    var g = this.rel || false;
    tb_show(t, a, g);
    this.blur();
    return false;
  });
}

// Internal functions for JoomGallery
// needful to avoid displaying the same picture multiple
// and the right counter in the slimbox
// JoomGallery team April 2010

// Analyze the images array and construct
// an array with unique numbers
function joomcheckmulti(images)
{
  var o =
  {};
  for ( var i = 0; i < images.length; i++)
  {
    // Create an array with unique URL
    // and number of object in images
    o[images[i].href] = i;
  }
  // Create an array with the object numbers from o
  var p = new Array();
  for ( var i in o)
  {
    p[o[i]] = true;
  }
  return p;
}

// Returns the count of all unique pictures
function joomuniquelength (uniarr)
{
  var arrlength=uniarr.length;
  var uniquelength=arrlength;

  for (var i=0; i<arrlength; i++)
  {
    if(uniarr[i] != true)
    {
      uniquelength--;
    }
  }
  return uniquelength;
}

// Returns the max. object id of picture in the array
function joomidmax(uniarr, imlength)
{
  var maxid = 0;
  for ( var i = 0; i <= imlength; i++)
  {
    if (uniarr[i] == true)
    {
      maxid = Math.max(maxid, i);
    }
  }
  return maxid;
}

// Returns the count of actual picture showing in the box
function joomgetactcount(uniarr, imlength, aktcounter)
{
  var actcount = 0;
  for ( var i = 0; i <= imlength; i++)
  {
    if (uniarr[i] == true)
    {
      actcount++;
      if (i == aktcounter)
      {
        break;
      }
    }
  }
  return actcount;
}
// end internal functions for JoomGallery

function tb_show(caption, url, imageGroup)
{// function called when the user clicks on a thickbox link
  try
  {
    if (typeof document.body.style.maxHeight === "undefined")
    {// if IE 6
      jQuery("body", "html").css(
      {
        height : "100%",
        width : "100%"
      });
      jQuery("html").css("overflow", "hidden");
      if (document.getElementById("TB_HideSelect") === null)
      {// iframe to hide select elements in ie6
        jQuery("body")
            .append(
                "<iframe id='TB_HideSelect'></iframe><div id='TB_overlay'></div><div id='TB_window'></div>");
        jQuery("#TB_overlay").click(tb_remove);
      }
    }
    else
    {// all others
      if (document.getElementById("TB_overlay") === null)
      {
        jQuery("body")
            .append("<div id='TB_overlay'></div><div id='TB_window'>");
        jQuery("#TB_overlay").click(tb_remove);
      }
    }

    if (caption === null)
    {
      caption = "";
    }
    jQuery("body").append(
        "<div id='TB_load'><img src='" + imgLoader.src + "' /></div>");// add
    // loader
    // to
    // the
    // page
    jQuery('#TB_load').show();// show loader

    var baseURL;
    if (url.indexOf("?") !== -1)
    { // ff there is a query string involved
      baseURL = url.substr(0, url.indexOf("?"));
    }
    else
    {
      baseURL = url;
    }

    var urlString = /\.jpg|\.jpeg|\.png|\.gif|\.bmp/g;
    var urlType = baseURL.toLowerCase().match(urlString);

    if (true)
    { // (urlType == '.jpg' || urlType == '.jpeg' || urlType == '.png' ||
      // urlType == '.gif' || urlType == '.bmp'){//code to show images

      TB_PrevCaption = "";
      TB_PrevURL = "";
      TB_PrevHTML = "";
      TB_NextCaption = "";
      TB_NextURL = "";
      TB_NextHTML = "";
      TB_imageCount = "";
      TB_FoundURL = false;
      if (imageGroup)
      {
        TB_TempArray = jQuery("a[rel=" + imageGroup + "]").get();

        // edit JoomGallery team
        // check multiple links for correction of the counter
        // return an array with unique object keys
        var uniquearr = new Array();
        uniquearr = joomcheckmulti(TB_TempArray);
        var uniquecount = joomuniquelength(uniquearr);
        var uniquemaxid = joomidmax(uniquearr, TB_TempArray.length);

        for (TB_Counter = 0; ((TB_Counter < TB_TempArray.length) && (TB_NextHTML === "")); TB_Counter++)
        {
          if ((TB_TempArray[TB_Counter].href == url))
          {
            // actual picture found
            var prevImage = TB_Counter - 1;
            var nextImage = TB_Counter + 1;
            var changed = false;
            while (uniquearr[TB_Counter] != true)
            {
              TB_Counter++;
              changed = true;
            }
            if (changed)
            {
              prevImage = TB_Counter - 1;
              nextImage = TB_Counter + 1;
            }
            // look for previous and next image
            while (uniquearr[prevImage] != true && prevImage >= 0)
            {
              prevImage--;
            }
            while (uniquearr[nextImage] != true && nextImage <= uniquemaxid)
            {
              nextImage++;
            }
            if (nextImage > uniquemaxid)
            {
              nextImage = -1;
            }
            // get the right counter of actual image
            var imageactcounter = 0;
            if (prevImage < 0)
            {
              imageactcounter = 1;
            }
            else
            {
              imageactcounter = joomgetactcount(uniquearr, TB_TempArray.length,
                  TB_Counter);
            }
            // actual image
            TB_imageCount = joomgallery_image + " " + (imageactcounter) + " "
                + joomgallery_of + " " + (uniquecount);

            // next image if existent
            if (nextImage != -1)
            {
              TB_NextCaption = TB_TempArray[nextImage].title;
              TB_NextURL = TB_TempArray[nextImage].href;
              // Edit b2m language
              TB_NextHTML = "<span id='TB_next'>&nbsp;&nbsp;<a href='#'>"
                  + joomgallery_next + " &gt;</a></span>";
            }
            // previous picture if existent
            if (prevImage >= 0)
            {
              TB_PrevCaption = TB_TempArray[prevImage].title;
              TB_PrevURL = TB_TempArray[prevImage].href;
              // Edit b2m language
              TB_PrevHTML = "<span id='TB_prev'>&nbsp;&nbsp;<a href='#'>&lt;"
                  + joomgallery_prev + "</a></span>";
            }
            break;
          }
        }
      }

      imgPreloader = new Image();
      imgPreloader.onload = function()
      {
        imgPreloader.onload = null;

        // Resizing large images - orginal by Christian Montoya edited by me.
        var pagesize = tb_getPageSize();
        var x = pagesize[0] - 150;
        var y = pagesize[1] - 150;
        var imageWidth = imgPreloader.width;
        var imageHeight = imgPreloader.height;
        // Edit b2m edit resize
        if (resizeJsImage == 1)
        {
          if (imageWidth > x)
          {
            imageHeight = imageHeight * (x / imageWidth);
            imageWidth = x;
          }
          if (imageHeight > y)
          {
            imageWidth = imageWidth * (y / imageHeight);
            imageHeight = y;
          }
        }
        // End b2m Edit resize
        // End Resizing

        TB_WIDTH = imageWidth + 30;
        TB_HEIGHT = imageHeight + 60;
        // Edit b2m language
        jQuery("#TB_window")
            .append(
                "<a href='' id='TB_ImageOff' title='"
                    + joomgallery_close
                    + "'><img id='TB_Image' src='"
                    + url
                    + "' width='"
                    + imageWidth
                    + "' height='"
                    + imageHeight
                    + "'/></a>"
                    + "<div id='TB_caption'>"
                    + caption
                    + "<div id='TB_secondLine'>"
                    + TB_imageCount
                    + TB_PrevHTML
                    + TB_NextHTML
                    + "</div></div><div id='TB_closeWindow'><a href='#' id='TB_closeWindowButton' title='"
                    + joomgallery_close + "'>" + joomgallery_close + "</a> "
                    + joomgallery_press_esc + "</div>");
        // End edit b2m language

        jQuery("#TB_closeWindowButton").click(tb_remove);

        if (!(TB_PrevHTML === ""))
        {
          function goPrev()
          {
            if (jQuery(document).unbind("click", goPrev))
            {
              jQuery(document).unbind("click", goPrev);
            }
            jQuery("#TB_window").remove();
            jQuery("body").append("<div id='TB_window'></div>");
            tb_show(TB_PrevCaption, TB_PrevURL, imageGroup);
            return false;
          }
          jQuery("#TB_prev").click(goPrev);
        }

        if (!(TB_NextHTML === ""))
        {
          function goNext()
          {
            jQuery("#TB_window").remove();
            jQuery("body").append("<div id='TB_window'></div>");
            tb_show(TB_NextCaption, TB_NextURL, imageGroup);
            return false;
          }
          jQuery("#TB_next").click(goNext);

        }

        document.onkeydown = function(e)
        {
          if (e == null)
          { // ie
            keycode = event.keyCode;
          }
          else
          { // mozilla
            keycode = e.which;
          }

          keychar = String.fromCharCode(keycode).toLowerCase();

          if (keycode == 27)
          { // close
            tb_remove();
          }
          else if (keychar == 'n' || keycode == 39)
          { // display next image
            if (!(TB_NextHTML == ""))
            {
              document.onkeydown = "";
              goNext();
            }
          }
          else if (keychar == 'p' || keycode == 37)
          { // display previous image
            if (!(TB_PrevHTML == ""))
            {
              document.onkeydown = "";
              goPrev();
            }
          }
        };

        tb_position();
        jQuery("#TB_load").remove();
        jQuery("#TB_ImageOff").click(tb_remove);
        jQuery("#TB_window").css(
        {
          display : "block"
        }); // for safari using css instead of show
      };

      imgPreloader.src = url;
    }
    else
    {// code to show html pages

      var queryString = url.replace(/^[^\?]+\??/, '');
      var params = tb_parseQuery(queryString);

      TB_WIDTH = (params['width'] * 1) + 30 || 630; // defaults to 630 if no
      // paramaters were added to
      // URL
      TB_HEIGHT = (params['height'] * 1) + 40 || 440; // defaults to 440 if no
      // paramaters were added
      // to URL
      ajaxContentW = TB_WIDTH - 30;
      ajaxContentH = TB_HEIGHT - 45;

      if (url.indexOf('TB_iframe') != -1)
      {
        urlNoQuery = url.split('TB_');
        // Edit b2m language
        jQuery("#TB_window")
            .append(
                "<div id='TB_title'><div id='TB_ajaxWindowTitle'>"
                    + caption
                    + "</div><div id='TB_closeAjaxWindow'><a href='#' id='TB_closeWindowButton' title='"
                    + joomgallery_close
                    + "'>"
                    + joomgallery_close
                    + "</a> "
                    + joomgallery_press_esc
                    + "</div></div><iframe frameborder='0' hspace='0' src='"
                    + urlNoQuery[0]
                    + "' id='TB_iframeContent' name='TB_iframeContent' style='width:"
                    + (ajaxContentW + 29) + "px;height:" + (ajaxContentH + 17)
                    + "px;' onload='tb_showIframe()'> </iframe>");
      }
      else
      {
        if (jQuery("#TB_window").css("display") != "block")
        {
          if (params['modal'] != "true")
          {
            jQuery("#TB_window")
                .append(
                    "<div id='TB_title'><div id='TB_ajaxWindowTitle'>"
                        + caption
                        + "</div><div id='TB_closeAjaxWindow'><a href='#' id='TB_closeWindowButton'>"
                        + joomgallery_close + "</a> " + joomgallery_press_esc
                        + "</div></div><div id='TB_ajaxContent' style='width:"
                        + ajaxContentW + "px;height:" + ajaxContentH
                        + "px'></div>");
            // End Edit b2m language
          }
          else
          {
            jQuery("#TB_overlay").unbind();
            jQuery("#TB_window").append(
                "<div id='TB_ajaxContent' class='TB_modal' style='width:"
                    + ajaxContentW + "px;height:" + ajaxContentH
                    + "px;'></div>");
          }
        }
        else
        {
          jQuery("#TB_ajaxContent")[0].style.width = ajaxContentW + "px";
          jQuery("#TB_ajaxContent")[0].style.height = ajaxContentH + "px";
          jQuery("#TB_ajaxContent")[0].scrollTop = 0;
          jQuery("#TB_ajaxWindowTitle").html(caption);
        }
      }

      jQuery("#TB_closeWindowButton").click(tb_remove);

      if (url.indexOf('TB_inline') != -1)
      {
        jQuery("#TB_ajaxContent").html(jQuery('#' + params['inlineId']).html());
        tb_position();
        jQuery("#TB_load").remove();
        jQuery("#TB_window").css(
        {
          display : "block"
        });
      }
      else if (url.indexOf('TB_iframe') != -1)
      {
        tb_position();
        if (frames['TB_iframeContent'] === undefined)
        {// be nice to safari
          jQuery("#TB_load").remove();
          jQuery("#TB_window").css(
          {
            display : "block"
          });
          jQuery(document).keyup(function(e)
          {
            var key = e.keyCode;
            if (key == 27)
            {
              tb_remove();
            }
          });
        }
      }
      else
      {
        jQuery("#TB_ajaxContent").load(
            url += "&random=" + (new Date().getTime()), function()
            {// to do a post change this load method
              tb_position();
              jQuery("#TB_load").remove();
              tb_init("#TB_ajaxContent a.thickbox");
              jQuery("#TB_window").css(
              {
                display : "block"
              });
            });
      }

    }

    if (!params['modal'])
    {
      document.onkeyup = function(e)
      {
        if (e == null)
        { // ie
          keycode = event.keyCode;
        }
        else
        { // mozilla
          keycode = e.which;
        }
        if (keycode == 27)
        { // close
          tb_remove();
        }
      };
    }

  } catch (e)
  {
    // nothing here
  }
}

// helper functions below
function tb_showIframe()
{
  jQuery("#TB_load").remove();
  jQuery("#TB_window").css(
  {
    display : "block"
  });
}

function tb_remove()
{
  jQuery("#TB_imageOff").unbind("click");
  jQuery("#TB_overlay").unbind("click");
  jQuery("#TB_closeWindowButton").unbind("click");
  jQuery("#TB_window").fadeOut("fast", function()
  {
    jQuery('#TB_window,#TB_overlay,#TB_HideSelect').remove();
  });
  jQuery("#TB_load").remove();
  if (typeof document.body.style.maxHeight == "undefined")
  {// if IE 6
    jQuery("body", "html").css(
    {
      height : "auto",
      width : "auto"
    });
    jQuery("html").css("overflow", "");
  }
  // restore the onkeydown event from JoomGallery
  document.onkeydown = joom_onkeydownsave;

  return false;
}

function tb_position()
{
  jQuery("#TB_window").css(
  {
    marginLeft : '-' + parseInt((TB_WIDTH / 2), 10) + 'px',
    width : TB_WIDTH + 'px'
  });
  if (!(jQuery.browser.msie && typeof XMLHttpRequest == 'function'))
  { // take away IE6
    jQuery("#TB_window").css(
    {
      marginTop : '-' + parseInt((TB_HEIGHT / 2), 10) + 'px'
    });
  }
}

function tb_parseQuery(query)
{
  var Params =
  {};
  if (!query)
  {
    return Params;
  }// return empty object
  var Pairs = query.split(/[;&]/);
  for ( var i = 0; i < Pairs.length; i++)
  {
    var KeyVal = Pairs[i].split('=');
    if (!KeyVal || KeyVal.length != 2)
    {
      continue;
    }
    var key = unescape(KeyVal[0]);
    var val = unescape(KeyVal[1]);
    val = val.replace(/\+/g, ' ');
    Params[key] = val;
  }
  return Params;
}

function tb_getPageSize()
{
  var de = document.documentElement;
  var w = window.innerWidth || self.innerWidth || (de && de.clientWidth)
      || document.body.clientWidth;
  var h = window.innerHeight || self.innerHeight || (de && de.clientHeight)
      || document.body.clientHeight;
  arrayPageSize =
  [ w, h ];
  return arrayPageSize;
}
