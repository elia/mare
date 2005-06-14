<?php
require_once('logger.class.php');
require_once('gui_internal.class.php');
require_once('db_mare.class.php');
require_once('folder.class.php');
session_start(); 
// verificare validità login
Logger::verify_log();
$my_db = new DB_mare();
// prepariamo GUI e DB
$printer = new GUI_internal();
$fld = new Folder();
///////////////////////////////////////////////////////////////////////////
$printer->print_header("Folders"); // e stampiamo l'inizio pagina
if (isset($_GET['id']))
	$fld = new Folder($_GET['id']);
else
	$fld = new Folder();
// Javascript e inizio tabella e form1
Folder::print_form_stuff();

{
	?> 
<table width="100%" border="0" cellpadding="0" cellspacing="1" bgcolor="#0099CC">
 <tr align="center" valign="middle" bgcolor="#FFFFFF"> 
  <td><table width="100%" border="0" cellspacing="0" cellpadding="5">
	<tr> 
	 <td><strong>Folders' Tree:</strong> <div align="right"></div></td>
	 <td><div align="right"><a href="javascript: newFolder()"><img src="icons/freccia_se.gif" width="16" height="16" align="absbottom"> 
	   create here a new folder</a></div></td>
	</tr>
	<tr> 
	 <td colspan="2"> 
	  <?php
$my_db->print_folder_tree();


?>
	 </td>
	</tr>
   </table></td>
 </tr>
</table>
<br>
<?
 }
$printer->print_footer(); // e stampiamo la fine pagina
?>
