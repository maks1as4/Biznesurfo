var ie = document.all
var dom = document.getElementById
var ns4 = document.layers

var fixedX = -1;
var fixedY = -1;
var startAt = 1;
var crossobj, monthSelected, yearSelected, dateSelected, omonthSelected, oyearSelected, odateSelected, monthConstructed, yearConstructed, ctlToPlaceValue, ctlNow, dateFormat, nStartingYear
var bPageLoaded=false
var today = new Date()
var dateNow  = today.getDate()
var monthNow = today.getMonth()
var yearNow  = today.getYear()
var bShow = false;

// В IE скрываем <select> и <applet>
function hideElement( elmID, overDiv )
{
    if (ie)
    {
        for( i = 0; i < document.all.tags( elmID ).length; i++ )
        {
            obj = document.all.tags( elmID )[i];
            if( !obj || !obj.offsetParent )
            {
                continue;
            }

            // Find the element's offsetTop and offsetLeft relative to the BODY tag.
            objLeft   = obj.offsetLeft;
            objTop    = obj.offsetTop;
            objParent = obj.offsetParent;

            while( objParent.tagName.toUpperCase() != "BODY" )
            {
                objLeft  += objParent.offsetLeft;
                objTop   += objParent.offsetTop;
                objParent = objParent.offsetParent;
            }

            objHeight = obj.offsetHeight;
            objWidth = obj.offsetWidth;

            if(( overDiv.offsetLeft + overDiv.offsetWidth ) <= objLeft );
            else if(( overDiv.offsetTop + overDiv.offsetHeight ) <= objTop );
            else if( overDiv.offsetTop >= ( objTop + objHeight ));
            else if( overDiv.offsetLeft >= ( objLeft + objWidth ));
            else
            {
                obj.style.visibility = "hidden";
            }
        }
    }
}

// В IE открываем <select> и <applet>
function showElement( elmID )
{
    if (ie)
    {
        for( i = 0; i < document.all.tags( elmID ).length; i++ )
        {
            obj = document.all.tags( elmID )[i];

            if( !obj || !obj.offsetParent )
            {
                continue;
            }

            obj.style.visibility = "";
        }
    }
}

if (dom)
{
    document.write ("<div onclick='bShow=true' id='calendar' class='div-style'>\n");
    document.write ("<table class='table-style'>\n");
    document.write ("<tr class='title-background-style' >\n");
    document.write ("   <td>\n");
    document.write ("   <table width='100%'>\n");
    document.write ("       <tr>\n");
    document.write ("           <td class='title-style'>\n");
    document.write ("               <span id='caption'></span>\n");
    document.write ("           </td>\n");
    document.write ("       </tr>\n");
    document.write ("       </table>\n");
    document.write ("   </td>\n");
    document.write ("</tr>\n");
    document.write ("<tr>\n");
    document.write ("   <td class='body-style'>\n");
    document.write ("       <span id='content'></span>\n");
    document.write ("   </td>\n");
    document.write ("</tr>");
    document.write ("</table>")
    document.write ("</div>");
}

function hideCalendar() {
    crossobj.visibility="hidden"
    showElement( 'SELECT' );
    showElement( 'APPLET' );
}

function padZero(num) {
    return (num < 10)? '0' + num : num ;
}

function closeCalendar() {
    var sTmp

    hideCalendar();

    ctlToPlaceValue.value = dateSelected
}

function incMonth () {
    monthSelected++
    if (monthSelected>11) {
        monthSelected=0
        yearSelected++
    }
    constructCalendar()
}

function decMonth () {
    monthSelected--
    if (monthSelected<0) {
        monthSelected=11
        yearSelected--
    }
    constructCalendar()
}

// Календарь
function constructCalendar () {
    var dateMessage
    var startDate = new Date (yearSelected,monthSelected,1)
    var endDate = new Date (yearSelected,monthSelected+1,1);
    endDate = new Date (endDate - (24*60*60*1000));
    numDaysInMonth = endDate.getDate()

    datePointer = 0
    dayPointer = startDate.getDay() - startAt

    if (dayPointer < 0)
    {
        dayPointer = 6
    }

    sHTML = "<table width='100%' border='0' cellpadding='1' cellspacing='1' class='body-style'><tr>"

    for (i=0; i<7; i++) {
        sHTML += "<td align='center'><B>"+ b2b5_dayName[i]+"</B></td>"
    }
    sHTML +="</tr><tr>"

    for ( var i=1; i<=dayPointer;i++ )
    {
        sHTML += "<td>&nbsp;</td>"
    }

    for ( datePointer=1; datePointer<=numDaysInMonth; datePointer++ )
    {
        dayPointer++;
        sHTML += "<td width='15' align='center'>"

        var sStyle="normal-day-style"; //regular day

        if ((datePointer==dateNow)&&(monthSelected==monthNow)&&(yearSelected==yearNow)) //today
        { sStyle = "current-day-style"; }

        if ((datePointer==odateSelected) && (monthSelected==omonthSelected) && (yearSelected==oyearSelected))
        { sStyle += " selected-day-style"; }

        sHint = ""

        var regexp= /\"/g
        sHint=sHint.replace(regexp,"&quot;")

        sHTML += "<a class='"+sStyle+"' title=\"" + sHint + "\" href='javascript:dateSelected="+datePointer+";closeCalendar();'>" + datePointer + "</a>"
        if ((dayPointer+startAt) % 7 == startAt) {
            sHTML += "</tr><tr>"
        }
    }

    document.getElementById("content").innerHTML   = sHTML
}

function popUpCalendar(ctl, ctl2, selMonth, selYear) {
    var leftpos=0
    var toppos=0

    DocumentRegisterEvents();
    if (bPageLoaded)
    {
        if ( crossobj.visibility == "hidden" ) {
            ctlToPlaceValue = ctl2

            dateSelected  = dateNow
            monthSelected = selMonth
            yearSelected  = selYear    

            odateSelected  = dateNow
            omonthSelected = selMonth
            oyearSelected  = selYear

            aTag = ctl
            do {
                aTag = aTag.offsetParent;
                leftpos += aTag.offsetLeft;
                toppos += aTag.offsetTop;
            } while(aTag.tagName!="BODY");

            crossobj.left = fixedX==-1 ? ctl.offsetLeft + leftpos + 'px':   fixedX
            crossobj.top = fixedY==-1 ? ctl.offsetTop + toppos + ctl.offsetHeight + 2 + 'px':   fixedY
            constructCalendar (1, monthSelected, yearSelected);
            crossobj.visibility=(dom||ie)? "visible" : "show"
            
            hideElement( 'SELECT', document.getElementById("calendar") );
            hideElement( 'APPLET', document.getElementById("calendar") );           

            bShow = true;
        }
    }
    else
    {
        DateSelectorInit()
        popUpCalendar(ctl, ctl2, selMonth, selYear)
    }
}

function DateSelectorInit() {
    if (!ns4)
    {
        if (!ie) { yearNow += 1900  }

        crossobj=(dom)?document.getElementById("calendar").style : ie? document.all.calendar : document.calendar
        hideCalendar()

        monthConstructed=false;
        yearConstructed=false;

        bPageLoaded=true
    }
}

function DocumentRegisterEvents()
{
        if (document.addEventListener) //IE
        {
                document.addEventListener("keypress", _hideCalender_Trap1, false);
                document.addEventListener("keydown", _hideCalender_Trap1, false);
        }
        else if (document.attachEvent) //W3C (Gecko...)
        {
                document.attachEvent("onkeypress", _hideCalender_Trap1);
                document.attachEvent("onkeydown", _hideCalender_Trap1);
        }
        else document.onkeypress = document.onkeydown = _hideCalender_Trap1;

        function _hideCalender_Trap1(e)
        {
                if (!e) e = window.event || null;
                if (!e) return;
                var n = e.keyCode?e.keyCode:e.charCode;
                if (n == 27)
                {
                        hideCalendar();
                }               
        }

        document.onclick = function hideCalender_Trap2()
        {
                if (!bShow)
                {
                        hideCalendar();
                }
                bShow = false;
        }
}
