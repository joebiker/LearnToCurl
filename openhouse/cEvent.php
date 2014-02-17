<?php

class Event
{
	// property declaration
	public $var = "at construction of object";
	public $db_auth;
	
	// User information
	public $id = 0;
	public $title = "none"; // EVENT_TITLE
	public $datetime_stamp = 0; //EVENT_DATE
	public $type = ""; // L=Learn to Curl, O=Open House, P=Pickup Game // EVENT_TYPE
	public $comments = "";
	public $max_guests =0;
	public $price_adult =0;
	public $price_junior =0;
	public $price_disc =0; // 4 adults and 4 children
	public $leagues = array("none");
	public $membership = array("none");
	public $attributes = array("none"); // all attributes
	public $dependents = array("none"); // opposite of headofhouse
	public $create_date = 0;
	public $create_mid = 0; // user who created the event
	
	private $update_to_date = 0;
	
	function getName() {
		if( strcmp($this->title, "none") == 0) 
			$this->getEventDetails(); 
		return $this->title;
	}
	
    function __construct($id = "0") {
		$this->var = "In BaseClass constructor\n";
		$this->id = $id;
		
		$this->var = "setting db connection";
// Causing problems finding "../database.php" (needs to be in local dir of every call).
		include 'database.php';
		$this->db_auth = new mysqli($DB_SERVER, $DB_USER, $DB_PASS, $DB_NAME);
		
		$this->var = $this->db_auth->host_info;
		if( $this->id > 0 )
			$this->getEventDetails();
	}
	
	public function setId($myid) {
		$this->id  = $myid;
	}
	
	public function getEventDetails() {
		
		if( $this->id > 0  )  {
			$query = "select EVENT_NAME, EVENT_DATE, EVENT_TYPE, MAX_GUESTS, PRICE_ADULT, PRICE_JUNIOR, PRICE_DISC, COMMENTS, CREATE_DATE, CREATE_MID from learntocurl_dates where id = ".$this->id;
			$result = mysqli_query($this->db_auth, $query); // , $this->db_auth);
			if (!$result) {
				echo "Database cannot be reached. ";
				echo mysqli_error(); // $this->db_auth);
				return false;
			}
			if( mysqli_affected_rows($this->db_auth) == 1 ) {
				// one result returned
				$row = mysqli_fetch_row($result);
				$this->title = $row[0];
				$this->datetime_stamp = strtotime($row[1]);
				$this->type = $row[2];
				$this->max_guests = $row[3];
				$this->price_adult = $row[4];
				$this->price_junior = $row[5];
				$this->price_disc = $row[6];
				$this->comments = $row[7];
				$this->create_date = $row[8];
				$this->create_mid = $row[9];
				
				$this->update_to_date = 1;
			}
			else {
				echo "No such event.";
				return false;
			}			
		}
	} // end eventDetails()
	
	function getPrice($adults, $juniors, $with_shipping=0) {
		$final_price = 0;
		$disc_applied = 0;
	
//		$p_adult    = mysql_result($result, 0, 0);
//		$p_junior   = mysql_result($result, 0, 1);
//		$p_discount = mysql_result($result, 0, 2);
		
		$remain_a = $adults;
		$remain_j = $juniors;
		
		if( $this->price_disc > 0 ) {
			while ($remain_a >= 1 && $remain_j >= 1 && !($remain_a == 1 && $remain_j == 1) ) {
				// start discount
				$disc_applied ++;
				$remain_a = $remain_a - 2;
				$remain_j = $remain_j - 4;
				$final_price += $this->price_disc;
			}
		} // apply discounts
		
		while ( $remain_a > 0) {
			$remain_a--;
			$final_price += $this->price_adult;
		}
		while ( $remain_j > 0) {
			$remain_j--;
			$final_price += $this->price_junior;
		}
		
		if($with_shipping > 0) 
			$final_price += $with_shipping;
		
		return $final_price;
	}
	
	public function getNiceDate() {
		if( $this->update_to_date != 1)
			$this->getEventDetails();
		return $nicedate = date('l jS \of F g:i A', $this->datetime_stamp);	
	}
	
	public function getNiceShortDate() {
		if( $this->update_to_date != 1)
			$this->getEventDetails();
		return $nicedate = date('D jS \of M g:i A', $this->datetime_stamp);	
	}
	
	public function availableOpenhouseCount() {
		if( $this->update_to_date != 1)
			$this->getEventDetails();
		
		return $this->max_guests - $this->registeredOpenhouseCount();
	}
	
	// Find out how many people are registered
	public function registeredOpenhouseCount() {
		if( $this->update_to_date != 1)
			$this->getEventDetails();
		
		// TESTING: count people with "free" payment type
		$query = "select coalesce(sum(group_adults+group_juniors),0) as REG from learntocurl where OPENHOUSE_ID = ".$this->id." and (PAID_DOLLARS > 0 or PAID_TYPE = 'free')";
		$spaceavail = mysqli_query($this->db_auth, $query);
		if( $spaceavail==FALSE ) {
			return -988; //error condition
		}
		$row = $spaceavail->fetch_assoc();
		//$reg_players = mysql_result($spaceavail, 0, 0);	
		$reg_players = $row['REG'];
		return $reg_players;
	}
	
	// Find out how many have attended, (regardless or payment)
	public function attendedOpenhouseCount() {
		if( $this->update_to_date != 1)
			$this->getEventDetails();
		
		$query = "select sum(group_adults+group_juniors) as ATT from learntocurl where OPENHOUSE_ID = ".$this->id." and ATTENDED = 1";
		$spaceavail = mysqli_query($this->db_auth, $query);
		if( $spaceavail==FALSE ) {
			return -989; //error condition
		}
		$row = $spaceavail->fetch_assoc();
		//$stringresult = mysql_result($spaceavail, 0, 0);
		$stringresult = $row['ATT'];
		if (strcmp($stringresult,"")==0)
			$stringresult = "0";
		return $stringresult;
	}

	// Returns array of emails and participant names
	function getEmails() {
		$arr = array();
		$query = "select group_name, email from learntocurl where openhouse_id = $this->id ";
		$result = mysqli_query($this->db_auth, $query); //, $this->db_auth);
		if (!$result) {
			echo "Database cannot be reached. ";
			echo mysqli_error($this->db_auth); // $this->db_auth);
			return false;
		}  else if ( $this->db_auth->affected_rows == 0 )
			return $arr;
		$result->data_seek(0);
		while ($row = $result->fetch_assoc()) {
			$t_jscript = "";
			$space = strpos($row['group_name'], " ");
			if(strlen($row['email'])>0)
				$arr[] = array ("email" => $row['email'], "first" => substr($row['group_name'],0,$space), "last" => substr($row['group_name'],$space+1, strlen($row['group_name'])-$space));
		}
/*		while ($row = mysql_fetch_array($result, MYSQL_BOTH)) {
			$t_jscript = "";
			$space = strpos($row[0], " ");
			if(strlen($row[1])>0)
				$arr[] = array ("email" => $row[1], "first" => substr($row[0],0,$space), "last" => substr($row[0],$space+1, strlen($row[0])-$space));
		}
*/		mysqli_free_result($result);
		return $arr;	
	}
	
	function __toString() {
		return $this->title;
	}
	
	// build in delay -- up to 800 hours+/-
	// $hours (int) = number of hours ahread of time to limit the displayed events
	function getAvailableOpenhouses_delay($hours=0, $type="") {
		// uses cast to int: "(int)$hours" -- will round down any fraction.
		$openhouses = array();
		$reduceByType = "";
		if ($type > "") $reduceByType = " EVENT_TYPE in ($type) and ";

		$query = "select ID, EVENT_NAME, EVENT_DATE, EVENT_TYPE, MAX_GUESTS from learntocurl_dates where ".$reduceByType." EVENT_DATE > addtime(now(),'".(int)$hours.":0:0') order by EVENT_DATE asc";
		$res = $this->db_auth->query($query);
		$res->data_seek(0);
		while ($row = $res->fetch_assoc()) {
			$openhouses[]=$row;
		}
		
		return $openhouses;
	}


}


?>