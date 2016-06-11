<?php
$langs = array('zh', 'ja', 'ko');
require('cyr_lat.php');


if(isset($_GET['do']) && file_exists($_GET['do'])){
	// Statistics?
	if(preg_match('/^(zh|ja|ko)_charcount.dat$/', $_GET['do'])){
		$file = $_GET['do'];
		require($file);
		arsort($chars);

		$total = 0;
		foreach($chars as $freq){
			$total += $freq;
		}
		$total /= 100;

		$counter = 0; $count_cn = $count_jp = 0;
			$unique_cn = $unique_jp = 0;
		foreach($chars as $num => $freq){
			// echo '<li>&#'.$num.'; abs: '.$freq.' ('.round($freq / $total).' %)</li>';	#~DISPLAYS EVERYTHING!!
			if(19968 <= $num && 40869 >= $num){
				$count_cn += $freq;
				$unique_cn++;
				continue;
			}

			if(12353 <= $num && 16143 >= $num){
				$count_jp += $freq;
				$unique_jp++;
				continue;
			}

			echo '<li>'.$num.'- <b>&#'.$num.';</b> ['.$freq.'] '.round($freq / $total).' %</li>';
			$counter++;

			if($counter >= 100){
				//break;
			}
		}
		echo '<li>Cn chars: '.$count_cn.'; '.round($count_cn / ($count_jp + $count_cn)*100).'%</li>
		<li>Jp chars: '.$count_jp.'; '.round($count_jp / ($count_jp + $count_cn)*100).'%</li>';
		echo '<li>Unique Cn chars: '.$unique_cn.'</li><li>Unique Jp chars: '.$unique_jp.'</li>';
		echo '<li>Total: '.($total * 100).'</li>';
		exit;

	}

	// Other .txt file?
	if(!preg_match('/^(ja|zh|ko)_([0-9]+_)+\.txt$/', $_GET['do'])){
		die('Invalid file');
	}
	$file = explode("\r", file_get_contents($_GET['do']));
	$file = $file[1];

	$lang = preg_replace('/^(ja|zh|ko)_.*$/', '\\1', $_GET['do']);
	if(file_exists($lang.'_charcount.dat')){
		require($lang.'_charcount.dat');
	}
	else{
		$chars = array();
	}

	for($i = 0; $i < strlen($file); $i++){
		if($file[$i] != '&'){
			continue;
		}
		else{
			$i += 2;
			$number = '';

			while(preg_match('/[0-9]/', $file[$i])){
				$number .= $file[$i];
				$i++;
			}
			$i++;

			if(isset($chars[$number])){
				$chars[$number]++;
			}
			else{
				$chars[$number] = 1;
			}
			
		}
	}

	$open = fopen($lang.'_charcount.dat', 'w');
	fwrite($open, '<?php $chars = '.var_export($chars, true).'; ?>');
	fclose($open);

	echo 'Done. total chars: '.count($chars);

}


$dir = getcwd();
if (is_dir($dir)) {
    if ($dh = opendir($dir)) {
        while (($file = readdir($dh)) !== false) {
		if(!is_dir($file) && preg_match('/^(zh|ja|ko)_([0-9]+_)+\.txt$/', $file)){
			$lang = preg_replace('/^(zh|ja|ko)_.*$/', '\\1', $file);
			$files[$lang][] = $file;
		}
		elseif(!is_dir($file) && preg_match('/.*_charcount.dat$/', $file)){
			$files['dat'][] = $file;
		}
        }
        closedir($dh);
    }
}

foreach($files as $lang => $data){
	echo '<h2>'.$lang.' files</h2>';
	foreach($data as $file){
		echo '<li><a href="?do='.$file.'">'.asian_title($file).'</a></li>';
	}
}
?>
