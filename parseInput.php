<?php


function extractGradesData($fullFileName, $rollNoColumnIndex, $studentNameColumnIndex, &$yearGrades=array(), &$session = ''){
$handle = fopen($fullFileName, 'r');
//echo 'opened input file';
$passThresholds=0;
$aryModuleCodes=0;
$startModulesIndex = 0;
if ($handle) {
		$i=1;
		while (($data = fgetcsv($handle, 0, ";")) !== false) {
			if(!is_array($passThresholds) && substr($data[0],0, 7)==='Session')
			{
				$session = substr($data[0],8, 7);
				//echo '<br />'.$session.'<br />';
			}
			else if(!is_array($passThresholds) && $data[0]==='Pass Grade')
			{
				//echo 'found passing marks: ' . $data[0]; die();
				//break;
				$passThresholds = array();
				$limit = count($data);
				$intCP = 0; $intP = 0; $aryThresholds = array();
				for($j=0; $j<$limit; $j++){
					if(empty($data[$j]) && $startModulesIndex>0){
						break;
					}
					if(!empty($data[$j]) && $startModulesIndex==0){
						$startModulesIndex = $j;
					}
					$aryThresholds = explode(',', $data[$j]);
					$passThresholds[$j-$startModulesIndex] = $aryThresholds;
				}
				//var_dump($passThresholds); print $j; die();
				$endModulesIndex = $j-1;
				//var_dump($passThresholds); die();
			}
			else if(!is_array($aryModuleCodes) && substr($data[0],0, 4)==='Sr #'){
				//echo 'found start: ' . $data[0]; die();
				$aryModuleCodes = array();
				for($j = $startModulesIndex; $j<=$endModulesIndex; $j++){
					if($passThresholds[$j-$startModulesIndex]>0){
						/* echo '<br /> Module codes consist of 7 characters';
						// echo '<br /> substr($data[$j],7, $start-8) gets you the module name, then remove whitespace ';
						// echo 'around the name including a line break, ';
						// echo '<br /> ECTS credits are enclosed in parentheses'; */
						$moduleCode = str_replace('-', '', substr($data[$j], 0, 7));
						$aryModuleCodes[$j-$startModulesIndex] = $moduleCode;
						$start = strpos($data[$j], '(') + 1;
						$length = strpos($data[$j], ')', $start) - $start;
						$ECTS = substr($data[$j], $start, $length);
						$credits[$moduleCode] = array(trim(substr($data[$j],7, $start-8), '
	 '), $ECTS, null);
					}
				}
				//var_dump($credits);die();
			}
			else if(is_array($aryModuleCodes) && $data[0]==='')
			{
				//end of data collection
				break;
			}
			else if(is_array($aryModuleCodes)){
				//start collecting student grade data as an associative
				// array, indexed by student UoB no.
				$rollNo = $data[$rollNoColumnIndex];
				// echo "<br /> collect grades for " . $rollNo;
				// echo '<br /> grades and pass/fail decision indexed by module name';
				//print 'current line of data: '; var_dump($data);
				for($j=$startModulesIndex; $j<=$endModulesIndex; $j++){
					if($passThresholds[$j-$startModulesIndex] > 0 && !empty($data[$j]) && $data[$j]!='A' && $data[$j]!='N/A' && $data[$j]>0){
						if($data[$j]<$passThresholds[$j-$startModulesIndex][0]){
							$passFail = 'F';
						}
						else if($data[$j]<$passThresholds[$j-$startModulesIndex][1]){
							$passFail = 'CP';
						}
						else{
							$passFail = 'P';
						}
						$credits[$aryModuleCodes[$j-$startModulesIndex]][2] = array($data[$j], $passFail);
					}
				}
				//echo "<br /> store module info, grades, overall BoE decision for ". $rollNo . " as well as the student's name";
				$yearGrades[$rollNo][0] = $credits;
				$yearGrades[$rollNo][1] = $data[count($data)-1];
				$yearGrades[$rollNo][2] = $data[$studentNameColumnIndex];
				//var_dump($credits);
				//print 'hello';
				//die();
			}
/* 			$num = count($data);
			echo "<p> $num fields in line $i: <br /></p>\n";
			for ($c=0; $c < $num; $c++) {
				echo $data[$c] . "<br />\n";
			}
 */	
			$i++;
		}
		fclose($handle);
	}
	//$passThresholds = array_pop($year0Data);
	//print 'modules:<br />';
	//var_dump($aryModuleCodes);
	//print 'passing marks:<br />';
	//var_dump($passThresholds);
	//print 'Year 0 grades:<br />';
	//var_dump($yearGrades);
	//die();
}
/*
define('YEAR0PERCENTAGE', 'Percentage %Year 0');
$module = '';
$moduleTitle = '';
$lvl = 1;
$passFail = 'P';
$credits = 0;
$ECTS = 0;
$hSize = count($header);
$lastGradeIndex = 0;
for($h=6;$h<$hSize;$h++){
	if(str_replace("\n", '', $header[$h])===YEAR0PERCENTAGE){
		$lastGradeIndex = $h;
		//echo $lastGradeIndex;die();
		break;
	}
}
foreach($year0Data as $rollNo => $gradeInfo)
{
	echo 'Name of Student: ' . $gradeInfo[3] . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;University Reference: ' . $rollNo . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Programme Title: ' . $gradeInfo[5] . '<br />';
	$gradeRows = '';
	for($g=6; $g<$lastGradeIndex; $g++){
		$module = $header[$g];
		$start = strpos($module, '(') + 1;
		$end = strpos($module, ')') - $start;
		$ECTS = substr($module, $start, $end);
		$credits = $ECTS * 2;
		$moduleTitle = str_replace('('.$ECTS.')', '', substr($header[$g], 8));
		//echo $session . '--' . substr($header[$g], 0, 7) . '--' . $moduleTitle . '--' . $lvl . '--' . $gradeInfo[$g] . '--' . $passFail . '--' . $credits . '--' . $ECTS . '<br />';
		$gradeRows = $gradeRows . "
					<tr>
						<td class=\"first\">$session
						</td>
						<td class=\"second\">" . substr($header[$g], 0, 7) .
						"</td>
						<td class=\"third\">$moduleTitle
						</td>
						<td class=\"fourth\">$lvl
						</td>
						<td class=\"fifth\">$gradeInfo[$g]
						</td>
						<td class=\"sixth\">$passFail
						</td>
						<td class=\"seventh\">$credits
						</td>
						<td class=\"eighth\">$ECTS
						</td>
					</tr>";
		
	}
	echo "<table>$gradeRows</table>";
}
*/
?>