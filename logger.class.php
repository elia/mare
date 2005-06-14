<?php
require_once('db_mare.class.php');
require_once('gui_external.class.php');

class Logger {
	// after this time (in secs) the session expires
	private static $session_expire_time = 1800;//30 minutes
	
	public static function verify_log() {
		if (!isset($_SESSION['user'])) {
			header('Location: login.php?status=not_logged');
		} else if ( (time()-$_SESSION['user']['time'])>=self::$session_expire_time ) {
			header('Location: login.php?status=logout');
		} else {
			$_SESSION['user']['time'] = time();
			return true;
		}
	}
	
	
	/*
	* 
	* 'login' bisogna loggarsi
	* 'error' tentativo di login fallito
	* 'logged' la macchina ha già una sessione
	*  autenticata attiva, si vuole uscire?
	* 'log_out' l'utente è appena uscito,
	*  si vuole rientrare?
	* 
	*/ 
	private $status = 'not_logged'; 
	private $display_login = false;
	private $display_message = false;
	
	public function set_display_message() {
		$this->display_message = true;
	}
	public function set_display_login() {
		$this->display_login = true;
	}
	public function set_status($status){
		$this->status = $status;
	}
	public function print_page() {
		$printer = new GUI_external();
		$printer->print_header("Login Page");
		
		{/*javascript*/?>
<script language="JavaScript" src="md5.js"> </script>
<script language="JavaScript" type="text/JavaScript">
function validate_login(frm) {
	var id_value = '';
	var pw_value = '';
	var errFlag = new Array();
	_qfMsg = '';
	id_value = frm.elements['login'].value;
	pw_value = frm.elements['pre_password'].value;
	if (id_value == '' && !errFlag['login']) {
		errFlag['login'] = true;
		_qfMsg = _qfMsg + '\n - Please enter your login name';
	}
	if (pw_value == '' && !errFlag['password']) {
		errFlag['password'] = true;
			_qfMsg = _qfMsg + '\n - Please enter your password';
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
<?}
		
		if ($this->display_message) {
			switch ($this->status) {
				case 'not_logged': {/////////////////////////////////////////////////////////////////////////
?>
   <h2>We're sorry, but you're not logged!</h2>
   If you want to enter, try login above. 
<?php
				} break;/////////////////////////////////////////////////////////////////////////////////////
				case 'error': {//////////////////////////////////////////////////////////////////////////////
?>
<!-- STARTS MESSAGE -->
   <table width="100%" border="0" cellpadding="0" cellspacing="1" bgcolor="#6699CC">
				<tr>
					<td bgcolor="#FFFFFF">
						<div align="center"><!-- STARTS MESSAGE TITLE -->
							<table width="100%" border="0" cellspacing="0" cellpadding="0">
								<tr>
									<td width="19%"><div align="center"><strong><font color="#FF9900" size="7"><blink>!</blink></font></strong></div></td>
									<td width="81%"><div align="center">
										<h3><font color="#FF9900">Alert: This login name does not 
												exist or password is incorrect!</font></h3>
										</div>
									</td>
								</tr>
							</table>
							<!-- ENDS MESSAGE TITLE -->
						</div>
					</td>
				</tr>
				<tr> 
					<td bgcolor="#FFFFFF"><!-- START MESSAGE BODY --><font size="2">Try the following: </font>
						<ul>
							<li><font size="2"><strong>Is the &quot;Caps Lock&quot; or &quot;A&quot; 
								light on your keyboard on?</strong><br>
								If so, hit &quot;Caps Lock&quot; key before trying again.</font></li>
							<li><font size="2"><strong>Did you forget or misspell your ID or 
								password?</strong><br>
								try retyping it or <a href="mailto:%20elia.schito@fastwebnet.it">contact 
								the MaRE administrator</a>.</font></li>
						</ul><!-- ENDS MESSAGE BODY --></td>
				</tr>
			</table>
<!-- ENDS MESSAGE -->

<?php
				} break;//////////////////////////////////////////////////////////////////////////////////////////////
				case 'logged': {//////////////////////////////////////////////////////////////////////////////////////
?>
		<h2>You're already logged</h2>
		In a few seconds you'll be sent to the MaRE home page...
		<script language="JavaScript" type="text/JavaScript">
		setTimeout("location.href = 'home.php'",3000);
		</script>

<?php
				} break;/////////////////////////////////////////////////////////////////////////////////////////////////
				case 'log_out': {/////////////////////////////////////////////////////////////////////////////////////
?>
   <h2>You've logged out, see you soon!</h2>
   If you want to get back, <a href="login.php">try login again</a>. 
<?php		
		} break;//////////////////////////////////////////////////////////////////////////////////////////////
	}
}
		if ($this->display_login) { ////////////////////////////////////////////////////////////////////////
?>
   <!-- STARTS LOGIN -->
   <h1>Log In:</h1>
			<form name="login_form" id="login_form" method="post" action="authorizer.php" onSubmit="return validate_login(this);">
				<table width="100%" border="0" cellspacing="0" cellpadding="5">
					<tr> 
						<td>Login name:</td>
						<td><input name="login" type="text" id="login" size="32"></td>
					</tr>
					<tr> 
						<td>Password:</td>
						<td><input name="password" type="hidden">
						<input name="pre_password" type="password" size="32"></td>
					</tr>
					<tr> 
						<td>&nbsp;</td>
						<td><input type="submit" name="Submit" value="--Enter--"></td>
					</tr>
				</table>
			</form>
   <!-- ENDS LOGIN -->
<?php
		} ///////////////////////////////////////////////////////////////////////////////////////////
		
		$printer->print_footer();
	}
	
}



?>