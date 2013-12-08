<html>
<head>
	<meta charset="utf-8">
	<meta name="author" content="Joe Petsche" />
	<meta name="DC.creator" content="Joe Petsche" />
	<title>Please Sign In</title>
	<link rel="SHORTCUT ICON" href="../favicon.ico">
	<link href="../learntocurl.css" rel="stylesheet" type="text/css" />
	<link href="http://www.hollywoodcurling.org/resources/theme/user.css?t=635163594440000000" rel="stylesheet" type="text/css" />
	<link href="http://www.hollywoodcurling.org/BuiltTheme/nature_bliss/dd7aef2a/styles/theme.less" rel="stylesheet" type="text/css" />
	<link href="http://www.hollywoodcurling.org/resources/theme/customStyles.css?t=635163591570000000" rel="stylesheet" type="text/css" />
</head>
<body>

<div id="wrapper">
<div id="main">
<?php
$waiver_msg = "Please fill out a waiver and give it to the event host. ";

include '../common.php';
include '../database.php';
include 'cEvent.php';

$_POST['openhouseid'] = $_REQUEST['view'];
$e = new Event($_POST['openhouseid']);  //	$e = new Event($_REQUEST['openhouseid']);

$e->getEventDetails();

echo "<div style='position:absolute; right:0px;z-index:-1;'>".$e->attendedOpenhouseCount()."</div>";
echo "<H1>".$e." - ".$e->getNiceDate()."</h1>";


	if (isset($_POST['type']) && $_POST['type'] == "newguest") {
		//echo "<div class='success'>Attempt to Add guest to openhouse</div>";
   		if ( strlen($_POST["groupname"]) < 1 ) {
   			echo "<div class='error'>You must specify a name.</div>";
		} // error check
		else {
			$confirmation_number = createConfirmation($_POST["groupname"], 0);
	   		$_POST['attended'] = 1;

	   		if((strcmp($_POST["groupname"], "done")==0 ||
				strcmp($_POST["groupname"], "closed")==0 ||
				strcmp($_POST["groupname"], "none")==0 )
			&& strcmp($_POST['email'], "")==0 ) {
				echo '<meta http-equiv="refresh" content="0;URL=openhouseview.php?'.$_SERVER['QUERY_STRING'].'">';
				return;
			} 

			echo "<div class=success>Thank you, ".$_POST["groupname"]. "</div>";
			if( !isset($_POST['waiver']) ) {
				echo "<div class='error'>$waiver_msg</div>";
				$_POST['waiver'] = 0;
			} else {
				$_POST['waiver'] = 1;
			}
	   		
			$_POST['adults'] = 1;
			$_POST['juniors'] = 0;
			if( isset($_POST['junior']) && strcmp($_POST['junior'], "on") == 0 ) {
				$_POST['adults'] = 0;
				$_POST['juniors'] = 1;
				echo "<div class='success'>Welcome junior curler. </div>";
			}			
			
			include '../database.php';
			$db_conn = connect_db($DB_SERVER, $DB_USER, $DB_PASS, $DB_NAME);	// from include
			$query = "insert into learntocurl (group_name, email, group_adults, group_juniors, confirmation, openhouse_id, paid_dollars, paid_type, attended, waiver, user_refer, reg_refer, create_browser, create_ip) values('".htmlspecialchars($_POST['groupname'])."', '".htmlspecialchars($_POST['email'])."', ".$_POST['adults'].", ".$_POST['juniors'].", '".$confirmation_number."', '".$_POST['openhouseid']."', ".$_POST['paid'].", '".$_POST['paid_type']."', ".$_POST['attended'].", ".$_POST['waiver'].", '".$_POST['user_refer']."', 'entered onsite', '".$_SERVER['HTTP_USER_AGENT']."', '".$_SERVER['REMOTE_ADDR']."' ) ";
			$result = mysql_query($query, $db_conn);
			if ($result) {
////			if (1) {
//				echo "<P>junior".$_POST['junior']."</P>";
//				echo "<P>email".$_POST['email']."</P>";
//				echo "<P>user_refer".$_POST['user_refer']."</P>";
				
				if( isset($_POST['paid_type']) && strcmp($_POST['paid_type'], "") != 0 ) {
					echo "<div class='success'>You have identified you will pay $".$_POST['paid']." by ".$_POST['paid_type']."</div>";
				}
//				echo "<P>paid_type".$_POST['paid_type']."</P>";
//				echo "<P>paid".$_POST['paid']."</P>";
				
				echo '<meta http-equiv="refresh" content="4;URL='.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'">';
			}
			else {
				echo "<div class='error'>Error adding guest to event</div>";
			}


			return;

		} // error checking end.
		
	}
	
?>

<h2 class="error">Please sign in below</h2>

<div id="useradd">

<?php
echo '<form action="'.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'" method="post" name="">';
?>
<p>Name: <td><input type=text name="groupname" id="groupname" size=35>
<p>Email: <td><input type=text name="email" id="email" size=35>
<p>Check the box if you are 21 or younger: <input type="checkbox" name="junior" id="junior">
<p>Select if you already signed a waiver: <input type="checkbox" name="waiver" id="waiver">
<p><input class="addbutton" type=submit value="Register">

<div id="optionaladd">
<P><B>Optional:</B> How did you hear about curling? <BR>
<input type=text name="user_refer" id="user_refer" value="" size="50"></tr>
<p><B>Optional:</B> How do you intend to pay: 
<select name="paid_type"><option value="">Not Paid</option>
<option value="cash">Cash</option>
<option value="check">Credit</option>
<option value="check">Check</option>
<option value="free">Free</option>
</select>
$<input type=text name="paid" id="paid" value="0" size="3">
<input type="hidden" name="type" value="newguest">
</div>
</form>

</div>


</div>  <!-- main -->
</div>  <!-- wrapper -->
</body>
</html>