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
			if(isset($_GET['search'])) {
				$search = $_GET['search'];
			}
			else {
				$search = "";
			}
		}
		
		if($_SESSION['membertype'] == 1) {

			if($search == "") {
				$stmt1 = $db->prepare("SELECT transaction.quantity, transaction.rate, transaction.paid, transaction.date,
										product.name AS product, product.brand, product.product_id
										FROM transaction
										INNER JOIN product
										ON transaction.product_id = product.product_id
										WHERE customer_phone = ?
										ORDER BY transaction.date DESC;");
				$stmt1->bind_param("s", $_SESSION['phone']);

				$stmt2 = $db->prepare("SELECT board.start_time, board.end_time, board.date, board.hourly_rate, board.board_no
									FROM board
									WHERE board.customer_phone = ?
									ORDER BY date DESC, start_time ASC;");
				$stmt2->bind_param("s", $_SESSION['phone']);
			}
			else {
				$stmt1 = $db->prepare("SELECT transaction.quantity, transaction.rate, transaction.paid, transaction.date,
										product.name AS product, product.brand, product.product_id
										FROM transaction
										INNER JOIN product
										ON transaction.product_id = product.product_id
										WHERE customer_phone = ? AND
										(transaction.date LIKE ? OR
										product.name LIKE ? OR
										product.product_id = ?)
										ORDER BY transaction.date DESC;");

				$like = "%".$search."%";
				$stmt1->bind_param("ssss", $_SESSION['phone'], $like, $like, $search);

				$stmt2 = $db->prepare("SELECT board.start_time, board.end_time, board.date, board.hourly_rate, board.board_no
									FROM board
									WHERE board.customer_phone = ? AND
									board.date LIKE ?
									ORDER BY date DESC, start_time ASC;");

				$like = "%".$search."%";
				$stmt2->bind_param("s", $_SESSION['phone'], $like);
			}
			
		}
		if($_SESSION['membertype'] == 2) {

			if($search == "") {
				$stmt1 = $db->prepare("SELECT user.name, user.phone, transaction.quantity, transaction.rate, transaction.paid, transaction.date,
										product.name AS product, product.brand, product.product_id
										FROM transaction
										INNER JOIN product
										ON transaction.product_id = product.product_id
										INNER JOIN user
										ON user.phone = transaction.customer_phone
										ORDER BY transaction.date DESC;");

				$stmt2 = $db->prepare("SELECT user.name, user.phone, board.start_time, board.end_time, board.date, board.hourly_rate, board.board_no
									FROM board
									INNER JOIN user
									ON user.phone = board.customer_phone
									ORDER BY date DESC, start_time ASC;");
			}
			else {
				$stmt1 = $db->prepare("SELECT user.name, user.phone, transaction.quantity, transaction.rate, transaction.paid, transaction.date,
										product.name AS product, product.brand, product.product_id
										FROM transaction
										INNER JOIN product
										ON transaction.product_id = product.product_id
										INNER JOIN user
										ON user.phone = transaction.customer_phone
										WHERE
										transaction.date LIKE ? OR
										user.phone = ? OR
										user.name LIKE ? OR
										product.name LIKE ? OR
										product.product_id = ?
										ORDER BY transaction.date DESC;");

				$like = "%".$search."%";
				$stmt1->bind_param("sssss", $like, $search, $like, $like, $search);

				$stmt2 = $db->prepare("SELECT user.name, user.phone, board.start_time, board.end_time, board.date, board.hourly_rate, board.board_no
									FROM board
									INNER JOIN user
									ON user.phone = board.customer_phone
									WHERE
									board.date LIKE ? OR
									user.phone = ? OR
									user.name LIKE ?
									ORDER BY date DESC, start_time ASC;");

				$like = "%".$search."%";
				$stmt2->bind_param("sss", $like, $search, $like);
			}
				
		}
	
		$stmt1->execute();
		$result1 = $stmt1->get_result();
		$row1 = mysqli_fetch_all($result1, MYSQLI_ASSOC);
		$count1 = mysqli_num_rows($result1);
		
		$stmt2->execute();
		$result2 = $stmt2->get_result();
		$row2 = mysqli_fetch_all($result2, MYSQLI_ASSOC);
		$count2 = mysqli_num_rows($result2);
	}

?>

<html>
	<head>
	<link rel="stylesheet" type="text/css" href="css/main.css">
	<link rel="stylesheet" type="text/css" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css"></link>
	<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css"></link>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js"></script>
	<title>Dashboard</title>
	</head>
	<body style="background-color: #222;">
		<div class="wrapper">
			<nav class="navbar navbar-expand-lg navbar-dark dark d-lg-flex align-items-lg-start">
				<a class="navbar-brand">
					Welcome Back, <?php echo $_SESSION['name']; ?>
					<p class="text-muted pl-1">Transactions and Rentings</p>
				</a>
				<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation"> <span class="navbar-toggler-icon"></span> </button>
				<div class="collapse navbar-collapse" id="navbarNav">
					<ul class="navbar-nav ml-lg-auto">
						<li class="nav-item ">
						<form method="get" action="" id="searchForm">
						<a onclick="document.getElementById(\'searchForm\').submit();"><span class="fa fa-search"></span></a>
						<input name="search" type="search" class="dark" placeholder="Search Criteria">
						</form>
						</li>
					</ul>
				</div>
			</nav>
			<?php
            
            if($_SESSION['membertype'] == 2) {
                
            echo '<div class="row mt-2 pt-2">
                <div class="col-md-6" id="income">
                    <div class="d-flex justify-content-start align-items-center">
                        <p class="fa fa-long-arrow-up"></p>
                        <p class="text mx-3">Income</p>
                        <p class="text-white ml-4 money">BDT';
                        
                        
                            $sum = 0;
                            foreach($row1 as $r) {
                                $sum += intval($r['paid']);
                            }
                            foreach($row2 as $r) {
                                $start = date_create($r['start_time']);
								$end = date_create($r['end_time']);
								$diff = date_diff($end, $start);
								$hours = $diff->h + ($diff->i / 60);
                                $sum += intval($r['hourly_rate'] * $hours);
                            }
                            echo $sum;
                        echo '</p>
                    </div>
                </div>
            </div>';
            }?>
			<div class="d-flex justify-content-between align-items-center mt-3">
				<ul class="nav nav-tabs w-75">
					<li class="nav-item"> <a class="nav-link active trbtn">Transaction History</a> </li>
					<li class="nav-item"> <a class="nav-link inactive mhbtn">Renting History</a> </li>
				</ul>
				<a class="btn btn-danger" href="../logout/">Logout</a>
				
				<?php
				if($_SESSION['membertype'] == 2) {
				    echo '<a class="btn btn-warning" href="../admin/">Admin</a>';
					echo '<a class="btn btn-primary trtable" href="../transaction/">New Transaction</a>';
					echo '<a class="btn btn-primary mhtable" href="../board/">Rent Board</a>';
				}
				?>
			</div>
			<div class="trtable">
				<div class="table-responsive mt-3">
					<table class="table table-dark table-borderless">
						<thead>
							<tr>
								<th scope="col">Product</th>
								<th scope="col">Brand</th>
								<?php
								if($_SESSION['membertype'] == 2) {
									echo '<th scope="col">Customer Name</th>';
								}
								?>
								<th scope="col">Date</th>
								<th scope="col">Rate</th>
								<th scope="col">Quantity</th>
								<th scope="col">Paid</th>
								<th scope="col">Remaining</th>
							</tr>
						</thead>
						<tbody>
						<?php
							foreach($row1 as $r) {
								echo '<tr>';

								echo '<td> <span class="mr-1"></span> <a style="color: #fff" href="../product?search='.$r['product_id'].'">' . $r['product'] . '</a> </td>';
								
								echo '
								<td> <span class="mr-1"></span> ' . $r['brand'] . ' </td>';
							
							if($_SESSION['membertype'] == 2) {
								echo '<td> <span class="mr-1"></span> <a style="color: #fff" href="../customer?phone='.$r['phone'].'">' . $r['name'] . '</a> </td>';
							}
								
							echo'	
								<td> <span class="mr-1"></span> ' . $r['date'] . ' </td>
								<td> <span class="mr-1"></span> ' . $r['rate'] . ' </td>
								<td> <span class="mr-1"></span> ' . $r['quantity'] . ' </td>
								<td> <span class="mr-1"></span> ' . $r['paid'] . ' </td>
								<td> <span class="mr-1"></span> ' . intval((intval($r['rate']) * intval($r['quantity']))  - intval($r['paid'])) . ' </td>
							</tr>
								';
							}
						?>
						</tbody>
					</table>
				</div>
				<div class="d-flex justify-content-between align-items-center results">
					<span class="pl-md-3"><b class="text-white">Showing <?php echo $count1;?> transactions</b></span>
				</div>
			</div>
			<div class="mhtable">
				<div class="table-responsive mt-3">
					<table class="table table-dark table-borderless">
						<thead>
							<tr>
								<?php
								if($_SESSION['membertype'] == 2) {
									echo '<th scope="col">Customer Name</th>';
								}
								?>
								<th scope="col">Date</th>
								<th scope="col">Start Time</th>
								<th scope="col">End Time</th>
								<th scope="col">Duration</th>
								<th scope="col">Board</th>
								<th scope="col">Hourly Rate</th>
								<th scope="col">Renting Fee</th>
							</tr>
						</thead>
						<tbody>
						<?php
							foreach($row2 as $r) {
								echo '<tr>';
								if($_SESSION['membertype'] == 2) {
									echo '<td> <span class="mr-1"></span> <a style="color: #fff" href="../customer?phone='.$r['phone'].'">' . $r['name'] . '</a> </td>';
								}

								$start = date_create($r['start_time']);
								$end = date_create($r['end_time']);
								$diff = date_diff($end, $start);
								$hours = $diff->h + ($diff->i / 60);

								echo '
									<td> <span class="fa-solid fa-box mr-1"></span> ' . $r['date'] . ' </td>
									<td> <span class="mr-1"></span> ' . substr($r['start_time'], 0, -3) . ' </td>
									<td> <span class="mr-1"></span> ' . substr($r['end_time'], 0, -3) . ' </td>
									<td> <span class="mr-1"></span> ' . $hours . ' hours </td>
									<td> <span class="mr-1"></span> ' . $r['board_no'] . ' </td>
									<td> <span class="mr-1"></span> ' . $r['hourly_rate'] . ' </td>
									<td> <span class="mr-1"></span> ' . $r['hourly_rate'] * $hours . ' </td>
								</tr>
								';
							}
						?>
						</tbody>
					</table>
				</div>
				<div class="d-flex justify-content-between align-items-center results">
					<span class="pl-md-3"><b class="text-white">Showing <?php echo $count2;?> rents</b></span>
				</div>
			</div>
		</div>
		
		<script type='text/javascript'>
			$(document).ready(function(){
				$('.mhtable').hide();
			});
			$('a.mhbtn').click(function() {
				$('.trtable').hide();
				$('.mhtable').show();
				
				$('a.trbtn').removeClass('active');
				$('a.trbtn').addClass('inactive');
				
				$('a.mhbtn').removeClass('inactive');
				$('a.mhbtn').addClass('active');
			});
			$('a.trbtn').click(function() {
				$('.trtable').show();
				$('.mhtable').hide();
				
				$('a.trbtn').removeClass('inactive');
				$('a.trbtn').addClass('active');
				
				$('a.mhbtn').removeClass('active');
				$('a.mhbtn').addClass('inactive');
			});
		</script>
	</body>
</html>