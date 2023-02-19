<?php
	session_start();
	
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
		if(isset($_GET['cphone']) && isset($_GET['pid']) && isset($_GET['quantity']) && isset($_GET['amount'])) {
			
			if ($db->connect_error) {
			  die("Connection failed: " . $db->connect_error);
			}

			$today = strval(date("Y-m-d"));
			$phone = $_GET['cphone'];

			$stmt1 = $db->prepare("SELECT * FROM member WHERE phone = ?");
			$stmt1->bind_param("s", $phone);
			$stmt1->execute();
			$result1 = $stmt1->get_result();
			$row1 = mysqli_fetch_all($result1, MYSQLI_ASSOC);
			$count1 = mysqli_num_rows($result1);

			if($count1 == 0) {
				$error = "User does not exist";
			}
			else {
				$quantity = intval($_GET['quantity']);
				if($quantity <= 0) {
					$error = "Must buy at least one item";
				}
				else {
					$stmt2 = $db->prepare("SELECT * FROM product WHERE product_id = ?");
					$stmt2->bind_param("s", $_GET['pid']);
					$stmt2->execute();
					$result2 = $stmt2->get_result();
					$row2 = mysqli_fetch_all($result2, MYSQLI_ASSOC);
					$count2 = mysqli_num_rows($result2);

					if($count2 == 0) {
						$error = "Invalid Product";
					}
					else {
						$stock = intval($row2[0]['stock']);
						
						if($stock < $quantity) {
							$error = "Not enough stock";
						}
						else {
							$stmt3 = $db->prepare("SELECT MAX(transaction_id) as last_id FROM transaction;");
							$stmt3->execute();
							$result3 = $stmt3->get_result();
							$row3 = mysqli_fetch_all($result3, MYSQLI_ASSOC);
							$count3 = mysqli_num_rows($result3);

							$new_id = intval($row3[0]['last_id']) + 1;

							$rate = $row2[0]['price'];

							$stmt4 = $db->prepare("INSERT INTO transaction (transaction_id, customer_phone, employee_phone, product_id, quantity, rate, paid, date)
												VALUES (?,?,?,?,?,?,?,?);");
							$stmt4->bind_param("ssssssss", $new_id, $phone, $_SESSION['phone'], $_GET['pid'], $quantity, $rate, $_GET['amount'], $today);
							$stmt4->execute();

							$new_stock = $stock - $quantity;

							$stmt5 = $db->prepare("UPDATE product SET stock = ? WHERE product_id = ?;");
							$stmt5->bind_param("ss", $new_stock, $_GET['pid']);
							$stmt5->execute();

							$error = "Payment Successful";

						}
					}
				}
			}
			

		}
	}
?>

<html>
	<head>
		<title>New Transaction</title>
		
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
                <a class="navbar-brand">New Transaction</a>
		</nav>
		<form method="get" action="">

			<div class="text-white ml-4" style="padding-top: 20; padding-bottom: 20px;">Customer Phone</div>
			<input type="number" class="form-control" name="cphone" id="cphone" placeholder="Phone Number" min="0" required>

			<div class="text-white ml-4" style="padding-top: 20px; padding-bottom: 20px;">Product</div>
			<select class="form-control form-control-lg" name="pid" id="pid" required>
				<?php
				$stmt05 = $db->prepare("SELECT product_id, name, price, stock
										FROM product
										ORDER BY product_id;");

				$stmt05->execute();
				$result05 = $stmt05->get_result();
				$row05 = mysqli_fetch_all($result05, MYSQLI_ASSOC);
				$count05 = mysqli_num_rows($result05);

				foreach($row05 as $r) {
					echo '<option value = "'.$r['product_id'].'">'.$r['product_id']. '. ' .$r['name'].' - '.$r['price'].' Taka ('.$r['stock'].')</option>';
				}
				?>
			</select>

			<div class="text-white ml-4" style="padding-top: 20; padding-bottom: 20px;">Quantity</div>
			<input type="number" class="form-control" name="quantity" id="quantity" placeholder="Quantity" min="1" required>

			<div class="text-white ml-4" style="padding-top: 20; padding-bottom: 20px;">Paid Amount</div>
			<input type="number" class="form-control" name="amount" id="amount" placeholder="Amount" min="0" required>

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