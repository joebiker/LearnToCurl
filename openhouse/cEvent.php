<?php

class Event
{
	// property declaration
	public $var = "at construction of object";
	public $db_auth;
	
	// User information
	public $id = 0;
	public $title = "none";
	public $datetime_stamp = 0;
	public $volunteer = 0;
	public $substitute =0;
	public $leagues = array("none");
	public $membership = array("none");
	public $attributes = array("none"); // all attributes
	public $dependents = array("none"); // opposite of headofhouse
	public $admin = 0;
	public static $colorHash = array("none"=>"#000000");
	
	private $update_to_date = 0;
	
	function getName() {
		if( strcmp($this->title, "none") == 0) 
			$this->getEventDetails(); 
		return $this->title;
	}
	
    function __construct($id = "0") {
		$this->var = "In BaseClass constructor\n";
		$this->id = $id;

		include '../database.php';
		$this->db_auth = connect_db($DB_SERVER, $DB_USER, $DB_PASS, $DB_NAME);	// from include
		$this->var = "setting db connection";
	}
	
	public function setId($myid) {
		$this->id  = $myid;
	}
	
	public function getNiceDate() {
		if( strcmp($this->title, "none") == 0) 
			$this->getEventDetails(); 
		return $nicedate = date('D jS \of F Y h:i:s A', $this->datetime_stamp);	
	}
	
	public function getEventDetails() {
	
	// identify user
	// use session variables
	if( $this->id < 1 && isset($_SESSION['user_id']) == true )  {
		$this->id = $_SESSION['user_id'];
	}
	
	if( $this->id > 0  )  {
		$query = "select EVENT_NAME, EVENT_DATE from learntocurl_dates where id = ".$this->id;
		$result = mysql_query($query, $this->db_auth);
		if (!$result) {
			echo "Database cannot be reached. ";
			echo mysql_error($this->db_auth);
			return false;
		}
		if( mysql_affected_rows() == 1 ) {
			// one result returned
			$row = mysql_fetch_row($result);
			$this->title = $row[0];
			$this->datetime_stamp = strtotime($row[1]);
/*			$this->email = $row[2];
			$this->gender = $row[3];
			//DOB 4
			$this->headofhouse = $row[5];
			$this->share_info = $row[6];
			$this->team_members = $row[7];
			$this->wheelchair = $row[8];
			$this->experience = $row[9];
			$this->position = $row[10];
			$this->needs_instruction = $row[11];
			$this->admin = $row[12];
			$this->volunteer = $row[13];
			$this->substitute = $row[14];
			
			$this->phpBB3_username = $row[15];
			$this->phpBB3_password = $row[16];
			
			$this->leagues = $this->getList("League");
			$this->leagues_current = $this->getList("League", 2012);
			
			$this->membership = $this->getList("Membership");
			$this->attributes = $this->getList("all");
*/			
			$this->update_to_date = 1;
		}
		else {
			echo "No such user.";
			return false;
		}
		
	}
	else
	{	// don't allow access
		echo "No user logged in.";
		return false;
	}
	} // end eventDetails()

	// Returns array of emails and participant names
	function getEmails() {
		$arr = array();
		$query = "select group_name, email from learntocurl where openhouse_id = $this->id ";
		$result = mysql_query($query, $this->db_auth);
		if (!$result) {
			echo "Database cannot be reached. ";
			echo mysql_error($this->db_auth);
			return false;
		} else if ( mysql_affected_rows() == 0 )
			return $arr; 
		while ($row = mysql_fetch_array($result, MYSQL_BOTH)) {
			$t_jscript = "";
			$space = strpos($row[0], " ");
			if(strlen($row[1])>0)
				$arr[] = array ("email" => $row[1], "first" => substr($row[0],0,$space), "last" => substr($row[0],$space+1, strlen($row[0])-$space));
		}
		mysql_free_result($result);
		return $arr;	
	}
	
	function __toString() {
		return $this->title;
	}
}


?>