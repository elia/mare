<?php
require_once('logger.class.php');
require_once('db_mare.class.php');
require_once('message.class.php');
session_start(); 
// verificare validità login
Logger::verify_log();
$my_db = new DB_mare();

switch ($_GET['op']) {
	
	case 'del':
	if (isset($_GET['id']))
		$my_db->del_account(urldecode($_GET['id']));
}
header('Location: accounts_manage.php');