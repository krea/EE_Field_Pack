
/** ------------------------------------
/**  Live URL Title Function
/** -------------------------------------*/

function liveUrlTitle()
{
	var defaultTitle = '';
	var NewText = document.getElementById("title").value;
	
	if (defaultTitle != '')
	{
		if (NewText.substr(0, defaultTitle.length) == defaultTitle)
		{
			NewText = NewText.substr(defaultTitle.length)
		}	
	}
	
	NewText = NewText.toLowerCase();
	var separator = "-";
		
	// Foreign Character Attempt
	
	var NewTextTemp = '';
	for(var pos=0; pos<NewText.length; pos++)
	{
		var c = NewText.charCodeAt(pos);
		
		if (c >= 32 && c < 128)
		{
			NewTextTemp += NewText.charAt(pos);
		}
		else
		{
			if (c == '223') {NewTextTemp += 'b'; continue;}
		if (c == '200') {NewTextTemp += 'e'; continue;}
		if (c == '201') {NewTextTemp += 'e'; continue;}
		if (c == '202') {NewTextTemp += 'e'; continue;}
		if (c == '203') {NewTextTemp += 'e'; continue;}
		if (c == '204') {NewTextTemp += 'i'; continue;}
		if (c == '205') {NewTextTemp += 'i'; continue;}
		if (c == '206') {NewTextTemp += 'i'; continue;}
		if (c == '207') {NewTextTemp += 'i'; continue;}
		if (c == '208') {NewTextTemp += 'd'; continue;}
		if (c == '209') {NewTextTemp += 'n'; continue;}
		if (c == '210') {NewTextTemp += 'o'; continue;}
		if (c == '211') {NewTextTemp += 'o'; continue;}
		if (c == '212') {NewTextTemp += 'o'; continue;}
		if (c == '213') {NewTextTemp += 'o'; continue;}
		if (c == '214') {NewTextTemp += 'o'; continue;}
		if (c == '215') {NewTextTemp += 'x'; continue;}
		if (c == '217') {NewTextTemp += 'u'; continue;}
		if (c == '218') {NewTextTemp += 'u'; continue;}
		if (c == '219') {NewTextTemp += 'u'; continue;}
		if (c == '220') {NewTextTemp += 'u'; continue;}
		if (c == '221') {NewTextTemp += 'y'; continue;}
		if (c == '222') {NewTextTemp += 'b'; continue;}
		if (c == '224') {NewTextTemp += 'a'; continue;}
		if (c == '225') {NewTextTemp += 'a'; continue;}
		if (c == '226') {NewTextTemp += 'a'; continue;}
		if (c == '229') {NewTextTemp += 'a'; continue;}
		if (c == '227') {NewTextTemp += 'a'; continue;}
		if (c == '230') {NewTextTemp += 'ae'; continue;}
		if (c == '228') {NewTextTemp += 'a'; continue;}
		if (c == '231') {NewTextTemp += 'c'; continue;}
		if (c == '232') {NewTextTemp += 'e'; continue;}
		if (c == '233') {NewTextTemp += 'e'; continue;}
		if (c == '234') {NewTextTemp += 'e'; continue;}
		if (c == '235') {NewTextTemp += 'e'; continue;}
		if (c == '236') {NewTextTemp += 'i'; continue;}
		if (c == '237') {NewTextTemp += 'i'; continue;}
		if (c == '238') {NewTextTemp += 'i'; continue;}
		if (c == '239') {NewTextTemp += 'i'; continue;}
		if (c == '241') {NewTextTemp += 'n'; continue;}
		if (c == '242') {NewTextTemp += 'o'; continue;}
		if (c == '243') {NewTextTemp += 'o'; continue;}
		if (c == '244') {NewTextTemp += 'o'; continue;}
		if (c == '245') {NewTextTemp += 'o'; continue;}
		if (c == '246') {NewTextTemp += 'o'; continue;}
		if (c == '249') {NewTextTemp += 'u'; continue;}
		if (c == '250') {NewTextTemp += 'u'; continue;}
		if (c == '251') {NewTextTemp += 'u'; continue;}
		if (c == '252') {NewTextTemp += 'u'; continue;}
		if (c == '253') {NewTextTemp += 'y'; continue;}
		if (c == '254') {NewTextTemp += 'y'; continue;}
		if (c == '255') {NewTextTemp += 'y'; continue;}
		if (c == '257') {NewTextTemp += 'a'; continue;}
		if (c == '269') {NewTextTemp += 'c'; continue;}
		if (c == '275') {NewTextTemp += 'e'; continue;}
		if (c == '282') {NewTextTemp += 'e'; continue;}
		if (c == '283') {NewTextTemp += 'e'; continue;}
		if (c == '291') {NewTextTemp += 'g'; continue;}
		if (c == '299') {NewTextTemp += 'i'; continue;}
		if (c == '311') {NewTextTemp += 'k'; continue;}
		if (c == '316') {NewTextTemp += 'l'; continue;}
		if (c == '326') {NewTextTemp += 'n'; continue;}
		if (c == '353') {NewTextTemp += 's'; continue;}
		if (c == '363') {NewTextTemp += 'u'; continue;}
		if (c == '382') {NewTextTemp += 'z'; continue;}
		if (c == '256') {NewTextTemp += 'a'; continue;}
		if (c == '268') {NewTextTemp += 'c'; continue;}
		if (c == '274') {NewTextTemp += 'e'; continue;}
		if (c == '290') {NewTextTemp += 'g'; continue;}
		if (c == '298') {NewTextTemp += 'i'; continue;}
		if (c == '310') {NewTextTemp += 'k'; continue;}
		if (c == '315') {NewTextTemp += 'l'; continue;}
		if (c == '325') {NewTextTemp += 'n'; continue;}
		if (c == '352') {NewTextTemp += 's'; continue;}
		if (c == '362') {NewTextTemp += 'u'; continue;}
		if (c == '381') {NewTextTemp += 'z'; continue;}
		if (c == '270') {NewTextTemp += 'd'; continue;}
		if (c == '271') {NewTextTemp += 'd'; continue;}
		if (c == '356') {NewTextTemp += 't'; continue;}
		if (c == '357') {NewTextTemp += 't'; continue;}
		if (c == '327') {NewTextTemp += 'n'; continue;}
		if (c == '328') {NewTextTemp += 'n'; continue;}
		if (c == '317') {NewTextTemp += 'l'; continue;}
		if (c == '318') {NewTextTemp += 'l'; continue;}
		if (c == '344') {NewTextTemp += 'r'; continue;}
		if (c == '345') {NewTextTemp += 'r'; continue;}
		if (c == '340') {NewTextTemp += 'r'; continue;}
		if (c == '341') {NewTextTemp += 'r'; continue;}
		if (c == '346') {NewTextTemp += 's'; continue;}
		if (c == '347') {NewTextTemp += 's'; continue;}
		if (c == '313') {NewTextTemp += 'l'; continue;}
		if (c == '314') {NewTextTemp += 'l'; continue;}
		
		}
	}

	var multiReg = new RegExp(separator + '{2,}', 'g');
	
	NewText = NewTextTemp;
	
	NewText = NewText.replace('/<(.*?)>/g', '');
	NewText = NewText.replace(/\s+/g, separator);
	NewText = NewText.replace(/\//g, separator);
	NewText = NewText.replace(/[^a-z0-9\-\._]/g,'');
	NewText = NewText.replace(/\+/g, separator);
	NewText = NewText.replace(multiReg, separator);
	NewText = NewText.replace(/-$/g,'');
	NewText = NewText.replace(/_$/g,'');
	NewText = NewText.replace(/^_/g,'');
	NewText = NewText.replace(/^-/g,'');
	NewText = NewText.replace(/\.+$/g,'');
	
	if (document.getElementById("url_title"))
	{
		document.getElementById("url_title").value = "" + NewText;			
	}
	else
	{
		document.forms['entryform'].elements['url_title'].value = "" + NewText; 
	}		
}
