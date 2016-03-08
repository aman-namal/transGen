<?php

$aryTest = array();
$aryTest['3.COM.2014-5'] = 'Year 3 grades';
$aryTest['2.COM.2013-4'] = 'Year 2 grades';
$aryTest['1.COM.2012-3'] = 'Year 1 grades';
$aryTest['0.Fnd.2011-2'] = 'Foundation year grades';

ksort($aryTest);
var_dump($aryTest);
?>