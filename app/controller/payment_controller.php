<?php
	date_default_timezone_set('Asia/Makassar');
	function parseAndFormatDate($dateString, $format = 'Y-m-d') {
		if (empty($dateString)) {
			return null;
		}
		
		if (is_numeric($dateString)) {
			return date($format, (int)$dateString);
		}
		
		$timestamp = strtotime($dateString);
		if ($timestamp === false) {
			return null;
		}
		
		$timestamp = strtotime('+1 day', $timestamp);
		
		return date($format, $timestamp);
	}
	
	function fixDatesInBookingDetails(array $booking): array {
		// List of date fields that need correction
		$dateFields = [
			'date', 'event_date', 'checkIn', 'checkOut', 'start_date', 'end_date'
		];
		
		// Process each date field if it exists
		foreach ($dateFields as $field) {
			if (isset($booking[$field]) && !empty($booking[$field])) {
				$booking[$field] = parseAndFormatDate($booking[$field]);
			}
		}
		
		return $booking;
	}
	define('BASE_PATH', realpath(__DIR__.'/../../'));
	require_once BASE_PATH . '/app/init.php';
	
	// Start session only once
	if (session_status() === PHP_SESSION_NONE) {
		session_start();
	}

	// Improved authentication check
	if (!isset($_SESSION['user'])) {
		header('Content-Type: application/json');
		http_response_code(401);
		echo json_encode([
			'status' => 'error',
			'message' => 'Session expired or invalid. Please login again.'
		]);
		exit();
	}

	// Define the absolute path to DOMPDF
	$dompdfPath = __DIR__ . '/../../lib/dompdf/autoload.inc.php';

	// Verify the file exists before requiring
	if (!file_exists($dompdfPath)) {
		throw new RuntimeException("DOMPDF library not found at: $dompdfPath");
	}

	require_once $dompdfPath;
	require_once dirname(__DIR__, 2) . '/vendor/midtrans-php/Midtrans.php';
	require_once dirname(__DIR__) . '/model/account_model.php';
	require_once dirname(__DIR__) . '/model/active_booking_model.php';
	require_once dirname(__DIR__) . '/model/payment_history_model.php';
	require_once dirname(__DIR__) . '/model/details_model.php';

	use Dompdf\Dompdf;
	use Dompdf\Options;

	header("Access-Control-Allow-Origin: http://localhost");
	header("Content-Type: application/json");
	header("Access-Control-Allow-Methods: POST, OPTIONS");

	// Improved deadline calculation function
	function calculateDeadline(array $booking): ?string {
		if (!isset($booking['type'])) {
			return null;
		}

		// First determine the event date based on booking type
		$eventDate = null;
		if (strpos($booking['type'], 'hotel') !== false) {
			$eventDate = $booking['checkIn'] ?? null;
		} elseif (strpos($booking['type'], 'housing') !== false) {
			$eventDate = $booking['start_date'] ?? $booking['startDate'] ?? null;
		} elseif (strpos($booking['type'], 'venue') !== false) {
			$eventDate = $booking['date'] ?? $booking['event_date'] ?? null;
		}

		// Housing deadline logic - always use end_date
		if (strpos($booking['type'], 'housing') !== false) {
			$endDate = $booking['end_date'] ?? $booking['endDate'] ?? null;
			
			if (empty($endDate)) {
				throw new InvalidArgumentException("Housing booking requires end date", 400);
			}
			
			// Use our helper function to ensure consistent date handling
			$formattedEndDate = parseAndFormatDate($endDate, 'Y-m-d H:i:s');
			if (!$formattedEndDate) {
				throw new InvalidArgumentException("Invalid end date format for housing", 400);
			}
			
			return $formattedEndDate;
		}
		
		// For venue bookings, set deadline to end of event day
		if (strpos($booking['type'], 'venue') !== false && $eventDate) {
			$formattedEventDate = parseAndFormatDate($eventDate, 'Y-m-d');
			if ($formattedEventDate) {
				return $formattedEventDate . ' 23:59:59';
			}
		}
		
		return null;
	}

	// PDF Generation functions
	function generatePDF($title, $headers, $data, $filename) {
		try {
			$options = new Options();
			$options->set('isHtml5ParserEnabled', true);
			$options->set('isRemoteEnabled', true);
			$options->set('defaultFont', 'Arial');
			
			$dompdf = new Dompdf($options);
			
			$html = '<!DOCTYPE html>
			<html>
			<head>
				<meta charset="UTF-8">
				<title>'.$title.'</title>
				<style>
					body { font-family: Arial, sans-serif; margin: 15px; }
					h1 { color: #2c3e50; text-align: center; margin-bottom: 20px; font-size: 18pt; }
					.report-info { text-align: center; margin-bottom: 15px; color: #555; font-size: 10pt; }
					table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 9pt; }
					th { background-color: #3498db; color: white; padding: 8px; text-align: left; }
					td { padding: 6px; border-bottom: 1px solid #ddd; }
					tr:nth-child(even) { background-color: #f8f8f8; }
					.footer { margin-top: 20px; text-align: right; font-size: 8pt; color: #777; }
				</style>
			</head>
			<body>
				<h1>'.$title.'</h1>
				<div class="report-info">
					Generated on: '.date('Y-m-d H:i:s').'
				</div>
				<table>
					<thead>
						<tr>';
			
			foreach ($headers as $header) {
				$html .= '<th>'.$header.'</th>';
			}
			
			$html .= '</tr>
					</thead>
					<tbody>';
			
			foreach ($data as $row) {
				$html .= '<tr>';
				foreach ($headers as $key => $header) {
					$columnKey = is_numeric($key) ? $header : $key;
					$value = $row[$columnKey] ?? '';
					$html .= '<td>'.htmlspecialchars($value).'</td>';
				}
				$html .= '</tr>';
			}
			
			$html .= '</tbody>
				</table>
				<div class="footer">
					Generated by Jens House Booking System
				</div>
			</body>
			</html>';
			
			$dompdf->loadHtml($html);
			$dompdf->setPaper('A4', 'landscape');
			$dompdf->render();
			
			// Set headers
			header('Content-Type: application/pdf');
			header('Content-Disposition: attachment; filename="'.$filename.'"');
			
			// Output the document directly
			echo $dompdf->output();
			exit;
			
		} catch (Exception $e) {
			error_log("PDF Generation Error: ".$e->getMessage());
			throw new RuntimeException("PDF generation failed: ".$e->getMessage());
		}
	}

	// Handle PDF generation requests
	if (isset($_GET['action'])) {
		try {
			if (!isset($_SESSION['user'])) {
				throw new RuntimeException("Unauthorized", 401);
			}

			$accountModel = new account_model();
			$customer = $accountModel->getCustomerById($_SESSION['user']['id']);
			if (!$customer) {
				throw new RuntimeException("Customer not found", 404);
			}

			// Clear any previous output
			if (ob_get_length()) ob_end_clean();

			switch ($_GET['action']) {
				case 'history_pdf':
					$historyModel = new payment_history_model();
					$rawData = $historyModel->getAllDataCustomer($_SESSION['user']['id']);
					
					if (empty($rawData)) {
						throw new RuntimeException("No history data available", 404);
					}
					
					// Map history data fields
					$pdfData = [];
					foreach ($rawData as $row) {
						$pdfData[] = [
							'Invoice' => $row['cus_invoice'] ?? $row['invoice'] ?? 'N/A',
							'Name' => $row['cus_name'] ?? $row['name'] ?? 'N/A',
							'Email' => $row['cus_email'] ?? $row['email'] ?? 'N/A',
							'Phone' => $row['cus_phone'] ?? $row['phone'] ?? 'N/A',
							'Payment' => $row['cus_payment'] ?? $row['payment'] ?? 'N/A',
							'Date' => $row['cus_date'] ?? $row['date'] ?? 'N/A',
						];
					}
					
					generatePDF(
						'Payment History Report',
						['Invoice', 'Name', 'Email', 'Phone', 'Payment', 'Date'],
						$pdfData,
						'payment_history_'.date('Y-m-d').'.pdf'
					);
					exit;
					
				case 'active_pdf':
					$activeModel = new active_booking_model();
					$rawData = $activeModel->getAllDataCustomer($_SESSION['user']['id']);
					
					if (empty($rawData)) {
						throw new RuntimeException("No active bookings available", 404);
					}
					
					// Map active bookings data fields
					$pdfData = [];
					foreach ($rawData as $row) {
						$pdfData[] = [
							'Invoice' => $row['cus_invoice'] ?? $row['invoice'] ?? 'N/A',
							'Name' => $row['cus_name'] ?? $row['name'] ?? 'N/A',
							'Email' => $row['cus_email'] ?? $row['email'] ?? 'N/A',
							'Phone' => $row['cus_phone'] ?? $row['phone'] ?? 'N/A',
							'Payment' => $row['cus_payment'] ?? $row['payment'] ?? 'N/A',
							'Deadline' => $row['cus_deadline'] ?? $row['deadline'] ?? 'N/A',
							'Date' => $row['cus_date'] ?? $row['date'] ?? 'N/A',
						];
					}
					
					generatePDF(
						'Active Bookings Report',
						['Invoice', 'Name', 'Email', 'Phone', 'Payment', 'Deadline', 'Date'],
						$pdfData,
						'active_bookings_'.date('Y-m-d').'.pdf'
					);
					exit;
					
				default:
					throw new RuntimeException("Invalid action", 400);
			}
		} catch (Exception $e) {
			header('Content-Type: application/json');
			http_response_code($e->getCode() ?: 500);
			echo json_encode([
				'status' => 'error',
				'message' => $e->getMessage()
			]);
			exit;
		}
	}

	try {
		// Authentication check
		if (!isset($_SESSION['user'])) {
			throw new RuntimeException("User  not logged in", 401);
		}

		// Input validation
		$jsonInput = file_get_contents('php://input');
		if ($jsonInput === false) {
			throw new RuntimeException("Failed to read input", 400);
		}

		$input = json_decode($jsonInput, true);
		if (json_last_error() !== JSON_ERROR_NONE) {
			throw new RuntimeException("Invalid JSON format", 400);
		}

		// Validate required fields
		$requiredFields = ['totalAmount', 'bookingDetails', 'customer'];
		foreach ($requiredFields as $field) {
			if (!isset($input[$field])) {
				throw new InvalidArgumentException("Missing required field: $field", 400);
			}
		}

		if (!is_numeric($input['totalAmount']) || $input['totalAmount'] <= 0) {
			throw new InvalidArgumentException("Invalid totalAmount value", 400);
		}

		if (!is_array($input['bookingDetails']) || empty($input['bookingDetails'])) {
			throw new InvalidArgumentException("Booking details must be a non-empty array", 400);
		}

		// Get customer data
		$accountModel = new account_model();
		$customer = $accountModel->getCustomerById($_SESSION['user']['id']);
		if (!$customer) {
			throw new RuntimeException("Customer data not found", 404);
		}

		// Initialize Midtrans
		\Midtrans\Config::$serverKey = 'YOUR MIDTRANS SERVER KEY';
		\Midtrans\Config::$isProduction = false;

		$orderId = 'JENS-' . time() . '-' . bin2hex(random_bytes(2));

		// Prepare transaction
		$transaction = [
			'transaction_details' => [
				'order_id' => $orderId,
				'gross_amount' => (int)$input['totalAmount']
			],
			'customer_details' => [
				'first_name' => $customer['name'],
				'email' => $customer['email'],
				'phone' => $customer['phone'] ?? ''
			],
			'finish_redirect_url' => 'url',
			'unfinish_redirect_url' => 'url',
			'error_redirect_url' => 'url',
			'callback_url' => 'url'
		];

		// Generate payment token
		$snapToken = \Midtrans\Snap::getSnapToken($transaction);
		$redirectUrl = \Midtrans\Snap::getSnapUrl($transaction);

		// Initialize models
		$activeModel = new active_booking_model();
		$historyModel = new payment_history_model();
		$detailsModel = new details_model();

		// Process each booking
		foreach ($input['bookingDetails'] as $booking) {
			if (!is_array($booking) || !isset($booking['type'], $booking['price'])) {
				continue;
			}

			// Validate service price
			if (!is_numeric($booking['price']) || $booking['price'] <= 0) {
				throw new InvalidArgumentException("Invalid price for {$booking['type']}", 400);
			}

			// Initialize variables
			$basePrice = (float)$booking['price'];
			$addonTotal = 0;
			$originalTotal = $basePrice; // Default to base price if no addons

			// Make sure we're working with the original price, not downpayment price
			if (isset($booking['originalTotal']) && is_numeric($booking['originalTotal'])) {
				$originalTotal = (float)$booking['originalTotal'];
			} else {
				if (isset($booking['addons']) && is_array($booking['addons'])) {
					foreach ($booking['addons'] as $addon) {
						if (isset($addon['price'])) {
							$addonPrice = is_numeric($addon['price']) ? 
										(float)$addon['price'] : 
										(float)preg_replace('/[^\d]/', '', $addon['price']);
							$addonTotal += $addonPrice;
						}
					}
				}
				$originalTotal = $basePrice + $addonTotal;
			}
			
			$isDownpayment = $booking['downpayment'] ?? false;
			$paymentAmount = $isDownpayment ? $originalTotal * 0.5 : $originalTotal;
			
			// Store the correct values in the booking details
			$booking['originalTotal'] = $originalTotal;
			$booking['paymentAmount'] = $paymentAmount;

			// Generate unique invoice per service
			$serviceOrderId = $orderId . '-' . strtoupper(substr($booking['type'], 0, 3));
			
			$paymentDescription = $isDownpayment
				? '50% Downpayment (IDR ' . number_format($paymentAmount, 0, ',', '.') . ')'
				: 'Full Payment (IDR ' . number_format($originalTotal, 0, ',', '.') . ')';

			// Get deadline based on booking type
			$deadline = calculateDeadline($booking);
			
			// Get event date
			$eventDate = null;
			if (strpos($booking['type'], 'hotel') !== false) {
				$eventDate = $booking['checkIn'] ?? null;
			} elseif (strpos($booking['type'], 'housing') !== false) {
				$eventDate = $booking['start_date'] ?? $booking['startDate'] ?? null;
			} elseif (strpos($booking['type'], 'venue') !== false) {
				$eventDate = $booking['date'] ?? $booking['event_date'] ?? null;
			}

			// Format event date for storage using our helper function
			$formattedEventDate = parseAndFormatDate($eventDate);
			if (!$formattedEventDate) {
				$formattedEventDate = date('Y-m-d'); // Default to today if no event date or invalid date
			}

			// Prepare detailed booking information without originalTotal
			$bookingDetails = [
				'type' => $booking['type'],
				'title' => $booking['title'] ?? '',
				'price' => $basePrice,
				'downpayment' => $isDownpayment,
				'originalTotal' => $originalTotal,
				'addons' => $booking['addons'] ?? [],
				// Include all relevant dates based on booking type
				'date' => $booking['date'] ?? null, // For venues
				'event_date' => $booking['event_date'] ?? $booking['date'] ?? null,
				'checkIn' => $booking['checkIn'] ?? null, // For hotels
				'checkOut' => $booking['checkOut'] ?? null,
				'start_date' => $booking['start_date'] ?? $booking['startDate'] ?? null, // For housing
				'end_date' => $booking['end_date'] ?? $booking['endDate'] ?? null,
				'guests' => $booking['guests'] ?? null,
				'plan' => $booking['plan'] ?? null,
				'created_date' => date('Y-m-d H:i:s') // Add creation timestamp
			];
			
			$bookingDetails = fixDatesInBookingDetails($bookingDetails);
			
			// Prepare data for details table
			$detailsData = [
				'invoice' => $serviceOrderId,
				'customer_id' => $_SESSION['user']['id'],
				'payment' => $paymentDescription,
				'date' => $formattedEventDate, // Use properly defined event date
				'deadline' => $deadline,
				'details' => json_encode($bookingDetails),
				'status' => 'pending_payment'
			];

			if (!$detailsModel->insertDetails($detailsData)) {
				throw new RuntimeException("Failed to save payment details for {$booking['type']}", 500);
			}

			// Prepare data for booking tables
			$bookingData = [
				'cus_invoice' => $serviceOrderId,
				'cus_name' => $customer['name'],
				'cus_email' => $customer['email'],
				'cus_phone' => $customer['phone'] ?? '',
				'cus_payment' => $paymentDescription,
				'cus_deadline' => $deadline,
				'cus_date' => date('Y-m-d H:i:s'),
				'cus_details' => json_encode($bookingDetails),
				'customer_id' => $_SESSION['user']['id'],
				'status' => 'pending_payment'
			];

			// Save to appropriate table based on payment type
			if ($isDownpayment) {
				if (!$activeModel->insertDataCustomer($bookingData)) {
					throw new RuntimeException("Failed to save active booking for {$booking['type']}", 500);
				}
			} else {
				if (!$historyModel->insertDataCustomer($bookingData)) {
					throw new RuntimeException("Failed to save payment history for {$booking['type']}", 500);
				}
			}
		}
		
		// Return success response
		http_response_code(200);
		echo json_encode([
			'status' => 'success',
			'token' => $snapToken,
			'redirect_url' => $redirectUrl,
			'invoice' => $orderId
		]);

	} catch (Exception $e) {
		http_response_code($e->getCode() ?: 500);
		echo json_encode([
			'status' => 'error',
			'message' => $e->getMessage(),
			'error_type' => get_class($e),
			'trace' => $e->getTraceAsString()
		]);
	}
?>