<?php
    class payment_history extends Controller {
        public function __construct() {
            session_start();
            if (!isset($_SESSION['operator'])) {
                header("Location: " . APP_PATH . "enroll/login");
                exit;
            }
        }
		
		public function index($name = "name", $email = "email") {
			$arr_data['title'] = "Customer History";
			$arr_data['customer'] = $this->logic("payment_history_model")->getAllDataCustomer();
			$this->display('template/operator/header', $arr_data);
			$this->display("payment_history/cus_history", $arr_data);
			$this->display('template/operator/footer');
		}
		
		public function active_payment($name = "name", $email = "email") {
			$arr_data['title'] = "Current Active Booking";
			$arr_data['customer'] = $this->logic("active_booking_model")->getAllDataCustomer();
			$this->display('template/operator/header', $arr_data);
			$this->display("active_booking/cus_active", $arr_data);
			$this->display('template/operator/footer');
		}
		
		public function insert() {
			$result = $this->logic("payment_history_model")->insertDataCustomer($_POST);
			if ($result === true) {
				header('Location: ' . APP_PATH . 'payment_history');
				exit;
			} elseif ($result === "duplicate") {
				echo "<script>
						alert('Invoice number already exists, please asign another invoice number.');
						window.location.href='" . APP_PATH . "payment_history';
					  </script>";
				exit;
			} else {
				echo "Error inserting customer data.";
			}
		}
		
		public function delete($invoice) {
			if ($this->logic("payment_history_model")->deleteDataCustomer($invoice) == true) {
				header('Location: ' . APP_PATH . 'payment_history');
				exit;
			} else {
				// Handle error as needed (for example, show a message)
				echo "Error deleting customer data.";
			}
		}
		
		public function update() {
			if ($this->logic("payment_history_model")->updateDataCustomer($_POST) == true) {
				header('Location: ' . APP_PATH . 'payment_history');
				exit;
			} else {
				// Handle error as needed (for example, show a message)
				echo "Error updating customer data.";
			}
		}
	}
?>