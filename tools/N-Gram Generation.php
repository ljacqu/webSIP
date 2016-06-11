<style type="text/css">
body { font-family: Verdana; font-size: 10pt; margin: 35px }
</style>
<?php
error_reporting(E_ALL);

if(!isset($_GET['file'])){
	$dir = getcwd();
	$files = array();

	// Open a known directory, and proceed to read its contents
	if(is_dir($dir)){
		if($dh = opendir($dir)){
			while(($file = readdir($dh)) !== false){
				if(preg_match('/^.*\.txt$/', $file)){
					$files[] = $file;
				}
			}
			closedir($dh);
		}
		else{ die('OPENDIR fail :('); }
	}else{ die('$dir ('.htmlentities($dir).') is not a directory'); }

	if(file_exists('files.log')){
		include('files.log');
	}
	else{ $log = array(); }

	$processed = $unproc = 0;
	$list_proc = $list_unproc = '';

	echo '<h1>SELECT FILE</h1><ul>';
	$old = ''; $found_lang = 0;
	foreach($files as $file){
		$langid = preg_replace('/([a-z]{2,4}).*\.txt$/', '\\1', $file);
		if(!in_array($file, $log)){

			// automatic redir
			if(isset($_GET['auto']) && $_GET['lang'] == $langid){
				echo '<meta http-equiv="refresh" content="0; URL=?file='.$file.'&amp;redir" />';
				exit;
			}

			// propose redir
			if($old != $langid){
				$list_unproc .= '<li style="margin-top: 20px">Automatic: <a href="?auto&amp;lang='.$langid.'">'.$langid.' files</a>';
				$old = $langid;
			}

			$list_unproc .= '<li><a href="?file='.htmlentities($file).'">'.$file.'</a></li>';
			$unproc++;
		}
		else{
			if(isset($_GET['auto']) && $_GET['lang'] == $langid){
				$found_lang = 1;
			}
			$list_proc .= '<li>'.$file.'</li>'; $processed++; }
		echo '</li>';
	}

	if(isset($_GET['auto']) && $found_lang){
		echo '<li><b>All languages for <u>'.$_GET['lang'].'</u> have been processed. :)</b></li>';
	}
	echo $list_unproc.'<li>-----</li>'.$list_proc;

	echo '</ul><hr />Total: '.($processed + $unproc).'<br />Processed: '.$processed.'<br />Unprocessed: '.$unproc;
	exit;
}

############
## DO N-Gram #
############

// File validation + content retrieval
$file = $_GET['file'];
if(!file_exists($file) || !preg_match('/^[a-z]{2,4}_.*?\.txt$/', $file)){
	die('Invalid file! It either does not exist or is not well-formed.');
}

$log = array();
if(file_exists('files.log')){
	include('files.log');
}
$log[] = $file;

$text = explode("\r", file_get_contents($file));

unset($text[0]);
if(count($text) > 1){
	$text = implode(' ', $text);
}
else{ $text = $text[1]; }

if(!preg_match('/[a-z;]/', $text[(strlen($text)-1)])){
	$text = substr($text, 0, -1);
}

$text = str_replace(array('&#34;', '&#62;', '&#60;', '&#8220;', '&#8222;', '&#8211;'), ' ', $text);

// start processing -> substrings
$lang = preg_replace('/^([a-z]{2,4})_.*/', '\\1', $file);
if($lang == ''){ die('Malformed file name :('); }
// if $lang is an exception
if(in_array($lang, array('ja', 'zh', 'ko'))){
	die('Exception language.');
}

if(file_exists($lang.'.dat')){
	include($lang.'.dat');
}
else{
	$one = array();
	$bi = array();
	$tri = array();
}

$pointers = array();

function NextLetter(){
	global $i, $text;

	if(!isset($text[$i])){ die('$text['.$i.'] is not set!'); }
	
	if(preg_match('/[a-z\']/', $text[$i])){
		return $text[$i++];
	}
	if($text[$i] == '&'){
		if($text[($i+1)] != '#'){ die('Malformed text! [1]'); }
		$string = '&#'; $i += 2;
		while(preg_match('/[0-9]/', $text[$i])){
			$string .= $text[$i];
			$i++;
		}
		if($text[$i] != ';'){ die('Malformed text! [2]'); }
		$i++;
		return $string.';';
	}
	else{ // other char gets turned to _
		global $pointers;
		$last = end($pointers); // with end instead of $pointers[2], we hope we can use it at the beginning, when $pointers is still empty?
		if($last == '_'){
			$i++;
			if($i >= strlen($text)){
				die('Error! Too many special characters at end of text: '.$i);
			}
			return NextLetter($i);
		}
		return '_';
	}
}

// Fill $pointers for first time
$i = 0;
if(!preg_match('/[a-z\']/', $text[0])){ $pointers[0] = '_'; $i = 1; }
else{ $pointers[0] = NextLetter(0); }

$pointers[1] = NextLetter($i);
$pointers[2] = NextLetter($i);

while($i < strlen($text)){
	// stats for 2- and 3-gram
	$one_name = $pointers[0];
	$bi_name = $pointers[0].$pointers[1];
	$tri_name = $pointers[0].$pointers[1].$pointers[2];

	if($one_name != '_'){
		if(isset($one[$one_name])){
			$one[$one_name]++;
		}
		else{ $one[$one_name] = 1; }
	}

	if(isset($bi[$bi_name])){
		$bi[$bi_name]++;
	}
	else{ $bi[$bi_name] = 1; }

	if(isset($tri[$tri_name])){
		$tri[$tri_name]++;
	}
	else{ $tri[$tri_name] = 1; }

	// move to next char
	array_shift($pointers);
	$pointers[2] = NextLetter($i);
}

// finishing properly
for($i = 0; $i < 2; $i++){ // <-- voll dä Trick
	$one_name = $pointers[0];
	$bi_name = $pointers[0].$pointers[1];
	$tri_name = $pointers[0].$pointers[1].$pointers[2];

	if($one_name != '_'){
		if(isset($one[$one_name])){ $one[$one_name]++; } else{ $one[$one_name] = 1; }
	}
	if($bi_name != '__'){
		if(isset($bi[$bi_name])){ $bi[$bi_name]++; } else{ $bi[$bi_name] = 1; }
	}
	if(!preg_match('/.*(_){2}$/', $tri_name)){
		if(isset($tri[$tri_name])){ $tri[$tri_name]++; } else{ $tri[$tri_name] = 1; }
	}

	array_shift($pointers);
	$pointers[2] = '_';
}

natsort($one);
natsort($bi); // why not
natsort($tri);

$open = fopen($lang.'.dat', 'w');
fwrite($open, '<?php $one = '.var_export($one, true).'; $bi = '.var_export($bi, true).'; 
$tri = '.var_export($tri, true).'; ?'.'>');
fclose($open);

$open = fopen('files.log', 'w');
fwrite($open, '<?php $log = '.var_export($log, true).'; ?'.'>');
fclose($open);

echo 'N-Gram generation ('.$lang.'.dat) done.';

############
## Words
############

$word_save = '';
$words = array();

if(file_exists($lang.'_words.dat')){
	include($lang.'_words.dat'); // $words
}
if(file_exists($lang.'_adv_words.dat')){
	include($lang.'_adv_words.dat'); // $words_sp
}
$words_sp_once = array();


for($i = 0; $i < strlen($text); $i++){
	if(preg_match('/[a-z\']/', $text[$i])){
		$word_save .= $text[$i];
	}
	elseif($text[$i] == '&'){
		if($text[($i+1)] != '#'){ die('Invalid use of & in text! :/'); }
		$i += 2;

		$number = '';
		while(preg_match('/[0-9]/', $text[$i])){
			$number .= $text[$i];
			$i++;
		}
		$word_save .= '&#'.$number.';';
	}
	else{
		if(strlen($word_save) > 0){
			if(isset($words[$word_save])){
				$words[$word_save]++;
			}
			else{
				$words[$word_save] = 1;
			}

			if(!isset($words_sp_once[$word_save])){
				$words_sp_once[$word_save] = 1;
				if(!isset($words_sp[$word_save])){
					$words_sp[$word_save] = '1|0';
				}
				else{
					$words_sp[$word_save] = explode('|', $words_sp[$word_save]);
					$words_sp[$word_save][0]++;
					$words_sp[$word_save] = implode('|', $words_sp[$word_save]);
				}
			}

			$words_sp[$word_save] = explode('|', $words_sp[$word_save]);
			$words_sp[$word_save][1]++;
			$words_sp[$word_save] = implode('|', $words_sp[$word_save]);
		}
		$word_save = '';
	}
}
if($word_save != ''){
	if(isset($words[$word_save])){ $words[$word_save]++; }
	else{ $words[$word_save] = 1; }

###########
			if(!isset($words_sp_once[$word_save])){
				$words_sp_once[$word_save] = 1;
				if(!isset($words_sp[$word_save])){
					$words_sp[$word_save] = '1|0';
				}
				else{
					$words_sp[$word_save] = explode('|', $words_sp[$word_save]);
					$words_sp[$word_save][0]++;
					$words_sp[$word_save] = implode('|', $words_sp[$word_save]);
				}
			}

			$words_sp[$word_save] = explode('|', $words_sp[$word_save]);
			$words_sp[$word_save][1]++;
			$words_sp[$word_save] = implode('|', $words_sp[$word_save]);
#############
}

natsort($words);

$open = fopen($lang.'_words.dat', 'w');
fwrite($open, '<?php $words = '.var_export($words, true).'; ?'.'>');
fclose($open);

$open = fopen($lang.'_adv_words.dat', 'w');
fwrite($open, '<?php $words_sp = '.var_export($words_sp, true).'; ?'.'>');
fclose($open);

echo '<br />Words done. Total: <b>'.count($words).' / '.count($words_sp);

echo '</b><div style="font-size: 14pt; position: absolute; top: 100px; left: 400px"><a href="?">BACK</a></div>';
if(isset($_GET['redir'])){
	echo '<meta http-equiv="refresh" content="1; URL=?auto&amp;lang='.$lang.'" />';
}
?>