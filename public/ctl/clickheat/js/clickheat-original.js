/**
ClickHeat : Suivi et analyse des clics / Tracking and clicks analysis

@author Yvan Taviaud - LabsMedia - www.labsmedia.com/clickheat/
@since 27/10/2006
@update 01/03/2007 - Yvan Taviaud : correctif Firefox (Károly Marton)
@update 23/03/2007 - Yvan Taviaud : protection de 2 secondes entre chaque clic, et X clics maximum par page
@update 18/05/2007 - Yvan Taviaud : suppression de clickHeatPage, ajout de clickHeatGroup et clickHeatSite
@update 27/08/2007 - Yvan Taviaud : changement du système de débug
@update 28/09/2007 - Yvan Taviaud : ajout de quelques messages de débug
@update 16/03/2008 - Yvan Taviaud : utilisation des Listeners - ajout d'un délai pour enregistrer le clic correctement - correctif JSLint
@update 05/07/2010 - Yvan Taviaud : ajout de Chrome, ajout du test non-Ajax pour libérer le clic plus rapidement

Tested under :
Windows 2000 - IE 6.0
Linux - Firefox 2.0.0.1, Konqueror 3.5.5, IE 7
*/

/** Main variables */
var clickHeatGroup = '';
var clickHeatSite = '';
var clickHeatServer = '';
var clickHeatLastIframe = -1;
var clickHeatTime = 0;
var clickHeatQuota = -1;
var clickHeatBrowser = '';
var clickHeatDocument = '';
var clickHeatWait = 500;
var clickHeatLocalWait = 0;
var clickHeatDebug = (window.location.href.search(/debugclickheat/) != -1);

/**
* Shows a debug string
**/
function showClickHeatDebug(str)
{
	if (clickHeatDebug == true)
	{
		document.getElementById('clickHeatDebuggerSpan').innerHTML = str;
		document.getElementById('clickHeatDebuggerDiv').style.display = 'block';
	}
}

/** Main function */
function catchClickHeat(e)
{
	/** Use a try{} to avoid showing errors to users */
	try
	{
		showClickHeatDebug('Gathering click data...');
		if (clickHeatQuota == 0)
		{
			showClickHeatDebug('Click not logged: quota reached');
			return true;
		}
		if (clickHeatGroup == '')
		{
			showClickHeatDebug('Click not logged: group name empty (clickHeatGroup)');
			return true;
		}
		/** Look for the real event */
		if (e == undefined)
		{
			e = window.event;
			c = e.button;
			element = e.srcElement;
		}
		else
		{
			c = e.which;
			element = null;
		}
		if (c == 0)
		{
			showClickHeatDebug('Click not logged: no button pressed');
			return true;
		}
		/** Filter for same iframe (focus on iframe => popup ad => close ad => new focus on same iframe) */
		if (element != null && element.tagName.toLowerCase() == 'iframe')
		{
			if (element.sourceIndex == clickHeatLastIframe)
			{
				showClickHeatDebug('Click not logged: same iframe (a click on iframe opens a popup and popup is closed => iframe gets the focus again)');
				return true;
			}
			clickHeatLastIframe = element.sourceIndex;
		}
		else
		{
			clickHeatLastIframe = -1;
		}
		var x = e.clientX;
		var y = e.clientY;
		var w = clickHeatDocument.clientWidth != undefined ? clickHeatDocument.clientWidth : window.innerWidth;
		var h = clickHeatDocument.clientHeight != undefined ? clickHeatDocument.clientHeight : window.innerHeight;
		var scrollx = window.pageXOffset == undefined ? clickHeatDocument.scrollLeft : window.pageXOffset;
		var scrolly = window.pageYOffset == undefined ? clickHeatDocument.scrollTop : window.pageYOffset;
		/** Is the click in the viewing area? Not on scrollbars. The problem still exists for FF on the horizontal scrollbar */
		if (x > w || y > h)
		{
			showClickHeatDebug('Click not logged: out of document (should be a click on scrollbars)');
			return true;
		}
		/** Check if last click was at least 1 second ago */
		clickTime = new Date();
		if (clickTime.getTime() - clickHeatTime < 1000)
		{
			showClickHeatDebug('Click not logged: at least 1 second between clicks');
			return true;
		}
		clickHeatTime = clickTime.getTime();
		if (clickHeatQuota > 0)
		{
			clickHeatQuota = clickHeatQuota - 1;
		}
		params = 's=' + clickHeatSite + '&g=' + clickHeatGroup + '&x=' + (x + scrollx) + '&y=' + (y + scrolly) + '&w=' + w + '&b=' + clickHeatBrowser + '&c=' + c + '&random=' + Date();
		showClickHeatDebug('Ready to send click data...');
		/** Local request? Try an ajax call */
		var sent = false;
		if (clickHeatServer.substring(0, 4) != 'http')
		{
			var xmlhttp = false;
			try
			{
				xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
			}
			catch (er)
			{
				try
				{
					xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
				}
				catch (oc)
				{
					xmlhttp = null;
				}
			}
			if (!xmlhttp && typeof XMLHttpRequest != undefined)
			{
				xmlhttp = new XMLHttpRequest();
			}
			if (xmlhttp)
			{
				if (clickHeatDebug == true)
				{
					xmlhttp.onreadystatechange = function()
					{
						if (xmlhttp.readyState == 4)
						{
							if (xmlhttp.status == 200)
							{
								showClickHeatDebug('Click recorded at ' + clickHeatServer + ' with the following parameters:<br />x = ' + (x + scrollx) + ' (' + x + 'px from left + ' + scrollx + 'px of horizontal scrolling)<br />y = ' + (y + scrolly) + ' (' + y + 'px from top + ' + scrolly + 'px of vertical scrolling)<br />width = ' + w + '<br />browser = ' + clickHeatBrowser + '<br />click = ' + c + '<br />site = ' + clickHeatSite + '<br />group = ' + clickHeatGroup + '<br /><br />Server answer: ' + xmlhttp.responseText);
							}
							else if (xmlhttp.status == 404)
							{
								showClickHeatDebug('click.php was not found at: ' + (clickHeatServer != '' ? clickHeatServer : '/clickheat/click.php') + ' please set clickHeatServer value');
							}
							else
							{
								showClickHeatDebug('click.php returned a status code ' + xmlhttp.status + ' with the following error: ' + xmlhttp.responseText);
							}
							/** Stop waiting */
							clickHeatLocalWait = 0;
						}
					};
				}
				xmlhttp.open('GET', clickHeatServer + '?' + params, true);
				xmlhttp.setRequestHeader('Connection', 'close');
				xmlhttp.send(null);
				sent = true;
			}
		}
		if (sent == false)
		{
			/** This test is needed, as it includes the call to click.php in the iframe */
			if (clickHeatDebug == true)
			{
				showClickHeatDebug('Click recorded at ' + clickHeatServer + ' with the following parameters:<br />x = ' + (x + scrollx) + ' (' + x + 'px from left + ' + scrollx + 'px of horizontal scrolling)<br />y = ' + (y + scrolly) + ' (' + y + 'px from top + ' + scrolly + 'px of vertical scrolling)<br />width = ' + w + '<br />browser = ' + clickHeatBrowser + '<br />click = ' + c + '<br />site = ' + clickHeatSite + '<br />group = ' + clickHeatGroup + '<br /><br />Server answer:<br />' + '<iframe src="' + clickHeatServer + '?' + params + '" width="700" height="60"></iframe>');
			}
			else
			{
				var clickHeatImg = new Image();
				clickHeatImg.src = clickHeatServer + '?' + params;
				//			clickHeatImg.onload = function() { clickHeatLocalWait = 0; }
			}
		}
		/** Little waiting cycle: default is to wait until Ajax sent or until the end of the time if no Ajax is available */
		var now = new Date();
		clickHeatLocalWait = now.getTime() + clickHeatWait;
		while (clickHeatLocalWait > now.getTime())
		{
			now = new Date();
		}
	}
	catch(err)
	{
		showClickHeatDebug('An error occurred while processing click (Javascript error): ' + err.message);
	}
	return true;
}

function initClickHeat()
{
	/** Debug Window */
	if (clickHeatDebug == true)
	{
		document.write('<div id="clickHeatDebuggerDiv" style="padding:5px; display:none; position:absolute; top:10px; left:10px; border:1px solid #888; background-color:#eee; z-index:99;"><strong>ClickHeat debug: <a href="#" onmouseover="document.getElementById(\'clickHeatDebuggerDiv\').style.display = \'none\'; return false">Rollover to close</a></strong><br /><br /><span id="clickHeatDebuggerSpan"></span></div>');
	}

	if (clickHeatGroup == '' || clickHeatServer == '')
	{
		showClickHeatDebug('ClickHeat NOT initialised: either clickHeatGroup or clickHeatServer is empty');
		return false;
	}

	/** If current website has the same domain as the script, we remove the domain so that the call is made using Ajax */
	domain = window.location.href.match(/http:\/\/[^/]+\//);
	if (domain != null && clickHeatServer.substring(0, domain[0].length) == domain[0])
	{
		clickHeatServer = clickHeatServer.substring(domain[0].length - 1, clickHeatServer.length);
	}
	/** Add onmousedown event using listeners */
	if (document.addEventListener)
	{
		document.addEventListener('mousedown', catchClickHeat, false);
	}
	else if (document.attachEvent)
	{
		document.attachEvent('onmousedown', catchClickHeat);
	}
	/** Add onfocus event on iframes (mostly ads) - Does NOT work with Gecko-powered browsers, because onfocus doesn't exist on iframes */
	iFrames = document.getElementsByTagName('iframe');
	for (var i = 0; i < iFrames.length; i++)
	{
		if (document.addEventListener)
		{
			iFrames[i].addEventListener('focus', catchClickHeat, false);
		}
		else if (document.attachEvent)
		{
			iFrames[i].attachEvent('onfocus', catchClickHeat);
		}
	}
	/** Preparing main variables */
	clickHeatDocument = (document.documentElement != undefined && document.documentElement.clientHeight != 0) ? document.documentElement : document.body;
	/** Also the User-Agent is not the best value to use, it's the only one that gives the real browser */
	var b = navigator.userAgent != undefined ? navigator.userAgent.toLowerCase().replace(/-/g, '') : '';
	/** Always test Chrome before Safari */
	var browsers = ['chrome', 'firefox', 'safari', 'msie', 'opera'];
	clickHeatBrowser = 'unknown';
	for (var i = 0; i < browsers.length; i++)
	{
		if (b.indexOf(browsers[i]) != -1)
		{
			clickHeatBrowser = browsers[i];
			break
		}
	}
	showClickHeatDebug('ClickHeat initialised with:<br />site = ' + clickHeatSite + '<br />group = ' + clickHeatGroup + '<br />server = ' + clickHeatServer + '<br />quota = ' + (clickHeatQuota == -1 ? 'unlimited' : clickHeatQuota) + '<br /><br />browser = ' + clickHeatBrowser);
}