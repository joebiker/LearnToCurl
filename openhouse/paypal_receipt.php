<HTML>
<HEAD>
	<title>Learn to Curl Receipt</title>
	<link href="admin.css" rel="stylesheet" type="text/css" />
</HEAD>
<BODY>
<div id="wrapper">
<div id="main">
<?php
	// read the post from PayPal system and add 'cmd'
	$req = 'cmd=_notify-synch';
	
	$tx_token = $_GET['tx'];
	$auth_token = $PAYPAL_AUTH_TX;
	$req .= "&tx=$tx_token&at=$auth_token";
	// post back to PayPal system to validate
	$header = "POST /cgi-bin/webscr HTTP/1.0\r\n";
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
		if( FALSE ) {
			echo ("<p><h3>Prelim output test</h3></p>");
			echo ( $res);
			echo ("<p><h3>Finished - Prelim output test</h3></p>");
		}
		$keyarray = array();
		if (strcmp ($lines[0], "SUCCESS") == 0) {
			for ($i=1; $i<count($lines);$i++){
				$arr_line = explode("=", $lines[$i]);
				if(isset($arr_line[1]))
					$keyarray[$arr_line[0]] = $arr_line[1];
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
			$shipping = $keyarray['shipping'];
			$confirmation = trim($keyarray['invoice']);
			$groupname = $keyarray['custom'];
			
			echo " <!--  begin key arrays: \n";
			echo $keyarray['first_name'] .":first_name, ";
			echo $keyarray['last_name'] .":last_name, ";
			echo $keyarray['item_name'] .":item_name, ";
			echo $keyarray['payment_gross'] .":payment_gross, ";
			echo $keyarray['mc_gross'] .":mc_gross, ";
			echo $keyarray['shipping'] .":shipping, ";
			echo $keyarray['invoice'] .":invoice, ";
			echo $keyarray['custom'] .":custom";
			echo "\n end key arrays --> ";
			
			
			include '../common.php';
			include '../database.php';
			$db_conn = connect_db($DB_SERVER, $DB_USER, $DB_PASS, $DB_NAME);	// from include
			$query = "select group_name, email, sum(group_adults), sum(group_juniors), event_date, event_name from learntocurl, learntocurl_dates where openhouse_id=id and CONFIRMATION = '".$confirmation."'";
			// DEBUG
			echo "<!-- " . $query ." -->";
			
			$result = mysql_query($query);
			if( !$result ) {
				die("<div class='error'>Could not retrieve your reservation: " . mysql_error(). "</div>");
			}
			
			$reg_details = mysql_fetch_array($result);
			
			echo "<!-- Rows affected: ". mysql_affected_rows ($db_conn);
		    printf("\ngroup: %s  email: %s", $reg_details["group_name"], $reg_details["email"]);
		    echo "\n  -->";
?>
		
<h3>Your Registration is Confirmed - <?php echo $confirmation; ?>  (Print this page!)</h3>
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
<BR>
<?php 
// Display users waiver document

echo "<font size='-1'>";
$waiver = "waiver_content.txt";
if (file_exists($waiver)) {
	readfile($waiver);
}
echo "</font>";
?>
<strong>I HAVE READ THIS RELEASE OF LIABILITY AND ASSUMPTION OF RISK AGREEMENT, FULLY
UNDERSTAND ITS TERMS, UNDERSTAND THAT I HAVE GIVEN UP SUBSTANTIAL RIGHTS BY SIGNING
IT, AND SIGN IT FREELY AND VOLUNTARILY WITHOUT ANY INDUCEMENT.</strong>
<table border=0 cellpadding="1"><tbody>
<?php 

for ( $i=0;$i<($reg_details[2] + $reg_details[3]); $i++ ) {
	echo "<tr><td>___________________<br><div style='font-size: 10px;'>&nbsp;&nbsp;PRINT NAME </div>";
	echo "</td><td>x_____________________<br><div style='font-size: 10px;'>&nbsp;&nbsp;PARTICIPANT'S SIGNATURE </div>";
	echo "</td><td valign='top'>Age:_____<br><div style='font-size: 10px;'>(if under 18)</div></td><td valign='top'>Date Signed:____________</td></tr>";
}
?>
	
<tr><td colspan="4">Postal address_______________________________________________________</td></tr>
<tr><td valign="top">Telephone. #________________
    </td><td colspan="3">Email address:__________________________<br><div style="font-size: 10px;">
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(We will not distribute your addresses)</div></td></tr>
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
</div>
</div>

</body>
</HTML>
