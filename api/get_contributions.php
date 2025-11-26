<?php
require_once __DIR__ . '/includes/ContributionModel.php';
require_once __DIR__ . '/includes/Response.php';

// Start session for user authentication
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    Response::unauthorized();
}

// Handle GET requests for fetching contributions
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        // Get query parameters
        $month = $_GET['month'] ?? null;
        $year = $_GET['year'] ?? null;

        if (!$month || !$year) {
            Response::error('Month and year are required');
        }

        // Initialize model
        $contributionModel = new ContributionModel();

        // Get contributions
        $contributions = $contributionModel->getContributionsByMonthYear($month, $year);
        
        // Get totals
        $totals = $contributionModel->getTotalContributions($month, $year);

        // Return success response
        Response::success([
            'contributions' => $contributions,
            'totals' => $totals
        ]);

    } catch (Exception $e) {
        error_log("Error in get_contributions.php: " . $e->getMessage());
        Response::error('Failed to fetch contributions');
    }
}

// Handle POST requests for creating/updating contributions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get POST data
        $data = $_POST;
        
        // Initialize model
        $contributionModel = new ContributionModel();

        // Save contribution
        $id = $contributionModel->saveContribution($data);

        // Return success response
        Response::success([
            'id' => $id
        ], 'Contribution saved successfully');

    } catch (Exception $e) {
        error_log("Error in get_contributions.php: " . $e->getMessage());
        Response::error('Failed to save contribution');
    }
}
