<?php
require_once('db_mare.class.php');

class Folder {
	private $db;
	private $folder;
	
	public function __construct($id = null) {
		$this->db = new DB_mare();
		if (is_null($id)) {
			$this->folder = $this->db->get_inbox_folder();
			//^^^ quanto sopra non mi convince un gran che ma per ora...
		} else $this->folder = $this->db->get_folder($id);
	}
	public static function get_inbox_id() { 
		$db = new DB_mare();
		$fld = $db->get_inbox_folder();
		return $fld['id_cartella'];
	}
	public function is_valid_id(){
		// da completare
		if (isset($this->folder))
			return true;
		else return false;
	}
	public function get_subs() {
		return $this->db->get_subfolders($id);}
	public function get_path() {
		return $this->db->get_path_to_folder($id);}
	public function get_id() {
		return $this->folder['id_cartella'];}
	public function get_parent_id() {
		return $this->folder['id_cartella_superiore'];}
	public function get_name() {
		return $this->folder['nome'];}
	
	public static function print_folder($name, $id, $parent){
		echo '<a href="view_folder.php?id='.$id.'">'.$name.'</a> ';//..........<img src="icons/spacer.gif" width="1" height="1">';
		//si può cancellare o modificare solo se non si tratta di InBOx
		if ($name!="InBox" || $parent!=null){
			echo '<font size=1>[<a href="javascript: renFolder('.$id.', \''.$name.'\')">rename</a>, ';//<img src="icons/freccia_o.gif" alt="rename folder" width="16" height="16" border="0" align="absmiddle"></a> ';
			echo '<a href="javascript: delFolder('.$id.')">delete</a>]</font>';//<img src="icons/ics.gif" alt="delete folder" width="16" height="16" border="0" align="absmiddle"></a> ';
		}
	}
	public static function print_message($id, $subject, $date, $account, $col_separator =  '</td><td colspan="2">', $folder=null){
		if (!is_null($folder)) $folder = "&fld=$folder";
		else $folder = "";
		trim(&$subject);
		$subject = (is_null($subject)||empty($subject))?"--no subject--":$subject;
		echo '<a href="view_message.php?id='.$id.$folder.'">'.$subject.'</a>';
		echo $col_separator.$date.$col_separator.$account;
		
	}
	
	
	public static function print_form_stuff(){
		
		{?>
		<form action=<? $id=isset($_GET['id'])?("?id=".$_GET['id']):""; echo "folder_tools.php$id"; ?>
		 method="post" name="folder_op" id="folder_op">
		 <input name="fld_id" type="hidden" id="fld_id" value="">
		 <input name="operation" type="hidden" id="operation" value="">
		 <input name="folder_name" type="hidden" id="folder_name" value="New Folder">
		 <input name="back_address" type="hidden" id="back_address" value="folders.php">
		</form>
		<script language="JavaScript" type="text/JavaScript">
			document.getElementById("back_address").value = location.href;
			function newFolder() {
				var newName = prompt("Enter here the name for the new folder:","type here");
				if (newName=="") {
					alert("You can't leave blank the folder's name");
				} else if (newName!=null){
					document.getElementById("operation").value = "new";
					document.getElementById("folder_name").value = newName;
					document.getElementById("folder_op").submit();
				}
			}
			function delFolder(id) {
				document.getElementById("fld_id").value = id;
				document.getElementById("operation").value = "del";
				if (confirm("Please confirm the folder deletion.")) {
					document.getElementById("folder_op").submit();
				}
			}
			function renFolder(id, oldName) {
				var newName = prompt("Enter here the new name for the folder:", oldName);
				if (newName=="") {
					alert("You can't leave blank the folder's name");
					return false;
				} else {
					document.getElementById("fld_id").value = id;
					document.getElementById("operation").value = "ren";
					document.getElementById("folder_name").value = newName;
					document.getElementById("folder_op").submit();
				}
			}
		</script>
		<?}
	}
	
	
}




?>