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
		<link rel="stylesheet" href="<?php echo APP_PATH; ?>css/style.css">
		<link rel="stylesheet" href="<?php echo APP_PATH; ?>css/responsive.css">
		<link rel="stylesheet" href="<?php echo APP_PATH; ?>css/jquery.mCustomScrollbar.min.css">
		<!-- Font Awesome and Fancybox -->
		<link rel="stylesheet" href="https://netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css">
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/2.1.5/jquery.fancybox.min.css" media="screen">
		<!-- Favicon -->
		<link rel="icon" href="<?php echo APP_PATH; ?>images/fevicon.png" type="image/gif">
	</head>
	<body>
		<!-- header -->
		<header class="full_bg">
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
									<div class="logo_section">
										<div class="full">
											<div class="center-desk">
												<div class="logo">
													<a aria-current="page" href="<?php echo APP_PATH; ?>">
														<img src="<?php echo APP_PATH; ?>/img/Logo.png" alt="Hotel Logo" class="hotel-logo">
													</a>
													<a href="<?php echo APP_PATH; ?>">Jen's House</a>
												</div>
											</div>
										</div>
									</div>
									<nav class="navigation navbar navbar-expand-md navbar-dark">
										<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarsExample04" aria-controls="navbarsExample04" aria-expanded="false" aria-label="Toggle navigation">
											<span class="navbar-toggler-icon"></span>
										</button>
										<div class="collapse navbar-collapse" id="navbarsExample04">
											<ul class="navbar-nav mr-auto">
												<li class="nav-item">
													<a class="nav-link" href="<?php echo APP_PATH; ?>default/checking">Booking</a>
												</li>
												<li class="nav-item">
													<a class="nav-link" href="<?php echo APP_PATH; ?>default/home">Home</a>
												</li>
												<li class="nav-item">
													<a class="nav-link" href="<?php echo APP_PATH; ?>default/faq">Frequently Asked Question (FAQ)</a>
												</li>
												<li class="nav-item dropdown">
													<a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
														<?php if ($loggedIn): ?>
															<?php echo htmlspecialchars($name); ?>
														<?php else: ?>
															Account
														<?php endif; ?>
													</a>
													<ul class="dropdown-menu" aria-labelledby="navbarDropdown">
														<?php if ($loggedIn): ?>
															<li><a class="dropdown-item" href="<?php echo APP_PATH; ?>default/details">Booking Details</a></li>
															<li><hr class="dropdown-divider"></li>
															<li><a class="dropdown-item" href="<?php echo APP_PATH; ?>default/logout">Logout</a></li>
														<?php else: ?>
															<li><a class="dropdown-item" href="<?php echo APP_PATH; ?>enroll/login">Login</a></li>
															<li><hr class="dropdown-divider"></li>
															<li><a class="dropdown-item" href="<?php echo APP_PATH; ?>enroll/register">Register</a></li>
														<?php endif; ?>
													</ul>
												</li>
											</ul>
										</div>
									</nav>
								</div> <!-- row -->
							</div> <!-- header_bottom -->
						</div>
					</div> <!-- row -->
				</div> <!-- container -->
			</div> <!-- header -->
			<!-- end header inner -->
			<!-- Banner Carousel -->
			<section class="banner_main">
				<div id="myCarousel" class="carousel slide banner" data-ride="carousel">
					<ol class="carousel-indicators">
						<li data-target="#myCarousel" data-slide-to="0" class="active"></li>
						<li data-target="#myCarousel" data-slide-to="1"></li>
						<li data-target="#myCarousel" data-slide-to="2"></li>
					</ol>
					<div class="carousel-inner">
						<!-- Slide 1 -->
						<div class="carousel-item active">
							<div class="container">
								<div class="carousel-caption banner_po">
									<div class="row">
										<div class="col-md-9">
											<div class="build_box">
												<?php if ($loggedIn): ?>
													<h2 class="welcome-line">Welcome, <?php echo htmlspecialchars($name); ?>!</h2>
													<h3 class="subhead">Would you like to check our hotel room?</h3>
													<p class="description">Pick a selection of rooms for a couple of days, click the option below or go to reservation page to continue.</p>
												<?php else: ?>
													<h2 class="welcome-line">Welcome, Guest!</h2>
													<h3 class="subhead">Would you like to check our hotel room?</h3>
													<p class="description">Pick a selection of rooms for a couple of days, click the option below or go to reservation page to continue.</p>
												<?php endif; ?>
											</div>
										</div>
									</div> <!-- row -->
								</div> <!-- carousel-caption -->
							</div> <!-- container -->
						</div> <!-- carousel-item -->
						<div class="carousel-item">
							<div class="container">
								<div class="carousel-caption banner_po">
									<div class="row">
										<div class="col-md-9">
											<div class="build_box">
												<?php if ($loggedIn): ?>
													<h2 class="welcome-line">Welcome, <?php echo htmlspecialchars($name); ?>!</h2>
													<h3 class="subhead">Would you like to check our contracted housing/kost?</h3>
													<p class="description">Pick a variety of house options that you can live in for months or even years, click the option below or go to reservation page to continue.</p>
												<?php else: ?>
													<h2 class="welcome-line">Welcome, Guest!</h2>
													<h3 class="subhead">Would you like to check our contracted housing/kost?</h3>
													<p class="description">Pick a variety of house options that you can live in for months or even years, click the option below or go to reservation page to continue.</p>
												<?php endif; ?>
											</div>
										</div>
									</div> <!-- row -->
								</div> <!-- carousel-caption -->
							</div> <!-- container -->
						</div> <!-- carousel-item -->
						<div class="carousel-item">
							<div class="container">
								<div class="carousel-caption banner_po">
									<div class="row">
										<div class="col-md-9">
											<div class="build_box">
												<?php if ($loggedIn): ?>
													<h2 class="welcome-line">Welcome, <?php echo htmlspecialchars($name); ?>!</h2>
													<h3 class="subhead">Would you like to check our venue?</h3>
													<p class="description">Celebrate your wedding, birthday, or any other party in our top venue, click the option below or go to reservation page to continue.</p>
												<?php else: ?>
													<h2 class="welcome-line">Welcome, Guest!</h2>
													<h3 class="subhead">Would you like to check our venue?</h3>
													<p class="description">Celebrate your wedding, birthday, or any other party in our top venue, click the option below or go to reservation page to continue.</p>
												<?php endif; ?>
											</div>
										</div>
									</div> <!-- row -->
								</div> <!-- carousel-caption -->
							</div> <!-- container -->
						</div> <!-- carousel-item -->
						<a class="carousel-control-prev" href="#myCarousel" role="button" data-slide="prev">
							<span class="carousel-control-prev-icon" aria-hidden="true"></span>
							<span class="sr-only">Previous</span>
						</a>
						<a class="carousel-control-next" href="#myCarousel" role="button" data-slide="next">
							<span class="carousel-control-next-icon" aria-hidden="true"></span>
							<span class="sr-only">Next</span>
						</a>
					</div> <!-- #myCarousel -->
				</div>
			</section>
			<!-- end banner -->
			<!-- three_box -->
			<div class="three_box">
				<div class="container">
					<div class="row">
						<div class="col-md-3">
							<div id="text_hover" class="const text_align_center">
								<a class="nav-link" href="<?php echo APP_PATH; ?>default/checking">
									<b><font size="5">3</font></b>
									<span>Hotel rooms</span>
								</a>
							</div>
						</div>
						<div class="col-md-3">
							<div id="text_hover" class="const text_align_center">
								<a class="nav-link" href="<?php echo APP_PATH; ?>default/checking">
									<b><font size="5">2</font></b>
									<span>Contracted house/Kost</span>
								</a>
							</div>
						</div>
						<div class="col-md-3">
							<div id="text_hover" class="const text_align_center">
								<a class="nav-link" href="<?php echo APP_PATH; ?>default/checking">
									<b><font size="5">3</font></b>
									<span>Venue</span>
								</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</header>