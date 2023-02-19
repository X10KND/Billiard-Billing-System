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
									category.name AS cname, category.category_id
									FROM product
									INNER JOIN category
									ON product.category_id = category.category_id
									WHERE category.category_id = ? OR
									category.name LIKE ?;");
				
			$like = "%".$_GET['search']."%";
			$stmt1->bind_param("ss", $_GET['search'], $like);
			
			$stmt1->execute();
			$result1 = $stmt1->get_result();
	        $row1 = mysqli_fetch_all($result1, MYSQLI_ASSOC);
			$count1 = mysqli_num_rows($result1);
			
			if($count1 == 0) {
				$error = "Invalid Category ID";
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
	<title>Category Info</title>
	
	</head>
    <body style="background-color: #222;">
        <div class="wrapper">
            <nav class="navbar navbar-expand-lg navbar-dark dark d-lg-flex align-items-lg-start">
                <a class="navbar-brand">
                    <?php
					if($count1 > 0) {
						if(isset($_GET['search'])) {
							echo $row1[0]['cname'];
						}
						else {
							echo "Category List";
						}
					}
					else {
						echo $error;
					}
					?>
                    <p class="text-muted pl-1">Category Info</p>
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
							<input name="search" type="search" class="dark" placeholder="Search Category">
							</form>
							</li>
						</ul>
					</div>
					';
				}
				?>
            </nav>
			
			<div>
				<div class="table-responsive mt-3">
					<table class="table table-dark table-borderless">
						<thead>
							<tr>
								<th scope="col">Category</th>
								<th scope="col">Category ID</th>
								<th scope="col">Product</th>
								<th scope="col">Brand</th>
								<th scope="col">Stock</th>
								<th scope="col">Price</th>
							</tr>
						</thead>
						<tbody>
						<?php
							foreach($row1 as $r) {
								echo '<tr>
								<td> <span class="mr-1"></span> <a style="color: #fff" href="../category?search='.$r['category_id'].'">' . $r['cname'] . '</a> </td>
								<td> <span class="mr-1"></span> <a style="color: #fff" href="../category?search='.$r['category_id'].'">' . $r['category_id'] . '</a> </td>
								<td> <span class="mr-1"></span> <a style="color: #fff" href="../product?search='.$r['product_id'].'">' . $r['pname'] . '</a> </td>
								<td> <span class="mr-1"></span> ' . $r['brand'] . ' </td>
								<td> <span class="mr-1"></span> ' . $r['stock'] . ' </td>
								<td> <span class="mr-1"></span> ' . $r['price'] . ' </td>
							</tr>
								';
							}
						?>
						</tbody>
					</table>
				</div>
				<div class="d-flex justify-content-between align-items-center results">
					<span class="pl-md-3"><b class="text-white">Showing <?php echo $count1;?> products</b></span>
				</div>
			</div>
			
			<a class="btn btn-warning"style="margin-top: 10px" href="localhost/bbs/dashboard/">Back to Dashboard</a>
        </div>
        
    </body>
</html>