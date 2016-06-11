<?php
header('Content-Type: text/html; charset=UTF-8', true);


/*	

	Diese Datei zeigt den Inhalt anderer Dateien an
	und ist kein eigentlicher Bestandteil des
	Sprachenerkennungsprogramms

*/

$smart = 1;
if(isset($_GET['smart'])){
	if($_GET['smart']){
		setcookie('smart', 'tak', time()+99999);
		$smart = 0;
	}
	else{
		setcookie('smart', 'nie', time()-9);
	}
}
elseif(isset($_COOKIE['smart'])){
	$smart = 0;
}

if(!isset($_GET['file']) || !isset($_GET['plain'])){
?>
<style type="text/css">
body { font-family: Verdana; font-size: 10pt; }
li { margin-bottom: 2px; }
acronym:hover { cursor: help; }
</style>
<body>
<?php
}
error_reporting(E_ALL);







if(isset($_GET['file'])){
	$file = $_GET['file'];
	if(!preg_match('/^(\.\/[a-z]{3,7}\/){0,1}[a-z_\.0-9]{1,35}\.[a-z]{2,4}$/', $file)){
		die('Unerlaubte Zeichen im Dateinamen');
	}
	if(!file_exists($file)){
		die('Datei existiert nicht.');
	}

	$dir = preg_replace('/^\.\/([a-z]{2,5})\/(.*)$/', '\\1', $file);
	if($dir == '' || $dir == $file){
		$dir = 'cwd';
		$dir_name = 'm Hauptordner';
	}
	else{ $dir_name = ' '.$dir; }

	if(isset($_GET['plain'])){
		if(preg_match('/.*\.(png|jpg|gif)$/', $file)){
			header('location:'.$file);
			exit;
		}
		header('Content-Type: text/plain; charset=UTF-8', true);
		echo file_get_contents($file);
		exit;
	}

	echo '<img src="./icons2/back.png" /> <a href="?dir='.$dir.'">Zur&uuml;ck zu'.$dir_name.'</a>
<br /><img src="./icons2/txt.png" /> <a href="?plain&amp;file='.htmlentities($file).'">Als Textdatei anzeigen</a>
<br />';
if(!$smart){
	echo '<img src="./icons2/ent_off.png" /> <a href="?file='.htmlentities($file).'&amp;smart=0">HTML Entities anzeigen</a> (anstatt &auml; -&gt; <acronym title="&auml;">&amp;auml;</acronym> zeigen)';
}
else{
	echo '<img src="./icons2/ent.png" /> <a href="?file='.htmlentities($file).'&amp;smart=1">HTML Entities ersetzen</a> (anstatt <acronym title="&auml;">&amp;auml;</acronym> -&gt; &auml; zeigen)';
}
echo '<hr />';


	if($file == 'source.php' && !isset($_GET['pie'])){
		die('<br /><br /><b>Source.php</b> ist diese Datei. Sie zeigt den Code anderer Dateien an, 
ist aber kein Bestandteil dieses Programms.
<br />&raquo; <a href="?file=source.php&amp;pie" title="Kein Problem">Trotzdem Code anschauen</a>');
	}

	if(preg_match('/.*\.(png|jpg|gif)$/', $file)){
		echo '<img src="'.$file.'" alt="Image" style="margin: 50px" />';
	}
	else{
		function no_amp($match){
			global $smart;
			if($smart){
				return '<acronym title="'.str_replace('&amp;', '&', $match[0]).'">'.$match[0].'</acronym>';
			}
			else{
				return str_replace('&amp;', '&', $match[0]);
			}
		}
		echo preg_replace_callback('/(&amp;(#[0-9]{2,5}|[a-z]{2,7});)/i', 'no_amp', show_source($file, true));
	}
	echo '<hr /><img src="./icons2/back.png" style="border: 0" /> <a href="?dir='.$dir.'">Zur&uuml;ck zu'.$dir_name.'</a>';
exit;
}






$cwd = getcwd();
if(!isset($_GET['dir']) || $_GET['dir'] == 'cwd' || 
	strpos($_GET['dir'], '/') !== false || strpos($_GET['dir'], '.') !== false){
	$dir = $cwd;
}
else{
	if(is_dir('./'.$_GET['dir'].'/')){
		$dir = './'.$_GET['dir'].'/';
	}
	else{
		die('Ung&uuml;ltiger Ordnername');
	}
}

if($dir != $cwd){
	echo '<h1>Ordner: '.$dir.'</h1>';

echo '<ul>
	<li style="list-style-image: url(\'./icons2/back.png\'); border-bottom: 1px solid #ccc; margin-bottom: 5px"><a href="?dir=cwd">Zur&uuml;ck</a></li>';


}
else{
	echo '<h1>Hauptordner</h1><ul>';
}


if($dh = opendir($dir)){
	while(($file = readdir($dh)) !== false){ 
		if($file != '.' && $file != '..' && $file != 'icons2'){
			if(is_dir($file)){
				echo '<li style="list-style-image: url(\'./icons2/go.png\')">Ordner: <a href="?dir='.htmlentities($file).'">'.htmlentities($file).'</a></li>';
			}
			else{
				$ext = preg_replace('/.*\.([a-z]{2,4})$/', '\\1', $file); // regex = black magic
				if($ext == ''){ $ext = 'html'; }

				if($dir != $cwd){ $full_file = $dir.$file; }
				else{ $full_file = $file; }
				echo '<li style="list-style-image: url(\'./icons2/'.$ext.'.png\')"><a href="?file='.htmlentities($full_file).'">'.htmlentities($file).'</a> ('.round(filesize($full_file)/1024).' KB)</li>';
			}
		}
	}
	closedir($dh);
}
echo '</ul>';
?>
</body>