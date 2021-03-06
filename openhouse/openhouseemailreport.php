<?php

include '../common.php';
include "../database.php";

// process download file first
if(isset($_REQUEST['openhouseid']) && isset($_REQUEST['type']) ) {
	$id = trim($_REQUEST['openhouseid']);
	
	include "cEvent.php";
	$e = new Event($_REQUEST['openhouseid']);
	$arr = $e->getEmails();
	$nicedate = $e->getNiceDate();
	
	header("Content-type: text/csv");
	header("Content-Disposition: attachment; filename=$nicedate.csv");
	header("Pragma: no-cache");
	header("Expires: 0");

	echo 'Email Address,First Name,Last Name'."\r\n";
	foreach ( $arr as $guest ) {
		if(strlen($guest["email"]) > 0)
			echo $guest['email'].",".$guest['first'].",".$guest['last']."\r\n";
	}
	exit();
}


session_name('Private');
session_start();

// AUTH //
	$a = new Auth();
	$a->start();
	if (! $a->getAdmin()) {
		exit();
	}
//////////

?>
<HTML>
<HEAD>
<title>Learn to Curl - Admin Report - Email</title>
<link href="admin.css" rel="stylesheet" type="text/css" />
</HEAD>
<BODY>

<div id="report_list" style="position:absolute; right: 10; top: 0px; color: green; z-index: 1;">
	<form name="refresh" ACTION="openhouseemailreport.php" method="POST" >
	<input type="hidden" value="Jump to Open House Email Report">
	</form>
	
	<ul>
	<li><a href="openhousereferralreport.php">Referral Report</a>
	<li><a href="openhouseadmin.php">back to Admin</a>
	</ul>
</div>


<h1>Learn to Curl - Email Report</h1>
<form method='post' name='category'>
<input type="hidden" name="search" value="%">
<?php

	$db_conn = connect_db($DB_SERVER, $DB_USER, $DB_PASS, $DB_NAME);	// from include

	$modify_event_id = isset($_REQUEST['openhouseid'])?trim($_REQUEST['openhouseid']):"";
	echo 'Event: <select name="openhouseid" id="openhouseid">';
	echo '<option value=""></option>\n';

	$events_result = mysql_query("select ID, EVENT_DATE, EVENT_NAME from learntocurl_dates order by EVENT_DATE DESC", $db_conn);
	if($events_result) { //query was a success
		while ($row = mysql_fetch_array($events_result, MYSQL_BOTH)) {
			if( $modify_event_id == $row[0] ) $selected = "selected"; else $selected = "";
			$stamp = strtotime($row[1]);
			$nicedate = date('D jS \of F Y h:i:s A', $stamp);
		    printf ("<option value='$row[0]' $selected>$nicedate - $row[2]</option>\n");

		}
	}else echo mysql_error();
	mysql_free_result($events_result);

echo '</select>';

?>
<input type=submit value="Search">
</form>

<?php

if(isset($_REQUEST['openhouseid'])) {
	$id = trim($_REQUEST['openhouseid']);
	echo "<a href='".$_SERVER['PHP_SELF']."?openhouseid=".$id."&type=csv'>download with names";
	echo "<img style='vertical-align:middle;border-width:0;' width=60 src='http://openclipart.org/image/80px/svg_to_png/169752/file-icon-csv.png'></a>";
	include "cEvent.php";
	$e = new Event($_REQUEST['openhouseid']);
	$arr = $e->getEmails();

	echo "<table class='datatable'>";
	foreach ( $arr as $guest ) {
		if(strlen($guest["email"]) > 0)
			echo "<TR><TD>".$guest["email"]."</TD></TR>\n";
	}
	echo "</table>";
}
?>

</body>
</html>