<?php
require_once('logger.class.php');
require_once('gui_internal.class.php');
require_once('db_mare.class.php');

session_start(); //verifca se da tenere

// verificare validità login
Logger::verify_log();

// prepariamo la GUI
$printer = new GUI_internal();

$printer->print_header("Statistics"); // e stampiamo l'inizio pagina

//script controllo form
?>

<form name="statistics" id="statistics" action="stats_results.php" method="post">
 <table width="70%" border="0" align="center" cellpadding="5" cellspacing="1" bgcolor="#6699CC">
  <tr> 
   <td colspan="2" bgcolor="#FFFFFF"> <h3 align="center">Statistics</h3>
	<p align="center">Select below the field you want to analyse:</p>
	<p align="center"> 
	 <select name="stat_field" id="stat_field">
	  <option value="" selected>- select field here -</option>
	  <option value="user_agent">user agent</option>
	  <option value="received">received</option>
	  <option value="from">from (addresser)</option>
	  <option value="reply-to">reply-to (addresser)</option>
	  <option value="sender">sender (addresser)</option>
	  <option value="to">to (addressee)</option>
	  <option value="cc">cc (addressee)</option>
	  <option value="bcc">bcc (addressee)</option>
	 </select>
	</p>
	<p align="center">You can specify a date &amp; time range here:<br>
	 <font size="1">(otherwise we assumed you have no e-mails sent before the 
	 XX century nor after today) </font> </p>
	<div align="center">
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
	</div>
	<p align="center">&nbsp;</p>
   </td>
  </tr>
  <tr> 
   <td bgcolor="#FFFFFF"><div align="center"><a href="javascript: verifyAndSubmit();"> 
	 <img src="icons/freccia_se.gif" width="16" height="16" align="absmiddle"> 
	 get STATS</a> </div></td>
   <td bgcolor="#FFFFFF"><div align="center"><a href="home.php"><img src="icons/freccia_o.gif" width="16" height="16" align="absmiddle"> 
	 go back</a></div></td>
  </tr>
 </table>
</form>
<script language="JavaScript" >
function checkField() {
	if (document.getElementById("stat_field").value!="") {
		return true;
	} else {
		alert("You must select a field for the statistic.");
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
		if ( check_date() && check_time() && checkField())
			document.getElementById("statistics").submit();
}
</script>
<?
$printer->print_footer();
?>












