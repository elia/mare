<?php
require_once('logger.class.php');
require_once('gui_internal.class.php');
require_once('db_mare.class.php');
require_once('folder.class.php');
session_start(); 
// verificare validità login
Logger::verify_log();

// prepariamo GUI e DB
$printer = new GUI_internal();
$my_db = new DB_mare();

{///////FOLDER
//se non specifichi la cartella ti mando all'albero cmpleto
$id = $_GET['id'];
if (!isset($_GET['id'])) $id=Folder::get_inbox_id();
if ($_GET['id']==="") header("Location: folders.php");
//istanziamo l'obj cartella
$fld = new Folder($id);
unset($id);
if (!$fld->is_valid_id()) header("Location: folders.php");
}


// cominciamo a stampare
$printer->print_header("View Folder - ".$fld->get_name()); // stampiamo l'inizio pagina
{ // Content
	?>
<table width="100%" border="0" cellpadding="0" cellspacing="1" bgcolor="#0099CC">
 <tr valign="middle" bgcolor="#FFFFFF"> 
  <td> <table width="100%" border="0" cellspacing="0" cellpadding="5">
	<tr> 
	 <td><a href="folders.php"><img src="icons/freccia_no.gif" width="16" height="16" align="absmiddle">
	 <u>Local Folders</u>
	 </a><strong>:
	 <?php

// stampiamo il path per questa cartella
$separator = " &gt; ";
foreach($my_db->get_path_to_folder($fld->get_id()) as $val)
	echo '<a href="view_folder.php?id='.$val['id_cartella'].'">'.$val['nome'].'</a>'.$separator;
	?>
	 </strong></td>
	 <td> <div align="right">
	 <?= '<a href="view_folder.php?id='.$fld->get_parent_id().'">';
	 ?>
	 <font size="2"> parent 
	   <img src="icons/freccia_n.gif" alt="view parent folder contents" width="16" height="16" align="absbottom"></font></a></div></td>
	</tr>
   </table></td>
 </tr>
</table>
<br>
<table width="100%" border="0" cellpadding="0" cellspacing="1" bgcolor="#0099CC">
 <tr align="center" valign="middle" bgcolor="#FFFFFF"> 
  <td>
  <? 	{/////////ACTIONS
	
?>
   <table width="100%" border="0" cellspacing="0" cellpadding="5">
	<tr> 
	 <td><strong>Actions:</strong> <br>
	  <font size="1">(to be applyed to the current opened folder)</font> </td>
	 <td><?	
	 	// gestione delle operazioni sulle cartelle
		Folder::print_form_stuff();
		?></td>
	 <td><a href="javascript: newFolder()"><img src="icons/freccia_se.gif" width="16" height="16" align="absbottom"> 
	  create</a><br>
	  <font size="1">(create a new folder)</font></td>
	<?
		if ($fld->get_name()!="InBox" || $fld->get_parent_id()!=null ){//se è InBox non la si può cancellare né modificare
			?>
			 <td><a href=<? echo'"javascript: renFolder('.$fld->get_id().', \''.$fld->get_name().'\')"'; ?>><img src="icons/freccia_no.gif" width="16" height="16" align="absbottom"> 
			  rename</a><br> <font size="1">(rename current folder)</font></td>
			 <td><a href=<? echo'"javascript: delFolder('.$fld->get_id().')"'; ?>><img src="icons/ics.gif" width="16" height="16" align="absbottom"> 
			  delete</a> <font size="1"><br>
			  (delete current folder)</font></td>
			 <?
		}
	}?>

	</tr>
   </table></td>
 </tr>
</table>
<br>
<table width="100%" border="0" cellpadding="0" cellspacing="1" bgcolor="#0099CC">
 <tr align="center" valign="middle" bgcolor="#FFFFFF"> 
  <td><table width="100%" border="0" cellspacing="0" cellpadding="5">
<?
{//////////FOLDERS
	//$my_db = new DB_mare();
	unset($val);
	$sub_flds = $my_db->get_subfolders($fld->get_id());
	
	if (is_array($sub_flds)) {
		?>
			<tr> 
			 <td><strong>Folders:</strong></td>
			 <td></td>
			</tr>
			<tr> 
			 <td colspan="2">
		<?
		$separator = "<br>";
		foreach($sub_flds as $val) {
			Folder::print_folder($val['nome'],$val['id_cartella'],$val['id_cartella_superiore']);
			echo $separator;
		}
		?>
			 </td>
			</tr>
		<?
	} else echo "\n<font size='1'>no sub-folders found here</font><br>\n";
}

{//////////MESSAGES
	//$my_db = new DB_mare();
	unset($val);
	$messages = $my_db->get_messages($fld->get_id());
	
	if (is_array($messages)) {
		/* ordiniamo per subject l'array con un ausilio */
		foreach ($messages as $key => $row)
			$subjects[$key] = $row['subject'];
		// questo per far diventare lowercase i subject, così ordina lowercase
		$low_subjects = array_map('strtolower', $subjects);
		array_multisort($low_subjects, SORT_ASC, $messages)
		/* fine ausilio per array_multisort */
		?>
			<tr> 
			 <td colspan="4"><strong>Messages:</strong></td>
			</tr>
			<tr><td><font size="2">
		<?
		$row_separator = "\n</font></td></tr><tr><td><font size=\"2\">\n";
		$col_separator = "\n</font></td><td><font size=\"2\">\n";
		foreach($messages as $val) {
			Folder::print_message($val['id_messaggio'],$val['subject'],$val['date'], $val['email_account'], $col_separator, $fld->get_id());
			echo $row_separator;
		}
		?>
			</font></td></tr>
		<?

	} else echo "\n<font size='1'>no messages found here</font>\n";
}?>
   </table></td>
 </tr>
</table>
<br>
<?
 }//fine content
$printer->print_footer(); // e stampiamo la fine pagina
?>
