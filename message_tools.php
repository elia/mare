<?php
/*
op = del_this, del_all, move, copy, html, thread

html: {
	frame = set, top, main
	id = [id del msg]
}
del_this: {
	id = [id del msg]
	fld = [id della cartella corrente]
}
del_all: {
	id = [id del msg]
}
move: {//controllare se a destinazione esiste già
	print = tree {
		//qui stampa l'albero in cui scegliere la destinazione
		id = [id del msg]
		fld = [id della cartella corrente]
		// per identificare la tupla in inclusione_cartella_msg
	}
	!print { // se non c'è proprio tra i GET
		//qui fa l'operazione
		id = [id del msg]
		fld = [id della cartella corrente]
		fld_dest = [id della cartella di destinazione]
	}
}
copy: {//controllare se a destinazione esiste già
	print = tree {
		//qui stampa l'albero in cui scegliere la destinazione
		id = [id del msg]
	}
	!print { // se non c'è proprio tra i GET
		//qui fa l'operazione
		id = [id del msg]
		fld_dest = [id della cartella di destinazione]
	}
}

*/
require_once('logger.class.php');
require_once('db_mare.class.php');
require_once('message.class.php');
session_start(); 
// verificare validità login
Logger::verify_log();
// prepariamo il DB
$my_db = new DB_mare();

if (!isset($_GET['op'])) die('Access denied tio this file.');
switch ($_GET['op']) {
	case "html": {
		///////MESSAGE
		//se non specifichi il messaggio ti mando all'albero delle cartelle
		if (!isset($_GET['id']) || $_GET['id']=="") header("Location: folders.php");
		//istanziamo l'obj messaggio
		$msg = new Message($_GET['id']);
		if (!$msg->is_valid_id()) header("Location: folders.php");
		// e stampiamo l'inizio pagina
		switch ($_GET['frame']){
			case "set":	{
				?>
				<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">
				<html>
				<head>
				<title>MaRE<? echo " - View HTML - ".$msg->get_subject();?></title>
				<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
				</head>
				
				<frameset rows="91,*" cols="*" framespacing="3" frameborder="yes" border="3" bordercolor="#FFCC66">
				 <frame src=<?='"message_tools.php?op=html&frame=top&id='.$_GET['id'].'"'; 
							/////////////////////////////////////////////			
							?> name="topFrame" frameborder="no" scrolling="NO" >
				 <frame src=<?='"message_tools.php?op=html&frame=main&id='.$_GET['id'].'"'; 
							/////////////////////////////////////////////			
							?> name="html_body" frameborder="no">
				</frameset>
				<noframes><body>
				I advise you to get a frame-compliant browser...
				</body></noframes>
				</html>
				<? 
				break;
			}
			case "top": {
				
				?>
				<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
				<html>
				<head>
				<title>MaRE<? echo " - View HTML - ".$msg->get_subject();?></title>
				<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
				<link href="MaRE.css" rel="stylesheet" type="text/css">
				<link href="inMaRE.css" rel="stylesheet" type="text/css">
				</head>
				
				<body leftmargin="0" topmargin="0">
				<p align="center"><a href="javascript: top.close()"><font size="2">
				<img src="icons/ics.gif" width="16" height="16" align="absbottom"> CLOSE 
				</font></a> </p>
				<table border="0" cellpadding="3" cellspacing="2" bgcolor="#E1EBF2">
				 <tr bgcolor="#FFFFFF"> 
				  <td width="175"><font size="2"><strong>Date: </strong> 
				   <?= $msg->get_date();
					///////////////////
					?>
				   </font></td>
				  <td width="250"><font size="2"><strong>Subject:</strong> 
				   <?= $msg->get_subject();
					///////////////////
					?>
				   </font> </td>
				 </tr>
				</table>
				</body>
				</html>
				<?
				break;
			}
			case "main": {
				
				if (!is_null($msg->get_html())) {
					echo $msg->get_html();
				} else {
					?>
					<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
					<html>
					<head>
					<title>MaRE - View HTML - <? echo $msg->get_subject();?></title>
					<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
					<link href="MaRE.css" rel="stylesheet" type="text/css">
					</head>
					
					<body leftmargin="0" topmargin="0">
					<br>
					<br>
					<br>
					<br>
					<br>
					<h2 align="center">No HTML body found for this message.</h2></body></html>
					<?
				}
			break;
			}
		}
		break;
	}
	case "del_this": {
		if (isset($_GET['id'],$_GET['fld'])){
			$my_db->del_message_from_folder($_GET['id'],$_GET['fld']);
		}
		header('Location: view_folder.php?id='.$_GET['fld']);
		break;
	}
	case "del_all": {
		if (isset($_GET['id'],$_GET['fld'])){
			$my_db->del_message($_GET['id']);
		}
		header('Location: folders.php');
		break;
	}
	case "copy": {
		if (isset($_GET['print'],$_GET['id'])){
			?>
			<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
			<html>
			<head>
			<title>MaRE - Copy to folder:</title>
			<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
			<link href="MaRE.css" rel="stylesheet" type="text/css">
			<link href="inMaRE.css" rel="stylesheet" type="text/css">
			</head><body leftmargin="0" topmargin="100">
			<p align="center"><a href="javascript: top.close()"><font size="2"> <img src="icons/ics.gif" width="16" height="16" align="absbottom"> 
			 CLOSE</font></a> 
			<p align="center">Please click on the destination folder:
			<div align="center">
			 <table border="0" align="center" cellpadding="5" cellspacing="1">
			  <tr>
			   <td>
				<?
				
				$my_db->print_folder_tree_msg('copy', $_GET['id']);
				
				?>
				</td>
			  </tr>
			 </table>
			</div></p>
			</body>
			</html>
			<?
			break;
		} else if (isset($_GET['id'],$_GET['fld_dest'])){
			$my_db->add_message_to_folder($_GET['id'], $_GET['fld_dest']);
			header('Location: view_folder.php?id='.$_GET['fld_dest']);
		}
		exit;
		header('Location: folders.php');
		break;
	}
	case "move": {
		if (isset($_GET['print'],$_GET['id'])){
			?>
			<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
			<html>
			<head>
			<title>MaRE - Copy to folder:</title>
			<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
			<link href="MaRE.css" rel="stylesheet" type="text/css">
			<link href="inMaRE.css" rel="stylesheet" type="text/css">
			</head><body leftmargin="0" topmargin="100">
			<p align="center"><a href="javascript: top.close()"><font size="2"> <img src="icons/ics.gif" width="16" height="16" align="absbottom"> 
			 CLOSE</font></a> 
			<p align="center">Please click on the destination folder:
			<div align="center">
			 <table border="0" align="center" cellpadding="5" cellspacing="1">
			  <tr>
			   <td>
				<?
				
				$my_db->print_folder_tree_msg('move', $_GET['id'], $_GET['fld']);
				
				?>
				</td>
			  </tr>
			 </table>
			</div></p>
			</body>
			</html>
			<?
			break;
		} else if (isset($_GET['id'],$_GET['fld'],$_GET['fld_dest'])){
			$my_db->add_message_to_folder($_GET['id'], $_GET['fld_dest']);
			$my_db->del_message_from_folder($_GET['id'], $_GET['fld']);
			header('Location: view_folder.php?id='.$_GET['fld_dest']);
		}
		exit;
		header('Location: folders.php');
		break;
	}
}









?>