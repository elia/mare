<?php
require_once('logger.class.php');
require_once('gui_internal.class.php');
require_once('db_mare.class.php');

session_start(); //verifca se da tenere

// verificare validità login
Logger::verify_log();
$my_db = new DB_mare();
// prepariamo la GUI
$printer = new GUI_internal();


if (isset($_GET['view']) && $_GET['view']=='folder_tree') {
	echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
			<html><head><title>MaRE - Select Folder:</title><meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
			<link href="MaRE.css" rel="stylesheet" type="text/css"></head><body leftmargin="20" topmargin="15">';
	?>
	<p align="center"><a href="javascript: top.close()"><font size="2"> <img src="icons/ics.gif" width="16" height="16" align="absbottom"> 
	 CLOSE</font></a> 
	<p align="center">Please click on the destination folder:
	<div align="center">
	 <table border="0" align="left" cellpadding="5" cellspacing="1">
	  <tr>
	   <td>
		<?
		
		$my_db->print_folder_tree_search();
		
		?>
		<script language="JavaScript" >
		function sendFolder(id, name) {
			opener.document.getElementById('folder_id').value = id;
			opener.document.getElementById('folder').value = name;
		}
		</script>
		</td>
	  </tr>
	 </table>
	</div></p>
	<?
	echo '</body></html>';
	
	exit;
}


$printer->print_header("Searches"); // e stampiamo l'inizio pagina

//script controllo form
?>
<script language="JavaScript" >
function trim(s) {
	while (s.substring(0,1) == ' ') {
		s = s.substring(1,s.length);
	}
	while (s.substring(s.length-1,s.length) == ' ') {
		s = s.substring(0,s.length-1);
	}
	return s;
}
function oneAtLeast() {
	if (	trim(document.getElementById("subject").value)!=""
			|| trim(document.getElementById("body").value)!=""
			|| trim(document.getElementById("addresser").value)!=""
			|| trim(document.getElementById("addressee").value)!=""
			|| trim(document.getElementById("folder").value)!="" 
			|| trim(document.getElementById("attachment").value)!="") {
		return true;
	} else {
		alert("You must fill at least one field.");
		return false;
	}
}
function check_date() {
	var rex_date =/^(19|20)\d\d-(0[1-9]|1[012])-(0[1-9]|[12][0-9]|3[01])$/;
	if(document.getElementById("date_start").value != "" && document.getElementById("date_end").value != ""
			&& document.getElementById("date_start").value.match(rex_date) 
			&& document.getElementById("date_end").value.match(rex_date)
			&& (document.getElementById("date_start").value.replace(/-/,"") <= document.getElementById("date_end").value.replace(/-/,""))){
		return true;
	} else {
		alert("Please check the date fields values.");
		return false;
	}
}
function check_time() {
	var rex_time =/^[0-1][0-9]|2[0-3]:[0-5]\d:[0-5]\d$/;
	if(document.getElementById("time_start").value != "" && document.getElementById("time_end").value != ""
			&& document.getElementById("time_start").value.match(rex_time) 
			&& document.getElementById("time_end").value.match(rex_time)){
		if ((document.getElementById("date_start").value == document.getElementById("date_end").value)
				&& (document.getElementById("time_start").value.replace(/:/,"") > document.getElementById("time_end").value.replace(/:/,"")))
			return false;
		return true;
	} else {
		alert("Please check the time fields values.");
		return false;
	}
}
function verifyAndSubmit() {
		if ( check_date() && check_time() && oneAtLeast() )
			document.getElementById("search_form").submit();
}
</script>
<form name="search_form" id="search_form" action="search_results.php" method="post">
 <table width="70%" border="0" align="center" cellpadding="5" cellspacing="1" bgcolor="#6699CC">
  <tr align="center"> 
   <td colspan="2" bgcolor="#FFFFFF"> 
	<h3>Searches</h3>
	<p><font size="2">Search among messages according to 
	 <select name="search_method" id="search_method">
	  <option value="AND">strictly all (AND)</option>
	  <option value="OR">at least one of (OR)</option>
	 </select>
	 the filled fields:<br>
	 <br>
	 </font></p>
	<table border="0" align="center" cellpadding="5" cellspacing="5">
	 <tr align="left" valign="top"> 
	  <td nowrap>Subject:</td>
	  <td> 
	   <input name="subject" type="text" id="subject" size="30"> </td>
	  <td nowrap>Body:</td>
	  <td> 
	   <input name="body" type="text" id="body" size="30"> </td>
	 </tr>
	 <tr align="left" valign="top"> 
	  <td nowrap>Addresser:<br> <font size="1">(From, Sender, ...)</font></td>
	  <td> 
	   <input name="addresser" type="text" id="addresser" size="30"> </td>
	  <td nowrap>Addressee:<br> <font size="1">(To, Cc, ...)</font></td>
	  <td> 
	   <input name="addressee" type="text" id="addressee" size="30"> </td>
	 </tr>
	 <tr align="left" valign="top"> 
	  <td nowrap> 
	   <p>Folder:<br>
		<font size="1">(left <a href="javascript:blank_folder();">blank</a> for 
		<em><br>
		'all folders'</em>)<br>
		</font></p>
	   </td>
	  <td> 
	   <input name="folder" type="text" id="folder" 
	   onClick="void(window.open('search.php?view=folder_tree', 'select_folder', 'toolbar=no,menubar=no,status=no,location=no,resizable=yes,height=200,width=300'))" size="30" readonly>
	   <font size="1">check to include subfolders: 
	   <input name="subfolders" type="checkbox" id="subfolders" value="true" style="border: 0px;">
	   <input name="folder_id" type="hidden" id="folder_id">
	   </font> </td>
	  <td nowrap>Attachment:<br> <font size="1">(only name)</font></td>
	  <td> 
	   <input name="attachment" type="text" id="attachment" size="30"> </td>
	 </tr>
	</table>
	<p>You can specify a date &amp; time range here:<br>
	 <font size="1">(otherwise we assumed you have no e-mails sent before the 
	 XX century nor after today) </font> </p>
	<table align="center">
	 <tr> 
	  <td width="179"><p align="center">Starting Date:<br>
		<font size="1">(format: yyyy-mm-dd)</font></p></td>
	  <td width="123"> <input name="date_start" id="date_start" type="text" size="10" maxlength="10" value="1900-01-01"> 
	  </td>
	 </tr>
	 <tr> 
	  <td><div align="center">Ending Date:<br>
		<font size="1">(format: yyyy-mm-dd)</font></div></td>
	  <td> <input name="date_end" id="date_end" type="text" size="10" maxlength="10" value=<?
	  echo'"'.strftime("%Y-%m-%d").'"'; ?>> </td>
	 </tr>
	 <tr> 
	  <td><div align="center">Starting Time:<br>
		<font size="1">(format: hh:mm:ss)</font> </div></td>
	  <td> <input name="time_start" id="time_start" type="text" size="10" maxlength="8" value="00:00:00"> 
	  </td>
	 </tr>
	 <tr> 
	  <td><div align="center">Ending Time:<br>
		<font size="1">(format: hh:mm:ss)</font> </div></td>
	  <td> <input name="time_end" id="time_end" type="text" size="10" maxlength="8" value="23:59:59"> 
	  </td>
	 </tr>
	</table>
	</td>
  </tr>
  <tr> 
   <td bgcolor="#FFFFFF"><div align="center"><a href="javascript: verifyAndSubmit();"> 
	 <img src="icons/freccia_se.gif" width="16" height="16" align="absmiddle"> 
	 Search!</a> </div></td>
   <td bgcolor="#FFFFFF"><div align="center"><a href="home.php"><img src="icons/freccia_o.gif" width="16" height="16" align="absmiddle"> 
	 go back</a></div></td>
  </tr>
 </table>
</form>

<?php
$printer->print_footer();
?>












