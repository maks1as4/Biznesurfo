var d = document;
var winIE = (navigator.userAgent.indexOf("Opera")==-1 && (d.getElementById && d.documentElement.behaviorUrns)) ? true : false;
function bodySize(){
d.body.clientWidth = (d.body.clientWidth<990)?"990px":"100%";
}
function init(){
if(winIE) { bodySize(); }
}
onload = init;
if(winIE) { onresize = bodySize; }