<?php
// Database connection settings
$dsn      = 'mysql:host=localhost;dbname=db_jens_house;charset=utf8';
$username = 'opt1';
$password = 'Jens_House';

try {
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Fetch all rows from the table (ensure you have an identifier like 'email')
$sql = "SELECT email, password FROM tbl_admin";
$stmt = $pdo->query($sql);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($users as $user) {
    // If the password does not appear to be hashed (does not start with '$2y$')
    if (strpos($user['password'], '$2y$') !== 0) {
        $hashedPassword = password_hash($user['password'], PASSWORD_DEFAULT);
        $updateSQL = "UPDATE tbl_admin SET password = :hashedPassword WHERE email = :email";
        $updateStmt = $pdo->prepare($updateSQL);
        $updateStmt->execute([
            ':hashedPassword' => $hashedPassword,
            ':email'          => $user['email']
        ]);
        echo "Updated user with email: " . $user['email'] . "\n";
    } else {
        echo "User with email: " . $user['email'] . " already has a hashed password.\n";
    }
}

echo "Password hashing completed!";
?>