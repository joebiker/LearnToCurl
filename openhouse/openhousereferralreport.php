<?php

session_name('Private');
session_start();

include '../common.php';
include "../database.php";

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
<title>Learn to Curl - Admin Report - Referral</title>
<link href="admin.css" rel="stylesheet" type="text/css" />
</HEAD>
<BODY>

<div id="report_list" style="position:absolute; right: 10; top: 0px; color: green; z-index: 1;">
	<form name="refresh" ACTION="openhouseemailreport.php" method="POST" >
	<input type="hidden" value="Jump to Open House Email Report">
	</form>
	
	<ul>
	<li><a href="openhouseemailreport.php">Email Report</a>
	<li><a href="openhouseadmin.php">back to Admin</a>
	</ul>
</div>


<h1>Learn to Curl - Referral Report</h1>
<form method='post' name='category'>
<input type="hidden" name="search" value="%">
<?php
	$db_conn = connect_db($DB_SERVER, $DB_USER, $DB_PASS, $DB_NAME);	// from include

	echo "<div id='view_openhouse'>";
	$result = mysql_query("select DATE_FORMAT(create_date,'%d-%b-%Y'),confirmation, gid, group_name,user_refer,reg_refer,openhouse_id from learntocurl where User_refer is not null and User_refer > '' group by confirmation order by create_date desc limit 100;", $db_conn);
	if($result) { //query was a success
		echo "<table>\n<TR><TH align=left title='Date user registered for event'>Registered</TH><TH align=left>Who<TH align=left>Referral Commented from User</TH><TH align=left>URL linked to registration</TH></TR>";
		while ($row = mysql_fetch_array($result, MYSQL_BOTH)) {
	
			echo "<TR><TD><nobr>".$row[0]."</nobr></TD>";
			echo "<TD><a href='openhouseuseredit.php?gid=".$row[2]."&openhouseid=".$row[6]."'>".$row[3]."</a></TD>"; // openhouseuseredit.php?gid=2611&openhouseid=215
			echo "<TD>".$row[4]."</TD>";
			
			// display short URL -- create function
			if( strncmp($row[5],"http",4) == 0 ) // display nice URL
				echo "<TD><A HREF='".$row[5]."'>".substr($row[5],strpos($row[5],'//')+2,35)."</A></TD> </TR>";
			else
				echo "<TD>".$row[5]."</TD></TR>\n";
	
		}
		echo "</table>";
	}
	
	mysql_free_result($result);
	echo "</table>";
	echo "</div>";
?>

</body>
</html>