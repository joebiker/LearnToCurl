<?php setcookie("reg_referral", getenv("HTTP_REFERER")); 
?><!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="author" content="Joe Petsche" />
	<meta name="DC.creator" content="Joe Petsche" />
	<title>Learn to Curl Registration</title>
	<link href="../learntocurl.css" rel="stylesheet" type="text/css" />
	<script type="text/javascript" src="http://code.jquery.com/jquery-1.10.1.min.js"></script>
	<script type="text/javascript" src="http://code.jquery.com/ui/1.10.3/jquery-ui.min.js"></script>
</head>

<body>
<div id="wrapper">
<div id="content">

<h1>Learn to Curl Registration</h1>
<P>Registration and payment on the website guarantees a spot in the class. Walk-ins are welcomed, but there is no guarantee of getting a spot on the ice. 
</P>

<form name="registration" ACTION="register.php" method="POST" >
<input type="hidden" name="type" value="newopenhouse">
	
<?php
include '../common.php';
include '../database.php';
$db_conn = connect_db($DB_SERVER, $DB_USER, $DB_PASS, $DB_NAME);	// from include

$ERROR_MSG = ""; // needs to be there as not to display PHP WARNING MESSAGE.
$modify_group    = "";
$modify_email    = "";
$modify_adults   = "";
$modify_juniors  = "";
$modify_event_id = "";
$modify_confirm  = "";

if( isset($_REQUEST['type']) && "editopenhouse" == $_REQUEST['type'] ) {  // edit registration
	
	if( isset($_REQUEST['confnumber']) && (strlen($_REQUEST['confnumber']) > 4) ) { // confirmation number exists in POST method
		if(isset($_POST['confnumber']) && $_POST['confnumber'] == "admin") { 
			die( "<meta http-equiv='refresh' content='2;url=openhouseadmin.php'>You will be re-directed in 2 seconds. If you are in a hurry, <a href='openhouseadmin.php'>click here</a>.");
			// http_redirect("openhouseadmin.php"); only if page data hasn't been sent.
		}
	}
	else {
		die("<div class='error'>The registration number could not be found. </div>");
	}
	
	$confemail = "";
	if( isset($_POST['confemail']) && (strlen($_POST['confemail']) > 4) ){ // email exists 
		$confemail = $_POST['confemail'];
	}
	
	$db_conn = connect_db($DB_SERVER, $DB_USER, $DB_PASS, $DB_NAME);	// from include
	
	// Check if confirmation code exists.....
	$query = "select group_name, email, group_adults, group_juniors, event_date, event_name, id from learntocurl, learntocurl_dates where openhouse_id=id and EVENT_DATE >= current_date() and CONFIRMATION = '".$_REQUEST["confnumber"]."' ";
	$result = mysql_query($query);
	if( !$result ) {
		die("<div class='error'>Could not execute your edit resuest: " . mysql_error(). "</div>");
	}
	$reg_details = mysql_fetch_array($result);
	
	if($reg_details ) {
		echo "<div class='success'>Edit registration</div>"; // only if successful edit
		$modify_group    = $reg_details[0];
		$modify_email    = $reg_details[1];
		$modify_adults   = $reg_details[2];
		$modify_juniors  = $reg_details[3];
		$modify_event_id = $reg_details[6];
		$modify_confirm  = trim($_REQUEST["confnumber"]);
	}
	else 
		$ERROR_MSG = "The confirmation code could not be found, or the event date has already passed. (";
	
}
?>

<fieldset>
<legend>
	<img class="icon" width="16" height="16" alt="" src="../stone.ico"/>
	<strong>Registration</strong>
</legend> 
<table cellpadding=2 cellspacing=0 border=0>
<TR>
<TD>Requested Event 
<TD><select name="openhouseid" id="openhouseid" onchange="checkReg(this.value);">
<?php
	// TODO: Create built in function to print <select> and eliminate open houses that are currently full.
	$events_result = mysql_query("select ID, EVENT_DATE, EVENT_NAME from learntocurl_dates where EVENT_DATE >= current_date() and EVENT_TYPE='L' order by EVENT_DATE ASC", $db_conn);
	if($events_result) { //query was a success
		while ($row = mysql_fetch_array($events_result, MYSQL_BOTH)) {
			if( $modify_event_id == $row[0] ) $selected = "selected"; else $selected = "";
			$stamp = strtotime($row[1]);
			$nicedate = date('D jS \of F Y h:i:s A', $stamp);
		    printf ("<option value='$row[0]' $selected>$nicedate</option>");  // $row[1] - $row[2]
		}
	}
	mysql_free_result($events_result);
	
?>
</select>
<TD><span id="eventname"></span></TR>

<TR>
<TD>Adult Leader 
<TD><input type="text" name="groupname" id="groupname" value="<?php echo $modify_group; ?>" size="30">
<TD>Email <input type="text" name="email" id="email"  value="<?php echo $modify_email; ?>" size="30"></TR>
<TR>
<TD>How many adults 
<TD><select name="adults" id="adults">
<?php if(strlen($modify_confirm) > 4 ) echo "<option>0</option>"; ?>
<option <?php if($modify_adults == 1) echo "selected"; ?>>1</option>
<option <?php if($modify_adults == 2) echo "selected"; ?>>2</option>
<option <?php if($modify_adults == 3) echo "selected"; ?>>3</option>
<option <?php if($modify_adults == 4) echo "selected"; ?>>4</option>
<option <?php if($modify_adults == 5) echo "selected"; ?>>5</option>
<option <?php if($modify_adults == 6) echo "selected"; ?>>6</option>
</select> <TD><span id="openspace"></span></TR>
<TR>
<TD>21 yrs old or younger 
<td><select name="juniors" id="juniors">
<option <?php if($modify_juniors == 0) echo "selected"; ?>>0</option>
<option <?php if($modify_juniors == 1) echo "selected"; ?>>1</option>
<option <?php if($modify_juniors == 2) echo "selected"; ?>>2</option>
<option <?php if($modify_juniors == 3) echo "selected"; ?>>3</option>
<option <?php if($modify_juniors == 4) echo "selected"; ?>>4</option>
</select> <td></TR>
<TR>
<TD colspan=3><textarea cols="80" rows="5" readonly="readonly" style="font-size: 10px;">
<?php
$waiver = "waiver_content.txt";
if (file_exists($waiver)) {
	readfile($waiver);
}
?>
</textarea><BR>
<input type="checkbox" name="waiver">I have read and understand the risk involved in curling<br>
</TR>

<TR>
<TD colspan=3><textarea cols="80" rows="2" readonly="readonly" style="font-size: 10px;">
<?php
$paypal = "payment_terms.txt";
if (file_exists($paypal)) {
	readfile($paypal);
}
?>
</textarea><BR>
<input type="checkbox" name="payment">I have read and understand the payment terms and conditions<br>
</TR>
<?php if(strlen($modify_confirm) > 4 ) 
echo "<TR><TD>Confirmation Number: <TD><input type='txt' readonly name='confnumber' value='".$modify_confirm."' size=6> <TD></TR>"; 
?>
<TR><TD colspan=3>
&nbsp;
</tr>
<TR><TD colspan=3>
How did you hear about our Learn to Curl Event? <input type=text name="user_refer" maxlength=255>
</TR>
<TR><TD colspan=3>
<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_paynowCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
<!-- input type="submit" value="Go to PayPal to pay" -->
</TR>
</TABLE>
</fieldset>
<P>Note: "You are guaranteed your spot only after submitting payment"</P>
<?php

$event_referral = "";
if( isset($_COOKIE["event_referral"]))
	$event_referral = $_COOKIE["event_referral"];
$reg_referral = getenv("HTTP_REFERER");
if($DEBUG) {
	echo "<BR>event: ".$event_referral;
	echo "<BR>reg: ".$reg_referral;
}

//send along with registration
echo '<input type=hidden name="learn_refer" value="'.$event_referral.'">';
echo '<input type=hidden name="reg_refer" value="'.$reg_referral.'">';

?>
<script>
$("#openhouseid").change(function() {
	var data = $(this).val();
	//alert (data);
	var requestname = $.ajax({  
		type: "GET",  
		url: "openhousecheck.php", 
		data: { id: data, name: 1 }
	});
	requestname.done(function(msg) {  
		//alert (msg);
		$("#eventname").html( msg );});

	var requestspace = $.ajax({  
		type: "GET",  
		url: "openhousecheck.php", 
		data: { id: data }
	});
	requestspace.done(function(msg) {  
		//alert (msg);
		$("#openspace").html( msg );});
	
}).trigger('change');
</script>
</form>

</div>
</div>

</body>
</html>