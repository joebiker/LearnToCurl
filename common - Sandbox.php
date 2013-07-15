<?php

$DEBUG            = 0;
$ERROR_MSG        = "";
$PP_FORM_POST     = "https://www.sandbox.paypal.com/cgi-bin/webscr"; // sandbox
$PAYPAL_BUSINESS  = "joebiker-facilitator@gmail.com";
$PAYPAL_USER      = "joebiker-facilitator_api1.gmail.com";
$PAYPAL_PWD       = "1373322733";
$PAYPAL_SIGNATURE = "ALwSR0njMxqCl66dLcAXlqfM.9TBADul6C6AD9Lx.YbXirwWo5glc03c";
$PAYPAL_CANCELURL = "http://www.evergreencurling.com/LearnToCurl/openhouse/index.php";
$PAYPAL_RETURN    = "http://www.evergreencurling.com/LearnToCurl/openhouse/paypal_receipt.php";
$PAYPAL_NOTIFY_URL= "http://www.evergreencurling.com/LearnToCurl/openhouse/paypal_ipn.php";
$PAYPAL_AUTH_TX   = "";
$PAYPAL_IPN_URL   = "www.paypal.com";	// UNUSED.  Difficulties. something isn't right here. Just edit the file Sept-2009
$PAYPAL_RCPT_URL  = "www.sandbox.paypal.com";	// ipn and receipt.  Not tested if receipt and ipn can be in sandbox at same time
//$webconfirm = "http://www.evergreencurling.com/learn/openhouse/register.php?type=editopenhouse&confnumber=".$lookup; // used in paypal_ipn.php

//////// My Function to generate a confirmation number //////////////////////
function createConfirmation($group, $number) {
	srand();
	
	$a = chr(rand(65, 90));
	$b = chr(rand(65, 90));
	$c = chr(rand(65, 90));
	
	$number = $number % 10;
	
	$conf = strtoupper($group[0]). $a . $b . $c . $number;
	return $conf;
}

//////// August 2009 $10 for adult, $5 for junior, $20 for family (defined 1 or 2 adults and up to 4 juniors), $shipping = 1 include in price, 0. Don't include.
//////// UPDATE Database has prices in it, however for quick edit, I will modify this file instead. $20/person $10/junior $40/famil.y
function calculatePrice($adults, $juniors, $with_shipping) {
	$shipping = 1;
	$price = ($adults * 20) + ($juniors * 10);
	if(($price > 40) && ($adults <= 2)) {
		$price = 40; // family
	}
	if($with_shipping == 1) 
		$price += $shipping;
		
	return $price .".00";
}

// no delay -  Typically for Admin display lists
function getAvailableOpenhouses() {
	return getAvailableOpenhouses_delay(0);
}

// build in delay -- up to 800 hours+/-
// $hours (int) = number of hours ahread of time to limit the displayed events
function getAvailableOpenhouses_delay($hours) {
	// uses cast to int: "(int)$hours" -- will round down any fraction.
	$openhouses = array();
	$query = "select ID, EVENT_NAME, EVENT_DATE, EVENT_TYPE, MAX_GUESTS from learntocurl_dates where EVENT_DATE > addtime(now(),'".(int)$hours.":0:0') order by EVENT_DATE asc";
	$result = mysql_query($query);
	if(($result!=false) ) { //query was a success and returned results
		while ($row = mysql_fetch_assoc($result)) {
			$openhouses[]=$row;
		}
	}
	return $openhouses;
}


function registeredOpenhouseCount($id) {
	// Check if registration is open for given event......
	$query = "select (select max_guests from learntocurl_dates where ID = ".$id.") as MAX, (select sum(group_adults+group_juniors) from learntocurl where OPENHOUSE_ID = ".$id." and PAID_DOLLARS > 0 ) as PLAYERS";
	
	$spaceavail = mysql_query($query);
	if( !$spaceavail ) {
		if($bError)
			die("<div class='error'>Could not execute request for Open House availability: " . mysql_error(). "</div>");
		else 
			die();
	}
	$stringresult = mysql_result($spaceavail, 0, 0);
	if( !$stringresult) {
		if($bError)
			die ("<div class='error'>Cannot retrieve Open House availability: ".mysql_error()."</div>");
		else 
			die();
	}
	$max_guests = $stringresult;
	$reg_players  = mysql_result($spaceavail, 0, 1)?mysql_result($spaceavail, 0, 1):0;
	
	return $reg_players;
}


function availableOpenhouseCountNoError($id) { // displays nothing if error
	return availableOpenhouseCountErrorMinus($id, 0, "");
}


function availableOpenhouseCount($id) { // displays errors
	return availableOpenhouseCountErrorMinus($id, 1, "");
}

// $confirmation - record to exclude from count
function availableOpenhouseCountErrorMinus($id, $bError, $confirmation) {
	// Check if registration is open for given event......
	$query = "select (select max_guests from learntocurl_dates where ID = ".$id.") as MAX, (select sum(group_adults+group_juniors) from learntocurl where OPENHOUSE_ID = ".$id." and PAID_DOLLARS > 0 and CONFIRMATION != '".$confirmation."') as PLAYERS";
	//echo "<div class='error'>" .$query. "</DIV>";
	
	$spaceavail = mysql_query($query);
	if( !$spaceavail ) {
		if($bError)
			die("<div class='error'>Could not execute request for Open House availability: " . mysql_error(). "</div>");
		else 
			die();
	}
	$stringresult = mysql_result($spaceavail, 0, 0);
	if( !$stringresult) {
		if($bError)
			die ("<div class='error'>Cannot retrieve Open House availability: ".mysql_error()."</div>");
		else 
			die();
	}
	$max_guests = $stringresult;
	$reg_players  = mysql_result($spaceavail, 0, 1)?mysql_result($spaceavail, 0, 1):0;
	
	return $max_guests - $reg_players;
}


function attendedOpenhouseCountError($id, $bError) {
	// Check if registration is open for given event......
	$query = "select sum(group_adults+group_juniors) from learntocurl where OPENHOUSE_ID = ".$id." and PAID_DOLLARS > 0 and ATTENDED = 1";
	//echo "<div class='error'>" .$query. "</DIV>";
	
	$attended = mysql_query($query);
	if( !$attended ) {
		if($bError)
			die("<div class='error'>Could not execute request for Open House attended count: " . mysql_error(). "</div>");
		else 
			return -1;
	}
	$stringresult = mysql_result($attended, 0, 0);
	if( !$stringresult) {
		if($bError) {
			// die ("<div class='error'>Cannot retrieve Open House attended count (null): ".mysql_error()."</div>");
			echo "Null result returned";
			return 0;
		}
		else 
			return 0;
	}
	$attended_guests = $stringresult;
	
	return $attended_guests;
}


function connect_db($server, $user, $pass, $db_name) {

	$db_conn = mysql_connect($server, $user, $pass);
	if( !$db_conn ) {
		die ('<div class="error">Could not communicate with <span name="$server" title="'.mysql_error().'">database server</span>. Please try again later.</div>');
	}

	$db_selected = mysql_select_db($db_name, $db_conn);
	if (!$db_selected) {
	    echo '<div class="error">Could not connect to <span name="$db_name" title="'.mysql_error().'">database</span>. Please try again later.</div>';
		// die ('Can\'t use foo : ' . mysql_error());
	}

	return $db_conn;
}

// $confirmation - record to exclude from count
function setFlag($confirmation, $field, $value) {
	// update record based on Confirmation number
	$query = "update learntocurl SET $field = $value where CONFIRMATION = '".$confirmation."' ";
	//echo "<div class='error'>" .$query. "</DIV>";
	
	$result = mysql_query($query);
	if( $result )
		return "<div class='success'>Set $field to $value ($confirmation)</div>";
	else
		return "<div class='error'>Error</div>";
}


?>