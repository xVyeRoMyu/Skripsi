<div class="wrapper">
    <div class="header header-flex">
        <h2>Customer Registration</h2>
    </div>
    <form id="registrationForm" method="post" action="">
        <input type="hidden" name="role" id="hiddenRoleInput" value="customer">
        <div class="input-field-top">
            <input type="text" id="name" name="cus_name" placeholder=" " required spellcheck="false" autocomplete="off">
            <label for="name">Name:</label>
        </div>
        <div class="input-field-top">
            <input type="email" id="email" name="cus_email" placeholder=" " required spellcheck="false" autocomplete="off">
            <label for="email">Email:</label>
        </div>
        <div class="input-field-top phone-field">
            <input type="tel" id="phone" name="cus_phone" placeholder="823.." required spellcheck="false" autocomplete="off">
            <label for="phone" class="phone-number">Phone Number:</label>
        </div>
        <div class="input-field password-field">
            <input type="password" id="pass" name="cus_password" placeholder=" " required spellcheck="false" autocomplete="off">
            <label for="pass">Password:</label>
            <span class="show-password-toggle" onclick="togglePassword('pass', this)" tabindex="0">Show</span>
        </div>
        <div class="input-field password-field">
            <input type="password" id="confirm-pass" name="confirm_password" placeholder=" " required spellcheck="false" autocomplete="off">
            <label for="confirm-pass">Confirm Password:</label>
            <span class="show-password-toggle" onclick="togglePassword('confirm-pass', this)" tabindex="0">Show</span>
        </div>
        <div class="button-row">
            <button type="button" class="btn-accept" onclick="validateRegister()">Register</button>
            <button type="button" class="btn-back" onclick="window.location.href='<?php echo APP_PATH; ?>enroll/login'">Back</button>
        </div>
        <button type="submit" name="register_user" id="hiddenSubmit" style="display:none;"></button>
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
    </form>
</div>