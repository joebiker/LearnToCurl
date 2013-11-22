<?php if( strlen(getenv("HTTP_REFERER")) > 0) 
setcookie("reg_referral", getenv("HTTP_REFERER")); 
?><!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="author" content="Joe Petsche" />
	<meta name="DC.creator" content="Joe Petsche" />
	<title>Learn to Curl Registration</title>
	<link href="../learntocurl.css" rel="stylesheet" type="text/css" />
	<script src="//ajax.googleapis.com/ajax/libs/mootools/1.4.5/mootools-yui-compressed.js"></script>
	<script type="text/javascript" src="http://code.jquery.com/jquery-1.10.1.min.js"></script>
	<script type="text/javascript" src="http://code.jquery.com/ui/1.10.3/jquery-ui.min.js"></script>
	<script language="Javascript" type="text/javascript">
	
	$(document).ready(function(){
	    $("#parent1").css("display","none");
	    $(".field").css("display","none");
	    $("#extra_message").css("display","none");
	    $('.junior').hide();
	    $(".aboveage1").click(function(){
	        if ($('input[name=age1]:checked').val() == "No" ) {
	            $("#parent1").slideDown("fast"); //Slide Down Effect
	        } else {
	            $("#parent1").slideUp("fast");  //Slide Up Effect
	        }
	    });
	    $("#adults").change(function(){
		    $('.field').hide();
		    var selection = $('#adults').val();
		    if(selection>=2) {
		            $('#a2').slideDown("fast");
		            $('#extra_message').slideDown("slow");
	        }
		    if(selection>=3)
		            $('#a3').slideDown("fast");
		    if(selection>=4)
		            $('#a4').slideDown("fast");
		    if(selection>=5)
		            $('#a5').slideDown("fast");
		    if(selection>=6)
		            $('#a6').slideDown("fast");
		    if(selection>=7)
		            $('#a7').slideDown("fast");
		    if(selection>=8)
		            $('#a8').slideDown("fast");
		});
		$("#juniors").change(function(){
		    $('.junior').hide();
		    var selection = $('#juniors').val();
		    if(selection>=1)
		            $('#j1').show();
		    if(selection>=2)
		            $('#j2').show();
		    if(selection>=3)
		            $('#j3').show();
		    if(selection>=4)
		            $('#j4').show();
		});
	});

function checkReg($id) {
	//make the ajax call, replace text
	var req = new Request.HTML({
		method: 'get',
		url: 'openhousecheck.php',
		data: { 'id' : $id },
		onRequest: function() { /* alert('Request made. Please wait...'); */ },
		update: $('openspace'),
		onComplete: function(response) { /* alert('Request completed successfully.'); $('openspace').setStyle('background','#fffea1'); */
		}
	}).send();
	
	/* setTimeout('$(\'openspace\').setStyle(\'background\',\'#fffea1\')', 500);
	$('openspace').setStyle('background','#fffec1');
	$('openspace').setStyle('background','#fffed1');
	$('openspace').setStyle('background','#fffef1');
	$('openspace').setStyle('background','#ffffff'); */
	return;
}

	</script>
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
<TD>Adult Leader <TD><input type="text" name="groupname[]" id="groupname" value="<?php echo $modify_group; ?>" size="30"><td>Email <input type="text" name="email[]" id="email"  value="<?php echo $modify_email; ?>" size="30"></TR>
<TR id="a2" class="field">
<TD>Adult (2nd) <font color=red size=-1><sup>*</sup></font><TD><input type="text" name="groupname[]" id="groupname2"  value="<?php echo '' ?>" size="30"><td>Email <input type="text" name="email[]" id="email2"  value="<?php echo '' ?>" size="30"></tr>
<TR id="a3" class="field">
<TD>Adult (3rd) <font color=red size=-1><sup>*</sup></font><TD><input type="text" name="groupname[]" id="groupname3"  value="<?php echo '' ?>" size="30"><td>Email <input type="text" name="email[]" id="email3"  value="<?php echo '' ?>" size="30"></tr>
<TR id="a4" class="field">
<TD>Adult (4th) <font color=red size=-1><sup>*</sup></font><TD><input type="text" name="groupname[]" id="groupname4"  value="<?php echo '' ?>" size="30"><td>Email <input type="text" name="email[]" id="email4"  value="<?php echo '' ?>" size="30"></tr>
<TR id="a5" class="field">
<TD>Adult (5th) <font color=red size=-1><sup>*</sup></font><TD><input type="text" name="groupname[]" id="groupname5"  value="<?php echo '' ?>" size="30"><td>Email <input type="text" name="email[]" id="email5"  value="<?php echo '' ?>" size="30"></tr>
<TR id="a6" class="field">
<TD>Adult (6th) <font color=red size=-1><sup>*</sup></font><TD><input type="text" name="groupname[]" id="groupname6"  value="<?php echo '' ?>" size="30"><td>Email <input type="text" name="email[]" id="email6"  value="<?php echo '' ?>" size="30"></tr>
<TR id="a7" class="field">
<TD>Adult (7th) <font color=red size=-1><sup>*</sup></font><TD><input type="text" name="groupname[]" id="groupname7"  value="<?php echo '' ?>" size="30"><td>Email <input type="text" name="email[]" id="email7"  value="<?php echo '' ?>" size="30"></tr>
<TR id="a8" class="field">
<TD>Adult (8th) <font color=red size=-1><sup>*</sup></font><TD><input type="text" name="groupname[]" id="groupname8"  value="<?php echo '' ?>" size="30"><td>Email <input type="text" name="email[]" id="email8"  value="<?php echo '' ?>" size="30"></tr>
<TR id="j1" class="junior">
<TD>Junior (1st) <font color=red size=-1><sup>*</sup></font><TD><input type="text" name="juniorname[]" id="juniorname1"  value="<?php echo '' ?>" size="30"><td> - </tr>
<TR id="j2" class="junior">
<TD>Junior (2nd) <font color=red size=-1><sup>*</sup></font><TD><input type="text" name="juniorname[]" id="juniorname2"  value="<?php echo '' ?>" size="30"><td> - </tr>
<TR id="j3" class="junior">
<TD>Junior (3rd) <font color=red size=-1><sup>*</sup></font><TD><input type="text" name="juniorname[]" id="juniorname3"  value="<?php echo '' ?>" size="30"><td> - </tr>
<TR id="j4" class="junior">
<TD>Junior (4th) <font color=red size=-1><sup>*</sup></font><TD><input type="text" name="juniorname[]" id="juniorname4"  value="<?php echo '' ?>" size="30"><td> - </tr>
<TR id="j5" class="junior">
<TD>Junior (5th) <font color=red size=-1><sup>*</sup></font><TD><input type="text" name="juniorname[]" id="juniorname5"  value="<?php echo '' ?>" size="30"><td> - </tr>
<TR id="j6" class="junior">
<TD>Junior (6th) <font color=red size=-1><sup>*</sup></font><TD><input type="text" name="juniorname[]" id="juniorname6"  value="<?php echo '' ?>" size="30"><td> - </tr>
<TR id="j7" class="junior">
<TD>Junior (7th) <font color=red size=-1><sup>*</sup></font><TD><input type="text" name="juniorname[]" id="juniorname7"  value="<?php echo '' ?>" size="30"><td> - </tr>
<TR id="j8" class="junior">
<TD>Junior (8th) <font color=red size=-1><sup>*</sup></font><TD><input type="text" name="juniorname[]" id="juniorname8"  value="<?php echo '' ?>" size="30"><td> - </tr>

<TR id=extra_message class=info>
<TD colspan=3> <font color=red size=-1><sup>*</sup></font>Name and email addresses can be changed later. Participants will not automatically be emailed, if you would like them to know they are registered, please forward the registration email.
</TD></TR>

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
<option <?php if($modify_adults == 7) echo "selected"; ?>>7</option>
<option <?php if($modify_adults == 8) echo "selected"; ?>>8</option>
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
<!-- Plan on arriving 20 minutes prior to your scheduled start time. -->
<?php

$event_referral = "";
$reg_referral = "";
if( isset($_COOKIE["event_referral"]))
	$event_referral = $_COOKIE["event_referral"];
if( isset($_COOKIE["reg_referral"]))
	$reg_referral = $_COOKIE["reg_referral"];
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

<hr>
<!-- P> 
We are no longer allowing modifications:

If you need to modify your registration, please do so here.

<form name="editregistration" ACTION="register.php" method="POST">
<?php if( strlen($ERROR_MSG) > 0 ) echo "<DIV class='error'>".$ERROR_MSG ."</DIV>";
?>
<input type="hidden" name="type" value="editopenhouse">
Confirmation Number <input type="text" name="confnumber"  size="10"><br>
<input type="submit" value="Edit Registration">
</form
-->

</div>
</div>


</body>
</html>