<style type="text/css">
body { font-family: Verdana; font-size: 10pt; }
</style>

<?php
if(isset($_GET['txt'])){
	if(!isset($_GET['file'])){
#######
echo '<h1>TXT Files</h1>
&raquo; <a href="?">Main</a>';
$dir = getcwd();
$files = $size = array();
$count = 0;

// Open a known directory, and proceed to read its contents
if (is_dir($dir)) {
    if ($dh = opendir($dir)) {
        while (($file = readdir($dh)) !== false) {
	if($file != '.' && $file != '..' && !is_dir($file)){
		$ext = preg_replace('/.*\.([a-z]{2,4})$/', '\\1', $file);
		if($ext == 'txt'){
			$lang = preg_replace('/^([a-z]{2,4})(_){0,1}.*\.'.$ext.'$/', '\\1', $file);
			if(!$lang){ echo '<li>Error.'; var_dump($lang); var_dump($ext); var_dump($file); echo '</li>'; continue; }
			if(!isset($files[$lang])){
				$files[$lang] = array();
				$size[$lang] = 0;
			}
			$size[$lang] += filesize($file);
			$files[$lang][] = $file;
			$count++;
		}
	}
        }
        closedir($dh);
    } else{ die('OPENDIR FAIL'); }
} else{ die('DIR is not a dir'); }

echo '<ul>';
foreach($files as $lang => $data){
	echo '<li>'.$lang.' files: ('.round($size[$lang]/1000).' KB)<ul>';
	foreach($data as $file){
		echo '<li><a href="?txt&amp;file='.$file.'">'.$file.'</a></li>';
	}
	echo '</ul>';
}
echo '<li>Total files: '.$count.'</li></ul>
<br />&raquo; <a href="?main">Main</a>';




#######
	}
	else{
		if(!preg_match('/.*\.txt$/', $_GET['file']) || strpos($_GET['file'], '/') !== false){
			die("Invalid file name");
		}
		echo '<h1>View File</h1><a href="?txt">Back</a> ';
		$data = explode("\r", file_get_contents($_GET['file'])) or die('FILE RETRIEVAL FAIL');
		echo 'Source: <a href="'.$data[0].'" target="_blank">'.urldecode($data[0]).'</a><hr />';
		$words = count(explode(' ', $data[1]));
		echo '<b>'.$words.' words</b> -- avg. word length: '.round((strlen($data[1]) - $words)/$words, 3);
		echo '<br><br /><br />'.$data[1];
		echo ' <a href="?txt">Back</a>';
	}
exit;
}



require('i18n_functions.php');
if(isset($_GET['view'])){
echo '&raquo; <a href="?">Main</a>';
	$file = $_GET['view'];
	if(!preg_match('/.*\.dat$/', $file) || strpos($file, '/') !== false){
		die('Invalid file');
	}

	echo '<h1>'.$file.' contents</h1>';
	require($file);
	if(isset($words)){

if(!isset($_GET['max'])){	echo '<a href="?view='.$file.'&max=10">Change maximum to 10</a>'; }
else{ echo '<a href="?view='.$file.'">Normal maximum (5)</a>'; }

if(strpos($file, 'filtered') === false){
	$words = array_reverse($words);
}

		if(isset($_GET['max']) && preg_match('/^[1-9]{1,}[0-9]{0,}$/', $_GET['max'])){
			$max = $_GET['max'];
		}
		else{ $max = 5; }

		$all = 0; $all_five = 0; $abnormal = 0; $ab_ = 0; $count = 1;
		foreach($words as $word => $freq){
			$all += $freq;
			if(utf8_strlen($word) <= $max){
				$all_five++;
				if($max > 5 && utf8_strlen($word) > 5){
					$abnormal++;
					if($count <= 100){
						$ab_++;
					}
				}
			}
			$count++;
		}

		echo '<ul>
<li><b>'.count($words).' words</b></li>
<li><b>'.$all.' occurrences (= avg. '.round($all / count($words), 3).' occ./word)</b></li>
<li><b>'.$all_five.' words with &lt;= '.$max.' letters.</b></li>';
if($max > 5){
	echo '<li><b>'.$abnormal.' words over 5 letters, '.$ab_.' in top 200</b></li>';
}
		$all /= 100; $all_five /= 100;
		$count = 1;

		foreach($words as $word => $freq){
			if(utf8_strlen($word) <= $max){
				echo '<li>'.$word.' ('.round($freq / $all, 2).'%, '.round($freq/$all_five, 2).'%)</li>';
				$count++;
			}
			if($count > 200){ break; }
		}
		echo '</ul> <a href="?">Main</a>'; exit;
	}

	if(!isset($one)){
		if(!isset($one_)){ die('Data couldnt be determined'); }
		$one = $one_;
		$bi = $bi_;
		$tri = $tri_;
	}
	if(!isset($one_)){
		$one = array_reverse($one);
		$bi = array_reverse($bi);
		$tri = array_reverse($tri);
	}


#####
	echo '<b>'.count($one).' characters</b><ul>';

	$every = 0;
	foreach($one as $item => $freq){
		$every += $freq;
	}

	echo '<li><b>'.$every.' character occurences counted.</b></li>';
	$every /= 100;
	$count = 1; $per_all = 0;
	foreach($one as $item => $freq){
		$per = $freq / $every; $per_all += $per;
		echo '<li>'.$item.' ('.round($freq / $every, 2).'%)</li>';
		if($count > 26){ break; }
		$count++;
	}
	echo '<li>Total: '.round($per_all, 2).'%</li></ul>';

#####
	echo '<b>'.count($bi).' bigrams</b><ul>';

	$every = 0;
	foreach($one as $item => $freq){
		$every += $freq;
	}

	echo '<li><b>'.$every.' bigram occurences counted.</b></li>';
	$every /= 100;
	$count = 1; $per_all = 0;
	foreach($bi as $item => $freq){
		$per = $freq / $every; $per_all += $per;
		echo '<li>'.$item.' ('.round($freq / $every, 2).'%)</li>';
		if($count > 150){ break; }
		$count++;
	}
	echo '<li>Total: '.round($per_all, 2).'%</li></ul>';

####
	echo '<b>'.count($bi).' trigrams</b><ul>';

	$every = 0;
	foreach($one as $item => $freq){
		$every += $freq;
	}

	echo '<li><b>'.$every.' trigram occurences counted.</b></li>';
	$every /= 100;
	$count = 1; $per_all = 0;
	foreach($tri as $item => $freq){
		$per = $freq / $every; $per_all += $per;
		echo '<li>'.$item.' ('.round($per, 2).'%)</li>';
		if($count > 300){ break; }
		$count++;
	}
	echo '<li>Total: '.round($per_all, 2).'%</li></ul>';

echo '<a href="?">Main</a>';





exit;
}


$dir = getcwd();
$files = array();

// Open a known directory, and proceed to read its contents
if (is_dir($dir)) {
    if ($dh = opendir($dir)) {
        while (($file = readdir($dh)) !== false) {
	if($file != '.' && $file != '..' && !is_dir($file)){
		$ext = preg_replace('/.*\.([a-z]{2,4})$/', '\\1', $file);
		if($ext == 'dat'){
			$lang = preg_replace('/^([a-z]{2,4})(_){0,1}.*\.'.$ext.'$/', '\\1', $file);
			if(!$lang){ echo '<li>Error.'; var_dump($lang); var_dump($ext); var_dump($file); echo '</li>'; continue; }
			if(!isset($langs[$lang])){ $langs[$lang] = 0; }
			$files[] = $file;
		}
	}
        }
        closedir($dh);
    } else{ die('OPENDIR FAIL'); }
} else{ die('DIR is not a dir'); }

echo '<h1>Files</h1><ul>';
if(isset($_GET['type'])){
	$types = array(
		'Evaluated N-Grams' => '/^([a-z]{2,4})_filtered\.dat$/',
		'Evaluated Words' => '/^([a-z]{2,4})_filtered_words\.dat$/',
		'Raw N-Grams' => '/^[a-z]{2,4}\.dat$/',
		'Raw Words' => '/^[a-z]{2,4}_words\.dat$/',
		'Other (?)' => '/.*/',
	);
	foreach($types as $name => $regex){
		if(count($files) == 0){ break; }
		echo '<li><b>'.$name.'</b><ul>';
		foreach($files as $key => $file){
			if(preg_match($regex, $file)){
				echo '<li><a href="?view='.$file.'">'.$file.'</a></li>';
				unset($files[$key]);
			}
		}
		echo '</ul>';
	}
			
}
else{
	foreach($files as $file){
		echo '<li><a href="?view='.$file.'">'.$file.'</a></li>';
	}
}
echo '</ul>';
if(isset($_GET['type'])){
	echo '<br />&raquo; <a href="?">Normal order</a>';
}
else{
	echo '<br />&raquo; <a href="?type">Order by type</a>';
}
echo '<br><br>&raquo; <a href="?txt">View .txt files</a>';
?>