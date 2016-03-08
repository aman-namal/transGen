<?php

// Include the main TCPDF library (search for installation path).
require_once('tcpdf_include.php');

// Include the file that sets the explanatory text
require_once('explTxt.php');

/*
//Include the library that gets the Year 0 grades
//require_once('parseInput.php');

//Include the library that gets Bradford grades
//require_once('parseBradfordData.php');
*/

class transGenPDF extends TCPDF{
	private $w_page = 'PAGE';
	private $print_fancy_header = false;
	//public $passFail = '';
	//public $session = '';
	//public $lvl = 0;
	//public $dataHeader = array();
	//public $lastGradeIndex = 0;
	public $rollNo = '';
	//public $gradeInfo = array();
	private $ECTSTotal = 0; 
	private $aryYearLookup = array(
			'4' => '4th',
			'3' => '3rd',
			'2' => '2nd',
			'1' => '1st'
			);

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

/*
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
*/
	
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

	public function writeTranscript($issuerOfficialPost, $certFurtherInfo, $aryExplTxt, $qualificationSought, $teachingInstitute, $awardingInstitute, $programmeOfStudy, $programmeTitle, $langInstr, $langAssmnt, $programmeLevel, $programmeHours, $awardAchieved, $studentName, $studentDOB, $studentIntakeYear, $dtAward, $aryGradesTrans, $aryProgAvg, $aryWghtAvg, $classed, $blnLocalised){
		$this->SetFont('helvetica', '', 9);
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
					font-weight:bold;
					font-size: 10pt;
				}
				.personal{
					border-spacing:0px;
					padding-right: 4px;
					padding-left: 4px;
					padding-top:2px;
					padding-bottom:0px;
					width:100%;
				}
				.personal tr td.five{
					width: 26.32%;
					//color: red;
				}
				.personal tr td.four{
					width: 21.05%;
				}
				.personal tr td.heavy{
					font-weight:bold;
				}
				.qualification{
					border-spacing:0px;
					padding-right: 4px;
					padding-left: 4px;
					padding-top:2px;
					padding-bottom:0px;
					width:100%;
				}
				.qualification tr td.first{
					width: 40%;
					text-align:right;
				}
				.qualification tr td.second{
					width: 60%;
				}
				.qualification tr td.heavy{
					font-weight:bold;
				}
				.results{
					border-spacing:0px;
					padding-right: 4px;
					padding-left: 4px;
					padding-top:2px;
					padding-bottom:0px;
					/*width:1234px;*/
				}
				.results tr.header{
					vertical-align:middle;
				}
				.header tr td.first.localised{
					text-align:center;
				}
				.results tr td.heavy{
					font-weight:bold;
				}
				.results tr td.heavyL{
					font-weight:bold;
				}
				.results tr td.first{
					width: 13.42%;
				}
				.results tr td.firstL{
					width: 4.95%;
					vertical-align:top;
					text-align:center;
				}
				.results tr td.second{
					width: 8.03%;
				}
				.results tr td.secondL{
					width: 10.42%;
					text-align:center;
				}
				.results tr td.third{
					width: 39.47%;
				}
				.results tr td.thirdL{
					width: 40.48%;
				}
				.results tr td.fourth{
					width: 7.76%;
				}
				.results tr td.fourthL{
					width: 9.18%;
					text-align:center;
				}
				.results tr td.fifth{
					width: 8.03%;
				}
				.results tr td.fifthL{
					width: 9.67%;
					text-align:center;
				}
				.results tr td.sixth{
					width: 7.89%;
				}
				.results tr td.sixthL{
					width: 8.93%;
					text-align:center;
				}
				.results tr td.seventh{
					width: 7.76%;
				}
				.results tr td.seventhL{
					width: 7.44%;
					text-align:center;
				}
				.results tr td.eighth{
					width: 7.63%;
				}
				.results tr td.eighthL{
					width: 8.93%;
					text-align:center;
				}
				.results tr.footer td.second{
					width: 7.76%;
				}
				.results tr.footer td.third{
					width: 7.63%;
				}

				.qualified{
					border-spacing:0px;
					padding-right: 4px;
					padding-left: 4px;
					padding-top:2px;
					padding-bottom:0px;
					width:100%;
				}
				.qualified tr td.heavy{
					font-weight:bold;
				}
				.qualified tr td.first{
					width: 63.42%;
				}
				.qualified tr td.second{
					width: 18.42%;
					//color:red;
				}
				.qualified tr td.third{
					width: 18.16%;
					//color:red;
				}

				.qualified tr td.firstL{
					width: 20%;
				}
				.qualified tr td.secondL{
					width: 20%;
				}
				.qualified tr td.thirdL{
					width: 35%;
				}
				.qualified tr td.fourthL{
					width: 25%;
				}

				.certificate{
					border-spacing:0px;
					padding-right: 4px;
					padding-left: 4px;
					padding-top:2px;
					padding-bottom:0px;
					width:100%;
				}
				.certificate tr td.heavy{
					font-weight:bold;
					text-align: center;
				}
				.certificate tr td.heavyL{
					font-weight:bold;
					text-align: left;
				}
				.certificate tr td.spacious{
					border:2px double black;
					text-align:center;
					font-weight:bold;
				}
				.certificate tr td.first{
					width: 18%;
				}
				.certificate tr td.second{
					width: 20%;
				}
				.certificate tr td.third{
					width: 30%;
				}
				.certificate tr td.fourth{
					width: 32%;
				}
				.certificate tr td.firstL{
					width: 17%;
					text-align: right;
				}
				.certificate tr td.secondL{
					width: 50%;
				}
				.certificate tr td.thirdL{
					width: 33%;
				}

				.rules{
					border-spacing:0px;
					padding: 4px;
					width:100%;
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
							<td class="five">'. $studentName .'
							</td>
							<td class="four heavy">Date of Birth
							</td>
							<td class="five">'. $studentDOB .'
							</td>
						</tr>
						<tr>
							<td class="five heavy">University Reference
							</td>
							<td class="five">' . $this->rollNo . '
							</td>
							<td class="four heavy">Intake Year
							</td>
							<td class="five"> ' . $studentIntakeYear . '
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
							<!--<td class="first heavy">Awarding Institution
							</td>-->
							<td class="first heavy">Partner Institution
							</td>
							<td class="second">' . $awardingInstitute . '
							</td>
						</tr>';
		if($blnLocalised){
			$strQualHTML = $strQualHTML . 
						'<!-- <tr>
							<td class="first heavy">Programme of Study
							</td>
							<td class="second">' . $programmeOfStudy . '
							</td>
						</tr> -->
						<tr>
							<td class="first heavy">Programme Title
							</td>
							<td class="second">' . $programmeTitle . '
							</td>
						</tr>';
		}
		else{
			$strQualHTML = $strQualHTML . 
						'<tr>
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
						</tr>';
		}
		$strQualHTML = $strQualHTML .
						'<tr>
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
		//echo '<table>'. $strQualHTML .'</table>'; die();
		if($blnLocalised){
			$strQualLvlHTML = '';
		}
		else{
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
		}
		$transRows = '';
		$transResHdr = '<tr class="header">
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
						</tr>';
		
		$transResHdrLocalised = '<tr class="header">
							<td class="firstL heavyL">Year
							</td>
							<td class="secondL heavyL">Module Code
							</td>
							<td class="thirdL heavyL">Module Title
							</td>
							<td class="fourthL heavyL">Semester
							</td>
							<td class="fifthL heavyL">Marks
							</td>
							<td class="sixthL heavyL">Result
							</td>
							<td class="seventhL heavyL">Credits
							</td>
							<td class="eighthL heavyL">ECTS Credits
							</td>
						</tr>';
		//echo $strStyleHTML . ' <table>' . ($blnLocalised ? $transResHdrLocalised : $transResHdr) . '</table>'; die();
		$intColCount = 8;
		if($blnLocalised){
			$this->genTransRowsLocalised($aryGradesTrans, $transRows, $aryProgAvg);
			$intColCount = 8;
			$strCreditsFooter = '
							<td colspan="6" align="right"><B>Total credits gained/Total ECTS &nbsp;&nbsp;</B>
							</td>
							<td align="center">' . ($this->ECTSTotal*2) . '
							</td>
							<td align="center">' . $this->ECTSTotal . '
							</td>';
			$transFinalResHdrLocalised = '<tr class="sectionHdr">
				<td>&nbsp;3b. Qualification Achieved</td>
			</tr>';
			/*$transFinalRes = '<table class="qualified">
						<tr>
							<td colspan="6" style="font-size:1px;">&nbsp;&nbsp;&nbsp;&nbsp;</td>
						</tr>
						<tr>
							<td width="85">Classification:</td><td width="220">'. $classed .'</td>
							<td width="105" style="text-align:right">Award Average:</td><td width="40">'. number_format($aryWghtAvg['Award'], 2) .'</td>
							<td width="165" style="text-align:right">3rd Year Weighted Average:</td><td width="40">'. number_format($aryWghtAvg[3], 2) .'</td>
						</tr>
						<tr>
							<td width="85">Date of Award:</td><td colspan="3">'. $dtAward .'</td>
							<td width="165" style="text-align:right">4th Year Weighted Average:</td><td width="40">'. number_format($aryWghtAvg[4], 2) .'</td>
						</tr>
					</table>';
			*/
			$transFinalRes = '<table class="qualified">
						<tr>
							<td class="firstL heavy">Overall Average
							</td>
							<td class="secondL heavy">Award Average
							</td>
							<td class="thirdL heavy">Classification
							</td>
							<td class="fourthL heavy">Date of Award
							</td>
						</tr>
						<tr>
							<td class="firstL">' . number_format($aryProgAvg[4], 2) . '
							</td>
							<td class="secondL">' . number_format($aryWghtAvg['Award'], 2) .'
							</td>
							<td class="thirdL">' . $classed . '
							</td>
							<td class="fourthL">' . $dtAward . '
							</td>
						</tr>
					</table>';
		}
		else{
			$this->genTransRows($aryGradesTrans, $transRows);
			$strCreditsFooter = '
							<td colspan="6" align="right"><B>Total credits gained / Total ECTS credits gained&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</B>
							</td>
							<td class="second">' . $this->ECTSTotal * 2 . '
							</td>
							<td class="third">' . $this->ECTSTotal . '
							</td>';
			$transFinalResHdr = '<tr class="sectionHdr">
				<td>&nbsp;4b. Qualifications Achieved</td>
			</tr>';
			$transFinalRes = '<table class="qualified">
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
							<td class="second">' . $classed . '
							</td>
							<td class="third">' . $dtAward . '
							</td>
						</tr>
					</table>';
		}
		//echo '<table border="1">' . $transRows . '</table>'; die();

		$strTransHTML='			
			<tr class="sectionHdr">
				<td>&nbsp;' . ($blnLocalised ? '3' : '4') . 'a. Contents of the Programme of Study and Results Achieved</td>
			</tr>
			<tr>
				<td style="border-bottom-width:0px;">
					<table class="results">
						' . ($blnLocalised ? $transResHdrLocalised : $transResHdr) . '
						' . $transRows . '
						<tr>
							<td colspan="'. $intColCount .'" align="right" style="font-size:1;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
							</td>
						</tr>
						<tr class="footer">' .
							$strCreditsFooter .
						'</tr>
					</table>
				</td>
			</tr>
			' . ($blnLocalised ? $transFinalResHdrLocalised : $transFinalResHdr) . '
			<tr>
				<td>'. $transFinalRes .'
				</td>
			</tr>
			';
		//echo "<table>" . $strTransHTML . "</table>"; die();
		if($blnLocalised){
			/*$transCertification = '<table class="certificate">
						<tr>
							<td colspan="4" style="font-size:1px;">&nbsp;&nbsp;&nbsp;&nbsp;</td>
						</tr>
						<tr>
							<td class="first heavy">Date of Issue
							</td>
							<td class="second heavy">Authorised By
							</td>
							<td class="third heavy">Signature
							</td>
							<td class="fourth heavy">Official Post
							</td>
						</tr>
						<tr>
							<td class="first spacious">&nbsp;&nbsp;&nbsp;<br />&nbsp;&nbsp;&nbsp;<br />&nbsp;&nbsp;&nbsp;<br />&nbsp;&nbsp;&nbsp;
							</td>
							<td class="second spacious"><br /><br />' . $this->author . '
							</td>
							<td class="third spacious">&nbsp;&nbsp;&nbsp;<br />&nbsp;&nbsp;&nbsp;<br />&nbsp;&nbsp;&nbsp;<br />&nbsp;&nbsp;&nbsp;
							</td>
							<td class="fourth spacious"><br /><br />' . $issuerOfficialPost . '<br />
							</td>
						</tr>
						<tr>
							<td colspan="4" style="font-size:3px;">&nbsp;&nbsp;&nbsp;&nbsp;</td>
						</tr>
						<tr>
							<td class="first heavy" style="text-align:right;">Further Information:
							</td>
							<td class="second" colspan="3">' . $certFurtherInfo . '
							</td>
						</tr>
					</table>';
			*/
			$transCertification = '<table class="certificate">
						<tr>
							<td class="firstL heavyL">Date Of Issue
							</td>
							<td class="secondL">
							</td>
							<td class="thirdL heavyL">Signature and Stamp
							</td>
						</tr>
						<tr>
							<td class="firstL heavyL">Authorised By
							</td>
							<td class="secondL" colspan="2">' . $this->author . '
							</td>
						</tr>
						<tr>
							<td class="firstL heavyL">Official Post
							</td>
							<td class="secondL" colspan="2">' . $issuerOfficialPost . '
							</td>
						</tr>
						<tr>
							<td class="firstL heavyL">Further Information
							</td>
							<td class="secondL" colspan="2">' . $certFurtherInfo . '
							</td>
						</tr>
					</table>';
		}
		else{
			$transCertification = '<table class="certificate">
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
					</table>';
		}
		$strCertHTML='			
			<tr class="sectionHdr">
				<td>&nbsp;'. ($blnLocalised ? '4' : '5') .'. Transcript Certification</td>
			</tr>
			<tr>
				<td>
				'. $transCertification .'
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
							<td class="first heavy">Programmes, modules,<br /> credits and levels
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
							<td class="first heavy">Awards (including those<br /> that are accredited by external bodies)
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
		//echo $tbl; die();
		$this->SetX(8.4);
		$this->writeHTML($tbl, true, false, false, false, '');
		
		/* COMMENTED OUT TEMPORARILY TILL WE GET TEXT FOR 2ND PAGE
		//$this->AddPage();
		//$this->Write(0, 'Example of booklet mode', '', 0, 'L', true, 0, false, false, 0);
		//$this->Cell(0, 0, 'PAGE 2', 1, 1, 'C');
		
		$tblRules = '
		<HTML>
		<HEAD>
			<STYLE>				
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
					font-weight:bold;
					font-size:10px;
				}
				.rules{
					border-spacing:0px;
					padding: 4px;
					width:100%;
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
			</STYLE>
		</HEAD>
		<BODY>
		<table id="tblRules" cellspacing="0" cellpadding="1" border="1">
			{RULES}
		</table>
		</BODY>
		</HTML>';
		$tblRules = str_replace('{RULES}', $strRulesHTML, $tblRules);
		$this->SetX(8.4);
		$this->writeHTML($tblRules, true, false, false, false, '');
		*/
	}
	
	/*
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
	*/

	function genTransRows($aryGradesTrans, &$gradeRows = ''){
		$this->ECTSTotal=0;
		//$cumulGrade = 0; $sumCredits = 0;
		foreach($aryGradesTrans as $gradeSet => $grades){			
			foreach($grades[0] as $moduleCode => $gradeInfo){
				if(!empty($gradeInfo[2][0])){
					$gradeRows = $gradeRows . "
								<tr>
									<td class=\"first\">". $gradeInfo[3] .
									"</td>
									<td class=\"second\">$moduleCode
									</td>
									<td class=\"third\">" . $gradeInfo[0] .
									"</td>
									<td class=\"fourth\">". $gradeInfo[4] .
									"</td>
									<td class=\"fifth\">" . number_format($gradeInfo[2][0], 2) . 
									"</td>
									<td class=\"sixth\">" . $gradeInfo[2][1] .
									"</td>
									<td class=\"seventh\">" . $gradeInfo[1] .
									"</td>
									<td class=\"eighth\">" . $gradeInfo[1] / 2 .
									"</td>
								</tr>";
					$this->ECTSTotal = $this->ECTSTotal + ($gradeInfo[1]/2);
					//$cumulGrade = $cumulGrade + ($gradeInfo[2][0] * $gradeInfo[1]);
					//$sumCredits = $sumCredits + $gradeInfo[1];
				}
				//print '<br />module code: '.$moduleCode;
				//var_dump($gradeInfo);
			}
		}
	}

	function genTransRowsLocalised($aryGradesTrans, &$gradeRows = '', $aryProgAvg){
		$this->ECTSTotal=0;
		//$cumulGrade = 0; $sumCredits = 0;
		$oldLvl = -1; $lvlModuleCount = 0;
		$strTopBorder = '';
		//'border-top: 1px solid black; border-right: 1px solid black;';
		foreach($aryGradesTrans as $gradeSet => $grades){
			//var_dump($grades[0]);
			foreach($grades[0] as $moduleCode => $gradeInfo){
				if(!empty($gradeInfo[2][0])){
					$lvl = $gradeInfo[4];
					//echo '<br />Level: ' . $lvl . ', old level: ' . $oldLvl . ', module: ' . $gradeInfo[0] . ', Module Count: ' . $lvlModuleCount; //die();
					/*
					if($oldLvl!=$lvl)
					{
						$gradeRows = str_replace('{modCount}', $lvlModuleCount, $gradeRows);
						$gradeRows = str_replace('{topOffset}', str_repeat('<br />', ((int)$lvlModuleCount/2)+1), $gradeRows);
						$yearCell = '<td class="first localised" width="50" rowspan="{modCount}" style="border-top: 1px solid black; border-right: 1px solid black; text-align:center; font-size:10px;">{topOffset}' . $this->aryYearLookup[$lvl] . ' Year<br />' . $gradeInfo[3] . '</td>';
						$lvlModuleCount = 1;
						$strTopBorder = 'border-top: 1px solid black; border-right: 1px solid black;';
						$progAvgCell = '<td class="eighth localised" width="70" rowspan="{modCount}" style="border-top: 1px solid black; text-align:center; font-size:10px;">{topOffset}' . number_format($aryProgAvg[(int)$lvl], 2) . '</td>';
					}
					else{
						$yearCell = '';
						$progAvgCell = '';
						$strTopBorder = 'border-right: 1px solid black;';
						$lvlModuleCount++;
					}
					*/
					$gradeRows = $gradeRows . '
								<tr>
									<td class="firstL">' . $lvl . ' </td>
									<td class="secondL">' . $moduleCode . '</td>
									<td class="thirdL">' . $gradeInfo[0] . '</td>
									<td class="fourthL">' . $gradeInfo[5] . '</td>
									<td class="fifthL">' . number_format($gradeInfo[2][0], 2) . '</td>
									<td class="sixthL">' . $gradeInfo[2][1] . '</td>
									<td class="seventhL">' . $gradeInfo[1] . '</td>
									<td class="eighthL">' . ($gradeInfo[1] / 2) . '</td>
								</tr>';
								//	' . $progAvgCell . '
					//echo '<table>' . $gradeRows . '</table>'; die();
					$oldLvl = $lvl;
					$this->ECTSTotal = $this->ECTSTotal + ($gradeInfo[1]/2);
				}
				//print '<br />module code: '.$moduleCode;
				//var_dump($gradeInfo);
			}
			//$gradeRows = str_replace('{modCount}', $lvlModuleCount, $gradeRows);
			//$gradeRows = str_replace('{topOffset}', str_repeat('<br />', ((int)$lvlModuleCount/2)+1), $gradeRows);
		}
		
	}
	
}
?>