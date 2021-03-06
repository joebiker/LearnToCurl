<?php

session_name('Private');
session_start();

include '../common.php';
include '../database.php';
include 'cEvent.php';

// AUTH //
	$a = new Auth();
	$a->start();
	if (! $a->getAdmin()) {
		exit();
	}
//////////

?>
<HTML>
<HEAD>
<title>Learn to Curl Administration</title>
	<link rel="stylesheet" media="all" type="text/css" href="admin.css" />
	<link rel="stylesheet" media="all" type="text/css" href="http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css" />
	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/mootools/1.4.5/mootools-yui-compressed.js"></script>
	<script type="text/javascript" src="http://code.jquery.com/jquery-1.10.1.min.js"></script>
	<script type="text/javascript" src="http://code.jquery.com/ui/1.10.3/jquery-ui.min.js"></script>
	<script type="text/javascript" src="jquery-ui-timepicker-addon.js"></script>
	<script type="text/javascript" src="jquery-ui-sliderAccess.js"></script>
	<script type="text/javascript" language="Javascript">
$(function() {
	$('#newdate').datetimepicker({
		showOtherMonths: true,
		selectOtherMonths: true,
		dateFormat: "yy-mm-dd"
	});
});

function toggleCheck($gid, $field, $checkb, $msgspan) {
	if($checkb.checked==true) {
		$value = 'on';
	} 
	else {
		$value = 'off';
	}
	
	var req = new Request.HTML({
		method: 'get',
		url: 'openhousecheck.php',
		data: { 'gid' : $gid, 'field' : $field, 'value': $value },
		onRequest: function() { /* alert('Request made. Please wait...'); */ },
		update: $msgspan,
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


function editOpenHouse($var0,$var1,$var2,$var3,$var4,$var5,$var6,$var7,$var8) {
	document.createopenhouse.type.value = "editopenhouse";
	document.createopenhouse.id.value = $var0;
	document.createopenhouse.newname.value = $var1;
	document.createopenhouse.newdate.value = $var2;
	document.createopenhouse.newmax.value = $var3;
	document.createopenhouse.newcomments.value = $var4;
	document.createopenhouse.newtype.value = $var5;
	document.createopenhouse.newpriceadult.value = $var6;
	document.createopenhouse.newpricejunior.value = $var7;
	document.createopenhouse.newpricedisc.value = $var8;
	document.createopenhouse.submitbutton.value = "Edit Open House Name/Max Guests";
//	$writethis = "";
//	for (var i = 0; i < $attributes.length; i++){ 
//		$writethis += $attributes[i] + " <A HREF='javascript: void(0); ' onclick='confirmPost(\"" + $attributes[i] + "\");'>x</A>, "; 
//	}
//	document.getElementById('attributes').innerHTML = $writethis;
	
	return;	
}
</script>
</head>
<body>

<h1>Learn to Curl Administration</h1>

<?php

if( isset($_POST['type']) && (strlen($_POST['type']) > 4) ) {
	$db_conn = connect_db($DB_SERVER, $DB_USER, $DB_PASS, $DB_NAME);	// from include

	$newcomments = preg_replace("/\r\n|\r|\n/", " <br>", trim($_POST['newcomments'])); // /\r\n|\r|\n/  or  /(\r?\n){2,}/
	if( $_POST['type'] == "newopenhouse" ) {  // create new record
		$result = mysql_query("insert into learntocurl_dates(event_date, event_name, max_guests, comments, event_type, price_adult, price_junior, price_disc) values('".$_POST['newdate']."', '".$_POST['newname']."', ".$_POST['newmax'].", '".$newcomments."', upper('".$_POST['newtype']."'), '".$_POST['newpriceadult']."', '".$_POST['newpricejunior']."', '".$_POST['newpricedisc']."')");
		if ($result) {
			echo "<div class='success'>".$_POST['newdate']. " event created.</div>";
		}
		else {
			echo "<div class='error'>Event not created. </div>". mysql_error();
		}
	}
	else if ($_POST['type'] == "editopenhouse") {
		// not able to update date.
		// Consider allowing date change when no one has registered.
		$query = "update learntocurl_dates set max_guests = ".$_POST['newmax'].", event_name = '".$_POST['newname']."', comments = '".$newcomments."' where id = ".$_POST['id']." and event_date = '".$_POST['newdate']."' ";
		$result = mysql_query($query);
		$affect = mysql_affected_rows();
		if ($result && $affect > 0) {
			echo "<div class='success'>Open House Modified!</div>";
		}
		else {
			echo "<div class='error'>Open House was not modified!</div>";
			echo $query;
			echo "<BR>";
		}
		
	}
	else if ($_POST['type'] == "deleteopenhouse") {
		
		$result = mysql_query("delete from learntocurl_dates where id = ".$_POST['id']);
		if ($result) {
			echo "<div class='success'>Open House Removed</div>";
		}
		else {
			echo "<div class='error'>Event not removed</div>";
		}
	}
	else if ($_POST['type'] == "newguest") {
   		if ( strlen($_POST["groupname"]) < 2 ) {
   			echo "<div class='error'>When adding, you must specify a name.</div>";
		} // error check
		else {
		
		$confirmation_number = createConfirmation($_POST["groupname"], $_POST["adults"] + $_POST["juniors"]);
   		if( strcmp($_POST['attended'], "on") == 0 )
	   		$_POST['attended'] = 1;
	   	else 
	   		$_POST['attended'] = 0;
   		if( strcmp($_POST['waiver'], "on") == 0 )
	   		$_POST['waiver'] = 1;
	   	else 
	   		$_POST['waiver'] = 0;
   		
   		$query = "insert into learntocurl (group_name, email, group_adults, group_juniors, confirmation, openhouse_id, paid_dollars, paid_type, attended, waiver, user_refer, reg_refer, create_browser, create_ip) values('".htmlspecialchars($_POST['groupname'])."', '".htmlspecialchars($_POST['email'])."', ".$_POST['adults'].", ".$_POST['juniors'].", '".$confirmation_number."', '".$_POST['openhouseid']."', ".$_POST['paid'].", '".$_POST['paid_type']."', ".$_POST['attended'].", ".$_POST['waiver'].", '".$_POST['user_refer']."', 'manually entered user', '".$_SERVER['HTTP_USER_AGENT']."', '".$_SERVER['REMOTE_ADDR']."' ) ";
		$result = mysql_query($query, $db_conn);		
		if ($result) {
			echo "<div class='success'>Guest added to event</div>";
		}
		else {
			echo "<div class='error'>Error adding guest to event</div>";
		}
		
		} // error checking end.
	} // new guess end.
	else if( isset($_REQUEST['type']) && "deleteguest" == $_REQUEST['type'] ) {  // remove eronous users
		
		$confirmation_number = $_POST['confnumber'];
		$query = "delete from learntocurl where confirmation = '".$confirmation_number."'";
		$update = mysql_query($query, $db_conn);
		if( $update ) {
			echo "<div class='success'>Successfully removed $confirmation_number.</div>";
		}
		else {
			die ("<div class='error'>An error occured removing $confirmation_number, please try again later. ".mysql_error()."</div>");
		}
	}
}

?>

<div id="report_list" style="position:absolute; right: 10; top: 0px; color: green; z-index: 1;">
	<form name="refresh" ACTION="openhouseemailreport.php" method="POST" >
	<input type="hidden" value="Email Report">
	</form>
	
	<ul>
	<li><a href="openhouseemailreport.php">Email Report</a>
	<li><a href="openhousereferralreport.php">Referral Report</a>
	</ul>
</div>

<span id="openspace"></span>

<?php

$db_conn = connect_db($DB_SERVER, $DB_USER, $DB_PASS, $DB_NAME);	// from include

$result = false;
// only display the selected open house
if(isset($_GET['view']))
$result = mysql_query("select EVENT_NAME, EVENT_DATE, MAX_GUESTS, ID, COMMENTS, PRICE_ADULT, PRICE_JUNIOR, PRICE_DISC from learntocurl_dates where id = ".$_GET['view']." order by EVENT_DATE ASC", $db_conn);

if($result && isset($_GET['view']) ) { //query was a success
	//echo "statement hit";
	while ($row = mysql_fetch_array($result, MYSQL_BOTH)) {
		$current_event = new Event($row[3]);
		$phpdate = strtotime( $row[1] );
		echo '<table class="headertable">'; 								// Build HEADERS FOR ALL OPEN HOUSES
	    echo '<tr><TH class="headertable">'. $row[0] .'&nbsp;';
	    //echo "<TH class=headertable>".date('g:i A - l F j, Y',$phpdate) ."&nbsp;"
	    echo "<TH class=headertable>".$current_event->getNiceDate() ."&nbsp;"
	    ."<TH class=headertable>".strval(registeredOpenhouseCount($row[3]))." registered  (". attendedOpenhouseCountError($row[3], 0) ." attended)</th>"
	    ."<TH class=headertable>Limit $row[2] &nbsp;</TR>";
	    echo "<TR><TD colspan='4' class=headertable>"; //<div id='myPanel$row[3]'>&nbsp;";  // IE6 needs the &nbsp;
		
		echo $row[4];
		echo "<table class='datatable'>";								// Build DATA ROWS PER-EACH OPEN HOUSE
		echo "<TR><TH>&nbsp;<TH>Name<TH>Adults<TH>Jr.<TH>Paid Type<TH>Paid Dollars<TH>Attended<TH>Waiver<TH>Confirm<TH>Email<TH>How'd you hear?</TR>";
		
		$resultdata = mysql_query("select group_name, group_adults, group_juniors, email, paid_dollars, paid_type, confirmation, attended, waiver, user_refer, learn_refer, reg_refer, gid from learntocurl, learntocurl_dates where id = openhouse_id AND id = $row[3] order by EVENT_DATE ASC, group_name", $db_conn);
		if($resultdata) { //query was a success
			while ($rowdata = mysql_fetch_array($resultdata, MYSQL_BOTH)) {
				echo '<form action="openhouseuseredit.php?conf='.$rowdata[6].'" method=post name="oh'.$row[3].'">';
				echo '<input type="hidden" name="type" value="editguest">';
				echo '<input type="hidden" name="openhouseid" value="'.$row[3].'">';
				echo '<input type="hidden" name="callingpage" value="'.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'">';

			    echo "<tr><td><input class='editbutton' type=submit value='edit' name='$rowdata[6]'>"
			        ."</form> ";
			    echo "<td>$rowdata[0]"; // name
			    echo "<td align=right>$rowdata[1] &nbsp;";
			    echo "<td align=right>$rowdata[2] &nbsp;";
			    echo "<td align=right>$rowdata[5] &nbsp;"; // type of payment
			    echo "<td align=right>$rowdata[4] &nbsp;"; // dollars
			    echo '<td align=center><input onClick="toggleCheck(\''.$rowdata[12].'\', \'attended\', this, \'openhouse'.$row[3].'\');" type="checkbox" '; if(strcmp($rowdata[7],"1")==0) echo ' checked="yes" '; echo '>';
			    echo '<td align=center><input onClick="toggleCheck(\''.$rowdata[12].'\', \'waiver\',   this, \'openhouse'.$row[3].'\');" type="checkbox" '; if(strcmp($rowdata[8],"1")==0) echo ' checked="yes" '; echo '>';
			    echo "<td>$rowdata[6]&nbsp;"; // confirm
			    echo "<td>$rowdata[3]&nbsp;";
			  /*echo "<td><select><option value=''>Not Paid</option>"
			        ."<option value='cash'";  if($rowdata[5]=="cash")  echo "selected"; echo ">Cash</option>"
			        ."<option value='check' "; if($rowdata[5]=="check")  echo "selected"; echo ">Check</option>"
			        ."<option value='paypal' "; if($rowdata[5]=="paypal") echo "selected"; echo ">PayPal</option>"
			        ."</select>&nbsp;"; */
			    echo '<td style="overflow: hidden;" title="'.$rowdata[10].'">'.$rowdata[9].'&nbsp;</tr>';
			}
		}
		mysql_free_result($resultdata);
		echo '<form action="'.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'" method=post name="oh'.$row[3].'">';
		echo '<input type="hidden" name="type" value="newguest">';
		echo '<input type="hidden" name="openhouseid" value="'.$row[3].'">';

	    echo '<tr><td><input class="addbutton" type=submit value="add"><td><input type=text name="groupname" id="groupname"><td align=right><input type=text name="adults" id="adults" value="1" size="1">&nbsp;<td align=right><input type=text name="juniors" id="juniors" value="0" size="1">';
	    echo '<td><select name="paid_type"><option value="">Not Paid</option>';
	    echo '<option value="cash">Cash</option>';
	    echo '<option value="check">Check</option>';
	    echo '</select>';
	    echo '<td align=right><input type=text name="paid" id="paid" value="0" size="3">';
	    echo '<td align=center><input type="checkbox" name="attended" id="attended">';
	    echo '<td align=center><input type="checkbox" name="waiver" id="waiver">';
	    echo '<td>&nbsp;';
	    echo '<td><input type=text name="email" id="email" size=20>';
	    echo '<td><input type=text name="user_refer" id="user_refer" value="" size="5"></tr>';		
		echo '</form></table>';//&nbsp;</div>'; // IE6 needs the &nbsp; 
	    echo "</td></tr></table><hr>";
	}
}
?>

<div id=search>
Search by <b>confirmation number</b> or <b>group name</b>. <i>NOTE: The first occurrence will be returned if multiple exist.</I>  
<form method="post" action="openhousesearch.php" name="search">
<input type="text" name="search" value="<?php if ( isset( $_REQUEST['search'])) echo  $_REQUEST['search']; ?>" size=20>
<input type=submit value='Search'><font size="-1"> &nbsp;&nbsp; Wildcard: %</font></form>
</div>


<?php
echo "<div id='view_openhouse'>";
echo "<table class='datatable' cellpadding=5>";
echo "<TR><TH>&nbsp;<TH>Event Name<TH>Event Date<TH>Edit<TH>Available<TH>Attended<TH>Delete</TR>";

$result = mysql_query("select EVENT_NAME, EVENT_DATE, MAX_GUESTS, ID, COMMENTS, EVENT_TYPE, PRICE_ADULT, PRICE_JUNIOR, PRICE_DISC from learntocurl_dates where EVENT_DATE >= now() order by EVENT_DATE asc", $db_conn);
if($result) { //query was a success
	include "openhouse_list.php";
	echo "<tr bgcolor=white><td>-</TD><td>&nbsp;-&nbsp;</TD><td>-</TD><td>-</TD><td>-</TD><td>-</TD><td>-</TD></TR>";
}
$result = mysql_query("select EVENT_NAME, EVENT_DATE, MAX_GUESTS, ID, COMMENTS, EVENT_TYPE, PRICE_ADULT, PRICE_JUNIOR, PRICE_DISC from learntocurl_dates where EVENT_DATE < now() order by EVENT_DATE desc limit 30", $db_conn);
if($result) { //query was a success
	include "openhouse_list.php";
}

mysql_free_result($result);
echo "</table>";
echo "</div>";
?>

<div id="create_openhouse">
	<form name="createopenhouse" ACTION="openhouseadmin.php" method="POST" >
	<h3>Create Open House</h3>
	<input type="hidden" name="id" value="">
	<input type="hidden" name="type" value="newopenhouse">
	<table cellpadding=0 cellspacing=0 border=0>
	<TR><TD>Date / Time: </TD><TD><input type="text" name="newdate" id="newdate" size=40 maxlength=255></TD><TD> *(2008-08-31 14:00:00)</TD></TR>
	<TR><TD>Display Name: </TD><TD><input type="text" name="newname" size=40 maxlength=255></TD><TD> To be displayed on Website - varchar(255) (updatable)</TD></TR>
	<TR><TD>Max Guests: </TD><TD><input type="text" name="newmax" size=10 maxlength=10></TD><TD>type int  (updatable)</TD></TR>
	<TR><TD>Adult Price: </TD><TD>$<input type="text" name="newpriceadult" size=6 maxlength=10></TD><TD>*type int</TD></TR>
	<TR><TD>Junior Price: </TD><TD>$<input type="text" name="newpricejunior" size=6 maxlength=10></TD><TD>*type int</TD></TR>
	<TR><TD>Group Disc: </TD><TD>$<input type="text" name="newpricedisc" size=6 maxlength=10></TD><TD>*type int (2 adults + 4 juniors max)</TD></TR>
	<TR><TD>Type: </TD><TD><input type="text" name="newtype" size=10 maxlength=5></TD><TD> *L=Learn to Curl, P=Pickup game</TD></TR>
	<TR><TD>Comments: </TD><TD><TEXTAREA name="newcomments" rows=6 cols=30></textarea></TD><TD> (varchar(1000))  (updatable)</TD></TR>
	</table>
	<input type="submit" name="submitbutton" value="Create Open House">
	</form>
</div>

</body></html>