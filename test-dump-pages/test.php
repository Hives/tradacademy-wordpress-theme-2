<?php 

$IDs=array(2542,2541,2540,2539,2538,2537,2434,2428,2423);

foreach ($IDs as $ID) {
	$date = get_end_date_from_course_ID($ID);
	echo $ID . ": " . date("M d Y H:i:s", $date) . "<br>";
	echo "---<br>";
}

?>