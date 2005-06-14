<?php
require_once('logger.class.php');
require_once('gui_internal.class.php');
require_once('db_mare.class.php');
require_once('message.class.php');

session_start(); 

// verificare validità login
Logger::verify_log();

$my_db = new DB_mare();

// GESTIONE DELLE OPERAZIONI DI MODIFICA
if (isset($_POST['account'])) {
	if ($_POST['account']=='new_account') {
		$account = $_POST['email_account'];
		$my_db->add_account($account);
	} else	$account = $_POST['account'];
	// non si accettano file vuoti
	if ($_FILES['mbox']['size']>0) {
		//error_reporting(0);
		//load and split and parse the MBOX file
		require_once ('Mail/mbox.php');
		$file_path = $_FILES['mbox']['tmp_name'];
		
		// carichiamo il file nell'oggetto
		$mbox = new Mail_Mbox();
		$mbox_id = $mbox->open($file_path);
		
		//scorriamo i file e per ora stampiamo qlcsa
		for ($i=1; $i<=$mbox->size($mbox_id); $i++) {
			//echo "Message: $i<pre>";
			$current_msg = $mbox->get($mbox_id,$i);
			// se è un oggetto allora è un errore di PEAR
			if (!is_object($current_msg))
				$my_db->add_message($account, $current_msg);
		}
		
		//echo "OK!";
		//var_dump($_FILES);
		//var_dump($_POST);
		//exit;
		header('Location: view_folder.php');
		exit;
	} else {
		/*var_dump($_FILES);
		var_dump($_POST);
		echo('Location: load_mbox.php?error=empty_file');*/
		
		header('Location: load_mbox.php?error=empty_file');
		exit;
	}
}

// prepariamo GUI e DB
$printer = new GUI_internal();

$printer->print_header("Load an MBOX file"); // e stampiamo l'inizio pagina

{ // JAvascript e inizio tabella e form1
	?>
<style type="text/css">
/* specs for layers that slide. */
div.loadingLayer { 
	position:absolute;
	visibility:hidden; 
	left:10;
	vertical-align: middle;
	/*width:280px; z-index:200;*/
	background-color: #6699CC;
	color: #FFFFFF;
}
</style>	
<!-- layers that slide  -->
<div id="loading_file" class="loadingLayer">
<h5><p><blink>&nbsp;&nbsp;Please be patient&nbsp;&nbsp;
<br>&nbsp;&nbsp;while loading your file...&nbsp;&nbsp;</blink></p>
<p><blink>&nbsp;&nbsp;It could be take some minutes.&nbsp;&nbsp;</blink></p>
<center><br>&gt;&gt;&gt;<img src="icons/loading.gif">&lt;&lt;&lt;</center>
</div>
<form action="load_mbox.php" method="POST" enctype="multipart/form-data" name="mbox_form" id="mbox_form">
<script language="JavaScript" type="text/JavaScript">//{
	<?
	if (isset($_GET['error']))
		echo "\n".'alert(\'Error:\n'.$_GET['error']."');\n";
	?>
	function checkAndEmpty(field) {
		if (field.value=='   --insert here the account e-mail--')
			field.value='';
		document.getElementById("new_account").checked = true;
	}
	
	//SUBMIT FUNCTIONS
	function checkFile(field) {
		if (field.value=='') {
			alert("Select first an MBOX file to load.");
			return false;
		} else
			return true;
	}
	function checkMail() {
		var address = document.getElementById("email_account").value;
		if (address.match(/^[a-zA-Z0-9._%-]+@[a-zA-Z0-9._%-]+.[a-zA-Z]{2,4}$/)) {
			return true;
		} else {
			alert("Check the E-Mail address: \nthere's something wrong!");
			return false;
		}
	}
	function submitAll() {
	 	if (checkFile(document.getElementById("file_path"))) {
			if ( ((document.getElementById("new_account").checked) && checkMail())
					|| !document.getElementById("new_account").checked ) {
				
				//document.getElementById("loading_file").style.visibility="hidden";
				document.getElementById("loading_file").style.visibility="visible";
				document.getElementById("mbox_form").submit();
			}
		}
	}
	
//}
</script>
 <table width="100%" border="0" cellpadding="0" cellspacing="1" bgcolor="#0099CC">
 <tr align="center" valign="top" bgcolor="#FFFFFF"> 
  <td> <h2 align=center id="prova">Select <strong>an MBOX file to load in <font face="Georgia, Times New Roman, Times, serif">M<span class="alert">a</span>RE</font></strong> 
	 <!-- The data encoding type, enctype, MUST be specified as below -->
	</h2>
   
	<ol>
	 <li> <!-- MAX_FILE_SIZE must precede the file input field -->
	  <input type="hidden" name="MAX_FILE_SIZE" value="12000000">
	  <!-- Name of input element determines name in $_FILES array -->
	  Select your MBOX file here:<br>
	  <br>
	  <input name="mbox" id="file_path" type="file">
	  <br><font size=1>Maximum file size: 10MB</font>
	 </li>
	 <li>Select on wich account you prefer to load your messages:<br>
	  <br>
	  <? }
	  
$accounts = $my_db->get_accounts();
if (is_array($accounts)){
	ksort($accounts);
	$counter = 0;
	foreach ($accounts as $account=>$visible) { // stampa gli account come checkbox
		$check = !$counter?' checked':'';
		$to_print = "\n".'<a href="#" onClick="document.getElementById(\'acc'.$counter.'\').checked=true">'.$account.'</a>';
		echo '<input name="account" type="radio" id="acc'.$counter.'" value="'.$account.'"'.$check.'>'.$to_print.'<br><br>'."\n";
		$counter++;
		}
	$check = "";
	echo 'or<strong> type</strong> an e-mail address as a <strong>new account</strong>:<br>';
} else {
	echo '<font size="1">No accounts found, please insert a new account below.</font><br>';
	$check = " checked";
}
{ // il resto della pagina
	?>
	  <br>
	  <input name="account" type="radio" id="new_account" value="new_account" <?=$check; ?>>
	  <input name="email_account" type="text" id="email_account" value="   --insert here the account e-mail--" size="30" onFocus="checkAndEmpty(this)">
	  <br>
	  <font size="1">NOTE: it must be a valid address</font> </li>
	</ol>
  </td>
 </tr>
 <tr align="center" valign="middle" bgcolor="#FFFFFF"> 
  <td> <table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
	 <tr align="center"> 
	  <td height="24"><a href="javascript:submitAll()"> <img src="icons/freccia_se.gif" width="16" height="16" align="absmiddle"> 
	   load this MBOX</a> </td>
	  <td><a href="home.php"><img src="icons/freccia_o.gif" width="16" height="16" align="absmiddle"> 
	   go BACK</a></td>
	 </tr>
	</table></td>
 </tr>
</table>
</form>
<?}
// $printer->print_row_delimiter();

$printer->print_footer();


?>
