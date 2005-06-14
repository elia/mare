<?php

require_once('logger.class.php');

$logger = new Logger();

if ($_GET['status']) {
	switch ($_GET['status']) {
		case 'error':
		$logger->set_display_login();
		$logger->set_display_message();
		$logger->set_status('error');
		break;
		
		case 'logged':
		$logger->set_display_message();
		$logger->set_status('logged');
		break;
		
		case 'logout':
		session_start();
		session_unset();
		session_destroy();
		$logger->set_display_message();
		$logger->set_status('log_out');
		break;
		
		case 'not_logged':
		$logger->set_display_login();
		$logger->set_display_message();
		$logger->set_status('not_logged');
		
		break;
	}
} else {
	$logger->set_display_login();
	$logger->set_status('login');
}

$logger->print_page();


?>
