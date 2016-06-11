<?php
$go = array('da' => 0, 'nob' => 0, 'sv' => 0);
foreach($text_words as $word){
	if($word == 'ikke' || $word == 'og' || $word == 'ogs&#229;' || $word == 'er' || $word == 'at' || strpos($word, '&#216;') !== false || strpos($word, '&#230;') !== false){
		$go['da']++;
		$go['nob']++;
	}
	elseif($word == 'icke' || $word == 'inte' || $word == 'och' || $word == 'ocks&#229;' || $word == '&#228;r' || $word == 'att' || strpos($word, '&#246;') !== false || strpos($word, '&#228;') !== false){
		$go['sv']++;
	}
}
arsort($go);
reset($go);

if($go['da'] != $go['sv']){
	if(current($go) > 0){
		$sure = key($go);
	}
}

$go = array('da' => 0, 'nob' => 0);
$sv_cnt = 0; // set separate because we don't always want it

if(!isset($sure) || $sure != 'sv'){
	foreach($text_words as $word){
		if($word == '&#229;'){ //aring
			$go['nob']++;
		}
		elseif($word == 'ett' || $word == 'av'){
			$sv_cnt++;
			$go['nob']++;
		}
		elseif($word == '&#233;t' || $word == 'af'){
			$go['da']++;
		}
		elseif($word == '&#242;g'){
			$go['nob']++;
		}#
		if(strpos($word, 'ej') !== false || strpos($word, '&#248;j') !== false){
			$go['da']++;
		}
		elseif(strpos($word, 'ei') !== false || strpos($word, '&#248;y') !== false){
			$go['nob']++;
		}#
		if(preg_match('/.*(ss|nn)$/', $word)){
			$go['nob']++;
		}#
		if(preg_match('/^(g|k)j(e|&#230;|&#248;|o|a)/', $word)){
			$go['nob']++;
		}#
		if(preg_match('/ds/', $word)){
			//$go['da'] += 0.5;
		}#
		if(preg_match('/^(d|m|s)eg$/', $word)){
			$go['nob']++;
		}
		elseif(preg_match('/^(d|m|s)ig$/', $word)){
			$go['da']++; $sv_cnt++;
		}
	}
}

if(isset($sure) && $sure == 'sv'){
	$do = array('sv' => 1, 'da' => 0, 'nob' => 0);
}
else{
	arsort($go); reset($go);
	$high = key($go);
	if(isset($sure)){
		if(($go[$high] - next($go)) > 0){
			$do = array($high => 1, key($go) => 0, 'sv' => 0);
		}
		else{
			end($go);
			$do = array('da' => 1, 'nob' => 1, 'sv' => 0);
		}
	}
	else{
		if($sv_cnt > current($go)){
			$do = array('sv' => 1, 'da' => 0, 'nob' => 0);
		}
		elseif(($go[$high] - end($go)) > 0){
			if($sv_cnt == $go[$high]){
				$do = array($high => 1, 'sv' => 1, key($go) => 0);
			}
			else{
				$do = array($high => 1, key($go) => 0, 'sv' => 0);
			}
		}
		else{
			$do = array('da' => 0, 'nob' => 0, 'sv' => 0);
		}
	}
}
arsort($do);

debug('$do: '.print_r($do, true));

// special N-grams
if(count($tri) > 30 && key($do) != 'sv'){
	require('./data/scand.dat');
	$ngram_special = array('da' => 0, 'nob' => 0);
	foreach($tri as $key => $rank){
		if(isset($scand[$key])){
			if(preg_match('/^[0-9]{1,3}(d|n)$/', $scand[$key])){
				$position = preg_replace('/^([0-9]{1,3})(d|n)$/', '\\1', $scand[$key]);
				$factor = pow(1.01, (186 - $position)); // 1.01^166 ~= 5

				if(preg_replace('/^[0-9]{1,3}(d|n)$/', '\\1', $scand[$key]) == 'd'){
					$ngram_special['da'] += $factor;
				}
				else{
					$ngram_special['nob'] += $factor;
				}
			}
			else{
				$pos_da = explode(',', $scand[$key]);
				$pos_no = $pos_da[1]; $pos_da = $pos_da[0];
				$differentie = $pos_no - $pos_da; if($differentie < 0){ $differentie *= -1; }
				$factor = pow(1.0127, $differentie-20); // 1.0127^(148-20) = 5

				$pos_da -= $rank; if($pos_da < 0){ $pos_da *= -1; }
				$pos_no -= $rank; if($pos_no < 0){ $pos_no *= -1; }

				$differentie = $pos_da - $pos_no; if($differentie < 0){ $differentie *= -1; }

				if($differentie < 10){ continue; }

				$factor = sqrt($factor * pow(pow(5, 1/50), $differentie));
				if($factor > 6){ $factor = 6; }

				if($pos_da > $pos_no){
					$ngram_special['nob'] += $factor;
				}
				else{
					$ngram_special['da'] += $factor;
				}
			}
		}
	}
	debug('<pre>'.var_export($ngram_special, true).'</pre>');

	arsort($ngram_special);
	$first_lang = current($ngram_special);

	if((next($ngram_special) > 0 && ($first_lang/current($ngram_special)) > 1.5) || 
		(current($ngram_special) == 0 && $first_lang > 1.5)){

		reset($ngram_special);
		if($do['sv'] == current($ngram_special)){
			$save_key = key($ngram_special); // be sure that sv doesn't get a disadvantage
		}
		$do[key($ngram_special)]++;
		if(isset($save_key)){
			$do['sv']++;
		}
	}
	debug('$do: '.print_r($do, true));
}

$max = 0; $min = 'f';
$lang_max = $lang_min = '';
foreach($do as $key => $value){
	if($count_words[$key] > $max){
		$max = $count_words[$key];
		$lang_max = $key;
	}
	if($count_words[$key] < $min || $min == 'f'){
		$min = $count_words[$key];
		$lang_min = $key;
	}
}
if($max - $min > 0){
	$last_key = str_replace(array($lang_min, $lang_max), '', 'danobsv'); // little trick up my sleeve :3
	$do[$lang_max]++;
	if($count_words[$last_key] == $max){
		$do[$last_key]++;
	}
}
debug('$do: '.print_r($do, true));
arsort($do);

$possibles = array();
$max = current($do);
foreach($do as $key => $val){
	if($max == $val){
		$possibles[] = $key;
	}
}
if(count($possibles) > 1){
	$not_sure = $scand_unsure = TRUE;
}
else{
	reset($do);
	$scand_sure = key($do); // used for $result output
	$possibles = array();
}

if(isset($scand_unsure) && $scand_unsure){
	$save_words = array();
	foreach($do as $key => $val){
		$save_words[$key] = $count_words[$key];
	}
	arsort($save_words);
	if((reset($save_words) - next($save_words)) > 1){
		reset($save_words);
		$do[key($save_words)]++;
	}

	arsort($do);
	reset($do);
	$possibles = array();
	$max = current($do);
	foreach($do as $key => $val){
		if($max == $val){
			$possibles[] = $key;
		}
	}
	if(count($possibles) == 1){
		reset($do);
		$scand_sure = key($do);
		$possibles = array();
		$scand_unsure = $not_sure = FALSE;
	}
}
?>