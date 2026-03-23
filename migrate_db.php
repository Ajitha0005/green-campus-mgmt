<?php
require_once 'config.php';

try {
    // Add google_form_url to events
    $pdo->exec("ALTER TABLE events ADD COLUMN IF NOT EXISTS google_form_url VARCHAR(255) AFTER location");
    
    // Change energy_usage.month to energy_usage.date
    // First check if 'month' exists
    $stmt = $pdo->query("SHOW COLUMNS FROM energy_usage LIKE 'month'");
    if ($stmt->fetch()) {
        $pdo->exec("ALTER TABLE energy_usage CHANGE COLUMN month date DATE");
    }
    
    echo "Migration successful! Redirecting...";
    header("Refresh: 2; URL=dashboard.php");
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage();
}
?>
