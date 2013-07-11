<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Learn to Curl</title>
	<link href="learntocurl.css" rel="stylesheet" type="text/css" />
</head>
<body>

<div id="wrapper">
<div id="main">

<h1>Learn to Curl</h1>
<hr />

<h3>What to expect at an Learn to Curl...</h3>
 <p>Set aside about 2 hours to learn to curl.  
 It all starts off with a brief off ice instruction including curling terminology.
 Once on the ice, instructors will go over the basics of curling including throwing a stone and sweeping. 
 Participants are urged to wear layered, loose clothing and sneakers. All other equipment is provided.
 Be sure to arrive 15 mins before the scheduled start time of your Learn to Curl.
 </p>

<h3>Sessions:</h3>
<?php
include "common.php";
include "database.php";
$db_conn = connect_db($DB_SERVER, $DB_USER, $DB_PASS, $DB_NAME);	// from include

$result = mysql_query("select EVENT_NAME, EVENT_DATE, MAX_GUESTS, ID from openhouse_dates where EVENT_DATE > adddate(now(),-1) order by EVENT_DATE asc", $db_conn);
if($result) { //query was a success
	while ($row = mysql_fetch_array($result, MYSQL_BOTH)) {
		$phpdate = strtotime( $row[1] );
		$remain = availableOpenhouseCount($row[3]);
		if ($remain < 0 ) $remain = 0 ;
	    printf (date('g:i A - l F j, Y',$phpdate) . " -  (". $remain." spaces)  <BR/>");
	}
	
	$sessions_avail = mysql_num_rows ( $result );
	if ($sessions_avail == 0) {
		printf ("<B>No Learn to Curl sessions currently scheduled.</B> Check back soon! You can also sign up for our email notification list. Look for the sign up form on this page.<BR/>");
	}
	else {
		printf ("<p><font color=red size='+1'><a href='openhouse/'>Sign up</a> to reserve your space! </font></p>");
	}
}

?>

<hr />
<h3>Reasons to Curl </h3>
<ul>
	<li>You saw curling on TV and you want to give it a try!</li>
	<li>You enjoy competitive sports either by yourself  or with friends and family.</li>
	<li>You want to play a winter sport that would  benefit you and your family's physical health and mental well-being!</li>
	<li>You want to get your teenagers out of the house?</li>
	<li>Curling is not only a fun sport for the whole family, it's a game steeped in tradition and it teaches valuable life lessons about problem solving, sportsmanship, leadership, communication, etiquette and more!</li>
	<li>You want to get out and meet new people and  socialize in an open and non-threatening environment!</li>
	<li>Sound good? Then curling is for you. Come on out to our Learn to Curl and give curling a try. You have got nothing to lose, and a great new experience to gain.</li>
</ul>

<P>Good Curling!</P>
</div> <!-- main -->

<br clear="all" />

</div> <!-- wrapper -->
</body>
</html>