<?php
//require_once ('Mail/RFC822.php');


class Message_parser {
	public static function date_prepare($date) {
		/*
		Per inserire date nel formato corretto  bisogna controllare che nella data non si presente la timezone si in formato numerico che di testo
		
		Es Thu, 24 Mar 2005 20:24:35 -0500 (EST)
		
		=> aggiungere al parser funzione che controlla ciò ed eventualmente elimini la Timezone  in versione letterale 
		control_date() fa ciò
		
		*/
		if(strstr($date,"(")) {
			$date = explode("(",$date);
			return $date[0];
		}
		else return $date; 
	
	}
	public static function parse_received($st_t){
		$st_t=trim($st_t);
		
		//estraggo la data e la stringa  che contiene i form e i by 
		$risultato = explode(";", $st_t);
		$st_t = $risultato[0];
		
		$date = self::date_prepare($risultato[1]); 
		$str_by_iniziale = strstr($st_t, "by");
		if( (substr($st_t,0,4) == "from")) {
			
			//trovo la stringa_by e la rimouovo dalla stringa totale così mi rimane la stringa from su cui inizio a lavorare
			$str_from_iniziale = trim(str_replace($str_by_iniziale,"",$st_t));
			
			if($check=strpos($str_from_iniziale,"(")!=false){
				$risultato = explode("(",$str_from_iniziale);
				if(trim($risultato[0])!="from") {
					$str_from= substr($risultato[0],5);
				} else  {
					/* verifico presenza parentesi quadre che non siano in posizione 0 
					se true prendo il valore che precede le quadre altrimenti il valore interno alle quadre */
					if($check=strpos($risultato[1],"[")==false) {		
						$risultato = explode(" ",$risultato[1]);
						
						//controlla che non sia rimasta )
						#echo $risultato[0];
						$str_from = substr($risultato[0],0,strlen($risultato[0])-1);
					} elseif($check=strpos($risultato[1],"[")==0) {
						$risultato = explode(" ",$risultato[1]);
						$str_from = $risultato[0];
					} elseif($check=strpos($risultato[1],"[")!=0) {
						$risultato = explode(" ",$risultato[1]);
						$str_from = $risultato[0];
					}
				}
			}
		}
		
		$string_temp=strstr($str_by_iniziale, "with");
		$str_by_iniziale = str_replace($string_temp,"",$str_by_iniziale); 
		//stringa by su cui bisogna lavorare per togliere () ed eventuali quadre contenute..
		
		if($check=strpos($str_by_iniziale,"(")!=false){
			
			$risultato = explode("(",$str_by_iniziale);
			
			if(trim($risultato[0])!="by") {
				$str_by= substr($risultato[0],3);
			} else  {
				//verifico presenza parentesi quadre che non siano in posizione 0 se true prendo il valore che precede le quadre altrimenti il valore interno alle quadre
				if($check=strpos($risultato[1],"[")==false) {		
					$risultato = explode(" ",$risultato[1]);
					//controlla che non sia rimasta )
					$str_by = substr($risultato[0],0,strlen($risultato[0])-1);
				} elseif($check=strpos($risultato[1],"[")==0) {
					$risultato = explode(" ",$risultato[1]);
					$str_by = $risultato[0];
				} elseif($check=strpos($risultato[1],"[")!=0) {
					$risultato = explode(" ",$risultato[1]);
					$str_by = $risultato[0];
				}
			}
		}
		trim(&$str_from);
		trim(&$str_by);
		$ricevuto = array(	'host' => $str_from,
							'date' => $date,
							'type' => 'from');
		/*,  array(	'host' => $str_by,
					'date' => $date,
					'type' => 'by');*/
		return $ricevuto;
	}
	public static function prepare_bodies($msg){
		if (trim($msg->ctype_primary)=="text" and trim($msg->ctype_secondary)=="plain"){
			$body['text']=$msg->body;
			$body['html']=null;
		}
		elseif(trim($msg->ctype_primary)=="text" and trim($msg->ctype_secondary)=="html"){
			$body['text']=null;
			$body['html']=$msg->body;
		}
		elseif(trim($msg->ctype_primary)=="multipart" and trim($msg->ctype_secondary)=="alternative"){
			$body['text']=$msg->parts[0]->body;
			$body['html']=$msg->parts[1]->body;
			
		}
		elseif(trim($msg->ctype_primary)=="multipart" and trim($msg->ctype_secondary)=="mixed"){
			if (trim($msg->parts[0]->ctype_primary)=="text" and trim($msg->parts[0]->ctype_secondary)=="plain"){
				$body['text']=$msg->body;
				$body['html']=null;
			}
			elseif (trim($msg->parts[0]->ctype_primary)=="text" and trim($msg->parts[0]->ctype_secondary)=="html"){
				$body['text']=null;
				$body['html']=$msg->body;
			}
			elseif(trim($msg->parts[0]->ctype_primary)=="multipart" and trim($msg->parts[0]->ctype_secondary)=="alternative"){
				$body['text']=$msg->parts[0]->parts[0]->body;
				$body['html']=$msg->parts[0]->parts[1]->body;
			}
		}
		return $body;
	}
	public static function is_forwarded($subject){
		if( (preg_match('/^(?i)(\[fwd:)|(i:)|(fwd:)|(fw:)(?-i)/',$subject)) )
			return true;
		else 
			return false;	
	}
	public static function get_newsgroup_name($string){
		$result = explode("<",$string);
		$result[0] = trim($result[0]);
		$result[1] = trim(str_replace(array(">","<"), "", $result[1]));
		
		
		
		if (empty($result[0]) && empty($result[1]))
			return null;
		else return empty($result[1])?$result[0]:$result[1];
		#                   finisce qui                       #
		#######################################################
		//return $result[0]." - ".$result[1];
		//$result = substr($result[1],0,(strlen($result[1])-1) );
	}
}

?>