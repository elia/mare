<?php
require_once('logger.class.php');
require_once('gui_internal.class.php');
require_once('db_mare.class.php');
session_start(); 
// verificare validità login
Logger::verify_log();
$my_db = new DB_mare();
// prepariamo GUI e DB
$printer = new GUI_internal();
///////////////////////////////////////////////////////////////////////////
if (!isset($_GET['id']))
	header('Location: folders.php');


// Javascript e inizio tabella e form1
$printer->print_header("Message Thread"); // e stampiamo l'inizio pagina
{
	?> 
<table width="100%" border="0" cellpadding="0" cellspacing="1" bgcolor="#0099CC">
 <tr align="center" valign="middle" bgcolor="#FFFFFF"> 
  <td><table width="100%" border="0" cellspacing="0" cellpadding="5">
	<tr> 
	 <td><strong>Conversation thread for message number: <?='<a href="view_message.php?id='
	 .$_GET['id'].($_GET['fld']?'&fld='.$_GET['id']:'').'"> '.$_GET['id'].'</a>'; ?></strong><br>
	 <font size=2>[ <?='<a href="view_message.php?id='
	 .$_GET['id'].($_GET['fld']?'&fld='.$_GET['id']:'').'"> go back to the message </a>'; ?>]</td>
	</tr>
	<tr> 
	 <td colspan="2"><pre>
<?php
$thread = $my_db->get_message_thread($_GET['id']);
//var_dump($thread);
for ($i=0; $i<count($thread); $i++){
	$curr_msg = $my_db->get_message($thread[$i]);
	
	echo "\n".str_repeat("&nbsp;", $i).'<a href="view_message.php?id='.$thread[$i]
	.'" target="_blank"><img src="icons/freccia_se.gif" width="16" height="16" align="absbottom">'
	.$curr_msg['subject'].'</a><br>';
}
/*
for ($i; $i>0; $i--){
	echo "\n".str_repeat(" ", $i)."</dir>";
}*/

?>
	 </pre></td>
	</tr>
   </table></td>
 </tr>
</table>
<br>
<?
 }
$printer->print_footer(); // e stampiamo la fine pagina
?>
