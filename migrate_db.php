<?php
require_once 'config.php';

try {
    // Add google_form_url to events
    $pdo->exec("ALTER TABLE events ADD COLUMN IF NOT EXISTS google_form_url VARCHAR(255) AFTER location");
    
    // Change energy_usage.month to energy_usage.date securely
    $hasMonth = $pdo->query("SHOW COLUMNS FROM energy_usage LIKE 'month'")->fetch();
    $hasDate = $pdo->query("SHOW COLUMNS FROM energy_usage LIKE 'date'")->fetch();
    
    if ($hasMonth && !$hasDate) {
        // Step 1: Add new DATE column
        $pdo->exec("ALTER TABLE energy_usage ADD COLUMN date DATE AFTER id");
        
        // Step 2: Safely migrate data. If format is YYYY-MM, append -01. Otherwise fallback to current date
        $pdo->exec("
            UPDATE energy_usage 
            SET date = CASE 
                WHEN month REGEXP '^[0-9]{4}-[0-9]{2}$' THEN CONCAT(month, '-01')
                ELSE CURRENT_DATE
            END
        ");
        
        // Step 3: Drop the old month column
        $pdo->exec("ALTER TABLE energy_usage DROP COLUMN month");
    }
    
    echo "Migration successful! Redirecting...";
    header("Refresh: 2; URL=dashboard.php");
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage();
}
?>
