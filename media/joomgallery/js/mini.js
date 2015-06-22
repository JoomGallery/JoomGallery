// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/media/joomgallery/js/mini.js $
// $Id: mini.js 4403 2014-06-13 07:13:01Z chraneco $
/****************************************************************************************\
**   JoomGallery 3                                                                      **
**   By: JoomGallery::ProjectTeam                                                       **
**   Copyright (C) 2008 - 2013  JoomGallery::ProjectTeam                                **
**   Based on: JoomGallery 1.0.0 by JoomGallery::ProjectTeam                            **
**   Released under GNU GPL Public License                                              **
**   License: http://www.gnu.org/copyleft/gpl.html or have a look                       **
**   at administrator/components/com_joomgallery/LICENSE.TXT                            **
\****************************************************************************************/

/**
 * Variable indicating whether the mini thumbnails are currently displayed
 *
 * @var   boolean
 * @since 3.0
 */
var jg_minis_visible = false;

/**
 * Inserts an image into the current editor window
 *
 * @param   int     id          The ID of the image to insert
 * @param   string  editor      The ID of the text area into which the image will be inserted
 * @param   object  form        The form element of the form containing the selected options, defaults to form 'imagesForm'
 * @param   string  link_type   Default value for the link type on the image
 * @param   string  linked_type Default value for the linked image type
 * @param   string  opt_class   Default value for an optional CSS class
 * @param   string  text        Default value for an optional text to link
 * @param   string  alttext     Default value for the alt text of the image
 * @return  void
 */
function insertJoomPluWithId(id, editor, form, link_type, linked_type, opt_class, text, alttext)
{
  if(form == null)
  {
    form = document.id('imagesForm');
    var link_type = radioGetCheckedValue(form.linked);
    var linked_type = radioGetCheckedValue(form.linked_type);
    var opt_class = document.getElementById('jg_bu_class').value;
    var text = document.getElementById('jg_bu_text').value;
    var alttext = document.getElementById('jg_bu_alttext').value;
  }

  var type = radioGetCheckedValue(form.type);
  var position = radioGetCheckedValue(form.position);

  options = new Array();

  if(type == 'img')
  {
    options.push('type=orig');
  }
  else
  {
    if(type != 'orig')
    {
      if(linked_type == 'orig')
      {
        options.push('type=orig');
      }
      else
      {
        options.push('type=img');
      }
    }
  }

  container = false;
  align = '';
  if(position == 'right')
  {
    align = ' style="float:right;"';
  }
  else
  {
    if(position == 'left')
    {
      align = ' style="float:left;"';
    }
    else
    {
      if(position == 'center')
      {
        container = true;
      }
      else
      {
        align = '';
      }
    }
  }

  if(alttext)
  {
    alt = alttext;
  }
  else
  {
    alt = 'joomplu:' + id;
  }

  var opt_class2 = '';
  if(opt_class)
  {
    opt_class2  = ' class="' + opt_class + '"';
    opt_class = ' ' + opt_class;
  }
  else
  {
    opt_class = '';
  }

  tag = '';

  if(container)
  {
    tag = tag + '<div style="text-align:center;">';
  }

  var linked = false;
  if(link_type == '2')
  {
    options.push('catlink=1');
    linked = true;
  }
  if(link_type == '1')
  {
    linked = true;
  }

  options_string = '';
  if(options.length)
  {
    options_string = ' ' + options.join('|');
  }

  if(linked)
  {
    tag = tag + '<a href="joomplu:' + id + options_string + '"' + opt_class2 + '>';
  }

  if(text)
  {
    tag = tag + text;
  }
  else
  {
    tag  = tag + '<img src="index.php?option=com_joomgallery&view=image&format=raw&id=' + id + '&type=' + type + '" class="jg_photo' + opt_class + '" alt="' + alt + '"' + align + ' />';
  }

  if(linked)
  {
    tag = tag + '</a>';
  }

  if(container)
  {
    tag = tag + '</div>';
  }

  window.parent.jInsertEditorText(tag, editor);
  window.parent.SqueezeBox.close();
}

/**
 * Inserts a category into the current editor window
 *
 * @param   int     id      The ID of the category to insert
 * @return  void
 */
function insertCategory(id)
{
  editor = jg_e_name;
  textlink  = document.getElementById('jg_bu_category1').checked;

  if(textlink)
  {
    linkedtext = document.getElementById('jg_bu_category_linkedtext').value;
    if(!linkedtext)
    {
      alert(Joomla.JText._('COM_JOOMGALLERY_MINI_PLEASE_ENTER_TEXT'));
      document.getElementById('category_catid').selectedIndex = 0;
      return false;
    }

    tag = '<a href="joomplulink:' + id + ' view=category">' + linkedtext + '</a>';
  }
  else
  {
    number    = document.getElementById('jg_bu_thumbnail_number').value;
    columns   = document.getElementById('jg_bu_thumbnail_columns').value;
    ordering  = document.getElementById('jg_bu_thumbnail_ordering').value;

    tag = '{joomplucat:' + id;

    options = new Array();

    if(number)
    {
      options.push('limit=' + number);
    }

    if(columns && columns != 2)
    {
      options.push('columns=' + columns);
    }

    if(ordering != 0)
    {
      options.push('ordering=random')
    }

    if(options.length)
    {
      tag = tag + ' ' + options.join('|');
    }

    tag = tag + '}';
  }

  window.parent.jInsertEditorText(tag, editor);
  window.parent.SqueezeBox.close();
}

/**
 * Does an Ajax request for the previous page
 *
 * @param   string  url The URL sending the request to
 * @return  void
 */
function ajaxRequestPrevPage(url)
{
  ajaxRequest(url, jg_minis_page - 1);
}

/**
 * Does an Ajax request for the next page
 *
 * @param   string  url The URL sending the request to
 * @return  void
 */
function ajaxRequestNextPage(url)
{
  ajaxRequest(url, jg_minis_page + 1);
}

/**
 * Does an Ajax request for a specific page
 *
 * @param   string  url   The URL sending the request to
 * @param   int     page  The page to request
 * @return  void
 */
function ajaxRequest(url, page, query)
{
  // Empty the container
  $('jg_bu_minis').empty();

  // Show spinner
  $('jg_bu_minis').addClass('jg_spinner');

  if(query != null)
  {
    query = '&' + query;
  }
  else
  {
    query = '';
  }

  // Do the Ajax request
  new Request.JSON(
            {
              url: url,
              method: 'post',
              onError: function(text, error)
              {
                // Remove spinner
                $('jg_bu_minis').removeClass('jg_spinner');

                // Set error message
                $('jg_bu_minis').set('html', error + "\n\n" + text);
              },
              onFailure: function()
              {
                // Remove spinner
                $('jg_bu_minis').removeClass('jg_spinner');

                // Set error message
                $('jg_bu_minis').set('html', 'Error');
              },
              onSuccess: function(response, responceText)
              {
                // Remove spinner and old pagination
                $('jg_bu_minis').removeClass('jg_spinner');
                $('jg_bu_pagelinks').empty();

                // Insert response
                $('jg_bu_minis').set('html', response.minis);
                $('jg_bu_pagelinks').set('html', response.pagination);

                // Now we have to create the tooltips for all the new images
                jQuery('.hasMiniTip').tooltip({container: 'body'});

                // Set current page if it was changed
                if(page > 0)
                {
                  jg_minis_page = page;
                }
              }
            }).send('page=' + page + query);
}

/**
 * Method for bootstraping radio buttons
 *
 * @return  void
 * @since   3.1
 */
function adaptRadios()
{
  // Turn radios into btn-group
  jQuery('.radio.btn-group label').addClass('btn');
  jQuery(".btn-group label:not(.active)").click(function()
  {
    var label = jQuery(this);
    var input = jQuery('#' + label.attr('for'));

    if (!input.prop('checked')) {
      label.closest('.btn-group').find("label").removeClass('active btn-success btn-danger btn-primary');
      if (input.val() == '') {
        label.addClass('active btn-primary');
      } else if (input.val() == 0) {
        label.addClass('active btn-danger');
      } else {
        label.addClass('active btn-success');
      }
      input.prop('checked', true);
    }
  });
  jQuery(".btn-group input[checked=checked]").each(function()
  {
    if (jQuery(this).val() == '') {
      jQuery("label[for=" + jQuery(this).attr('id') + "]").addClass('active btn-primary');
    } else if (jQuery(this).val() == 0) {
      jQuery("label[for=" + jQuery(this).attr('id') + "]").addClass('active btn-danger');
    } else {
      jQuery("label[for=" + jQuery(this).attr('id') + "]").addClass('active btn-success');
    }
  });
}

/**
 * Renders the insert options for images uploaded in MiniJoom
 *
 * 
 * @param   uploader  object  The uploader object
 * @param   fileName  string  Name of the uploaded file
 * @param   r         object  Response from the upload request
 * @return  void
 * @since   3.1
 */
function displayInsertOptions(uploader, item, fileName, r)
{
  // For each upload procedure scrolling is allowed only for the first image
  if(!jg_scrolled)
  {
    jg_scrolled = true;
    new Fx.Scroll(window).toElement(new Element(item));
  }
  
  // Display options container and note
  var options = new Element(item.getElementsByClassName('qq-upload-options-selector')[0]).removeClass('hide');
  new Element(item.getElementsByClassName('qq-upload-note-selector')[0]).removeClass('hide');
  
  // Render the options
  var form = new Element('form', {
    action: 'index.php',
    method: 'post',
    id: 'insertOptionsForm' + r.id,
    name: 'insertOptionsForm' + r.id
  });
  form.inject(options);

  // Image type controls
  var controlGroup1 = new Element('div', {
    'class': 'control-group'
  });
  var label1 = new Element('label', {
    id: 'jg_bu_type-lbl' + r.id,
    'for': 'jg_bu_type' + r.id,
    'class': 'control-label',
    html: Joomla.JText._('COM_JOOMGALLERY_MINI_TYPE')
  });
  label1.inject(controlGroup1);
  var controls1 = new Element('div', {
    'class': 'controls'
  });
  controls1.inject(controlGroup1);
  var fieldset1 = new Element('fieldset', {
    id: 'jg_bu_type' + r.id,
    'class': 'radio btn-group'
  });
  fieldset1.inject(controls1);
  var input11 = new Element('input', {
    id: 'jg_bu_type0' + r.id,
    type: 'radio',
    name: 'type',
    value: 'thumb'
  });
  if(default_values.type == 'thumb')
  {
    input11.setAttribute('checked', 'checked');
  }
  input11.inject(fieldset1);
  var label11 = new Element('label', {
    'for': 'jg_bu_type0' + r.id,
    html: Joomla.JText._('COM_JOOMGALLERY_COMMON_THUMBNAIL')
  });
  label11.inject(fieldset1);
  var input12 = new Element('input', {
    id: 'jg_bu_type1' + r.id,
    type: 'radio',
    name: 'type',
    value: 'img'
  });
  if(default_values.type == 'img')
  {
    input12.setAttribute('checked', 'checked');
  }
  input12.inject(fieldset1);
  var label12 = new Element('label', {
    'for': 'jg_bu_type1' + r.id,
    html: Joomla.JText._('COM_JOOMGALLERY_MINI_DETAIL')
  });
  label12.inject(fieldset1);
  var input13 = new Element('input', {
    id: 'jg_bu_type2' + r.id,
    type: 'radio',
    name: 'type',
    value: 'orig'
  });
  if(default_values.type == 'orig')
  {
    input13.setAttribute('checked', 'checked');
  }
  input13.inject(fieldset1);
  var label13 = new Element('label', {
    'for': 'jg_bu_type2' + r.id,
    html: Joomla.JText._('COM_JOOMGALLERY_MINI_ORIGINAL')
  });
  label13.inject(fieldset1);
  controlGroup1.inject(form);

  // Alignment controls
  var controlGroup2 = new Element('div', {
    'class': 'control-group'
  });
  var label2 = new Element('label', {
    id: 'jg_bu_position-lbl' + r.id,
    'for': 'jg_bu_position' + r.id,
    'class': 'control-label',
    html: Joomla.JText._('COM_JOOMGALLERY_MINI_POSITION')
  });
  label2.inject(controlGroup2);
  var controls2 = new Element('div', {
    'class': 'controls'
  });
  controls2.inject(controlGroup2);
  var fieldset2 = new Element('fieldset', {
    id: 'jg_bu_position' + r.id,
    'class': 'radio btn-group'
  });
  fieldset2.inject(controls2);
  var input21 = new Element('input', {
    id: 'jg_bu_position0' + r.id,
    type: 'radio',
    name: 'position',
    value: ''
  });
  if(default_values.position == '')
  {
    input21.setAttribute('checked', 'checked');
  }
  input21.inject(fieldset2);
  var label21 = new Element('label', {
    'for': 'jg_bu_position0' + r.id,
    html: Joomla.JText._('JNONE')
  });
  label21.inject(fieldset2);
  var input22 = new Element('input', {
    id: 'jg_bu_position1' + r.id,
    type: 'radio',
    name: 'position',
    value: 'center'
  });
  if(default_values.position == 'center')
  {
    input22.setAttribute('checked', 'checked');
  }
  input22.inject(fieldset2);
  var label22 = new Element('label', {
    'for': 'jg_bu_position1' + r.id,
    html: Joomla.JText._('JGLOBAL_CENTER')
  });
  label22.inject(fieldset2);
  var input23 = new Element('input', {
    id: 'jg_bu_position2' + r.id,
    type: 'radio',
    name: 'position',
    value: 'left'
  });
  if(default_values.position == 'left')
  {
    input23.setAttribute('checked', 'checked');
  }
  input23.inject(fieldset2);
  var label23 = new Element('label', {
    'for': 'jg_bu_position2' + r.id,
    html: Joomla.JText._('JGLOBAL_LEFT')
  });
  label23.inject(fieldset2);
  var input24 = new Element('input', {
    id: 'jg_bu_position3' + r.id,
    type: 'radio',
    name: 'position',
    value: 'right'
  });
  if(default_values.position == 'right')
  {
    input24.setAttribute('checked', 'checked');
  }
  input24.inject(fieldset2);
  var label24 = new Element('label', {
    'for': 'jg_bu_position3' + r.id,
    html: Joomla.JText._('JGLOBAL_RIGHT')
  });
  label24.inject(fieldset2);
  controlGroup2.inject(form);

  // Image title controls
  var controlGroup3 = new Element('div', {
    'class': 'control-group'
  });
  var label3 = new Element('label', {
    id: 'jg_bu_title-lbl' + r.id,
    'for': 'caption' + r.id,
    'class': 'control-label',
    html: Joomla.JText._('COM_JOOMGALLERY_MINI_ALTTEXT')
  });
  label3.inject(controlGroup3);
  var controls3 = new Element('div', {
    'class': 'controls'
  });
  controls3.inject(controlGroup3);
  var input = new Element('input', {
    type: 'text',
    placeholder: r.imgtitle,
    name: 'caption' + r.id,
    id: 'caption' + r.id,
    'class': 'span12'
  });
  input.inject(controls3);
  controlGroup3.inject(form);

  // Insert button
  var controlGroup4 = new Element('div', {
    'class': 'control-group'
  });
  var controls4 = new Element('div', {
    'class': 'controls'
  });
  controls4.inject(controlGroup4);
  var insert = 'insertJoomPluWithId(' + r.id + ', jg_e_name, document.id(\'insertOptionsForm' + r.id + '\'), default_values.linked, ';
  insert    += 'default_values.linked_type, default_values.opt_class, default_values.text, document.id(\'caption' + r.id + '\').value);';
  var button = new Element('button', {
    'class': 'btn btn-primary',
    html: 'Insert',
    onclick: insert
  });
  button.inject(controls4);
  controlGroup4.inject(form);

  adaptRadios();
}

// Preparations
window.addEvent('domready', function()
{
  jQuery('.hasTooltip').tooltip({container: 'body'});

  jQuery('.hasMiniTip').tooltip({container: 'body'});

  document.formvalidator.setHandler('joompositivenumeric', function(value) {
    regex=/^[1-9]+[0-9]*$/;
    return regex.test(value);
  });

  jQuery('*[rel=tooltip]').tooltip();

  adaptRadios();
});

(function($)
{
  $('#joomgallery-images-sliders').collapse({ parent: false, toggle: true, active: 'param-page-images-options'});
})(jQuery);