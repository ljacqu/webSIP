<?php
error_reporting(0);
/* 
 * *********************
 * Language identification tool
 * 
 * ljacqu, 2010-2011, 2016
 * *********************
*/

#################
# Demo feature (at the top because we use cookies)
#################
$do_demo = 0;
if(isset($_GET['demo']) || isset($_POST['demo'])){
	$do_demo = 1;
	require('demo.php');

	if(isset($_GET['lang']) && is_string($_GET['lang']) && isset($demo[$_GET['lang']])){
		$text = $demo[$_GET['lang']];
	}
	else{
		if(isset($_COOKIE['demo'])){
			$dm_dat = explode(',', $_COOKIE['demo']);
			if(count($dm_dat) == count($demo)){
				unset($dm_dat);
			}
		}

		// We need to shuffle the keys and not $demo, because shuffle() overwrites array keys.
		$keys = array_keys($demo);
		shuffle($keys);

		foreach($keys as $key){
			if(isset($dm_dat) && in_array($key, $dm_dat)){
				continue;
			}

			$text = $demo[$key]; break;
		}

		if(isset($dm_dat)){
			setcookie('demo', implode(',', $dm_dat).','.$key, time()+999);
		}
		else{
			setcookie('demo', $key, time()+999);
		}
	}
}

#################
# Startup:
# include function files & set various things
#################
#error_reporting(E_ALL); // report all errors

header('Content-Type: text/html; charset=UTF-8', true); // force UTF-8 charset
header('Accept-Charset: UTF-8', true); // send data in UTF-8

require('./fts/lang.de.txt'); // $lang
require('./fts/header.php'); // Start HTML

require('./fts/misc.php'); // timer, debug(), error()
require('./fts/utf8.php'); // all functions handling special chars

timer_start(); // start timer

$text_copy = ''; // used for <textarea>

// Punctuation + other things that can't 
// be a part of a word
// &, # and ; used in HTML entities!
$points = array('.', ',', '&#34;', '\'', "\r", "\n", '&#60;', '&#62;', '=', '&#160;', '/', '\\', '!', '?', '&#38;', '&#171;', '&#187;', '&#8250;', '&#8249;');

#################
# Text is submitted
#################
if((isset($_POST['text']) && strlen(trim($_POST['text'])) > 2) || $do_demo){ // 3 letters = absolute minimum

	if(!$do_demo){
		$text = $_POST['text'];

		if(get_magic_quotes_gpc()){ // undo pesky magic quotes. Info:
			$text = stripslashes($text); // -> http://bit.ly/4nI1md
		}

		// turn non-ASCII char's into HTML entities
		$utf8_error = FALSE;
		$text = utf8_HtmlEntities($text);

		if($utf8_error){
			error($lang['error_utf']); // fail if data was not sent in UTF-8
		}
	}

	// Easter egg
	if(preg_match('/^[^a-z]{0,3}h(a|e)llo[^a-z]{0,5}$/i', $text)){
		die('<h2>'.htmlentities($text).'</h2> Wie gehts? :3');
	}

	if(strlen($text) > 20000){
		$text = substr($text, 0, 20000);
		for($i = -6; $i < 0; $i++){ // be sure we don't split in the middle of an entity
			if(substr($text, $i, 1) == '&'){
				$start = $i; $entity_done = FALSE;
				for($i += 1; $i < 0; $i++){
					if(preg_match('/^(#|[0-9])$/', substr($text, $i, 1))){
						continue;
					}
					if(substr($text, $i, 1) == ';' && 0 > $i){
						$entity_done = TRUE;
						break;
					}
				}
			}
		}

		if(isset($entity_done) && !$entity_done){
			$text = substr($text, 0, $start);
		}
	}
	$text_copy = $text;

	// Turkish I -> dotless i
	if(strpos($text, 'I') !== false){
		$text_tr = utf8_StrToLower(str_replace('I', '&#305;', $text));
	}
	$text = utf8_StrToLower($text); // text all in lowercase

	// check for length
	// minimum of 3 words.
	$words = str_replace($points, ' ', $text);
	$words = preg_split('/([^a-z0-9\'&\#;])/', $words, NULL, PREG_SPLIT_NO_EMPTY);
	$amt_words = 0;
	foreach($words as $word){
		if(!preg_match('/(&#[0-9]{2,4};|[a-z])+/', $word)){
			continue;
		}
		$amt_words++;
		if($amt_words > 12){ break; }
	}

	// at least 3 words needed for Latin/Cyr. alphabets
	// Since CJK can be entered, we throw an error after alphabet detection

#################
# Edit text so that it's usable
#################
// clear $clean_text of any punctuation marks and spaces
// $points should only contain symbols that are not part of a word 
// and that leave words distinguishable! It is also used farther below
// Characters used for entities (&, #, [0-9] and ;) cannot be removed!

$clean_text = str_replace($points, '', $text);
$clean_text = str_replace(' ', '', $text);

#################
# Detect alphabet
#################
/* HTML entity ranges:
	Latin: 192 - 382
	Greek #1: 880 - 1023 \994-1007
	Cyrillic: 1024 - 1119
	Hebrew #1: 1424 - 1530
	Arabic: 1536 - 1791
	Tamil: 2944 - 3071
	Thai: 3585 - 3675 \3647
	Georgian: 4304 - 4336
	Greek #2: 7936 - 8185
	Japanese: 12353 - 16143
	Chinese: 19968 - 40869
	Korean: 44032 - 55203
	Hebrew #2: 64285 - 64335
*/

// arrays are useful for sorting
$alph = array(
'lat' => 0,
'cyr' => 0,
'el' => 0, //= Greek
'he' => 0,
'ar' => 0,
'ta' => 0,
'th' => 0,
'ka' => 0, //=Georgian
'ja' => 0,
'zh' => 0,
'ko' => 0,
'unk' => 0, //unknown
);

$all = 0;

// Only check for non-Latin alphabets if the probability is high
// Easy: Remove a-z in the text and divide w/ normal text length.
// if result < 1/2 it's the Latin alphabet; no check needed


if((utf8_StrLen(preg_replace('/[a-z]/i', '', $clean_text))/utf8_StrLen($clean_text)) > 0.5){

for($i = 0; $i < strlen($clean_text); $i++){ // go through each letter

	if(preg_match('/[a-z]/', $clean_text[$i])){
		$alph['lat']++;
	}
	else if($clean_text[$i] == '&' && $clean_text[($i+1)] == '#'){ // detect HTML entity
		$i += 2; $number = '';
		while(preg_match('/[0-9]/', $clean_text[$i])){ // fetch entity number
			$number .= $clean_text[$i];
			$i++;
		}
		$number = (int)$number;


		// check if number matches a range:
		if(192 <= $number && $number <= 382){
			$alph['lat']++;
		}
		elseif(880 <= $number && $number <= 1023 && !(994 <= $number && $number <= 1007)){
			$alph['el']++;
		}
		elseif(1024 <= $number && $number <= 1119){
			$alph['cyr']++;
		}
		elseif(1424 <= $number && $number <= 1530){
			$alph['he']++;
		}
		elseif(1536 <= $number && $number <= 1791){
			$alph['ar']++;
		}
		elseif(2944 <= $number && $number <= 3071){
			$alph['ta']++;
		}
		elseif(3585 <= $number && $number <= 3675 && !(3647 == $number)){
			$alph['th']++;
		}
		elseif(4304 <= $number && $number <= 4336){
			$alph['ka']++;
		}
		elseif(7936 <= $number && $number <= 8185){
			$alph['el']++;
		}
		elseif(12353 <= $number && $number <= 16143){
			$alph['ja']++;
		}
		elseif(19968 <= $number && $number <= 40869){
			$alph['zh']++;
		}
		elseif(44032 <= $number && $number <= 55203){
			$alph['ko']++;
		}
		elseif(64285 <= $number && $number <= 64335){
			$alph['he']++;
		}
		else{ debug($i.': '.$number); $alph['unk']++; }

		$all++;
	}
}

// determine alphabet
// Problem: JP uses CN chars
if($alph['ja'] > 0){
	$alph['ja'] += $alph['zh'];
}

arsort($alph);
reset($alph);

debug(print_r($alph, true));

// Be sure that the result is clear
if(key($alph) == 'unk' || (current($alph) / $all) < 0.6){
	error($lang['unknown']);
}
else{
	$alph_used = key($alph);
}

} // end of if(... > 1/2)
else{
	$alph_used = 'lat';
}


#################
# Latin/Hebrew alphabet
#################
if(in_array($alph_used, array('lat', 'he', 'cyr', 'ar'))){
	if($amt_words < 3){
		error($lang['short']);
	}

	if($alph_used == 'lat'){
		require('./data/lat_accents.php'); // load latin accents file
		$accent_once = $accents; // copy of $accents to unset(), alternative method to check

		// Possible languages:
		$lang_return = array('cs', 'da', 'de', 'en', 'es', 'fi', 'fr', 'hr', 'hu', 'is', 'it', 'lt', 'nl', 'nob', 'pl', 'pt', 'sk', 'sv', 'sq', 'tr');
	}
	elseif($alph_used == 'cyr'){
		require('./data/cyr_accents.php'); // not all cyr langs use the same letters
		$accent_once = $accents; 

		// Possible languages:
		$lang_return = array('bg', 'ru', 'uk');
	}
	elseif($alph_used == 'he'){
		$lang_return = array('he', 'yi');
	}
	elseif($alph_used == 'ar'){
		require('./data/ar_accents.php');
		$accent_once = $accents;

		$lang_return = array('ar', 'fa');
	}

	$lang_return = array_fill_keys($lang_return, 0);
	$lang_once = $lang_return; // copy for alt. check


	if($alph_used != 'he'){

	// Go through text for accents and increase the probability
	// of each language that uses the detected accent
	$all = 0; $all_once = 0;
	for($i = 0; $i < strlen($clean_text); $i++){ // go through text char by char

		if($clean_text[$i] == '&' && $clean_text[($i+1)] == '#'){ // HTML entity detected
			$number = '';
			$i += 2;
			while(preg_match('/[0-9]/', $clean_text[$i])){ // fetch number
				$number .= $clean_text[$i];
				$i++;
			}

			if(isset($accents[$number])){ // increase probability of languages that use this accent
				$possibilities = explode(',', $accents[$number]);
				$all++;

				foreach($possibilities as $item){
					if(isset($lang_return[$item])){
						$lang_return[$item]++;

						if(isset($accent_once[$number])){
							$lang_once[$item]++;
						}
					}
				}
				if(isset($accent_once[$number])){
					$all_once++;
					unset($accent_once[$number]);
				}
			}
		}
	}
	if(!isset($accent_once['307'])){	// replace ij-ligature if present
		$text = str_replace('&#307;', 'ij', $text);
	}

	arsort($lang_once);
	arsort($lang_return);

	} // End accents for lat/cyr

	##############
	# N-Grams
	# Create N-Grams and compare them with existing language profiles
	##############
	require('./fts/ngram.php'); // create_ngram()

	if(isset($text_tr)){
		create_ngram($text_tr); $tri_tr = $tri;
	}
	create_ngram($text); // returns the 300 most frequent trigrams

	// calculate the distance of each trigram
	$distance = array();
	foreach($lang_return as $language => $value){
		require('./data/'.$language.'.dat'); // n-gram profile for $language ($tri_)
		$distance[$language] = 0; // total distances

		if($language == 'tr' && isset($tri_tr)){
			$tri_normal = $tri;
			$tri = $tri_tr;
		}
		foreach($tri as $name => $freq){
			if(!isset($tri_[$name])){ $distance[$language] += 300; } // maximum distance is 300
			else{
				$temp_distance = ($tri[$name] - $tri_[$name]);
				if($temp_distance < 0){ $temp_distance = 0 - $temp_distance; } // distance always positive
				$distance[$language] += $temp_distance;
			}
		}
		if(isset($tri_normal)){
			$tri = $tri_normal;
			unset($tri_normal);
		}
		$distance[$language] = 1 - $distance[$language]/(count($tri)*300); // similarity 
	}
	arsort($distance);

	##############
	# Short words
	##############
	/* The idea here is to have some confirmation that it's really that language
	The problem w/ similar n-grams is that they're very probably similar lang's
	Thus, they might have some words that are written the same (albeit different in meaning)
	Therefore, 'tis not a reliable check :( */
	// Split the text into its words
	$text_words = str_replace($points, ' ', $text);
	$text_words = preg_replace('/([^0-9]);+/', '\\1 ', $text_words);
	$text_words = preg_replace('/([^&])#+/', '\\1 ', $text_words);
	$text_words = preg_split('/([^a-z0-9\'&\#;])/', $text_words, NULL, PREG_SPLIT_NO_EMPTY);

	// load lists & check if words are in lists
	$count_words = array(); $all = 0;
	foreach($distance as $language => $lang_dist){
		require('./data/'.$language.'_words.dat'); // include list of short words ($words)
		$count_words[$language] = 0;

		foreach($text_words as $word){
			if(isset($words[$word])){
				$count_words[$language]++;
				$all++;
			}
		}
	}
	arsort($count_words);

	if($all > 0){
		foreach($count_words as $language => $freq){
			$count_words[$language] = $freq;
		}
	}
	reset($count_words); // foreach does not reset the array pointer

	##############
	# Evaluation
	##############
	/* Variables:
	* Accents: $lang_return + $lang_once
	* N-Gram: $distance
	* Short words: $count_words
	* Multiple lang's: (bool) $not_sure ? $possibles
	*/

	$result = array(); // Final results

	// n-gram distance is the most important
	foreach($distance as $language => $number){
		if($amt_words < 5){
			$result[$language] = pow($number, 0.3);
		}
		else{
			$result[$language] = pow($number, 3);
		}
	}

	reset($distance);
	$ngram = key($distance); // save current for comparison 

	// words
	$smallest = 0;
	if(current($count_words) > 0){ // if there is data
		foreach($count_words as $language => $freq){
			if($freq == 0){
				$freq = $smallest*0.95; // already makes a huge difference
			}
			else{ $smallest = $freq; } // OK, since $count_words has been arsort()ed

			if($amt_words < 5){
				$result[$language] *= pow($freq, 3);
			}
			else{
				$result[$language] *= pow($freq, 0.3);
			}
		}
	}

	// accents
	// using $lang_once instead of $lang_return
	// because I guess it's better. 
	if(current($lang_once) > 0){
		$smallest = 0;
		foreach($lang_once as $language => $freq){
			if($freq == 0){
				$freq = $smallest*0.9;
			}
			else{	$smallest = $freq; }

			$result[$language] *= pow($freq, 0.3);
		}
	}

	arsort($result);
	reset($result); // pointer to #1

	// Language conflicts
	//1 Da/nob/sv
	if(preg_match('/^(da|nob|sv)$/', key($result))){
		include('./fts/scand.php');
	}

	//2 Cs/sk
	if(preg_match('/^(cs|sk)$/', key($result))){
		$go = array('cs' => 0, 'sk' => 0);
		$go['sk'] += $lang_return['sk'];
		$go['cs'] += $lang_return['cs'];
		arsort($go);
		if(current($go) > next($go)){
			reset($go);
			$go[key($go)] = current($go) - next($go);
			$go[key($go)] = 0;
		}
		else{
			$go['sk'] = $go['cs'] = 0;
		}
		str_replace(array('iu', 'dz', 'd&#382;'), '', $text, $count_sk);
		$go['sk'] += $count_sk;

		foreach($text_words as $word){
			if(preg_match('/^.*(cia|dlo|om)$/', $word)){
				$go['sk']++;
			}
			elseif(preg_match('/^.*(c(i)?e|tko|em)$/', $word)){
				$go['cs']++;
			}
		}

		if($count_words['sk'] - $count_words['cs'] != 0){
			if($count_words['sk'] > $count_words['cs']){
				$go['sk'] += ($count_words['sk'] - $count_words['cs']);
			}
			else{
				$go['cs'] += ($count_words['cs'] - $count_words['sk']);
			}
		}
	}

	if(!isset($possibles)){
		$possibles = array();
	}

	arsort($result);
	// check if >1 languages are probable
	$first = current($result);
	next($result); // move pointer to next element (#2)
	$not_sure = FALSE; // We sure about the end result?

	if(($first - current($result)) < ($first / 33)){
		debug('Various languages probable');
		$scand_passed = FALSE; // so we don't put all scand lang's in $possibles again

		$not_sure = TRUE; // we not sure
		foreach($result as $language => $prob){ // no need to reset();
			if(($first - $prob) < ($first / 33)){
				if(preg_match('/^(da|nob|sv)$/', $language)){
					if($scand_passed){ continue; }
					else{ $scand_passed = TRUE; }
				}

				debug('<b>'.$language.'</b>: '.$prob.'; diff: '.round($first - $prob, 5));
				$possibles[] = $language;
			}
			else{ break; }
		}
		if($scand_passed && count($possibles) == 1){ $possibles = array(); $not_sure = FALSE; }
	} 

	// check if the most probable language still is the one determined 
	// solely by n-gram comparison
	reset($result);
	if(key($result) != $ngram && $amt_words > 7){
		debug('Multiple lang\'s: '.key($result).' (current) vs. '.$ngram.' (ngram)');
		if(!$not_sure){
			$not_sure = TRUE;
			$possibles[] = key($result);
			$possibles[] = $ngram;
		}
		else{
			if(!in_array($ngram, $possibles)){
				$possibles[] = $ngram;
			}
		}
	}
	arsort($result); reset($result);

# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	# debug
	$dg = '</td><td><pre>';
	debug('
<table border=1 style="font-size: 8pt">
<tr><td>$lang_once</td><td>'.nl2br(print_r($lang_once, true)).'</td></tr>
<tr><td>$distance</td><td>'.nl2br(print_r($distance, true)).'</td></tr>
<tr><td>$count_words</td><td>'.nl2br(print_r($count_words, true)).'</td></tr>
<tr><td>$result</td><td>'.nl2br(print_r($result, true)).'</td></tr>
</table>');
	if($not_sure){
		debug('$possibles: '.print_r($possibles, true));
	}
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

}
elseif(preg_match('/^(el|ja|ko|ta|zh|ka|th)$/', $alph_used)){
	$not_sure = FALSE;

	if($alph['ja'] > 0){
		$alph['ja'] -= $alph['zh'];
	}

	$all_possibs = array('el', 'ja', 'zh', 'ko', 'ta', 'ka', 'th');

	arsort($alph); reset($alph);
	if(!preg_match('/^(zh|ja)$/', $alph_used)){
		$result = array($alph_used => 1);
	}
	else if($alph['zh'] == 0 || ($alph['ja'] / $alph['zh']) > 0.4){
		$result = array('ja' => 1);
	}
	else{
		if($alph['ja'] / $alph['zh'] > 1/7){
			$result = array('ja' => 1, 'zh' => 0.9);
			$possibles = array('ja', 'zh');
			$not_sure = TRUE;
		}
		else{
			$result = array('zh' => 1);
		}
	}

	// add all langs that are missing to $result
	foreach($all_possibs as $possibility){
		if(!isset($result[$possibility])){
			$result[$possibility] = 0.1;
		}
	}

}


##############
# Output evaluation result
##############
echo '<h2>'.$lang['result'].'</h2>
 <p>';
// scand. langs
if(isset($scand_unsure) && $scand_unsure){
	if(isset($possibles) && count($possibles) > 3){
		$scand_unsure = FALSE; // trigger the $lang['undef'] msg
	}

	if(!$not_sure){
		$not_sure = TRUE;
	}

	if(count($possibles) == 1){ //is this block necessary?
		$not_sure = FALSE;
		$possibles = array();
		$scand_unsure = FALSE;
	}
}

if($not_sure){
	// sort all possible lang's according to $result
	$probs = array();
	foreach($possibles as $key => $langname){
		$probs[$langname] = $result[$langname];
	}
	arsort($probs);
	$possibles = array();
	foreach($probs as $langname => $key){
		$possibles[] = $langname;
	}
}

if($not_sure && count($possibles) > 3 && (!isset($scand_unsure) || !$scand_unsure)){ // if more than 3 lang's probable
	echo $lang['undef'];
}
else{
	if(current($result) < 0.001){
		$not_sure = TRUE; //bug fix
		echo $lang['undef'];
	}
	else if($not_sure){
		$msg = explode('[x]', $lang['multiple']);
		echo $msg[0];
		$count = 1;

		foreach($possibles as $item){
			if($count == 1){ echo '<b class="langinfo" onclick="animatedcollapse.toggle(\'box1\')">'.$lang[$item].' <img src="./img/info.png" alt="" /></b> '; }
			else{ echo '<span class="langinfo" onclick="animatedcollapse.toggle(\'box'.$count.'\')">'.$lang[$item].' <img src="./img/info.png" alt="" /></span>'; }

			if($count+1 < count($possibles)){
				echo ', ';
			}
			elseif($count+1 == count($possibles)){
				echo $lang['or']; 
			}
			$count++;
		}
		echo $msg[1];
		if(isset($scand_unsure) && $scand_unsure){ echo '<br />'.$lang['scand']; }
		if(in_array('sk', $possibles) || in_array('cs', $possibles)){
			echo '<br />'.$lang['skcs'];
		}
	}
	else{ //we're sure
		$result_ = key($result); // we still need $result for details
		if(preg_match('/^(da|nob|sv)$/', $result_)){ // scand. lang's
			reset($do);
			$result_ = key($do);
		}
		echo str_replace('[x]', '<b class="langinfo" onclick="animatedcollapse.toggle(\'box1\')">'.$lang[$result_].' <img src="./img/info.png" alt="" /></b>', $lang['result_info']);
		if(preg_match('/^(sk|cs)$/', $result_)){
			echo '<br />'.$lang['skcs'];
		}
		if($result_ == 'hr'){
			echo '<br />'.$lang['BKS_info'];
		}
		if(isset($all_possibs)){ //indicates identification based on writing script
			echo '<br />'.$lang['writing_only'];
		}
	}

	// code for lang boxes (containing lang info)
	include('langbox.php');
	if(!$not_sure){
		$possibles = array($result_);
	}

	echo "</p>\r";
	$box = 1;

	foreach($possibles as $item){
		$say = $demo[$item];
		echo "\r".' <div class="box" id="box'.$box.'" style="display: '.
(($box == 1 && isset($_COOKIE['box1']) && $_COOKIE['box1']) ? "block" : "none")
.'">
  <table class="box">
   <tr>
    <td>
     <b class="title">'.$lang[$item].'</b>
     <br />'.$lang['box_name'].': '.$say['name'].'
     <br />'.$lang['box_ppl'].': '.$say['ppl'].'
     <br />'.$lang['box_news'].': <a href="http://'.$say['news'].'">'.$say['news'].'</a>
     <br />'.$lang['box_wiki'].': <a href="http://'.$say['wiki'].'">'.$lang['here'].'</a>
     <br />'.$lang['box_yt'].': <a href="http://youtube.com/watch?v='.$say['yt'].'">Video</a>
    </td>
    <td class="close">
     <a href="javascript:animatedcollapse.hide(\'box'.$box.'\')"><img src="./img/close.png" alt="Close" /></a>
    </td>
   </tr>
  </table>
 </div>';
		$box++;
	}
}


#############
# DETAILS section
#############
function helpimg($helpid){
	global $lang;
	return '<a href="help.php?'.$helpid.'" onclick="vindov(\''.$helpid.'\'); return false"><img src="./img/help.png" alt="Infos" title="'.$lang['explanations'].'" /></a>';
}


echo "\r ".'<div class="details" id="details" style="display: '.((isset($_COOKIE['details']) && $_COOKIE['details']) ? 'block' : 'none').'">
  <table class="details">';

// Asian stuff
if(preg_match('/^(el|ko|ja|ta|zh|ka|th)$/', $alph_used)){
	echo '   <tr><td class="title" colspan="3">'.$lang['det_CJK'].helpimg('scripts').'</td></tr>
   <tr><td class="scand" colspan="3">'.$lang['det_CJK_explain'].'</td></tr>';

	reset($result); $first = $alph[key($result)];
	if($not_sure){ $first = $alph['zh']; } // bug fix

	foreach($result as $CJK => $val){
		echo "\r   <tr><td class='derc'>".$lang[$CJK].'</td><td>'.$alph[$CJK].'</td><td><img src="./img/bar.png" alt="" style="width: '.round((150 / $first) * $alph[$CJK]).'px" /></td></tr>';
	}

}	
// LAT/CYR ALPHABET
else if(preg_match('/^(cyr|he|lat|ar)$/', $alph_used)){
	echo '   <tr><td class="title" colspan="3">'.$lang['det_ngram'].helpimg('ngram').'</td></tr>';

	$first = reset($distance);
	if($first == 0){
		echo "\r   <tr><td colspan='3'>".$lang['det_ngram_fail']."</td></tr>";
	}
	else{
		for($i = 0; $i < 3; $i++){
			$value = current($distance);
			if($value == 0){ break; }
			echo "\r    <tr><td class='derc'>".$lang[key($distance)].'</td><td>'.round(current($distance) * 100, 1).'%</td><td><img src="./img/bar.png" alt="" style="width: '.round((150 / $first) * current($distance)).'px" /></td></tr>';
			next($distance);
		}
	}

	echo "\r  ".'<tr><td class="title" colspan="3">'.$lang['det_words'].helpimg('words').'</td></tr>';
	$first = reset($count_words);
	if($first == 0){ echo "\r   <tr><td colspan='3'>{$lang['det_words_fail']}</td></tr>"; }
	else{
		$group = 1; $save = $first;
		for($i = 0; $i < 10; $i++){
			if(current($count_words) < $save){
				$group++;
				$save = current($count_words);
				if($save == 0 || $group > 3 || $i > 2){
					break;
				}
			}
			echo "\r   <tr><td class='derc'>{$lang[key($count_words)]}</td><td style='text-align: center'>".current($count_words).'</td><td><img src="./img/bar.png" alt="" style="width: '.round((150 / $first) * current($count_words)).'px" /></td></tr>';
			next($count_words);
		}
	}

	if(preg_match('/^(cyr|lat|ar)$/', $alph_used)){ // No special chars for hebrew
		if($alph_used == 'lat'){ $helpid = 'chars'; }
		else{ $helpid = 'cyrillic'; }
		echo "\r  ".'<tr><td class="title" colspan="3">'.$lang['det_chars_'.$alph_used].helpimg($helpid).'</td></tr>';
		$first = reset($lang_once);
		if($first == 0){ echo "\r  <tr><td colspan='3'>".$lang['det_chars_fail_'.$alph_used].'</td></tr>'; }
		else{
			$group = 1; $save = $first;
			for($i = 0; $i < 10; $i++){
				if(current($lang_once) < $save){
					$group++;
					$save = current($lang_once);
					if($save == 0 || $group > 3 || $i > 2){
						break;
					}
				}
				echo "\r   <tr><td class='derc'>{$lang[key($lang_once)]}</td><td style='text-align: center'>".current($lang_once).'</td><td><img src="./img/bar.png" alt="" style="width: '.round((150 / $first) * current($lang_once)).'px" /></td></tr>';
				next($lang_once);
			}
		}
	}

	if((isset($result_) && preg_match('/^(da|nob|sv)$/', $result_)) || (isset($scand_unsure) && $scand_unsure)){
		echo "\r  ".'<tr><td class="title" colspan="3">'.$lang['det_scand'].helpimg('similar').'</td></tr>
  <tr><td colspan="3" class="scand">'.$lang['det_scand_explain'].'</td></tr>';

		arsort($do);
		$first = reset($do);
		if($first == 0){ $first = 1; } // quick fix

		foreach($do as $key => $val){
			echo "\r   ".'<tr><td class="derc">'.$lang[$key].'</td><td style="text-align: center">'.$val.'</td><td><img src="./img/bar.png" alt="" style="width: '.round((150 / $first) * $val).'px" /></td></tr>';
		}
	}
}
	
	echo "\r  </table>
 </div>";

echo '<div class="detailslink"><a href="javascript:animatedcollapse.toggle(\'details\')">'.$lang['show_details'].'</a></div>';


// ~~~~~~~~~~~~~~~~~~~~~~
// end of LID
}
elseif(isset($_GET['info'])){
	include('info.php'); // Show some info
}
else{
	echo $lang['hello']; // intro txt
}


###############
# SUBMIT FORM
###############
echo "\r\r<h2>".$lang['enter_text'].'</h2>
 <form action="'.$_SERVER['PHP_SELF'].'#content" method="post" accept-charset="UTF-8">
  <table class="form">
   <tr>
    <td colspan="2">
	<!-- TEXT -->
	<textarea name="text" cols="70" rows="10" tabindex="1">'.$text_copy.'</textarea>
	<!-- END TEXT -->
    </td>
   </tr>
   <tr>
    <td style="text-align: left; width: 30%">
     <input tabindex="3" type="submit" name="demo" value="Demo" title="Beispielstext" class="demo" />
    </td>
    <td style="text-align: right; padding-right: 7px">
     <input type="submit" tabindex="2" value="'.$lang['lang_determine'].'" />
    </td>
   </tr>
  </table>
 </form>
 <div id="footer">
  <p class="time">'.$lang['time'].' '.timer_eval(); ?> s</p><br />
  <p class="info"> <a href="?info">Informationen</a></p>
 </div>

  </div>
  </div>
 </body>
</html>