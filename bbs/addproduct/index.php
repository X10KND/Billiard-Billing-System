<?php
	session_start();
	
	if($_SESSION['membertype'] != 2) {
		header("location: localhost/BBS/dashboard/");
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
		if(isset($_GET['pname']) && isset($_GET['brand']) && isset($_GET['price']) && isset($_GET['stock']) && isset($_GET['sid']) && isset($_GET['cid'])) {
			
			if ($db->connect_error) {
			  die("Connection failed: " . $db->connect_error);
			}

			$stmt1 = $db->prepare("SELECT MAX(product_id) AS last_id FROM product;");
			$stmt1->execute();
			$result1 = $stmt1->get_result();
			$row1 = mysqli_fetch_all($result1, MYSQLI_ASSOC);
			$count1 = mysqli_num_rows($result1);

			$pro_id = intval($row1[0]['last_id']) + 1;

			$stmt5 = $db->prepare("INSERT INTO product (product_id, supplier_id, category_id, name, brand, stock, price) VALUES(?,?,?,?,?,?,?);");
			$stmt5->bind_param("sssssss", $pro_id, $_GET['sid'], $_GET['cid'], $_GET['pname'], $_GET['brand'], $_GET['stock'], $_GET['price']);
			$stmt5->execute();

			$error = "Insert Successful";
		}
	}
?>

<html>
	<head>
		<title>Product Management</title>
		
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
                <a class="navbar-brand">Product Management</a>
		</nav>
		<form method="get" action="">

			<div class="text-white ml-4" style="padding-top: 20; padding-bottom: 20px;">Product Name</div>
			<input type="text" class="form-control" name="pname" id="pname" placeholder="Product" required>

			<div class="text-white ml-4" style="padding-top: 20; padding-bottom: 20px;">Brand Name</div>
			<input type="text" class="form-control" name="brand" id="brand" placeholder="Brand" required>

			<div class="text-white ml-4" style="padding-top: 20; padding-bottom: 20px;">Price</div>
			<input type="number" class="form-control" name="price" id="price" placeholder="Price" min="0" required>

			<div class="text-white ml-4" style="padding-top: 20; padding-bottom: 20px;">Stock</div>
			<input type="number" class="form-control" name="stock" id="stock" placeholder="Stock" min="0" required>

			<div class="text-white ml-4" style="padding-top: 20; padding-bottom: 20px;">Supplier ID</div>
			<input type="number" class="form-control" name="sid" id="sid" placeholder="Supplier" min="0" required>

			<div class="text-white ml-4" style="padding-top: 20; padding-bottom: 20px;">Category ID</div>
			<input type="number" class="form-control" name="cid" id="cid" placeholder="Category" min="0" required>

			<div class="text-white ml-4" style="padding-top: 20px; padding-bottom: 20px;">
				<?php
				if(isset($error)) {
					if(strpos($error, "Successful") !== false) {
					    echo "<b style='color:green !important;'>".$error."</b>";
					}
					else {
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