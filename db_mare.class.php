<?php
require_once('db_cover.class.php');
require_once ('Mail/mimeDecode.php');
require_once ('Mail/RFC822.php');


class DB_mare extends DB_cover {
	
	function __construct() {
		parent::__construct();
		$this->add_message_runned = false;
	}
	function __destruct() {
		parent::__destruct();
	}
	
	
	/////////////////////////USER{
	////////////////////////////////
	public function get_user($login){//ok
		$ass_arr = parent::get_array("SELECT * FROM utente WHERE login='".$login."';");
		return $ass_arr[0];
	}
	public function set_user($login, $password, $name = null, $surname = null) {//ok
		trim(&$name);
		trim(&$surname);
		// se vuoti li imposta a null
		$name = empty($name)?'NULL':"'$name'";
		$surname = empty($surname)?'NULL':"'$surname'";
		$result = parent::get_array("INSERT INTO utente(login, password, id_utente, nome, cognome) 
			VALUES ('$login', '$password', DEFAULT, $name, $surname);");
		return $result;
	}
	////////////////////////////////}
	/////////////////////////ACCOUNT{
	////////////////////////////////
	public function get_accounts(){//ok
		$result = parent::get_array("SELECT email_account, visibile FROM account 
			WHERE id_utente=".$_SESSION['user']['id'].";");
		// questo ciclo butta fuori un'array nella forma 'indirizzo_mail'=>'{true|false}'
		if (is_array($result)) {
			foreach ($result as $item) {
				$arr[$item['email_account']] = $item['visibile']=='t'?true:false;
			}
		} else {
			return $result;
		}
		return $arr;
	}
	public function set_accounts($accounts) {//ok
		/* il parametro accounts richiede un array come quello fornito da get accounts 
		* con almeno gli account da attivare (comunque settati a true)
		* in forma array ( 'account_uno@asg'=>true|false, ...)
		* OPPURE la stringa 'all' che attivarà automaticamente tutti gli account
		*/
		// tutti ON se c'è 'all'
		if (is_string($accounts) && $accounts == 'all') {
			$result = parent::get_array("UPDATE account SET visibile = TRUE 
				WHERE id_utente=".$_SESSION['user']['id'].";");
			return $result;
		}
		
		////////////////////////////////////////////////////
		
		// poi mettiamo a ON tutti quelli con $visible = true
		// prepara la query solo per gli account con $visible = true
		$addr_counter=0;
		$list = "";
		foreach ($accounts as $address=>$visible) {// crea la lista di condizioni per la query
			if ($visible) {
				// mette OR davanti a tutti tranne che al primo della lista
				$list .= $addr_counter!=0 ? " OR " : "" ;
				// aggiunge gli indirizzi per la condizione WHERE
				$list .= " email_account = '".$address."' ";
				//impediamo di mettere OR davanti al primo
				$addr_counter++;
			}
		}
		// prima mettiamo tutti OFF
		$clear_query = "UPDATE account SET visibile = FALSE 
			WHERE id_utente=".$_SESSION['user']['id'].";";
		// inserisce la lista e manda la query
		$query = "UPDATE account SET visibile = TRUE 
			WHERE (id_utente = ".$_SESSION['user']['id']." AND (".$list."));";
		$result = parent::get_array($clear_query);
		$result = parent::get_array($query);
		
		return $result;
	}
	public function add_account($new_account) {//ok
		/* per caso esiste già una copia di quella cartella? */
		$copy_parent = is_null($parent_fld)?" IS NULL ":" =$parent_fld ";
		$copy = $this->get_array("SELECT * FROM account
			WHERE email_account='$new_account' AND id_utente=".$_SESSION['user']['id']." ;");
		if (is_array($copy)) return false;
		/* fine controllo per copie... */
		parent::get_array("INSERT INTO account(email_account, visibile, id_utente) 
			VALUES ('$new_account', DEFAULT, ".$_SESSION['user']['id'].");");
	}
	public function del_account($del_account) {//ok
		parent::get_array("DELETE FROM account 
			WHERE email_account='$del_account' AND id_utente=".$_SESSION['user']['id'].";");
	}
	////////////////////////////////}
	/////////////////////////MESSAGE{
	////////////////////////////////
	private function get_messaggio_new_id(){
			$result = parent::get_array("select nextval('public.messaggio_id_messaggio_seq'::text)");
			return $result[0]['nextval'];
		}
	public function add_message($account,$message){
		require_once('message_parser.class.php');
		// $messagge è il testo RFC2822 del messaggio
		// l'estrazione dei messagi dall'MBOX viene fatta in 'load_mbox.php'
		$params['include_bodies'] = true;
		$params['decode_bodies']  = true;//impostato a true da false
		$params['decode_headers'] = true;
		$params['input']          = $message;
		$params['crlf']           = "\r\n";
		
		// $st come $structure
		$st = Mail_mimeDecode::decode($params);
		
		$headers=$st->headers;
		
		//prepariamo i dati per la tabella msg
		
		// User-agent
		if (isset($headers['user-agent'])) {
			$user_agent = $headers['user-agent'];
			$user_agent = "'$user_agent'";
			trim(&$user_agent);
		} elseif(isset($headers['x-mailer'])) {
			$user_agent=$headers['x-mailer'];
			$user_agent = "'$user_agent'";
			trim(&$user_agent);
		} else {
			$user_agent = 'NULL';
		}
		
		// Message-ID
		$msg_id_arr = Mail_RFC822::parseAddressList($headers['message-id'], '', false);
		if (!is_object($msg_id_arr)){
			$msg_id = addslashes($msg_id_arr[0]->mailbox.'@'.$msg_id_arr[0]->host);
		}
		
		// Subject
		$subject = addslashes($headers['subject']);
		
		// Body
		$body =	Message_parser::prepare_bodies($st);
		//controllo il tipo testo e decido quale text inserire nel db
		$testo_normale = is_null($body['text'])?'NULL':"'".addslashes($body['text'])."'";
		$testo_html = is_null($body['html'])?'NULL':"'".addslashes($body['html'])."'";
		
		$date = Message_parser::date_prepare($headers['date']);
		$forwarded = Message_parser::is_forwarded($subject)?'TRUE':'FALSE';
		
		if(isset($headers['list-id']) and trim($headers['list-id'])!="") {
			$newsgroup = Message_parser::get_newsgroup_name($headers['list-id']);
			$newsgroup = is_null($newsgroup)?'NULL':"'".addslashes($newsgroup)."'";
		} else {
			$newsgroup = 'NULL';
		}
		
		//ci riserviamo un id della seq_messaggio del db
		$id_current_msg = $this->get_messaggio_new_id();
		
		//inserisci messaggio nel db
		parent::get_array("
			INSERT INTO messaggio (
			id_utente, email_account, id_messaggio, 
			date, subject, testo_normale, testo_html, message_id, 
			forwarded, newsgroup, user_agent)
			VALUES ( ".$_SESSION['user']['id'].", '$account', $id_current_msg, 
			'$date', '$subject', $testo_normale, $testo_html, '$msg_id', 
			$forwarded, $newsgroup, $user_agent );");
		
		
		//lavoriamo su from ,to ,cc 
		/*ESEMPIO di array fatto da Mail_RFC822::parseAddressList():
				array(1) {
				  [0]=>
				  object(stdClass)#7 (4) {
					["personal"]=>
					string(0) "NOME_VISUALIZZATO"
					["comment"]=>
					array(0) {
					}
					["mailbox"]=>
					string(13) "progetto_mare"
					["host"]=>
					string(8) "yahoo.it"
				  }
				}*/
		$types = array("from", "sender", "reply-to", "to", "cc", "bcc");
		
		foreach ($types as $type) {
			if (isset($headers[$type])) {
				$addr_arr = Mail_RFC822::parseAddressList($headers[$type], '', false);
				if (is_object($addr_arr)) continue;
				for ($i=0;$i<count($addr_arr);$i++) {
					$email = $addr_arr[$i]->mailbox.'@'.$addr_arr[$i]->host;
					$name = addslashes($addr_arr[$i]->personal);
					$this->add_message_address($id_current_msg, $type, $i, $email, $name);
				}
			}
		}
		
		// Received
		//tabella host_riceventi e host mittenti vedi dove inserire by e from
		if (is_array($headers['received']) && count($headers['received'])) {
			foreach($headers['received'] as $key_receive => $value_receive) {
				$host = Message_parser::parse_received($value_receive);
				if ($host['host'] != ""){
					parent::get_array("INSERT INTO received (id_messaggio, posizione, date, id_host)
									VALUES ($id_current_msg, $key_receive, '".$host['date']."', '".$host['host']."');");
				}
			}
		}
		
		//Attachment
		if(trim($st->ctype_primary)=="multipart" and trim($st->ctype_secondary)=="mixed"){
			foreach($st->parts as $part_num => $part){
				if( (isset($part->disposition)) && $part->body!=""){//and (trim($parts_value->disposition)=="attachment"))
					$file_name = $part->ctype_parameters['name'];
					trim(&$file_name);
					if ($file_name == ""){
						$file_name = "unnamed_attachment_$part_num";
					}
					$this->add_attachment($id_current_msg, $file_name, $part->body);
				}
			}
		}
		
		// In-Reply_to
		if( isset($headers['in-reply-to']) and  $headers['in-reply-to'] != "") {
			$msg_id_arr = Mail_RFC822::parseAddressList($headers['in-reply-to'], '', false);
			// se è un oggetto allora è sicuramente PEAR_Error 
			// quindi si fa il ciclo solo se non lo è.
			if (!is_object($msg_id_arr)){
				for ($i=0;$i<count($msg_id_arr);$i++) {
					$msg_id = $msg_id_arr[$i]->mailbox.'@'.$msg_id_arr[$i]->host;
					$this->add_message_in_reply_to($id_current_msg, $msg_id);
				}
			}
		}
	}
	private function add_message_address($id, $type, $pos, $email, $name=''){
		parent::get_array("INSERT INTO indirizzo_in_messaggio 
			(id_messaggio, tipo_header, posizione_in_elenco, indirizzo_email, nome_visualizzato) 
			VALUES ($id, '$type', $pos, '$email', '$name' );");
	}
	public function add_message_to_folder($id_msg, $id_fld) {
		/* per caso esiste già una copia in quella cartella? */
		$copy = $this->get_array("SELECT * FROM inclusione_cartella_msg
			WHERE id_cartella=$id_fld AND id_messaggio=$id_msg;");
		if (is_array($copy)) return false;
		/* fine controllo per copie... */
		$this->get_array("
			INSERT INTO inclusione_cartella_msg (id_cartella, id_messaggio)
			VALUES ($id_fld, $id_msg);
		");
		return true;
	}
	public function add_message_in_reply_to($id_msg, $msg_id){
		$this->get_array("
				INSERT INTO risposta_messaggio (id_msg_risposta, msg_id_originale)
				VALUES ($id_msg, '$msg_id');");
	}
	public function get_message_in_reply_to($id){
		$result = $this->get_array("SELECT msg_id_originale FROM risposta_messaggio WHERE id_msg_risposta=$id;");
		return $result;
	}
	public function get_message($id){
		$result = parent::get_array("SELECT * 
			FROM messaggio
			WHERE 
				id_utente = ".$_SESSION['user']['id']." 
			AND id_messaggio = ".$id."
			;");
		return $result[0];
	}
	public function get_message_address($id_msg, $type, $pos){
		$result = parent::get_array("SELECT indirizzo_email, nome_visualizzato 
			FROM indirizzo_in_messaggio
			WHERE id_messaggio = $id_msg AND tipo_header='$type'
			AND posizione_in_elenco=$pos;");
		return $result[0];
	}
	public function get_message_addresses($id, $type=null){
		// per aumentare la sicurezza, anche qui controlliamo l'utente
		if ($type == null) {
			$result = parent::get_array("SELECT ind.tipo_header, ind.posizione_in_elenco, ind.indirizzo_email, 
					ind.nome_visualizzato, c_email.id_contatto 
				FROM ((messaggio AS msg NATURAL JOIN account AS acc) 
					NATURAL JOIN indirizzo_in_messaggio AS ind) 
					LEFT JOIN (contatto NATURAL JOIN contatto_email AS c_email) 
						ON (ind.indirizzo_email = c_email.indirizzo_email)
				WHERE 
						acc.id_utente = ".$_SESSION['user']['id']." 
					AND acc.visibile = TRUE 
					AND ind.id_messaggio = ".$id."
				ORDER BY ind.tipo_header, posizione_in_elenco ASC
				;");
			return $result;
		} else {
			$result = parent::get_array("SELECT ind.posizione_in_elenco, ind.indirizzo_email, 
					nome_visualizzato, c_email.id_contatto 
				FROM ((messaggio AS msg NATURAL JOIN account AS acc) 
					NATURAL JOIN indirizzo_in_messaggio AS ind) 
					LEFT JOIN (contatto NATURAL JOIN contatto_email AS c_email) 
						ON (ind.indirizzo_email = c_email.indirizzo_email)
				WHERE 
					acc.id_utente = ".$_SESSION['user']['id']." 
				AND acc.visibile = TRUE 
				AND ind.id_messaggio = ".$id."
				AND ind.tipo_header = '$type' 
				ORDER BY posizione_in_elenco ASC
				;");
			return $result;
			
		}
	}
	public function get_messages($id_fld){
		$result = parent::get_array("SELECT msg.id_messaggio, msg.subject, msg.date, msg.email_account 
			FROM (messaggio AS msg NATURAL JOIN account AS acc) NATURAL JOIN inclusione_cartella_msg AS inc 
			WHERE 
				acc.id_utente = ".$_SESSION['user']['id']." 
			AND acc.visibile = TRUE 
			AND inc.id_cartella = ".$id_fld."
			;");
		return $result;
		
	}
	public function get_message_attachments($id) {
		$result = parent::get_array("SELECT id_file, percorso, nome_allegato 
			FROM messaggio AS m  NATURAL JOIN file_allegato AS f
			WHERE 
				m.id_messaggio = ".$id."
			;");
		return $result;
	
	}
	public function del_message_from_folder($id_msg, $id_fld) {
		if (!$this->get_message($id_msg)) return false;
		$result = parent::get_array("DELETE FROM inclusione_cartella_msg
		WHERE id_cartella=$id_fld AND id_messaggio=$id_msg ;");
		return $result;
	}
	public function del_message($id_msg) {
		if (!$this->get_message($id_msg)) return false;
		$result = parent::get_array("DELETE FROM messaggio
		WHERE id_messaggio=$id_msg;");
		return $result;
	}
	public function get_message_thread($id){
		/*{
		* il formato finale sarà un array del tipo:
		* $msg_thread[0]=id_messaggio più vecchio
		* $msg_thread[1]=id_messaggio
		* $msg_thread[2]=id_messaggio
		* $msg_thread[3]=id_messaggio corrente
		}*/
		// RICORSIVO
		$parent_id = $this->get_array("SELECT id_messaggio FROM messaggio
					WHERE message_id = (SELECT msg_id_originale FROM risposta_messaggio
										WHERE id_msg_risposta=$id)
					AND id_utente = ".$_SESSION['user']['id']." ;");
		if (is_array($parent_id)) {
			$recursive_arr = $this->get_message_thread($parent_id[0]['id_messaggio']);
			return array_merge($recursive_arr, array($id));
		} else {
			return array($id);
		}
	}
	
	////////////////////////////////}
	/////////////////////////FOLDER{
	////////////////////////////////
	public function get_folder($id){//ok
		// non è gestito il caso di richiesta dell'ID 0 (InBox)
		// forse da completare, bisogna recuperare anche i nomi di tutte le cartelle superiori
		$id = intval($id);
		$fld_arr = parent::get_array("SELECT id_cartella, nome, id_cartella_superiore FROM cartella 
			WHERE id_cartella='".$id."' AND id_utente=".$_SESSION['user']['id'].";");
		return $fld_arr[0];
	}
	public function get_subfolders($id = null){//ok
		if(is_integer(intval($id)) && $id>0) $id = " = $id ";
		else $id = ' IS NULL ';
		
		$subfolders = parent::get_array("
			SELECT id_cartella, nome, id_cartella_superiore FROM cartella 
				WHERE id_cartella = any
					(SELECT id_cartella FROM cartella
						WHERE id_cartella_superiore ".$id." 
						AND id_utente=".$_SESSION['user']['id'].");");
		return $subfolders;
	}
	public function get_all_subfolders($id){//ok
		// questo metodo serve per le ricerche
		$subs = $this->get_subfolders($id);
		$current_fld[] = $this->get_folder($id);
		if (is_array($subs)) {
			foreach($subs as $val) {
				$current_arr = $this->get_all_subfolders($val['id_cartella']);
				return array_merge($current_fld, $current_arr);
			}
		} else {
			return $current_fld;
		}
	}
	public function get_inbox_folder(){
		$inbox_fld = parent::get_array("
			SELECT id_cartella, nome, id_cartella_superiore FROM cartella 
				WHERE nome = 'InBox' 
					AND id_cartella_superiore IS NULL
					AND id_utente=".$_SESSION['user']['id'].";");
		return $inbox_fld[0];
	}
	private function get_parent_folder($id){//ok
		$fld_arr = parent::get_array("
			SELECT id_cartella, nome, id_cartella_superiore FROM cartella 
				WHERE id_cartella = 
					(SELECT id_cartella_superiore FROM cartella
						WHERE id_cartella='".$id."' 
						AND id_utente=".$_SESSION['user']['id'].");");
		return $fld_arr[0];
	}
	public function get_path_to_folder($id = null){//ok
		//la cartella stessa è inclusa
		/*{
		* il formato finale sarà un array del tipo:
		* $fld_tree[0]['nome cartella'] = "cartella attaccata alla radice"
		* $fld_tree[1]['nome cartella'] = "cartella intermedia"
		* $fld_tree[2]['nome cartella'] = "cartella intermedia"
		* $fld_tree[3]['nome cartella'] = "cartella foglia"
		}*/
		// l'id=0 non è permesso nel db
		if ($id==0) {
			$all_fld[0]['id_cartella'] = null;
			$all_fld[0]['id_cartella_superiore'] = null;
			$all_fld[0]['nome'] = null;
			
			return $all_fld;
		}
		// RICORSIVO
		// per ora si implementa in php ma se salta fuori una soluzione in PGSQL è preferibile
		$curr_fld[0] = $this->get_folder($id);
		$parent_id = $curr_fld[0]['id_cartella_superiore'];
		unset($curr_fld[0]['id_cartella_superiore']);
		if (!is_null($parent_id)) {
			$recursive_arr = $this->get_path_to_folder($parent_id);
			return array_merge($recursive_arr, $curr_fld);
		} else {
			return $curr_fld;
		}
	}
	public function add_folder($name, $parent_fld = null){
		/* per caso esiste già una copia di quella cartella? */
		$copy_parent = is_null($parent_fld)?" IS NULL ":" =$parent_fld ";
		$copy = $this->get_array("SELECT * FROM cartella
			WHERE nome='$name' AND id_cartella_superiore $copy_parent;");
		if (is_array($copy)) return false;
		/* fine controllo per copie... */
		$parent_fld = is_null($parent_fld)?" NULL ":" $parent_fld ";
		$fld = parent::get_array("
			INSERT INTO cartella (id_cartella, nome, id_cartella_superiore, id_utente)
				VALUES (DEFAULT, '$name', $parent_fld, ".$_SESSION['user']['id']." );");
		return $fld;
	}
	public function del_folder($id) {
		parent::get_array("DELETE FROM cartella 
			WHERE id_cartella=".$id." AND id_utente=".$_SESSION['user']['id']." ;");
	}
	public function ren_folder($id, $name) {
		parent::get_array("UPDATE cartella SET nome='".$name."' 
			WHERE id_cartella=".$id." AND id_utente=".$_SESSION['user']['id']." ;");
	}
	public function print_folder_tree($id = null){
		require_once('folder.class.php');
		$subs = $this->get_subfolders($id);
		echo "<dir>";
		if (is_array($subs)) {
			foreach($subs as $val) {
				echo '<li>';
				Folder::print_folder($val['nome'], $val['id_cartella'], $val['id_cartella_superiore']);
				//echo $separator;
				$this->print_folder_tree($val['id_cartella']);
			}
		}
		echo "</dir>";

	}
	public function print_folder_tree_msg($op, $id_msg, $fld=null, $id = null){
		// questo metodo è fondamentalmente per il file message_tools.php
		require_once('folder.class.php');
		$subs = $this->get_subfolders($id);
		echo "<dir>";
		if (is_array($subs)) {
			foreach($subs as $val) {
				$fld_query = is_null($fld)?"":"&fld=$fld";//per quando si tratta di move
				echo '<li>';
				echo "<a href=\"javascript: opener.location.href='message_tools.php?op=$op&id=$id_msg&fld_dest="
				.$val['id_cartella'].$fld_query."'; window.close()\" >".$val['nome']."</a> ";
				//echo $separator;
				$this->print_folder_tree_msg($op, $id_msg, $fld, $val['id_cartella']);
			}
		}
		echo "</dir>";

	}
	public function print_folder_tree_search($id=null){
		// questo metodo è fondamentalmente per il file message_tools.php
		$subs = $this->get_subfolders($id);
		echo "<dir>";
		if (is_array($subs)) {
			foreach($subs as $val) {
				$fld_query = is_null($fld)?"":"&fld=$fld";//per quando si tratta di move
				echo '<li>';
				echo "<a href=\"javascript: sendFolder(".$val['id_cartella'].",'".$val['nome']."'); window.close()\" >".$val['nome']."</a> ";
				//echo $separator;
				$this->print_folder_tree_search($val['id_cartella']);
			}
		}
		echo "</dir>";

	}
	////////////////////////////////}
	/////////////////////////ATTACHMENT{
	////////////////////////////////
	private function get_attachment_new_id(){
			$result = parent::get_array("select nextval('public.file_allegato_id_file_seq'::text)");
			return $result[0]['nextval'];
		}
	public function add_attachment($id_msg, $file_name, &$attachment) {
		// aggiungere l'allegato al fileystem
		$id_file = $this->get_attachment_new_id();
		$attach_dir = "./attachments/";
		$file_path = "$attach_dir$id_file/";
		if (!is_dir($attach_dir)) mkdir($attach_dir);
		mkdir($file_path);
		touch($file_path.$file_name);
		file_put_contents($file_path.$file_name, $attachment);
		// aggiungere al DB
		parent::get_array("INSERT INTO file_allegato 
			(id_messaggio, id_file, percorso, nome_allegato)
			VALUES ($id_msg, $id_file, '$file_path', '$file_name' );");
	}
	public function del_attachments(){
		//error_reporting(0);
		$to_del = parent::get_array("
			SELECT * FROM file_allegato
			WHERE id_messaggio IS NULL;");
		if (!is_array($to_del))
			return false;
		foreach ($to_del as $attach) {
			if(file_exists($attach['percorso'].$attach['nome_allegato']))
				unlink($attach['percorso'].$attach['nome_allegato']);
			if (rmdir($attach['percorso']))
				parent::get_array("DELETE FROM file_allegato 
					WHERE id_file=".$attach['id_file'].";");
		}
		return true;
	}
	////////////////////////////////}
	/////////////////////////CONTACT{
	////////////////////////////////
	public function get_contact_new_id(){
		$result = parent::get_array("SELECT nextval('public.contatto_id_contatto_seq'::text)");
		return $result[0]['nextval'];
	}
	public function get_contacts(){
		$results = parent::get_array("SELECT id_contatto, nome, cognome 
			FROM contatto WHERE id_utente = ".$_SESSION['user']['id']." ;");
		if(!is_bool($results)){
			foreach ($results as $key => $row){
				$tmp=$this->get_contact_email($row['id_contatto']);
				$results[$key]['email']=$tmp[0];
			}
		}
		return $results;
		
	}
	public function get_contact($id){
		$result = parent::get_array("SELECT * FROM contatto
			WHERE id_contatto=$id AND id_utente=".$_SESSION['user']['id']." ;");
		return $result[0];
	}
	public function get_contact_email($id){
		$tmp_result = parent::get_array("SELECT indirizzo_email FROM contatto_email
			WHERE id_contatto=$id  ;");
		if (is_array($tmp_result)){
			foreach ($tmp_result as $key => $email){
				$result[$key] = $tmp_result[$key]['indirizzo_email'];
			}
		}else $result = $tmp_result;
		return $result;
	}
	public function get_contact_number($id, $type){
		$tmp_result = parent::get_array("SELECT numero FROM contatto_numero
			WHERE id_contatto=$id AND tipo='$type' ;");
		if (is_array($tmp_result)){
			foreach ($tmp_result as $key => $email){
				$result[$key] = $tmp_result[$key]['numero'];
			}
		}else $result = $tmp_result;
		return $result;
	}
	public function add_contact($id, $name, $surname, $address) {
		parent::get_array("INSERT INTO contatto 
			(id_contatto, nome, cognome, indirizzo, id_utente) 
			VALUES ($id, '$name', '$surname', '$address', ".$_SESSION['user']['id']." );");
	}
	public function add_contact_email($id, $email){
		/* per caso esiste già una copia di quella contatto? */
		$copy_parent = is_null($parent_fld)?" IS NULL ":" =$parent_fld ";
		$copy = $this->get_array("SELECT * FROM contatto_email
			WHERE id_contatto='$id' AND indirizzo_email='$email' ;");
		if (is_array($copy)) return false;
		/* fine controllo per copie... */
		parent::get_array("INSERT INTO contatto_email 
		(id_contatto, indirizzo_email) 
		VALUES ($id, '$email' );");
	}
	public function add_contact_number($id, $number, $type){
		/* per caso esiste già una copia di quella contatto? */
		$copy_parent = is_null($parent_fld)?" IS NULL ":" =$parent_fld ";
		$copy = $this->get_array("SELECT * FROM contatto_numero
			WHERE id_contatto='$id' AND numero='$number' AND tipo='$type';");
		if (is_array($copy)) return false;
		/* fine controllo per copie... */
		parent::get_array("INSERT INTO contatto_numero 
		(id_contatto, numero, tipo) 
		VALUES ($id, '$number', '$type' );");
		return true;
	}
	public function mod_contact($id, $name, $surname, $address) {
		parent::get_array("UPDATE contatto 
			SET nome='$name', 
			cognome= '$surname',
			indirizzo= '$address'
			WHERE id_contatto=$id
			AND id_utente= ".$_SESSION['user']['id']." ;");
	}
	public function del_contact($id){
		parent::get_array("DELETE FROM contatto 
			WHERE id_contatto=$id AND id_utente=".$_SESSION['user']['id']." ;");
	}
	public function del_contact_email($id, $email){
		parent::get_array("DELETE FROM contatto_email 
			WHERE id_contatto=$id AND indirizzo_email='$email' ;");
	}
	public function del_contact_number($id, $number, $type){
		parent::get_array("DELETE FROM contatto_numero 
			WHERE id_contatto=$id AND numero='$number' AND tipo='$type' ;");
	}
	////////////////////////////////}
}



?>