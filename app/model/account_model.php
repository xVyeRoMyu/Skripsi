<?php
	class account_model {
		private $db;
		
		public function __construct() {
			$this->db = new Database;
			if (!$this->db || !$this->db->getConnection()) {
				throw new Exception("Database connection failed.");
			}
		}

		public function registerCustomer($data) {
			$requiredFields = ['cus_name', 'cus_email', 'cus_phone', 'cus_password'];
			foreach ($requiredFields as $field) {
				if (empty(trim($data[$field]))) {
					error_log("Registration failed: Required field '$field' is empty.");
					return false;
				}
			}

			if (!filter_var($data['cus_email'], FILTER_VALIDATE_EMAIL)) {
				error_log("Registration failed: Invalid email format.");
				return false;
			}

			$hashedPassword = password_hash($data['cus_password'], PASSWORD_DEFAULT);
			
			$sql = "INSERT INTO tbl_customer (name, email, phone, password) VALUES (?, ?, ?, ?)";
			$stmt = $this->db->prepare($sql);
			if (!$stmt) {
				error_log("Registration statement preparation failed: " . $this->db->getConnection()->error);
				return false;
			}
			
			$stmt->bind_param("ssss", $data['cus_name'], $data['cus_email'], $data['cus_phone'], $hashedPassword);
			return $stmt->execute();
		}

		public function loginCustomer($email, $password) {
			if (empty(trim($email))) {
				throw new Exception("Email is required");
			}
			if (empty(trim($password))) {
				throw new Exception("Password is required");
			}
			
			$sql = "SELECT customer_id AS id, name, email, phone, password FROM tbl_customer WHERE email = ? LIMIT 1";
			$stmt = $this->db->prepare($sql);
			if (!$stmt) {
				throw new Exception("Database error: " . $this->db->getConnection()->error);
			}
			
			$stmt->bind_param("s", $email);
			if (!$stmt->execute()) {
				throw new Exception("Database error: " . $stmt->error);
			}
			
			$result = $stmt->get_result();
			if ($result->num_rows !== 1) {
				return false;
			}
			
			$user = $result->fetch_assoc();
			if (!password_verify($password, $user['password'])) {
				return false;
			}
			
			return [
				'id' => $user['id'],
				'name' => $user['name'],
				'email' => $user['email'],
				'phone' => $user['phone'],
				'role' => 'customer'
			];
		}

		public function loginOperator($email, $password) {
			if (empty(trim($email)) || empty(trim($password))) {
				return false;
			}
			
			$sql = "SELECT operator_id AS id, name, email, password FROM tbl_operator WHERE email = ? LIMIT 1";
			$stmt = $this->db->prepare($sql);
			if (!$stmt) {
				error_log("Operator login failed: " . $this->db->getConnection()->error);
				return false;
			}
			
			$stmt->bind_param("s", $email);
			if (!$stmt->execute()) {
				error_log("Operator login failed: " . $stmt->error);
				return false;
			}
			
			$result = $stmt->get_result();
			if ($result->num_rows !== 1) return false;
			
			$operator = $result->fetch_assoc();
			return password_verify($password, $operator['password']) ? [
				'id' => $operator['id'],
				'name' => $operator['name'],
				'email' => $operator['email'],
				'role' => 'operator'
			] : false;
		}
		
		public function loginAdmin($email, $password) {
			if (empty(trim($email)) || empty(trim($password))) {
				return false;
			}
			
			$sql = "SELECT admin_id AS id, name, email, password FROM tbl_admin WHERE email = ? LIMIT 1";
			$stmt = $this->db->prepare($sql);
			if (!$stmt) {
				error_log("Admin login failed: " . $this->db->getConnection()->error);
				return false;
			}
			
			$stmt->bind_param("s", $email);
			if (!$stmt->execute()) {
				error_log("Admin login failed: " . $stmt->error);
				return false;
			}
			
			$result = $stmt->get_result();
			if ($result->num_rows !== 1) return false;
			
			$admin = $result->fetch_assoc();
			return password_verify($password, $admin['password']) ? [
				'id' => $admin['id'],
				'name' => $admin['name'],
				'email' => $admin['email'],
				'role' => 'admin'
			] : false;
		}

		public function getCustomerById($customerId) {
			$sql = "SELECT customer_id AS id, name, email, phone FROM tbl_customer WHERE customer_id = ? LIMIT 1";
			$stmt = $this->db->prepare($sql);
			
			if (!$stmt) {
				error_log("Customer query failed: " . $this->db->getConnection()->error);
				return false;
			}
			
			$stmt->bind_param("s", $customerId);
			if (!$stmt->execute()) {
				error_log("Customer query failed: " . $stmt->error);
				return false;
			}
			
			$result = $stmt->get_result();
			return $result->fetch_assoc();
		}
	}

	// AJAX Handlers
	if (isset($_POST['action'])) {
		header('Content-Type: application/json');
		$model = new account_model();
		$response = ['status' => 'FAIL', 'error' => 'Unknown error'];

		try {
			if (session_status() === PHP_SESSION_NONE) {
				session_start();
			}
			
			switch ($_POST['action']) {
				case 'register':
					$data = [
						'cus_name'     => $_POST['cus_name'] ?? '',
						'cus_email'    => $_POST['cus_email'] ?? '',
						'cus_phone'    => $_POST['cus_phone'] ?? '',
						'cus_password' => $_POST['cus_password'] ?? ''
					];
					$response['status'] = $model->registerCustomer($data) ? 'OK' : 'FAIL';
					break;
					
				case 'login_customer':
					$email = $_POST['email'] ?? '';
					$password = $_POST['password'] ?? '';
					if (empty($email) || empty($password)) {
						$response['error'] = 'Email and password are required';
						break;
					}
					
					$customerData = $model->loginCustomer($email, $password);
					if ($customerData) {
						$_SESSION['user'] = $customerData;
						$_SESSION['customer_id'] = $customerData['id'];
						$response = [
							'status' => 'OK',
							'redirect' => APP_PATH . 'default/home'
						];
					} else {
						$response['error'] = 'Invalid email or password';
					}
					break;
					
				case 'login_operator':
					$email = $_POST['email'] ?? '';
					$password = $_POST['password'] ?? '';
					if (empty($email) || empty($password)) {
						$response['error'] = 'Email and password are required';
						break;
					}
					
					$operatorData = $model->loginOperator($email, $password);
					if ($operatorData) {
						$_SESSION['user'] = $operatorData;
						$_SESSION['operator'] = $operatorData['name']; // Add this line
						$_SESSION['operator_email'] = $operatorData['email']; // Add this line
						$response = [
							'status' => 'OK',
							'redirect' => APP_PATH . 'payment_history/cus_history'
						];
					} else {
						$response['error'] = 'Invalid email or password';
					}
					break;
					
				case 'login_admin':
					$email = $_POST['email'] ?? '';
					$password = $_POST['password'] ?? '';
					if (empty($email) || empty($password)) {
						$response['error'] = 'Email and password are required';
						break;
					}
					
					$adminData = $model->loginAdmin($email, $password);
					if ($adminData) {
						$_SESSION['user'] = $adminData;
						$response = [
							'status' => 'OK',
							'redirect' => APP_PATH . 'default/reservation'
						];
					} else {
						$response['error'] = 'Invalid email or password';
					}
					break;
					
				default:
					$response['error'] = 'Invalid action';
			}
		} catch (Exception $e) {
			$response['error'] = $e->getMessage();
		}

		echo json_encode($response);
		exit;
	}
?>