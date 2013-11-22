<?php
	
	while ($row = mysql_fetch_array($result, MYSQL_BOTH)) {
		$current_event = new Event($row[3]);
		$sel = "";
		if( isset($_REQUEST['view']) && !strcmp($_REQUEST['view'],$row[3]) )
			$sel = "id='selected' ";
	    printf ("<tr ".$sel."><td>$row[5]<td><A href='openhouseview.php?view=$row[3]'>$row[0]</A>");
	    echo "<td>".$current_event->getNiceDate();
																		// openhouse_id,  display name,  date/time,  max guests, comments, type
		$jscript = " (<a href=\"#create_openhouse\" onclick=\"editOpenHouse('$row[3]', '$row[0]', '$row[1]', '$row[2]', '$row[4]', '$row[5]', '$row[6]', '$row[7]', '$row[8]' ); \">edit</a>) ";
		echo "<td>".$jscript."</td>\n";

		printf ("<td>".availableOpenhouseCount($row[3])." / $row[2]</td><td>". attendedOpenhouseCountError($row[3], 0) ."</td>");
		//printf ("<td>".registeredOpenhouseCount($row[3])."<td>$row[2]</td><td>". attendedOpenhouseCountError($row[3], 0) ."</td>");	// JVP 27-DEC-2012	
		
	    //printf ("<td> ".availableOpenhouseCountErrorMinus($row[3], 1 , "")." </td>");

	    if(availableOpenhouseCount($row[3]) == $row[2]) {  
		?>
	    	<form name="delete" ACTION="openhouseadmin.php" method="POST" >
			<input type="hidden" name="type" value="deleteopenhouse">
			<input type="hidden" name="id" value="<?php echo $row[3]; ?>">
			<TD><input type="submit" onClick="if(confirm('Are you sure you want to delete:\n  <?php echo $row[0]; ?>\n  <?php echo $row[1]; ?>'))return true; else return false;" value="Delete"></td>
			</td></form>

		<?php
	    }
		else {
			printf ("<td title='There are registered users, therefore this record cannot be deleted.'>Unavailable</td>");
		}
		
		printf("</TR>");
	}

?>