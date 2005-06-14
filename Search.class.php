<?

class Search {
	public static function print_folder_test2($name, $id){
	echo "<a href=# onclick=".'"insert_id_directory('.$id.')">'.$name."</a>";
		//echo "<a href=#>".$name."</a>";
		//echo '<a href="view_folder.php?id='.$id.'">'.$name.'</a> ';//..........<img src="icons/spacer.gif" width="1" height="1">';
		//si può cancellare o modificare solo se non si tratta di InBOx
		/*if ($name!="InBox" || $parent!=null){
			echo '<font size=1>[<a href="javascript: renFolder('.$id.', \''.$name.'\')">rename</a>, ';//<img src="icons/freccia_o.gif" alt="rename folder" width="16" height="16" border="0" align="absmiddle"></a> ';
			echo '<a href="javascript: delFolder('.$id.')">delete</a>]</font>';//<img src="icons/ics.gif" alt="delete folder" width="16" height="16" border="0" align="absmiddle"></a> ';
		}*/
	}
public static function print_message_search($id, $subject, $date, $account, $col_separator =  '</td><td colspan="2">', $folder=null){
		if (!is_null($folder)) $folder = "&fld=$folder";
		else $folder = "";
		#$id_folder=$id.$folder;
		#echo '<a href="view_message.php?id='.$id.$folder.'">'.$subject.'</a>';
		?><a href="javascript:void(window.open('view_message.php?id=<?echo"$id.$folder"?> '))"><?echo"$subject"?></a><?#;href="."javascript:void(window.open('view_message.php?id=))".">".$subject."</a>";
		echo $col_separator.$date.$col_separator.$account;
		
	}
}

