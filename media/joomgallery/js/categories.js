// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/media/joomgallery/js/categories.js $
// $Id: categories.js 4078 2013-02-12 10:56:43Z erftralle $
/******************************************************************************\
**   JoomGallery 3                                                            **
**   By: JoomGallery::ProjectTeam                                             **
**   Copyright (C) 2008 - 2013  JoomGallery::ProjectTeam                      **
**   Based on: JoomGallery 1.0.0 by JoomGallery::ProjectTeam                  **
**   Released under GNU GPL Public License                                    **
**   License: http://www.gnu.org/copyleft/gpl.html or have a look             **
**   at administrator/components/com_joomgallery/LICENSE.TXT                  **
\******************************************************************************/

var JoomGallerySearchCategories = new Class({
  Implements: [Options, Events],
  options: {
    'inputbox': 'jg-catid-input',
    'resultbox': 'jg-catid-results',
    'hiddenbox': 'catid',
    'moreresults': 'jg-catid-more-results',
    'defaultcontent': '- Search category -',
    'variablename': 'catidsearch',
    'action': '',
    'filter': 0,
    'current': 0,
    'onchange': '',
    'url': 'index.php?option=com_joomgallery&task=categories.getcategories&format=json'
  },
  initialize: function (options)
  {
    this.setOptions(options);
    this.searchstring = '';
    this.timer = null;
    this.counter = 0;
    this.more = null;
    this.visible = false;
    this.positioned = false;
    this.selectedElement = -1;
    this.elements = [];
    this.inputBox = document.id(this.options.inputbox).setProperty('autocomplete', 'off');
    //var c = this.inputBox.getCoordinates();
    this.results = document.id(this.options.resultbox).inject(document.body);
    document.id(this.options.moreresults).setStyle('display', 'none');
    this.results.reveal();
    //this.fx = new Fx.Tween(this.results).set('opacity', 0);
    //this.options.keyevents = true;
    //window.addEvent('resize', function () {
      //this.results.setStyle('left', this.getLeft())
    //}.bind(this));
    this.addEvents();
    this.keyEvents();
    this.isValid.periodical(1000, this);
    this.results.addEvent('click', function(e) {
      e.stop();
    });
    document.addEvent('click', function(e) {
      //alert(e.page.y);
      this.cancel();
    }.bind(this));
    window.addEvent('resize', function(e) {
      this.results.position({
        relativeTo: this.inputBox,
        position: 'bottomLeft',
        edge: 'upperLeft'
      });
    }.bind(this));
    this.request = new Request.JSON({
      method: 'post',
      url: this.options.url,
      link: 'chain',
      onRequest: function()
      {
        if(this.more)
        {
          // If 'more' is greater than 0 we have already displayed some results for
          // the current searchstring, so display the spinner at the more link
          document.id(this.options.moreresults).addClass('jg-spinner');
        }
        else
        {
          // Otherwise it is a new searchstring and we have to remove all previous results first
          document.id(this.options.moreresults).set('style', 'display:none;');
          var children = $$('#' + this.options.resultbox + ' div.categories-results');
          children.destroy();
          this.inputBox.addClass('jg-spinner').reveal();
          this.more = null;
          this.selectedElement = -1;
          this.elements = [];
          this.fireEvent('loaded');

          // Position the results box now because position could be not correct earlier
          if(!this.positioned)
          {
            this.results.position({
              relativeTo: this.inputBox,
              position: 'bottomLeft',
              edge: 'upperLeft'
            });
            this.positioned = true;
          }
        }
      }.bind(this),
      onSuccess: function(r) {
        if (r.error && r.message)
        {
          alert(r.message);
        }
        if (r.messages)
        {
          Joomla.renderMessages(r.messages);
        }
        if(r.data)
        {
          if(r.data.results)
          {
            this.insertResults(r.data.results);
          }
          if(r.data.more)
          {
            // If there are more results than the sent ones
            // display the more link
            this.more = r.data.more;
            document.id(this.options.moreresults).reveal();
          }
          else
          {
            this.more = null;
            document.id(this.options.moreresults).set('style', 'display:none;');
          }
        }
        document.id(this.options.inputbox).removeClass('jg-spinner');
        document.id(this.options.moreresults).removeClass('jg-spinner');
      }.bind(this),
      onFailure: function(xhr)
      {
        alert(Joomla.JText._('COM_JOOMGALLERY_COMMON_REQUEST_ERROR'));
        document.id(this.options.inputbox).removeClass('jg-spinner');
        document.id(this.options.moreresults).removeClass('jg-spinner');
      }.bind(this),
      onError: function(text, error)
      {
        alert(error + "\n\n" + text);
        document.id(this.options.inputbox).removeClass('jg-spinner');
        document.id(this.options.moreresults).removeClass('jg-spinner');
      }.bind(this)
    });
  },
  /*getLeft: function () {
    var a = this.inputBox.getCoordinates(),
    x = document.id(this.options.resultbox).getSize().x;
    var b = document.id(window).getSize(),
    left;
    if (b.x / 2 < a.left + a.width) {
      left = a.left + a.width - x
    } else {
      left = a.left
    }
    if (left < 0) left = a.left;
    return left
  },*/
  addEvents: function () {
    this.inputBox.addEvents({
      'click': function (e) {
        if(this.inputBox.get('value') == this.options.defaultcontent)
        {
          this.inputBox.value = '';
        }
        e.stop();
      }.bind(this),
      'blur': function () {
        if(this.inputBox.get('value') == '')
        {
          this.inputBox.value = this.options.defaultcontent;
          document.id(this.options.hiddenbox).value = this.options.current;
        }
      }.bind(this),
      'keydown': function (e) {
        e = new DOMEvent(e);
        clearTimeout(this.timer);
        if (e.key == 'enter') e.stop()
      },
      'keyup': function (e) {
        e = new DOMEvent(e);
        if (e.code == 17 || e.code == 18 || e.code == 224 || e.alt || e.control || e.meta) return false;
        if (e.alt || e.control || e.meta || e.key == 'esc' || e.key == 'up' || e.key == 'down' || e.key == 'left' || e.key == 'right') return true;
        if (e.key == 'enter') e.stop();
        if (e.key == 'enter' && this.selectedElement != -1)
        {
          if(this.selectedElement == -2)
          {
            this.loadMore();
          }
          else if(this.selectedElement || this.selectedElement == 0)
          {
            eval(this.elements[this.selectedElement].get('data-onclick'));
          }
          return false;
        };

        clearTimeout(this.timer);
        this.searchstring = this.inputBox.value;
        this.more = null;
        this.timer = this.request.post.delay(500, this.request, 'searchstring=' + encodeURIComponent(this.searchstring) + '&action=' + this.options.action + '&filter=' + this.options.filter + '&current=' + this.options.current);
      }.bind(this)
    });
  },
  keyEvents: function () {
    var b = {
      'keyup': function (e) {
        e = new DOMEvent(e);
        if (e.key == 'up' || e.key == 'down' || e.key == 'enter' || e.key == 'esc') {
          e.stop();
          if(e.key == 'esc')
          {
            this.cancel();
          }
          else if(e.key == 'down')
          {
            if(this.selectedElement == -2)
            {
              return;
            }

            a = this.selectedElement;
            if(this.selectedElement + 1 < this.elements.length)
            {
              this.selectedElement++;
            }
            else
            {
              if(this.more)
              {
                this.selectedElement = -2;
              }
              else
              {
                return;
              }
            }
            if(a != -1)
            {
              this.elements[a].removeClass('jg-category-result-hover');
            }
            if(this.selectedElement == -2)
            {
              document.id(this.options.moreresults).addClass('jg-category-result-hover');
            }
            else if(this.selectedElement || this.selectedElement == 0)
            {
              new Fx.Scroll(window).toElement(this.elements[this.selectedElement]);
              this.elements[this.selectedElement].addClass('jg-category-result-hover');
            }
          }
          else if(e.key == 'up')
          {
            a = this.selectedElement;
            if(this.selectedElement - 1 >= 0)
            {
              this.selectedElement--;
            }
            else
            {
              if(this.selectedElement == -2)
              {
                this.selectedElement = this.elements.length - 1;
              }
              else
              {
                return;
              }
            }
            if(a != -1 && a != -2)
            {
              this.elements[a].removeClass('jg-category-result-hover');
            }
            if(a == -2)
            {
              document.id(this.options.moreresults).removeClass('jg-category-result-hover');
              this.elements[this.selectedElement].addClass('jg-category-result-hover');
            }
            else if(this.selectedElement || this.selectedElement == 0)
            {
              new Fx.Scroll(window).toElement(this.elements[this.selectedElement]);
              this.elements[this.selectedElement].addClass('jg-category-result-hover');
            }
          }
          /*else if(e.key == 'enter')
          {
            if(this.selectedElement || this.selectedElement == 0)
            {
              eval(this.elements[this.selectedElement].get('data-onclick'));
            }
          }*/
        }
      }.bind(this)
    };
    if(!this.options.keyevents)
    {
      this.addEvent('loaded', function () {
        document.addEvent('keyup', b.keyup)
      });
      this.addEvent('unloaded', function () {
        document.removeEvent('keyup', b.keyup)
      })
    }
  },
  insertResults : function(results)
  {
    this.visible = true;
    var count = this.elements.length;

    // For creating an individual ID for each result we use a counter
    this.counter = this.counter + 1;

    // Create a container into which all the results will be inserted
    var results_div = new Element('div', {
      id: this.options.resultbox + this.counter,
      'class': 'categories-results',
      style: 'display:none;'
    });

    // Create some elements for each result and insert it into the container
    Array.each(results, function (item, index) {
      var div = new Element('div', {
        'class':	'jg-category-result row' + index%2,
        onclick:	this.options.variablename + '.selectCategory(' + this.counter + index + ');',
        'data-onclick': this.options.variablename + '.selectCategory(' + this.counter + index + ')'
      });
      this.elements.push(div);
      //div.addEvent('click', function(e, test, test2) {
        //alert(arguments.length);
      //}.bind(this));
      var path = new Element('div', {
        id:				'category-path' + this.counter + index,
        'class':	'result-path',
        html:			'&nbsp;' + item.path
      });
      path.inject(div);
      var key = new Element('div', {
        id:				'category-name' + this.counter + index,
        'class':	'result-name',
        html:			'&nbsp;&raquo; ' + item.name,
        'data-cid': item.cid,
        'data-name':item.name
      });
      if(item.none)
      {
        key.set('data-name', '-');
      }
      key.inject(div);
      div.inject(results_div);
    }, this);

    // If there aren't any results display an appropriate message
    if(!results.length)
    {
      var noresult = new Element('div', {
        'class': 'jg-no-results',
        html: Joomla.JText._('COM_JOOMGALLERY_COMMON_CATEGORIES_NO_RESULTS')
      });
      noresult.inject(results_div);
    }

    // Finally insert the container afore the more link and reveal it
    results_div.inject(document.id(this.options.moreresults), 'before');
    document.id(this.options.resultbox + this.counter).reveal();

    if(this.selectedElement == -2)
    {
      document.id(this.options.moreresults).removeClass('jg-category-result-hover');
      this.selectedElement = count;
      this.elements[this.selectedElement].addClass('jg-category-result-hover');
    }

    var scroll = new Fx.Scroll(window);
    scroll.toElementEdge.delay(500, scroll, this.options.moreresults);
  },
  loadMore : function()
  {
    this.request.post('searchstring=' + encodeURIComponent(this.searchstring) + '&more=' + this.more + '&action=' + this.options.action + '&filter=' + this.options.filter + '&current=' + this.options.current);
  },
  selectCategory : function(id)
  {
    this.fireEvent('unloaded');
    document.id(this.options.inputbox).value = document.id('category-name' + id).get('data-name');
    document.id(this.options.hiddenbox).value = document.id('category-name' + id).get('data-cid');
    this.options.defaultcontent = document.id('category-name' + id).get('data-name');
    var children = $$('#' + this.options.resultbox + ' div.categories-results');
    children.destroy();
    document.id(this.options.moreresults).set('style', 'display:none;');
    this.visible = false;
    if(this.options.onchange.length)
    {
      eval(this.options.onchange);
    }
    document.id(this.options.hiddenbox).fireEvent('change');
  },
  isValid : function()
  {
    if(document.id(this.options.hiddenbox).hasClass('invalid'))
    {
      this.inputBox.addClass('invalid');
    }
    else
    {
      this.inputBox.removeClass('invalid');
    }
  },
  cancel : function()
  {
    if(this.visible)
    {
      this.fireEvent('unloaded');
      this.inputBox.set('value', this.options.defaultcontent);
      var children = $$('#' + this.options.resultbox + ' div.categories-results');
      children.destroy();
      document.id(this.options.moreresults).set('style', 'display:none;');
      this.selectedElement = -1;
      this.elements = [];
      this.visible = false;
    }
  }
});