<?php
	session_start();
	
	$blink = "http://REDACTED/REDACTED/update/V";
	
	if($_SESSION['membertype'] != 2) {
		header("location: localhost/bbs/dashboard/");
	}
	if(!isset($_SESSION['loginstatus'])) {
		header("location: localhost/bbs/login/");
	}
	
	if($_SESSION['loginstatus'] == 0) {
		header("location: localhost/bbs/login/");
	}

	define('DB_SERVER', 'localhost');
	define('DB_USERNAME', 'REDACTED');
	define('DB_PASSWORD', 'REDACTED');
	define('DB_DATABASE', 'REDACTED');

	$db = new mysqli(DB_SERVER,DB_USERNAME,DB_PASSWORD,DB_DATABASE);

	if ($db->connect_error) {
	  die("Connection failed: " . $db->connect_error);
	}

	if($_SERVER['REQUEST_METHOD'] === 'GET') {
		if(isset($_GET['cphone']) && isset($_GET['bno']) && isset($_GET['stime']) && isset($_GET['etime'])) {

			$stmt1 = $db->prepare("SELECT * FROM board WHERE date = ? AND board_no = ?");

			$today = strval(date("Y-m-d", time() + 3600*6));
			$stmt1->bind_param("ss", $today, $_GET['bno']);
			$stmt1->execute();
			$result1 = $stmt1->get_result();
			$row1 = mysqli_fetch_all($result1, MYSQLI_ASSOC);
			$count1 = mysqli_num_rows($result1);

			$start = strtotime($_GET['stime']);
			$end = strtotime($_GET['etime']);

			$overlap = false;
			$mismatch = $start >= $end;

			if($mismatch) {
				$error = "Start time should be earlier than end time";
			}
			else {
				$stmt4 = $db->prepare("SELECT * FROM member WHERE phone = ?");
				$stmt4->bind_param("s", $_GET['cphone']);
				$stmt4->execute();
				$result4 = $stmt4->get_result();
				$row4 = mysqli_fetch_all($result4, MYSQLI_ASSOC);
				$count4 = mysqli_num_rows($result4);

				if($count4 == 0) {
					$error = "User does not exist";
				}
				else {
					foreach($row1 as $r) {

						$s = strtotime($r['start_time']);
						$e = strtotime($r['end_time']);

						$overlap = ($start >= $s && $start <= $e) || ($end >= $s && $end <= $e) || ($start <= $s && $end >= $e);
						
						if($overlap) {
							break;
						}
					}

					if($overlap) {
						$error = "Slot is already booked";
					}
					else {

						$stmt2 = $db->prepare("SELECT MAX(log_id) as last_id FROM board;");
						$stmt2->execute();
						$result2 = $stmt2->get_result();
						$row2 = mysqli_fetch_all($result2, MYSQLI_ASSOC);
						$count2 = mysqli_num_rows($result2);


						$stmt4 = $db->prepare("SELECT hourly_rate FROM board_url WHERE board_no = ?;");
						$stmt4->bind_param("s", $_GET['bno']);
						$stmt4->execute();
						$result4 = $stmt4->get_result();
						$row4 = mysqli_fetch_all($result4, MYSQLI_ASSOC);
						$count4 = mysqli_num_rows($result4);

						$hr = intval($row4[0]['hourly_rate']);

						$new_id = intval($row2[0]['last_id']) + 1;

						$stmt3 = $db->prepare("INSERT INTO board (log_id, board_no, customer_phone, employee_phone, date, start_time, end_time, hourly_rate)
												VALUES (?,?,?,?,?,?,?,?);");


						$stmt3->bind_param("ssssssss", $new_id, $_GET['bno'], $_GET['cphone'], $_SESSION['phone'], $today, $_GET['stime'], $_GET['etime'], $hr);
						$stmt3->execute();
                        
                        $url = $blink . $_GET['bno'] . "?value=1";
                        file_get_contents($url);
                        
                        $start1 = date_create($_GET['stime']);
						$end1 = date_create($_GET['etime']);
						$diff = date_diff($end1, $start1);
						$hours = $diff->h + ($diff->i / 60);
                        $sum = intval($hr * $hours);
                        
						$error = "Rent Successful - Price : " . $sum;
						
					}

				}
			}

		}
	}
?>

<html>
	<head>
		<title>Rent Board</title>
		
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		
		<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/css/bootstrap.min.css">
		<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.slim.min.js"></script>
		<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/js/bootstrap.bundle.min.js"></script>
		
		<link rel="stylesheet" type="text/css" href="fonts/font-awesome-4.7.0/css/font-awesome.min.css">
		<link rel="stylesheet" type="text/css" href="css/util.css">
		<link rel="stylesheet" type="text/css" href="css/main.css">
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
	</head>
	
	<body style="background-color: #222;">
	<div class="container">
		<nav class="navbar navbar-expand-lg navbar-dark dark d-lg-flex align-items-lg-start">
				<a class="navbar-brand">Rent Board</a>
		</nav>
		<form method="get" action="">

			<div class="text-white ml-4" style="padding-top: 20; padding-bottom: 20px;">Customer Phone</div>
			<input type="number" class="form-control" name="cphone" id="cphone" placeholder="Phone Number" min="0" required>

			<div class="text-white ml-4" style="padding-top: 20px; padding-bottom: 20px;">Board</div>
			<select class="form-control form-control-lg" name="bno" id="bno" required>
				<?php

				$stmt5 = $db->prepare("SELECT DISTINCT board.board_no, board_url.hourly_rate
										FROM board
										INNER JOIN board_url
										ON board.board_no = board_url.board_no
										ORDER BY board.board_no;");
				$stmt5->execute();
				$result5 = $stmt5->get_result();
				$row5 = mysqli_fetch_all($result5, MYSQLI_ASSOC);
				$count5 = mysqli_num_rows($result5);

				foreach($row5 as $r) {
					echo '<option value = "'.$r['board_no'].'">Board '.$r['board_no']. ' - ' .$r['hourly_rate'] .' Taka</option>';
				}
				?>
			</select>

			<div class="text-white ml-4" style="padding-top: 20px; padding-bottom: 20px;">Start Time</div>
			<input class="form-control form-control-lg" type="time" id="stime" name="stime" required value="<?php echo date("h:i",time() - 3600 * 6);?>">

			<div class="text-white ml-4" style="padding-top: 20px; padding-bottom: 20px;">End Time</div>
			<input class="form-control form-control-lg" type="time" id="etime" name="etime" required>

			<div class="text-white ml-4" style="padding-top: 20px; padding-bottom: 20px;">
				<?php
				
				if(isset($error)) {
					if(strpos($error, "Successful") !== false){
					    echo "<b style='color:green !important;'>".$error."</b>";
					}
					else{
					    echo "<b style='color:red !important;'>".$error."</b>";
					}
				}
				?>
			</div>
			<button type="submit" class="btn btn-primary">Submit</button>
			<a class="btn btn-warning" href="localhost/bbs/dashboard/">Back to Dashboard</a>
		</form>
	</body>
</html>