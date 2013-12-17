<?php

session_name('Private');
session_start();

include '../common.php';
include '../database.php';
include 'cEvent.php';
include 'learntocurl.php'; // only for setFlag()

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
<title>Learn to Curl Administration - Event</title>
	<link href="../learntocurl.css" rel="stylesheet" type="text/css" />
	<link href="admin.css" rel="stylesheet" type="text/css" />
	<script src="//ajax.googleapis.com/ajax/libs/mootools/1.4.5/mootools-yui-compressed.js"></script>
	<script type="text/javascript" language="Javascript">
function toggleCheck($gid, $field, $checkb, $msgspan) {
	//make the ajax call, replace text
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


function editOpenHouse($var0,$var1,$var2,$var3,$var4,$var5) {
	// openhouse_id,  display name,  date/time,  max guests
	document.createopenhouse.type.value = "editopenhouse";
	//document.getElementById('id').value = $var0;
	document.createopenhouse.id.value = $var0;
	//document.getElementById('newname').value = $var1;
	document.createopenhouse.newname.value = $var1;
	//document.getElementById('newdate').value = $var2;
	document.createopenhouse.newdate.value = $var2;
	//document.getElementById('newmax').value = $var3;
	document.createopenhouse.newmax.value = $var3;
	document.createopenhouse.newcomments.value = $var4;
	document.createopenhouse.newtype.value = $var5;
	//document.getElementById('submitbutton').value = "Edit Open House Name/Max Guests";
	document.createopenhouse.submitbutton.value = "Edit Open House Name/Max Guests";
	// //setCheckedValue(document.getElementById('experience'), $var13);
	//setCheckedValue(document.editrecord.experience, $var13);
	//document.getElementById('shareinfo').checked = ($var16 == "1" )?true : false;
	//document.getElementById('adminuser').checked = ($var17 == "1" )?true : false;
	// //document.getElementById('attributes').innerHTML = $attributes;
	// //alert($attributes[1]);
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

<h1>Learn to Curl - Event</h1>

<?php

if( isset($_POST['type']) && (strlen($_POST['type']) > 4) ) {
	$db_conn = connect_db($DB_SERVER, $DB_USER, $DB_PASS, $DB_NAME);	// from include

	if( $_POST['type'] == "newopenhouse" ) {  // create new record
		$result = mysql_query("insert into learntocurl_dates(event_date, event_name, max_guests, comments, event_type) values('".$_POST['newdate']."', '".$_POST['newname']."', ".$_POST['newmax'].", '".$_POST['newcomments']."', '".$_POST['newtype']."')");
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
		$query = "update learntocurl_dates set max_guests = ".$_POST['newmax'].", event_name = '".$_POST['newname']."', comments = '".$_POST['newcomments']."' where id = ".$_POST['id']." and event_date = '".$_POST['newdate']."' ";
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
			echo "<div class='success'>Event removed</div>";
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
   		if( isset($_POST['attended']) && strcmp($_POST['attended'], "on") == 0 )
	   		$_POST['attended'] = 1;
	   	else 
	   		$_POST['attended'] = 0;
   		if( isset($_POST['waiver']) && strcmp($_POST['waiver'], "on") == 0 )
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
	
	else if( isset($_REQUEST['type']) && "modifyuser" == $_REQUEST['type'] ) {  // edit user registration (from openhouseuseredit.php) -- duplicated with index.php allowing user to modify their registrations
		$confirmation_number =  $_POST['confnumber'];
		$gid = $_POST['gid'];
		//echo "<div class='success'>Your Modifications are able to be updated. ".$gid. $_POST['groupname']."</div>";
		
		//include "openhouse_edit.php";
		// UPDATE sql 
		$db_conn = connect_db($DB_SERVER, $DB_USER, $DB_PASS, $DB_NAME);	// from include
	
		$query = "update learntocurl set group_name='".$_POST['groupname']."', email='".$_POST['email']."', group_adults=".$_POST['adults'].", group_juniors=".$_POST['juniors'].", openhouse_id=".$_POST['openhouseid'].", paid_dollars='".$_POST['paiddollars']."', paid_type='".$_POST['paidtype']."', edit_count=edit_count+1, edit_ip='".$_SERVER['REMOTE_ADDR']."', edit_date=now() where gid='".$gid."'";
		// update learntocurl set group_name='Joe Mod', email='joebiker@gmail.com', group_size=1, openhouse_id=1, edit_count=edit_count+1, edit_ip='127.1.1.1' where confirmation = 'JOCL1'
		
		// needs paid dollars / attended / waiver
		$update = mysql_query($query, $db_conn);
		if( $update ) {
			echo "<div class='success'>Your Modifications were recorded.</div>";
		}
		else {
			die ("<div class='error'>An error occured saving your information, please try again later. ".mysql_error()."</div>");
		}

		$attended = 0;
		$waiver   = 0;
		
		if( isset($_POST['attended']) )
			$attended = $_POST['attended'];
		if( isset($_POST['waiver']) )
			$waiver = $_POST['waiver'];
		
		// additionally: paid dollars / attended / waiver
		if ( strcmp($attended, "on") == 0 )
			$attended = "1";
		if ( strcmp($attended, "1") != 0 )
			$attended = "0";	
		if ( strcmp($waiver, "on") == 0 )
			$waiver = "1";
		if ( strcmp($waiver, "1") != 0 )
			$waiver = "0";	
			
		/*echo*/ setFlag($gid, 'attended', $attended);
		/*echo*/ setFlag($gid, 'waiver', $waiver);

	}
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

<div id="logo" style="position:absolute; left: 10; top: 2px; color: green; ">
	<A href="openhouseadmin.php"><< Back</a>
</div>

<div id="report_list" style="position:absolute; right: 10; top: 0px; color: green; z-index: 1;">
	<form name="refresh" ACTION="openhouseemailreport.php" method="POST" >
	<input type="hidden" value="Email Report">
	</form>
	
	<?php 
	$openhouseid = 0;
	if ( isset($_REQUEST['openhouseid']) )
		$openhouseid = $_REQUEST['openhouseid'];
	if ( isset($_REQUEST['view']) )
		$openhouseid = $_REQUEST['view'];
	?>
	
	<ul>
	<li><a href="openhouseemailreport.php<?php if($openhouseid > 0 ) echo "?openhouseid=".$openhouseid;  ?>">Email Report</a>
	<li><a href="openhousereferralreport.php">Referral Report</a>
	</ul>
</div>

<span id="openspace"></span>

<?php

$db_conn = connect_db($DB_SERVER, $DB_USER, $DB_PASS, $DB_NAME);	// from include

$result = false;
$event = null;
// only display the selected open house -- ALLOW view form submit
if(isset($_REQUEST['view'])) {
	$result = mysql_query("select EVENT_NAME, EVENT_DATE, MAX_GUESTS, ID, COMMENTS, PRICE_ADULT, PRICE_JUNIOR, PRICE_DISC from learntocurl_dates where id = ".$_REQUEST['view']." order by EVENT_DATE ASC", $db_conn);
	$event = new Event($_REQUEST['view']); // TODO: build this out...
}
	

if($result && isset($_REQUEST['view']) ) { //query was a success
	//echo "statement hit";
	$price_adult = 0;
	$price_junior = 0;
	$price_disc = 0;
	
	while ($row = mysql_fetch_array($result, MYSQL_BOTH)) {
		$price_adult = $row[5];
		$price_junior = $row[6];
		$price_disc = $row[7];
		$phpdate = strtotime( $row[1] );
		echo '<table class="headertable">'; 								// Build HEADERS FOR ALL OPEN HOUSES
	    echo '<tr><TH class="headertable">'. $row[0] .'&nbsp;';
	    echo "<TH class=headertable>".date('g:i A - l F j, Y',$phpdate) ."&nbsp;"
	    ."<TH class=headertable>".strval($event->registeredOpenhouseCount())." / $row[2] registered  (". $event->attendedOpenhouseCount() ." attended)</th>"
		."</TR>";
	    //	    ."<TH class=headertable>Limit $row[2] &nbsp;</TR>";
	    echo "<TR><TD colspan='3' class=headertable>"; //<div id='myPanel$row[3]'>&nbsp;";  // IE6 needs the &nbsp;
		
		echo "\n<div class='comments'>$row[4]</div>";
		echo "\n<table class='datatable'>";								// Build DATA ROWS PER-EACH OPEN HOUSE
		echo "\n<TR><TH>Conf#<TH>Name<TH>Adults<TH>Jr.<TH>Paid Type<TH>Paid Dollars<TH>Attend<TH>Waiver</TR>";
		
		$resultdata = mysql_query("select group_name, group_adults, group_juniors, email, paid_dollars, paid_type, confirmation, attended, waiver, user_refer, learn_refer, reg_refer, gid from learntocurl, learntocurl_dates where id = openhouse_id AND id = $row[3] order by EVENT_DATE ASC, attended desc, waiver desc, confirmation, group_adults desc, group_name", $db_conn);
		if($resultdata) { //query was a success
			while ($rowdata = mysql_fetch_array($resultdata, MYSQL_BOTH)) {
				echo '<form action="openhouseuseredit.php?conf='.$rowdata[6].'" method=post name="oh'.$row[3].'">';
				echo '<input type="hidden" name="type" value="editguest">';
				echo '<input type="hidden" name="openhouseid" value="'.$row[3].'">';
				echo '<input type="hidden" name="callingpage" value="'.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'">';
			    echo "<tr><td>";
				// remove BUTTOM for replacement of text confirm number JVP-Jun-2013
				// echo "<input class='editbutton' type=submit value='view' name='$rowdata[6]'>";
			    echo "</form> ";
			    echo "$rowdata[6]</td>\n";
//			    echo "<a href='openhouseuseredit.php?conf=$rowdata[6]&openhouseid=$row[3]'>$rowdata[6]</a></td>\n";
			    echo "<td><a href='openhouseuseredit.php?gid=$rowdata[12]&openhouseid=$row[3]'>$rowdata[0]</a>"; // name
//			    echo "<td>$rowdata[0]"; // name
			    echo "<td align=right>$rowdata[1] &nbsp;";
			    echo "<td align=right>$rowdata[2] &nbsp;";
			    echo "<td align=right>$rowdata[5] &nbsp;"; // type of payment
			    echo "<td align=right>$rowdata[4] &nbsp;"; // dollars
			    echo '<td align=center><input onClick="toggleCheck(\''.$rowdata[12].'\', \'attended\', this, \'openhouse'.$row[3].'\');" type="checkbox" '; if(strcmp($rowdata[7],"1")==0) echo ' checked="yes" '; echo '>';
			    echo '<td align=center><input onClick="toggleCheck(\''.$rowdata[12].'\', \'waiver\',   this, \'openhouse'.$row[3].'\');" type="checkbox" '; if(strcmp($rowdata[8],"1")==0) echo ' checked="yes" '; echo '>';
			    //echo "<td>$rowdata[6]&nbsp;"; // confirm
			    //echo "<td>$rowdata[3]&nbsp;"; // email
			    //echo '<td style="overflow: hidden;" title="'.$rowdata[10].'">'.$rowdata[9].'&nbsp;</tr>'; // Howd you hear?
			    echo "</tr>\n";
			}
		}
		echo '</table>';
		mysql_free_result($resultdata);
		
		
		echo '<form action="'.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'" method="post" name="oh'.$row[3].'">';
		echo '<input type="hidden" name="type" value="newguest">';
		echo '<input type="hidden" name="openhouseid" value="'.$row[3].'">';
		
		echo '<TABLE width="100%" border=0><TR><TD valign=top>'; // Add Attendee
		echo '<table>';
		echo '<tr><th>Add</th><th>Attendee</th></tr>';
	    echo '<tr><td>Group Name: <td><input type=text name="groupname" id="groupname">';
	    echo '<tr><td># Adults: <td><input type=text name="adults" id="adults" value="1" size="1">';
	    echo '<tr><td># Juniors: <td><input type=text name="juniors" id="juniors" value="0" size="1">';
	    echo '<tr><td>Paid: <td><input type=text name="paid" id="paid" value="0" size="3"> ';
	    echo '<select name="paid_type"><option value="">Not Paid</option>';
	    echo '<option value="cash">Cash</option>';
	    echo '<option value="check">Credit</option>';
	    echo '<option value="check">Check</option>';
	    echo '<option value="free">Free</option>';
	    echo '</select>';
	    echo '<tr><td>Attended: <td><input type="checkbox" name="attended" id="attended">';
	    echo '<tr><td>Waiver: <td><input type="checkbox" name="waiver" id="waiver">';
	    echo '<tr><td>Email: <td><input type=text name="email" id="email" size=20>';
	    echo '<tr><td>How did you hear<BR>about curling? <td valign=bottom><input type=text name="user_refer" id="user_refer" value=""></tr>';		
	    echo '<tr><td><td><input class="addbutton" type=submit value="add">';
		echo '</form>';//&nbsp;</div>'; // IE6 needs the &nbsp; 
	    echo "</td></tr></table>";
	    echo "</TD><TD valign=top>"; // Price info
	    
	    echo '<table><th>&nbsp;</th><th align=left>Price</th></tr>';
	    echo '<tr><td>Adult:&nbsp;&nbsp;</td><td>$'.$price_adult.'</td></tr>';
	    echo '<tr><td>Junior:&nbsp;&nbsp;</td><td>$'.$price_junior.'</td></tr>';
	    echo '<tr><td>Discount:&nbsp;&nbsp;</td><td>$'.$price_disc.'</td></tr>';
	    echo '<tr><td colspan=2 class="info">Discount price for up to 2 adults <BR>and 4 children, calculated at checkout.</td></tr>';
	    echo '</table>';
	    
	    echo "\n<BR><P><a href='openhouseuseradd.php?".$_SERVER['QUERY_STRING']."'>Self Service Add >></a><BR><font size='-1'>Enter <b><i>done</i></b> in the name field to return.</font></P>";
	    
	    echo '</TD></TR>';
	    
	    echo '</tr>'; //class="headertable">
		echo "</table>\n"; // class="headertable">
	    
	} // while
} // if

?>
</tr></table>
<div id=search>
Search by <b>confirmation number</b> or <b>group name</b>. <i>NOTE: The first occurrence will be returned if multiple exist.</I>
<form method="post" action="openhousesearch.php" name="search">
<input type="text" name="search" value="<?php if ( isset( $_REQUEST['search'])) echo  $_REQUEST['search']; ?>" size=20>
<input type=submit value='Search'><font size="-1"> &nbsp;&nbsp; Wildcard: %</font></form>
</div>

<P><A href="openhouseadmin.php"><< Back</a></P>


</body></html>