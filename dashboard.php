<?php
require_once 'config.php';
require_once 'includes/header.php';

// Fetch summary metrics
$treesStmt = $pdo->query("SELECT COUNT(*) as total FROM plantation");
$totalTrees = $treesStmt->fetch()['total'];

$wasteStmt = $pdo->query("SELECT SUM(dry_waste) as total_dry, SUM(wet_waste) as total_wet FROM waste");
$waste = $wasteStmt->fetch();
$totalDry = $waste['total_dry'] ?? 0;
$totalWet = $waste['total_wet'] ?? 0;

$energyStmt = $pdo->query("SELECT SUM(electricity_units) as total_elec, SUM(water_usage) as total_water FROM energy_usage");
$energy = $energyStmt->fetch();
$totalElec = $energy['total_elec'] ?? 0;
$totalWater = $energy['total_water'] ?? 0;

// Fetch Waste Chart Data (Last 6 entries)
$wasteChartStmt = $pdo->query("SELECT date, dry_waste, wet_waste FROM waste ORDER BY date DESC LIMIT 6");
$wasteChartData = array_reverse($wasteChartStmt->fetchAll());
$wasteDates = json_encode(array_column($wasteChartData, 'date'));
$dryData = json_encode(array_column($wasteChartData, 'dry_waste'));
$wetData = json_encode(array_column($wasteChartData, 'wet_waste'));

function formatNumber($num, $unit = '') {
    $num = (float)$num;
    if ($num >= 1000000000) {
        $val = round($num / 1000000000, 1) . 'B';
    } elseif ($num >= 1000000) {
        $val = round($num / 1000000, 1) . 'M';
    } elseif ($num >= 1000) {
        $val = round($num / 1000, 1) . 'K';
    } else {
        $val = number_format($num, ($num == (int)$num ? 0 : 1));
    }
    $val = str_replace('.0', '', (string)$val);
    return $val . ($unit ? ' ' . $unit : '');
}

// Fetch Generic Chart Data (Monthly/Daily)
try {
    // Check if 'date' column exists, otherwise fallback to 'month'
    $colCheck = $pdo->query("SHOW COLUMNS FROM energy_usage LIKE 'date'");
    $dateCol = $colCheck->fetch() ? 'date' : 'month';
    $chartStmt = $pdo->query("SELECT $dateCol as label, electricity_units, water_usage FROM energy_usage ORDER BY id DESC LIMIT 6");
    $chartData = array_reverse($chartStmt->fetchAll());
    $months = json_encode(array_column($chartData, 'label'));
    $elecData = json_encode(array_column($chartData, 'electricity_units'));
    $waterData = json_encode(array_column($chartData, 'water_usage'));
} catch (Exception $e) {
    $months = json_encode([]);
    $elecData = json_encode([]);
    $waterData = json_encode([]);
}

// Fetch Recent Feedback
$feedbackListStmt = $pdo->query("SELECT f.*, u.name as user_name FROM feedback f JOIN users u ON f.user_id = u.id ORDER BY f.created_at DESC LIMIT 5");
$recentFeedbacks = $feedbackListStmt->fetchAll();

// Usage Alerts Logic
$alerts = [];
$latestEnergy = end($chartData);
if ($latestEnergy) {
    if ($latestEnergy['electricity_units'] > 500) {
        $alerts[] = ["type" => "danger", "msg" => "High Electricity Usage: " . $latestEnergy['electricity_units'] . " units. Consider checking heavy equipment."];
    }
    if ($latestEnergy['water_usage'] > 2000) {
        $alerts[] = ["type" => "warning", "msg" => "Water Usage Alert: " . $latestEnergy['water_usage'] . " Liters. Potential leak detected."];
    }
}

// Check if migration is needed (Admin Only)
$migrationNeeded = false;
if (isAdmin()) {
    $checkEvents = $pdo->query("SHOW COLUMNS FROM events LIKE 'google_form_url'");
    $checkEnergy = $pdo->query("SHOW COLUMNS FROM energy_usage LIKE 'date'");
    if (!$checkEvents->fetch() || !$checkEnergy->fetch()) {
        $migrationNeeded = true;
    }
}
?>

<div class="page-title">Dashboard Overview</div>

<!-- Migration Required Alert (Admin Only) -->
<?php if ($migrationNeeded): ?>
<div class="alert alert-warning" style="margin-bottom: 2rem; border-left: 5px solid #f59e0b; background: rgba(245, 158, 11, 0.1);">
    <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
        <div>
            <strong style="color: #92400e;">Database Update Required!</strong>
            <p style="margin: 0.2rem 0 0; font-size: 0.85rem; color: #b45309;">Some new features require a database schema update. Click the button to sync.</p>
        </div>
        <a href="migrate_db.php" class="btn-primary" style="width: auto; padding: 0.6rem 1.2rem; font-size: 0.85rem; background: #f59e0b; text-decoration: none;">Run Migration Now</a>
    </div>
</div>
<?php endif; ?>

<!-- Alerts Section -->
<?php if (!empty($alerts)): ?>
<div class="alerts-container" style="margin-bottom: 2rem;">
    <?php foreach ($alerts as $alert): ?>
        <div class="alert alert-<?php echo $alert['type'] == 'danger' ? 'error' : 'success'; ?>" 
             style="display: flex; align-items: center; gap: 15px; border-radius: 12px; padding: 1rem 1.5rem;">
            <span class="material-symbols-outlined"><?php echo $alert['type'] == 'danger' ? 'report' : 'check_circle'; ?></span>
            <span style="font-weight: 500; font-size: 0.95rem;"><?php echo $alert['msg']; ?></span>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="grid-cards">
    <div class="card">
        <span class="material-symbols-outlined card-icon">forest</span>
        <div class="card-title">Trees Planted</div>
        <div class="card-value"><?php echo formatNumber($totalTrees); ?></div>
    </div>
    <div class="card">
        <span class="material-symbols-outlined card-icon">bolt</span>
        <div class="card-title">Elec Usage (kWh)</div>
        <div class="card-value"><?php echo formatNumber($totalElec, 'KWH'); ?></div>
    </div>
    <div class="card">
        <span class="material-symbols-outlined card-icon">water_drop</span>
        <div class="card-title">Water Usage (L)</div>
        <div class="card-value"><?php echo formatNumber($totalWater, 'L'); ?></div>
    </div>
    <div class="card">
        <span class="material-symbols-outlined card-icon">delete_forever</span>
        <div class="card-title">Total Waste (kg)</div>
        <div class="card-value"><?php echo formatNumber($totalDry + $totalWet, 'KG'); ?></div>
    </div>
</div>

<!-- Charts Section -->
<div class="grid-cards" style="grid-template-columns: 1fr;">
    <div class="table-card">
        <div class="table-header">
            <h3>Usage Trends Analysis</h3>
            <p style="color: var(--text-muted); font-size: 0.9rem;">Visual comparison of Electricity and Water consumption (Monthly)</p>
        </div>
        <div style="height: 400px; margin-top: 1rem;">
            <canvas id="usageChart"></canvas>
        </div>
    </div>
</div>

<div class="grid-cards" style="grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));">
    <!-- Waste Management Graph -->
    <div class="table-card" style="margin-top: 0;">
        <div class="table-header">
            <h3>Waste Analysis (Dry vs Wet)</h3>
        </div>
        <div style="height: 300px; margin-top: 1rem;">
            <canvas id="wasteChart"></canvas>
        </div>
    </div>

    <!-- Recent Plantations -->
    <div class="table-card" style="margin-top: 0;">
        <div class="table-header">
            <h3>Recent Plantations</h3>
        </div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Tree Name</th>
                        <th>Location</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $pdo->query("SELECT tree_name, location, date_planted FROM plantation ORDER BY date_planted DESC LIMIT 5");
                    while($row = $stmt->fetch()):
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['tree_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['location']); ?></td>
                        <td><?php echo htmlspecialchars($row['date_planted']); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Feedback Section -->
    <div class="table-card" style="margin-top: 0; padding: 2.5rem;">
        <div class="table-header">
            <h3>Share Your Suggestions</h3>
            <p style="color: var(--text-muted); font-size: 0.9rem;">Help us make our campus greener and smarter</p>
        </div>
        <form action="feedback_handler.php" method="POST" style="margin-top: 1.5rem;">
            <div class="form-group">
                <textarea name="message" class="form-control" placeholder="Your feedback or suggestions..." required style="height: 120px; padding: 1.2rem; border-radius: 18px; font-size: 1.1rem; border: 1.5px solid var(--border-color);"></textarea>
            </div>
            <button type="submit" class="btn-primary" style="width: auto; padding: 1rem 3rem;">Send Feedback</button>
        </form>

        <!-- Feedback Display -->
        <div class="feedback-list" style="margin-top: 3rem; border-top: 1px solid var(--border-color); padding-top: 2rem;">
            <h4 style="margin-bottom: 1.5rem; color: var(--dark-green);">Recent Feedback</h4>
            <?php if (empty($recentFeedbacks)): ?>
                <p style="color: var(--text-muted);">No feedback yet. Be the first to share!</p>
            <?php else: ?>
                <?php foreach ($recentFeedbacks as $fb): ?>
                    <div class="feedback-item" style="background: rgba(16, 185, 129, 0.03); padding: 1.5rem; border-radius: 15px; margin-bottom: 1rem; border: 1px dashed var(--primary-green);">
                        <p style="color: var(--text-main); line-height: 1.6; font-size: 1.05rem;">"<?php echo htmlspecialchars($fb['message']); ?>"</p>
                        <div style="margin-top: 0.8rem; display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-weight: 700; color: var(--primary-green); font-size: 0.9rem;">- <?php echo htmlspecialchars($fb['user_name']); ?></span>
                            <span style="color: var(--text-muted); font-size: 0.8rem;"><?php echo date('d M Y, h:i A', strtotime($fb['created_at'])); ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('usageChart').getContext('2d');
const myChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?php echo $months; ?>,
        datasets: [{
            label: 'Electricity (kWh)',
            data: <?php echo $elecData; ?>,
            backgroundColor: 'rgba(16, 185, 129, 0.2)',
            borderColor: '#10b981',
            borderWidth: 2,
            borderRadius: 8,
            tension: 0.4
        }, {
            label: 'Water (L)',
            data: <?php echo $waterData; ?>,
            backgroundColor: 'rgba(6, 78, 59, 0.1)',
            borderColor: '#064e3b',
            borderWidth: 2,
            borderRadius: 8,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top',
                labels: { color: '#475569', font: { family: 'Outfit', size: 13, weight: '600' }, usePointStyle: true, padding: 20 }
            },
            tooltip: {
                backgroundColor: '#064e3b',
                titleFont: { family: 'Outfit', size: 14 },
                bodyFont: { family: 'Outfit', size: 13 },
                padding: 12,
                cornerRadius: 10,
                displayColors: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: { color: 'rgba(0, 0, 0, 0.04)', drawBorder: false },
                ticks: { color: '#64748b', font: { family: 'Outfit', size: 12 } }
            },
            x: {
                grid: { display: false },
                ticks: { color: '#64748b', font: { family: 'Outfit', size: 12 } }
            }
        }
    }
});

// Waste Chart
const ctxWaste = document.getElementById('wasteChart').getContext('2d');
new Chart(ctxWaste, {
    type: 'line',
    data: {
        labels: <?php echo $wasteDates; ?>,
        datasets: [{
            label: 'Dry Waste (kg)',
            data: <?php echo $dryData; ?>,
            borderColor: '#10b981',
            backgroundColor: 'rgba(16, 185, 129, 0.1)',
            fill: true,
            tension: 0.4
        }, {
            label: 'Wet Waste (kg)',
            data: <?php echo $wetData; ?>,
            borderColor: '#064e3b',
            backgroundColor: 'rgba(6, 78, 59, 0.1)',
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'top', labels: { usePointStyle: true, font: { family: 'Outfit' } } } },
        scales: { 
            y: { beginAtZero: true, grid: { display: false } },
            x: { grid: { display: false } }
        }
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
