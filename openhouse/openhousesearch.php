<?php

include '../common.php';
include '../database.php';
$db_conn = connect_db($DB_SERVER, $DB_USER, $DB_PASS, $DB_NAME);	// from include

if(isset($_REQUEST['search'])) {
	$s = trim($_REQUEST['search']);
	echo $s;
	
	$query = "select id from learntocurl oh, learntocurl_dates d where (group_name like '$s' or confirmation like '$s') and d.id = oh.openhouse_id";
	$result = mysql_query($query, $db_conn);
	echo $query;
	if($result) { //query was a success
		//echo "<BR>Row count: ". mysql_num_rows($result) ."<BR>";
		$row = mysql_fetch_row($result);
		echo "<p>good</p>";
		$oh_id = $row[0];
		echo "<BR>". $oh_id;
		if( $oh_id > 0 ) {
			header( "Location: openhouseview.php?view=$oh_id" ) ;
		}
		else {
			header( "Location: openhouseadmin.php?search=$s" ) ;
		}
	}
}
else 
{
	// no search found
	header( 'Location: openhouseadmin.php' );
}
?>
<HTML>
<HEAD>
<title>Admin Open House Report</title>
<body>





</body>
</html>