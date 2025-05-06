<?php
	class payment_history_model {
		private $db;
		private $table = 'tbl_payment_history';

		public function __construct() {
			$this->db = new Database();
			if(!$this->db) {
				throw new Exception("Database connection failed.");
			}
		}

		public function getAllDataCustomer() {
			$stmt = $this->db->prepare("SELECT * FROM {$this->table} ORDER BY invoice ASC");
			$stmt->execute();
			$result = $stmt->get_result();
			
			$data = [];
			if ($result->num_rows > 0) {
				while ($row = $result->fetch_assoc()) {
					// Update any timestamp fields with current local time if needed
					$row['generated_on'] = date('Y-m-d H:i:s'); // Current local time
					$data[] = $row;
				}
			}
			return $data;
		}
			
		public function insertDataCustomer($data) {
			// Check for duplicate invoice first
			$checkStmt = $this->db->prepare("SELECT invoice FROM {$this->table} WHERE invoice = ?");
			$checkStmt->bind_param("s", $data['cus_invoice']);
			$checkStmt->execute();
			$checkResult = $checkStmt->get_result();
			
			if ($checkResult->num_rows > 0) {
				$checkStmt->close();
				return "duplicate";
			}
			$checkStmt->close();

			// Insert new record
			$sql = "INSERT INTO {$this->table} 
					(invoice, name, email, phone, payment, date, details) 
					VALUES (?, ?, ?, ?, ?, ?, ?)";
			
			$stmt = $this->db->prepare($sql);
			if (!$stmt) {
				return false;
			}
			
			// Handle null details by providing an empty string
			$details = isset($data['cus_details']) ? $data['cus_details'] : '';
			
			$stmt->bind_param("sssssss", 
				$data['cus_invoice'],
				$data['cus_name'],
				$data['cus_email'],
				$data['cus_phone'],
				$data['cus_payment'],
				$data['cus_date'],
				$details
			);
			
			$result = $stmt->execute();
			$stmt->close();
			
			return $result;
		}
			
		public function deleteDataCustomer($invoice) {
			$sql = "DELETE FROM {$this->table} WHERE invoice = ?";
			$stmt = $this->db->prepare($sql);
			
			if ($stmt) {
				$stmt->bind_param("s", $invoice);
				$result = $stmt->execute();
				$stmt->close();
				return $result;
			}
			return false;
		}

		public function updateDataCustomer($data) {
			$sql = "UPDATE {$this->table} SET 
					name=?, email=?, phone=?, payment=?, date=? 
					WHERE invoice=?";
			
			$stmt = $this->db->prepare($sql);
			if ($stmt) {
				$stmt->bind_param("ssssss", 
					$data['cus_name_update'],
					$data['cus_email_update'],
					$data['cus_phone_update'],
					$data['cus_payment_update'],
					$data['cus_date_update'],
					$data['cus_invoice_update']
				);
				$result = $stmt->execute();
				$stmt->close();
				return $result;
			}
			return false;
		}
		
		public function updatePaymentDescription($invoice, $description) {
			$sql = "UPDATE {$this->table} SET payment = ? WHERE invoice = ?";
			$stmt = $this->db->prepare($sql);
			
			if ($stmt) {
				$stmt->bind_param("ss", $description, $invoice);
				$result = $stmt->execute();
				$stmt->close();
				return $result;
			}
			return false;
		}
	}
?>