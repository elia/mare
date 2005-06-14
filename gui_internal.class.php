<?php

require_once('gui.class.php');

class GUI_internal extends GUI{
	
	public function print_header($title = '') {
		
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>MaRE<? echo empty($title)?"":" - $title";?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="MaRE.css" rel="stylesheet" type="text/css">
<link href="inMaRE.css" rel="stylesheet" type="text/css">
</head>

<body leftmargin="0" topmargin="0">
<table width="100%" border="0" cellpadding="10" cellspacing="0">
  <tr> 
    <td height="55" colspan="2"><font size="5"><strong> </strong></font> 
      <table border="0" cellpadding="0" cellspacing="0" bgcolor="#FFFFFF">
	<tr>
     <td height="65" valign="bottom"><strong>User:</strong> <br>
	  <strong><? echo $_SESSION['user']['name']." ".$_SESSION['user']['surname']; ?></strong><br>
	  <font size="2">[ <a href="home.php">Home</a> , <a href="login.php?status=logout">Log 
	  Out</a> ]</font></td>
        </tr>
      </table>
      </td>
  </tr>
  <tr> 
    <td colspan="2">
		<?
	}
	
	public function print_row_delimiter() {
	
	
	?>
</td>
  </tr>
  <tr> 
    
  <td colspan="2">
<?
	
	}
	
	public function print_footer() {
		
		?>
   </td>
  </tr>
  <tr>
    <td colspan="2" bgcolor="#FFFFFF"><font size="1">
		<p align="center">a <font color="#FF9900">MA</font>il<font color="#FF9900">R</font>epository<font color="#FF9900">E</font>nviroment 
	<br />
	<font color="#CCCCCC">Last Update: 14 June 2005<br />
	graphic project and design: &copy; 2004-2005 Elia Schito
		| database design and management: &copy; 2004-2005 Riccardo Morandotti, Elia Schito</font>
        </p></font>
      </td>
  </tr>
</table>
</body>
</html>




		<?
	}
	
}

?>