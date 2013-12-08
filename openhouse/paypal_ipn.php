<?php

function recordPayment() {
	$filename = 'ipn_log.txt';
	$handle = fopen($filename, 'a');
	
			// Check the payment_status is Completed
			if($_POST['payment_status'] == "Completed" )
				fwrite($handle, "payment_status: Complete\n");
			else {
				fwrite($handle, "payment_status BAD: ".$_POST['payment_status'].". Quitting. \n");
				return "error";
			}
			
			// Check that txn_id has not been previously processed
			// Check that receiver_email is your Primary PayPal email
			// Check that payment_amount/payment_currency are correct
			if($_POST['mc_currency'] == "USD" )
				fwrite($handle, "payment_currency: USD\n");
			else 
				fwrite($handle, "payment_currency NOT EXPECTED: ".$_POST['mc_currency']." \n");			
			
			$payment = 0.0;
			// Process payment
			if($_POST['mc_gross'] > 0.50 ) {
				fwrite($handle, "payment exists.\n");
				$payment = $_POST['mc_gross'];
			}
			else if($_POST['payment_gross'] > 0.50 ) {
				fwrite($handle, "warning! payment exists under 'payment_gross'. \n");
				$payment = $_POST['payment_gross'];
			}
			else {
				fwrite($handle, "ERR: payment NOT FOUND!: looking for 'mc_gross' or 'payment_gross'. Quitting. \n");
				// error handling here is required... the rest will not work without payment.
				return "error";
			}
			
			
			include '../common.php';
			include '../database.php';
						
			// $_POST['invoice']  // the record to update.
			// $_POST['payment_gross'] // how much total was transfered
			
			$lookup = $_POST['invoice'];
			if(strlen($lookup) != 5)
				$lookup = $_POST['custom'];
			if(strlen($lookup) != 5) {
				$output = "Confirmation code not found. (invoice:".$_POST['invoice'].",custom:".$_POST['custom'].") Quitting\n";
				fwrite($handle, $output);
				
				// ------------------ Send Email  -------------------- //
				$subject = 'Learn to Curl Registration FAILED';
				$headers = 'From: '.$EMAIL_FROM_ADMIN. "\r\n" .
				    'Reply-To: '.$EMAIL_FROM_ADMIN. "\r\n" . // If you want different address
				    'X-Mailer: PHP/' . phpversion();
				
				// The message
				$message = "An error has occured. Payment of: " . $payment . " has been processed, however our database didn't pick up the request because the Confirmation number was not present. Please view the log file (ipn_log.txt).\n\n  Error\n". $output." \n\nend";
				
				// In case any of our lines are larger than 70 characters, we should use wordwrap()
				$message = wordwrap($message, 70);
				
				fwrite($handle, "Emailing: ".$to." \nheader: ".$headers);
				fwrite($handle, "\nSubject: ". $subject ."\n".$message);
				
				// Send
				if( strlen($EMAIL_ERRORS_TO) > 0)
					mail($EMAIL_ERRORS_TO, $subject, $message, $headers);
				
				return "error";	
			}
			
			// cannot have duplicate Confromation numbers outside of a given registration.
			$db_conn = connect_db($DB_SERVER, $DB_USER, $DB_PASS, $DB_NAME);	// from include
			$query = "select count(*) from learntocurl where confirmation='".$lookup."' ";
			$result = mysql_query($query);
			$attendance = mysql_fetch_array($result);
			$total_attend = intval($attendance[0]);
			$payment_per = intval($payment)/$total_attend;
			$query = "update learntocurl set paid_type='paypal', paid_dollars=".$payment_per.", PAYPAL_TX_ID='".$_POST['txn_id']."', paid_date=now() where confirmation='".$lookup."' ";
			$update = mysql_query($query, $db_conn);
			if( $update ) {
				fwrite($handle, "DATABASE record ".$lookup." UPDATED! \n");
			}
			else {
				fwrite($handle, " Attempt on:".$lookup.". DATABASE ERROR ".mysql_error()."\n");
			}
			
			// Lookup name/email address
			$query = "select group_name, email FROM learntocurl WHERE confirmation='".$lookup."' ";
			
			$result = mysql_query($query, $db_conn);
			if( $result ) {
				// ------------------ Send Email  -------------------- //
				$to      = mysql_result($result, 0, 1);
				$subject = 'Learn to Curl confirmation number';
				$headers = 'From: '.$EMAIL_FROM_ADMIN. "\r\n" .
				    'Reply-To: '.$EMAIL_FROM_ADMIN. "\r\n" . // If you want different address
				    'X-Mailer: PHP/' . phpversion();
				
				// http://www.evergreencurling.com/learn/openhouse/register.php?type=editopenhouse&confnumber=CXNG3
				$webconfirm = $REGISTER_URL."?type=editopenhouse&confnumber=".$lookup;
				// The message
				$message = "Thank you, " . mysql_result($result, 0, 0) . ", \n\nYour payment has been received: $".$payment."\n\nYour confirmation number is: ".$lookup.".\n".$webconfirm."\n\n\n";  // additional messages can be written here in the final confirmation message.
				
				// In case any of our lines are larger than 70 characters, we should use wordwrap()
				$message = wordwrap($message, 70);
				
				fwrite($handle, "Emailing: ".$to." \nheader: ".$headers);
				fwrite($handle, "\nSubject: ". $subject ."\n".$message);
				
				// Send
				mail($to, $subject, $message, $headers);
			}
			else {
				fwrite($handle, "Err: ".mysql_error());
				
				// ------------------ Send Email  -------------------- //
				$subject = 'Learn to Curl Registration FAILED';
				$headers = 'From: '.$EMAIL_FROM_ADMIN. "\r\n" .
				    'Reply-To: '.$EMAIL_FROM_ADMIN. "\r\n" . // If you want different address
				    'X-Mailer: PHP/' . phpversion();
				
				// The message
				$message = "An error has occured: " . mysql_error();
				
				// In case any of our lines are larger than 70 characters, we should use wordwrap()
				$message = wordwrap($message, 70);
				
				fwrite($handle, "Emailing: ".$to." \nheader: ".$headers);
				fwrite($handle, "\nSubject: ". $subject ."\n".$message);
				
				// Send
				if( strlen($EMAIL_ERRORS_TO) > 0)
					mail($EMAIL_ERRORS_TO, $subject, $message, $headers);
				
			}
			
	return "success";
}

	$emailtext = '';
	$filename = 'ipn_log.txt';
	if (!$handle = fopen($filename, 'a')) {
         //echo "Cannot open file ($filename)";
         //exit;
         // DONT CARE IF FILE CANT OPEN -- CONTINUE NO MATTER WHAT
	}
	$somecontent = date("M-d H:i:s")." - - - - - - - - - - - - - - - - - - - - - - - - - - - -\n";
	if (fwrite($handle, $somecontent) === FALSE) {
        //echo "Cannot write to file ($filename)";
        //exit;
        // DONT CARE
    }
    fclose($handle);
    
	// Read the post from PayPal and add 'cmd'
	$req = 'cmd=_notify-validate';
	if(function_exists('get_magic_quotes_gpc'))
		{ $get_magic_quotes_exits = true;}
	foreach ($_POST as $key => $value)
		// Handle escape characters, which depends on setting of magic quotes
		{ if(isset($get_magic_quotes_exists) && get_magic_quotes_gpc() == 1)
			{ $value = urlencode(stripslashes($value));
		} else {
			$value = urlencode($value);
		}
		$req .= "&$key=$value";
	}
	// Post back to PayPal to validate
	$header .= "POST /cgi-bin/webscr HTTP/1.0\r\n";
	$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
	$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
	$fp = fsockopen ('www.paypal.com', 80, $errno, $errstr, 30);
	
	// Process validation from PayPal
	if (!$fp) { // HTTP ERROR
	} else {
		// NO HTTP ERROR
		fputs ($fp, $header . $req);
		while (!feof($fp)) {
			$res = fgets ($fp, 1024);
			$status_now = "";
			if (strcmp ($res, "VERIFIED") == 0) {
				
				// If 'VERIFIED', send an email of IPN variables and values to the
				// specified email address
				
				$emailtext = "Status VERIFIED\n";
				foreach ($_POST as $key => $value){
					$emailtext .= $key . " = " .$value ."\n";
				}
				recordPayment();
				
				//fwrite($handle, "VERIFIED".$emailtext);
				//mail($email, "Live-VERIFIED IPN", $emailtext . "\n\n" . $req);
				
			} else if (strcmp ($res, "INVALID") == 0) {
				// If 'INVALID', send an email. TODO: Log for manual investigation.
				
				$emailtext = "Status INVALID\n";
				foreach ($_POST as $key => $value){
					$emailtext .= $key . " = " .$value ."\n";
				}
				recordPayment();
				
				//fwrite($handle, " INVALID \n".$emailtext);
				//mail($email, "Live-INVALID IPN", $emailtext . "\n\n" . $req);
			}
			
			
			$filename = 'ipn_log.txt';
			if (!$handle = fopen($filename, 'a')) {
				//don't care
			}
			$somecontent = $emailtext;
			if (fwrite($handle, $somecontent) === FALSE) {
		        // DONT CARE
		    }
		    fclose($handle);
			
		}
		fclose ($fp);
		
	}

?>
