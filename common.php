<?php

$DEBUG            = 0;
$ERROR_MSG        = "";
$L2C_PASS         = "";
$EMAIL_FROM_ADMIN = ""; // System sends email from this address
$EMAIL_ERRORS_TO  = ""; // If error occurs send email here. Seperate by commas if you want multiple
$PP_FORM_POST     = "";
$PAYPAL_BUSINESS  = "";
$PAYPAL_USER      = "";
$PAYPAL_PWD       = "";
$PAYPAL_SIGNATURE = "";
$PAYPAL_CANCELURL = "";
$PAYPAL_RETURN    = "";
$PAYPAL_NOTIFY_URL= "";
$PAYPAL_AUTH_TX   = "";
$PAYPAL_IPN_URL   = "";	// UNUSED.  Difficulties. something isn't right here. Just edit the file Sept-2009
$PAYPAL_RCPT_URL  = "";	// ipn and receipt.  Not tested if receipt and ipn can be in sandbox at same time


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

// August 2009 $10 for adult, $5 for junior, $20 for family (defined 1 or 2 adults and up to 4 juniors), $with_shipping will be added to price if included
function calculatePrice($id, $adults, $juniors, $with_shipping=0) {
	$p_adult    = 20; // defaults
	$p_junior   = 10;
	$p_discount = 40;
	$final_price = 0;
	$disc_applied = 0;

	// get prices from Database
	$query = "select PRICE_ADULT, PRICE_JUNIOR, PRICE_DISC from learntocurl_dates where ID = ".$id;
	$result = mysql_query($query);
	if( $result==FALSE )
		die('<div class="error">Could not execute <span name="mysql_error" title="'.mysql_error().'">request for price information</span>. </div>');
	$p_adult    = mysql_result($result, 0, 0);
	$p_junior   = mysql_result($result, 0, 1);
	$p_discount = mysql_result($result, 0, 2);
	
	$remain_a = $adults;
	$remain_j = $juniors;
	
	if( $p_discount > 0 ) {
		while ($remain_a >= 1 && $remain_j >= 1 && !($remain_a == 1 && $remain_j == 1) ) {
			// start discount
			$disc_applied ++;
			$remain_a = $remain_a - 2;
			$remain_j = $remain_j - 4;
			$final_price += $p_discount;
		}
	} // apply discounts
	
	while ( $remain_a > 0) {
		$remain_a--;
		$final_price += $p_adult;
	}
	while ( $remain_j > 0) {
		$remain_j--;
		$final_price += $p_junior;
	}
	
	if($with_shipping > 0) 
		$final_price += $with_shipping;
	
	return $final_price;
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
	$query = "select coalesce(sum(group_adults+group_juniors),0) as REG from learntocurl where OPENHOUSE_ID = ".$id." and (PAID_DOLLARS > 0 or PAID_TYPE = 'free')";
	
	$spaceavail = mysql_query($query);
	if( $spaceavail==FALSE ) {
		if($bError)
			die('<div class="error">Could not execute <span name="mysql_error" title="'.mysql_error().'">request for event registration</span>. </div>');
		else 
			die();
	}
	$reg_players = mysql_result($spaceavail, 0, 0);	
	return $reg_players;
}

// return could be negative if oversold.
function availableOpenhouseCountNoError($id) { // displays nothing if error
	return availableOpenhouseCountErrorMinus($id, 0, "");
}
function availableOpenhouseCount($id) { // displays errors
	return availableOpenhouseCountErrorMinus($id, 1, "");
}
// $confirmation - record to exclude from count // unused 
function availableOpenhouseCountErrorMinus($id, $bError, $confirmation) {
	// Check if registration is open for given event......
	$availableSpace = 0;
	$where_confirmation = "";
	if (strlen ( $confirmation) > 0 ) 
		$where_confirmation = "and CONFIRMATION != '".$confirmation."'";
	
	$query = "select (select coalesce(sum(group_adults+group_juniors),0) from learntocurl where OPENHOUSE_ID = ".$id." and (PAID_DOLLARS > 0 or PAID_TYPE = 'free') ".$where_confirmation.") as REG, (select max_guests from learntocurl_dates where ID = ".$id.") as MAX ";
	//echo "<div class='error'>" .$query. "</DIV>";
	
	$spaceavail = mysql_query($query);
	if( $spaceavail == FALSE) {
		if($bError)
			die('<div class="error">Could not execute <span name="mysql_error" title="'. mysql_error().'">request for event availability(-)</span>. </div>');
		else 
			die();
	} else {
		$reg_players = mysql_result($spaceavail, 0, 0);
		$max_guests = mysql_result($spaceavail, 0, 1);
		$availableSpace = $max_guests - $reg_players;
	}
	return $availableSpace;
}


function attendedOpenhouseCountError($id, $bError) {
	// Check if registration is open for given event......
	$query = "select sum(group_adults+group_juniors) from learntocurl where OPENHOUSE_ID = ".$id." and (PAID_DOLLARS > 0 or PAID_TYPE = 'free') and ATTENDED = 1";
	//echo "<div class='error'>" .$query. "</DIV>";
	
	$attended = mysql_query($query);
	if( !$attended ) {
		if($bError)
			die("<div class='error'>Could not execute request for event attended count: " . mysql_error(). "</div>");
		else 
			return -1;
	}
	$stringresult = mysql_result($attended, 0, 0);
	if( !$stringresult) {
		if($bError) {
			// die ("<div class='error'>Cannot retrieve event attended count (null): ".mysql_error()."</div>");
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


class Auth
{
	// property declaration
	public $var = "a default value";
	public $db_auth;
	public $current_user;
	
    function __construct() {
		$this->var = "In BaseClass constructor\n";
	}
	
	public function start() {
		if ( isset($_POST['pwd']) ) {
			// set session variable
			$_SESSION['pwd'] = $_POST['pwd'];
		}
		if ( !isset($_SESSION['pwd']) ) {
			// Show password prompt
			$this->showLogin();
		}
	}
	
	public function getAdmin() {
		global $L2C_PASS;
		// use session variables
		if( isset($_SESSION['pwd']) == true )  {
			if( strcmp($_SESSION['pwd'], $L2C_PASS)==0 ) {
				return true;
			}
			else {	// Incorrect password
				$this->showLogin();
				return false;
			}
		} 
		else
		{	// don't allow access
			return false;
		}
	}
	
	public function showLogin() {
		echo "<HTML><form method='post'>\n";
		echo "<input type='password' name='pwd' size='12'>\n";
		echo "<input type='submit'></form>\n</HTML>";
	}

}

?>