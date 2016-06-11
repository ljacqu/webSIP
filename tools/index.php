<?php
if(isset($_GET['menu'])){
?>
<a href="Analysis.php">Submit Data</a>
<br /><a href="N-Gram Generation.php">N-Gram Generation</a>
<br /><a href="Entity Table.php">Entity Table</a>
<br /><a href="Words.php">Words Evaluation</a>
<br /><a href="Pronouns_add.php">Add Pronouns</a>
<br /><a href="Fix_Ngram.php">Export N-Gram</a>
<br /><a href="Archive.php">Language Archives</a>
<br /><a href="Data_view.php">View Data</a>
<br /><a href="asian_count.php">Asian Languages</a>
<?php
exit;
}
if(isset($_GET['frame'])){
	$letter = array(0 => 'c', 1 => 'd', 2 => 'e', 3 => 'f', 4 => 'b', 5 => 'a');
	$color = '';
	for($i = 0; $i < 6; $i++){
		srand();
		$color .= $letter[rand(0,5)];
	}

echo '<body bgcolor="'.$color.'" style="margin: 3px; font-family: Verdana; font-size: 10pt">';
?>
<base target="big"><center>
<a href="Analysis.php">Submit Data</a> &nbsp;
<a href="N-Gram Generation.php">N-Gram Generation</a> &nbsp;
<a href="Entity Table.php">Entity Table</a> &nbsp;
<a href="Words.php">Words Evaluation</a> &nbsp;
<a href="Pronouns_add.php">Add Pronouns</a> &nbsp;
<a href="Fix_Ngram.php">Export N-Gram</a> &nbsp;
<a href="Archive.php">Archives</a> &nbsp;
<a href="Data_view.php">View Data</a> &nbsp;
<a href="asian_count.php">Asian languages</a></center>
<hr onmouseover="location.reload(true)" style="margin-top: 0; height: 5px; width: 20px">
<meta http-equiv="refresh" content="<?php echo rand(10, 120); ?>" />
<?php
exit;
}
?>

<FRAMESET rows="*, 25px" style="border: 0">
 <FRAME src="?menu" name="big" frameborder=0>
  <FRAME src="?frame" frameborder=0>
</Frameset>

