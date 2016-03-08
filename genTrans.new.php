<?php 

// Include the main TCPDF library (search for installation path).
require_once('tcpdf_include.php');

// Include the file that sets the explanatory text
require_once('explTxt.php');

//Include the library that gets the Year 0 grades
require_once('parseInput.php');

//Include the library that gets Bradford grades
require_once('parseBradfordData.php');

require_once('transGenPDF.php');

//echo $_POST['txtAwardDt'];
//die();

$rowOffset = 3;
$colHdrData = 'A';
$aryColsStudentName = array('C', 'B'); $colAwardAvg = 'G';
$rowYear = 1;
$rowRoute = 3;
$rowStage = 2;
$keyWordYear = 'AYR:'; $keyWordStage = 'Block:'; $keyWordRoute = 'Route:';
$year0RollNoColIndex = 2; $year0StudentNameColIndex = 3;
$colIndexModuleInfoNumericGuess = 7;
$blnStudentsOnly = false;

$year = '';
$stage = '';
$route = '';
$aryAllGrades = array();
$pathToData = 'data\\fullresult\\' . $_POST['hdfGradYear'] . '\\' . $_POST['hdfProg'];
//echo $pathToData; die();
if ($handle = opendir($pathToData)) {
    while (false !== ($entry = readdir($handle))) {
		if($entry!='.' && $entry!='..'){
			//echo '<br />getting grades for years 1 - 3.' . $entry;
			if (substr($entry, strpos($entry, '.')+1, 3)=='xls'){
				// 'ELE' === substr($entry, 0, 3) && 
				//print substr($entry, 0, strpos($entry, '.')) . '<br />';
				//print substr($entry, strpos($entry, '.')+1) . '<br />';
				$yearGrades = getBradfordGrades("$pathToData\\" . $entry, $rowOffset, $colHdrData, $aryColsStudentName, $colAwardAvg, $rowYear, $keyWordYear, $rowRoute, $keyWordRoute, $rowStage, $keyWordStage, $colIndexModuleInfoNumericGuess, $blnStudentsOnly, $year, $stage, $route);
				if(empty($year)){
					print '<br />Year not found in expected location in input file: "' . $entry . '. Skipping file."';
				}
				if(empty($stage)){
					print '<br />Stage not found in expected location in input file: "' . $entry . '. Skipping file."';
				}
				if(empty($route)){
					print '<br />Route not found in expected location in input file: "' . $entry . '. Skipping file."';
				}
				if($yearGrades===FALSE){
					print '<br />Unable to locate starting row of data grid in input file: ' . $entry . '. Skipping file."';
				}
				else{
					$routePrefix = '';
					if(false != strpos($route, 'Electrical'))
					{
						$routePrefix = 'ELE';
					}
					else if(false != strpos($route, 'Computer'))
					{
						$routePrefix = 'COM';
					}
					else if(false != strpos($route, 'Software'))
					{
						$routePrefix = 'SOF';
					}

					$aryAllGrades[$stage.'.'.$routePrefix.'.'.$year] = $yearGrades;
					print '<br />Bradford grades for : ' . $entry . ' coded as: ' . $stage.'.'.$routePrefix.'.'.$year; 
					//var_dump($yearGrades);
					//var_dump(getBradfordGrades("$pathToData\\" . $entry, $rowOffset, $colHdrData, $rowYear, $rowRoute, $year, $stage, $route));
					//print '<br /> Year: '.$year.', Stage: '.$stage.', Route: '.$route;
					//die();
				}
			}
			// echo 'getting grades for year 0.';
			else if(substr($entry, strpos($entry, '.')+1)=='csv'){
				print '<br />getting year 0 data: ' . $entry;
				$session = '';
				extractGradesData("$pathToData\\" . $entry, $year0RollNoColIndex, $year0StudentNameColIndex, $year0Grades,  $session);
				$aryAllGrades['0.Fnd.'. str_replace('-1','/', $session)] = $year0Grades;
				//var_dump($year0Grades);
			}
		}
    }
    closedir($handle);
}
//die();

$strTimestamp = date('Ymd\Thns');
$strOutputPath = __DIR__ . '\\data\\output\\'. $_POST['hdfProg'] .'\\' . $strTimestamp;
//echo $strOutputPath; die();
mkdir($strOutputPath);
$strOutputPath = $strOutputPath . '\\';

ksort($aryAllGrades);

$aryRollNosTrans = $_POST['lstTransStudents'];
//['11029350/1', '11029353/1', '11029356/1'];
$studentName = ''; $aryGradesTrans = array();
$graduatingCalendarYear = '2014/5';
$dtAwarded = '03/12/2015';

// for transcript header info
$progOfStudy = ''; $progTitle = ''; $progLvl = ''; $awarded = ''; $classification = ''; $qualSought = ''; $transCertifOfficial = '';
$transNote = 'The degree will be awarded by the University of Bradford UK.';
$transCertifPost = 'Controller Examinations'; $transPreparedBy = 'Exam Officer'; $transCheckedBy = 'Manager SSO';
$transcertFurtherInfo = 'https://edocs.bradford.ac.uk'; $progHours = '1,200 total study hours  per full-time academic year (or pro rata for part-time study)'; $teachingInst = 'NAMAL College'; $awardInst = 'University of Bradford'; $langInstr = 'English'; $langAssmnt = 'English';

//$dtIssue = date('d/m/Y'); // will be filled in by hand.

$aryClassificationCutOffs = [[68, 'First Class Honours'], [58, 'Second Class Honours - First Division'], [48, 'Second Class Honours - Second Division'], [40, 'Third Class']];

/*
foreach($aryAllGrades[$indexFndYr] as $rollNo => $grades){
	//print '<br /> '.$rollNo;
	if($rollNo == $rollNoTrans){
		$aryGradesTrans[$indexFndYr] = $grades;
		if(!empty($grades[2])){
			$studentName  = $grades[2];
		}
		
		foreach($aryAllGrades as $gradeSet => $gradeData){
			if($gradeSet!='Fnd.0.2013/4'){
				$j=0;
				foreach($gradeData as $haystack => $gradesHaystack){
					if ($rollNo == substr($haystack, 0, 8)){
						print "<br />.... hallelujah! ($j)";
						$aryGradesTrans[$gradeSet] = $gradesHaystack;
						if(empty($progOfStudy)){
							if(substr($gradeSet, 0, 3) == 'ELE'){
								$progOfStudy = 'BEng Partner Institutions School of Engineering';
								$progTitle = 'Electrical & Electronic Engineering';
								$progLvl = 'Bachelor of Engineering';
								$awarded = $progLvl; $qualSought = $progLvl;
							}
							else if(substr($gradeSet, 0, 3) == 'SOF'){
								$progOfStudy = 'BEng Partner Institutions School of Engineering';
								$progTitle = 'Software Engineering';
								$progLvl = 'Bachelor of Engineering';
								$awarded = $progLvl; $qualSought = $progLvl;
							}
							else if(substr($gradeSet, 0, 3) == 'COM'){
								$progOfStudy = 'BSc Partner Institutions School of Mathematics & Computer Science';
								$progTitle = 'Computer Science';
								$progLvl = 'Bachelor of Science';
								$awarded = $progLvl; $qualSought = $progLvl;
							}
						}
					}
					$j++;
				}
			}
		}
	}
}
*/
//var_dump($aryGradesTrans);
//$grades = '';
//$totalECTS = genTransRows($aryGradesTrans, $grades);
//print '<table>' . $grades . '</table>';
//print '<p>'.$totalECTS.'</p>';
//die();

print '<br />Read in all the grades. Generating transcripts...';

foreach($aryRollNosTrans as $i => $rollNoTrans){
	$aryGradesTrans = array(); // reset the array in which we collect the grades of an individual student
	foreach($aryAllGrades as $gradeSet => $gradeData){
		$j=0;
		$aryGradeSetCode = explode('.', $gradeSet);
		$gradeSetRoute = $aryGradeSetCode[1];
		//$gradeSetStage = $aryGradeSetCode[0]; 
		//print $gradeSetStage; die();
		//print $gradeSet; var_dump($gradeData);
		foreach($gradeData as $haystack => $gradesHaystack){
			$rollNoTransSafe = substr($rollNoTrans, 0, 8);
			if ($rollNoTransSafe == substr($haystack, 0, 8)){
				print "<br />.... hallelujah! ($j)";
				$aryGradesTrans[$gradeSet] = $gradesHaystack;
				//var_dump($aryGradesTrans);
				//if($gradeSetStage==3){
				//	print '<br />award average: ' . $gradesHaystack[3]; die();
				//}
				$studentName = $gradesHaystack[2];
				if(empty($progOfStudy)){
					if($gradeSetRoute == 'ELE'){
						$progOfStudy = 'BEng Partner Institutions School of Engineering';
						$progTitle = 'Electrical & Electronic Engineering';
						$progLvl = 'Bachelor of Engineering';
						$awarded = $progLvl; $qualSought = $progLvl;
					}
					else if($gradeSetRoute == 'SOF'){
						$progOfStudy = 'BEng Partner Institutions School of Engineering';
						$progTitle = 'Software Engineering';
						$progLvl = 'Bachelor of Engineering';
						$awarded = $progLvl; $qualSought = $progLvl;
					}
					else if($gradeSetRoute == 'COM'){
						$progOfStudy = 'BSc Partner Institutions School of Mathematics & Computer Science';
						$progTitle = 'Computer Science';
						$progLvl = 'Bachelor of Science';
						$awarded = $progLvl; $qualSought = $progLvl;
					}
				}
			}
			$j++;
		}
	}
	// create new PDF document using our slightly customised class
	$pdf = new transGenPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

	// set document information
	$pdf->SetCreator('University of Bradford');
	$pdf->SetAuthor($transCertifOfficial);
	$pdf->SetTitle('Transcript');
	$pdf->SetSubject('Transcript for ' . $studentName);
	$pdf->SetKeywords('UoB, Namal, transcript');

	// set default header data
	//$pdf->setHeaderData('', 0, '', '', array(0,0,0), array(255,255,255));
	$pdf->setHeaderData('', 0, '', '', array(0,0,0), array(255,255,255));
	$pdf->setFooterData(array(0,0,0), array(255,255,255));

	// set header and footer fonts
	$pdf->setHeaderFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
	$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

	// set default monospaced font
	$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

	// set margins
	$pdf->SetMargins(9.900990099, 9.900990099, 9.900990099, true); // left, top, right
	$pdf->SetHeaderMargin(0.9900990099);
	$pdf->SetFooterMargin(3.4653465347);

	// SET CELL PADDING
	$prev_cell_padding = $pdf->getCellPaddings();
	$pdf->SetCellPadding(0);

	// set auto page breaks
	$pdf->SetAutoPageBreak(TRUE, 7.4257425743);

	// set image scale factor
	$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

	//$pdf->SetCompression(false);

	// set some language-dependent strings (optional)
	if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
		require_once(dirname(__FILE__).'/lang/eng.php');
		$pdf->setLanguageArray($l);
	}

	// -------------------------------------------------------------------

	// add a page
	$pdf->AddPage();

	// set JPEG quality - probably not needed as using a PNG image
	//$pdf->setJPEGQuality(75);

	// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

	// Add UoB logo
	//$pdf->Rect(0, 10, 100, 19.899665551839, '', array(), array()); // useful for debugging position
	$pdf->Image('images/UoB.logo.png', 0, 10, 100, 0, 'PNG', '', '', true, 300, 'L', false, false, 0, 'L T', false, false);
	// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

	$pdf->SetFont('helvetica', '', 15.65);
	$pdf->Ln(23.612536839);

	// print the subject line
	$pdf->Cell(0, 0, 'University of Bradford Transcript', 0, 1, 'L', false, '', 0, false, 'T', 'A');

	// print the subtitle
	$pdf->SetFont('helvetica', '', 10);
	$pdf->Ln(1);
	$pdf->Cell(0, 0, 'This document is an official University of Bradford transcript.', 0, 1, 'L', false, '', 0, false, 'T', 'B');
	
	$pdf->Ln(0.85);

	$pdf->rollNo = $rollNoTrans;

	$pdf->writeTranscript($transCertifPost, $transcertFurtherInfo, $aryExplTxt, $awarded, $teachingInst, $awardInst, $progOfStudy, $progTitle, $langInstr, $langAssmnt, $qualSought, $progHours, $progLvl, $studentName, $dtAwarded, $aryGradesTrans, $aryClassificationCutOffs);
	
	$pdf->SetCellPaddings($prev_cell_padding['L'], $prev_cell_padding['T'], $prev_cell_padding['R'], $prev_cell_padding['B']);

	//Close and output PDF document
	$pdf->Output($strOutputPath . $rollNoTransSafe . '.pdf', 'F');
	
	print('<BR />Transcript ' . $rollNoTransSafe . '.pdf has been output to <a target="_blank" href="http://' . $_SERVER['HTTP_HOST'] . str_replace(basename(__FILE__), '', $_SERVER["REQUEST_URI"]) . 'data/output/' . $_POST['hdfProg'] . '/' . $strTimestamp . '/' .  $rollNoTransSafe . '.pdf">' . $rollNoTransSafe . '</a>');
	set_time_limit(120);
}

//echo 'Transcripts have been generated for the following students" ' . var_dump($aryRollNosTrans);
?>