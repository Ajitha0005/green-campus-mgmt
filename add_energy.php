<?php
require_once 'config.php';
require_once 'includes/header.php';

$msg = '';
$msgType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['date'];
    $electricity = intval($_POST['electricity']);
    $water = intval($_POST['water']);
    $user_id = $_SESSION['user_id'];

    // Defensive Check for 'date' vs 'month'
    $colCheck = $pdo->query("SHOW COLUMNS FROM energy_usage LIKE 'date'");
    $colName = $colCheck->fetch() ? 'date' : 'month';

    $stmt = $pdo->prepare("INSERT INTO energy_usage ($colName, electricity_units, water_usage, created_by) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$date, $electricity, $water, $user_id])) {
        $msg = "Energy record added successfully!";
        $msgType = "success";
    } else {
        $msg = "Failed to add record.";
        $msgType = "error";
    }
}
?>

<div class="page-title">Energy Management</div>

<?php if ($msg): ?>
    <div class="alert alert-<?php echo $msgType; ?>"><?php echo htmlspecialchars($msg); ?></div>
<?php endif; ?>

<div class="form-card">
    <div class="form-header" style="display: flex; align-items: center; gap: 10px; margin-bottom: 2rem;">
        <span class="material-symbols-outlined" style="color: var(--primary-green); font-size: 2rem;">bolt</span>
        <h3 style="margin: 0; color: var(--white);">Add Energy Record</h3>
    </div>
    <form method="POST" action="">
        <div class="form-group">
            <label>Date</label>
            <input type="date" name="date" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Electricity Units (kWh)</label>
            <input type="number" name="electricity" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Water Usage (Liters)</label>
            <input type="number" name="water" class="form-control" required>
        </div>
        <button type="submit" class="btn-primary">Save Record</button>
    </form>
</div>

<div class="table-card">
    <div class="table-header">
        <h3>Previous Entries</h3>
    </div>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Electricity Units</th>
                    <th>Water Usage</th>
                    <th>Added On</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $colCheck = $pdo->query("SHOW COLUMNS FROM energy_usage LIKE 'date'");
                $colName = $colCheck->fetch() ? 'date' : 'month';
                $stmt = $pdo->query("SELECT * FROM energy_usage ORDER BY id DESC LIMIT 10");
                while($row = $stmt->fetch()):
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($row[$colName]); ?></td>
                    <td><?php echo htmlspecialchars($row['electricity_units']); ?></td>
                    <td><?php echo htmlspecialchars($row['water_usage']); ?></td>
                    <td><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
