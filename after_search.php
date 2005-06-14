<?php
require_once('db_cover.class.php');
require_once('db_mare.class.php');
//DATO UN PERCORSO A UNA CARETLLA CI ESTRAE L'ID DI QUELLA CARTELLA

#$str_test = "/uni/progetto mare/riki/bdlab/sub1/sub2/sub3/sub4";
$str_test = "/InBox/1/2/3";

$my_db = new DB_mare();
$explode_result = explode("/",$str_test);

#echo "".$explode_result[1]."";
#echo "".$explode_result[count($explode_result)-1]."";

//parte dal presupposto scontato che sullo stesso livello non si possono avere cartelle con lo stesso nome
/*function get_search_folder($name,$id_superiore)
{
	$id = parent::get_array("SELECT id_cartella 
						FROM cartella WHERE nome='$name' and id_catella_superiore='$id_superiore'");
	return $id;
}
*/
$id = $my_db->get_search_folder($explode_result[1],"no_sup");

#foreach ($id as $v_k => $v)
	#echo "<br> $v_k  $v <br> ";
	#echo "".$id[0]['id_cartella']."";
	#foreach ($v as $v_k1 => $v1)
#	echo "<br>".$id[0]['id_cartella'].  "<br> ";
#$id = $id[0]['id_cartella'];
#$id =  $my_db->get_search_folder($explode_result[$i],$id);
for ($i=2;$i<= (count($explode_result)-1);$i++)
{
	/*foreach ($id as $v_k => $v)
	echo "<br>chiavwe $v_k  $v <br> ";
	foreach ($v as $v_k1 => $v1)
	echo "<br> cartella" .$id[0]['id_cartella'].  "<br>";
	*/
	#$id_temp = $my_db->get_search_folder($explode_result[$i],"$id");
	#echo "".$id[0]['id_cartella']."";
	#echo "<br>".$explode_result[$i]."<br>";
	#$id = $id[0]['id_cartella'];	 
	$id =  $my_db->get_search_folder($explode_result[$i],$id);
	#echo "<br>id$i ".$id[$i]['$id_cartella']."";
	#$id[$i-1]['id_cartella'];
}

echo "<br>id ".$id."";
?>