// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-3/JG/trunk/media/joomgallery/js/jswindow.js $
// $Id: jswindow.js 4078 2013-02-12 10:56:43Z erftralle $
/****************************************************************************************\
**   JoomGallery 3                                                                      **
**   By: JoomGallery::ProjectTeam                                                       **
**   Copyright (C) 2008 - 2013  JoomGallery::ProjectTeam                                **
**   Based on: JoomGallery 1.0.0 by JoomGallery::ProjectTeam                            **
**   Released under GNU GPL Public License                                              **
**   License: http://www.gnu.org/copyleft/gpl.html or have a look                       **
**   at administrator/components/com_joomgallery/LICENSE.TXT                            **
\****************************************************************************************/

function joom_openjswindow(imgsource, imgtitle, imgwidth, imgheight) {
  var imgwidth = parseInt(imgwidth);
  var imgheight = parseInt(imgheight);
  var scrbar = (resizeJsImage>0) ? 0 : 1;
  pgwindow = window.open('', 'JoomGallery', 'toolbar=0,location=0,directories=0,status=0,menubar=0,resizable=0,scrollbars='+scrbar+',width='+imgwidth+',height='+imgheight+'');
  with(pgwindow.document) {
    write("<html><head><title>" + imgtitle + "<\/title>\n");
    write("<meta http-equiv='imagetoolbar' content='no' />\n");
    write("<script language='javascript' type='text/javascript'>\n");
    write("<!--\n");
    write("var disableclick = "+jg_disableclick+";\n");
    write("if (disableclick>0) {document.oncontextmenu = function(){return false;} }\n");
    write("function resize() {\n");
    write(" if("+resizeJsImage+">0) {\n");
    write("  var windowWidth, windowHeight, padleft, padtop;\n" );
    write("  if (self.innerHeight) {	// all except Explorer\n" );
    write("   windowWidth = self.innerWidth;\n" );
    write("   windowHeight = self.innerHeight;\n" );
    write("   padleft = 6;\n" );
    write("   padtop = 55;\n" );
    write("  } else if (document.documentElement && document.documentElement.clientHeight) { // Explorer 6 Strict Mode\n" );
    write("   windowWidth = document.documentElement.clientWidth;\n" );
    write("   windowHeight = document.documentElement.clientHeight;\n" );
    write("   padleft = 10;\n" );
    write("   padtop = 35;\n" );
    write("  } else if (document.body) { // other Explorers\n" );
    write("   windowWidth = document.body.clientWidth;\n" );
    write("   windowHeight = document.body.clientHeight;\n" );
    write("   padleft = 10;\n" );
    write("   padtop = 35;\n" );
    write("  }\n" );
    write("  var imgwidth = "+imgwidth+"+padleft;\n");
    write("  var imgheight = "+imgheight+"+padtop;\n");
    write("  if(imgwidth>windowWidth) {\n");
    write("    imgheight = (imgheight * windowWidth)/imgwidth;\n");
    write("    imgwidth = windowWidth;\n");
    write("  }\n");
    write("  if(imgheight>windowHeight) {\n");
    write("    imgwidth = (imgwidth * windowHeight)/imgheight;\n");
    write("    imgheight = windowHeight;\n");
    write("  }\n");    
    write("  self.resizeTo(imgwidth, imgheight);\n");
    write("  self.document.getElementById('js_window_image').width = imgwidth-padleft;\n");
    write("  self.document.getElementById('js_window_image').style.width = imgwidth-padleft;\n");
    write("  self.document.getElementById('js_window_image').height = imgheight-padtop;\n");
    write("  self.document.getElementById('js_window_image').style.height = imgheight-padtop;\n");
    write("  self.document.body.style.overflow='hidden'\n");
    write(" } else {\n");
    write("  self.document.body.style.overflow=''\n");
    write(" }\n");
    write(" self.focus();\n");
    write("}\n");
    write("function clicker() { \n");
    write("if (disableclick>0) {self.close(); } \n");
    write("}\n");
    write("\/\/-->\n");
    write("<\/script>\n");
    write("<\/head>\n");
    write("<body topmargin='0' marginheight='0' leftmargin='0' marginwidth='0' onload='resize()' onclick='clicker()' onblur='self.focus()'>\n");
    write("<img src='" + imgsource + "' border='0' hspace='0' vspace='0' onclick='self.close()' alt='"+imgtitle+"'\ id=\"js_window_image\" class=\"pngfile\" />\n");
    write("<\/body><\/html>");
    close();
  }
  pgwindow.moveTo(0,0);
}