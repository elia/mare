<?
session_start();
session_unset();
session_destroy();

if (!$_POST) { 
	
	require_once('gui_external.class.php');
	$printer = new GUI_external();
	$printer->print_header("Create New User");
	
	if ($_GET['status']) {
		switch ($_GET['status']) {
			case 'login_present'://///////////////////////////////////////////////////////////////////////
			?>
   <h3 class="alert">Error: login-name already present</h3>
   <p>We're sorry, but the login-name you've typed is already owned by another 
	user.</p>
   <p> <strong>Solution:</strong></p>
   <ul>
	<li>Try to insert a different login-name. 
	</li>
   </ul>
   			<?
			break;////////////////////////////////////////////////////////////////////////////////////
		}
	}
	{//page body
	?>
<script language="JavaScript" src="md5.js"> </script>
<script language="JavaScript" type="text/JavaScript">
function validate_form(frm) {
	var id_value = '';
	var pw_value = '';
	var pw_retype_value = '';
	var _qfMsg = '';
	id_value = frm.elements['login'].value;
	pw_value = frm.elements['pre_password'].value;
	pw_retype_value = frm.elements['pre_password_retype'].value;
	
	if (id_value == '') {
		_qfMsg = _qfMsg + '\n - Please enter your login name';
	}
	if (pw_value == '') {
		_qfMsg = _qfMsg + '\n - Please enter your password';
	} else if (pw_value != pw_retype_value) {
		_qfMsg = _qfMsg + '\n - Please enter two times the same password';
	}
	
	if (_qfMsg != '') {
		_qfMsg = 'Invalid information entered.' + _qfMsg;
		_qfMsg = _qfMsg + '\nPlease correct these fields.';
		alert(_qfMsg);
		return false;
	}
	frm.elements['password'].value = (hex_md5(frm.elements['pre_password'].value));
	return true;
}
</script>
   <h1>Insert below your data:</h1>
   <form name="accounter_form" id="accounter_form" method="post" action="new_user.php" onSubmit="return validate_form(this);">
	<table width="100%" border="0" cellspacing="0" cellpadding="5">
	 <tr> 
	  <td><strong><span class="alert">*</span></strong>Login name:</td>
	  <td><input name="login" type="text" id="login" size="32"></td>
	 </tr>
	 <tr> 
	  <td><strong><span class="alert">*</span></strong>Password:</td>
	  <td><input name="pre_password" type="password" size="32">
	  <input name="password" type="hidden"></td>
	 </tr>
	 <tr> 
	  <td><strong><span class="alert">*</span></strong>Password:<span class="alert"><font size="2"><em>(retype)</em></font></span> 
	   <span class="alert"><font size="2"></font></span></td>
	  <td><input name="pre_password_retype" type="password" size="32"></td>
	 </tr>
	 <tr> 
	  <td>Name:</td>
	  <td><input name="name" type="text" size="32"></td>
	 </tr>
	 <tr> 
	  <td>Surname:</td>
	  <td><input name="surname" type="text" size="32"></td>
	 </tr>
	 <tr>
	  <td><strong><span class="alert">*</span></strong> <font size="2">required 
	   fields</font></td>
	  <td><input type="submit" name="Submit" value="--Submit--"></td>
	 </tr>
	</table>
   </form>
  </td>
	</tr>
	<?
	}
	$printer->print_footer('login');
	exit;
	
} //fine pagina web

//inizio elaborazione dati

require_once('db_mare.class.php');
$my_db = new DB_mare();

require_once('gui_external.class.php');
$printer = new GUI_external();
// se il login è gia presente
if (!is_null($my_db->get_user($_POST['login']))) {
	header('Location: new_user.php?status=login_present');
} else {
	$my_db->set_user($_POST['login'], $_POST['password'], $_POST['name'], $_POST['surname']);
}

// Benvenuto al nuovo utente
$printer->print_header("Hi ".$_POST['name']."!");
?>
<h1>Wellcome <? echo $_POST['name']." ".$_POST['surname'];?>!</h1>
   <p><font size="2">your login-name is: <span class="alert"><? echo $_POST['login']; ?></span></font></p>
   <p><font size="2">and your password is: <span class="alert"><? echo $_POST['pre_password']; ?></span></font></p></td>
<?
$printer->print_footer('login');

?>