<?php
//Include the library that gets Bradford grades
require_once('parseBradfordData.php');
?>
<!DOCTYPE HTML>
<HTML>
<HEAD>
<TITLE>Namal College - Transcript Generation Script - Step 1</TITLE>
<link rel="stylesheet" href="transGen.css">
</HEAD>
<BODY>
<H1>Namal + Bradford undergraduate transcript generation script</H1>
<FORM ID='frmProgSel' NAME='frmProgSel' ACTION='frmGenTrans.php' METHOD="POST">
<TABLE>
<TR>
	<TD>
		<LABEL FOR="cboGradYear">Choose Year of Graduation: </LABEL>
	</TD>
	<TD>
		<SELECT ID='cboGradYear' NAME='cboGradYear'>
			<OPTION VALUE='2014-5'>2014/5</OPTION>
			<OPTION VALUE='2013-4'>2013/4</OPTION>
		</SELECT>
	</TD>
</TR>
<TR>
	<TD>
		<LABEL FOR="cboProg">Choose Programme: </LABEL>
	</TD>
	<TD>
		<SELECT ID='cboProg' NAME='cboProg'>
			<OPTION VALUE='EE'>Electrical Engineering</OPTION>
			<OPTION VALUE='CS'>Computer Science</OPTION>
			<OPTION VALUE='SE'>Software Engineering</OPTION>
		</SELECT>
		<INPUT TYPE="SUBMIT" Value="Next >>" class="link_button" style='margin-left:20px;' />
	</TD>
</TR>
<TR>
	<TD COLSPAN="2">
		<<THIS SYSTEM IS UNDER CONSTRUCTION AND WILL SOON INCLUDE ADVANCED FEATURES. WELL, AS SOON AS THIS TUTORIAL GETS UNDERWAY. HAMMAD HASSAN, this is waqas>>
	</TD>
</TR>
<TABLE>
</FORM>
</BODY>
</HTML>
