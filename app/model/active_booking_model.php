<?php
	class active_booking_model {
		private $db;
		private $table = 'tbl_active_booking';

		public function __construct() {
			$this->db = new Database();
			if(!$this->db) {
				throw new Exception("Database connection failed.");
			}
		}

		public function getAllDataCustomer() {
			$result = $this->db->query("SELECT * FROM {$this->table} ORDER BY invoice ASC");
			
			if ($result instanceof mysqli_result) {
				$data = [];
				if ($result->num_rows > 0) {
					while ($row = $result->fetch_assoc()) {
						// Update any timestamp fields with current local time if needed
						$row['generated_on'] = date('Y-m-d H:i:s'); // Current local time
						$data[] = $row;
					}
				}
				$result->free();
				return $data;
			} else {
				$data = $this->db->resultSet();
				// Update timestamp for each record if using alternative database wrapper
				foreach ($data as &$row) {
					$row['generated_on'] = date('Y-m-d H:i:s');
				}
				return $data;
			}
		}
			
		public function insertDataCustomer($data) {
			$sql = "INSERT INTO {$this->table} 
					(invoice, name, email, phone, payment, deadline, date, details) 
					VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
			
			$stmt = $this->db->prepare($sql);
			if (!$stmt) {
				return false;
			}
			
			$invoice = $data['cus_invoice'];
			$name = $data['cus_name'];
			$email = $data['cus_email'];
			$phone = $data['cus_phone'];
			$payment = $data['cus_payment'];
			$deadline = $data['cus_deadline'];
			$date = $data['cus_date'];
			$details = $data['cus_details'] ?? null;
			
			$stmt->bind_param("ssssssss", 
				$invoice,
				$name,
				$email,
				$phone,
				$payment,
				$deadline,
				$date,
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
					name=?, email=?, phone=?, payment=?, deadline=?, date=? 
					WHERE invoice=?";
			
			$stmt = $this->db->prepare($sql);
			if ($stmt) {
				$stmt->bind_param("sssssss", 
					$data['cus_name_update'],
					$data['cus_email_update'],
					$data['cus_phone_update'],
					$data['cus_payment_update'],
					$data['cus_deadline_update'],
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

		public function getBookingByInvoice($invoice) {
			$stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE invoice = ?");
			$stmt->bind_param("s", $invoice);
			$stmt->execute();
			$result = $stmt->get_result();
			return $result->fetch_assoc();
		}

		public function acceptBooking($invoice) {
			$booking = $this->getBookingByInvoice($invoice);
			
			if(!$booking) return false;

			$historyModel = new payment_history_model();
			$historyData = [
				'cus_invoice' => $booking['invoice'],
				'cus_name' => $booking['name'],
				'cus_email' => $booking['email'],
				'cus_phone' => $booking['phone'],
				'cus_payment' => $booking['payment'],
				'cus_date' => $booking['date'],
				'cus_details' => $booking['details']
			];
			
			if($historyModel->insertDataCustomer($historyData)) {
				return $this->deleteDataCustomer($invoice);
			}
			
			return false;
		}
	}
?>