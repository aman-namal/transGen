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

function getBradfordGrades($fileName, $rowOffset, $colHdrData, $aryColsStudentName, $colAwardAvg, $rowYear, $keyWordYear, $rowRoute, $keyWordRoute, $rowStage, $keyWordStage, $colIndexModuleInfoNumericGuess, $blnYear3StudentsOnly, &$calendarYear, &$stage, &$route){

	$filterSubset = new ReadFilterBradfordGrades();
	$filterSubset->setMinRow(1); $filterSubset->setMaxRow(100);
	$filterSubset->setMinCol('A'); $filterSubset->setMaxCol('AI');
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

	// echo 'Will only work if file name has no dots except for extension';
	//$aryFileNamePartsByDot = explode('.', $fileName);
	// echo 'Loading Sheet "',$aryFileNamePartsByDot[0],'" only<br />';
	//$objReader->setLoadSheetsOnly($aryFileNamePartsByDot[0]);
	//$objReader->setLoadSheetsOnly('COMNAM Stage 3');

	// echo 'Loading Sheet using filter<br />';
	//
	//$objReader->setReadFilter($filterSubset);
	/*$objReader->getReadFilter()->setMinRow(1); $objReader->getReadFilter()->setMaxRow(100);
	$objReader->getReadFilter()->setMinCol('A'); $objReader->getReadFilter()->setMaxCol('AI');
	*/
	//print $fileName; die();
	$objPHPExcel = $objReader->load($fileName);
	//var_dump($objPHPExcel);
	
	/*
	// Echo memory usage
	//echo date('H:i:s') , ' Current memory usage: ' , (memory_get_usage(true) / 1024 / 1024) , " MB" , EOL;
	*/

	//var_dump($objPHPExcel->getActiveSheet()); die();
	$sheetData = $objPHPExcel->getActiveSheet()->toArray(null,false,false,true);
	//var_dump($sheetData); die();

	$startIndex = 0;
	$endModulesIndex = null; 
	//print($rowIndexHdrLast); 
	/*
	print '<br />first guess for year: ' . $sheetData[$rowYear][$colHdrData] . ' is ' . (strpos('dummy' . $sheetData[$rowYear][$colHdrData], $keyWordYear));
	print '<br />first guess for route: ' . $sheetData[$rowRoute][$colHdrData] . ' is ' . (strpos('dummy' . $sheetData[$rowRoute][$colHdrData], 	$keyWordRoute));
	print '<br />first guess for stage: ' . $sheetData[$rowStage][$colHdrData] . ' is ' . (strpos('dummy' . $sheetData[$rowStage][$colHdrData], $keyWordStage));
	//die();
	*/
	if(strpos('dummy' . $sheetData[$rowYear][$colHdrData], $keyWordYear)>0){
		$calendarYear = $sheetData[$rowYear][chr(ord($colHdrData)+1)];
	}
	else{
		$calendarYear = $sheetData[$rowYear][$colHdrData];
	}
	if($calendarYear == 3 && $blnYear3StudentsOnly){
		// this return false is made distinct from the other by the $blnYear3StudentsOnly binary argument
		return false;
	}
	//print '<br />' . $rowYear; print '<br />' . $colHdrData;
	//print '<br />' . $calendarYear;die();
	//print $stage; die();
	if(strpos('dummy' . $sheetData[$rowRoute][$colHdrData], $keyWordRoute)>0){
		$route = $sheetData[$rowRoute][chr(ord($colHdrData)+1)];
	}
	else{
		$route = $sheetData[$rowRoute][$colHdrData];
	}
	if(strpos('dummy' . $sheetData[$rowStage][$colHdrData], $keyWordStage)>0){
		$stage = $sheetData[$rowStage][chr(ord($colHdrData)+1)];
	}
	else{
		$aryStage = explode(' ', $sheetData[$rowStage][$colHdrData]);
		if(count($aryStage)>1){
			$stage = $aryStage[1];
		}
	}
	// in case the stage information cannot be retrieved from the most likely cells, try the filename:
	if(empty($stage)){
		$aryFileNameParts = explode(' ', $fileName); 
		$stage = count($aryFileNameParts) >= 3 && $aryFileNameParts[1]==='Stage' ? explode('.', $aryFileNameParts[2])[0] : '';  
	}
	$rowIndexHdrLast = max($rowRoute, $rowStage, $rowYear); 
	$rowIndexLikelyGridStart = -1;
	if (!empty($sheetData[$rowIndexHdrLast][$colHdrData]) && empty($sheetData[$rowIndexHdrLast+1][$colHdrData])){
		$rowIndexLikelyGridStart = $rowIndexHdrLast + 2;
	}
	else{
		for($r=$rowIndexHdrLast+2; $r<count($sheetData); $r++){
			if(getFirstDataCellInRow($sheetData[$r]) == -1){
				$rowIndexLikelyGridStart = $r + 1;
				break;
			}
		}
		if($rowIndexLikelyGridStart==-1){
			return false;
		}
	}
	$credits = array(); $yearGrades = array();
	$rollNo = ''; $aryStudents = array();
	foreach ($sheetData as $i => $row){
		if($startIndex==0 && $i>=$rowIndexLikelyGridStart){
			$colIndexHasVal = getFirstDataCellInRow($row);
			//print '<br />trying to locate row with module credits, first column with value: ' . $colIndexHasVal;
			// echo "<br /> found the row with the module ECTS credits";
			if(empty($row['A']) && $colIndexHasVal>$colIndexModuleInfoNumericGuess){
				$colIndexModuleInfoNumeric = $colIndexHasVal;
				$startIndex = $i+2;
				//print '<br />module column: '. $colIndexModuleInfoNumeric . ', start index: ' . $startIndex; die();
			}
		}
		if($startIndex>0 && $i>($startIndex+$rowOffset) && empty($row['A'])){
			// echo "<br /> end of student data grid ";
			break;
		}
		if($startIndex>0 && $i>=$startIndex){
			//echo "<BR /> collect module info";
			if($i==$startIndex)
			{
				$j = 0;
				foreach($row as $col => $data){
					if($j>$colIndexModuleInfoNumeric){
						if(empty($data)){
							break;
						}
						/* echo '<br /> $data contains module codes, '
						// echo '<br /> $sheetData[$i-1] refers to module name, '
						// echo '<br /> $sheetData[$i-2] refers to the module ECTS credits' */
						$credits[str_replace('-', '', $data)] = array(trim(str_replace('(Namal College)', '', str_replace('(Namal)', '', $sheetData[$i-1][$col]))), $sheetData[$i-2][$col], null);
					}
					$j++;
				}
				//var_dump($credits); print $j; die();
				$endModulesIndex = $j-1;
			}

			//echo "skip the next row and start collecting each student's grades and BoE decision";
			else if($i>$startIndex+1){
				$j = 0;
				//$rollNo = substr($row['A'], 0, 8); // just get the full roll no
				$rollNo = $row['A'];
				// echo "<br /> collect grades for " . $rollNo;
				if($blnYear3StudentsOnly==FALSE){
					foreach($row as $col => $data){
						if($j>$colIndexModuleInfoNumeric && $j<=$endModulesIndex && !empty($data)){
							$aryGradeDetail = explode(' ', $data);
							$credits[str_replace('-', '', $sheetData[$startIndex][$col])][2] = array($aryGradeDetail[0], $aryGradeDetail[1]);
						}
						$j++;
					}
					//echo "<br /> store module info, grades, overall BoE decision for ". $rollNo . ", as well as the student's name";
					$yearGrades[$rollNo][0] = $credits;
					$yearGrades[$rollNo][1] = $row['E'];
					$yearGrades[$rollNo][2] = $row[$aryColsStudentName[0]] . ' ' . $row[$aryColsStudentName[1]];
					// echo "<br /> also store the student's award average if these are Year 3 grades";
					if(!empty($stage) && $stage == 3){
						$yearGrades[$rollNo][3] = $row[$colAwardAvg];
					}
				}
				else{
					$aryStudents[$rollNo] = $row[$aryColsStudentName[0]] . ' ' . $row[$aryColsStudentName[1]];
				}
				//print($rollNo . ': '); var_dump($credits);
				//print 'hello';
				//die();
			}
		}
	}
	//var_dump($yearGrades['12036744/1']); //die();
	if($blnYear3StudentsOnly){
		return $aryStudents;
	}
	else{
		return $yearGrades;
	}
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