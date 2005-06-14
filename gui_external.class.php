<?php

require_once('gui.class.php');

class GUI_external extends GUI{
	private $bottom_type;
	
	
	public function print_header($title = '') {
		
		?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<html>
<head>
<title>MaRE<? echo empty($title)?"":" - $title";?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="MaRE.css" rel="stylesheet" type="text/css">
</head>

<body>

<table width="100%" height="100%" border="0" cellpadding="0" cellspacing="10">
	<tr> 
		
  <td width="50%" rowspan="2" align="center" valign="middle" class="outMaREtitleCell"> 
   <table border="0" cellspacing="5" cellpadding="5">
	<tr>
	 <td><img src="immagini/logo_out_mare.JPG" alt="MaRE - a mAIL rEPOSITORY eNVIROMENT" width="274" height="110" align="absmiddle"></td>
	</tr>
   </table> </td>
		
  <td width="50%" height="100%" valign="middle"> 




		
		
		<?
	}
	
	
	
	public function print_footer($type = 'new_user') {
		$this->bottom_type = $type;
		switch ($this->bottom_type) {
			case 'new_user': {
			?>
	<tr>
		<td>
			<table width="100%" border="0" cellspacing="0" cellpadding="0">
				<tr> 
					
	 <td width="16" height="32" rowspan="2" valign="top"><a href="new_user.php"><img src="icons/freccia_ne.gif" alt=" " width="16" height="16" border="0"></a></td>
					
	 <td width="96%" align="left" valign="top"><a href="new_user.php">REGISTER 
	  NEW USER</a></td>
				</tr>
				<tr> 
					<td align="left" valign="top"><font size="2">for brand new users</font></td>
				</tr>
			</table>
		</td>
	</tr>
			<?
			}
			break;
			case 'login': {
			?>

</td>
	</tr>
	<tr>
		<td>
			<table width="100%" border="0" cellspacing="0" cellpadding="0">
				<tr> 
					
	 <td width="16" height="32" rowspan="2" valign="top"><a href="login.php"><img src="icons/freccia_ne.gif" alt=" " width="16" height="16" border="0"></a></td>
					
	 <td width="96%" align="left" valign="top"><a href="login.php">JUMP TO LOGIN 
	  PAGE</a></td>
				</tr>
				<tr> 
					
	 <td align="left" valign="top"><font size="2">for accounted users</font></td>
				</tr>
			</table>
		</td>
	</tr>
</table>
</body>
</html>


			<? }
			break;
		}
	}
}

?>