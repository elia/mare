<?php
require_once('logger.class.php');
require_once('db_mare.class.php');
require_once('folder.class.php');
session_start(); 
// verificare validità login
Logger::verify_log();
$my_db = new DB_mare();
if (isset($_POST['fld_id']) && $_POST['fld_id']!=""){
	$id = $_POST['fld_id'];
} else if (isset($_GET['id'])){
	$id = $_GET['id'];
}
switch ($_POST["operation"]) {
	case "new":
	$id=isset($_GET['id'])?$_GET['id']:null;
	$my_db->add_folder($_POST['folder_name'], $id);
	break;
	
	case "del":
	$my_db->del_folder($id);
	break;
	
	case "ren":
	$my_db->ren_folder($id, $_POST['folder_name']);
	break;
	
}

$back_address = "Location: ".$_POST['back_address'];
header($back_address);

/*
echo "\n GET\n";
var_dump($_GET);
echo "\n\nPOST\n";
var_dump($_POST);
*/

?>