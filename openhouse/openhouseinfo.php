<?php
include 'cEvent.php';

if( isset($_GET['id']) && (strlen($_GET['id']) < 10) ) { // no sql injection please
// Display a text message about the registration for the given ID
	
	$myEvent = new Event($_GET['id']);

	if( isset($_GET['name']) ) { 
		// get the name of the event
		echo $myEvent->getName();
	}
	else if( isset($_GET['adults']) ) { 
		// get the price of the event
		$num_adults = $_GET['adults'];
		$num_juniors = 0;
		if ( isset($_GET['juniors']) )
			$num_juniors = $_GET['juniors'];
		
		echo $myEvent->getPrice($num_adults, $num_juniors);
	}
	else {
		// get the number of spaces that remain
		$openspace = $myEvent->availableOpenhouseCount();
	
		if ($openspace == 1 ) {
			echo "Only one space is open";
		}
		else if ($openspace <= 0 ) {
			echo "Selected Open House is currently full.";
		}
		else {
			echo "There are ";
			echo $openspace;
			echo " spaces remaining.";
		}
	}

}  else {
	echo "Nope. ";
	echo var_dump($_REQUEST);

}

?>