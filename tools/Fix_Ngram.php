<?php
header('Content-Type: text/html; charset=UTF-8', true);
?>
<style>
<!--
li li { list-style-type: square; }
body {	font-family: Verdana; font-size: 10pt }
--></style>
<?php
if(!isset($_GET['file'])){
	$dir = getcwd();
	$files = array();

	// Open a known directory, and proceed to read its contents
	if(is_dir($dir)){
		if($dh = opendir($dir)){
			while(($file = readdir($dh)) !== false){
				if(preg_match('/^[a-z]{2,4}\.dat$/', $file)){
					$files[] = $file;
				}
			}
			closedir($dh);
		}
		else{ die('OPENDIR fail :('); }
	}else{ die('$dir ('.htmlentities($dir).') is not a directory'); }

	echo '<h1>SELECT FILE</h1><ul>';
	foreach($files as $file){
		echo '<li><a href="?file='.htmlentities($file).'">'.$file.'</a></li>';
	}
	exit;
}

$file = $_GET['file'];
if(!preg_match('/^[a-z]{2,4}\.dat$/', $file)){
	die('Invalid file');
}
$lang = preg_replace('/^([a-z]{2,4})\.dat$/', '\\1', $file);
if(!preg_match('/^[a-z]{2,4}$/', $lang)){ die('Invalid file?'); }
//require('i18n_functions.php');
require($file);

echo '<h1>N-Gram Data</h1>';
echo 'Total letters: '.count($one).'
<br />Total 2-Grams: '.count($bi).'
<br />Total 3-Grams: '.count($tri);

$i = 0;

natsort($one);
$one = array_reverse($one);
natsort($bi);
$bi = array_reverse($bi);
natsort($tri);
$tri = array_reverse($tri);

$total_one = 0;
$total_bi = 0;
$total_tri = 0;

foreach($one as $item => $freq){
	$total_one += $freq;
}
foreach($bi as $item => $freq){
	$total_bi += $freq;
}
foreach($tri as $item => $freq){
	$total_tri += $freq;
}

$count = 1; $one_ = array();
foreach($one as $item => $freq){
	$one_[$item] = $freq/$total_one;
	if($count >= 26){	break; }
	$count++;
}

$count = 1; $bi_ = array();
foreach($bi as $item => $freq){
	$bi_[$item] = $freq/$total_bi;
	if($count > 150){ break; }
	$count++;
}

$count = 1; $tri_ = array();
foreach($tri as $item => $freq){
	$tri_[$item] = $freq/$total_tri;
	if($count > 300){ break; }
	$count++;
}

$open = fopen($lang.'_filtered.dat', 'w');
fwrite($open, '<?php $one_ = '.var_export($one_, true).'; $bi_ = '.var_export($bi_, true).'; $tri_ = '.var_export($tri_, true).'; ?'.'>');
fclose($open);

echo '<h1>Data</h1>
<ul>
 <li><b><u>Letters ('.$total_one.'):</u></b><ul>';
foreach($one_ as $item => $freq){
	echo "\r".'<li><b>'.$item.'</b> ('.round($freq*100, 2).'%)</li>';
}
echo '</ul><br /></li>
<li><b><u>Bigrams ('.$total_bi.'):</u></b><ul>';
$count = 1;
foreach($bi_ as $item => $freq){
	echo "\r".'<li><b>'.$item.'</b> ('.round($freq*100, 2).'%)</li>';
	if($count >= 50){ break; }
	$count++;
}
echo '<br /></ul></li>
<li><b><u>Trigrams ('.$total_tri.'):</u></b><ul>';
$count = 1;
foreach($tri_ as $item => $freq){
	echo "\r".'<li><b>'.$item.'</b> ('.round($freq*100, 2).'%)</li>';
	if($count >= 50){ break; }
	$count++;
}
echo '</ul></li></ul>';

echo '<h3>Exporting N-Gram to ../LID/data/</h3>';

$pointer = 0; $rang = 0;
foreach($one_ as $item => $freq){
	if($freq != $pointer){
		$pointer = $freq;
		$rang++;
	}
	$one_[$item] = $rang;
}

$pointer = 0; $rang = 0;
foreach($bi_ as $item => $freq){
	if($freq != $pointer){
		$pointer = $freq;
		$rang++;
	}
	$bi_[$item] = $rang;
}

$pointer = 0; $rang = 0;
foreach($tri_ as $item => $freq){
	if($freq != $pointer){
		$pointer = $freq;
		$rang++;
	}
	$tri_[$item] = $rang;
}

$open = fopen('../LID/data/'.$lang.'.dat', 'w');
fwrite($open, '<?php $one_ = '.var_export($one_, true).'; $bi_ = '.var_export($bi_, true).'; $tri_ = '.var_export($tri_, true).'; ?'.'>');
fclose($open);

echo '<hr />&raquo; Exported.';
?>