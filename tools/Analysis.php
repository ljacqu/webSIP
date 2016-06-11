<?php
//////////
// Language Options
$lanqs = array(

'Afrikaans (af)',
'العربية (ar)',
'Български (bg)',
'&#268;esky (cs)',
'Dansk (da)',
'Deutsch (de)',
'English (en)',
'Espa&#241;ol (es)',
'فارسی (fa)',
'Suomi (fi)',
'Fran&ccedil;ais (fr)',
'עברית (he)',
'Hrvatski (hr)',
'Magyar (hu)',
'&Iacute;slenska (is)',
'Italiano (it)',
'日本語 (ja)',
'Lietuvi&#371; (lt)',
'Nederlands (nl)',
'Norsk Bokm&aring;l (nob)',
'Polski (pl)',
'Portugu&ecirc;s (pt)',
'Русский (ru)',
'Sloven&#269;ina (sk)',
'Shqip (sq)',
'Српски (sr)',
'Svenska (sv)',
'T&uuml;rk&ccedil;e (tr)',
'Українська (uk)',
'ייִדיש (yi)',
'中文 (zh)',

);
//////////////////


header('Content-Type: text/html; charset=UTF-8', true);
header('Accept-Charset: utf-8', true);

if(isset($_COOKIE['lang'])){
	$cook_lang = $_COOKIE['lang'];
}
else{ $cook_lang = ''; }

if(isset($_POST['language']) && preg_match('/^[a-z]{2,4}$/', $_POST['language'])){
	setcookie('lang', $_POST['language']);
}

require('i18n_functions.php');
?>
<style type="text/css">
<!--
body {
	font-family: Verdana;
	font-size: 10pt;
}
textarea, input, select {
	border: 1px solid #000;
}
td {
	vertical-align: middle;
	font-size: 10pt;
}
-->
</style>
<?php
if(!isset($_GET['file'])){

if(isset($_POST['do'])){
	$file = $_POST['lang'].'_'.$_POST['title'].'.txt';
	$text = utf8_HtmlEntities($_POST['text']);
	if(md5($text) != $_POST['md5']){
//		die('<b>!</b> MD5 hash does not correspond.');
	}

	$open = fopen($file, 'w');
	fwrite($open, $_POST['url']);
	fwrite($open, "\r".$text);
	fclose($open);

	echo 'Done!';


}

if(isset($_POST['submit'])){
	$utf8_error = FALSE;
	$title = trim(utf8_HtmlEntities($_POST['title']));
	$text = trim(utf8_HtmlEntities($_POST['text']));


	if(preg_match('/^(sr|ru|uk|bg)$/', $_POST['language'])){
		include('cyr_lat.php');
		if($_POST['language'] == 'uk'){
			$title = cyr_to_lat(utf8_strtolower($title), true);
		}
		else{
			$title = cyr_to_lat(utf8_strtolower($title));
		}

		$text = str_replace('&#769;', '', $text);
		$text = preg_replace('/[a-z]+/i', '', $text);
	}
	elseif(preg_match('/^(zh|ja)$/', $_POST['language'])){
		$title = str_replace(array('&', '#', ';'), array('', '', '_'), $title);
	}
	elseif(preg_match('/^(he|yi)$/', $_POST['language'])){
		include('cyr_lat.php');
		$title = hebr_lat($title);
	}
	elseif(preg_match('/^(ar|fa)$/', $_POST['language'])){
		$text = preg_replace('/([a-z]{1,})/i', ' ', $text);
	}


	if(preg_match('/[^a-z0-9-_\.\(\) ]/i', $title)){
		die('<b>! </b>Title may not contain characters outside of a-z, 0-9, - _ . and spaces.');
	}

	for($i = 1632; $i <= 1785; $i++){
		$text = str_replace('&#'.$i.';', ' ', $text);
		if($i == 1641){
			$i = 1775;
		}
	}


	$URL = trim(utf8_HtmlEntities($_POST['url']));
	if($utf8_error){ die('<b>!</b> Data was not sent under UTF-8.'); }

	if($title == ''){
		die('<b>!</b> Blank title. :(');
	}
	if(!preg_match('/^[a-z]{2,4}$/', $_POST['language'])){
		die('<b>!</b> Please select a language.');
	}
	$filename = $_POST['language'].'_'.$title.'.txt';
	if(file_exists($filename)){
		die('<b>!</b> The file '.$filename.' already exists! Choose another title.');
	}

	$points = array('&#8221;', '&#8220;', '&#8212;', '&#8222;', '&#8230;', '&#128;', '&#171;', '&#187;', '%', '&#176;c', '&#176;');
	$text = str_replace($points, '', $text);
	// strip small paranthesis
	$text = preg_replace('/\(([a-z]|[0-9]|&#[0-9]{3,5};|\.| |,|:){3,100}\)/i', ' ', $text);
	$text = preg_replace('/\[[0-9]{1,3}\]/i', ' ', $text);
	$text = preg_replace('/\&#171;([a-z]|[0-9]|&#[0-9]{3,5};|\.| ){3,35}&#187;/i', ' ', $text);
	$text = preg_replace('/ [0-9]{1,}/', '', $text);
	$text = preg_replace('/([^0-9]);/', '\\1', $text);
	$text = preg_replace('/ [0-9]{1,}/', ' ', $text);
	$text = str_replace('&#8217;', "'", $text);

	$text = str_replace('-', ' ', $text);
	$text = str_replace(array("\r", "\n", "\t"), ' ', $text);
	$text = str_replace(array('.', ',', ': '), ' ', $text);
	$text = str_replace('  ', ' ', $text);
	$text = str_replace('  ', ' ', $text);

	$words = count(explode(' ', $text));
	if($words < 100){ die('<b>!</b> Need at least 100 words.'); }
	$strlen = utf8_StrLen($text);

	echo '<h1>'.htmlentities($title).'</h1>
	Words: '.$words.'<br />
	String length: '.$strlen.'<br />
	Avg. word length: '.($strlen/$words).'<br /><hr />';


	if($_POST['language'] == 'tr'){
		$text = str_replace('I', '&#305;', $text);
		$text = str_replace('&#304;', 'i', $text);
	}
	$text = utf8_StrToLower($text);

	echo $text;

	echo '<form action="'.$_SERVER['PHP_SELF'].'" method="post">
<input type="hidden" name="text" value="'.str_replace('"', '&quot;', $text).'" />
<input type="hidden" name="md5" value="'.md5($text).'" />
<input type="hidden" name="lang" value="'.$_POST['language'].'" />
<input type="hidden" name="url" value="'.$URL.'" />
<input type="hidden" name="title" value="'.$title.'" />
<input type="hidden" name="do" value="DB" />
<input type="submit" value="Write to DB" />
</form>';







}
else{
echo '<h1>Submit Text</h1>
<form action="'.$_SERVER['PHP_SELF'].'" method="post" accept-charset="utf-8">
<table>
<tr>
<td>Title</td><td><input type="text" name="title" style="width: 400px" /></td></tr>
<tr><td>URL</td><td><input type="text" name="url" style="width: 400px" /></td></tr>
<tr><td>Language</td><td><select name="language" style="width: 400px">';
foreach($lanqs as $langval){
	$id = preg_replace('/^.* \(([a-z]{2,4})\)$/', '\\1', $langval);
	if($id == ''){ die('Invalid lang ('.htmlentities($langval).')'); }
	echo "\r".'<option value="'.$id.'"';
	if($cook_lang == $id){
		echo ' selected="selcted"';
	}
	echo '>'.$langval.'</option>';
}
echo '</select></td></tr>
<tr><td>Text</td><td><textarea cols="100" rows="15" name="text"></textarea></td></tr>
<tr><td>Submit</td><td><input type="submit" name="submit" /></td></tr>
</table>
</form>';
}

}
