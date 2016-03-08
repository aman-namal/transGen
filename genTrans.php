<?php 

// Include the main TCPDF library (search for installation path).
require_once('tcpdf_include.php');

// Include the file that sets the explanatory text
require_once('explTxt.php');

//Include the library that gets the Year 0 grades
require_once('parseInput.php');

//Include the library that gets Bradford grades
//require_once('parseBradfordData.php');
require_once('parseResults.php');

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

// init parameters for new data retrieval routine:
$intColIndexRollNo = 'B'; $intColIndexName = 'C'; $intColIndexDOB = 'D'; 
$rowIndexStudentData = 7; $colIndexModuleInfoNumeric = 3; $colIndexIntakeYear = 'E';

$year = '';
$stage = '';
$route = '';
$aryAllGrades = array();
$pathToData = 'data\\fullresult\\' . $_POST['hdfGradYear'];
$blnLocalised = $_POST['txtLocalised'];
//echo $blnLocalised; die();
//echo $pathToData; die();
if ($handle = opendir($pathToData)) {
    while (false !== ($entry = readdir($handle))) {
		if($entry!='.' && $entry!='..'){
			//echo '<br />getting grades for years 1 - 3.' . $entry;
			if (substr($entry, strpos($entry, '.')+1, 3)=='xls'){
				// 'ELE' === substr($entry, 0, 3) && 
				//print substr($entry, 0, strpos($entry, '.')) . '<br />';
				//print substr($entry, strpos($entry, '.')+1) . '<br />';
				$yearGrades = parseResults("$pathToData\\" . $entry, $intColIndexRollNo, $intColIndexName, $intColIndexDOB, $rowIndexStudentData, $colIndexModuleInfoNumeric, $colIndexIntakeYear);
				//var_dump($yearGrades); die();
				// hard-coding the route as COM
				$routePrefix = 'COM';

				$aryAllGrades[$routePrefix] = $yearGrades;
				print '<br />Bradford grades for : ' . $entry . ' coded as: '.$routePrefix;
			}
		}
    }
    closedir($handle);
}
//var_dump($aryAllGrades); die();

$strTimestamp = date('Ymd\Thns');
$strOutputPath = __DIR__ . '\\data\\output\\'. $_POST['hdfProg'] .'\\' . $strTimestamp;
//echo $strOutputPath; die();
mkdir($strOutputPath);
$strOutputPath = $strOutputPath . '\\';

ksort($aryAllGrades);

$aryRollNosTrans = $_POST['lstTransStudents'];
//['11029350/1', '11029353/1', '11029356/1'];
$studentName = ''; $studentDOB = ''; $studentCurrentStatus = ''; $aryGradesTrans = array();
$graduatingCalendarYear = '2014/5';
$dtAwarded = date_format(DateTime::createFromFormat('D M d Y', $_POST['txtAwardDt']), 'F j, Y'); 
$transCertifOfficial = $_POST['txtCertOfficial']; 
$transCertifPost = $_POST['txtIssuerPost']; 
$transcertFurtherInfo = $_POST['txtFurtherInfo'];

// for transcript header info
$progOfStudy = ''; $progTitle = ''; $progLvl = ''; $awarded = ''; $classification = ''; $qualSought = '';  $progHours = '1,200 total study hours  per full-time academic year (or pro rata for part-time study)'; $teachingInst = 'Namal College, Pakistan'; $awardInst = 'School of Electrical Engineering & Computer Science, University of Bradford'; $langInstr = 'English'; $langAssmnt = 'English';

//$dtIssue = date('d/m/Y'); // will be filled in by hand.

$aryClassificationCutOffs = [[68, 'First Class Honours'], [58, 'Second Class Honours - First Division'], [48, 'Second Class Honours - Second Division'], [40, 'Third Class']];

var_dump($aryAllGrades); //die();
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
		//$aryGradeSetCode = explode('.', $gradeSet);
		//$gradeSetRoute = $aryGradeSetCode[1];
		//$gradeSetStage = $aryGradeSetCode[0]; 
		//print $gradeSetStage; die();
		//print $gradeSet; var_dump($gradeData);
		$gradeSetRoute = $gradeSet;
		foreach($gradeData as $haystack => $gradesHaystack){
			$rollNoTransSafe = substr($rollNoTrans, 0, 8);
			if ($rollNoTransSafe == substr($haystack, 0, 8)){
				//print "<br />.... hallelujah! ($j)";
				$aryGradesTrans[$gradeSet] = $gradesHaystack;
				var_dump($aryGradesTrans);
				//if($gradeSetStage==3){
				//	print '<br />award average: ' . $gradesHaystack[3]; die();
				//}
				$studentName = $gradesHaystack[1];
				$studentDOB = $gradesHaystack[2];
				$studentIntakeYear = $gradesHaystack[6];
				$studentCurrentStatus = $gradesHaystack[5];
				$aryProgAvg = $gradesHaystack[3];
				$aryWghtAvg = $gradesHaystack[4];
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
						//$awarded = 'BSc (Hons.) Computer Science'; 
						$awarded = 'Bachelor of Science (Hons)'; 
						$qualSought = $progLvl;
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

	$pdf->writeTranscript($transCertifPost, $transcertFurtherInfo, $aryExplTxt, $awarded, $teachingInst, $awardInst, $progOfStudy, $progTitle, $langInstr, $langAssmnt, $qualSought, $progHours, $progLvl, $studentName, $studentDOB, $studentIntakeYear, $dtAwarded, $aryGradesTrans, $aryProgAvg, $aryWghtAvg, $studentCurrentStatus, $blnLocalised);
	
	$pdf->SetCellPaddings($prev_cell_padding['L'], $prev_cell_padding['T'], $prev_cell_padding['R'], $prev_cell_padding['B']);

	//Close and output PDF document
	$pdf->Output($strOutputPath . $rollNoTransSafe . '.pdf', 'F');
	
	print('<BR />Transcript ' . $rollNoTransSafe . '.pdf has been output to <a target="_blank" href="http://' . $_SERVER['HTTP_HOST'] . str_replace(basename(__FILE__), '', $_SERVER["REQUEST_URI"]) . 'data/output/' . $_POST['hdfProg'] . '/' . $strTimestamp . '/' .  $rollNoTransSafe . '.pdf">' . $rollNoTransSafe . '</a>');
	set_time_limit(120);
}

//echo 'Transcripts have been generated for the following students" ' . var_dump($aryRollNosTrans);
?>