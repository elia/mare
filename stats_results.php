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
$printer->print_header("STATS Results"); // e stampiamo l'inizio pagina

$date_start = "".$_POST['date_start']." ".$_POST['time_start']."";
$date_end = "".$_POST['date_end']." ".$_POST['time_end']."";
$total_msgs = $my_db->get_array ("SELECT count(*) FROM messaggio 
				WHERE date >= '$date_start' AND date <= '$date_end'
				AND id_utente=".$_SESSION['user']['id'].";");
$total_msgs = $total_msgs[0]['count'];

$stat_field = $_POST['stat_field'];
switch ($stat_field) {
	case 'user_agent':{
		$title = "User Agents";
		$stats = $my_db->get_array (
				"SELECT user_agent AS nome, count(*) AS messaggi FROM messaggio 
				WHERE date >= '$date_start' AND date <= '$date_end'
				AND id_utente=".$_SESSION['user']['id']." 
				GROUP BY user_agent
				ORDER BY messaggi DESC;");
		
		break;
	}
	case 'from':{
		$title = ($stat_field=='from')?"From addressers":$title;
	}
	case 'reply-to':{
		$title = ($stat_field=='reply-to')?"Reply-To addressers":$title;
	}
	case 'sender':{
		$title = ($stat_field=='sender')?"Sender addressers":$title;
	}
	case 'cc':{
		$title = ($stat_field=='cc')?"Carbon-Copy addressees":$title;
	}
	case 'bcc':{
		$title = ($stat_field=='bcc')?"Blind-Carbon-Copy addressees":$title;
	}
	case 'to':{
		$title = ($stat_field=='to')?"To addressees":$title;
		$stats = $my_db->get_array (
				"SELECT indirizzo_email as nome, count(*) as messaggi
				FROM messaggio NATURAL JOIN (
						SELECT DISTINCT ON (id_messaggio, tipo_header, indirizzo_email) 
						id_messaggio, tipo_header, indirizzo_email
						FROM indirizzo_in_messaggio 
						ORDER BY id_messaggio ASC, 
							tipo_header ASC, indirizzo_email ASC) 
						AS ind_msg
				WHERE date >= '$date_start' AND date <= '$date_end' 
				AND tipo_header='$stat_field'
				AND id_utente=".$_SESSION['user']['id']." 
				GROUP BY indirizzo_email
				ORDER BY messaggi DESC;");
		
		break;
	}
	case 'received':{
		$title = "Received hosts";
		//la sottoquery serve perché un msg con due id_host uguali conti una volta sola
		$stats = $my_db->get_array (
				"SELECT id_host AS nome, count(*) AS messaggi 
				FROM messaggio NATURAL JOIN (
						SELECT DISTINCT ON (id_messaggio, id_host) id_messaggio, id_host
						FROM received ORDER BY id_messaggio ASC, id_host ASC) AS dist_receiv
				WHERE messaggio.date >= '$date_start' AND messaggio.date <= '$date_end' 
				AND id_utente=".$_SESSION['user']['id']." 
				GROUP BY id_host
				ORDER BY messaggi DESC
				");	
				
	}
}


?>
<center><h2>Statistic for <?=$title;?>:</h2></center>
<table border="0" width="100%" align="left" cellpadding="5" cellspacing="1">
 <tr>
  <td width="50%" align="right"><font size="2">[ <a href="stats.php">go back to STATS page</a> ]</font></td>
  <td width="50%" align="left"><font size=2>Total messages considered: <?=$total_msgs;?></font></td>
 </tr>
<?php
if (is_array($stats)){
	foreach($stats as $stat){
		if (!$stat['nome']){
			$stat['nome']="<i>NULL</i>";
		}
		if ($stat['messaggi']>400){
			$length = 400;
			$dots='<img src="icons/spacer_orange.gif" width=5 height="10" align="absbottom">
			<img src="icons/spacer_orange.gif" width=3 height="10" align="absbottom">
			<img src="icons/spacer_orange.gif" width=1 height="10" align="absbottom">';
		} else {
			$dots="";
			$length = $stat['messaggi'];
		}
		?>
		 <tr>
		  <td width="50%" align="right"><font size="2"><?=$stat['nome']?></font></td>
		  <td width="50%" align="left" nowrap><font size="1"><img src="icons/spacer_orange.gif" 
			width=<?=$length?> height="10" align="absbottom">
			<?=$dots." ".$stat['messaggi']?>
			</font></td>
		 </tr>
		<?
	}
	echo '</table>';
}else echo '</table><p><center><font size=2><em>No results available for this statistic</em></font></center>';
?>

<?php

$printer->print_footer();

?>