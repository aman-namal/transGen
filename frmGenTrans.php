<?php
//Include the library that gets Bradford grades
require_once('parseResults.php');

$rowOffset = 3;
$colHdrData = 'A';
$aryColsStudentName = array('C', 'B'); $colAwardAvg = 'G';
$rowYear = 1;
$rowRoute = 3;
$rowStage = 2;
$keyWordYear = 'AYR:'; $keyWordStage = 'Block:'; $keyWordRoute = 'Route:';
$year0RollNoColIndex = 2; $year0StudentNameColIndex = 3;
$colIndexModuleInfoNumericGuess = 7;
$blnStudentsOnly = true;
$year = '';
$stage = '';
$route = '';
$pathToData = 'data\\fullresult\\' . $_POST['cboGradYear'] . '\\';
//echo $pathToData; die();
//$strFiles = '';
$strOptions = '';

$intColIndexRollNo = 'B'; $intColIndexName = 'C'; $intColIndexDOB = 'D'; 
$rowIndexStudentData = 7;

if ($handle = opendir($pathToData)) {
    while (false !== ($entry = readdir($handle))) {
		if($entry!='.' && $entry!='..'){
			//echo '<br />getting grades for years 1 - 3.' . $entry;
			//$strFiles = $strFiles . ', ' . $entry;
			if (substr($entry, strpos($entry, '.')+1, 3)=='xls'){
				//echo "$pathToData\\" . $entry; die();
				$aryStudents = getStudentInfo($pathToData . $entry, $intColIndexRollNo, $intColIndexName, $rowIndexStudentData);

				//var_dump($aryStudents); die();
				if($aryStudents!==false){
					foreach($aryStudents as $rollNo => $name){
						$strOptions = $strOptions . "<OPTION Value='$rollNo'>$name ($rollNo)</OPTION>";
					}
					break;
				}
			}
		}
    }
    closedir($handle);
}

//echo $strFiles. '<br />';
//echo $strOptions . '<br />';
?>

<!DOCTYPE HTML>
<HTML>
<HEAD>
<TITLE>Namal College - Transcript Generation Script - Step 2</TITLE>
<link rel="stylesheet" href="Pikaday/css/pikaday.css">
<link rel="stylesheet" href="transGen.css">

<SCRIPT language="javascript"> 

function anyStudentSelected(lstTransStudents){
	var i;
	var goNoGo;
	goNoGo=false;
	for (i = 0; i < lstTransStudents.options.length; i++) 
	{
		if(lstTransStudents.options[i].value != ''){
			lstTransStudents.options[i].selected = 'selected';
			goNoGo = true;
		}
	}
	return goNoGo;
}

/*
Courtesy of http://www.developergeekresources.com/examples/javascript/javascript-listbox_to_listbox.php
*/
function move(tbFrom, tbTo) 
{
 var arrFrom = new Array(); var arrTo = new Array(); 
 var arrLU = new Array();
 var i;
 for (i = 0; i < tbTo.options.length; i++) 
 {
	if(tbTo.options[i].value != ""){
	  arrLU[tbTo.options[i].text] = tbTo.options[i].value;
	  arrTo[i] = tbTo.options[i].text;
	}
 }
 var fLength = 0;
 var tLength = arrTo.length;
 for(i = 0; i < tbFrom.options.length; i++) 
 {
  arrLU[tbFrom.options[i].text] = tbFrom.options[i].value;
  if (tbFrom.options[i].selected && tbFrom.options[i].value != "") 
  {
   arrTo[tLength] = tbFrom.options[i].text;
   tLength++;
  }
  else 
  {
   arrFrom[fLength] = tbFrom.options[i].text;
   fLength++;
  }
}

tbFrom.length = 0;
tbTo.length = 0;
var ii;

for(ii = 0; ii < arrFrom.length; ii++) 
{
  var no = new Option();
  no.value = arrLU[arrFrom[ii]];
  no.text = arrFrom[ii];
  tbFrom[ii] = no;
}

for(ii = 0; ii < arrTo.length; ii++) 
{
 var no = new Option();
 no.value = arrLU[arrTo[ii]];
 no.text = arrTo[ii];
 no.selected = 'selected';
 tbTo[ii] = no;
}
}
</SCRIPT>

</HEAD>

<BODY>
<H1>Namal + Bradford undergraduate transcript generation script</H1>
<FORM ID='frmInputTransGen' NAME='frmInputTransGen' ACTION='genTrans.php' METHOD="POST">
<TABLE>
<TR>
	<TD><LABEL ID='lblAwardDr'>Date of Award</label></TD>
	<TD><INPUT TYPE='TEXT' ID='txtAwardDt' NAME='txtAwardDt' Value='12/3/2015' /></TD>
</TR>
<TR>
	<TD><LABEL ID='lblCertOfficial'>Authorised By</label></TD>
	<TD><INPUT TYPE='TEXT' ID='txtCertOfficial' NAME='txtCertOfficial' Value='Dr. Irfan Awan' /></TD>
</TR>
<TR>
	<TD><LABEL ID='lblPost'>Official Post</label></TD>
	<TD><INPUT TYPE='TEXT' ID='txtIssuerPost' NAME='txtIssuerPost' Value='Head, School of Electrical Enginnering and Computer Science' /></TD>
</TR>
<TR>
	<TD><LABEL ID='lblFurtherInfo'>Further Information</label></TD>
	<TD><INPUT TYPE='TEXT' ID='txtFurtherInfo' NAME='txtFurtherInfo' Value='https://edocs.bradford.ac.uk' /></TD>
</TR>
<TR>
	<TD><LABEL ID='lblOutputLocalised'>Localised output format?</label></TD>
	<TD><INPUT TYPE='TEXT' ID='txtLocalised' NAME='txtLocalised' Value='0' /></TD>
</TR>
</TABLE>
<BR />
<TABLE ID='tblStudentSel'>
<TR>
	<TD COLSPAN="2"><LABEL ID='lblBatch'>Graduating Class</LABEL></TD>
	
	<TD><LABEL ID='lblSelection'>Generate transcripts for: </LABEL></TD>
</TR>
<TR>
	<TD>
		<SELECT MULTIPLE="multiple" SIZE="8" ID="lstGraduatingStudents" NAME="lstGraduatingStudents">
		<?php
			echo $strOptions;
		?>
		</SELECT>
	</TD>
	<TD>
		<LABEL ID="btnAdd" class="link_button" onClick="move(this.form.lstGraduatingStudents, this.form.lstTransStudents)">>></LABEL>
		<BR /><BR /><BR />
		<LABEL ID="btnRemove" class="link_button" onClick="move(this.form.lstTransStudents, this.form.lstGraduatingStudents)"><<</LABEL>
	</TD>
	<TD>
		<SELECT MULTIPLE="multiple" SIZE="8" ID="lstTransStudents" NAME="lstTransStudents[]">
			<OPTION Value="">No Student Selected</OPTION>
		</SELECT>
	</TD>
</TR>
<TR>
	<TD COLSPAN="3" align="CENTER">
		<BR /><BR /><INPUT TYPE="SUBMIT" Value="Generate Transcripts" class="link_button" onClick="if(anyStudentSelected(this.form.lstTransStudents)==false){alert('Please select at least one student.'); return false;};" />
	</TD>
</TR>
</TABLE>
<BR /><BR /><BR />
<INPUT TYPE='hidden' ID='hdfProg' NAME='hdfProg' VALUE='<?php echo $_POST['cboProg']; ?>'>
<INPUT TYPE='hidden' ID='hdfGradYear' NAME='hdfGradYear' VALUE='<?php echo $_POST['cboGradYear']; ?>'>
</FORM>

<SCRIPT src="Pikaday/pikaday.js"></SCRIPT>
<SCRIPT>

    var picker = new Pikaday(
    {
        field: document.getElementById('txtAwardDt'),
        firstDay: 1,
        minDate: new Date(2000, 0, 1),
        maxDate: new Date(2020, 12, 31),
        yearRange: [2000,2020]
    });

</SCRIPT>
</BODY>

</HTML> 