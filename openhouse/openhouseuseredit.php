<?php
header('Cache-Control: no-cache, no-store, must-revalidate'); // HTTP 1.1.
header('Pragma: no-cache'); // HTTP 1.0.
header('Expires: 0'); // Proxies.

session_name('Private');
session_start();

include '../common.php';
include "../database.php";

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
	<title>Learn to Curl Administration - User</title>
	<link href="../learntocurl.css" rel="stylesheet" type="text/css" />
	<link href="admin.css" rel="stylesheet" type="text/css" />
</HEAD>
<body>

<h1>Learn to Curl - User</h1>
<?php

$callingpage = 'openhouseview.php';
if( isset($_REQUEST['callingpage'])) 
	$callingpage = $_REQUEST['callingpage'];

$openhouseid = -1;
if( isset($_REQUEST['openhouseid'])) 
	$openhouseid = $_REQUEST['openhouseid']; 
?>


<div id="logo" style="position:absolute; left: 10; top: 2px; color: green; ">
	<a href="<?php echo $callingpage.'?view='.$openhouseid; ?>"><< Back</a> 
</div>


<?php
// based completely on CONF number. Consider using OpenhouseID and CONF Number jvp-June-2013	
if( isset($_GET['gid']) && strlen($_GET['gid']) < 10) {
	$gid = $_GET['gid'];
	
	$db_conn = connect_db($DB_SERVER, $DB_USER, $DB_PASS, $DB_NAME);	// from include
	$resultdata = mysql_query("select group_name, group_adults, group_juniors, email, paid_dollars, paid_type, confirmation, attended, waiver, id, user_refer, learn_refer, reg_refer, learntocurl.create_date, paid_date from learntocurl, learntocurl_dates where id = openhouse_id AND gid='$gid' order by EVENT_DATE ASC, group_name", $db_conn);
	if($resultdata) { //query was a success

		while ($rowdata = mysql_fetch_array($resultdata, MYSQL_BOTH)) {
			$delete = "";
			if( strcmp($rowdata[5],"paypal") != 0 ) 
				$delete = "x";
				?>
				<form action="<?php echo $callingpage."?view=".$openhouseid; ?>" method=post>
				<input type="hidden" name="type" value="modifyuser">
				<input type="hidden" name="gid" value="<?php echo $gid; ?>">
				<table class='usertable'>
				<TR><TH>Confirmation
					<td title='<?php echo $gid; ?>'><?php echo $rowdata[6];?> <input type="hidden" name="confnumber" value="<?php echo $rowdata[6];?>">
				<TR><TH>Name
					<td><input type=text name="groupname" value="<?php echo $rowdata[0]; ?>"></td>
				<TR><TH>Email
					<td><input type=text size=40 name="email" value='<?php echo $rowdata[3]; ?>'></td>
				<TR><TH>Adults
					<td align=left><input type=text name="adults" value='<?php echo $rowdata[1]; ?>' size=2></td>
				<TR><TH>Juniors
					<td align=left><input type=text name="juniors" value='<?php echo $rowdata[2]; ?>' size=2></td>
				<TR><TH>Paid Dollars
					<td align=left><input type=text name="paiddollars" value='<?php echo $rowdata[4]; ?>' size=3></td>
				<TR><TH>Paid Type
					<?php
			    echo "<td><select name='paidtype'><option value=''>Not Paid</option>"
			        ."<option value='cash'";  if($rowdata[5]=="cash")  echo "selected"; echo ">Cash</option>"
			        ."<option value='credit' "; if($rowdata[5]=="credit")  echo "selected"; echo ">Credit</option>"
			        ."<option value='check' "; if($rowdata[5]=="check")  echo "selected"; echo ">Check</option>"
			        ."<option value='promotion' "; if($rowdata[5]=="promotion") echo "selected"; echo ">Promotion</option>"
			        ."<option value='paypal' "; if($rowdata[5]=="paypal") echo "selected"; echo ">PayPal</option>"
			        ."<option value='free' "; if($rowdata[5]=="free") echo "selected"; echo ">Free</option>"
			        ."</select></td>";
				echo "<TR><TH>Attended";
					echo '<td> &nbsp; <input type="checkbox" name="attended" '; if(strcmp($rowdata[7],"1")==0) echo ' checked="yes" '; echo '>';
				echo "<TR><TH>Waiver";
					echo '<td> &nbsp; <input type="checkbox" name="waiver"   '; if(strcmp($rowdata[8],"1")==0) echo ' checked="yes" '; echo '></tr>';
			    // allow move to another open house if not attended.

			    echo "<TR><TH>Move User to</TH>";
				echo "<TD><select name=openhouseid>";
				$result = mysql_query("select EVENT_NAME, EVENT_DATE, MAX_GUESTS, ID from learntocurl_dates order by EVENT_DATE DESC", $db_conn);
				if($result) { //query was a success
					while ($row = mysql_fetch_array($result, MYSQL_BOTH)) {
						$selected = "";
						if ($rowdata[9] == $row[3])
							$selected = "selected";
					    echo "<option value='$row[3]' $selected>$row[1] - $row[0]";
					}
				}
				?>
				</select>
				<TR><TH>How'd You Hear?
				<TD><input type=text name=referral size=40 value="<?php echo $rowdata[10]; ?>"></td></TR>
			    <TR><TH></th><td><input class="addbutton"  type="submit" value="Save user record"></td></tr>
			    <TR><TH> </th><td>&nbsp;</td></tr>
			    
				<tr><th title='If the referral is blank, they probably typed the address into their web browser.'>Home Page Referral</th>
				<td><?php echo $rowdata[11]; ?>&nbsp;</TR>
				<tr><th title='If the referral is blank, they probably typed the address into their web browser.'>Registration Referral</th>
				<td><?php echo $rowdata[12]; ?>&nbsp;</TR>
				<tr><th>Record Created On</th>
				<td><?php echo $rowdata[13]; ?>&nbsp;</TR>
				<tr><th>Payment On</th>
				<td><?php echo $rowdata[14]; ?>&nbsp;</TR>
		    
		    </form>
			
				<tr><th> </th>
				<td><?php 
				if( strcmp($delete, "x") != 0 ) 
				echo "<P class='savebutton'>Cannot delete. Payment exists.</P><BR>";
				else {  ?>
				<form action="<?php echo $callingpage; ?>" method="post" name="delete">
				<input type=hidden name="type" value="deleteguest">
				<input type=hidden name="gid" value="<?php echo $gid;?>">
				<input type=hidden name="confnumber" value="<?php echo $rowdata[6];?>">
				<input type=hidden name="view" value="<?php echo $rowdata[9];?>">
				
				<input class="savebutton" onClick="if(confirm('Are you sure you want to delete this user?\nConfirmation: <?php echo $rowdata[6];?>'))return true; else return false;" type=submit value="Delete user record">
				</form>
				<?php } ?>
				</td></tr>
			</table>
		     
		    <a href="<?php echo $callingpage.'?view='.$openhouseid; ?>"><< Back</a> 
		    
		    
		    <?php
		    
	    }
	    
    }
    else {
	    echo mysql_error();
    }
}
else
{
echo '<div class="error">Please select a record from the open house admin page.</div>';
}

?>

</body>
</html>
