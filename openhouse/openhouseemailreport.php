<HTML>
<link href="admin.css" rel="stylesheet" type="text/css" />
<title>Learn to Curl Email Report</title>
<BODY>

<div id="report_list" style="position:absolute; right: 10; top: 0px; color: green; z-index: 1;">
	<form name="refresh" ACTION="openhouseemailreport.php" method="POST" >
	<input type="hidden" value="Jump to Open House Email Report">
	</form>
	
	<ul>
	<li><a href="openhouseadmin.php">back to Admin</a>
	</ul>
</div>


<h3>Email Report</h3>
<form method='post' name='category'>
<input type="hidden" name="search" value="%">
<?php
	include '../common.php';
	include '../database.php';		
	$db_conn = connect_db($DB_SERVER, $DB_USER, $DB_PASS, $DB_NAME);	// from include

	$modify_event_id = isset($_REQUEST['openhouseid'])?trim($_REQUEST['openhouseid']):"";
	echo 'Event: <select name="openhouseid" id="openhouseid">';
	echo '<option value=""></option>\n';

	$events_result = mysql_query("select ID, EVENT_DATE, EVENT_NAME from openhouse_dates order by EVENT_DATE DESC", $db_conn);
	if($events_result) { //query was a success
		while ($row = mysql_fetch_array($events_result, MYSQL_BOTH)) {
			if( $modify_event_id == $row[0] ) $selected = "selected"; else $selected = "";
			$stamp = strtotime($row[1]);
			$nicedate = date('D jS \of F Y h:i:s A', $stamp);
		    printf ("<option value='$row[0]' $selected>$nicedate - $row[2]</option>\n");  // $row[1] - $row[2]

		}
	}else echo mysql_error();
	mysql_free_result($events_result);

echo '</select>';

?>
<input type=submit value="Search">
</form>

<?php

if(isset($_REQUEST['search'])) {
	$s = trim($_REQUEST['search']);
	$id = trim($_REQUEST['openhouseid']);
		
	// Basic Search Query
	$query = "select group_name, email from openhouse where openhouse_id = $id ";
	
	$a = explode(":", $s);
	if( count($a) > 1) {
		echo "bad query hit ";
		$query = "select id, first, last, address1, city, state, zip, country, homephone, cellphone, email, SEX, DOB, experience, comments, team from members where id IN (select member from attributes a, attribute_lnk l where a.id = l.attribute and a.attribute like '%$a[1]%') order by last ASC";
	}

	$result = mysql_query($query, $db_conn);
	// echo $query; // DEBUG
	if($result) {
		//echo "<BR>Row count: ".mysql_num_rows($result) ."<BR>";
		echo "<table class='datatable'>";

		if ( mysql_affected_rows() == 0 )
			echo "<TD>No results found</TD>"; 
		while ($row = mysql_fetch_array($result, MYSQL_BOTH)) {
			$t_jscript = "";
			$html = "";
			if(strlen($row[1])>0)
				echo "<TR><TD>$row[1]</TD></TR>\n";
		}
	    echo "</table>";
	}
	else {
		echo "SQL Failed: ". mysql_error();
	}
	mysql_free_result($result);
} // end isset

?>

</body>
</html>