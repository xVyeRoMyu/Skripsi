		<!-- 1) jQuery (Full Version) -->
		<script src="<?php echo APP_PATH; ?>js/jquery.min.js"></script>
		  
		<!-- 2) Bootstrap Bundle (includes Popper.js) -->
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

		<!-- 3) intl-tel-input plugin (dependencies first) -->
		<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/intlTelInput.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/utils.js"></script>
		
		<!-- 4) Bootstrap JS -->
		<script src="<?php echo APP_PATH; ?>js/bootstrap.min.js"></script>
		<script src="<?php echo APP_PATH; ?>js/bootstrap.bundle.min.js"></script>
		  
		<!-- 5) Additional JS Files -->
		<script src="<?php echo APP_PATH; ?>js/jquery.mCustomScrollbar.concat.min.js"></script>
		<script src="<?php echo APP_PATH; ?>js/register/register.js"></script>
		<script>
			<?php if (!empty($error)): ?>
				showPopup("<?php echo $error; ?>");
			<?php endif; ?>
		</script>
	</body>
</html>
