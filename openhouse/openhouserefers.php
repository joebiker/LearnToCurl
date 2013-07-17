<?php

include '../common.php';
include '../database.php';
$db_conn = connect_db($DB_SERVER, $DB_USER, $DB_PASS, $DB_NAME);	// from include

?>
<HTML>
<HEAD>
<title>Admin Open House Report</title>
<LINK REL="SHORTCUT ICON" HREF="../../16ringHoriz.ico">
<link href="admin.css" rel="stylesheet" type="text/css" />
<script src="mootools-1.2.4-core.js" type="text/javascript"></script>
<script src="mootools-1.2.4.2-more.js" type="text/javascript"></script>
</head>
<body>

<h1>Evergreen Open House Referals</h1>

<?php

include '../../_includes/database.php';

if( isset($_POST['type']) && (strlen($_POST['type']) > 4) ) {
	$db_conn = connect_db($DB_SERVER, $DB_USER, $DB_PASS, $DB_NAME);	// from include

}

?>

<div id="logo" style="position:absolute; left: 10; top: 2px; color: green; z-index: -1;">
	<img src="/images/shield.gif" width="46" height="59">
</div>

<div id="report_list" style="position:absolute; right: 10; top: 0px; color: green; z-index: 1;">
	<form name="refresh" ACTION="openhouseemailreport.php" method="POST" >
	<input type="hidden" value="Jump to Open House Email Report">
	</form>
	
	<form name="refresh" ACTION="../../join/memform/memberadmin.php" method="POST" >
	<input type="hidden" value="Jump to Member Admin">
	</form>
	
	<ul>
	<li><a href="openhouseemailreport.php">Open House Email Report</a>
	<li><a href="../../members/">back to Board Tools</a>
	</ul>
</div>

<span id="openspace"></span>
<!-- use PHP to build list of available dates -->
	<!-- date, name, id for sessions. Name can be used to describe novice/attended openhouse before--game play -->

<?php
$db_conn = connect_db($DB_SERVER, $DB_USER, $DB_PASS, $DB_NAME);	// from include
?>

<div id=search>
Search by <b>confirmation number</b> or <b>group name</b>. <i>NOTE: The first occurance will be returned if
multiple exist!!! </I>  '%' wildcard works. 
<form method="post" action="openhousesearch.php" name="search">
<input type="text" name="search" value="<?php if ( isset( $_REQUEST['search'])) echo  $_REQUEST['search']; ?>" size=20>
<input type=submit value='Search'></form>
</div>


<?php
echo "<div id='view_openhouse'>";
echo "<h3>Referral Data</h3>";
$result = mysql_query("select DATE_FORMAT(create_date,'%d-%b-%y'),confirmation, group_adults, group_juniors,user_refer,reg_refer from openhouse where User_refer is not null and User_refer > '' order by create_date desc limit 500;", $db_conn);
if($result) { //query was a success
	// include "openhouse_view.php";
	echo "<table>\n<TR><TH>Registered</TH><TH>A</TH><TH>J</TH><TH>Comment referal</TH><TH>URL used to link to registration</TH></TR>";
	while ($row = mysql_fetch_array($result, MYSQL_BOTH)) {

		echo "<TR> <TD>".$row[0]."</TD><TD>".$row[2]."</TD><TD>".$row[3]."</TD><TD>".$row[4]."</TD>";
		
		// display short URL -- create function
		if( strncmp($row[5],"http",4) == 0 ) // display nice URL
			echo "<TD><A HREF='".$row[5]."'>".substr($row[5],strpos($row[5],'//')+2,35)."</A></TD> </TR>";
		else
			echo "<TD>".$row[5]."</TD> </TR>";

	}
	echo "</table>";
}

mysql_free_result($result);
echo "</table>";
echo "</div>";
?>


</body></html>