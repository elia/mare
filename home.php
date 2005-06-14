<?php
session_start(); 

// verificare validità login
require_once('logger.class.php');
require_once('gui_internal.class.php');



Logger::verify_log();

$printer = new GUI_internal();

$printer->print_header();

?>
<h2 align="center">What do you want to do? </h2>
<? $printer->print_row_delimiter();
?>
<p align="center"><a href="view_folder.php"><img src="icons/inbox.gif" width="16" height="16" border="0" align="absmiddle"> 
 Go to <strong>InBox</strong></a></p>
<? $printer->print_row_delimiter();
?>
<p align="center"><a href="folders.php"><img src="icons/folders.gif" width="16" height="16" border="0" align="absmiddle"> 
 Go to <strong>folders</strong></a></p>
<? $printer->print_row_delimiter();
?>
<p align="center"><a href="view_contacts.php"><img src="icons/users.gif" width="16" height="16" border="0" align="absmiddle"> 
 Address <strong>Book</strong></a></p>
<? $printer->print_row_delimiter();
?>
<p align="center"><a href="accounts_manage.php"><img src="icons/accounts.gif" width="16" height="16" border="0" align="absmiddle"> 
 Set visible <strong>accounts</strong></a></p>
<? $printer->print_row_delimiter();
?>
<p align="center"><a href="load_mbox.php"><img src="icons/mbox.gif" width="16" height="16" border="0" align="absmiddle"> 
 Load an <strong>MBOX</strong> file</a></p>
<?
?>
<p align="center"><a href="search.php"><img src="icons/search.gif" width="15" height="15" border="0" align="absmiddle"> 
 <strong>Search</strong> among your messages</a></p>
<?
?>
<p align="center"><a href="stats.php"><img src="icons/stats.gif" width="16" height="16" border="0" align="absmiddle"> 
 view <strong>STATS</strong> page</a></p>
<?

$printer->print_footer();

?>