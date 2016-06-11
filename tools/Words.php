<?php
if(!isset($_GET['file'])){
	$dir = getcwd();
	$files = array();

	// Open a known directory, and proceed to read its contents
	if(is_dir($dir)){
		if($dh = opendir($dir)){
			while(($file = readdir($dh)) !== false){
				if(preg_match('/^[a-z]{2,4}_(adv_)?words\.dat$/', $file)){
					$files[] = $file;
				}
			}
			closedir($dh);
		}
		else{ die('OPENDIR fail :('); }
	}else{ die('$dir ('.htmlentities($dir).') is not a directory'); }

	echo '<h1>SELECT FILE</h1><ul>';
	$no_adv = '';
	foreach($files as $file){
		if(strpos($file, '_adv_') !== false){
			echo '<li><a href="?file='.htmlentities($file).'">'.$file.'</a> [<a href="?file='.htmlentities($file).'&amp;1">Export</a>]</li>';
		}
		else{
			$no_adv .= '<li><a href="?file='.htmlentities($file).'">'.$file.'</a> [<a href="?file='.htmlentities($file).'&amp;1">Export</a>]</li>';
		}
	}
	echo '<hr />'.$no_adv;
	exit;
}

$file = $_GET['file'];
$sp_file = '';
if(isset($_GET['1'])){
	$file = preg_replace('/^([a-z]{2,4})_(adv_)?words\.dat$/', '\\1_\\2filtered_words.dat', $file);
	if(!preg_match('/^[a-z]{2,4}_(adv_)?filtered_words.dat$/', $file) || !file_exists($file)){
		die('File not found or invalid. Please evaluate words before exporting.');
	}
}
elseif(isset($_GET['delete'])){
	$sp_file = preg_replace('/^([a-z]{2,4})_(adv_)?words\.dat$/', '\\1_\\2filtered_words.dat', $file);
	if(!file_exists($sp_file) || !preg_match('/^[a-z]{2,4}_(adv_)?filtered_words\.dat$/', $sp_file)){
		die('Invalid file param. (DEL mode)');
	}
}
else{
	if(!preg_match('/^[a-z]{2,4}_(adv_)?words\.dat$/', $file)){
		die('Invalid file');
	}
}

require('i18n_functions.php');
if(trim($sp_file) != ''){
	require($sp_file);
}
else{
	require($file);
}

if(!isset($_GET['1']) && strpos($file, '_adv_') !== false){
	$words = $words_sp;
}

natsort($words);
$words = array_reverse($words);

$check = 1;
if(!isset($_GET['delete']) && !isset($_GET['1'])){
	if(strpos($file, '_adv_') !== false){
		echo 'Adv. file detected';
		$check = 0;
	}
	else{
		echo 'Stripping too long words. (max. 5 letters)';
	}
}
elseif(isset($_GET['1'])){
	echo 'Exporting data.';
}



$real_words = array();
$total = 0;
foreach($words as $word => $freq){

	if(isset($_GET['delete'])){
		if($word === $_GET['delete']){
			continue;
		}
	}

	if(!$check){
		$real_words[$word] = $freq;
		$freq = explode('|', $freq);
		$total += $freq[1];
	}
	else if(utf8_strlen($word) <= 5){
		$real_words[$word] = $freq;
		$total += $freq;
	}
	if(isset($_GET['1'])){
		if(count($real_words) >= 100){
			break;
		}
	}
}


$list = '<ul style="list-style-type: square">';
echo '<h1>Most frequent short words ('.count($real_words).')</h1><ul>';
foreach($real_words as $word => $freq){
	if(strlen($word) < 3 || preg_match('/^(&#[0-9]{2,4};|[a-z]){1,2}$/', $word)){
		echo '<li style="color: #f00; font-weight: bold">';
		$list .= "\r".'<li>'.$word.' [<a href="?file='.htmlentities($file).'&amp;delete='.urlencode($word).'#del">Del</a>]</li>';
	}
	else{
		echo '<li>';
	}
	echo $word.' ('.round($freq / $total * 100, 2).'%) [<a href="?file='.htmlentities($file).'&amp;delete='.urlencode($word).'#del">Del</a>]</li>';
}

if(!isset($_GET['1'])){
	echo '<h1 id="del">Delete Suggestions</h1>'.$list.'</ul>';
	echo '<h1>Writing to file</h1>';

	if(trim($sp_file) != ''){
		$file = $sp_file;
	}
	else{
		$file = preg_replace('/^([a-z]{2,4})_(adv_)?words\.dat$/', '\\1_\\2filtered_words.dat', $file);
	}
	$open = fopen($file, 'w');
	fwrite($open, '<?php $words = '.var_export($real_words, true).'; ?'.'>');
	fclose($open);

	echo '<br />&raquo; Done.';
}
else{
	$file = preg_replace('/^([a-z]{2,4})_.*/', '\\1_words.dat', $file);
	$file = '../LID/data/'.$file;
	echo '<h1>Writing to file ('.htmlentities($file).')</h1><br/>';

	$open = fopen($file, 'w');
	fwrite($open, '<?php $words = '.var_export($real_words, true).'; ?'.'>');
	fclose($open);
	echo '&raquo; DONE';
}