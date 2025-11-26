<?php
session_start();
require_once '../includes/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format";
        header("Location: ../login.php");
        exit();
    }

    // Check if email exists in database
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Generate reset token
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Store token in database
        $updateStmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE email = ?");
        $updateStmt->bind_param("sss", $token, $expiry, $email);
        $updateStmt->execute();

        // In a real application, you would send an email here with the reset link
        // For now, we'll just show a success message
        $_SESSION['success'] = "If an account exists with this email, you will receive password reset instructions.";
    } else {
        // Don't reveal if email exists or not for security
        $_SESSION['success'] = "If an account exists with this email, you will receive password reset instructions.";
    }

    header("Location: ../login.php");
    exit();
}
?>
