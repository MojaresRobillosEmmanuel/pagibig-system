<?php
session_start();
$_SESSION['user_id'] = 1;
$_GET['month'] = 'January';
$_GET['year'] = '2025';

// Test get_contributions.php
include 'includes/get_contributions.php';
?>
