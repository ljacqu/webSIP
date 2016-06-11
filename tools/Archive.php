<style>
<!--
body { font-family: Verdana; font-size: 10pt; margin: 35px }
h1 { border-bottom: 1px solid #000; font-weight: normal; font-size: 18pt; }
ul { list-style-type: circle; }
-->
</style>
<?php
if(isset($_GET['arch'])){
	do {

	echo '<h1>Archive</h1>';
	$arch = $_GET['arch'];
	if(!preg_match('/^[a-z]{2,4}$/', $arch)){
		echo 'Invalid language param.';
		break;
	}

	if(!file_exists($arch) || (file_exists($arch) && !is_dir($arch))){
		mkdir($arch);
	}

$dir = getcwd();
$success = 0;
if (is_dir($dir)) {
    if ($dh = opendir($dir)) {
        while (($file = readdir($dh)) !== false) {
	if($file != '.' && $file != '..'){
		$ext = preg_replace('/.*\.([a-z]{2,4})$/', '\\1', $file);
		if($ext == 'php'){ continue; }
		if((!isset($_GET['txt']) && $ext == 'dat') || $ext == 'txt'){
			$lang = preg_replace('/^([a-z]{2,4})(_){0,1}.*\.'.$ext.'$/', '\\1', $file);
			if(!$lang){ echo '<li>Error.'; var_dump($lang); var_dump($ext); var_dump($file); echo '</li>'; continue; }
			if($lang == $arch){
				$new_name = './'.$arch.'/'.$file;
				if(file_exists($new_name)){
					echo '<font color="red">WARNING</font> '.$new_name.' already exists!';
					continue;
				}
				rename($file, './'.$arch.'/'.$file);
				$success++;
			}
		}
	}
        }
        closedir($dh);
    } else{ die('OPENDIR FAIL'); }
} else{ die('DIR is not a dir'); }

echo '&raquo; Moved '.$success.' files (lang: '.$arch.')';
	

	
	} while(0);


}


if(isset($_GET['unarch'])){
	echo '<h1>Unarchive</h1>';
	$unarch = $_GET['unarch'];
	do{

	if(!preg_match('/^[a-z]{2,4}$/', $unarch)){
		echo 'Invalid <b>unarch</b> parameter!';
		break;
	}

	$dir = './'.$unarch.'/';

$success = 0;
$all = 0;
if (is_dir($dir)) {
    if ($dh = opendir($dir)) {
        while (($file = readdir($dh)) !== false) {
	if($file != '.' && $file != '..'){
		$all++;
		$ext = preg_replace('/.*\.([a-z]{2,4})$/', '\\1', $file);
		if((!isset($_GET['txt']) && $ext == 'txt') || $ext == 'dat'){
			if(file_exists($file)){
				echo '<font color=red>WARNING</font> File '.$file.' already exists!';
				continue;
			}
			rename('./'.$unarch.'/'.$file, $file);
			$success++;
		}
	}
        }
        closedir($dh);
    } else{ die('OPENDIR FAIL'); }
} else{ print('Invalid directory'); break; }

if($all == $success){
	rmdir($unarch);
}
else{
	if(!isset($_GET['txt'])){
	echo 'Note: Folder not removed -- files remaining therein.<br />';
	}
}
echo '&raquo; Unarchived '.$success.' files.';


	} while(0);


}

$dir = getcwd();
$langs = array();
$folders = array();

// Open a known directory, and proceed to read its contents
if (is_dir($dir)) {
    if ($dh = opendir($dir)) {
        while (($file = readdir($dh)) !== false) {
	if($file != '.' && $file != '..'){
		if(is_dir($file) && preg_match('/^[a-z]{2,4}$/', $file)){
			$folders[$file] = 1;
			continue;
		}
		$ext = preg_replace('/.*\.([a-z]{2,4})$/', '\\1', $file);
		if($ext == 'php'){ continue; }
		if($ext == 'dat' || $ext == 'txt'){
			$lang = preg_replace('/^([a-z]{2,4})(_){0,1}.*\.'.$ext.'$/', '\\1', $file);
			if(!$lang){ echo '<li>Error.'; var_dump($lang); var_dump($ext); var_dump($file); echo '</li>'; continue; }
			if(!isset($langs[$lang])){ $langs[$lang] = 0; }
			$langs[$lang]++;
		}
	}
        }
        closedir($dh);
    } else{ die('OPENDIR FAIL'); }
} else{ die('DIR is not a dir'); }

echo '<h1>Unarchive Language Files</h1><ul>';
foreach($folders as $lang => $s){
	echo '<li><a href="?unarch='.$lang.'">'.$lang.'</a> &nbsp; [<a href="?unarch='.$lang.'&amp;txt">Dat</a>]</li>';
}
echo '</ul>';

echo '<h1>Archive Language Files</h1><ul>';
foreach($langs as $name => $amount){
	echo '<li><a href="?arch='.$name.'">'.$name.'</a> ('.$amount.') [<a href="?arch='.$name.'&amp;txt">Txt</a>]</li>';
}
echo '</ul>';
?>