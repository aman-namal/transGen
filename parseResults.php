<?php
/**
 * PHPExcel
 *
 * Copyright (C) 2006 - 2014 PHPExcel
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category   PHPExcel
 * @package    PHPExcel
 * @copyright  Copyright (c) 2006 - 2014 PHPExcel (http://www.codeplex.com/PHPExcel)
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt	LGPL
 * @version    1.8.0, 2014-03-02
 */

error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);

define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');

//date_default_timezone_set('Europe/London');

/** Include PHPExcel_IOFactory */
require_once dirname(__FILE__) . '/../PHPExcel/Classes/PHPExcel/IOFactory.php';

class ReadFilterBradfordGrades implements PHPExcel_Reader_IReadFilter
{
	private $minRow = 0;
	private $maxRow = 0;
	private $minCol = '';
	private $maxCol = '';
	
	public function setMinRow($iMinRow){
		$this->minRow = $iMinRow;
	}
	public function setMaxRow($iMaxRow){
		$this->maxRow = $iMaxRow;
	}

	public function setMinCol($iMinCol){
		$this->minCol = $iMinCol;
	}
	public function setMaxCol($iMaxCol){
		$this->maxCol = $iMaxCol;
	}

	public function readCell($column, $row, $worksheetName = '') {
		//print 'going to read column: ' . $column . ', row: ' . $row;
		// Read rows 1 to 7 and columns A to E only
		//echo 'checked my filter'; die();
		if ($row >= $this->minRow && $row <= $this->maxRow) {
			if (in_array($column,range($this->minCol,$this->maxCol))) {
				return true;
			}
		}
		return false;
	}
}

/* sample call: 
$rowOffset = 3;
$colHdrData = 'A';
$aryColsStudentName = array('C', 'B');
$rowYear = 1;
$rowRoute = 3;
$rowStage = 2;
$calendarYear = '';
$stage = '';
$route = '';
$colAwardAvg = 'G';

$yearGrades = getBradfordGrades("data\\fullresult\\COMNAM Stage 3.xlsx", $rowOffset, $colHdrData, $aryColsStudentName, $colAwardAvg, $rowYear, $rowRoute, $rowStage, $calendarYear, $stage, $route);
echo '<br /><B>Year</B>: ' . $calendarYear . '. Stage: ' . $stage . '. Route: '. $route . '<br />';
var_dump($yearGrades);
*/
// $yearGrades = getBradfordGrades("$pathToData\\" . $entry, $rowOffset, $colHdrData, $aryColsStudentName, $colAwardAvg, $rowYear, $rowRoute, $rowStage, $year, $stage, $route);

function getFirstDataCellInRow($rowPHPExcel)
{
	$i = 0;
    foreach ($rowPHPExcel as $index => $cell) {
        if (!empty($cell)) {
            return $i;
        }
		$i++;
    }
    return -1;
}

function getStudentInfo($fileName, $colRollNo, $colStudentName, $rowIndexStudentData){
	if(substr($fileName, strpos($fileName, '.')+1)=='xlsx')
	{
		$inputFileType = 'Excel2007';
	}
	else if (substr($fileName, strpos($fileName, '.')+1)=='xls'){
		$inputFileType = 'Excel5';
	}
	$aryStudents = array();
	$objReader = PHPExcel_IOFactory::createReader($inputFileType);

	// Read only the data, no formatting
	$objReader->setReadDataOnly(true);	
	
	$objPHPExcel = $objReader->load($fileName);
	
	$sheetData = $objPHPExcel->getActiveSheet()->toArray(null,false,true,true);
	//var_dump($sheetData); die();
	$startIndex = 1;
	
	foreach ($sheetData as $i => $row){
		if($i>($startIndex+$rowIndexStudentData) && empty($row['A'])){
			// echo "<br /> end of student data grid ";
			break;
		}
		else if($i>=$rowIndexStudentData){
			$j = 0;
			$aryStudents[$row[$colRollNo]] = $row[$colStudentName];
		}
	}
	return $aryStudents;
}

/*
$intColIndexRollNo = 'B'; $intColIndexName = 'C'; $intColIndexDOB = 'D'; 
$rowIndexStudentData = 7; $colIndexModuleInfoNumeric = 4;


$aryStudents = getStudentInfo('E:\Namal\DevCell\transcriptGen\reqs\convergingInputDataFormat.xlsx', $intColIndexRollNo, $intColIndexName, $rowIndexStudentData);

var_dump($aryStudents);
die();


$studentGrades = parseResults('E:\Namal\DevCell\transcriptGen\reqs\convergingInputDataFormat.xlsx', $intColIndexRollNo, $intColIndexName, $intColIndexDOB, $rowIndexStudentData, $colIndexModuleInfoNumeric);
*/

function parseResults($fileName, $colRollNo, $colStudentName, $colDOB, $rowIndexStudentData, $colIndexModuleInfoNumeric, $colIndexIntakeYear){

	/*
	$filterSubset = new ReadFilterBradfordGrades();
	$filterSubset->setMinRow(1); $filterSubset->setMaxRow(100);
	$filterSubset->setMinCol('A'); $filterSubset->setMaxCol('AI');
	*/
	// guessing file type by extension. Should work for our controlled conditions.
	if(substr($fileName, strpos($fileName, '.')+1)=='xlsx')
	{
		$inputFileType = 'Excel2007';
	}
	else if (substr($fileName, strpos($fileName, '.')+1)=='xls'){
		$inputFileType = 'Excel5';
	}
	$objReader = PHPExcel_IOFactory::createReader($inputFileType);

	// Read only the data, no formatting
	$objReader->setReadDataOnly(true);	


	// echo 'Loading Sheet using filter<br />';
	//
	//$objReader->setReadFilter($filterSubset);
	/*$objReader->getReadFilter()->setMinRow(1); $objReader->getReadFilter()->setMaxRow(100);
	$objReader->getReadFilter()->setMinCol('A'); $objReader->getReadFilter()->setMaxCol('AI');
	*/
	//print $fileName; die();
	$objPHPExcel = $objReader->load($fileName);
	//var_dump($objPHPExcel); die();
	
	/*
	// Echo memory usage
	//echo date('H:i:s') , ' Current memory usage: ' , (memory_get_usage(true) / 1024 / 1024) , " MB" , EOL;
	*/

	//var_dump($objPHPExcel->getActiveSheet()); die();
	$sheetData = $objPHPExcel->getActiveSheet()->toArray(null,false,true,true);
	//var_dump($sheetData); die();

	$startIndex = 1;
	$endModulesIndex = null; 
	/* We'll have calendar years (2012/13, etc) and degree years (1st year, 2nd year, etc)
		Calendar years will be in the 4th row. Degree years will be deduced by the progression
		down the list of modules. Each time the code entounters the phrase 'Progression Average' in the 2nd row, that's the 
		breaker for the list of modules for that year.
	*/
	$credits = array(); $yearGrades = array();
	$rollNo = '';
	
	foreach ($sheetData as $i => $row){
		// echo $i . ': ' . var_dump($row);
		if($i>($startIndex+$rowIndexStudentData) && empty($row['A'])){
			// echo "<br /> end of student data grid ";
			break;
		}
		if($i>=$startIndex){
			// echo "<BR /> collect module info";
			if($i==$startIndex)
			{
				$j = 0;
				foreach($row as $col => $data){
					if($j>$colIndexModuleInfoNumeric){
						if(!empty($data)){
							/* echo '<br /> $data contains module credits, '
							// echo '<br /> $sheetData[$i+1][$col] refers to module name, '
							// echo '<br /> $sheetData[$i+2][$col] refers to the module code ' */
							// echo '<br /> $sheetData[$i+3][$col] refers to the calendar year ' */
							// echo '<br /> $sheetData[$i+4][$col] refers to the grade year ' */
							// echo '<br /> $sheetData[$i+5][$col] refers to the semester(s) in which the module is taught ' */
							// so, store the module name, credits, the calendar year and the grade year, indexed by the module code
							$credits[$sheetData[$i+2][$col]] = array(trim(str_replace('(Namal College)', '', str_replace('(Namal)', '', $sheetData[$i+1][$col]))), $data, null, $sheetData[$i+3][$col], $sheetData[$i+4][$col], $sheetData[$i+5][$col]);
						}
					}
					$j++;
				}
				var_dump($credits); //print $j; die();
				$endModulesIndex = $j-1;
			}
			//echo "skip down to the start of the student data section and start collecting each student's grades and various averages";
			else if($i>=$rowIndexStudentData){
				$j = 0;
				$rollNo = $row[$colRollNo];
				// echo "<br /> collect grades for " . $rollNo;
				$intIndexCalendarYear = 1;
				$aryProgAvg = array();
				$aryWghtAvg = array();
				$strCurrentStatus = '';
				foreach($row as $col => $data){
					if($j>$colIndexModuleInfoNumeric && $j<=$endModulesIndex){
						if(!empty($sheetData[$startIndex][$col])){
							if(!empty($data) && $data!='N/A'){
								$aryGradeDetail = explode(' ', $data);
								// echo 'grade split: '; var_dump($aryGradeDetail);
								$credits[$sheetData[$startIndex+2][$col]][2] = array($aryGradeDetail[0], count($aryGradeDetail)==3 ? $aryGradeDetail[1].$aryGradeDetail[2] : $aryGradeDetail[1]);
								// if required the feature to show repeated attempts will have to be coded in here,
								// using the data in the "First Attempt" column.
							}
							// don't do anything if the cell is empty - it simply means that the student did not take that module.
						}
						else{
							//echo 'data in second row: ' . $sheetData[$startIndex+1][$col] . '<br />';
							if(stripos($sheetData[$startIndex+1][$col], 'Progression Average')!==FALSE){
								// collect the student's progression average for that year
								//echo 'data in second row: ' . $sheetData[$startIndex+1][$col] . '<br />';
								$aryProgAvg[$intIndexCalendarYear] = $data;
								$intIndexCalendarYear++;
							}
							else if($sheetData[$startIndex+1][$col]=='Stage 2 Weighted Average'){
								$aryWghtAvg[3] = $data;
							}
							else if($sheetData[$startIndex+1][$col]=='Stage 3 Weighted Average'){
								$aryWghtAvg[4] = $data;
							}
							else if($sheetData[$startIndex+1][$col]=='Award Average'){
								$aryWghtAvg['Award'] = $data;
							}
							else if(stripos($sheetData[$startIndex+1][$col], 'Current Status')!==FALSE){
								$strCurrentStatus = $data;
							}
						}
					}
					$j++;
				}
				// echo "<br /> store module info, DOB, grades for ". $rollNo . ", as well as the student's name";
				$yearGrades[$rollNo][0] = $credits;
				//$yearGrades[$rollNo][1] = $row['E'];
				$yearGrades[$rollNo][1] = $row[$colStudentName];
				$yearGrades[$rollNo][2] = PHPExcel_Shared_Date::ExcelToPHPObject($row[$colDOB])->format('d/m/Y');
				// echo "<br /> also store the student's progression averages, weighted averages and award average";
				// var_dump($aryProgAvg);
				$yearGrades[$rollNo][3] = $aryProgAvg;
				$yearGrades[$rollNo][4] = $aryWghtAvg;
				$yearGrades[$rollNo][5] = $strCurrentStatus;
				$yearGrades[$rollNo][6] = $row[$colIndexIntakeYear];
				$dtDOB = PHPExcel_Shared_Date::ExcelToPHPObject($row[$colDOB]);
				echo '<br />DOB of ' . $row[$colStudentName] . ' is: ' . $dtDOB->format('Y-m-d');
				echo '<br />Intake Year of ' . $row[$colStudentName] . ' is: ' . $row[$colIndexIntakeYear];;
				//print($rollNo . ': '); var_dump($credits);
				//print 'hello';
				//die();
			}
		}
	}
	//var_dump($yearGrades); die();
	return $yearGrades;
}

/*
echo date('H:i:s') , " Write to Excel2007 format" , EOL;
$callStartTime = microtime(true);

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save(str_replace('.php', '.xlsx', __FILE__));

$callEndTime = microtime(true);
$callTime = $callEndTime - $callStartTime;

echo date('H:i:s') , " File written to " , str_replace('.php', '.xlsx', pathinfo(__FILE__, PATHINFO_BASENAME)) , EOL;
echo 'Call time to write Workbook was ' , sprintf('%.4f',$callTime) , " seconds" , EOL;
// Echo memory usage
echo date('H:i:s') , ' Current memory usage: ' , (memory_get_usage(true) / 1024 / 1024) , " MB" , EOL;


// Echo memory peak usage
echo date('H:i:s') , " Peak memory usage: " , (memory_get_peak_usage(true) / 1024 / 1024) , " MB" , EOL;

// Echo done
echo date('H:i:s') , " Done writing file" , EOL;
echo 'File has been created in ' , getcwd() , EOL;
*/