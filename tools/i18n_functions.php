<?php

function Utf8_HtmlEntities($string){
####
# Text to UTF-8 or HTML Entities tool Copyright (c) 2006 - Brian Huisman (GreyWyvern)
# This script is licenced under the BSD licence: http://www.greywyvern.com/code/bsd
# Modification to a PHP function and bug fixing: 2008, 2009 - Lucas Jacques
####
	global $utf8_error;
	$string = trim($string);
	$char = ''; $string_copy = $string;
	while(strlen($string) > 0){
		preg_match('/^(.)(.*)$/us', $string, $match);
		$test = utf8_decode($match[1]);
		if(strlen($match[1]) > 1 || ($test == '?' && uniord($match[1]) != '63')){
			$char .= '&#'.uniord($match[1]).';';
		}
		else{
			if(strlen($match[1]) != strlen(htmlentities($match[1]))){
				$char .= '&#'.uniord($match[1]).';';
			}
			else{
				$char .= $match[1];
			}
		}
		$string = $match[2];
	}
	// UTF-8 check
	if(strlen($char) < strlen($string_copy)){
		$utf8_error = true;
		return '';
	}
	return $char;
}

function UniOrd($c){
	$ud = 0;
	if (ord($c{0}) >= 0 && ord($c{0}) <= 127) $ud = ord($c{0});
	if (ord($c{0}) >= 192 && ord($c{0}) <= 223) $ud = (ord($c{0})-192)*64 + (ord($c{1})-128);
	if (ord($c{0}) >= 224 && ord($c{0}) <= 239) $ud = (ord($c{0})-224)*4096 + (ord($c{1})-128)*64 + (ord($c{2})-128);
	if (ord($c{0}) >= 240 && ord($c{0}) <= 247) $ud = (ord($c{0})-240)*262144 + (ord($c{1})-128)*4096 + (ord($c{2})-128)*64 + (ord($c{3})-128);
	if (ord($c{0}) >= 248 && ord($c{0}) <= 251) $ud = (ord($c{0})-248)*16777216 + (ord($c{1})-128)*262144 + (ord($c{2})-128)*4096 + (ord($c{3})-128)*64 + (ord($c{4})-128);
	if (ord($c{0}) >= 252 && ord($c{0}) <= 253) $ud = (ord($c{0})-252)*1073741824 + (ord($c{1})-128)*16777216 + (ord($c{2})-128)*262144 + (ord($c{3})-128)*4096 + (ord($c{4})-128)*64 + (ord($c{5})-128);
	if (ord($c{0}) >= 254 && ord($c{0}) <= 255) $ud = false; // error
	return $ud;
}

###########
# utf8_strlen
###########

function utf8_strlen($text){
	$count = 0;
	for($i = 0; $i < strlen($text); $i++){
		if($text[$i] == '&' && $text[($i+1)] == '#'){
			$i += 2;
			while(preg_match('/[0-9]/', $text[$i])){
				$i++;
			}
		}
		$count++;
	}
	return $count;
}


########
# utf8_strtolower
########

function utf8_strtox_init(){
	global $utf8_strtox, $except;
	if(isset($utf8_strtox) && is_array($utf8_strtox)){
		return true;
	}
$utf8_strtox = array();
for($i = 192; $i < 223; $i++){
	if($i == 215){ continue; }
	$utf8_strtox[$i] = $i+32;
}
$except = array(304, 305, 312, 329, 376);
for($i = 256; $i < 382; $i += 2){
	if(in_array($i, $except)){ $i--; continue; }
	$utf8_strtox[$i] = $i+1;
	if(in_array($i+1, $except)){
		$utf8_strtox[$i]++;
		$i++;
	}
}
$except = array(390, 393, 394, 397, 398, 399, 400, 403, 404, 405, 406, 407, 410, 411, 412, 413, 414, 415, 422, 425, 426, 427, 430, 433, 434, 439, 442, 443);
function utf8_next_i($i){
	global $except;
	$i++;
	while(in_array($i, $except)){
		$i++;
	}
	return $i;
}

for($i = 386; $i < 445; $i = utf8_next_i($i)){
	$utf8_strtox[$i] = utf8_next_i($i);
	$i = utf8_next_i($i);
}
$except = array(477, 496, 497, 498, 499, 502, 503, 544, 545, 564, 573);
for($i = 461; $i < 591; $i++){
	if(in_array($i, $except)){
		if($i == 564){ $i = 570; }
		if($i == 573){ $i = 581; }
		continue;
	}

	$utf8_strtox[$i] = $i+1;
	$while = false;
	while(in_array($utf8_strtox[$i], $except)){
		$while = true;
		if($utf8_strtox[$i] == 564){ $utf8_strtox[$i] = 571; }
		else if($utf8_strtox[$i] == 573){ $utf8_strtox[$i] = 582; }
		else{ $utf8_strtox[$i]++; }
	}
	if($while){ $i = $utf8_strtox[$i]; }
	else{ $i++; }
}
$strdata = '880-881|882-883|886-887|902-940|904-941|905-942|906-943|908-972|910-973|911-974|304-105|394-599|385-595|390-596|393-598|398-477|399-601|400-603|403-608|404-611|406-617|407-616|412-623|413-626|415-629|422-640|425-643|430-648|434-651|439-658|502-405|503-447|544-414|570-11365|573-410|574-11366|579-384|580-649|581-652|891-1021|497-499|498-499|1015-1016|1017-1010|1018-1019|1022-892|1023-893|376-255|7838-223|433-650|1216-1231|8122-8048|8123-8049|8124-8115|8136-8050|8137-8051|8138-8052|8139-8053|8140-8131|8152-8144|8153-8145|8154-8054|8155-8055|8168-8160|8169-8161|8170-8058|8171-8059|8172-8165|8184-8056|8185-8057|8186-8060|8187-8061|8188-8179';
$strdata = explode('|', $strdata);
foreach($strdata as $key => $val){
	$val = explode('-', $val);
	$utf8_strtox[$val[0]] = $val[1];
}

for($i = 913; $i < 940; $i++){
	if($i == 930){ continue; }
	$utf8_strtox[$i] = $i+32;
}
for($i = 984; $i < 1007; $i++){
	$utf8_strtox[$i] = $i+1;
	$i++;
}
for($i = 452; $i < 459; $i += 3){
	$utf8_strtox[$i] = $i + 2;
	$utf8_strtox[$i+1] = $i + 2;
}
for($i = 1024; $i < 1040; $i++){
	$utf8_strtox[$i] = $i + 80;
}
for($i = 1040; $i < 1072; $i++){
	$utf8_strtox[$i] = $i + 32;
}
for($i = 1120; $i < 1153; $i += 2){
	$utf8_strtox[$i] = $i + 1;
}
for($i = 1162; $i < 1315; $i += 2){
	if($i == 1216 || $i == 1231){
		$i--;
		continue;
	}
	$utf8_strtox[$i] = $i + 1;
}
for($i = 1329; $i < 1367; $i++){
	$utf8_strtox[$i] = $i + 48;
}
for($i = 7680; $i < 7829; $i += 2){
	$utf8_strtox[$i] = $i + 1;
}
for($i = 7840; $i < 7929; $i += 2){
	$utf8_strtox[$i] = $i + 1;
}
for($m = 0; $m < 7; $m++){
	$u = 7944 + ($m * 16);
	for($i = $u; $i < ($u + 8); $i++){
		if(($m == 1 || $m == 4) && $i == ($u + 6)){
			break;
		}
		if($m == 5 && (substr($i, -1) % 2 == 0)){ continue; }
		$utf8_strtox[$i] = $i - 8;
	}
}
for($m = 0; $m < 4; $m++){
	$u = 8072 + ($m * 16);
	for($i = $u; $i < ($u + 8); $i++){
		if($i == 8122){ break; }
		$utf8_strtox[$i] = $i - 8;
	}
}
return true;
}

function utf8_strtox_get($number){
	global $utf8_strtox;
	$number = $number[1];
	if(!is_numeric($number)){ return '&#'.$number.';'; }
	if(isset($utf8_strtox[$number])){
		return '&#'.$utf8_strtox[$number].';';
	}
	else{ return '&#'.$number.';'; }
}

function utf8_strtolower($text){
	global $utf8_strtox, $stop_adv_search;
	if($stop_adv_search){ return strtolower($text); }
	if(!isset($utf8_strtox) || !is_array($utf8_strtox)){
		utf8_strtox_init();
	}
	$test = preg_replace_callback('/&#([0-9]{3,4});/im', 'utf8_strtox_get', $text);
	return strtolower($test);
}

?>