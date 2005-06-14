<?php
require_once('db_mare.class.php');

class Message {
	
	private $db;
	private $message;
	
	public function __construct($id) {
		$this->db = new DB_mare();
		$this->message = $this->db->get_message($id);
		
	}
	public function get_id(){
		return $this->message['id_messaggio'];
	}
	public function is_valid_id(){
		if (isset($this->message))
			return true;
		else return false;
	}
	public function is_valid_folder($id_fld){
		$result = $this->db->get_array("
			SELECT id_messaggio FROM inclusione_cartella_msg
			WHERE id_messaggio=".$this->get_id()." AND id_cartella=$id_fld;");
		return isset($result)?true:false;
	}
	public function get_date() {
		return $this->message['date'];
	}
	public function get_subject(){
		return $this->message['subject'];
	}
	public function is_forwarded(){
		return ($this->message['forwarded']=='t')?true:false;
	}
	public function is_reply(){
		$replied = $this->db->get_message_in_reply_to($this->get_id());
		return (is_array($replied))?true:false;
	}
	public function get_text() {
		if (is_null($this->message['testo_normale']) || $this->message['testo_normale']=="")
			return null;
		else return $this->message['testo_normale'];
	}
	public function get_html() {
		if (is_null($this->message['testo_html']) || $this->message['testo_html']=="")
			return null;
		else return $this->message['testo_html'];
	}
	public function get_addresses($type = null){
		return $this->db->get_message_addresses($this->get_id(), $type);
	}
	public static function store_in_db($message){
		//echo $current_msg;    
		require_once ('Mail/mimeDecode.php');
		echo "<hr>";
		$params['include_bodies'] = true;
		$params['decode_bodies']  = false;
		$params['decode_headers'] = true;
		$params['input']          = $message;
		$params['crlf']           = "\r\n";
		
		$structure = Mail_mimeDecode::decode($params);
		
		#$decoder = new Mail_mimeDecode($current_msg, "\r\n");
		#$structure = $decoder->decode();
		var_dump($structure);
		
		echo "</pre><hr><hr><hr>";
		
	}
	public static function print_address($msg_arr){
		echo is_null($msg_arr['nome_visualizzato'])?$msg_arr['email']:"";
		echo is_null($msg_arr['email'])?$msg_arr['nome_visualizzato']:$msg_arr['nome_visualizzato']." (".$msg_arr['email'].")";
		if (is_null($msg_arr['id_contatto']))
			echo '
			<a href="contact_tools.php?op=reg&id_msg='.$this->get_id().'&pos='.$msg_arr['posizione'].'&type='.$types[$i].'">
			<img src="icons/reg_user.gif" alt=\''.$msg_arr['email'].'\' width="16" height="16" border="0" align="absbottom"></a>, ';
		else 
			echo '
			<a href="view_contact"><img src="icons/info_user.gif" alt=\''.$msg_arr['email'].'\' width="16" height="16" border="0" align="absbottom"></a>, ';
		
	}
	public function print_addresses($separator="\n</tr><tr>\n"){
		//////////////FROM
		$types = array("from", "sender", "reply-to", "to", "cc", "bcc");
		//cicla su ogni tipo di indirizzo
		for ($i=0;$i<count($types);$i++){
			$addr = $this->get_addresses($types[$i]);
			// se per il tipo corrente ci sono indirizzi li stampiamo
			if (is_array($addr)) {
				echo '<td><font size="2"><strong>'.ucwords($types[$i]).': </strong>'; 
				foreach ($addr as $val) {
					$nome = trim($val['nome_visualizzato']);
					$nome = empty($nome)?$val['indirizzo_email']:$nome;
					$mail = !empty($val['indirizzo_email'])?"mailto:".$val['indirizzo_email']:"#";
					echo "<a href='$mail'>$nome</a> "; 
					if (is_null($val['id_contatto']))
						echo '
						 <a href="contact_tools.php?op=reg&id_msg='.$this->get_id().'&pos='.$val['posizione_in_elenco'].'&type='.$types[$i].'">
						 <img src="icons/reg_user.gif" alt=\''.$val['email'].'\' width="16" height="16" border="0" align="absbottom"></a>, ';
					else 
						echo '
						 <a href="view_contacts.php?id='.$val['id_contatto'].'"><img src="icons/info_user.gif" alt=\''.$val['indirizzo_email'].'\' width="16" height="16" border="0" align="absbottom"></a>, ';
				}
				echo '</font></td>';
			} //else echo '<font size=1>no from addresses</font>';
			echo "\n$separator\n";
		}
	}
	public function get_attachments(){
		return $this->db->get_message_attachments($this->get_id());
	}
	
}



?>