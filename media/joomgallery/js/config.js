// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/media/joomgallery/js/config.js $
// $Id: config.js 4078 2013-02-12 10:56:43Z erftralle $
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
 * Overwrite function for Joomla.submitbutton.
 * Redirects to CPanel, validates the form data or cleans it before submitting. 
 *  
 * @param   string  pressbutton The button pressed
 * @return  void
 */
Joomla.submitbutton = function(pressbutton)
{
  if (pressbutton == 'cpanel')
  {
    location.href = 'index.php?option=com_joomgallery';
    return;
  }
  if(document.adminForm.jg_paththumbs && document.adminForm.jg_paththumbs.value == '')
  {
     alert(Joomla.JText._('COM_JOOMGALLERY_CONFIG_GS_PD_ALERT_THUMBNAIL_PATH_SUPPORT'));
  }
  else
  {
    joom_testDefaultValues();
    Joomla.submitform(pressbutton);
  }
};

/**
 * Test the values in configuration manager and delete the not modified values in DOM
 *
 * @return  void
 */
function joom_testDefaultValues()
{
  var what = document.adminForm;
  var result;
  var todelete = Array();
  var todeletecount = 0;
  var elemcount = what.elements.length;
  var elem = null;
  var elemType = null;
  var myName = null;

  for ( var i = 0; i < elemcount; i++)
  {
    result = false;
    elem = what.elements[i];
    elemType = what.elements[i].type;
    myName = what.elements[i].name;

    if (myName.substr(0, 3) == "jg_")
    {
      if (elemType == "text")
      {
        if (String(elem.value) == String(elem.defaultValue))
        {
          todelete[todeletecount++] = myName;
        }
        else
        {
          result = true; // save
        }
      }
      else if (elemType == "textarea")
      {
        if (String(elem.value) == String(elem.defaultValue))
        {
          todelete[todeletecount++] = myName;
        }
        else
        {
          result = true; // save
        }
      }
      else if (elemType == "select-one" || elemType == "select-multiple")
      {
        var l = elem.options.length;
        for ( var k = 0; k < l; k++)
        {
          if (String(elem.options[k].selected) != String(elem.options[k].defaultSelected))
          {
            result = true; // save
            break;
          }
        }
        if (!result)
        {
          todelete[todeletecount++] = myName;
        }
      }
    }
  }

  for ( var i = 0; i < todeletecount; i++)
  {
    var elem = document.getElementsByName(todelete[i])[0];
    elem.parentNode.removeChild(elem);
  }
};