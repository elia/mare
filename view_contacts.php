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


// cominciamo a stampare
$printer->print_header("View Contacts"); // stampiamo l'inizio pagina
{ // Content
	?> 
<table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#0099CC">
 <tr align="left" valign="top" bgcolor="#FFFFFF"> 
  <td width="35%">
   <table border="0" cellspacing="0" cellpadding="5">
	<tr>
	 <td><h2>Address Book:<br>
	   <font size="1">[ <a href="view_contacts.php?id=new">new contact</a> ]</font></h2>
<? 
$contacts=$my_db->get_contacts();
if (is_array($contacts)){
	/* ordiniamo per subject l'array con un ausilio */
	foreach ($contacts as $key => $row){
		$contacts[$key]['screen_name']=trim($row['nome'].' '.$row['cognome']);
		$names[$key] = $contacts[$key]['screen_name'];
	}
	// questo per far diventare lowercase i subject, così ordina lowercase
	$low_names = array_map('strtolower', $names);
	array_multisort($low_names, SORT_ASC, $contacts);
	/* fine ausilio per array_multisort */
	foreach($contacts as $contact){
		echo '<p><strong>'.$contact['screen_name'].'</strong>'
			.' <font size="1">[ <a href="view_contacts.php?id='.$contact['id_contatto']
			.'">view/edit</a>, <a href="contact_tools.php?op=del&id='.$contact['id_contatto']
			.'">delete</a> ]</font><br>';
		echo '<font size="2"><a href="mailto:'.$contact['email'].'">'.$contact['email'].'</a></font></p>';
	}
}
?>
	  </td>
	</tr>
   </table></td>
<?php
if ($_GET['id']){
	if ($_GET['id']=='new'){//vuoi inserire un nuovo contatto?
		?>
		<script language="JavaScript" type="text/JavaScript">
		function trim(s) {
			while (s.substring(0,1) == ' ') {
				s = s.substring(1,s.length);
			}
			while (s.substring(s.length-1,s.length) == ' ') {
				s = s.substring(0,s.length-1);
			}
			return s;
		}
		function checkMail() {
			var address = document.getElementById("email").value;
			if (address=="" ||
				address.match(/^[a-zA-Z0-9._%-]+@[a-zA-Z0-9._%-]+.[a-zA-Z]{2,4}$/)) {
				return true;
			} else {
				alert("Check the E-Mail address: \nthere's something wrong!");
				return false;
			}
		}
		function checkName() {
			var name = trim(document.getElementById('name').value);
			var surname = trim(document.getElementById('surname').value);
			document.getElementById('name').value = name;
			document.getElementById('surname').value = surname;
			
			
			if (name!="" || surname!="") {
				return true;
			} else {
				alert("Check the Name and Surname: \nthey can't be both empty!");
				return false;
			}
		}
		function checkForm(){
			return (checkName() && checkMail());
		}
		</script>
		  <td><form name="new_contact" method="post" action="contact_tools.php?op=new" onSubmit="return checkForm();">
			<table border="0" align="right" cellpadding="10" cellspacing="1" bgcolor="#0099CC">
			 <tr>
			  <td height="397" valign="top" bgcolor="#FFFFFF"> 
			   <h3>New Contact: </h3>
			   <table border="0" cellspacing="1" cellpadding="5">
				<tr align="left" valign="top"> 
				 <td><strong>Name:</strong></td>
				 <td valign="top"> <input name="name" type="text" id="name" size="30" maxlength="30"></td>
				</tr>
				<tr align="left" valign="top"> 
				 <td><strong>Surname:</strong></td>
				 <td valign="top"> <input name="surname" type="text" id="surname" size="30" maxlength="30"></td>
				</tr>
				<tr align="left" valign="top"> 
				 <td><strong>Address:</strong></td>
				 <td valign="top"> <textarea name="address" cols="28" rows="4" wrap="PHYSICAL" id="address"></textarea></td>
				</tr>
				<tr align="left" valign="top"> 
				 <td><strong>E-mail address:</strong><br> 
				  <font size="1">(to add more e-mails wait <br>
				  after the essential data submission)</font></td>
				 <td valign="top"> <input name="email" type="text" id="email" size="30" maxlength="100"> 
				 </td>
				</tr>
				<tr align="left" valign="top"> 
				 <td><strong>Telephone number:</strong><br>
				  <font size="1">(to add more numbers wait <br>
				  after the essential data submission)</font></td>
				 <td valign="top"> <input name="tel" type="text" id="tel" size="30" maxlength="100"> 
				 </td>
				</tr>
				<tr align="left" valign="top"> 
				 <td><strong>Fax number:</strong><font size="1"><br>
				  (to add more numbers wait <br>
				  after the essential data submission)</font></td>
				 <td valign="top"> <input name="fax" type="text" id="fax" size="30" maxlength="100"></td>
				</tr>
				<tr align="left" valign="top">
				 <td align="right"><em><font size="2">(create contact)</font></em> <br></td>
				 <td valign="top"><input name="Submit" type="submit" value="Submit Essential Data"></td>
				</tr>
			   </table>
			   <br>
			   <em> </em> </td>
			</tr>
		   </table></form>
		   </td>
		   <?
	} else {// vuoi vederne modificarne uno vecchio
		$contact = $my_db->get_contact($_GET['id']);
		/*echo "<td>";
		var_dump($contact);
		echo "</td>";*/
		?>
		<script language="JavaScript" type="text/JavaScript">
		function trim(s) {
			while (s.substring(0,1) == ' ') {
				s = s.substring(1,s.length);
			}
			while (s.substring(s.length-1,s.length) == ' ') {
				s = s.substring(0,s.length-1);
			}
			return s;
		}
		function name_for_field(field_name){
			switch (field_name){
				case 'email':
					return 'e-mail address';
				case 'tel':
					return 'telephone number';
				case 'fax':
					return 'fax number';
			}
		}
		function add(field_id) {
			while ((new_val = prompt("Enter here the new "+name_for_field(field_id)+":","type here"))!=null){
				if ( (field_id=='email' && checkMail(new_val))
					|| (field_id!='email' && checkName(new_val)) ) {
					document.getElementById(field_id).value = trim(new_val);
					document.getElementById("mod_type").value = "add";
					document.getElementById("mod_field").value = field_id;
					document.getElementById("mod_form").submit();
					break;
				}
			}
		}
		function edit(field_id) {
			var old_val = document.getElementById(field_id).value;
			while ((new_val = prompt("Enter here the new "+name_for_field(field_id)+":",old_val))!=null){
				if ( (field_id.match(/email\d+/) && checkMail(new_val))
					|| (!field_id.match(/email\d+/) && checkName(new_val)) ) {
					document.getElementById("old_value").value = old_val;
					document.getElementById("new_value").value = new_val;
					document.getElementById(field_id).value = new_val;
					document.getElementById("mod_type").value = "edit";
					document.getElementById("mod_field").value = field_id.match(/[a-z]+/);
					document.getElementById("mod_form").submit();
					break;
				}
			}
		}
		function edit_address() {
			document.getElementById("mod_type").value = "edit";
			document.getElementById("mod_field").value = 'address';
			document.getElementById("mod_form").submit();
		}
		function del(field_id) {
			var old_val = document.getElementById(field_id).value;
			document.getElementById("old_value").value = old_val;
			document.getElementById("mod_type").value = "del";
			document.getElementById("mod_field").value = field_id.match(/[a-z]+/);
			document.getElementById("mod_form").submit();
		}
		function checkMail(address) {
			if (address.match(/^[a-zA-Z0-9._%-]+@[a-zA-Z0-9._%-]+.[a-zA-Z]{2,4}$/)) {
				return true;
			} else {
				alert("Check the E-Mail address: \nthere's something wrong!");
				return false;
			}
		}
		function checkName(word) {
			word = trim(word);
			if (word!="") {
				return true;
			} else {
				alert("Check the what you typed: \nit can't be empty!");
				return false;
			}
		}
		function checkForm(){
			return (checkName() && checkMail(document.getElementById("email").value));
		}
		</script>
		  <td><form name="mod_form" id="mod_form" method="post" action=<?='"contact_tools.php?op=mod&id='.$_GET['id'].'" '?>>
			<table border="0" align="right" cellpadding="10" cellspacing="1" bgcolor="#0099CC">
			 <tr>
			  <td height="397" valign="top" bgcolor="#FFFFFF"> 
			   <h3>Contact details:</h3>
			   <input name="mod_type" type="hidden" id="mod_type" value="">
			   <input name="mod_field" type="hidden" id="mod_field" value="">
			   <input name="old_value" type="hidden" id="old_value" value="">
			   <input name="new_value" type="hidden" id="new_value" value="">
			   <strong>Name:</strong>&nbsp;&nbsp;
				<?=$contact['nome'];?>
				<font size="1">[ <a href="javascript:edit('name');">edit</a> ]</font> 
				<input name="name" type="hidden" id="name" value=<?='"'.$contact['nome'].'"';?>>
						<br>
				<strong>Surname:</strong>&nbsp;&nbsp;
				<?=$contact['cognome'];?>
				<font size="1">[ <a href="javascript:edit('surname');">edit</a> ]</font> 
				<input name="surname" type="hidden" id="surname" value=<?='"'.$contact['cognome'].'"';?>>
			   <br>
			   <br>
			   <table border="0" cellspacing="0" cellpadding="0">
				<tr align="left" valign="top"> 
				 <td><strong>Address:</strong></td>
				 <td>&nbsp;&nbsp;</td>
				 <td><?=str_replace("\n","<br>\n",htmlentities($contact['indirizzo']));?></td>
				 <td>&nbsp;<font size="1">[ <a href=<?="\"javascript:void(window.open('contact_tools.php?op=pop&id=".$_GET['id']
				 		."', 'insert_address', 'toolbar=no,menubar=no,status=no,location=no,resizable=yes,height=200,width=330'));\">edit</a>";
						
						?>
				  ]</font> 
				  <input name="address" type="hidden" id="address" value=<?='"'.$contact['indirizzo'].'"';?>></td>
				</tr>
			   </table>
			   <br />
			   <table border="0" cellspacing="0" cellpadding="0">
				<tr align="left" valign="top"> 
				 <td><strong>E-mail addresses:</strong><br>
				  <font size="1">[ <a href="javascript:add('email');">add</a> ]
				  <input name="email" type="hidden" id="email">
				  </font></td> 
				   <td>
		<?
		$emails = $my_db->get_contact_email($_GET['id']);
		if (is_array($emails)){
			foreach($emails as $key => $email){
				echo '&nbsp;&nbsp;<a href="mailto:'.$email.'">'.$email.'</a> <font size="1">[ ';
				echo "\n<a href=\"javascript:edit('email$key');\">edit</a>, ";
				echo "<a href=\"javascript:del('email$key');\">delete</a> ]</font> \n";
				echo "<input name=\"email$key\" type=\"hidden\" id=\"email$key\" value=\"$email\"> <br>";
			}
		} else echo "&nbsp;&nbsp<i>none</i>";
		?>
				 </td>
				</tr>
			   </table>
			   <br />
			   <table border="0" cellspacing="0" cellpadding="0">
				<tr align="left" valign="top"> 
				 <td><strong>Telephone numbers:</strong><br>
				  <font size="1">[ <a href="javascript:add('tel');">add</a> ] 
				  <input name="tel" type="hidden" id="tel">
				  </font></td>
				   <td>
		<?
		$tels = $my_db->get_contact_number($_GET['id'], 'tel');
		if (is_array($tels)){
			foreach($tels as $key => $tel){
				echo '&nbsp;&nbsp;'.$tel.' <font size="1">[ ';
				echo "\n<a href=\"javascript:edit('tel$key');\">edit</a>, ";
				echo "<a href=\"javascript:del('tel$key');\">delete</a> ]</font> \n";
				echo "<input name=\"tel$key\" type=\"hidden\" id=\"tel$key\" value=\"$tel\"> <br>";
			}
		} else echo "&nbsp;&nbsp<i>none</i>";
		?>
				 </td>
				</tr>
			   </table>
			   <br />
			   <table border="0" cellspacing="0" cellpadding="0">
				<tr align="left" valign="top"> 
				 <td><strong>Fax numbers:</strong><br>
				  <font size="1">[ <a href="javascript:add('fax');">add</a> ] 
				  <input name="fax" type="hidden" id="fax">
				  </font></td>
				 <td>
		<?
		$faxs = $my_db->get_contact_number($_GET['id'], 'fax');
		if (is_array($faxs)){
			foreach($faxs as $key => $fax){
				echo '&nbsp;&nbsp;'.$fax.' <font size="1">[ ';
				echo "\n<a href=\"javascript:edit('fax$key');\">edit</a>, ";
				echo "<a href=\"javascript:del('fax$key');\">delete</a> ]</font> \n";
				echo "<input name=\"fax$key\" type=\"hidden\" id=\"fax$key\" value=\"$fax\"> <br>";
			}
		} else echo "&nbsp;&nbsp<i>none</i>";
		?>
				 </td>
				</tr>
			   </table>
			  </td>
			</tr>
		   </table></form>
		   </td>
		   <?
	}
}
   ?>
 </tr>
</table>

<?
}//fine content
$printer->print_footer(); // e stampiamo la fine pagina
?>
