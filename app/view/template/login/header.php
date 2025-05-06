<?php
    session_start();
    require_once __DIR__ . '/../../../model/account_model.php';

    $error = "";
    if (isset($_POST['login_user'])) {
        $email    = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $role     = trim($_POST['role'] ?? 'customer');
        $model = new account_model();

        $redirectUrl = '';
        if ($role === 'operator') {
            $operator = $model->loginOperator($email, $password);
            if ($operator) {
                $_SESSION['operator'] = $operator['name'];
                $_SESSION['operator_email'] = $operator['email'];
                $_SESSION['operator_id'] = $operator['operator_id']; // Store operator ID
                $redirectUrl = APP_PATH . "payment_history/cus_history";
            } else {
                $error = "Wrong email/password combination for operator.";
            }
        } elseif ($role === 'customer') {
            $customer = $model->loginCustomer($email, $password);
            if ($customer) {
                $_SESSION['customer'] = $customer['name'];
                $_SESSION['customer_email'] = $customer['email'];
                $_SESSION['customer_id'] = $customer['id'];
                $_SESSION['phone'] = $customer['phone'];
                $redirectUrl = APP_PATH . "default/home";
            } else {
                $error = "Wrong email/password combination for customer.";
            }
        }

        // AJAX response handling
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => empty($error),
                'error' => $error,
                'redirect' => $redirectUrl
            ]);
            exit;
        } else {
            // Non-AJAX fallback
            if ($error) {
                $_SESSION['login_error'] = $error;
            } else {
                header("Location: " . $redirectUrl);
            }
            exit;
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title><?php echo $data['title']; ?></title>
        <link rel="stylesheet" href="<?php echo APP_PATH; ?>/css/accountstyle/style.css">
    </head>