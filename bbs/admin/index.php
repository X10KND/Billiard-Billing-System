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
	
	<body style="background-color: #222;>
	<div class="container">
	    <nav class="navbar navbar-expand-lg navbar-dark dark d-lg-flex align-items-lg-start">
			<a class="navbar-brand" style="font-size:30px">
				Welcome Back, <?php echo $_SESSION['name']; ?>
				<p class="text-muted pl-1" style="font-size:20px">Admin Panel</p>
			</a>
		</nav>
		<div class="d-flex align-items-center mt-3">
			<a class="btn btn-primary ml-3 mr-3" href="../transaction/">New Transaction</a>
			<a class="btn btn-primary ml-3 mr-3" href="../board/">Rent Board</a>
		</div>	
		<div class="d-flex align-items-center mt-3">
			<a class="btn btn-light ml-3 mr-3" href="../addcategory/">Add Category</a>
			<a class="btn btn-light ml-3 mr-3" href="../addproduct/">Add Product</a>
			<a class="btn btn-light ml-3 mr-3" href="../stock/">Add Stock</a>
			</div>	
		<div class="d-flex align-items-center mt-3">
			<a class="btn btn-info ml-3 mr-3" href="../category/">Browse Category</a>
			<a class="btn btn-info ml-3 mr-3" href="../supplier/">Browse Supplier</a>
			</div>	
		<div class="d-flex align-items-center mt-3">
			<a class="btn btn-success ml-3 mr-3" href="../dashboard/">Dashboard</a>
			<a class="btn btn-danger ml-3 mr-3" href="../logout/">Logout</a>
			
		</div>
	</div>
	</body>
</html>