// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/media/joomgallery/js/permissions.js $
// $Id: permissions.js 4078 2016-04-05 10:56:43Z erftralle $
/******************************************************************************\
**   JoomGallery 3                                                            **
**   By: JoomGallery::ProjectTeam                                             **
**   Copyright (C) 2008 - 2016  JoomGallery::ProjectTeam                      **
**   Based on: JoomGallery 1.0.0 by JoomGallery::ProjectTeam                  **
**   Released under GNU GPL Public License                                    **
**   License: http://www.gnu.org/copyleft/gpl.html or have a look             **
**   at administrator/components/com_joomgallery/LICENSE.TXT                  **
\******************************************************************************/

/**
 * Override for Joomla!'s function to send permissions via AJAX to com_config
 * application controller.
 * 
 * @since   3.2
 */
var sendPermissions = function(event)
{
  // Set the icon while storing the values
  var icon = document.getElementById('icon_' + this.id);
  icon.removeAttribute('class');
  icon.setAttribute('style', 'background: url(../media/system/images/modal/spinner.gif); display: inline-block; width: 16px; height: 16px');

  // Get values and prepare GET-Parameter
  var id = this.id.split('_');
  var asset = null;
  var option = getUrlParam('option');
  var controller = getUrlParam('controller');  
  var task = getUrlParam('task');
  var value = this.value;
  
  if (controller == 'categories' && task == 'edit')
  {
    asset = option + '.category.' + getUrlParam('cid');
  }
  else if (controller == 'images' && task == 'edit' )
  {
    asset = option + '.image.' + getUrlParam('cid');
  }
  
  var title = document.getElementById('title').value;
  
  var data = '&comp=' + asset + '&action=' + id[1] + '&rule=' + id[2] + '&value=' + value + '&title=' + title;
  var url = 'index.php?option=com_config&task=config.store&format=raw' + data;

  if(asset != null)
  {
    // Doing ajax request
    jQuery.ajax({
      type: 'GET',
      url: url,
      datatype: 'JSON'
    }).success(function (response) {
      var element = event.target;
      var resp = JSON.parse(response);
      if (resp.data == 'true')
      {
        icon.removeAttribute('style');
        icon.setAttribute('class', 'icon-save');
        if (value == '1')
        {
          jQuery(element).parents().next('td').find('span')
            .removeClass('label label-important').addClass('label label-success')
            .html(Joomla.JText._('JLIB_RULES_ALLOWED'));
        }
        else
        {
          jQuery(element).parents().next('td').find('span')
            .removeClass('label label-success').addClass('label label-important')
            .html(Joomla.JText._('JLIB_RULES_NOT_ALLOWED'));
        }
      }
      else
      {
        var msg = { error: [Joomla.JText._('JLIB_RULES_DATABASE_FAILURE ')] };
        Joomla.renderMessages(msg);
        icon.removeAttribute('style');
        icon.setAttribute('class', 'icon-cancel');
      }
      if (resp.message == 0)
      {
        var msg = { error: [Joomla.JText._('JLIB_RULES_SAVE_BEFORE_CHANGE_PERMISSIONS')] };
        Joomla.renderMessages(msg);
        icon.removeAttribute('style');
        icon.setAttribute('class', 'icon-cancel');
      }
    }).fail(function() {
      // Set cancel icon on http failure
      var msg = { error: [Joomla.JText._('JLIB_RULES_REQUEST_FAILURE')] };
      Joomla.renderMessages(msg);
      icon.removeAttribute('style');
      icon.setAttribute('class', 'icon-cancel');
    })
  }
}