<?php
	class active_booking extends Controller {
		public function __construct() {
			session_start();
			if (!isset($_SESSION['operator'])) {
				header("Location: " . APP_PATH . "enroll/login");
				exit;
			}
		}
		
		public function index($name = "Alex", $email = "s22110055@student.unklab.ac.id") {
			$arr_data['title'] = "Current Active Booking";
			$arr_data['customer'] = $this->logic("active_booking_model")->getAllDataCustomer();
			$this->display('template/operator/header', $arr_data);
			$this->display("active_booking/cus_active", $arr_data);
			$this->display('template/operator/footer');
		}
		
		public function payment_history($name = "Alex", $email = "s22110055@student.unklab.ac.id") {
			$arr_data['title'] = "Customer History";
			$arr_data['customer'] = $this->logic("payment_history_model")->getAllDataCustomer();
			$this->display('template/operator/header', $arr_data);
			$this->display("payment_history/cus_history", $arr_data);
			$this->display('template/operator/footer');
		}
		
		public function insert() {
			$result = $this->logic("active_booking_model")->insertDataCustomer($_POST);
			if ($result === true) {
				header('Location: ' . APP_PATH . 'active_booking');
				exit;
			} elseif ($result === "duplicate") {
				echo "<script>
						alert('Invoice number already exists, please asign another invoice number.');
						window.location.href='" . APP_PATH . "active_booking';
					  </script>";
				exit;
			} else {
				echo "Error inserting customer data.";
			}
		}
		
		public function delete($invoice) {
			if ($this->logic("active_booking_model")->deleteDataCustomer($invoice) == true) {
				header('Location: ' . APP_PATH . 'active_booking');
				exit;
			} else {
				echo "Error deleting customer data.";
			}
		}
		
		public function update() {
			if ($this->logic("active_booking_model")->updateDataCustomer($_POST) == true) {
				header('Location: ' . APP_PATH . 'active_booking');
				exit;
			} else {
				echo "Error updating customer data.";
			}
		}
		
		public function accept($invoice) {
			if (!isset($_SESSION['operator'])) {
				header('Location: ' . APP_PATH . 'auth/login');
				exit;
			}

			// Use the framework's logic() method instead of direct instantiation
			$activeModel = $this->logic("active_booking_model");
			$historyModel = $this->logic("payment_history_model");
			
			// Get the booking data
			$booking = $activeModel->getBookingByInvoice($invoice);
			
			if(!$booking) {
				$_SESSION['error'] = "Booking not found";
				header('Location: ' . APP_PATH . 'active_booking');
				exit;
			}

			// Prepare data for history
			$historyData = [
				'cus_invoice' => $booking['invoice'],
				'cus_name' => $booking['name'],
				'cus_email' => $booking['email'],
				'cus_phone' => $booking['phone'],
				'cus_payment' => $booking['payment'],
				'cus_date' => $booking['date'],
				'cus_details' => $booking['details']
			];
			
			// Process the transfer
			if ($historyModel->insertDataCustomer($historyData)) {
				if ($activeModel->deleteDataCustomer($invoice)) {
					$_SESSION['success'] = "Booking successfully moved to history";
				} else {
					$_SESSION['error'] = "Booking moved to history but failed to remove from active";
				}
			} else {
				$_SESSION['error'] = "Failed to move booking to history";
			}
			
			header('Location: ' . APP_PATH . 'active_booking');
			exit;
		}

		public function get_phone($invoice) {
			$booking = $this->logic("active_booking_model")->getBookingByInvoice($invoice);
			if ($booking) {
				header('Content-Type: application/json');
				echo json_encode(['phone' => $booking['phone']]);
			} else {
				http_response_code(404);
				echo json_encode(['error' => 'Booking not found']);
			}
		}
	}
?>