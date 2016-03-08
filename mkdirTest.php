<?php
echo 'http://' . $_SERVER['HTTP_HOST'] . str_replace(basename(__FILE__), '', $_SERVER["REQUEST_URI"]) . '<br />'; 
//echo $_SERVER['HTTP_HOST'];
die();
$strTimestamp = date('Ymd\Thns');
mkdir('data/output/CS/'.$strTimestamp);

die();
if (mkdir('data\\output\\CS')) {
	$handle = opendir('data\\output\\CS');
	
	$i = 0;
	while (false !== ($entry = readdir($handle)) && $i<5) {
		print '<br />' . $entry;
		$i++;
	}
    closedir($handle);
}
?>