<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="author" content="Joe Petsche" />
	<meta name="DC.creator" content="Joe Petsche" />
	<title>Pending Registration</title>
	<link href="../learntocurl.css" rel="stylesheet" type="text/css" />
</head>
<body>

<div id="wrapper">
<div id="main">

<h1>Learn to Curl Registration</h1>

<?php
include '../common.php';
include '../database.php';
include "cEvent.php";

$confirmation_number = "";

if( isset($_POST['type']) && "newopenhouse" == $_POST['type'] ) { // continue with new registration
	if($DEBUG) echo "Registration for: <I>". $_POST['type'] . "</I> <BR>";
	
	if( isset($_POST['groupname']) && isset($_POST['email']) && isset($_POST['waiver']) && isset($_POST['payment']) &&
	    $_POST['groupname'][0] > "" && $_POST['email'][0] > "" && ($_POST['waiver']) == "on"  && ($_POST['payment']) == "on" ){

		    
	if( isset($_POST['confnumber']) && $_POST['confnumber'] > "" ) {
		$confirmation_number = $_POST['confnumber'];
		$_POST['type'] = "modifyopenhouse";
	}
	else 
   		$confirmation_number = createConfirmation($_POST["groupname"][0], $_POST["adults"] + $_POST["juniors"]);
   	
   	// insert request into database
	$db_conn = connect_db($DB_SERVER, $DB_USER, $DB_PASS, $DB_NAME);	// from include
	$available_spots = availableOpenhouseCountErrorMinus($_POST["openhouseid"], 1, $confirmation_number); // requires $db_conn
	
	if( ($_POST['adults'] + $_POST['juniors']) <= $available_spots ) {
		// registration open
	}
	else die ("<div class='error'>There is not enough space! Try another <a class='error' href='index.php'>Learn to Curl</a>.</div>");
	
	if( $_POST['type'] == "modifyopenhouse" ) {
		// This needs to be re-written now that multiple users are added to the registration JVP-July-2013
/*		
		$query = "update learntocurl set group_name='".$_POST['groupname']."', email='".$_POST['email']."', group_adults=".$_POST['adults'].", group_juniors=".$_POST['juniors'].", openhouse_id=".$_POST['openhouseid'].", edit_count=edit_count+1, edit_ip='".$_SERVER['REMOTE_ADDR']."', edit_date=now() where confirmation = '".$confirmation_number."'";
		
		$update = mysql_query($query, $db_conn);
		if( $update ) {
			echo "<div class='success'>Your Modifications were recorded.</div>";
		}
		else {
			die ("<div class='error'>An error occured saving your information, please try again later. ".mysql_error()."</div>");
		}
*/		
	} 
	else { //normal registration
	
	//TODO: dummy check, check 'group_name' if already exists
	
	$i = 0;
	$dbConfirm = $confirmation_number;
	while( ($dbConfirm == $confirmation_number) && ($i < 11) ) {
		/// check if random confirmation code exists in database
		$isConfirmation = mysql_query("select group_name from learntocurl where CONFIRMATION = '".$confirmation_number."'", $db_conn);
		if($isConfirmation) { //query was a success
			if( mysql_num_rows($isConfirmation) > 0 ) { // Confirmation was found
				// create new confirmation number
		   		$confirmation_number = createConfirmation($_POST["groupname"][0], $_POST["adults"] + $_POST["juniors"]);
				if($DEBUG) echo "<div class='error'>DEBUG: Confirmation number exists! Trying again... </div>";
			}
			$i++;
		}
	} //end while
	
	// Possibly check $_POST['num'] is really a number //
	
	$learn_refer = "";
	$reg_refer = "";
	if( isset($_POST['learn_refer']) )
		$learn_refer = $_POST['learn_refer'];
	if( isset($_POST['reg_refer']) )
		$reg_refer = $_POST['reg_refer'];
	$user_refer = htmlspecialchars ($_POST['user_refer']);
	
/*	// This needs to be re-written for multiple users JVP-July-2013

	/////////////////////////////// INSERT CUSTOMER ////////////////////////////////////////////
	$insert = mysql_query("insert into learntocurl (group_name, email, group_adults, group_juniors, confirmation, openhouse_id, learn_refer, reg_refer, user_refer, create_browser, create_ip) values('".htmlspecialchars($_POST['groupname'])."', '".htmlspecialchars($_POST['email'])."', ".$_POST['adults'].", ".$_POST['juniors'].", '".$confirmation_number."', '".$_POST['openhouseid']."', '".$learn_refer."', '".$reg_refer."', '".$user_refer."', '".$_SERVER['HTTP_USER_AGENT']."', '".$_SERVER['REMOTE_ADDR']."' ) ", $db_conn);
	
	if( $insert ) {
		echo "<div class='info'>Reservation is not gauranteed until payment is received!</div>";
	}
	else {
		die ("<div class='error'>An error occured saving your information, please try again later. ".mysql_error()."</div>");
	}
*/
	
	} // end normal registration
	
	// display registration 
	$e = new Event($_REQUEST['openhouseid']);
	$arr = $e->getEmails();
	$nicedate = $e->getNiceDate();
?>
Event: <span class='userinput'><?php 
echo $e->getNiceDate() ." - ". $e->getName(); ?> </span><BR>
Leader: <span class='userinput'><?php echo $_POST["groupname"][0]; ?> </span><BR>
Email: <span class='userinput'><?php echo htmlspecialchars($_POST["email"][0]); ?> </span><BR>
Number of adults: <span class='userinput'><?php echo htmlspecialchars($_POST["adults"]); ?> </span><BR>
Number of juniors: <span class='userinput'><?php echo htmlspecialchars($_POST["juniors"]); ?> </span><BR>
Confirmation number: <span class='userinput'><?php echo $confirmation_number; ?> </span><BR>


<?php payWithPayPal($confirmation_number, $e->getNiceDate());

if($DEBUG)	echo "<div class=debug >";
else 		echo "<div class=debug style='display:none;'>";

for ( $i=0; $i < count($_POST['groupname']); $i++) {
	$modified_name = "Unknown";
	if( $i < $_POST['adults'] ) {   // 1 based vs 0 based comparrison
		if(strlen($_POST['groupname'][$i])>0) {
			$modified_name = $_POST['groupname'][$i];
		}
		echo "<BR>$i ) $modified_name.  Email: ".$_POST['email'][$i];
		$insert = mysql_query("insert into learntocurl(group_name, email, group_adults, group_juniors, confirmation, openhouse_id, learn_refer, reg_refer, user_refer, create_browser, create_ip) ".
		"values('".htmlspecialchars($modified_name)."', '".htmlspecialchars($_POST['email'][$i])."', 1, 0, '".$confirmation_number."', '".$_POST['openhouseid']."', '".$learn_refer."', '".$reg_refer."', '".$user_refer."', '".$_SERVER['HTTP_USER_AGENT']."', '".$_SERVER['REMOTE_ADDR']."' ) ", $db_conn);
		if( $insert ) { /* echo "<div class='success'>Reservation is not gauranteed until payment is received!</div>";  */ }
		else { 	die ("<div class='error'>An error occured saving your information, please try again later. ".mysql_error()."</div>"); }
		
	} // end selected user
}

$i=0;
foreach($_POST['juniorname'] as $name) {
	$modified_junior = "Unknown";
	if( $i < $_POST['juniors']) {
		if(strlen($name)>0) {
			$modified_junior = $name;
		}
		echo "<BR>$i ) $modified_junior (junior)";
		$insert = mysql_query("insert into learntocurl(group_name, email, group_adults, group_juniors, confirmation, openhouse_id, learn_refer, reg_refer, user_refer, create_browser, create_ip) ".
		"values('".htmlspecialchars($modified_junior)."', '', 0, 1, '".$confirmation_number."', '".$_POST['openhouseid']."','".$learn_refer."', '".$reg_refer."', '".$user_refer."', '".$_SERVER['HTTP_USER_AGENT']."', '".$_SERVER['REMOTE_ADDR']."' ) ", $db_conn);
		if( $insert ) { /* echo "<div class='success'>Reservation is not gauranteed until payment is received!</div>";  */ }
		else { 	die ("<div class='error'>An error occured saving your information, please try again later. ".mysql_error()."</div>"); }
		
	}
	$i++;
}
echo "</div>";
?>
</P>
<br>
<hr>
<BR>
<P>
<?php

// send email confirmation
$to      = $_POST["email"][0];
$subject = 'Learn to Curl Registration Pending';
$headers = 'From: '.$EMAIL_FROM_ADMIN. "\r\n" .
    'Reply-To: '.$EMAIL_FROM_ADMIN;

$message = "Your registration is pending payment. Your spot is reserved when payment is received. \n\n".
"Event: ".$e->getNiceDate()." \n". // add event name
"Leader: ".$_POST['groupname'][0]." \n".
"Email: ".$_POST['email'][0]." \n".
"Number of adults: ".$_POST['adults']." \n".
"Number of juniors: ".$_POST['juniors']." \n".
"Confirmation number: ".$confirmation_number." \n".
"\n\n";  // removed link to continue payment.
// "If you haven't paid you can resume your registration from this link (and check payment status): \n".
// "http://www.evergreencurling.com/learn/openhouse/register.php?type=editopenhouse&confnumber=".$confirmation_number." \n";
if( !mail($to, $subject, $message, $headers) ) {
	// email didn't send, print message to screen
	echo "<p>Email did not send, contents would have been:</p>";
	echo "<pre>" . $message . "</pre>";
}
?>
<P>
<BR>
<?php
	}
	else {
		$goBack = "";
		if( !isset($_POST['waiver'])) {
			echo "<div class='error'>You must accept the waiver to continue.</div>";
			$goBack = 1;
		}

		if( !isset($_POST['payment'])) {
			echo "<div class='error'>You must accept the payment terms and conditions to continue.</div>";
			$goBack = 1;
		}
		if( isset($_POST['payment']) && isset($_POST['waiver']))   {
			echo "<div class='error'>Adult Leader, name and email, are required for registration. Please try again.</div>";
			if( $DEBUG ) {
				echo "Var: Waiver  = ".$_POST['waiver']."<BR>";
				echo "Var: Payment = ".$_POST['payment']."<BR>";
			} 
			$goBack = 1;
		}
		if ($goBack == 1) {
			echo "<BR><BR><div>Please use your browser's back button to go back and fix the problem.</div>";
		}

	}

} // End newopenhouse registration
else if( isset($_REQUEST['type']) && "editopenhouse" == $_REQUEST['type'] &&
		 isset($_REQUEST['confnumber']) )  {  // edit registration
	
	
	$confirmation_number = $_REQUEST['confnumber'];
	echo "<div class='success'>Current registration</div>";
	
	if( isset($confirmation_number) && (strlen($confirmation_number) > 4) ) { // confirmation number exists 
		if($confirmation_number == "admin") { 
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
	$query = "select group_name, email, sum(group_adults), sum(group_juniors), event_date, event_name, sum(paid_dollars), paid_type, openhouse_id from learntocurl, learntocurl_dates where openhouse_id=id and CONFIRMATION = '".$confirmation_number."'";
	$result = mysql_query($query);
	if( !$result ) {
		die("<div class='error'>Could not execute your edit resuest: " . mysql_error(). "</div>");
	}
	else if ( mysql_affected_rows ($db_conn) <= 0 ) {
		die("<div class='error'>Could not execute your registration resuest.</div>");
	}
	
	$reg_details = mysql_fetch_array($result);
	
	//For payWithPayPal(...) function -- no need to pass variables
	$_POST["groupname"][0] = $reg_details[0];
	$_POST["adults"] = $reg_details[2];
	$_POST["juniors"] = $reg_details[3];
	
?>
<P>

Event: <span class='userinput'><?php 
			$stamp = strtotime($reg_details[4]);
			$nicedate = date('l jS \of M g:i:s A', $stamp);
echo $nicedate ." - ". $reg_details[5]; ?> </span><BR>
Leader: <span class='userinput'><?php echo $reg_details[0]; ?> </span><BR>
Number of adults: <span class='userinput'><?php echo $reg_details[2]; ?> </span><BR>
Number of juniors: <span class='userinput'><?php echo $reg_details[3]; ?> </span><BR>
Confirmation number: <span class='userinput'><?php echo $confirmation_number; ?> </span><BR>
Payment status: <span class='userinput'><?php
if ($reg_details[6] < 1) {
	echo "Not paid";
}
else {
	echo "Paid $". $reg_details[6]." via ".$reg_details[7];
}
?>
</span><BR>



<?php

if ($reg_details[6] < 1) {

	if ( availableOpenhouseCountNoError($reg_details[8]) < ($reg_details[2]+$reg_details[3]) ) {
		// We are comparing number of available spots to all participants in the confirmation code
		echo "action: <span class='userinput'>$ERROR_FULL_EVENT</span>";
	}
	else {
		// allow the payWithPayPal function to get the proper details.
		$_POST["groupname"][0] = $reg_details[0];
		$_POST["adults"] = $reg_details[2];
		$_POST["juniors"] = $reg_details[3];
		$_POST["openhouseid"] = $reg_details[8];
		payWithPayPal($confirmation_number, $reg_details[4]);
		// Will display message with PayPal button
	}
}
else {
	echo "action: <span class='userinput'>No further action required.</span>";
	////  ask to PRINT WAIVERS  //////
}
?>


<?php

}
else {
	// code not regonised
	echo "<div class='error'>An error has occured, please try again.</div>";
}
?>


</div>
</div>
</body>
</html>
<?php

function payWithPayPal($confirmation_number, $openhousedate) {
	global $PP_FORM_POST,$PAYPAL_USER, $PAYPAL_PWD,$PAYPAL_SIGNATURE,$PAYPAL_CANCELURL,$PAYPAL_RETURN,$PAYPAL_NOTIFY_URL,$PAYPAL_BUSINESS;
	?>
<form action="<?php echo $PP_FORM_POST; ?>" method="post">
<input type=hidden name=USER value="<?php echo $PAYPAL_USER; ?>">
<input type=hidden name=PWD value="<?php echo $PAYPAL_PWD; ?>">
<input type=hidden name=SIGNATURE value="<?php echo $PAYPAL_SIGNATURE; ?>">
<input type=hidden name=CANCELURL value="<?php echo $PAYPAL_CANCELURL; ?>">
<INPUT TYPE="hidden" NAME="RETURN" value="<?php echo $PAYPAL_RETURN; ?>">
<!-- above line will be used for Auto Return. Needs auth_token.  Before this was enabled, paypal had a button that said return to website (used this link) -->
<INPUT TYPE="hidden" NAME="notify_url" value="<?php echo $PAYPAL_NOTIFY_URL; ?>">
<input type=hidden name=custom value="<?php echo $_POST["groupname"][0]; ?>">
<input type=hidden name=invoice value="<?php echo $confirmation_number; ?>">

Payment Due: <span class='userinput'><?php echo "$". calculatePrice($_POST["openhouseid"], $_POST["adults"], $_POST["juniors"], 0) ?></span>
<br><input type="hidden" name="cmd" value="_xclick">
<input type="hidden" name="business" value="<?php echo $PAYPAL_BUSINESS; ?>">
<input type="hidden" name="lc" value="US">
<input type="hidden" name="item_name" value="<?php echo $_POST["groupname"][0]; ?> - <?php echo $openhousedate; ?> (group of <?php echo $_POST["adults"] + $_POST["juniors"]; ?>) <?php echo $confirmation_number; ?>">
<input type="hidden" name="button_subtype" value="services">
<input type="hidden" name="amount" value="<?php echo calculatePrice($_POST["openhouseid"], $_POST["adults"], $_POST["juniors"], 0); ?>">
<input type="hidden" name="currency_code" value="USD">
<input type="hidden" name="shipping" value="0">
<input type="hidden" name="bn" value="PP-BuyNowBF:btn_paynowCC_LG.gif:NonHosted">
<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_paynowCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
</form>

<?php
}
?>
