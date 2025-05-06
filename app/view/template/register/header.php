<?php
	if (session_status() !== PHP_SESSION_ACTIVE) {
		session_start();
	}
	if (isset($_POST['action']) && $_POST['action'] === 'register') {
		require_once __DIR__ . '/../../../model/account_model.php';
		$model = new account_model();
		$data = [
			'cus_name'     => $_POST['cus_name'] ?? '',
			'cus_email'    => $_POST['cus_email'] ?? '',
			'cus_phone'    => $_POST['cus_phone'] ?? '',
			'cus_password' => $_POST['cus_password'] ?? ''
		];

		$success = $model->registerCustomer($data);
		header('Content-Type: application/json');
		echo json_encode(["status" => $success ? "OK" : "FAIL"]);
		exit;
	}
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<title>Customer Registration</title>
		<link rel="stylesheet" href="<?php echo APP_PATH; ?>/css/accountstyle/style.css">
		<!-- Add intl-tel-input CSS -->
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/css/intlTelInput.css">
	</head>
	<body>
		<img src="<?php echo APP_PATH; ?>/img/Hotel.png" alt="Background" class="bg-image">
		<header class="top-bar">
			<a href="<?php echo APP_PATH; ?>">
				<img src="<?php echo APP_PATH; ?>/img/Logo.png" alt="Hotel Logo" class="hotel-logo">
			</a>
		</header>
