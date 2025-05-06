<?php
session_start();
	$loggedIn = isset($_SESSION['user']); // Check generic 'user' session
	$userRole = $loggedIn ? $_SESSION['user']['role'] : null;
	$name = $loggedIn ? $_SESSION['user']['name'] : 'Guest';
?>
	<body class="<?php echo $userRole === 'admin' ? 'admin-logged-in' : ''; ?>">
		<!-- Mobile Sidebar Menu Button -->
		<button class="sidebar-menu-button">
			<span class="material-symbols-rounded">menu</span>
		</button>
		<aside class="sidebar collapsed">
			<!-- Sidebar Header -->
			<header class="sidebar-header">
				<div class="header-logo">
					<a aria-current="page" href="<?php echo APP_PATH; ?>default/home">
						<img src="<?php echo APP_PATH; ?>/img/Logo.png" alt="Hotel Logo" class="hotel-logo">
					</a>
					<a href="<?php echo APP_PATH; ?>default/home" class="brand-name">Jen's House</a>
				</div>
				<button class="sidebar-toggler">
					<span class="material-symbols-rounded">chevron_left</span>
				</button>
			</header>
			<nav class="sidebar-nav">
				<ul class="nav-list primary-nav">
					<li class="nav-item">
						<a class="nav-link" href="<?php echo APP_PATH; ?>default/home">
							<span class="material-symbols-rounded">dashboard</span>
							<span class="nav-label">Home</span>
						</a>
						<div class="tooltip-wrapper">
							<div class="dropdown-menu-tooltip">
								<span class="tooltip-text">Home</span>
							</div>
						</div>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="<?php echo APP_PATH; ?>default/faq">
							<span class="material-symbols-rounded">quiz</span>
							<span class="nav-label">FAQ</span>
						</a>
						<div class="tooltip-wrapper">
							<div class="dropdown-menu-tooltip">
								<span class="tooltip-text">FAQ</span>
							</div>
						</div>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="<?php echo APP_PATH; ?>default/home#contact-section">
							<span class="material-symbols-rounded">person</span>
							<span class="nav-label">Contact</span>
						</a>
						<div class="tooltip-wrapper">
							<div class="dropdown-menu-tooltip">
								<span class="tooltip-text">Contact Us</span>
							</div>
						</div>
					</li>
					<li class="nav-item">
						<div class="nav-main-title">Specific Searching Menu</div>
						<div class="filter-options">
							<div class="filter-option">
								<input type="radio" id="filter-hotel" name="filter-type" value="hotel">
								<label for="filter-hotel">
									<span class="material-symbols-rounded filter-icon">hotel</span>
									<span class="filter-label">Show Only Hotel</span>
								</label>
							</div>
							<div class="filter-option">
								<input type="radio" id="filter-housing" name="filter-type" value="housing">
								<label for="filter-housing">
									<span class="material-symbols-rounded filter-icon">house</span>
									<span class="filter-label">Show Only Housing</span>
								</label>
							</div>
							<div class="filter-option">
								<input type="radio" id="filter-venue" name="filter-type" value="venue">
								<label for="filter-venue">
									<span class="material-symbols-rounded filter-icon">event</span>
									<span class="filter-label">Show Only Venue</span>
								</label>
							</div>
							<div class="filter-option">
								<input type="radio" id="filter-all" name="filter-type" value="all" checked>
								<label for="filter-all">
									<span class="material-symbols-rounded filter-icon">all_inclusive</span>
									<span class="filter-label">Show All</span>
								</label>
							</div>
							<div class="price-range-container" style="display: none;">
								<div class="price-range-slider"></div>
								<div class="price-range-values">
									<span class="min-price">IDR 0</span>
									<span class="max-price">IDR 0</span>
								</div>
								
								<!-- Housing plan options -->
								<div class="housing-plan-options" style="display: none;">
									<div class="housing-plan-option">
										<input type="radio" id="housing-plan-all" name="housing-plan" value="all" checked>
										<label for="housing-plan-all">All Plans</label>
									</div>
									<div class="housing-plan-option">
										<input type="radio" id="housing-plan-monthly" name="housing-plan" value="monthly">
										<label for="housing-plan-monthly">Monthly</label>
									</div>
									<div class="housing-plan-option">
										<input type="radio" id="housing-plan-quarterly" name="housing-plan" value="quarterly">
										<label for="housing-plan-quarterly">Quarterly</label>
									</div>
									<div class="housing-plan-option">
										<input type="radio" id="housing-plan-yearly" name="housing-plan" value="yearly">
										<label for="housing-plan-yearly">Yearly</label>
									</div>
								</div>
							</div>
						</div>
					</li>
				</ul>
				<ul class="nav-list secondary-nav">
					<li class="nav-item dropdown">
						<a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
							<span class="material-symbols-rounded"><?php echo $loggedIn ? 'account_circle' : 'login'; ?></span>
							<span class="nav-label"><?php echo $loggedIn ? htmlspecialchars($name) : 'Account'; ?></span>
						</a>
						<div class="tooltip-wrapper">
							<div class="dropdown-menu-tooltip">
								<span class="tooltip-text">Account Menu</span>
							</div>
						</div>
						<ul class="dropdown-menu">
							<?php if ($loggedIn): ?>
								<?php if ($userRole === 'customer'): ?>
									<li>
										<a class="dropdown-item" href="<?php echo APP_PATH; ?>default/details">
											<span class="material-symbols-rounded">receipt</span>
											<span>Booking Details</span>
										</a>
									</li>
									<li><hr class="dropdown-divider"></li>
								<?php endif; ?>
								<li>
									<a class="dropdown-item" href="<?php echo APP_PATH; ?>default/logout">
										<span class="material-symbols-rounded">logout</span>
										<span>Logout</span>
									</a>
								</li>
							<?php else: ?>
								<li>
									<a class="dropdown-item" href="<?php echo APP_PATH; ?>enroll/login">
										<span class="material-symbols-rounded">login</span>
										<span>Login</span>
									</a>
								</li>
								<li>
									<a class="dropdown-item" href="<?php echo APP_PATH; ?>enroll/register">
										<span class="material-symbols-rounded">person_add</span>
										<span>Register</span>
									</a>
								</li>
							<?php endif; ?>
						</ul>
					</li>
				</ul>
			</nav>
		</aside>
		<!-- Reservation Section -->
		<section class="reservation-content">
			<div class="hotel-section section-container">
				<div class="container reservation-container">
					<div class="section-header"> <!-- Added header -->
						<h2>Hotel Rooms</h2>
						<a class="nav-link" href="<?php echo APP_PATH; ?>default/faq#hotel-faq"><b>Further hotel details can be found here</b></a>
					</div>
					<div class="row">
						<div class="col-md-8 mb-4">
							<!-- Hotel Room 1 -->
							<div class="hotel-section-1">
								<div class="room-image mb-3">
									<div class="wrapper" role="group" aria-label="Room image slider">
										<input type="radio" name="slide-1" id="slide-1-one" checked>
										<input type="radio" name="slide-1" id="slide-1-two">
										<input type="radio" name="slide-1" id="slide-1-three">
										<!-- Slides -->
										<div class="img img-1">
											<img src="<?php echo APP_PATH; ?>/img/kingbed1a.png" alt="Room1">
										</div>
										<div class="img img-2">
											<img src="<?php echo APP_PATH; ?>/img/kingbed1b.png" alt="Room1">
										</div>
										<div class="img img-3 video-container">
											<video muted loop playsinline>
												<source src="<?php echo APP_PATH; ?>/vid/kingbed1.mp4" type="video/mp4">
											</video>
										</div>
										<!-- Navigation Arrows -->
										<div class="navigation-arrows">
											<button class="arrow prev" aria-label="Previous slide">❮</button>
											<button class="arrow next" aria-label="Next slide">❯</button>
										</div>
										<!-- Slider Dots -->
										<div class="sliders">
											<label for="slide-1-one" class="one"></label>
											<label for="slide-1-two" class="two"></label>
											<label for="slide-1-three" class="three"></label>
										</div>
									</div>
								</div>
								<div class="room-info">
									<h3>Single bed &mdash; 2 person room</h3>
									<div class="rate-item" data-booking-type="hotel-1" data-room-name="Hotel Room A">
										<div class="rate-details">
											<h4>Hotel Room A</h4>
											<ul>
												<li>Pay later or down-payment not available</li>
												<li>Air conditioning</li>
											</ul>
										</div>
										<div class="rate-price">IDR 300,000/day</div>
										<button class="book-btn">Book Now</button>
									</div>
								</div>
							</div>
							<!-- Hotel Room 2 -->
							<div class="hotel-section-2">
								<div class="room-image mb-3">
									<div class="wrapper" role="group" aria-label="Room image slider">
										<input type="radio" name="slide-2" id="slide-2-one" checked>
										<input type="radio" name="slide-2" id="slide-2-two">
										<input type="radio" name="slide-2" id="slide-2-three">								
										<!-- Slides -->
										<div class="img img-1">
											<img src="<?php echo APP_PATH; ?>/img/kingbed2.png" alt="Room2">
										</div>
										<div class="img img-2 video-container">
											<img src="<?php echo APP_PATH; ?>/img/loading.gif" alt="Loading animation">
										</div>	
										<div class="img img-3 video-container">
											<img src="<?php echo APP_PATH; ?>/img/loading.gif" alt="Loading animation">
										</div>						
										<!-- Navigation Arrows -->
										<div class="navigation-arrows">
											<button class="arrow prev" aria-label="Previous slide">❮</button>
											<button class="arrow next" aria-label="Next slide">❯</button>
										</div>							
										<!-- Slider Dots -->
										<div class="sliders">
											<label for="slide-2-one" class="one"></label>
											<label for="slide-2-two" class="two"></label>
											<label for="slide-2-three" class="three"></label>
										</div>
									</div>
								</div>
								<div class="room-info">
									<h3>Single bed &mdash; 2 person room</h3>
									<div class="rate-item" data-booking-type="hotel-2" data-room-name="Hotel Room B">
										<div class="rate-details">
											<h4>Hotel Room B</h4>
											<ul>
												<li>Pay later or down-payment not available</li>
												<li>Air conditioning not available</li>
											</ul>
										</div>
										<div class="rate-price">IDR 200,000/day</div>
										<button class="book-btn">Book Now</button>
									</div>
								</div>
							</div>
							<div class="hotel-section-3">
								<div class="room-image mb-3">
									<div class="wrapper" role="group" aria-label="Room image slider">
										<input type="radio" name="slide-3" id="slide-3-one" checked>
										<input type="radio" name="slide-3" id="slide-3-two">
										<input type="radio" name="slide-3" id="slide-3-three">								
										<!-- Slides -->
										<div class="img img-1">
											<img src="<?php echo APP_PATH; ?>/img/kingbed3.png" alt="Room3">
										</div>
										<div class="img img-2 video-container">
											<img src="<?php echo APP_PATH; ?>/img/loading.gif" alt="Loading animation">
										</div>	
										<div class="img img-3 video-container">
											<img src="<?php echo APP_PATH; ?>/img/loading.gif" alt="Loading animation">
										</div>						
										<!-- Navigation Arrows -->
										<div class="navigation-arrows">
											<button class="arrow prev" aria-label="Previous slide">❮</button>
											<button class="arrow next" aria-label="Next slide">❯</button>
										</div>							
										<!-- Slider Dots -->
										<div class="sliders">
											<label for="slide-3-one" class="one"></label>
											<label for="slide-3-two" class="two"></label>
											<label for="slide-3-three" class="three"></label>
										</div>
									</div>
								</div>
								<div class="room-info">
									<h3>Single bed &mdash; 2 person room</h3>
									<div class="rate-item" data-booking-type="hotel-3" data-room-name="Hotel Room C">
										<div class="rate-details">
											<h4>Hotel Room C</h4>
											<ul>
												<li>Cancelable</li>
												<li>Pay later or down-payment not available</li>
												<li>Air conditioning not available</li>
											</ul>
										</div>
										<div class="rate-price">IDR 225,000/day</div>
										<button class="book-btn">Book Now</button>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
		<!-- Contracted Housing Section -->
		<section class="contracted-housing">
			<div class="housing-section section-container">
				<div class="container contracted-container">
					<div class="section-header"> <!-- Added header -->
						<h2>Contracted Housing</h2>
						<a class="nav-link" href="<?php echo APP_PATH; ?>default/faq#kost-faq"><b>Further contracted house details can be found here</b></a>
					</div>
					<div class="row">
						<div class="col-md-8 mb-4">
							<!-- Housing 1 -->
							<div class="housing-section-1">
								<div class="unit-image mb-3">
									<div class="wrapper" role="group" aria-label="Housing image slider">
										<input type="radio" name="housing-1-slide" id="housing-1-one" checked>
										<input type="radio" name="housing-1-slide" id="housing-1-two">
										<input type="radio" name="housing-1-slide" id="housing-1-three">								
										<!-- Slides -->
										<div class="img img-1">
											<img src="<?php echo APP_PATH; ?>/img/Kost.png" alt="Housing 1">
										</div>
										<div class="img img-2">
											<img src="<?php echo APP_PATH; ?>/img/kost1b.png" alt="Housing 1">
										</div>
										<div class="img img-3 video-container">
											<img src="<?php echo APP_PATH; ?>/img/loading.gif" alt="Loading animation">
										</div>
										<!-- Navigation Arrows -->
										<div class="navigation-arrows">
											<button class="arrow prev" aria-label="Previous slide">❮</button>
											<button class="arrow next" aria-label="Next slide">❯</button>
										</div>
										<!-- Slider Dots -->
										<div class="sliders">
											<label for="housing-1-one" class="one"></label>
											<label for="housing-1-two" class="two"></label>
											<label for="housing-1-three" class="three"></label>
										</div>
									</div>
								</div>
								<div class="unit-info">
									<h3>House A &mdash; Family House</h3>
								</div>
								<div class="plan-item" data-booking-type="housing-1-monthly">
									<div class="plan-details">
										<h4>Monthly Plan</h4>
										<ul>
											<li>Fully furnished house</li>
											<li>Utilities not included</li>
											<li>Minimum 1 month stay</li>
											<li>50% Down-payment available</li>
										</ul>
									</div>
									<div class="plan-price">IDR 1,000,000/month</div>
									<button class="book-btn">Book Now</button>
								</div>
								<div class="plan-item" data-booking-type="housing-1-quarterly">
									<div class="plan-details">
										<h4>Quarterly Plan</h4>
										<ul>
											<li>Fully furnished house</li>
											<li>Utilities not included</li>
											<li>Minimum 3 months stay</li>
											<li>5% discount quarterly commitment</li>
											<li>50% Down-payment available</li>
										</ul>
									</div>
									<div class="plan-price">IDR 2,850,000/quarterly</div>
									<button class="book-btn">Book Now</button>
								</div>
								<div class="plan-item" data-booking-type="housing-1-yearly">
									<div class="plan-details">
										<h4>Yealy Plan</h4>
										<ul>
											<li>Fully furnished house</li>
											<li>Utilities not included</li>
											<li>20% discount for yearly commitment</li>
											<li>50% Down-payment available</li>
										</ul>
									</div>
									<div class="plan-price">IDR 9,600,000/yearly</div>
									<button class="book-btn">Book Now</button>
								</div>
							</div>
							<!-- Housing 2 -->
							<div class="housing-section-2">
								<div class="unit-image mb-3">
									<div class="wrapper" role="group" aria-label="Housing image slider">
										<input type="radio" name="housing-2-slide" id="housing-2-one" checked>
										<input type="radio" name="housing-2-slide" id="housing-2-two">
										<input type="radio" name="housing-2-slide" id="housing-2-three">								
										<!-- Slides -->
										<div class="img img-1">
											<img src="<?php echo APP_PATH; ?>/img/kost2a.png" alt="Housing 2">
										</div>
										<div class="img img-2 video-container">
											<img src="<?php echo APP_PATH; ?>/img/loading.gif" alt="Loading animation">
										</div>
										<div class="img img-3 video-container">
											<img src="<?php echo APP_PATH; ?>/img/loading.gif" alt="Loading animation">
										</div>								
										<!-- Navigation Arrows -->
										<div class="navigation-arrows">
											<button class="arrow prev" aria-label="Previous slide">❮</button>
											<button class="arrow next" aria-label="Next slide">❯</button>
										</div>								
										<!-- Slider Dots -->
										<div class="sliders">
											<label for="housing-2-one" class="one"></label>
											<label for="housing-2-two" class="two"></label>
											<label for="housing-2-three" class="three"></label>
										</div>
									</div>
								</div>
								<div class="unit-info">
									<h3>House B &mdash; Family House</h3>
								</div>
								<div class="plan-item" data-booking-type="housing-2-monthly">
									<div class="plan-details">
										<h4>Monthly Plan</h4>
										<ul>
											<li>Fully furnished house</li>
											<li>Utilities not included</li>
											<li>Minimum 1 month stay</li>
											<li>50% Down-payment available</li>
										</ul>
									</div>
									<div class="plan-price">IDR 800,000/month</div>
									<button class="book-btn">Book Now</button>
								</div>
								<div class="plan-item" data-booking-type="housing-2-quarterly">
									<div class="plan-details">
										<h4>Quarterly Plan</h4>
										<ul>
											<li>Fully furnished house</li>
											<li>Utilities not included</li>
											<li>Minimum 3 months stay</li>
											<li>5% discount quarterly commitment</li>
											<li>50% Down-payment available</li>
										</ul>
									</div>
									<div class="plan-price">IDR 2,280,000/quarterly</div>
									<button class="book-btn">Book Now</button>
								</div>
								<div class="plan-item" data-booking-type="housing-2-yearly">
									<div class="plan-details">
										<h4>Yealy Plan</h4>
										<ul>
											<li>Fully furnished house</li>
											<li>Utilities not included</li>
											<li>20% discount for yearly commitment</li>
											<li>50% Down-payment available</li>
										</ul>
									</div>
									<div class="plan-price">IDR 7,680,000/yearly</div>
									<button class="book-btn">Book Now</button>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
		<!-- Venue Section -->
		<section class="venue-section">
			<div class="container section-container">
				<div class="section-header">
					<h2>Event Venues</h2>
					<a class="nav-link" href="<?php echo APP_PATH; ?>default/faq#venue-faq"><b>Further venue details can be found here</b></a>
				</div>
				<div class="venue-options">
					<!-- Venue 1 -->
					<div class="venue-option" data-booking-type="venue">
						<div class="unit-image mb-3">
							<div class="wrapper" role="group" aria-label="Housing image slider">
								<input type="radio" name="venue-1-slide" id="venue-1-one" checked>
								<input type="radio" name="venue-1-slide" id="venue-1-two">
								<input type="radio" name="venue-1-slide" id="venue-1-three">								
								<!-- Slides -->
								<div class="img img-1 video-container">
									<video muted loop playsinline>
										<source src="<?php echo APP_PATH; ?>/vid/venue1.mp4" type="video/mp4">
									</video>
								</div>
								<div class="img img-2 video-container">
									<video muted loop playsinline>
										<source src="<?php echo APP_PATH; ?>/vid/venue2.mp4" type="video/mp4">
									</video>
								</div>
								<div class="img img-3 video-container">
									<img src="<?php echo APP_PATH; ?>/img/loading.gif" alt="Loading animation">
								</div>															
								<!-- Slider Dots -->
								<div class="sliders">
									<label for="venue-1-one" class="one"></label>
									<label for="venue-1-two" class="two"></label>
									<label for="venue-1-three" class="three"></label>
								</div>
							</div>
						</div>
						<div class="venue-content">
							<h3>Grand Venue</h3>
							<p>Elegant space, perfect for weddings and galas.</p>
							<ul class="venue-features">
								<li>Capacity: 500 guests</li>
								<li>Comes in package with floral arrangements and dressing room</li>
								<li>Customizable lighting and venue colouring</li>
								<li>Catering available</li>
								<li>50% Down-payment available</li>
							</ul>
							<div class="venue-price">IDR 7,500,000</div>
							<button class="book-btn">Book Now</button>
						</div>
					</div>
					<!-- Venue 2 -->
					<div class="venue-option" data-booking-type="venue">
						<div class="unit-image mb-3">
							<div class="wrapper" role="group" aria-label="Housing image slider">
								<input type="radio" name="venue-2-slide" id="venue-2-one" checked>
								<input type="radio" name="venue-2-slide" id="venue-2-two">
								<input type="radio" name="venue-2-slide" id="venue-2-three">								
								<!-- Slides -->
								<div class="img img-1">
									<img src="<?php echo APP_PATH; ?>/img/venue2a.png" alt="Venue 2">
								</div>
								<div class="img img-2">
									<img src="<?php echo APP_PATH; ?>/img/venue2b.png" alt="Venue 2">
								</div>
								<div class="img img-3 video-container">
									<img src="<?php echo APP_PATH; ?>/img/loading.gif" alt="Loading animation">
								</div>															
								<!-- Slider Dots -->
								<div class="sliders">
									<label for="venue-2-one" class="one"></label>
									<label for="venue-2-two" class="two"></label>
									<label for="venue-2-three" class="three"></label>
								</div>
							</div>
						</div>
						<div class="venue-content">
							<h3>Executive and Social Events</h3>
							<p>Professional setting for corporate and social gatherings.</p>
							<ul class="venue-features">
								<li>Capacity: 500 guests</li>
								<li>LCD projectors and sound system available</li>
								<li>Catering available</li>
								<li>50% Down-payment available</li>
							</ul>
							<div class="venue-price">IDR 7,500,000</div>
							<button class="book-btn">Book Now</button>
						</div>
					</div>
					<!-- Venue 3 -->
					<div class="venue-option" data-booking-type="venue">
						<div class="unit-image mb-3">
							<div class="wrapper" role="group" aria-label="Housing image slider">
								<input type="radio" name="venue-3-slide" id="venue-3-one" checked>
								<input type="radio" name="venue-3-slide" id="venue-3-two">
								<input type="radio" name="venue-3-slide" id="venue-3-three">								
								<!-- Slides -->
								<div class="img img-1">
									<img src="<?php echo APP_PATH; ?>/img/venue3a.png" alt="Venue 2">
								</div>
								<div class="img img-2">
									<img src="<?php echo APP_PATH; ?>/img/venue3b.png" alt="Venue 2">
								</div>
								<div class="img img-3 video-container">
									<img src="<?php echo APP_PATH; ?>/img/loading.gif" alt="Loading animation">
								</div>															
								<!-- Slider Dots -->
								<div class="sliders">
									<label for="venue-3-one" class="one"></label>
									<label for="venue-3-two" class="two"></label>
									<label for="venue-3-three" class="three"></label>
								</div>
							</div>
						</div>
						<div class="venue-content">
							<h3>Birthday and Celebration Party</h3>
							<p>Flexible outdoor space suitable for parties and celebration.</p>
							<ul class="venue-features">
								<li>Capacity: 500 guests</li>
								<li>Custom decor options</li>
								<li>Catering available</li>
								<li>50% Down-payment available</li>
							</ul>
							<div class="venue-price">IDR 7,500,000</div>
							<button class="book-btn">Book Now</button>
						</div>
					</div>
				</div>
			</div>
		</section>
		<div class="calendar-modal" id="calendarModal">
			<div class="modal-content">
				<span class="close-calendar">&times;</span> <!-- Correct class -->
				<div class="calendar-content">
					<div class="calendar-container">
						<div class="months-container" id="calendarMonths">
						<!-- Months will be populated by JavaScript -->
						</div>
						<!-- Regular inputs (for hotel) -->
						<div class="inputs" id="regularInputs">
							<div class="input-group">
								<label for="checkIn">Check-In:</label>
								<input type="text" id="checkIn" class="date-input" readonly placeholder="DD/MM/YYYY">
							</div>
							<div class="input-group">
								<label for="checkOut">Check-Out:</label>
								<input type="text" id="checkOut" class="date-input" readonly placeholder="DD/MM/YYYY">
							</div>
							<div class="input-group">
								<label for="person">Person:</label>
								<div class="person-dropdown">
									<select id="person" class="person-select">
										<option value="1">1 Person</option>
										<option value="2">2 People</option>
										<option value="3">3 People</option>
										<option value="4">4 People</option>
									</select>
								</div>
							</div>
						</div>
						<!-- Venue inputs (only shown for venues) -->
						<div class="inputs" id="venueInputs">
							<div class="input-group">
								<label for="venueDate">Event Date:</label>
								<input type="text" id="venueDate" class="date-input" readonly placeholder="DD/MM/YYYY">
							</div>
							<div class="input-group venue-input-group">
								<label for="venueHeadcount">Number of Guests:</label>
								<input type="number" id="venueHeadcount" class="venue-headcount-input" min="100" max="500" placeholder="100-500 guests" required>
							</div>
							<div class="addons-container">
								<h4>Addons:</h4>
								<div class="addon-option">
									<input type="checkbox" id="venueFloral" name="venueFloral" checked disabled>
									<label for="venueFloral">Floral Arrangement (IDR 0)</label>
								</div>
								<div class="addon-option">
									<input type="checkbox" id="venueAngpao" name="venueAngpao">
									<label for="venueAngpao">Angpao Boxes (IDR 50,000)</label>
								</div>
								<div class="addon-option">
									<input type="checkbox" id="venueCake" name="venueCake">
									<label for="venueCake">Decorative Cake (IDR 150,000)</label>
								</div>
								<div class="addon-option">
									<input type="checkbox" id="venueProjector" name="venueProjector">
									<label for="venueProjector">Projector/LCD (IDR 150,000)</label>
								</div>
								<div class="addon-option">
									<input type="checkbox" id="venueDressing" name="venueDressing" checked disabled>
									<label for="venueDressing">Dressing Room (IDR 0)</label>
								</div>
								<div class="addon-option">
									<input type="checkbox" id="venueCatering" name="venueCatering">
									<label for="venueCatering">Food Catering (IDR 1,500,000 base + IDR 950,000 per 100 guests)</label>
								</div>
								<div class="addon-option">
									<input type="checkbox" id="venueSound" name="venueSound">
									<label for="venueSound">Sound System (IDR 275,000)</label>
								</div>
								<div class="addon-option">
									<input type="checkbox" id="venuePianist" name="venuePianist">
									<label for="venuePianist">Hire a Pianist (IDR 375,000)</label>
								</div>
							</div>
							<div class="downpayment-option">
								<input type="checkbox" id="venueDownpayment" name="venueDownpayment">
								<label for="venueDownpayment">50% Down Payment</label>
							</div>
						</div>
						<!-- Contracted housing inputs (only shown for housing) -->
						<div class="inputs" id="housingInputs">
							<div class="input-group">
								<label for="housingStartDate">Start Date:</label>
								<input type="text" id="housingStartDate" class="date-input" readonly placeholder="DD/MM/YYYY">
							</div>
							<div class="input-group">
								<label for="housingEndDate">End Date:</label>
								<input type="text" id="housingEndDate" class="date-input" readonly>
							</div>
							<div class="downpayment-option">
								<input type="checkbox" id="housingDownpayment" name="housingDownpayment">
								<label for="housingDownpayment">50% Down Payment</label>
							</div>
						</div>
						<div class="booking-footer">
							<button class="modal-btn cancel" id="cancelBooking">Cancel</button>
							<button class="modal-btn book" id="bookNow">Book now</button>
						</div>
					</div>
				</div>
			</div>
		</div>
		<section class="payment-summary-section">
			<div class="container">
				<div class="row">
					<div class="col-md-8">
						<h3>Your Selected Items</h3>
						<div id="selected-items-container" class="selected-items-container">
						<!-- Selected items will appear here -->
						</div>
					</div>
					<div class="col-md-4">
						<div class="payment-summary">
							<h4>Payment Summary</h4>
							<div id="payment-details" class="payment-details">
							<!-- Payment details will appear here -->
							</div>
							<div class="total">Grand Total: <span id="grand-total">IDR 0</span></div>
							<button class="btn-next">PROCEED TO PAYMENT</button>
						</div>
					</div>
				</div>
			</div>
		</section>
		<div class="admin-floating-btn">
			<?php if ($userRole === 'admin'): ?>
			<button class="btn-add">
				<span class="material-symbols-rounded">add</span>
			</button>
			<?php endif; ?>
		</div>