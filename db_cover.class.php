<?php

class DB_cover {
	private $connection;
	
	function __construct() {
		//turn off all errors
		//error_reporting(0);
		// open db connection
		
		include('db_parameters.conf');
		//$host = file_exists('local.nfo')?"127.0.0.1":"81.174.20.156";
		$password=empty($password)?"":" password:$password ";
		$this->connection = pg_pconnect("host=$host port=$port dbname=$database user=$user")
		or die("<hr><hr><center><h3>A database error occurred.</h1></center><hr>".pg_last_error()."<hr>");
		/*
		$this->connection = pg_pconnect("host=$host port=5432 dbname=progetto_definitivo user=postgres")
		or die("<hr><hr><center><h3>A database error occurred.</h1></center><hr>".pg_last_error()."<hr>");
		*/
	}
	function __destruct() {
		// controlla se la conessione è ancora valida
		pg_close($this->connection);
		
	}
	public function get_array($query){
		$result = pg_query($this->connection, $query);
		return is_bool($result)?$result:pg_fetch_all($result);
	}
}

?>