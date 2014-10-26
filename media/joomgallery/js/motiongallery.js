/***********************************************
* CMotion Image Gallery- � Dynamic Drive DHTML code library (www.dynamicdrive.com)
* Visit http://www.dynamicDrive.com for source code
* This copyright notice must stay intact for legal use
* Modified for autowidth and optional starting positions in
* http://www.dynamicdrive.com/forums/showthread.php?t=11839 by jschuer1 8/5/06
***********************************************/

 //1) Set width of the "neutral" area in the center of the gallery.
var restarea=6;
 //2) Set top scroll speed in pixels. Script auto creates a range from 0 to top speed.
var maxspeed=7;
 //3) Set to maximum width for gallery - must be less than the actual length of the image train.
var maxwidth=1000;
 //4) Set to 1 for left start, 0 for right, 2 for center.
var startpos=3; //edit JoomGallery Team 
 //5) Set message to show at end of gallery. Enter "" to disable message.
var endofgallerymsg="";

function enlargeimage(path, optWidth, optHeight){ //function to enlarge image. Change as desired.
var actualWidth=typeof optWidth!="undefined" ? optWidth : "600px" //set 600px to default width
var actualHeight=typeof optHeight!="undefined" ? optHeight : "500px" //set 500px to  default height
var winattributes="width="+actualWidth+",height="+actualHeight+",resizable=yes"
window.open(path,"", winattributes)
}

////NO NEED TO EDIT BELOW THIS LINE////////////

var iedom=document.all||document.getElementById, scrollspeed=0, movestate='', actualwidth='', cross_scroll, ns_scroll, statusdiv, loadedyes=0, lefttime, righttime;

function ietruebody(){
return (document.compatMode && document.compatMode!="BackCompat")? document.documentElement : document.body;
}

function creatediv(){
statusdiv=document.createElement("div")
statusdiv.setAttribute("id","statusdiv")
document.body.appendChild(statusdiv)
statusdiv=document.getElementById("statusdiv")
statusdiv.innerHTML=endofgallerymsg
}

function positiondiv(){
var mainobjoffset=getposOffset(crossmain, "left"),
menuheight=parseInt(crossmain.offsetHeight),
mainobjoffsetH=getposOffset(crossmain, "top");
statusdiv.style.left=mainobjoffset+(menuwidth/2)-(statusdiv.offsetWidth/2)+"px";
statusdiv.style.top=menuheight+mainobjoffsetH+"px";
}

function showhidediv(what){
if (endofgallerymsg!="") {
positiondiv();
statusdiv.style.visibility=what;
}
}

function getposOffset(what, offsettype){
var totaloffset=(offsettype=="left")? what.offsetLeft: what.offsetTop;
var parentEl=what.offsetParent;
while (parentEl!=null){
totaloffset=(offsettype=="left")? totaloffset+parentEl.offsetLeft : totaloffset+parentEl.offsetTop;
parentEl=parentEl.offsetParent;
}
return totaloffset;
}


function moveleft(){
if (loadedyes){
movestate="left";
if (iedom&&parseInt(cross_scroll.style.left)>(menuwidth-actualwidth)){

cross_scroll.style.left=parseInt(cross_scroll.style.left)-scrollspeed+"px";
showhidediv("hidden");
}
else
showhidediv("visible");
}
lefttime=setTimeout("moveleft()",10);
}

function moveright(){
if (loadedyes){
movestate="right";

if (iedom&&parseInt(cross_scroll.style.left)<0){


cross_scroll.style.left=parseInt(cross_scroll.style.left)+scrollspeed+"px";
showhidediv("hidden");
}
else
showhidediv("visible");
}
righttime=setTimeout("moveright()",10);
}

function motionengine(e){
var mainobjoffset=getposOffset(crossmain, "left"),
dsocx=(window.pageXOffset)? pageXOffset: ietruebody().scrollLeft,
dsocy=(window.pageYOffset)? pageYOffset : ietruebody().scrollTop,
curposy=window.event? event.clientX : e.clientX? e.clientX: "";
curposy-=mainobjoffset-dsocx;
var leftbound=(menuwidth-restarea)/2;
var rightbound=(menuwidth+restarea)/2;
if (curposy>rightbound){
scrollspeed=(curposy-rightbound)/((menuwidth-restarea)/2) * maxspeed;
clearTimeout(righttime);
if (movestate!="left") moveleft();
}
else if (curposy<leftbound){
scrollspeed=(leftbound-curposy)/((menuwidth-restarea)/2) * maxspeed;
clearTimeout(lefttime);
if (movestate!="right") moveright();
}
else
scrollspeed=0;
}

function contains_ns6(a, b) {
if (b!==null)
while (b.parentNode)
if ((b = b.parentNode) == a)
return true;
return false;
}

function stopmotion(e){
if (!window.opera||(window.opera&&e.relatedTarget!==null))
if ((window.event&&!crossmain.contains(event.toElement)) || (e && e.currentTarget && e.currentTarget!= e.relatedTarget && !contains_ns6(e.currentTarget, e.relatedTarget))){
clearTimeout(lefttime);
clearTimeout(righttime);
movestate="";
}
}

function fillup(){
if (iedom){
crossmain=document.getElementById? document.getElementById("motioncontainer") : document.all.motioncontainer;
if(typeof crossmain.style.maxWidth!=='undefined')
crossmain.style.maxWidth=maxwidth+'px';
menuwidth=crossmain.offsetWidth;
cross_scroll=document.getElementById? document.getElementById("motiongallery") : document.all.motiongallery;
// Start JoomGallery Team, offsetWidth changed to scrollWidth
actualwidth=document.getElementById? document.getElementById("trueContainer").scrollWidth : document.all['trueContainer'].scrollWidth;
// End JoomGallery Team 

//Edit JoomGallery Team (aha)
if (actualwidth < menuwidth) {
  //prevent the left align, if there are not enough pics for the container -> centering
  startpos=2;
}

if (startpos && startpos != 3) {
  cross_scroll.style.left=((menuwidth-actualwidth)/startpos)+'px';
}
if (startpos == 3){
  jgminiakt = document.getElementById("jg_mini_akt");
  jgminiwidth = jgminiakt.width;
  jgminileftpos = jgminiakt.offsetLeft;
  jgminimove = ((menuwidth / 2) - ( jgminiwidth / 2) - jgminileftpos ); 

 // prevent a hole at the right side if clicking the last pictures at the right side
 if ((actualwidth+(jgminimove-menuwidth)) < 0) {
  jgminimove =-actualwidth+menuwidth;
 }

  if (jgminileftpos > (menuwidth * 0.8) ) {
    cross_scroll.style.left = jgminimove + "px";
  } else {
    cross_scroll.style.left = "0px";
  }
}
// End Edit JoomGallery Team (aha)

crossmain.onmousemove=function(e){
motionengine(e);
}
crossmain.onmouseout=function(e){
stopmotion(e);
showhidediv("hidden");
}
}
loadedyes=1
if (endofgallerymsg!=""){
creatediv();
positiondiv();
}
// Edit JoomGallery TEAM (aha)
//if (document.body.filters){
 //onresize() 
//}
}
//window.onload=fillup;
// end Edit JoomGallery TEAM (aha)

if ( typeof window.addEventListener != "undefined" )
    window.addEventListener( "load", fillup, false );
else if ( typeof window.attachEvent != "undefined" )
    window.attachEvent( "onload", fillup );
else {
    if ( window.onload != null ) {
        var oldOnload = window.onload;
        window.onload = function ( e ) {
            oldOnload( e );
            fillup();
        };
    }
    else
        window.onload = fillup;
}

onresize=function(){
  if (typeof motioncontainer!=='undefined'&&motioncontainer.filters){
    motioncontainer.style.width="0";
    motioncontainer.style.width="";
    motioncontainer.style.width=Math.min(motioncontainer.offsetWidth, maxwidth)+'px';
  }
  if (typeof crossmain!=='undefined') {
    menuwidth=crossmain.offsetWidth;
    cross_scroll.style.left=startpos? (menuwidth-actualwidth)/startpos+'px' : 0;  
  }
}
