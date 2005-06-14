<?php
require_once('logger.class.php');
require_once('db_mare.class.php');

session_start();


if (isset($_POST['login'], $_POST['password'])){ // se sono stati inviati login e pw alla pagina allora li procassiamo
	
	$my_db = new DB_mare();
	$user = $my_db->get_user($_POST['login']);
	
	if ($_POST['password'] == $user['password']) { // se la password coincide si puo registrare nella sessione i dati
		// registrazione dell'utente tramite le variabili di sessione
		$_SESSION['user']['login'] = $user['login'];
		$_SESSION['user']['name'] = $user['nome'];
		$_SESSION['user']['surname'] = $user['cognome'];
		$_SESSION['user']['id'] = $user['id_utente'];
		$_SESSION['user']['time'] = time();
		//controlliamo ad ogni login se ci sono allegati da cancellare dal disco
		$my_db->del_attachments();
		
		// poi si può anccedere alla home
		header("Location: home.php");
		
	} else if (is_bool($user) || $_POST['password'] != $user['password']){ // altrimenti bisogna bruciare tutte le carte e comunicare l'errore
		session_unset();
		session_destroy();
		header("Location: login.php?status=error");
	}
} else if (isset($_SESSION['user'])) { // se invece la sessione contiene già un utente loggato... 
	// se è già loggato vai alla pagina "logged.php"
	header ("Location: login.php?status=logged");
	
} else { // negli altri casi ti devi loggare
	header ("Location: login.php");
}


?>