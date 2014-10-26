// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/media/joomgallery/js/maintenance.js $
// $Id: maintenance.js 4078 2013-02-12 10:56:43Z erftralle $
/****************************************************************************************\
**   JoomGallery 3                                                                      **
**   By: JoomGallery::ProjectTeam                                                       **
**   Copyright (C) 2008 - 2013  JoomGallery::ProjectTeam                                **
**   Based on: JoomGallery 1.0.0 by JoomGallery::ProjectTeam                            **
**   Released under GNU GPL Public License                                              **
**   License: http://www.gnu.org/copyleft/gpl.html or have a look                       **
**   at administrator/components/com_joomgallery/LICENSE.TXT                            **
\****************************************************************************************/

function joom_selectnewuser(id)
{
  if(document.adminForm.tab.value == 'categories')
  {
    var task = 'setcategoryuser';
  }
  else
  {
    var task = 'setuser';
  }

  document.id('newuser').inject('correctuser' + id);
  document.id('filesave').inject('correctuser' + id);
  document.id('filesave').removeEvents();
  document.id('filesave').addEvent('click', function(){
    listItemTask('cb' + id, task);
  });
}

function joom_selectbatchjob(job)
{
  if(job == 'setuser')
  {
    document.id('newuser').inject('batchjobs');
    document.id('usertip').inject('batchjobs');
    document.id('filesave').inject('garage');
  }
  else
  {
    document.id('newuser').inject('garage');
    document.id('usertip').inject('garage');
    document.id('filesave').inject('garage');
  }

  if(document.adminForm.tab.value == 'categories' && job == 'setuser')
  {
    document.adminForm.task.value = 'setcategoryuser';
  }
  else
  {
    document.adminForm.task.value = job;
  }
}