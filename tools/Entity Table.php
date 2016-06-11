<style type="text/css">
table { font: Verdana 10pt #000; border-collapse: collapse; }
td { padding: 5px; }
</style>
<?php
$start = 200;
$end = 2000;
if(isset($_GET['start'])){
	if(is_numeric($_GET['start']) && is_numeric($_GET['end'])){
		if(($_GET['end'] - $_GET['start']) < 5000){
			$start = (int)$_GET['start'];
			$end = (int)$_GET['end'];
		}
	}
}


echo '<form action="'.$_SERVER['PHP_SELF'].'" method="get">
 Start: <input type="text" name="start" value="'.$start.'" />
<br />End: <input type="text" name="end" value="'.$end.'" />
<br /><input type="submit" value="Go!" />
</form>';

?>

<table border=1>
<?php
$range = '<tr style="font-weight: bold; background-color: #ccc"><td>RANGE</td>';

for($i = 0; $i < 10; $i++){
	$range .= '<td>'.$i.'</td>';
}



$i = $start;
while($i <= $end){
	if($i % 100 == 0){
		echo "\r".$range;
	}
	if($i % 10 == 0){
		echo "\r".'</tr><tr><td>'.substr($i, 0, -1).'</td>';
	}
	echo '<td>&#'.$i.';</td>';
	$i++;
}
?>
</table>