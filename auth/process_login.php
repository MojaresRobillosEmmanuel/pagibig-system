<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: ../login.php");
    exit();
}

require_once __DIR__ . '/../database/db_connect.php';
$conn = getConnection();

// Check for required fields
$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

if (empty($username) || empty($password)) {
    $_SESSION['error'] = "Both username and password are required";
    header("Location: ../login.php");
    exit();
}

// Check for too many login attempts
if (isset($_SESSION['login_attempts']) && $_SESSION['login_attempts'] >= 5) {
    if (time() - $_SESSION['last_attempt'] < 300) { // 5 minutes lockout
        $_SESSION['error'] = "Too many login attempts. Please try again in " . 
            ceil((300 - (time() - $_SESSION['last_attempt'])) / 60) . " minutes.";
        header("Location: ../login.php");
        exit();
    }
    // Reset attempts after lockout period
    unset($_SESSION['login_attempts']);
    unset($_SESSION['last_attempt']);
}

try {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Validate input
    if (empty($username) || empty($password)) {
        throw new Exception("Both username and password are required");
    }

    // Check user in database
    $query = "SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Database error: " . $conn->error);
    }

    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['password'])) {
            // Clear any existing session data
            session_unset();
            session_regenerate_id(true);
            
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['is_logged_in'] = true;
            $_SESSION['show_welcome'] = true;
            
            // Run auto-setup to ensure database schema is correct
            require_once __DIR__ . '/../database/auto_setup.php';
            
            // Close database connections
            $stmt->close();
            $conn->close();
            
            // Redirect to index.php
            $redirectUrl = "http://" . $_SERVER['HTTP_HOST'] . "/Pagibig/index.php";
            header("Location: " . $redirectUrl);
            exit();
        }
    }
    
    // If we get here, login failed
    $_SESSION['error'] = "Invalid username or password";
    
} catch (Exception $e) {
    error_log("Login error: " . $e->getMessage());
    $_SESSION['error'] = $e->getMessage();
} finally {
    // Close database connections if they exist
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($conn)) {
        $conn->close();
    }
}

// If we reach here, login failed
if (!isset($_SESSION['is_logged_in'])) {
    header("Location: ../login.php");
    exit();
}
?>
