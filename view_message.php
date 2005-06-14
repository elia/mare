<?php
require_once('logger.class.php');
require_once('gui_internal.class.php');
require_once('db_mare.class.php');
require_once('message.class.php');
session_start(); 
// verificare validità login
Logger::verify_log();

///////MESSAGE
//se non specifichi il messaggio ti mando all'albero delle cartelle
if (!isset($_GET['id']) || $_GET['id']=="") header("Location: folders.php");
//istanziamo l'obj messaggio
$msg = new Message($_GET['id']);
if (!$msg->is_valid_id()) header("Location: folders.php");



// prepariamo GUI e DB
$printer = new GUI_internal();
$my_db = new DB_mare();
// e stampiamo l'inizio pagina
$printer->print_header("View message - ".$msg->get_subject()); 

/////FOLDERS
if (($_GET['fld']) && $msg->is_valid_folder($_GET['fld'])) {
	echo '
	<table width="100%" border="0" cellpadding="0" cellspacing="1" bgcolor="#0099CC">
	 <tr valign="middle" bgcolor="#FFFFFF"> 
	  <td><table width="100%" border="0" cellspacing="0" cellpadding="5">
	    <tr>
		 <td><a href="view_folder.php?id='.$_GET['fld'].'">
		  <img src="icons/freccia_no.gif" width="16" height="16" align="absmiddle">
		   <u> Current Folder </u></a>: ';
	// stampiamo il path per questa cartella
	$separator = " &gt; ";
	echo "<strong>";
	foreach($my_db->get_path_to_folder($_GET['fld']) as $val)
		echo '<a href="view_folder.php?id='.$val['id_cartella'].'">'.$val['nome'].'</a>'.$separator;
	echo '
	  </strong></td>
	 <td> <div align="right"><a href="view_folder.php?id='.$_GET['fld'].'"><font size="2">view 
	   parent <img src="icons/freccia_n.gif" alt="view parent folder contents" width="16" height="16" align="absbottom"></font></a></div></td>
	</tr>
   </table></td>
 </tr>
</table><br>';
}
////END FOLDERS


////HTML BODY
{
	 ?>
<table width="100%" border="0" cellpadding="0" cellspacing="1" bgcolor="#0099CC">
 <tr align="center" valign="middle" bgcolor="#FFFFFF"> 
  <td>
   
   <table width="100%" border="0" cellspacing="0" cellpadding="5">
	<tr> 
	 <td><strong>Actions:</strong> <div align="right"></div></td>
	 <td><a href=<?='"javascript:void(window.open(\'message_tools.php?op=copy&print=tree&id='.$msg->get_id()
	   		.'\', \'complete_html\', \'toolbar=no,menubar=no,status=no,location=no,resizable=yes,height=400,width=600\'))"';
	   ////////////////////////////////////////////////
	   ?>><img src="icons/freccia_se.gif" width="16" height="16" align="absbottom"> 
	  copy</a><br><font size="1">(copy in another folder)</font></td>
	 <?
	 
	 if (($_GET['fld']) && $msg->is_valid_folder($_GET['fld'])) {
		 ?>
	 <td><a href=<?='"javascript:void(window.open(\'message_tools.php?op=move&print=tree&id='
	 .$msg->get_id().'&fld='.$_GET['fld']
	 .'\', \'completeHTML\', \'toolbar=no,menubar=no,status=no,location=no,resizable=yes,height=400,width=600\'))"';
	   ////////////////////////////////////////////////
	   ?>><img src="icons/freccia_no.gif" width="16" height="16" align="absbottom"> 
	  move</a><br><font size="1">(move to another folder)</font></td>
	 <td>
	  <a href=<?='"message_tools.php?op=del_this&fld='.$_GET['fld'].'&id='.$msg->get_id().'"'; ?>><img src="icons/ics.gif" width="16" height="16" align="absbottom"> 
	  delete</a><br><font size="1">
	  (only this copy of the message)</font>
	 </td>
	 	<?
	 }
	 ?>
	 <td><a href=<?='"message_tools.php?op=del_all&fld=2&id='.$msg->get_id().'"'; ?>><img src="icons/ics.gif" width="16" height="16" align="absbottom"> 
	  delete all</a><br><font size="1">
	  (all copies in any folder)</font></td>
	</tr>
   </table>
   
  </td>
 </tr>
</table>
<br>
<table width="100%" border="0" cellpadding="0" cellspacing="1" bgcolor="#0099CC">
 <tr align="center" valign="middle" bgcolor="#FFFFFF"> 
  <td><table width="100%" border="0" cellspacing="0" cellpadding="5">
	<tr> 
	 <td><strong>Envelope:</strong></td>
	 <td align="right">
	 <?
	 
	 if ($msg->is_reply()){
		 ?>
	    <div align="right"><font size="2"><a href=<?
		$thread_fld = $_GET['fld']?'&fld='.$_GET['fld']:"";
		echo '"view_message_thread.php?id='.$msg->get_id().$thread_fld.'"';
	   ////////////////////////////////////////////////
	   ?>>show reply 
	    thread <img src="icons/freccia_s.gif" width="16" height="16" align="absbottom"></a>,
		<?
	 }
	 
	   ?>
	   <a href=<?='"javascript:void(window.open(\'message_tools.php?op=html&frame=set&id='.$msg->get_id()
	   		.'\', \'completeHTML\', \'toolbar=no,menubar=no,status=no,location=no,resizable=yes,height=400,width=600\'))"';
	   ////////////////////////////////////////////////
	   ?>>complete html body <img src="icons/reload.gif" width="16" height="16" align="absbottom"></a></font></div></td>
	</tr>
	<tr> 
	 <td colspan="2">
	 <table width="100%" border="0" cellspacing="2" cellpadding="0">
	   <tr> 
		<?
		$separator = "\n</tr><tr>\n";
		$msg->print_addresses($separator);
		///////////////////////////////////
		?>
	   </tr>
	   <tr> 
		<td colspan="3"><font size="2"><strong><font size="2">Subject: <u>
		 <?
		 $subject = $msg->get_subject();
		 echo empty($subject)?"--no subject--":$subject;
		 //////////////////////////////
		 ?></u></strong></font></td>
		<?
		if ($msg->is_forwarded())
			echo '<td align="right"><font size=1>This message seems to be <b>forwarded</b></font></td>';
		?>
	   </tr>
	  </table></td>
	</tr>
   </table></td>
 </tr>
</table>
<?
if (is_array($msg->get_attachments())) {
?>
<br>
<table width="100%" border="0" cellpadding="0" cellspacing="1" bgcolor="#0099CC">
 <tr align="center" valign="middle" bgcolor="#FFFFFF"> 
  <td><table width="100%" border="0" cellspacing="0" cellpadding="5">
	<tr> 
	 <td><font size="2"><strong>Attachments:</strong> 
	 <?
	 $attach = $msg->get_attachments();
	 foreach($attach as $val) {
		 echo '<a href="'.$val['percorso'].$val['nome_allegato'].'" target="_blank"><img src="icons/attach.gif" width="16" height="16" 
		 align="absbottom"> '
		 .$val['nome_allegato'].'</a>, ';
	 }
	 ?>
	  </font></td>
	</tr>
   </table></td>
 </tr>
</table>
<? } ?>
<br>
<table width="100%" border="0" cellpadding="0" cellspacing="1" bgcolor="#0099CC">
 <tr align="center" valign="middle" bgcolor="#FFFFFF"> 
  <td><table width="100%" border="0" cellspacing="0" cellpadding="5">
	<tr>
	 <td>
	  <code> 
<?
}
////END HTML BODY

// per quendo c'è direttamente il testo, usare: htmlentities()
//che trasforma > in &gt;
//to print in browser
if (!is_null($msg->get_text())) {
	echo "".nl2br($msg->get_text())."";
} else {
	//$html = file_get_contents("message.htm");
	$html = $msg->get_html();
	$html = preg_replace("'<style[^>]*>.*</style[^>]*>'siU",'',$html);
	$html = preg_replace("'<script[^>]*>.*</script[^>]*>'siU",'',$html);
	$text = strip_tags($html, "<br><a><p>");
	echo "".$text."";
}
?>
	  </code>
	 </td>
	</tr>
   </table></td>
 </tr>
</table>
<?php
$printer->print_footer(); // e stampiamo la fine pagina
?>
