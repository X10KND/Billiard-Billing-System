<?php
	session_start();
	
	if(isset($_SESSION['loginstatus'])) {
		if($_SESSION['loginstatus'] == 1) {
			header("location: localhost/bbs/dashboard/");
		}
	}
	
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	    
		define('DB_SERVER', 'localhost');
    	define('DB_USERNAME', 'REDACTED');
    	define('DB_PASSWORD', 'REDACTED');
    	define('DB_DATABASE', 'REDACTED');

	
		$db = new mysqli(DB_SERVER,DB_USERNAME,DB_PASSWORD,DB_DATABASE);

		
		if ($db->connect_error) {
		  die("Connection failed: " . $db->connect_error);
		}
		
		$phone = $_POST['phone'];
        $password = $_POST['password'];
		
		$stmt = $db->prepare("SELECT user.name, user.password, user.phone,
								CONCAT(address.house, ', ', address.city, ', ', address.postal_code, ', ', address.country) AS fullAddress
								FROM user
								INNER JOIN address ON user.phone = address.phone
								WHERE user.phone = ?;");
				
		$stmt->bind_param("s", $phone);
		$stmt->execute();
		$result = $stmt->get_result();
        $row = mysqli_fetch_all($result, MYSQLI_ASSOC);
		$count = mysqli_num_rows($result);
		
		$userFound = false;
		
		if($count > 0) {
			if (password_verify($password, $row[0]['password'])) {
				
				$_SESSION['loginstatus'] = 1;
				$_SESSION['name'] = $row[0]['name'];
				$_SESSION['phone'] = $row[0]['phone'];
				
				foreach($row as $r) {
					$_SESSION['fullAddress'][] = $r['fullAddress'];
				}
				
				$stmt1 = $db->prepare("SELECT * FROM member WHERE phone = ?");
				$stmt1->bind_param("s", $phone);
				$stmt1->execute();
				$result1 = $stmt1->get_result();
				$row1 = mysqli_fetch_array($result1, MYSQLI_ASSOC);
				$count1 = mysqli_num_rows($result1);
				
				if($count1 == 1) {
					$_SESSION['membertype'] = 1;
					$_SESSION['member_since'] = $row1['member_since'];
					$_SESSION['payment_status'] = $row1['payment_status'];
					$_SESSION['DOB'] = $row1['DOB'];
					
					$userFound = true;
					$error = "";
					header("location: ../dashboard");
				}
				
				else {
					$stmt2 = $db->prepare("SELECT * FROM employee WHERE phone = ?");
					$stmt2->bind_param("s", $phone);
					$stmt2->execute();
					$result2 = $stmt2->get_result();
					$row2 = mysqli_fetch_array($result2, MYSQLI_ASSOC);
					$count2 = mysqli_num_rows($result2);
					
					if($count2 == 1) {
						$_SESSION['membertype'] = 2;
						$_SESSION['nid'] = $row2['nid'];
						$_SESSION['salary'] = $row2['salary'];
					
						$userFound = true;
						$error = "";
						header("location: ../admin");
					}
				}
			}
		}
        
        if(!$userFound) {
            $_SESSION['loginstatus'] = 0;
			$_SESSION['membertype'] = 0;
            $error = "Your Phone or Password is invalid";
        }

		
	}
?>

<html>
	<head>
		<title>BBS</title>
		
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
	<body>
	<div class="limiter">
		<div class="container-login100">
			<div class="wrap-login100 p-t-50 p-b-90">
				<form name="login" id="login-form" class="login100-form validate-form flex-sb flex-w" action="" method="post">
					<span class="login100-form-title p-b-51">
						Login
					</span>
					
					<div class="wrap-input100 validate-input m-b-16" data-validate="Phone number is required">
						<input class="input100" type="text" name="phone" id="phone" placeholder="Phone Number">
						<span class="focus-input100"></span>
					</div>
					
					<div class="wrap-input100 validate-input m-b-16" data-validate="Password is required">
						<input class="input100" type="password" name="password" id="password" placeholder="Password">
						<span class="focus-input100"></span>
					</div>
					
					
					<div class="container-login100-form-btn m-t-17">
						<button class="login100-form-btn">
							Login
						</button>
					</div>
					
					<?php
					    if(isset($error)) {
							echo '<div class="container alert alert-danger">
								<strong>' . $error . '</strong>
							</div>';
					    }
					?>
					
				</form>
			</div>
		</div>
	</div>
	<script src="js/main.js"></script>
	</body>
</html>