<?php
include '../common.php';
include '../database.php';
		
if( isset($_GET['id']) && (strlen($_GET['id']) < 10) ) { // no sql injection please
// Display a text message about the registration for the given ID
	$db_conn = connect_db($DB_SERVER, $DB_USER, $DB_PASS, $DB_NAME);	// from include
	$openspace = availableOpenhouseCountNoError($_GET['id']);

	if ($openspace == 1 ) {
		echo "Only one space is open";
	}
	else if ($openspace <= 0 ) {
		echo "Selected Open House is currently full.";
	}
	else {
		echo "There are ";
		echo $openspace;
		echo " spots remaining.";
	}

} else if (isset($_GET['confirmation_number']) && (strlen($_GET['confirmation_number']) == 5) ) { // no sql injection please
	$db_conn = connect_db($DB_SERVER, $DB_USER, $DB_PASS, $DB_NAME);	// from include
// get attended or waiver flag
// get on or off flag
	if ( isset($_GET['field']) && isset($_GET['value']) ) {
		if ( strcmp($_GET['field'], "attended") != 0 )
			$_GET['field'] = "waiver";
		if ( strcmp($_GET['value'], "on") == 0 )
			$_GET['value'] = "1";
		if ( strcmp($_GET['value'], "1") != 0 )
			$_GET['value'] = "0";	
			
		echo setFlag($_GET['confirmation_number'], $_GET['field'], $_GET['value']);
	}
	else 
		echo "Error 1";
} else {
	echo "NO! ";
	echo var_dump($_REQUEST);

}

?>