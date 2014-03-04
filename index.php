<?php if( strlen(getenv("HTTP_REFERER")) > 0) 
setcookie("event_referral", getenv("HTTP_REFERER"));
?><!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="author" content="Joe Petsche" />
	<meta name="DC.creator" content="Joe Petsche" />
	<title>Learn to Curl</title>
	<link href="learntocurl.css" rel="stylesheet" type="text/css" />
</head>
<body>

<div id="wrapper">
<div id="main">

<h1>Learn to Curl</h1>
<hr />

<h3>What to expect at a Learn to Curl</h3>
 <p>Set aside about 2 hours to learn to curl.  
 It all starts off with a brief off ice instruction including curling terminology.
 Once on the ice, instructors will go over the basics of curling including throwing a stone and sweeping. 
 Participants are urged to wear layered, loose clothing and clean sneakers. All other equipment is provided.
 Be sure to arrive 15 mins before the scheduled start time.
 </p>

<h3>Sessions:</h3>
<?php

$eventTypes = "'L','P'"; // must be single quoted and seperated by commas: 'L','O','P'
$hoursPreDelay = 2; // how long before the event starts to not display on list

include "openhouse/cEvent.php";
$oh = new Event();
$myarray = $oh->getAvailableOpenhouses_delay($hoursPreDelay, $eventTypes);
if( count($myarray) >0 ) {
	echo "\n<table class=eventList>";
	foreach($myarray as $event) {
		$openhouse = new Event($event['ID']);
		$remain = $openhouse->availableOpenhouseCount();
		echo "\n<TR id='".$event['EVENT_TYPE'].$event['ID']."'><TD>".date('g:i A - l F j, Y',strtotime($event["EVENT_DATE"]))."&nbsp;</TD><TD>".$event["EVENT_NAME"]." (". $remain." spaces)</TD></TR>";
	}
	echo "\n</table>";
	echo "<p><font color=red size='+1'><a href='openhouse/'>Sign up</a> to reserve your space! </font></p>";
	
} else { // none available
	printf ("<P><B>No sessions currently scheduled.</B> Check back soon! You can also sign up for our email notification list. Look for the sign up form on this page.<BR/></P>");
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
<?php

$event_referral = "";
if( isset($_COOKIE["event_referral"]))
	$event_referral = $_COOKIE["event_referral"];
if($DEBUG) echo $event_referral;

?>
</div> <!-- wrapper -->
</body>
</html>