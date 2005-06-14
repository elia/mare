<?php

/*
op = new, reg, mod, del, del_fax, del_tel, new_email, new_fax, new_tel, new_email,

new: {
	view = form
	| POST: {
		name
		surname
		address
		email
		-->view_contact
	}
}
reg: {
	id_msg = [id del msg]
	type
	pos
	--> view_contact
}
mod: 

*/

//var_dump($_GET);

require_once('logger.class.php');
require_once('db_mare.class.php');
//require_once('contact.class.php');
session_start(); 
// verificare validità login
Logger::verify_log();
// prepariamo GUI e DB
$my_db = new DB_mare();
// e stampiamo l'inizio pagina


switch ($_GET['op']) {
	case 'new':{
		$id = $my_db->get_contact_new_id();
		$my_db->add_contact($id, $_POST['name'], $_POST['surname'], $_POST['address']);
		if (trim($_POST['email'])!="")
			$my_db->add_contact_email($id, $_POST['email']);
		if (trim($_POST['tel'])!="")
			$my_db->add_contact_number($id, $_POST['tel'], 'tel');
		if (trim($_POST['fax'])!="")
			$my_db->add_contact_number($id, $_POST['fax'], 'fax');
		header("Location: view_contacts.php?id=$id");
		break;
	}
	case 'del':{
		if (isset($_GET['id']))
			$my_db->del_contact($_GET['id']);
		header('Location: view_contacts.php');
		break;
	}
	case 'reg':{
		$address = $my_db->get_message_address($_GET['id_msg'], $_GET['type'], $_GET['pos']);
		$id = $my_db->get_contact_new_id();
		if (empty($address['nome_visualizzato'])){
			$name = explode("@", $address['indirizzo_email']);
			$name = $name[0];
		} else $name = $address['nome_visualizzato'];
		$my_db->add_contact($id, $name, "", "");
		$my_db->add_contact_email($id, $address['indirizzo_email']);
		header("Location: view_contacts.php?id=".$id);
		break;
	}
	case 'mod':{
		switch ($_POST['mod_type']){
			case 'add':{
				if ($_POST['mod_field']=='email'){
					$my_db->add_contact_email($_GET['id'], $_POST['email']);
				} else {
					$my_db->add_contact_number($_GET['id'], $_POST[$_POST['mod_field']], $_POST['mod_field']);
				}
				break;
			}
			case 'edit':{
				switch ($_POST['mod_field']){
					case 'email':{
						$my_db->del_contact_email($_GET['id'], $_POST['old_value']);
						$my_db->add_contact_email($_GET['id'], $_POST['new_value']);
						break;
					}
					case 'tel':
					case 'fax':{
						$my_db->del_contact_number($_GET['id'], $_POST['old_value'], $_POST['mod_field']);
						$my_db->add_contact_number($_GET['id'], $_POST['new_value'], $_POST['mod_field']);
						break;
					}
					case 'name':
					case 'surname':
					case 'address':{
						$my_db->mod_contact($_GET['id'], $_POST['name'], $_POST['surname'], $_POST['address']);
						break;
					}
				}
				break;
			}
			case 'del':{
				if ($_POST['mod_field']=='email'){
					$my_db->del_contact_email($_GET['id'], $_POST['old_value']);
				} else {
					$my_db->del_contact_number($_GET['id'], $_POST['old_value'], $_POST['mod_field']);
				}
				break;
			}
		}
		header("Location: view_contacts.php?id=".$_GET['id']);
		break;
	}
	case 'pop':{
		?>
		<html>
		<head>
		<title>Insert new address</title>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
		<link href="MaRE.css" rel="stylesheet" type="text/css">
		<script language="JavaScript" type="text/JavaScript">
		function submit_address(){
			opener.document.getElementById("address").value = document.getElementById("address_pop").value;
			opener.edit_address();
			window.close();
		}
		</script>
		</head>
		
		<body>
		<table border="0" align="center" cellpadding="5" cellspacing="0">
		 <tr> 
		  <td> <div align="center">Please insert here the new address<br>
			and click on <em>Submit Address</em>:<br>
			<textarea name="address_pop" id="address_pop" cols="40" rows="4" wrap="physical"><?
			$contact = $my_db->get_contact($_GET['id']);
			echo $contact['indirizzo'];
			?></textarea>
			<br>
			<input type="button" name="Submit" value="Submit Address" onClick="submit_address()">
		   </div></td>
		 </tr>
		</table>
		</body>
		</html>
		<?
	}

	
}
//header("Location: view_contacts.php?id=".$_GET['id']);


?>