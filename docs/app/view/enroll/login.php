	<body>
		<img src="<?php echo APP_PATH; ?>/img/Hotel.png" alt="Background" class="bg-image">
		<header class="top-bar">
			<a href="<?php echo APP_PATH; ?>">
				<img src="<?php echo APP_PATH; ?>/img/Logo.png" alt="Hotel Logo" class="hotel-logo">
			</a>
		</header>
		<div class="wrapper">
			<div class="header header-flex">
				<h2>Login</h2><h3>as</h3>
				<div class="custom-dropdown">
					<button class="dropdown-btn">Customer</button>
						<ul class="dropdown-menu">
							<li class="dropdown-item" data-value="customer">Customer</li>
							<li class="dropdown-item" data-value="operator">Operator</li>
							<li class="dropdown-item" data-value="admin">Admin</li>
						</ul>
					<input type="hidden" id="roleSelect" name="role" value="customer">
				</div>
				
			</div>
			<form id="loginForm" method="post" action="">
				<input type="hidden" name="role" id="hiddenRoleInput" value="customer" />
				<div class="input-field-top">
					<input type="email" id="email" name="email" placeholder=" " required spellcheck="false" autocomplete="off">
					<label for="email">Email:</label>
				</div>
				<div class="input-field password-field">
					<input type="password" id="pass" name="password" placeholder=" " required spellcheck="false" autocomplete="off">
					<label for="pass">Password:</label>
					<span class="show-password-toggle" onclick="togglePassword(this)" tabindex="0">Show</span>
				</div>
				<div class="button-row">
					<button type="button" class="btn-accept" onclick="validateLogin()">Login</button>
					<button type="button" class="btn-accept" id="registerBtn" onclick="window.location.href='<?php echo APP_PATH; ?>enroll/register'">No account? Register here</button>
					<button type="button" class="btn-back" onclick="window.location.href='<?php echo APP_PATH; ?>'">Back</button>
				</div>
			</form>
		</div>
		<div class="register-tooltip" id="registerTooltip"></div>
		<!-- Error Popup -->
		<div class="popup-overlay" id="popup" style="display:none;">
			<div class="popup-content" style="min-width: 300px; min-height: 125px; position: relative;">
				<p id="popupText"></p>
				<div class="popup-buttons" id="popupButtons">
					<button class="btn-no" onclick="closePopup()">OK</button>
				</div>
			</div>
		</div>
		<!-- Confirmation Popup -->
		<div class="confirm-popup-overlay" id="confirmPopup" style="display:none;">
			<div class="confirm-popup-content" style="min-width: 300px; min-height: 125px; position: relative;">
				<p id="confirmPopupText"></p>
				<div class="confirm-popup-buttons" id="confirmPopupButtons">
					<button class="btn-yes" id="confirmYes">Yes</button>
					<button class="btn-no" id="confirmNo">No</button>
				</div>
			</div>
		</div>
		<script src="<?php echo APP_PATH; ?>js/login/login.js"></script>
		<script>
			<?php if (!empty($error)): ?>
				showPopup("<?php echo $error; ?>");
			<?php endif; ?>
		</script>
	</body>
</html>
