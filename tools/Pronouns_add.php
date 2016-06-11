<?php
header('Content-Type: text/html; charset=UTF-8', true);
header('Accept-Charset: UTF-8', true);

if(isset($_POST['lang']) || isset($_GET['lang'])){
	if(!isset($_POST['lang'])){
		$_POST['lang'] = $_GET['lang'];
	}

	if(!preg_match('/^[a-z]{2,4}$/', $_POST['lang'])){
		die('Invalid lang param');
	}
	if(!file_exists($_POST['lang'].'_filtered_words.dat')){
		if(file_exists('./'.$_POST['lang'].'/'.$_POST['lang'].'_filtered_words.dat')){
			die('File for '.$_POST['lang'].' is in archive.');
		}
		die('File '.$_POST['lang'].'_filtered_words.dat could not be found');
	}

	if(isset($_POST['write'])){
		require('i18n_functions.php');
		if(!isset($_POST['data']) || count($_POST['data']) <= 0){
			die('No data found :(');
		}

		require($_POST['lang'].'_filtered_words.dat');

		foreach($_POST['data'] as $key => $data){
			if(trim($data) == ''){ unset($_POST['data'][$key]); continue; }
			$data = trim(utf8_htmlentities($data));

			$words[$data] = 'p';

			if($utf8_error){ die('Send data in UTF-8'); }
		}

		$open = fopen($_POST['lang'].'_filtered_words.dat', 'w');
		fwrite($open, '<'.'?php $words = '.var_export($words, true).'; ?'.'>');
		fclose($open);

		echo 'Words ('.count($_POST['data']).') added.
<br />&raquo; <a href="Words.php?file='.$_POST['lang'].'_words.dat&1">Export</a>
<br />&raquo; <a href="">Main</a>';
	exit;
	}


	echo '<form action="'.$_SERVER['PHP_SELF'].'" method="post">
<input type="hidden" name="write" value="pancakes" />
<table id="tblSample">';
for($i = 0; $i < 10; $i++){
	echo '<tr><td><input type="text" name="data[]" maxlength="20" /> <input type="text" name="data[]" maxlength="20" /> <input type="text" name="data[]" maxlength="20" /></td></tr>';
}
echo '</table>
<input type="hidden" name="lang" value="'.$_POST['lang'].'">
<input type="submit" value="Write :3" />
</form>';

exit;
}

$dir = getcwd();

// Open a known directory, and proceed to read its contents
if (is_dir($dir)) {
    if ($dh = opendir($dir)) {
        while (($file = readdir($dh)) !== false) {
            if(preg_match('/^[a-z]{2,4}_filtered_words\.dat$/', $file)){
		echo '<li><a href="?lang='.preg_replace('/^([a-z]{2,4})_.*$/', '\\1', $file).'">'.$file.'</a></li>';
	}
        }
        closedir($dh);
    }
}