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

// Fetch Chart Data (Last 6 entries)
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
        $alerts[] = ["type" => "danger", "msg" => "High Electricity Usage: " . $latestEnergy['electricity_units'] . " units in " . $latestEnergy['month'] . ". Consider checking heavy equipment."];
    }
    if ($latestEnergy['water_usage'] > 2000) {
        $alerts[] = ["type" => "warning", "msg" => "Water Usage Alert: " . $latestEnergy['water_usage'] . " Liters in " . $latestEnergy['month'] . ". Potential leak detected."];
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
        <div class="card-value"><?php echo number_format($totalTrees); ?></div>
    </div>
    <div class="card">
        <span class="material-symbols-outlined card-icon">bolt</span>
        <div class="card-title">Elec Usage (kWh)</div>
        <div class="card-value"><?php echo number_format($totalElec); ?></div>
    </div>
    <div class="card">
        <span class="material-symbols-outlined card-icon">water_drop</span>
        <div class="card-title">Water Usage (L)</div>
        <div class="card-value"><?php echo number_format($totalWater); ?></div>
    </div>
    <div class="card">
        <span class="material-symbols-outlined card-icon">delete_forever</span>
        <div class="card-title">Total Waste (kg)</div>
        <div class="card-value"><?php echo number_format($totalDry + $totalWet, 1); ?></div>
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
    <!-- Recent Energy -->
    <div class="table-card" style="margin-top: 0;">
        <div class="table-header">
            <h3>Recent Energy Logs</h3>
        </div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Month</th>
                        <th>Elec (kWh)</th>
                        <th>Water (L)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $pdo->query("SELECT month, electricity_units, water_usage FROM energy_usage ORDER BY id DESC LIMIT 5");
                    while($row = $stmt->fetch()):
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['month']); ?></td>
                        <td><?php echo htmlspecialchars($row['electricity_units']); ?></td>
                        <td><?php echo htmlspecialchars($row['water_usage']); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
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
</script>

<?php require_once 'includes/footer.php'; ?>
