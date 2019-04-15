var request = false;
try
{
    request = new XMLHttpRequest();
}
catch (trymicrosoft)
{
    try
    {
        request = new ActiveXObject("Msxml2.XMLHTTP");
    }
    catch (othermicrosoft)
    {
        try
        {
            request = new ActiveXObject("Microsoft.XMLHTTP");
        }
        catch (failed)
        {
            request = false;
        }
    }
}
if (!request) alert ("Error initializing XMLHttpRequest!");

function connectByURL (url)
{
    request.open ("GET", url, true);
    request.onreadystatechange = getResponse;
    request.send(null);
}

function connectByURLpost (url, poststr)
{
    if (! url || ! poststr) return;
    request.open ("POST", url, true);
    request.onreadystatechange = getResponse;
    request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    request.setRequestHeader("Content-length", poststr.length);
    request.setRequestHeader("Connection", "close");
    request.send(poststr); //посылаем данные
}
