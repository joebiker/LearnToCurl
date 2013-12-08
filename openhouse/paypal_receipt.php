<HTML>
<HEAD>
	<title>Learn to Curl Receipt</title>
	<link href="admin.css" rel="stylesheet" type="text/css" />
</HEAD>
<BODY>
<?php
	// read the post from PayPal system and add 'cmd'
	$req = 'cmd=_notify-synch';
	
	$tx_token = $_GET['tx'];
	$auth_token = $PAYPAL_AUTH_TX;
	$req .= "&tx=$tx_token&at=$auth_token";
	// post back to PayPal system to validate
	$header .= "POST /cgi-bin/webscr HTTP/1.0\r\n";
	$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
	$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
	$fp = fsockopen ('www.paypal.com', 80, $errno, $errstr, 30);
	// If possible, securely post back to paypal using HTTPS
	// Your PHP server will need to be SSL enabled
	// $fp = fsockopen ('ssl://www.paypal.com', 443, $errno, $errstr, 30);
	if (!$fp) {
		// HTTP ERROR
		echo ("<p><h3>HTTP ERROR</h3></p>");
	} else {
		fputs ($fp, $header . $req);
		// read the body data
		$res = '';
		$headerdone = false;
		while (!feof($fp)) {
			$line = fgets ($fp, 1024);
			if (strcmp($line, "\r\n") == 0) {
				// read the header
				$headerdone = true;
			}
			else if ($headerdone)
			{
				// header has been read. now read the contents
				$res .= $line;
			}
		}
		// parse the data
		$lines = explode("\n", $res);
		if( $DEBUG ) {
			echo ("<p><h3>Prelim output test</h3></p>");
			echo ( $res);
			echo ("<p><h3>Finished - Prelim output test</h3></p>");
		}
		$keyarray = array();
		if (strcmp ($lines[0], "SUCCESS") == 0) {
			for ($i=1; $i<count($lines);$i++){
				list($key,$val) = explode("=", $lines[$i]);
				$keyarray[urldecode($key)] = urldecode($val);
			}
			// check the payment_status is Completed
			// check that txn_id has not been previously processed
			// check that receiver_email is your Primary PayPal email
			// check that payment_amount/payment_currency are correct
			// process payment
			$firstname = $keyarray['first_name'];
			$lastname = $keyarray['last_name'];
			$itemname = $keyarray['item_name'];
			$amount = $keyarray['payment_gross']; // either
			$amount2 = $keyarray['mc_gross'];	 // seem to work
			$confirmation = trim($keyarray['invoice']);
			$groupname = $keyarray['custom'];
			
			echo " <!--  begin key arrays: \n";
			echo $keyarray['first_name'] .", first_name";
			echo $keyarray['last_name'] .", last_name";
			echo $keyarray['item_name'] .", item_name";
			echo $keyarray['payment_gross'] .", payment_gross";
			echo $keyarray['mc_gross'] .", mc_gross";
			echo $keyarray['invoice'] .", invoice";
			echo $keyarray['custom'] .", custom";
			echo "\n end key arrays --> ";
			
			
		include '../common.php';
		include '../database.php';
		$db_conn = connect_db($DB_SERVER, $DB_USER, $DB_PASS, $DB_NAME);	// from include
		$query = "select group_name, email, group_adults, group_juniors, event_date, event_name from learntocurl, learntocurl_dates where openhouse_id=id and CONFIRMATION = '".$confirmation."'";
		// DEBUG
		echo "<!-- " . $query ." -->";
		
		$result = mysql_query($query);
		if( !$result ) {
			die("<div class='error'>Could not retrieve your reservation: " . mysql_error(). "</div>");
		}
		
		$reg_details = mysql_fetch_array($result);
		
		echo "<!-- Rows affected: ". mysql_affected_rows ($db_conn);
	    printf("\ngroup: %s  emal: %s", $reg_details["group_name"], $reg_details["email"]);
	    echo "\n  -->";
		?>
		
<h3>Your Registration is Confirmed!  (Print this page!)</h3>
It's a good idea to arrive 20 minutess early to sign waivers, etc.  To speed the process you may print this receipt for each person in the group
and fillout and sign the proper information. Junior curlers must fill out <a href="">this waiver</A> and have a legal parent or gardian present to sign the form.

To get started on time, it's a good idea to arrive <b>20 minutes</b> before your scheduled time to complete on-site registration, sign waivers. 
You can save time by prin
<BR>
A receipt for your purchase has been emailed to you. You
may log into your account at <a href="https://www.paypal.com/">www.paypal.com</a> to view details of this transaction. 
<BR>
<table><TR><TD>
time: <span class='userinput'><?php echo $reg_details[4]; ?> </span><BR>
name: <span class='userinput'><?php echo $reg_details[0]; ?> </span><BR>
email: <span class='userinput'><?php echo $reg_details[1]; ?> </span>
</TD><TD>
adults: <span class='userinput'><?php echo $reg_details[2]; ?> </span><BR>
juniors: <span class='userinput'><?php echo $reg_details[3]; ?> </span><BR>
Payment: <span class='userinput'>$ <?php echo $amount; ?> </span>
</TD>
<TR><TD colspan=2>
<span class="userinput"><?php echo $confirmation; ?> </span> 
is your confirmation number.
</TD></TR></TABLE>

<div style="text-align: center; text-decoration: underline;"><strong>RELEASE OF LIABILITY AND ASSUMPTION OF RISK AGREEMENT</strong></div>
<div style="text-align: center;"><strong>READ BEFORE SIGNING and PRINT LEGIBLY</strong></div>
In consideration of being allowed to participate in any way in the Curling Club program, its related events and
activities, I ______________________________________, the undersigned, acknowledge, appreciate, and agree that:
<div style="font-size: 12px;">
1. The risk of injury from the activities involved in this program
is significant, including the potential for permanent paralysis
and death, and while particular skills, equipment, and personal
discipline may reduce this risk, the risk of serious injury does exist;
and,</div>
<div style="font-size: 11px;">
2. I KNOWINGLY AND FREELY ASSUME ALL SUCH RISKS, both known and unknown, EVEN IF ARISING FROM
THE NEGLIGENCE OF THE RELEASEES or others, and assume full responsibility for my participation; and,</div>
<div style="font-size: 10px;">3. I willingly agree to comply with the stated and customary terms and conditions for participation. If, however, I observe any
unusual significant hazard during my presence or participation, I will remove myself from participation and bring such to the
attention of the Club immediately; and,
<div style="font-size: 9px;">
4. I, for myself and on behalf of my heirs, assigns, personal
representatives and next of kin,
HEREBY RELEASE, INDEMNIFY, AND HOLD HARMLESS THE CURLING
CLUB, their officers,
officials, agents and/or employees, other
participants, sponsoring agencies, sponsors, advertisers, and, if
applicable, owners and lessors of premises used for the activity
(Releasees), WITH RESPECT TO ANY AND ALL INJURY, DISABILITY, DEATH,
or loss or damage to person or property
associated with my presence or participation, WHETHER ARISING FROM THE
NEGLIGENCE OF THE RELEASEES OR OTHERWISE, to the fullest extent permitted by law.
</div>
<p>
<strong>I HAVE READ THIS RELEASE OF LIABILITY AND ASSUMPTION OF RISK AGREEMENT, FULLY
UNDERSTAND ITS TERMS, UNDERSTAND THAT I HAVE GIVEN UP SUBSTANTIAL RIGHTS BY SIGNING
IT, AND SIGN IT FREELY AND VOLUNTARILY WITHOUT ANY INDUCEMENT.</strong>
</p>

<table border=0 cellpadding="1">
<tbody><tr><td>x___________________________<br><div style="font-size: 10px;">&nbsp;&nbsp;PARTICIPANT'S SIGNATURE</div>
    </td><td valign="top">Age:_____<BR><div style="font-size: 10px;">(if under 18)</div></td><td valign="top">Date Signed:____________</td></tr>
    <?php /*
    if( $reg_details[2] + $reg_details[3] >= 2 ) {  ?>
	    <tr><td>x___________________________<br><div style="font-size: 10px;">&nbsp;&nbsp;PARTICIPANT'S SIGNATURE </div>
	    </td><td valign="top">Age:_____<BR><div style="font-size: 10px;">(if under 18)</div></td><td valign="top">Date Signed:____________</td></tr>
	<?php }
    if( $reg_details[2] + $reg_details[3] >= 3 ) {  ?>
	    <tr><td>x___________________________<br><div style="font-size: 10px;">&nbsp;&nbsp;PARTICIPANT'S SIGNATURE </div>
	    </td><td valign="top">Age:_____<BR><div style="font-size: 10px;">(if under 18)</div></td><td valign="top">Date Signed:____________</td></tr>
	<?php }
    if( $reg_details[2] + $reg_details[3] >= 4 ) {  ?>
	    <tr><td>x___________________________<br><div style="font-size: 10px;">&nbsp;&nbsp;PARTICIPANT'S SIGNATURE </div>
	    </td><td valign="top">Age:_____<BR><div style="font-size: 10px;">(if under 18)</div></td><td valign="top">Date Signed:____________</td></tr>
	<?php }
    if( $reg_details[2] + $reg_details[3] >= 5 ) {  ?>
	    <tr><td>x___________________________<br><div style="font-size: 10px;">&nbsp;&nbsp;PARTICIPANT'S SIGNATURE </div>
	    </td><td valign="top">Age:_____<BR><div style="font-size: 10px;">(if under 18)</div></td><td valign="top">Date Signed:____________</td></tr>
	<?php }
    if( $reg_details[2] + $reg_details[3] >= 6 ) {  ?>
	    <tr><td>x___________________________<br><div style="font-size: 10px;">&nbsp;&nbsp;PARTICIPANT'S SIGNATURE </div>
	    </td><td valign="top">Age:_____<BR><div style="font-size: 10px;">(if under 18)</div></td><td valign="top">Date Signed:____________</td></tr>
	<?php } */ ?>
	
<tr><td colspan="3">Postal address_______________________________________________________</td></tr>
<tr><td valign="top">Telephone. #________________
    </td><td colspan="2">Email address:__________________________<br><div style="font-size: 10px;">
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(We will not distribute your addresses)</div></td></tr>
</tbody></table>

<!-- Google Code for Open House Registration Conversion Page -->
<script type="text/javascript">
<!--
var google_conversion_id = 1045646227;
var google_conversion_language = "en";
var google_conversion_format = "3";
var google_conversion_color = "ffffff";
var google_conversion_label = "mk_YCJeRqwEQk5fN8gM";
var google_conversion_value = 0;
if ($1.00) {
  google_conversion_value = $1.00;
}
//-->
</script>
<script type="text/javascript" src="http://www.googleadservices.com/pagead/conversion.js">
</script>
<noscript>
<div style="display:inline;">
<img height="1" width="1" style="border-style:none;" alt="" src="http://www.googleadservices.com/pagead/conversion/1045646227/?value=$1.00&amp;label=mk_YCJeRqwEQk5fN8gM&amp;guid=ON&amp;script=0"/>
</div>
</noscript>
		
		<?php
		
		}
		else if (strcmp ($lines[0], "FAIL") == 0) {
			// log for manual investigation
			// my code displays information anyway.....
			for ($i=1; $i<count($lines);$i++){
				list($key,$val) = explode("=", $lines[$i]);
				$keyarray[urldecode($key)] = urldecode($val);
			}
			// check the payment_status is Complete
			// check that txn_id has not been previously processed
			// check that receiver_email is your Primary PayPal email
			// check that payment_amount/payment_currency are correc
			// process payment
			$firstname = $keyarray['first_name'];
			$lastname = $keyarray['last_name'];
			$itemname = $keyarray['item_name'];
			$amount = $keyarray['payment_gross'];
			
			echo ("<p><h3>YOUR SUBMISSION FAILED</h3></p>");
			echo ("<b>Payment Details</b><br>\n");
			echo ("<li>Name: $firstname $lastname</li>\n");
			echo ("<li>Item: $itemname</li>\n");
			echo ("<li>Amount: $amount</li>\n");
			echo ("");
		}

	}
	fclose ($fp);
?>

</body>
</HTML>
