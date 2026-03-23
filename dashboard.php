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

function formatNumber($num) {
    if ($num >= 1000000) return round($num / 1000000, 1) . 'M';
    if ($num >= 100000) return round($num / 100000, 1) . 'L'; // 1 Lakh = 100k
    if ($num >= 1000) return round($num / 1000, 1) . 'K';
    return is_numeric($num) ? number_format($num, ($num == (int)$num ? 0 : 1)) : $num;
}

// Fetch Generic Chart Data (Monthly)
$chartStmt = $pdo->query("SELECT month, electricity_units, water_usage FROM energy_usage ORDER BY id DESC LIMIT 6");
$chartData = array_reverse($chartStmt->fetchAll());
$months = json_encode(array_column($chartData, 'month'));
$elecData = json_encode(array_column($chartData, 'electricity_units'));
$waterData = json_encode(array_column($chartData, 'water_usage'));

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
?>

<div class="page-title">Dashboard Overview</div>

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
        <div class="card-value"><?php echo formatNumber($totalElec); ?></div>
    </div>
    <div class="card">
        <span class="material-symbols-outlined card-icon">water_drop</span>
        <div class="card-title">Water Usage (L)</div>
        <div class="card-value"><?php echo formatNumber($totalWater); ?></div>
    </div>
    <div class="card">
        <span class="material-symbols-outlined card-icon">delete_forever</span>
        <div class="card-title">Total Waste (kg)</div>
        <div class="card-value"><?php echo formatNumber($totalDry + $totalWet); ?></div>
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
<div class="grid-cards" style="grid-template-columns: 1fr;">
    <div class="form-card" style="max-width: 100%; margin-top: 0;">
        <div class="table-header">
            <h3>Share Your Suggestions</h3>
            <p style="color: var(--text-muted); font-size: 0.9rem;">Help us make our campus greener and smarter</p>
        </div>
        <form action="feedback_handler.php" method="POST" style="margin-top: 1.5rem;">
            <div class="form-group">
                <textarea name="message" class="form-control" placeholder="Your feedback or suggestions..." required style="height: 100px; padding: 1rem; border-radius: 15px;"></textarea>
            </div>
            <button type="submit" class="btn-primary" style="width: auto; padding: 0.8rem 2.5rem;">Send Feedback</button>
        </form>
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
