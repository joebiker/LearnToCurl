<HTML>
<HEAD>
	<title>Admin Learn to Curl Report</title>
	<link href="admin.css" rel="stylesheet" type="text/css" />
</HEAD>
<body>

<?php

$callingpage = 'openhouseview.php';
if( isset($_REQUEST['callingpage'])) 
	$callingpage = $_REQUEST['callingpage'];

$openhouseid = -1;
if( isset($_REQUEST['openhouseid'])) 
	$openhouseid = $_REQUEST['openhouseid']; 

// based completely on CONF number. Consider using OpenhouseID and CONF Number jvp-June-2013	
if( isset($_GET['conf']) && strlen($_GET['conf']) < 10) {
	$conf = $_GET['conf'];
	
	echo "<h1>Edit Registration</h1>";
	
	include '../common.php';
	include '../database.php';
	$db_conn = connect_db($DB_SERVER, $DB_USER, $DB_PASS, $DB_NAME);	// from include

	$resultdata = mysql_query("select group_name, group_adults, group_juniors, email, paid_dollars, paid_type, confirmation, attended, waiver, id, user_refer, reg_refer, learn_refer from learntocurl, learntocurl_dates where id = openhouse_id AND confirmation='$conf' order by EVENT_DATE ASC, group_name", $db_conn);

	if($resultdata) { //query was a success

		while ($rowdata = mysql_fetch_array($resultdata, MYSQL_BOTH)) {
			$delete = "";
			if( strcmp($rowdata[5],"paypal") != 0 ) 
				$delete = "x";
				?>
				
				<form action="<?php echo $callingpage."?view=".$openhouseid; ?>" method=post>
				<input type="hidden" name="type" value="modifyopenhouse">
				
				<table class='datatable'>
				<TR><TH>&nbsp;<TH>Name<TH>Adults<TH>Juniors<TH>Email<TH>Confirmation<TH>Paid Dollars<TH>Paid Type<TH>Attended<TH>Waiver</TR>
				
				<tr><td><?=$delete?> &nbsp; <?php echo $delete;?>
				<td><input type=text name="groupname" value="<?php echo $rowdata[0]; ?>"></td>
				<td align=right><input type=text name="adults" value='<?php echo $rowdata[1]; ?>' size=2></td>
				<td align=right><input type=text name="juniors" value='<?php echo $rowdata[2]; ?>' size=2></td>
				<td><input type=text name="email" value='<?php echo $rowdata[3]; ?>'></td>
				<td><?php echo $rowdata[6];?> <input type="hidden" name="confnumber" value="<?php echo $rowdata[6];?>">
				<td align=right><input type=text name="paiddollars" value='<?php echo $rowdata[4]; ?>' size=3></td>
				<?php
			    echo "<td><select name='paidtype'><option value=''>Not Paid</option>"
			        ."<option value='cash'";  if($rowdata[5]=="cash")  echo "selected"; echo ">Cash</option>"
			        ."<option value='check' "; if($rowdata[5]=="check")  echo "selected"; echo ">Check</option>"
			        ."<option value='paypal' "; if($rowdata[5]=="paypal") echo "selected"; echo ">PayPal</option>"
			        ."</select></td>";
			    echo '<td align=center><input type="checkbox" name="attended" '; if(strcmp($rowdata[7],"1")==0) echo ' checked="yes" '; echo '>';
			    echo '<td align=center><input type="checkbox" name="waiver"   '; if(strcmp($rowdata[8],"1")==0) echo ' checked="yes" '; echo '></tr>';
			    // allow move to another open house if not attended.

			    echo "<TR><TD>&nbsp;<TD colspan=4>Move User to: ";
				echo "<select name=openhouseid>";
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
				<TD>How'd You Hear?
				<TD colspan=4><?php echo $rowdata[10]; ?>&nbsp;</td></TR>
				<tr><Td>&nbsp;</td><td>Reg refer</td><td colspan=8><?php echo $rowdata[11]; ?>&nbsp;</TR>
				<tr><Td>&nbsp;</td><td>/Learn/ refer</td><td colspan=8><?php echo $rowdata[12]; ?>&nbsp;</TR>
			    </table>
			    <P>*If the above <i>refer</i> is blank, they probably typed the ECC address into their web browser.</P>
		    
		    <input class="addbutton"  type="submit" value="Save">
		    </form>
		    
		    <?php 
		    if( strcmp($delete, "x") != 0 ) 
		    	echo "Cannot delete. Payment exists.<BR>";
		    else {  ?>
			<form action="<?php echo $callingpage; ?>" method="post" name="delete">
		    <input type=hidden name="type" value="deleteguest">
		    <input type=hidden name="confnumber" value="<?php echo $rowdata[6];?>">
		    <input type=hidden name="view" value="<?php echo $rowdata[9];?>">
			
			<input class="savebutton" onClick="if(confirm('Are you sure you want to delete this user?\nConfirmation: <?php echo $rowdata[6];?>'))return true; else return false;" type=submit value="Delete">
		    </form>
		    <?php } ?>
		     
		    <a href="<?php echo $callingpage.'?view='.$rowdata[9]; ?>">< go back</a> 
		    
		    
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
