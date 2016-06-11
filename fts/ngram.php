<?php
function create_ngram($text){
	global $i, $one, $bi, $tri, $pointers;
	$one = $bi = $tri = array();
	$pointers = array();
	$i = 0;

	// global $pointers necessary because nextLetter() uses it!

	if(!preg_match('/[a-z&\']/', $text[0])){ $pointers[0] = '_'; $i = 1; }
	else{ $pointers[0] = NextLetter($text); }

$pointers[1] = NextLetter($text);
$pointers[2] = NextLetter($text);

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
	$pointers[2] = NextLetter($text);
}

$till = 2 - (3 - count($pointers));

// finishing properly
for($u = 0; $u < 2; $u++){ // <-- Trick
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

arsort($one);
arsort($bi); // why not
arsort($tri);

#########
// Handle only $tri
// because that's the only thing we use right now
#########

$tri_ = $tri;
$tri = array();

$current = 0; $rang = 0; $count = 1;
foreach($tri_ as $name => $freq){
	if($freq != $current){
		$rang += $count;
		$count = 0;
		$current = $freq;
		if($rang > 300){ break; }
	}

	$tri[$name] = $rang;
	$count++;
}

//---------
}

function NextLetter($text){
	global $i, $pointers;

	if(!isset($text[$i])){ var_dump($text); die('$text['.$i.'] is not set!'); }
	
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
		$last = end($pointers); // with end() instead of $pointers[2], we can use it at the beginning, when $pointers is still empty
		if($last == '_'){
			$i++;
			if($i >= strlen($text)){
				return '';
			}

			while($i < strlen($text) && !preg_match('/[a-z\'&]/', $text[$i])){
				$i++;
			}

			if($i >= strlen($text)){
				return '';
			}
			return NextLetter($text);
		}
		$i++;
		return '_';
	}
}