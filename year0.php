<?php
//============================================================+
// File name   : example_009.php
// Begin       : 2008-03-04
// Last Update : 2013-05-14
//
// Description : Example 009 for TCPDF class
//               Test Image
//
// Author: Nicola Asuni
//
// (c) Copyright:
//               Nicola Asuni
//               Tecnick.com LTD
//               www.tecnick.com
//               info@tecnick.com
//============================================================+

/**
 * Creates an example PDF TEST document using TCPDF
 * @package com.tecnick.tcpdf
 * @abstract TCPDF - Example: Test Image
 * @author Nicola Asuni
 * @since 2008-03-04
 */

// Include the main TCPDF library (search for installation path).
require_once('tcpdf_include.php');

// Include the file that sets the explanatory text
require_once('explTxt.php');

//Include the library that gets the Year 0 grades
require_once('parseInput.php');

//Include the library that gets Bradford grades
require_once('parseBradfordData.php');

class transGenPDF extends TCPDF{
	private $w_page = 'PAGE';
	private $print_fancy_header = false;
	public $passFail = '';
	public $session = '';
	public $lvl = 0;
	public $dataHeader = array();
	public $lastGradeIndex = 0;
	public $rollNo = '';
	public $gradeInfo = array();
	private $ECTSTotal = 0; 

	/**
	 * This method is used to render the page header.
	 * It is automatically called by AddPage() and could be overwritten in your own inherited class.
	 * AJK, 28-JUN-2015: overwritten to customise the header for our needs:
	 * removed feature to print document barcode;
	 * @public
	 */
	public function Header() {
		$x = 0;
		$dx = 0;
		if ($this->rtl) {
			$x = $this->w + $dx;
		} else {
			$x = 0 + $dx;
		}

		// print the fancy header template
		if($this->print_fancy_header){
			$this->printTemplate($this->header_xobjid, $x, 0, 0, 0, '', '', false);
		}
		if ($this->header_xobj_autoreset) {
			// reset header xobject template at each page
			$this->header_xobjid = false;
		}
		
		$this->y = $this->header_margin;
		$this->SimpleHeader();
		//$tmpltBase = $this->MakePaginationTemplate();
		//echo $tmpltBase;die();
		//echo('trying to print header template: ');
		//$this->printTemplate($tmpltBase, $x, $this->y, 0, 0, '', '', false);
		//die();
	}
	/**
	 * This method is used to render the page footer.
	 * It is automatically called by AddPage() and could be overwritten in your own inherited class.
	 * AJK, 28-JUN-2015: overwritten to simplify the code and customise footer format:
	 * removed feature to print document barcode;
	 * using ' OF ' instead of ' / '; using 'PAGE ' instead of 'page '; 
	 * printing the page info on the right & left margins.
	 * @public
	 */
	public function Footer(){
		$cur_y = $this->y;
		$this->SetTextColorArray($this->footer_text_color);
		//set style for cell border
		$line_width = (0.85 / $this->k);
		$this->SetLineStyle(array('width' => $line_width, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => $this->footer_line_color));

		//$w_page = isset($this->l['w_page']) ? $this->l['w_page'].' ' : '';
		//AJK, 28-JUN-2015: Could not find where l['w_page'] is set in class TCPDF, so simplifying code with own private member
		if (empty($this->pagegroups)) {
			$pagenumtxt = $this->w_page. ' ' . $this->getAliasNumPage().' OF '.$this->getAliasNbPages();
		} else {
			$pagenumtxt = $this->w_page. ' ' . $this->getPageNumGroupAlias().' OF '.$this->getPageGroupAlias();
		}
		$this->SetY($cur_y);
		//AJK, 28-JUN-2015: Print page number twice, i.e., on left and right edge of footer. 
		// Taking advantage of existing code that printed once based on whether document was set for RTL or LTR
		$this->SetX($this->original_lMargin);
		$this->Cell(0, 0, $pagenumtxt, 0, 0, 'L');
		$this->SetX($this->original_rMargin);
		$this->Cell(0, 0, $this->getAliasRightShift().$pagenumtxt, 0, 0, 'R');
	}

	private function MakePaginationTemplate(){
		//$cur_y = $this->y;
		$tmpltPagination = $this->startTemplate($this->w, $this->tMargin);
		$this->SetTextColorArray($this->header_text_color);
		//set style for cell border
		//$line_width = (0.85 / $this->k);
		//$this->SetLineStyle(array('width' => $line_width, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => $this->footer_line_color));

		//$w_page = isset($this->l['w_page']) ? $this->l['w_page'].' ' : '';
		//AJK, 28-JUN-2015: Could not find where l['w_page'] is set in class TCPDF, so simplifying code with own private member
		if (empty($this->pagegroups)) {
			$pagenumtxt = $this->w_page. ' ' . $this->getAliasNumPage().' OF '.$this->getAliasNbPages();
		} else {
			$pagenumtxt = $this->w_page. ' ' . $this->getPageNumGroupAlias().' OF '.$this->getPageGroupAlias();
		}
		//$this->SetY($cur_y);
		//AJK, 28-JUN-2015: Print page number twice, i.e., on left and right edge of footer. 
		// Taking advantage of existing code that printed once based on whether document was set for RTL or LTR
		$this->SetX($this->original_lMargin);
		$this->Cell(0, 0, $pagenumtxt, 0, 0, 'L');
		$this->SetX($this->original_rMargin);
		$this->Cell(0, 0, $this->getAliasRightShift().$pagenumtxt, 0, 0, 'R');
		$this->endTemplate();
		return $tmpltPagination;
	}

	private function SimpleHeader(){
		$this->SetTextColorArray($this->header_text_color);
		$currFontSize = $this->getFontSize();
		//set style for cell border
		//$line_width = (0.85 / $this->k);
		//$this->SetLineStyle(array('width' => $line_width, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => $this->footer_line_color));

		//$w_page = isset($this->l['w_page']) ? $this->l['w_page'].' ' : '';
		//AJK, 28-JUN-2015: Could not find where l['w_page'] is set in class TCPDF, so simplifying code with own private member
		if (empty($this->pagegroups)) {
			$pagenumtxt = $this->w_page. ' ' . $this->getAliasNumPage().' OF '.$this->getAliasNbPages();
		} else {
			$pagenumtxt = $this->w_page. ' ' . $this->getPageNumGroupAlias().' OF '.$this->getPageGroupAlias();
		}
		//AJK, 28-JUN-2015: Print page number twice, i.e., on left and right edge of footer. 
		// Taking advantage of existing code that printed once based on whether document was set for RTL or LTR
		$this->SetX($this->original_lMargin);
		$this->Cell(0, 0, $pagenumtxt, 0, 0, 'L');
		$this->SetX($this->original_rMargin);
		$this->Cell(0, 0, $this->getAliasRightShift().$pagenumtxt, 0, 0, 'R');
		$this->SetFontSize($currFontSize, false);
	}
	
	/**
	 * Set the same internal Cell padding for top, right, bottom, left 
	 * AJK, 28-JUN-2015: overloaded method allowing a padding of 0 to be set
	 * @param $pad (float) internal padding.
	 * @public
	 * @since 2.1.000 (2008-01-09)
	 * @see getCellPaddings(), setCellPaddings()
	 */
	public function SetCellPadding($pad) {
		$this->cell_padding['L'] = $pad;
		$this->cell_padding['T'] = $pad;
		$this->cell_padding['R'] = $pad;
		$this->cell_padding['B'] = $pad;
	}

	public function writeTranscript($issuerOfficialPost, $certFurtherInfo, $aryExplTxt, $qualificationSought, $teachingInstitute, $awardingInstitute, $programmeOfStudy, $programmeTitle, $langInstr, $langAssmnt, $programmeLevel, $programmeHours, $awardAchieved, $classification, $dtAward){
		$this->SetFont('helvetica', '', 8);
		$tbl = '
		<HTML>
		<HEAD>
			{STYLE}
		</HEAD>
		<BODY>
		<table id="tblTrans" cellspacing="0" cellpadding="1" border="1">
			{PERSONAL}
			{QUAL}
			{QUALLVL}
			{TRANSCRIPT}
			{CERTIFICATE}
			{RULES}
		</table>
		</BODY>
		</HTML>';
		$strStyleHTML='
			<STYLE TYPE="text/css">
				table{
					table-layout: fixed;
				}
				.sectionHdr{
					line-height: 16px;
				}
				.sectionHdr td {
					background-color:black;
					color:white;
					padding: 4px;
				}
				.personal{
					border-spacing:0px;
					padding: 4px;
				}
				.personal tr td.five{
					width: 26.32%;
				}
				.personal tr td.four{
					width: 21.05%;
				}
				.personal tr td.heavy{
					font-weight:bold;
				}
				.qualification{
					border-spacing:0px;
					padding: 4px;
				}
				.qualification tr td.first{
					width: 53.34%;
					text-align:right;
				}
				.qualification tr td.second{
					width: 46.66%
				}
				.qualification tr td.heavy{
					font-weight:bold;
				}
				.results{
					border-spacing:0px;
					padding: 0px;
				}
				.results tr td.heavy{
					font-weight:bold;
				}
				.results tr td.first{
					width: 13.42%;
				}
				.results tr td.second{
					width: 8.03%;
				}
				.results tr td.third{
					width: 39.47%;
				}
				.results tr td.fourth{
					width: 7.76%;
				}
				.results tr td.fifth{
					width: 8.03%;
				}
				.results tr td.sixth{
					width: 7.89%;
				}
				.results tr td.seventh{
					width: 7.76%;
				}
				.results tr td.eighth{
					width: 7.63%;
				}
				.results tr.footer td.second{
					width: 7.76%;
				}
				.results tr.footer td.third{
					width: 7.63%;
				}

				.qualified{
					border-spacing:0px;
					padding: 4px;
				}
				.qualified tr td.heavy{
					font-weight:bold;
				}
				.qualified tr td.first{
					width: 63.42%;
				}
				.qualified tr td.second{
					width: 18.42%;
					color:read;
				}
				.qualified tr td.third{
					width: 18.16%;
					color:read;
				}

				.certificate{
					border-spacing:0px;
					padding: 4px;
				}
				.certificate tr td.heavy{
					font-weight:bold;
				}
				.certificate tr td.first{
					width: 26.58%;
					text-align: right;
				}
				.certificate tr td.second{
					width: 73.42%;
				}

				.rules{
					border-spacing:0px;
					padding: 4px;
				}
				.rules tr td.heavy{
					font-weight:bold;
				}
				.rules tr td.first{
					width: 26.58%;
					text-align: right;
				}
				.rules tr td.second{
					width: 73.42%;
				}
			</STYLE>';
		$strPersonalHTML='
			<tr class="sectionHdr">
				<td>&nbsp;1. Personal Details</td>
			</tr>
			<tr>
				<td>
					<table class="personal">
						<tr>
							<td class="five heavy">Name of Student
							</td>
							<td class="five">'. $this->gradeInfo[3] .'
							</td>
							<td class="four heavy">Date of Birth
							</td>
							<td class="five">{STUDENTDOB}
							</td>
						</tr>
						<tr>
							<td class="five heavy">University Reference
							</td>
							<td class="five">' . $this->rollNo . '
							</td>
							<td class="four heavy">HESA Reference
							</td>
							<td class="five">{HESAREF}
							</td>
						</tr>
					</table>
				</td>
			</tr>';
		
		$strQualHTML = '			
			<tr class="sectionHdr">
				<td>&nbsp;2. Programme of Study and Qualification Sought</td>
			</tr>
			<tr>
				<td>
					<table class="qualification">
						<tr>
							<td class="first heavy">Qualification
							</td>
							<td class="second">' . $qualificationSought . '
							</td>
						</tr>
						<tr>
							<td class="first heavy">Teaching Institution
							</td>
							<td class="second">' . $teachingInstitute . '
							</td>
						</tr>
						<tr>
							<td class="first heavy">Awarding Institution
							</td>
							<td class="second">' . $awardingInstitute . '
							</td>
						</tr>
						<tr>
							<td class="first heavy">Programme of Study
							</td>
							<td class="second">' . $programmeOfStudy . '
							</td>
						</tr>
						<tr>
							<td class="first heavy">Programme Title
							</td>
							<td class="second">' . $programmeTitle . '
							</td>
						</tr>
						<tr>
							<td class="first heavy">Language(s) of Instruction
							</td>
							<td class="second">' . $langInstr . '
							</td>
						</tr>
						<tr>
							<td class="first heavy">Language(s) of Assessment
							</td>
							<td class="second">' . $langAssmnt . '
							</td>
						</tr>
					</table>
				</td>
			</tr>';
		$strQualLvlHTML='			
			<tr class="sectionHdr">
				<td>&nbsp;3. Level and Duration of the Qualification</td>
			</tr>
			<tr>
				<td>
					<table class="qualification">
						<tr>
							<td class="first heavy">Programme Level
							</td>
							<td class="second">' . $programmeLevel . '
							</td>
						</tr>
						<tr>
							<td class="first heavy">Programme Hours
							</td>
							<td class="second">' . $programmeHours . '
							</td>
						</tr>
					</table>
				</td>
			</tr>';
		$strTransHTML='			
			<tr class="sectionHdr">
				<td>&nbsp;4a. Contents of the Programme of Study and Results Achieved</td>
			</tr>
			<tr>
				<td style="border-bottom-width:0px;">
					<table class="results">
						<tr>
							<td class="first heavy">Year
							</td>
							<td class="second heavy">Module Code
							</td>
							<td class="third heavy">Module Title
							</td>
							<td class="fourth heavy">Level
							</td>
							<td class="fifth heavy">Mark
							</td>
							<td class="sixth heavy">Grade
							</td>
							<td class="seventh heavy">Credits
							</td>
							<td class="eighth heavy">ECTS Credits
							</td>
						</tr>
						'.$this->generateTransRows().'
						<tr>
							<td colspan="8" align="right">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
							</td>
						</tr>
						<tr class="footer">
							<td colspan="6" align="right"><B>Total credits gained / Total ECTS credits gained&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</B>
							</td>
							<td class="second">' . $this->ECTSTotal * 2 . '
							</td>
							<td class="third">' . $this->ECTSTotal . '
							</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr class="sectionHdr">
				<td>&nbsp;4b. Qualifications Achieved</td>
			</tr>
			<tr>
				<td>
					<table class="qualified">
						<tr>
							<td class="first heavy">Award Achieved
							</td>
							<td class="second heavy">Classification
							</td>
							<td class="third heavy">Date of Award
							</td>
						</tr>
						<tr>
							<td class="first">' . $awardAchieved . '
							</td>
							<td class="second">' . $classification . '
							</td>
							<td class="third">' . $dtAward . '
							</td>
						</tr>
					</table>
				</td>
			</tr>
			';
		$strCertHTML='			
			<tr class="sectionHdr">
				<td>&nbsp;5. Transcript Certification</td>
			</tr>
			<tr>
				<td>
					<table class="certificate">
						<tr>
							<td class="first heavy">Date Of Issue
							</td>
							<td class="second">
							</td>
						</tr>
						<tr>
							<td class="first heavy">Authorised By
							</td>
							<td class="second">' . $this->author . '
							</td>
						</tr>
						<tr>
							<td class="first heavy">Official Post
							</td>
							<td class="second">' . $issuerOfficialPost . '
							</td>
						</tr>
						<tr>
							<td class="first heavy">Further Information
							</td>
							<td class="second">' . $certFurtherInfo . '
							</td>
						</tr>
					</table>
				</td>
			</tr>';
		$strRulesHTML='			
			<tr class="sectionHdr">
				<td>&nbsp;6. University Regulations</td>
			</tr>
			<tr>
				<td>
					<table class="rules">
						<tr>
							<td class="first heavy">Programmes, modules, credits and levels
							</td>
							<td class="second">' . $aryExplTxt['prog'] . '
							</td>
						</tr>
						<tr>
							<td class="first heavy">Progression and Award
							</td>
							<td class="second">' . $aryExplTxt['assmnt'] . '
							</td>
						</tr>
						<tr>
							<td class="first heavy">Awards (including those that are accredited by external bodies)
							</td>
							<td class="second">' . $aryExplTxt['awards'] . '
							</td>
						</tr>
						<tr>
							<td class="first heavy">More Information
							</td>
							<td class="second">' . $aryExplTxt['more'] . '
							</td>
						</tr>
					</table>
				</td>
			</tr>';
		$tbl = str_replace('{STYLE}', $strStyleHTML, $tbl);
		$tbl = str_replace('{PERSONAL}', $strPersonalHTML, $tbl);
		$tbl = str_replace('{QUAL}', $strQualHTML, $tbl);
		$tbl = str_replace('{QUALLVL}', $strQualLvlHTML, $tbl);
		//$strTransHTML = str_replace('{RESULTS}', $this->generateTransRows(), $strTransHTML);
		$tbl = str_replace('{TRANSCRIPT}', $strTransHTML, $tbl);
		$tbl = str_replace('{CERTIFICATE}', $strCertHTML, $tbl);
		$tbl = str_replace('{RULES}', $strRulesHTML, $tbl);
		//echo $tbl; die();
		$this->SetX(8.4);
		$this->writeHTML($tbl, true, false, false, false, '');
	}
	
	private function generateTransRows(){
		
		$module = '';
		$moduleTitle = '';
		$credits = 0;
		$ECTSTotal = 0;
		$gradeRows = '';
		for($g=6; $g<$this->lastGradeIndex; $g++){
			$module = $this->dataHeader[$g];
			$moduleCode = substr($this->dataHeader[$g], 0, 7);
			$start = strpos($module, '(') + 1;
			$end = strpos($module, ')') - $start;
			$ECTS = substr($module, $start, $end);
			$credits = $ECTS * 2;
			$moduleTitle = str_replace('('.$ECTS.')', '', substr($this->dataHeader[$g], 8));
			//echo $session . '--' . substr($this->dataHeader[$g], 0, 7) . '--' . $moduleTitle . '--' . $lvl . '--' . $this->gradeInfo[$g] . '--' . $this->passFail . '--' . $credits . '--' . $ECTS . '<br />';
			$gradeRows = $gradeRows . "
						<tr>
							<td class=\"first\">$this->session
							</td>
							<td class=\"second\">$moduleCode
							</td>
							<td class=\"third\">$moduleTitle
							</td>
							<td class=\"fourth\">$this->lvl
							</td>
							<td class=\"fifth\">" . $this->gradeInfo[$g] . 
							"</td>
							<td class=\"sixth\">$this->passFail
							</td>
							<td class=\"seventh\">$credits
							</td>
							<td class=\"eighth\">$ECTS
							</td>
						</tr>";
			$this->ECTSTotal = $this->ECTSTotal + $ECTS;
		}
		return $gradeRows;
	}
	
}

$fullFileName = 'E:\\Namal\\DevCell\\transcriptGen\\reqs\\Final-Result-FY2013-14- After Supply Exam Aug 2014  EEE.csv';
define('YEAR0PERCENTAGE', 'Percentage %Year 0');


die();

$hSize = count($dataHeader);
$lastGradeIndex = 0;
for($h=6;$h<$hSize;$h++){
	if(str_replace("\n", '', $dataHeader[$h])===YEAR0PERCENTAGE){
		$lastGradeIndex = $h;
		//echo $lastGradeIndex;die();
		break;
	}
}
//die('extracted data, about to start the real work. lastGradeIndex: ' . $lastGradeIndex);

foreach($year0Data as $rollNo => $gradeInfo)
{
	// create new PDF document using our slightly customised class
	$pdf = new transGenPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

	// set document information
	$pdf->SetCreator('University of Bradford');
	$pdf->SetAuthor('Shirley Congdon');
	$pdf->SetTitle('Transcript');
	$pdf->SetSubject('Transcript for ' . $gradeInfo[3]);
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

	//echo 'Name of Student: ' . $gradeInfo[3] . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;University Reference: ' . $rollNo . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Programme Title: ' . $gradeInfo[5] . '<br />';
	$pdf->passFail = 'P';
	// <!-- should not have to set these each time
	$pdf->session = $session;
	$pdf->lvl = 1;
	$pdf->dataHeader = $dataHeader;
	$pdf->lastGradeIndex = $lastGradeIndex;
	// -->
	
	$pdf->rollNo = $rollNo;
	$pdf->gradeInfo = $gradeInfo;
	//echo 'current roll no: ' . $rollNo .'<br />';
	//inspectArray($gradeInfo);
	//die();
	
	$pdf->writeTranscript('Pro-Vice-Chancellor (Learning and teaching)', 'https://edocs.bradford.ac.uk', $aryExplTxt, 'Bachelor of Engineering', 'NAMAL College', 'University of Bradford', 'BEng Partner Institutions School of Engineering', 'Electrical and Electronic Engineering', 'English', 'English', 'Bachelor  of Engineering', '1,200 total study hours  per full-time academic year (or pro rata for part-time study)', 'Bachelor of Engineering', 'First Class Honours', '03/12/2014');
	
	$pdf->SetCellPaddings($prev_cell_padding['L'], $prev_cell_padding['T'], $prev_cell_padding['R'], $prev_cell_padding['B']);

	//Close and output PDF document
	$pdf->Output('E:\\Namal\\DevCell\\transcriptGen\\output\\' . $rollNo . '.pdf', 'F');
	set_time_limit(120);
	die();
}

//============================================================+
// END OF FILE
//============================================================+

function inspectArray($data){
	$num = count($data);
	echo "<p> $num elements in array: <br /></p>\n";
	for ($c=0; $c < $num; $c++) {
		echo $data[$c] . "<br />\n";
	}
}
