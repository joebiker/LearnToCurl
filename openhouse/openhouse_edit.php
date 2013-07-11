<?php 
	
if( isset($_POST['type']) &&$_POST['type'] == "modifyopenhouse" ) { // edit registration
		// UPDATE sql 
		$db_conn = connect_db($DB_SERVER, $DB_USER, $DB_PASS, $DB_NAME);	// from include
	
		$query = "update learntocurl set group_name='".$_POST['groupname']."', email='".$_POST['email']."', group_adults=".$_POST['adults'].", group_juniors=".$_POST['juniors'].", openhouse_id=".$_POST['openhouseid'].", edit_count=edit_count+1, edit_ip='".$_SERVER['REMOTE_ADDR']."', edit_date=now() where confirmation = '".$confirmation_number."'";
		
		// needs paid dollars / attended / waiver
		$update = mysql_query($query, $db_conn);
		if( $update ) {
			echo "<div class='success'>Your modifications were recorded.</div>";
		}
		else {
			die ("<div class='error'>An error occured saving your information, please try again later. ".mysql_error()."</div>");
		}
		
	} 

?>