<?php
// Enable strict error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set security headers
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

// Handle CORS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    http_response_code(204);
    exit;
}

// Initialize logging with better file handling
$log_dir = __DIR__ . '/logs';
if (!file_exists($log_dir)) {
    mkdir($log_dir, 0755, true);
}
$log_file = $log_dir . '/midtrans_webhook.log';

// Absolute path resolution for includes
$base_dir = dirname(__DIR__, 2); // Goes up two levels from public/ to Jens-House/
$init_path = $base_dir . '/app/init.php';
$midtrans_path = $base_dir . '/vendor/midtrans-php/Midtrans.php';
$model_path = $base_dir . '/app/model/details_model.php';

// Verify critical files exist before proceeding
if (!file_exists($init_path)) {
    error_log("Init file not found at: $init_path");
    http_response_code(500);
    die(json_encode(['error' => 'Server configuration error']));
}

try {
    // Load dependencies with verification
    require_once $init_path;
    require_once $midtrans_path;
    require_once $model_path;

    // Log incoming request
    $request_log = sprintf(
        "[%s] %s %s\nHeaders: %s\nPayload: %s\n",
        date('Y-m-d H:i:s'),
        $_SERVER['REQUEST_METHOD'],
        $_SERVER['REQUEST_URI'],
        json_encode(getallheaders()),
        file_get_contents('php://input')
    );
    file_put_contents($log_file, $request_log, FILE_APPEND);

    // Handle verification request
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        die(json_encode([
            'status' => 'active',
            'environment' => 'sandbox',
            'timestamp' => date('c'),
            'service' => 'Midtrans Webhook',
            'version' => '1.1'
        ]));
    }

    // Only process POST requests
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        die(json_encode(['error' => 'Method Not Allowed']));
    }

    // Configure Midtrans
    \Midtrans\Config::$serverKey = 'SB-Mid-server-DMMTKOneBWHYsTXh5hjrgcHx';
    \Midtrans\Config::$isProduction = false;
    \Midtrans\Config::$isSanitized = true;
    \Midtrans\Config::$is3ds = true;

    // Process notification
    $notification = new \Midtrans\Notification();
    $order_id = $notification->order_id;
    $transaction_status = $notification->transaction_status;
    $fraud_status = $notification->fraud_status ?? null;

    // Validate notification
    if (empty($order_id)) {
        throw new RuntimeException("Invalid notification: Missing order_id");
    }

    // Status mapping with additional validation
    $status_map = [
        'capture' => 'completed',
        'settlement' => 'completed',
        'pending' => 'pending_payment',
        'deny' => 'cancelled',
        'expire' => 'expired',
        'cancel' => 'cancelled'
    ];

    if (!array_key_exists($transaction_status, $status_map)) {
        throw new RuntimeException("Unrecognized transaction status: $transaction_status");
    }

    $db_status = $status_map[$transaction_status];

    // Update database with transaction
    $detailsModel = new details_model();
    $update_result = $detailsModel->updatePaymentStatus($order_id, $db_status);

    // Detailed logging
    $txn_log = sprintf(
        "Order: %s\nStatus: %s\nFraud: %s\nDB Status: %s\nResult: %s\n",
        $order_id,
        $transaction_status,
        $fraud_status,
        $db_status,
        $update_result ? 'Success' : 'Failed'
    );
    file_put_contents($log_file, $txn_log, FILE_APPEND);

    if (!$update_result) {
        throw new RuntimeException("Database update failed for order: $order_id");
    }

    // Success response
    $response = [
        'status' => 'success',
        'order_id' => $order_id,
        'transaction_status' => $transaction_status,
        'fraud_status' => $fraud_status,
        'database_status' => $db_status,
        'timestamp' => date('c')
    ];

    http_response_code(200);
    echo json_encode($response);

} catch (Throwable $e) {
    // Comprehensive error logging
    $error_log = sprintf(
        "[%s] ERROR: %s\nFile: %s:%d\nTrace:\n%s\n",
        date('Y-m-d H:i:s'),
        $e->getMessage(),
        $e->getFile(),
        $e->getLine(),
        $e->getTraceAsString()
    );
    file_put_contents($log_file, $error_log, FILE_APPEND);

    // Client response
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Payment processing error',
        'order_id' => $order_id ?? null,
        'error' => $e->getMessage(),
        'timestamp' => date('c')
    ]);
}