<?php

	session_start();
	
	if(!isset($_SESSION['loginstatus'])) {
		header("location: localhost/bbs/login/");
	}
	
	if($_SESSION['loginstatus'] == 0) {
		header("location: localhost/bbs/login/");
	}
	
	if($_SESSION['membertype'] == 1 || $_SESSION['membertype'] == 2) {
		
		define('DB_SERVER', 'localhost');
    	define('DB_USERNAME', 'REDACTED');
    	define('DB_PASSWORD', 'REDACTED');
    	define('DB_DATABASE', 'REDACTED');

	
		$db = new mysqli(DB_SERVER,DB_USERNAME,DB_PASSWORD,DB_DATABASE);

		
		if ($db->connect_error) {
		  die("Connection failed: " . $db->connect_error);
		}
		
		if($_SESSION['membertype'] == 1) {
			$phone = $_SESSION['phone'];
		}
		else if($_SESSION['membertype'] == 2 && $_SERVER['REQUEST_METHOD'] === 'GET') {
			$phone = $_GET['phone'];
		}
		
		$stmt1 = $db->prepare("SELECT user.name, user.phone, member.member_since, member.payment_status, member.DOB, CONCAT(address.house, ', ', address.city, ', ', address.postal_code, ', ', address.country) AS fullAddress
		FROM user
		INNER JOIN member
		ON user.phone = member.phone
		INNER JOIN address
		ON user.phone = address.phone
		WHERE user.phone = ?;");
			
		$stmt1->bind_param("s", $phone);
		
		$stmt1->execute();
		$result1 = $stmt1->get_result();
        $row1 = mysqli_fetch_all($result1, MYSQLI_ASSOC);
		$count1 = mysqli_num_rows($result1);
		
		if($count1 == 0) {
			$error = "Invalid Phone Number";
		}
		else {
			$error = "";
		}
		
	}

?>

<html>
    <head>
	<link rel="stylesheet" type="text/css" href="css/main.css">
	<link rel="stylesheet" type="text/css" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css"></link>
	<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css"></link>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js"></script>
	<title>Profile</title>
	
	</head>
    <body style="background-color: #222;">
        <div class="wrapper">
            <nav class="navbar navbar-expand-lg navbar-dark dark d-lg-flex align-items-lg-start">
                <a class="navbar-brand" href="">
                    <?php
					if($count1 > 0) {
						echo $row1[0]['name'];
					}
					else {
						echo $error;
					}
					?>
                    <p class="text-muted pl-1">Customer Profile</p>
                </a>
				<?php
				
				if($_SESSION['membertype'] == 2) {
					echo '
					<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation"> <span class="navbar-toggler-icon"></span> </button>
					<div class="collapse navbar-collapse" id="navbarNav">
						<ul class="navbar-nav ml-lg-auto">
							<li class="nav-item ">
							<form method="get" action="" id="searchForm">
							<a onclick="document.getElementById(\'searchForm\').submit();"><span class="fa fa-search"></span></a>
							<input name="phone" type="search" class="dark" placeholder="Search Phone">
							</form>
							</li>
						</ul>
					</div>
					';
				}
				?>
            </nav>
			
			
			<div class="row mt-2 pt-2 ml-2">
				<div class="d-flex justify-content-md-end align-items-center">
					<div class="text mx-3">Phone</div>
					<div class="text-white ml-4">
					<?php
					if($count1 > 0) {
						echo $row1[0]['phone'];
					}
					?>
					</div>
				</div>
			</div>
			
			<div class="row mt-2 pt-2 ml-2">
				<div class="d-flex justify-content-md-end align-items-center">
					<div class="text mx-3">Address</div>
					<div class="text-white ml-4">
					<?php
					if($count1 > 0) {
						$s = "";
						foreach($row1 as $r) {
							$s .= $r['fullAddress'] . '. ; ';
						}
						echo substr($s, 0, -3);
					}
					?>
					</div>
				</div>
			</div>
			
			<div class="row mt-2 pt-2 ml-2">
				<div class="d-flex justify-content-md-end align-items-center">
					<div class="text mx-3">Member Since</div>
					<div class="text-white ml-4">
					<?php
					if($count1 > 0) {
						echo date_format(date_create($row1[0]['member_since']), "d M, Y");
					}
					?>
					</div>
				</div>
			</div>
			
			<div class="row mt-2 pt-2 ml-2">
				<div class="d-flex justify-content-md-end align-items-center">
					<div class="text mx-3">Payment Status</div>
					<div class="text-white ml-4">
					<?php
					if($count1 > 0) {
						if($row1[0]['payment_status'] == 1) {
							echo "<b style='color:green !important;'>Paid</b>";
						}
						else {
							echo "<b style='color:red !important;'>Unpaid</b>";
						}
					}
					?>
					</div>
				</div>
			</div>
			<a class="btn btn-warning"style="margin-top: 10px" href="localhost/bbs/dashboard/">Back to Dashboard</a>
			
        </div>
    </body>
</html>