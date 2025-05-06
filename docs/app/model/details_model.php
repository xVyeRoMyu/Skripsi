<?php
	require_once(__DIR__ . '/../core/Database.php');

	class details_model {
		private $db;
		private $table = 'tbl_details';

		public function __construct() {
			try {
				$this->db = new Database();
				if (!$this->db->getConnection()) {
					throw new Exception("Database connection failed: " . $this->db->getError());
				}
			} catch (Exception $e) {
				error_log($e->getMessage());
				throw $e;
			}
		}

		// Add this method if missing (needed by generate_receipt.php)
		public function getDetailsByInvoice($invoice) {
			$stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE invoice = ?");
			if (!$stmt) {
				error_log("Prepare failed: " . $this->db->getError());
				return false;
			}
			
			$stmt->bind_param("s", $invoice);
			if (!$stmt->execute()) {
				error_log("Execute failed: " . $stmt->error);
				return false;
			}
			
			$result = $stmt->get_result();
			return $result->fetch_assoc();
		}

		public function getDetailsByCustomer($customerId) {
			$stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE customer_id = ? ORDER BY date DESC");
			$stmt->bind_param("i", $customerId);
			$stmt->execute();
			$result = $stmt->get_result();
			
			$data = [];
			while ($row = $result->fetch_assoc()) {
				$data[] = $row;
			}
			return $data;
		}

		public function insertDetails($data) {
			$sql = "INSERT INTO tbl_details 
					(invoice, customer_id, payment, date, deadline, details, status) 
					VALUES (?, ?, ?, ?, ?, ?, ?)";
					
			$stmt = $this->db->prepare($sql);
			if (!$stmt) {
				error_log("Prepare failed: " . $this->db->error);
				return false;
			}
			
			// Handle NULL deadline properly
			$deadline = isset($data['deadline']) && $data['deadline'] !== '' 
				? $data['deadline'] 
				: null;
			
			// Bind parameters with proper NULL handling
			$stmt->bind_param("sisssss",
				$data['invoice'],
				$data['customer_id'],
				$data['payment'],
				$data['date'],
				$deadline,
				$data['details'],
				$data['status']
			);
			
			if (!$stmt->execute()) {
				error_log("Execute failed: " . $stmt->error);
				return false;
			}
			
			return true;
		}
		
		public function getDetailsByAccount($accountId) {
			$stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE account_id = ? ORDER BY date DESC");
			$stmt->bind_param("i", $accountId);
			$stmt->execute();
			$result = $stmt->get_result();
			
			$data = [];
			while ($row = $result->fetch_assoc()) {
				$data[] = $row;
			}
			return $data;
		}
		
		public function updatePaymentStatus($order_id, $status) {
			$stmt = $this->db->prepare("UPDATE tbl_details SET status = ? WHERE invoice = ?");
			if (!$stmt) return false;
			$stmt->bind_param("ss", $status, $order_id);
			return $stmt->execute();
		}
	}
?>