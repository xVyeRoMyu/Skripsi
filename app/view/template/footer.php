<?php
	// Start session at the VERY TOP before any output
	if (session_status() === PHP_SESSION_NONE) {
		session_start();
	}
	$loggedIn = isset($_SESSION['user']);
	$name = $loggedIn ? $_SESSION['user']['name'] : 'Guest';
?>
		<!-- 1) jQuery (Full Version) -->
		<script src="<?php echo APP_PATH; ?>/js/jquery.min.js"></script>
		  
		<!-- 2) Popper.js -->
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
		  
		<!-- 3) Bootstrap JS -->
		<script src="<?php echo APP_PATH; ?>/js/bootstrap.min.js"></script>
		<script src="<?php echo APP_PATH; ?>/js/bootstrap.bundle.min.js"></script>
		  
		<!-- Additional JS Files -->
		<script src="<?php echo APP_PATH; ?>/js/jquery.mCustomScrollbar.concat.min.js"></script>
		<!--  footer -->
		<footer>
			<div class="footer">
				<div class="container">
					<div class="row">
						<!-- Left Column -->
						<div class="col-lg-3 col-md-6">
							<p class="many">
								<font size="5" color="white">
									The official Jen's House online booking website. 
									All in one packages with us!
								</font>
							</p>
						</div>
						<!-- Middle Column (Menu) -->
						<div class="col-lg-2 col-md-6">
							<h3>Go Back!</h3>
							<ul class="link_menu">
								<li><a href="<?php echo APP_PATH; ?>default/checking">Make a reservation</a></li>
								<li><a href="<?php echo APP_PATH; ?>default/home">Home</a></li>
								<li><a href="<?php echo APP_PATH; ?>default/faq">Frequently Asked Question (FAQ)</a></li>
								<?php if ($loggedIn): ?>
								
									<li class="dropdown">
										<a class="dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
											<?php echo htmlspecialchars($name); ?>
										</a>
										<ul class="dropdown-menu">
											<li><a class="dropdown-item" href="<?php echo APP_PATH; ?>default/payment_details">Payment Details</a></li>
											<li><hr class="dropdown-divider"></li>
											<li><a class="dropdown-item" href="<?php echo APP_PATH; ?>default/logout">Logout</a></li>
										</ul>
									</li>
								<?php else: ?>
									<li class="dropdown">
										<a class="dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
											Account
										</a>
										<ul class="dropdown-menu">
											<li><a class="dropdown-item" href="<?php echo APP_PATH; ?>enroll/login">Login</a></li>
											<li><a class="dropdown-item" href="<?php echo APP_PATH; ?>enroll/register">Register</a></li>
										</ul>
									</li>
								<?php endif; ?>
							</ul>
						</div>
						<!-- Mini Gallery Column -->
						<div class="col-lg-7 col-md-12">
							<h3 class="gallery-title">~ Mini Gallery ~</h3>
							<div class="mini-gallery">
								<!-- Embed 1 -->
								<blockquote class="instagram-media ig-embed" data-instgrm-permalink="https://www.instagram.com/p/CfgyXYBhkOR/" data-instgrm-version="14"></blockquote>

								<!-- Embed 2 (Fixed Size) -->
								<blockquote class="instagram-media ig-embed" data-instgrm-permalink="https://www.instagram.com/p/CMolJKMBXdQ/" data-instgrm-version="14"></blockquote>

								<!-- Embed 3 -->
								<blockquote class="instagram-media ig-embed" data-instgrm-permalink="https://www.instagram.com/p/CGjqzHTBAXt/" data-instgrm-version="14"></blockquote>

								<!-- Embed 4 -->
								<blockquote class="instagram-media ig-embed" data-instgrm-permalink="https://www.instagram.com/p/B5jzN77hiHF/" data-instgrm-version="14"></blockquote>

								<script async src="//www.instagram.com/embed.js"></script>
							</div>
						</div>
					</div>
				</div>

				<!-- Separator Line -->
				<hr class="footer-separator">

				<!-- Footer Bottom: Clickable Link -->
				<div class="copyright">
					<div class="container">
						<div class="row">
							<div class="col-md-8 offset-md-2 text-center">
								<p>
									<a href="<?php echo APP_PATH; ?>default/home" class="footer-link">
										Jen's House - 2025
									</a>
								</p>
							</div>
						</div>
					</div>
				</div>
			</div>
		</footer>
	</body>
</html>