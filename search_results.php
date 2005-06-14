<?php
require_once('logger.class.php');
require_once('gui_internal.class.php');
require_once('db_cover.class.php');
require_once('db_mare.class.php');
session_start();
// verificare validità login
Logger::verify_log();
// prepariamo il DB
$my_db = new DB_mare();

$printer = new GUI_internal();
$printer->print_header("Search Results"); // e stampiamo l'inizio pagina

$date_start = "".$_POST['date_start']." ".$_POST['time_start']."";
$date_end = "".$_POST['date_end']." ".$_POST['time_end']."";

$total_msgs = $my_db->get_array ("SELECT count(*) FROM messaggio 
				WHERE date >= '$date_start' AND date <= '$date_end'
				AND id_utente=".$_SESSION['user']['id'].";");
$total_msgs = $total_msgs[0]['count'];

///////////CONDIZIONI E JOIN
$conditions = '';
$fld_conditions = '';
$conditions = '';
$joins = ' messaggio ';
$and_or = $_POST['search_method'];
$searched_fields = '';
$searched_folder = '';
if (!empty($_POST['folder'])){//ok questo però è speciale: salva in $fld_conditions
	//per le cartelle mettiamo un AND obbligatorio...
	$searched_folder = ' - '.$_POST['folder'];
	$folder_column = ", id_cartella";
	$joins = "($joins RIGHT JOIN inclusione_cartella_msg AS inc_cart 
				ON (messaggio.id_messaggio=inc_cart.id_messaggio))";
	if (!isset($_POST['subfolders'])) {
		$fld_conditions .= " (id_cartella = ".$_POST['folder_id']." ) ";
	} else if ($_POST['subfolders']=='true'){
		// becchiamo tutte le sottocartelle
		$folders = $my_db->get_all_subfolders($_POST['folder_id']);
		foreach ($folders as $key=>$fld){
			$internal_or = $key==0?'':' OR ';
			$fld_conditions.="$internal_or id_cartella = ".$fld['id_cartella']." ";
		}
		$fld_conditions = "$fld_conditions";
	}
}
if (!empty($_POST['subject'])){
	$searched_fields .= ' - Subject ';
	$tmp_conditions = " subject LIKE '%".$_POST['subject']."%' ";
	$conditions .= empty($conditions)?" $tmp_conditions ":" $and_or $tmp_conditions ";
}

if (!empty($_POST['body'])){
	$searched_fields .= ' - Message body ';
	$tmp_conditions = " (testo_normale LIKE '%".$_POST['body']."%' 
					 OR testo_html LIKE '%".$_POST['body']."%') ";
	$conditions .= empty($conditions)?" $tmp_conditions ":" $and_or $tmp_conditions ";
}
if (!empty($_POST['addresser']) || !empty($_POST['addressee'])){//ok
	$joins = "($joins RIGHT JOIN indirizzo_in_messaggio AS ind_msg 
					ON (messaggio.id_messaggio=ind_msg.id_messaggio))";
	if (!empty($_POST['addresser'])){
		$searched_fields .= ' - Addresser ';
		$types = array('from', 'sender');
		foreach($types as $type){
			$tmp_conditions = " $and_or ( tipo_header='$type' AND indirizzo_email 
										LIKE '%".$_POST['addresser']."%' ) ";
			$conditions .= empty($conditions)?" ($tmp_conditions) ":" $and_or ($tmp_conditions) ";
		}
	}
	if (!empty($_POST['addressee'])){
		$searched_fields .= ' - Addressee ';
		$types = array('to', 'cc', 'bcc');
		foreach($types as $type){
			$tmp_conditions = " ( tipo_header='$type' AND indirizzo_email 
										LIKE '%".$_POST['addresser']."%' ) ";
			$conditions .= empty($conditions)?" ($tmp_conditions) ":" $and_or ($tmp_conditions) ";
		}
	}
}
if (!empty($_POST['atachment'])){//ok
	$searched_fields .= ' - Attachment Name ';
	$joins = "($joins RIGHT JOIN file_allegato AS file 
					ON (messaggio.id_messaggio=file.id_messaggio))";
	$tmp_conditions = " ( nome_allegato LIKE '%".$_POST['attachment']."%' ) ";
	$conditions .= empty($conditions)?" ($tmp_conditions) ":" $and_or ($tmp_conditions) ";
}


$conditions = empty($conditions)?'':" AND ($conditions) ";
$fld_conditions = empty($fld_conditions)?'':" AND ($fld_conditions) ";
$query = "SELECT DISTINCT messaggio.id_messaggio $folder_column FROM $joins
				WHERE date >= '$date_start' AND date <= '$date_end' 
				AND id_utente=".$_SESSION['user']['id']." $conditions $fld_conditions ;";


$search = $my_db->get_array ($query);

#echo '<pre>'.$query.'<br>'.$joins.'<br>'.$conditions.'<br>';var_dump($search);exit;

?>
<center><h1>Search Results:</h1>
<font size="2">[ <a href="search.php">go back to Search page</a> ]</font>
<br /><font size=2>Total messages considered: <?=$total_msgs;?> - Messages found: <?=count($search);?></font>
<br /><font size=2>Searched fields: <?=$searched_fields;?> - </font>

</center>
<table bgcolor="#0099cc" border="0" cellpadding="0" cellspacing="1" width="100%">
 <tbody><tr align="center" bgcolor="#ffffff" valign="middle"> 
  <td>
  <table border="0" width="100%" align="left" cellpadding="5" cellspacing="1">

			<tr> 
			 <td colspan="4"><strong>Messages:</strong></td>
			</tr>
			<tr><td><font size="2">
<?php
if (is_array($search)){
	require_once('folder.class.php');
	$row_separator = "\n</font></td></tr><tr><td><font size=\"2\">\n";
	$col_separator = "\n</font></td><td align=\"right\" nowrap><font size=\"2\">\n";
	foreach($search as $message){
		$msg = $my_db->get_message($message['id_messaggio']);
		$fld_id = isset($folder_column)?$message['id_cartella']:null;
		Folder::print_message($msg['id_messaggio'],$msg['subject'],$msg['date'], $msg['email_account'], $col_separator, $fld_id);
		echo $row_separator;
	}
	?>
		</font></td><td></td><td></td></tr>
	<?
}
?>
</table>
  </td>
 </tr>
</tbody></table>
<?php

$printer->print_footer();

?>