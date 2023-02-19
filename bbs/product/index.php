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
		
		if($_SERVER['REQUEST_METHOD'] === 'GET') {
			
			$stmt1 = $db->prepare("SELECT product.name AS pname, product.brand, product.stock, product.price, product.product_id,
									supplier.name AS sname, supplier.supplier_id,
									category.name AS cname, category.category_id
									FROM product
									INNER JOIN supplier
									ON product.supplier_id = supplier.supplier_id
									INNER JOIN category
									ON product.category_id = category.category_id
									WHERE product.product_id = ? OR
									product.name LIKE ?;");

			$like = "%".$_GET['search']."%";
			$stmt1->bind_param("ss", $_GET['search'], $like);
			
			$stmt1->execute();
			$result1 = $stmt1->get_result();
	        $row1 = mysqli_fetch_all($result1, MYSQLI_ASSOC);
			$count1 = mysqli_num_rows($result1);
			
			if($count1 == 0) {
				$error = "Invalid Product ID";
			}
			else {
				$error = "";
			}
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
	<title>Product Info</title>
	
	</head>
    <body style="background-color: #222;">
        <div class="wrapper">
            <nav class="navbar navbar-expand-lg navbar-dark dark d-lg-flex align-items-lg-start">
                <a class="navbar-brand">
                    <?php
					if($count1 > 0) {
						echo $row1[0]['pname'];
					}
					else {
						echo $error;
					}
					?>
                    <p class="text-muted pl-1">Product Info</p>
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
							<input name="search" type="search" class="dark" placeholder="Search Product">
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
					<div class="text mx-3">Product ID</div>
					<div class="text-white ml-4">
					<?php
					if($count1 > 0) {
						echo $row1[0]['product_id'];
					}
					?>
					</div>
				</div>
			</div>

			<div class="row mt-2 pt-2 ml-2">
				<div class="d-flex justify-content-md-end align-items-center">
					<div class="text mx-3">Brand</div>
					<div class="text-white ml-4">
					<?php
					if($count1 > 0) {
						echo $row1[0]['brand'];
					}
					?>
					</div>
				</div>
			</div>
			
			<div class="row mt-2 pt-2 ml-2">
				<div class="d-flex justify-content-md-end align-items-center">
					<div class="text mx-3">Stock</div>
					<div class="text-white ml-4">
					<?php
					if($count1 > 0) {
						echo $row1[0]['stock'];
					}
					?>
					</div>
				</div>
			</div>

			<div class="row mt-2 pt-2 ml-2">
				<div class="d-flex justify-content-md-end align-items-center">
					<div class="text mx-3">Price</div>
					<div class="text-white ml-4">
					<?php
					if($count1 > 0) {
						echo $row1[0]['price'];
					}
					?>
					</div>
				</div>
			</div>

			<div class="row mt-2 pt-2 ml-2">
				<div class="d-flex justify-content-md-end align-items-center">
					<div class="text mx-3">Supplier</div>
					<div class="text-white ml-4">
					<?php
					if($_SESSION['membertype'] == 2) {
						echo '<a style="color: #fff" href="../supplier?search='.$row1[0]['supplier_id'].'">';
					}
					if($count1 > 0) {
						echo $row1[0]['sname'];
					}
					if($_SESSION['membertype'] == 2) {
						echo '</a>';
					}
					?>
					</div>
				</div>
			</div>

			<div class="row mt-2 pt-2 ml-2">
				<div class="d-flex justify-content-md-end align-items-center">
					<div class="text mx-3">Category</div>
					<div class="text-white ml-4">
					<?php
					if($count1 > 0) {
						echo '<a style="color: #fff" href="../category?search='.$row1[0]['category_id'].'">';
						echo $row1[0]['cname'];
						echo '</a>';
					}
					?>
					</div>
				</div>
			</div>
			<a class="btn btn-warning"style="margin-top: 10px" href="localhost/bbs/dashboard/">Back to Dashboard</a>
			
        </div>
    </body>
</html>