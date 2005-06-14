<?php
require_once('logger.class.php');
require_once('gui_internal.class.php');
require_once('db_mare.class.php');

session_start(); 

// verificare validità login
Logger::verify_log();

$my_db = new DB_mare();

// GESTIONE DELLE OPERAZIONI DI MODIFICA
if (isset($_POST['select_all'])) {
	if ($_POST['select_all']=='all') {
		$my_db->set_accounts('all');
	} else {
		//trasforma i BOOL di pgSQL in BOOL di PHP
		foreach ($_POST['accounts'] as $account=>$visible) {
			$accounts[$account] = ($visible=='on')?true:false;
		}
		$my_db->set_accounts($accounts);
	}
	header('Location: accounts_manage.php');
} else if (isset($_POST['email_account'])) {
	$my_db->add_account($_POST['email_account']);
	header('Location: accounts_manage.php');
}

// prepariamo la GUI
$printer = new GUI_internal();

$printer->print_header("Accounts setup"); // e stampiamo l'inizio pagina
$accounts = $my_db->get_accounts();

{ // JAvascript e inizio tabella e form1
	?>
<script language="JavaScript" type="text/JavaScript">
	function emptyMail(field) {
		if (field.value=='   --insert here the account e-mail--')
			field.value='';
	}
	function selectAll() {
		document.getElementById("select_all").value = "all";
		acc_form.submit();
	}
	
	
	//SUBMIT FUNCTIONS
	function checkMail() {
		var isCorrectAddress = document.getElementById("email_account").value.match("^[a-zA-Z0-9._%-]+@[a-zA-Z0-9._%-]+.[a-zA-Z]{2,4}$");
		if (isCorrectAddress) {
			return true;
		} else {
			alert("Check the E-Mail address: \nthere's something wrong!");
			return false;
		}
	}
	function submitNewAccount() {
	 	if (checkMail()) {
			new_acc_form.submit();
		}
	}
	
	function oneAtLeast() {
		var coll = acc_form.elements;//document.getElementsByName('account');
		var counter = 0;
		for (i = 0; i < (coll.length-1); i++) {
			if (coll[i].checked==true){
				counter++;}
		}
		if (counter>0) {
			return true;
		} else {
			alert("You must select at least one account");
			return false;
		}
	}
	function submitAccounts() {
	 	if (oneAtLeast()) {
			acc_form.submit();
		}
	}

</script>
</a> 
<table width="100%" border="0" cellpadding="0" cellspacing="1" bgcolor="#0099CC">
 <tr align="center" valign="top" bgcolor="#FFFFFF"> 
  <td> 
   <h2 align=center id="prova">Select <strong>accounts</strong> to be displayed</h2>
   <form action="accounts_manage.php" method="post" name="acc_form" id="acc_form" onSubmit="oneAtLeast()">
<? }

if (is_array($accounts)) {//se non è un array non ci sono accounts
	ksort($accounts);//li visaulizziamo sempre nello stesso ordine (alfabetico)
	foreach ($accounts as $account=>$visible) { // stampa gli account come checkbox
		$check = $visible?' checked':'';
		echo '<p><input name="accounts['.$account.']" type="checkbox" id="account" '.$check.' style="border: 0px;">'.$account;
		echo " <font size=1>[ <a href=\"account_tools.php?op=del&id=".urlencode($account)."\">Delete</a> ]</font></p>";
	}
} else {
	echo "<p>You have no registered accounts!</p><p>Insert an account and click <strong>\"new account\"</strong> on the form on the right of this page.</p>"; 
}

{ // il resto della pagina
	?>
	<input name="select_all" type="hidden" id="select_all" value="">
	<p><font size=1>NOTE that DELETING an account you will REMOVE ALL MESSAGES ASSOCIATED with it.</p>
   </form>
   <p align=center> </p>
   
  </td>
  <td width="20">&nbsp;</td>
  <td> 
   <h3>Create new account</h3>
   <p>&nbsp;</p>
   <form action="accounts_manage.php" method="post" name="new_acc_form" id="new_acc_form"  onSubmit="return checkMail()">
	<p><font size="3">type here the e-mail address you want to add</font></p>
	<p> 
     <input name="email_account" type="text" id="email_account" onClick="emptyMail(this)" onFocus="emptyMail(this)" value="   --insert here the account e-mail--" size="30">
	 <br>
	 <font size="1">NOTE: it must be a valid address</font></p>
   </form>
   <p>&nbsp;</p>
   </td>
 </tr>
 <tr align="center" valign="middle" bgcolor="#FFFFFF"> 
  <td> 
   <table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
	<tr align="center"> 
	 <td height="24"><a href="javascript:submitAccounts()"> <img src="icons/freccia_se.gif" width="16" height="16"> 
	  apply changes</a> </td>
	 <td><a href="javascript:selectAll()"><img src="icons/freccia_ne.gif" width="16" height="16"> 
	  display all </a></td>
	 <td><a href="home.php"><img src="icons/freccia_o.gif" width="16" height="16"> go back</a></td>
	</tr>
   </table></td>
  <td>&nbsp;</td>
  <td> 
   <div align="center"><a href="javascript:submitNewAccount()"><img src="icons/freccia_e.gif" width="16" height="16"> 
	new account</a> </div></td>
 </tr>
</table>

<?}
// $printer->print_row_delimiter();

$printer->print_footer();


?>