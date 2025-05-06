<?php
	// Start session if not already started at the VERY TOP of the file
	if (session_status() === PHP_SESSION_NONE) {
		session_start();
	}

	// Check if user is logged in, redirect if not
	if (!isset($_SESSION['user'])) {
		header('Location: ' . APP_PATH . 'enroll/login');
		exit();
	}

	require_once '../app/model/details_model.php';
	require_once '../app/model/account_model.php'; // Add this line to load account model

	// Get customer details including phone number
	$accountModel = new account_model();
	$customer = $accountModel->getCustomerById($_SESSION['user']['id']);

	// Get payment details for the logged-in customer
	$detailsModel = new details_model();
	$payments = $detailsModel->getDetailsByCustomer($_SESSION['user']['id']);

	// Prepare data for view
	$data = [
		'payments' => is_array($payments) ? $payments : [],
		'customer' => $customer['name'] ?? 'Guest',
		'customer_email' => $customer['email'] ?? 'Not provided',
		'phone' => $customer['phone'] ?? 'Not provided' // Get phone from customer data
	];

	// Update session with phone number if it wasn't set before
	if (!isset($_SESSION['user']['phone']) && isset($customer['phone'])) {
		$_SESSION['user']['phone'] = $customer['phone'];
	}
?>
<div class="container">
    <h1>Your Booking Details</h1>
    <div class="operator-info">
		<p class="info-item"><span class="info-label">Logged in as:</span> <?php echo htmlspecialchars($data['customer']); ?></p>
		<p class="info-item"><span class="info-label">Email:</span> <?php echo htmlspecialchars($data['customer_email']); ?></p>
		<p class="info-item"><span class="info-label">Phone:</span> <?php echo htmlspecialchars($data['phone']); ?></p>
	</div>
    <div class="table-responsive no-wrap-table">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th class="text-center sortable" data-column="invoice" data-sort="none">
                        Invoice <i class="fas fa-sort"></i>
                    </th>
                    <th class="text-center sortable" data-column="payment" data-sort="none">
                        Payment <i class="fas fa-sort"></i>
                    </th>
                    <th class="text-center sortable" data-column="booking_date" data-sort="none">
                        Booking Date <i class="fas fa-sort"></i>
                    </th>
                    <th class="text-center sortable" data-column="deadline" data-sort="none">
                        Deadline <i class="fas fa-sort"></i>
                    </th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Details</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($data['payments']) && is_array($data['payments'])): ?>
                    <?php $count = 1; ?>
                    <?php foreach ($data['payments'] as $payment): 
                        $details = json_decode($payment['details'], true);
                        $bookingDate = '';
                        $deadline = '';
                        
                        // Determine booking date based on type
                        if (strpos($payment['payment'] ?? '', 'venue') !== false) {
                            $bookingDate = $details['date'] ?? $details['event_date'] ?? $payment['date'];
                        } elseif (strpos($payment['payment'] ?? '', 'housing') !== false) {
                            $bookingDate = $details['start_date'] ?? $payment['date'];
                            $deadline = $details['end_date'] ?? $payment['deadline'];
                        } else {
                            $bookingDate = $payment['date'];
                        }
                        
                        // Format dates consistently
                        $formattedBookingDate = $bookingDate ? date('Y-m-d', strtotime($bookingDate)) : 'N/A';
						$formattedDeadline = $deadline ? date('Y-m-d', strtotime($deadline)) : ($payment['deadline'] ? date('Y-m-d', strtotime($payment['deadline'])) : 'N/A');
                    ?>
                        <tr>
                            <td><?php echo $count; ?></td>
                            <td class="text-center"><?php echo htmlspecialchars($payment['invoice'] ?? ''); ?></td>
                            <td class="text-center"><?php echo htmlspecialchars($payment['payment'] ?? ''); ?></td>
                            <td class="text-center"><?php echo htmlspecialchars($formattedBookingDate); ?></td>
                            <td class="text-center"><?php echo htmlspecialchars($formattedDeadline); ?></td>
                            <td class="text-center">
                                <?php if (($payment['status'] ?? '') == 'completed'): ?>
                                    <span class="badge bg-success">Completed</span>
                                <?php elseif (($payment['status'] ?? '') == 'pending'): ?>
                                    <span class="badge bg-warning text-dark">Pending</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Cancelled</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-info btn-sm view-details" 
                                        data-details='<?= htmlspecialchars($payment['details'] ?? '{}', ENT_QUOTES) ?>'
                                        data-invoice='<?= htmlspecialchars($payment['invoice'] ?? '') ?>'
                                        title="View Details">
                                    <i class="fas fa-info-circle"></i>
                                </button>
                            </td>
                        </tr>
                        <?php $count++; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center">No booking records found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Details Modal -->
<div id="detailsModal" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Booking Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="detailsContent"></div>
            </div>
            <div class="modal-footer">
                <button id="downloadReceipt" class="btn btn-primary" 
                        data-invoice="">Download Receipt</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>