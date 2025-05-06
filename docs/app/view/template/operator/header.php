<?php
	// Start session at the VERY TOP before any output
	if (session_status() === PHP_SESSION_NONE) {
		session_start();
	}
	$loggedIn = isset($_SESSION['user']);
	$name = $loggedIn ? $_SESSION['user']['name'] : 'Guest';
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<title><?php echo $data['title']; ?></title>	
		<!-- Mobile metas -->
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<!-- Site metas -->
		<meta name="keywords" content="">
		<meta name="description" content="">
		<meta name="author" content="">
		<!-- Bootstrap CSS -->
		<link href="<?php echo APP_PATH; ?>css/bootstrap.min.css" rel="stylesheet">
		<!-- Custom CSS -->
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.css" />
		<link rel="stylesheet" href="<?php echo APP_PATH; ?>css/style.css">
		<link rel="stylesheet" href="<?php echo APP_PATH; ?>css/operatorstyle/style.css">
		<link rel="stylesheet" href="<?php echo APP_PATH; ?>css/responsive.css">
		<link rel="stylesheet" href="<?php echo APP_PATH; ?>css/jquery.mCustomScrollbar.min.css">
		<!-- Font Awesome and Fancybox -->
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
		<link rel="stylesheet" href="https://netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css">
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/2.1.5/jquery.fancybox.min.css" media="screen">
		<!-- Favicon -->
		<link rel="icon" href="<?php echo APP_PATH; ?>images/fevicon.png" type="image/gif">
	</head>
	<body>
		<!-- header inner -->
		<div class="header">
			<div class="header_top">
				<div class="container">
					<div class="row">
						<div class="col-md-3">
							<ul class="contat_infoma">
								<li><a href="tel:+62 81340260194"><i class="fa fa-phone" aria-hidden="true"></i> tel:+62 81340260194</a></li>
							</ul>
						</div>
						<div class="col-md-6">
							<ul class="social_icon_top text_align_center">
								<li><a href="https://www.facebook.com/profile.php?id=100055209049951"><i class="fa fa-facebook" aria-hidden="true"></i></a></li>
								<li><a href="https://www.instagram.com/jens_house_airmadidi/"><i class="fa fa-instagram" aria-hidden="true"></i></a></li>
							</ul>
						</div>
						<div class="col-md-3">
							<ul class="contat_infoma text_align_right">
								<li><a href="mailto:gmail.com"> <i class="fa fa-envelope" aria-hidden="true"></i> JensHouse@gmail.com</a></li>
							</ul>
						</div>
					</div> <!-- row -->
				</div> <!-- container -->
			</div> <!-- header_top -->
			<div class="container">
				<div class="row">
					<div class="col-md-12">
						<div class="header_bottom">
							<div class="row">
								<nav class="navigation navbar navbar-expand-md navbar-dark">
									<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarsExample04" aria-controls="navbarsExample04" aria-expanded="false" aria-label="Toggle navigation">
										<span class="navbar-toggler-icon"></span>
									</button>
									<div class="collapse navbar-collapse" id="navbarsExample04">
										<ul class="navbar-nav mr-auto">
											<li class="nav-item">
												<a class="nav-link" href="<?php echo APP_PATH; ?>payment_history/cus_history">Customer History</a>
											</li>
											<li class="nav-item">
												<a class="nav-link" href="<?php echo APP_PATH; ?>active_booking/cus_active">Booking Cancelation</a>
											</li>
											<li class="nav-item">
												<!-- (2) If logged in, show 'Logout?', else 'Account' -->
												<?php if ($loggedIn): ?>
													<a class="nav-link" href="<?php echo APP_PATH; ?>default/logout">Logout?</a>
												<?php else: ?>
													<a class="nav-link" href="<?php echo APP_PATH; ?>enroll/login">Account</a>
												<?php endif; ?>
											</li>
										</ul>
									</div> <!-- collapse -->
								</nav>
							</div> <!-- row -->
						</div> <!-- header_bottom -->
					</div>
				</div> <!-- row -->
			</div> <!-- container -->
		</div> <!-- header -->
		<!-- end header inner -->