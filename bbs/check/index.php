<?php
	session_start();
	
	$blink = "http://REDACTED/REDACTED/update/V";
    
	define('DB_SERVER', 'localhost');
	define('DB_USERNAME', 'REDACTED');
	define('DB_PASSWORD', 'REDACTED');
	define('DB_DATABASE', 'REDACTED');

	$db = new mysqli(DB_SERVER,DB_USERNAME,DB_PASSWORD,DB_DATABASE);

	if ($db->connect_error) {
	  die("Connection failed: " . $db->connect_error);
	}
	
    for($b = 1; $b <= 4; $b++) {
    	$stmt = $db->prepare("SELECT * FROM board WHERE date = ? AND board_no = ?
    	                        ORDER BY end_time DESC
    	                        LIMIT 1");
        
        $t = time() + 3600*6;
    	$today = strval(date("Y-m-d", $t));

        $stmt->bind_param("ss", $today, $b);
        
    	$stmt->execute();
    	
    	$result = $stmt->get_result();
    	$row = mysqli_fetch_all($result, MYSQLI_ASSOC);
    	$count = mysqli_num_rows($result);
    	
    	if($count > 0) {
    	    if(strtotime($row[0]['end_time']) - $t < 0) {
    	        $url = $blink . $b . "?value=0";
                file_get_contents($url);
                echo "off";
                echo $b;
    	    }
    	    else {
    	        echo "on";
                echo $b;
    	    }
    	}
    
    }

?>