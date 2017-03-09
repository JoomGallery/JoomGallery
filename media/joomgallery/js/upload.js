// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/media/joomgallery/js/upload.js $
// $Id: upload.js 4078 2013-02-12 10:56:43Z erftralle $
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
 * Validation for upload fields of single upload in frontend and backend
 *
 * @return  boolean True if form can be submitted, false otherwise
 * @since   2.1
 */
function joomOnSubmit()
{
  var form = document.getElementById('upload-form');
  var no_files_selected = true;
  var fullfields = new Array();
  var screenshotfieldname = new Array();
  var screenshotfieldvalue = new Array();
  document.id('arrscreenshot').removeClass('invalid');
  document.id('arrscreenshot').set('aria-invalid', 'false');
  document.id('arrscreenshot-lbl').removeClass('invalid');
  document.id('arrscreenshot-lbl').set('aria-invalid', 'false');

  // Search for filled fields
  var counter = 0;
  var number_of_fields = 10;
  if(typeof jg_inputcounter != "undefined")
  {
    number_of_fields = jg_inputcounter;
  }
  for(i = 0; i < number_of_fields; i++)
  {
    screenshotfieldname[i] = 'arrscreenshot['+i+']';
    screenshotfieldvalue[i] = document.getElementsByName(screenshotfieldname[i])[0].value;
    document.id('arrscreenshot' + i).removeClass('invalid');
    document.id('arrscreenshot' + i).set('aria-invalid', 'false');
    if(screenshotfieldvalue[i] != '')
    {
      no_files_selected = false;
      fullfields[counter] = i;
      counter++;
    }
  }
  if(no_files_selected)
  {
    alert(Joomla.JText._('COM_JOOMGALLERY_COMMON_ALERT_YOU_MUST_SELECT_ONE_IMAGE'));
    document.getElementsByName(screenshotfieldname[0])[0].focus();
    document.id('arrscreenshot').addClass('invalid');
    document.id('arrscreenshot').set('aria-invalid', 'true');
    document.id('arrscreenshot-lbl').addClass('invalid');
    document.id('arrscreenshot-lbl').set('aria-invalid', 'true');

    return false;
  }

  // Check file extensions
  var extensions_not_ok = false;
  var searchextensiontest = new Array();
  var searchextension = new Array();

  // You have to define this RegExp for each item
  searchextension[0] = new RegExp('\.jpg$|\.jpeg$|\.jpe$|\.gif$|\.png$','ig');
  searchextension[1] = new RegExp('\.jpg$|\.jpeg$|\.jpe$|\.gif$|\.png$','ig');
  searchextension[2] = new RegExp('\.jpg$|\.jpeg$|\.jpe$|\.gif$|\.png$','ig');
  searchextension[3] = new RegExp('\.jpg$|\.jpeg$|\.jpe$|\.gif$|\.png$','ig');
  searchextension[4] = new RegExp('\.jpg$|\.jpeg$|\.jpe$|\.gif$|\.png$','ig');
  searchextension[5] = new RegExp('\.jpg$|\.jpeg$|\.jpe$|\.gif$|\.png$','ig');
  searchextension[6] = new RegExp('\.jpg$|\.jpeg$|\.jpe$|\.gif$|\.png$','ig');
  searchextension[7] = new RegExp('\.jpg$|\.jpeg$|\.jpe$|\.gif$|\.png$','ig');
  searchextension[8] = new RegExp('\.jpg$|\.jpeg$|\.jpe$|\.gif$|\.png$','ig');
  searchextension[9] = new RegExp('\.jpg$|\.jpeg$|\.jpe$|\.gif$|\.png$','ig');
  for(i = 0; i < fullfields.length; i++)
  {
    searchextensiontest = searchextension[i].test(screenshotfieldvalue[fullfields[i]]);
    if(!searchextensiontest)
    {
      extensions_not_ok = true;
      document.id('arrscreenshot' + fullfields[i]).addClass('invalid');
      document.id('arrscreenshot' + fullfields[i]).set('aria-invalid', 'true');
    }
  }
  if(extensions_not_ok)
  {
    alert(Joomla.JText._('COM_JOOMGALLERY_COMMON_ALERT_WRONG_EXTENSION'));
    document.getElementsByName(screenshotfieldname[0])[0].focus();
    document.id('arrscreenshot').addClass('invalid');
    document.id('arrscreenshot').set('aria-invalid', 'true');
    document.id('arrscreenshot-lbl').addClass('invalid');
    document.id('arrscreenshot-lbl').set('aria-invalid', 'true');

    return false;
  }

  // Check for special characters if required
  if(!jg_filenamewithjs)
  {
    var filenames_not_ok = false;
    var searchwrongchars = /[^a-zA-Z0-9 _-]/;
    var lastbackslash = new Array();
    var endoffilename = new Array();
    var filename = new Array();
    for(i = 0; i < fullfields.length; i++)
    {
      lastbackslash[i] = screenshotfieldvalue[fullfields[i]].lastIndexOf('\\');
      if(lastbackslash[i] < 1)
      {
        lastbackslash[i] = screenshotfieldvalue[fullfields[i]].lastIndexOf('/');
      }
      endoffilename[i] = screenshotfieldvalue[fullfields[i]].lastIndexOf('\.') - screenshotfieldvalue[fullfields[i]].length;
      filename[i] = screenshotfieldvalue[fullfields[i]].slice(lastbackslash[i] + 1,endoffilename[i]);

      if(searchwrongchars.test(filename[i]))
      {
        filenames_not_ok = true;
        document.id('arrscreenshot' + fullfields[i]).addClass('invalid');
        document.id('arrscreenshot' + fullfields[i]).set('aria-invalid', 'true');
      }
    }
    if(filenames_not_ok)
    {
      alert(Joomla.JText._('COM_JOOMGALLERY_COMMON_ALERT_WRONG_FILENAME'));
      document.getElementsByName(screenshotfieldname[0])[0].focus();
      document.id('arrscreenshot').addClass('invalid');
      document.id('arrscreenshot').set('aria-invalid', 'true');
      document.id('arrscreenshot-lbl').addClass('invalid');
      document.id('arrscreenshot-lbl').set('aria-invalid', 'true');

      return false;
    }
  }

  // Check whether images have been selected twice
  if(fullfields.length> 1 )
  {
    var double_files = false;
    var field1 = new Number();
    var field2 = new Number();
    for(i = 0; i < fullfields.length; i++)
    {
      for(j = fullfields.length - 1; j > i; j--)
      {
        if(screenshotfieldvalue[fullfields[i]].indexOf(screenshotfieldvalue[fullfields[j]]) == 0)
        {
          double_files = true;
          document.id('arrscreenshot' + fullfields[i]).addClass('invalid');
          document.id('arrscreenshot' + fullfields[i]).set('aria-invalid', 'true');
          document.id('arrscreenshot' + fullfields[j]).addClass('invalid');
          document.id('arrscreenshot' + fullfields[j]).set('aria-invalid', 'true');
          field1 = i + 1;
          field2 = j + 1
          alert(Joomla.JText._('COM_JOOMGALLERY_UPLOAD_ALERT_FILENAME_DOUBLE_ONE') + field1 + ' ' + Joomla.JText._('COM_JOOMGALLERY_UPLOAD_ALERT_FILENAME_DOUBLE_TWO') + field2 + '.');
        }
      }
    }
    if(double_files)
    {
      document.getElementsByName(screenshotfieldname[0])[0].focus();
      document.id('arrscreenshot').addClass('invalid');
      document.id('arrscreenshot').set('aria-invalid', 'true');
      document.id('arrscreenshot-lbl').addClass('invalid');
      document.id('arrscreenshot-lbl').set('aria-invalid', 'true');

      return false;
    }
  }

  return true;
}