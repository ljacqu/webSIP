<?php
function debug($say){
	$debug = 0; // set to TRUE for output

	if($debug){
		echo '<br />&raquo; Debug: '.$say;
	}
}

function error($text){
	global $lang;
	die('<p><b>'.$lang['error'].'</b>: '.$text.'
<br />&raquo; <a href="javascript:history.back(-1)">'.$lang['back'].'</a></p></body></html>');
}

function timer_start(){
	global $time;
	$time = microtime(TRUE);
}

function timer_eval(){
	global $time;
	$diff = microtime(TRUE) - $time;

	// round properly
	if($diff > 1){
		return round($diff, 2);
	}

	$diff = (string) $diff;
	$diff = explode('.', $diff);
	for($i = 0; $i < strlen($diff[1]); $i++){
		if($diff[1][$i] != '0'){ break; }
	}
	$i++;

	$diff = implode('.', $diff);
	$diff = (float) $diff;

	return round($diff, $i);
}


?>