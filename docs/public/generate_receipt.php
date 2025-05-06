<?php
	// Start the session only once
	session_start();

	// Set base path - consider using a relative path instead of absolute
	$basePath = dirname(dirname(__FILE__)); // Goes up one directory from the current file
	// Alternative: $basePath = $_SERVER['DOCUMENT_ROOT'] . '/Jens-House';

	// 1. Load configuration and initialize session
	require_once($basePath . '/app/config/config.php');
	require_once($basePath . '/app/init.php');

	// 2. Verify invoice parameter
	if (!isset($_GET['invoice']) || empty($_GET['invoice'])) {
		header('HTTP/1.1 400 Bad Request');
		die(json_encode(['error' => 'Invoice not provided']));
	}

	// 3. Load required files
	require_once($basePath . '/app/core/Database.php');
	require_once($basePath . '/app/model/details_model.php');
	require_once($basePath . '/lib/dompdf/autoload.inc.php');

	use Dompdf\Dompdf;
	use Dompdf\Options;

	try {
		// 4. Get invoice details
		$model = new details_model();
		$details = $model->getDetailsByInvoice($_GET['invoice']);
		
		if (!$details) {
			header('HTTP/1.1 404 Not Found');
			die(json_encode(['error' => 'Invoice not found']));
		}

		// 5. Parse booking details with error handling
		$bookingDetails = json_decode($details['details'], true);
		if (json_last_error() !== JSON_ERROR_NONE) {
			$bookingDetails = [];
			error_log('Invalid JSON in booking details: ' . $details['details']);
		}

		$totalPrice = isset($bookingDetails['originalTotal']) ? $bookingDetails['originalTotal'] : 
					 (isset($bookingDetails['price']) ? $bookingDetails['price'] : 0);
		$isDownpayment = isset($bookingDetails['downpayment']) ? $bookingDetails['downpayment'] : false;
		$paymentAmount = $isDownpayment ? $totalPrice * 0.5 : $totalPrice;

		// Format dates properly
		$bookingDate = new DateTime($details['date']);
		$eventDate = null;
		
		if (isset($details['details']) && is_string($details['details'])) {
			if (strpos($details['details'], 'hotel') !== false) {
				$eventDate = isset($bookingDetails['checkIn']) ? new DateTime($bookingDetails['checkIn']) : null;
			} elseif (strpos($details['details'], 'housing') !== false) {
				$eventDate = isset($bookingDetails['start_date']) ? new DateTime($bookingDetails['start_date']) : 
							(isset($bookingDetails['startDate']) ? new DateTime($bookingDetails['startDate']) : null);
			} elseif (strpos($details['details'], 'venue') !== false) {
				$eventDate = isset($bookingDetails['date']) ? new DateTime($bookingDetails['date']) : 
							(isset($bookingDetails['event_date']) ? new DateTime($bookingDetails['event_date']) : null);
			}
		}

		// 6. Generate PDF with proper error handling
		$options = new Options();
		$options->set('isRemoteEnabled', true);
		$options->set('isFontEnabled', true);
		$options->set('defaultFont', 'Arial');
		
		$dompdf = new Dompdf($options);
		
		$html = '
		<!DOCTYPE html>
		<html>
		<head>
			<meta charset="UTF-8">
			<title>Payment Receipt - '.htmlspecialchars($details['invoice']).'</title>
			<style>
				body { font-family: Arial, sans-serif; margin: 20px; }
				.header { text-align: center; margin-bottom: 30px; }
				.header h1 { color: #2c3e50; margin-bottom: 5px; }
				.header p { color: #7f8c8d; }
				.details { margin-bottom: 20px; }
				table { width: 100%; border-collapse: collapse; margin-top: 20px; }
				th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
				th { background-color: #f8f9fa; }
				.total-row { font-weight: bold; background-color: #f8f9fa; }
				.footer { margin-top: 40px; text-align: center; font-size: 0.9em; color: #7f8c8d; }
			</style>
		</head>
		<body>
			<div class="header">
				<h1>Jens House</h1>
				<p>Official Payment Receipt</p>
			</div>
			
			<div class="details">
				<p><strong>Invoice #:</strong> '.htmlspecialchars($details['invoice']).'</p>
				<p><strong>Issued Date:</strong> '.$bookingDate->format('F j, Y').'</p>
				<p><strong>Event Date:</strong> '.($eventDate ? $eventDate->format('F j, Y') : 'N/A').'</p>
				<p><strong>Payment Status:</strong> '.($isDownpayment ? '50% Downpayment' : 'Full Payment').'</p>
			</div>
			
			<table>
				<thead>
					<tr>
						<th>Description</th>
						<th>Amount</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>'.htmlspecialchars(isset($bookingDetails['title']) ? $bookingDetails['title'] : 'Booking').'</td>
						<td>IDR '.number_format(isset($bookingDetails['price']) ? $bookingDetails['price'] : 0, 0, ',', '.').'</td>
					</tr>';
		
		// Add addons if they exist
		if (!empty($bookingDetails['addons']) && is_array($bookingDetails['addons'])) {
			foreach ($bookingDetails['addons'] as $addon) {
				$addonPrice = 0;
				if (isset($addon['price'])) {
					if (is_numeric($addon['price'])) {
						$addonPrice = $addon['price'];
					} else {
						$addonPrice = preg_replace('/[^\d.]/', '', $addon['price']);
					}
				}
				
				$html .= '
					<tr>
						<td>'.htmlspecialchars(isset($addon['name']) ? $addon['name'] : 'Addon').'</td>
						<td>IDR '.number_format((float)$addonPrice, 0, ',', '.').'</td>
					</tr>';
			}
		}
		
		$html .= '
					<tr class="total-row">
						<td>'.($isDownpayment ? 'Downpayment (50%)' : 'Total Payment').'</td>
						<td>IDR '.number_format($paymentAmount, 0, ',', '.').'</td>
					</tr>';
		
		if ($isDownpayment) {
			$html .= '
					<tr>
						<td>Remaining Payment</td>
						<td>IDR '.number_format($totalPrice * 0.5, 0, ',', '.').'</td>
					</tr>';
		}
		
		$html .= '
		
				</tbody>
			</table>
			
			<div class="footer">
				<p>Thank you for your booking with Jens House</p>
				<p>For any inquiries, please contact us at:</p>
				<p>JensHouse@gmail.com</p>
				<p>tel:+62 81340260194</p>
				<p>https://www.facebook.com/profile.php?id=100055209049951</p>
				<p>https://www.instagram.com/jens_house_airmadidi/</p>
			</div>
		</body>
		</html>';
		
		$dompdf->loadHtml($html);
		$dompdf->setPaper('A4', 'portrait');
		$dompdf->render();
		
		// Set proper content-type header for PDF
		header('Content-Type: application/pdf');
		header('Content-Disposition: attachment; filename="receipt-'.$details['invoice'].'.pdf"');
		
		// Output the generated PDF
		echo $dompdf->output();
		exit;

	} catch (Exception $e) {
		error_log('Receipt Generation Error: ' . $e->getMessage());
		header('HTTP/1.1 500 Internal Server Error');
		header('Content-Type: application/json');
		die(json_encode(['error' => 'Error generating receipt: ' . $e->getMessage()]));
	}
?>