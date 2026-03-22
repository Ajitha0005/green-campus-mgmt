<?php
require_once 'config.php';
require_once 'includes/header.php';

$msg = '';
$msgType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['date'];
    $dry = floatval($_POST['dry_waste']);
    $wet = floatval($_POST['wet_waste']);
    $user_id = $_SESSION['user_id'];

    $stmt = $pdo->prepare("INSERT INTO waste (date, dry_waste, wet_waste, created_by) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$date, $dry, $wet, $user_id])) {
        $msg = "Waste record added successfully!";
        $msgType = "success";
    } else {
        $msg = "Failed to add record.";
        $msgType = "error";
    }
}
?>

<div class="page-title">Waste Management</div>

<?php if ($msg): ?>
    <div class="alert alert-<?php echo $msgType; ?>"><?php echo htmlspecialchars($msg); ?></div>
<?php endif; ?>

<div class="form-card">
    <div class="form-header" style="display: flex; align-items: center; gap: 10px; margin-bottom: 2rem;">
        <span class="material-symbols-outlined" style="color: var(--primary-green); font-size: 2rem;">delete_outline</span>
        <h3 style="margin: 0; color: var(--white);">Add Waste Record</h3>
    </div>
    <form method="POST" action="">
        <div class="form-group">
            <label>Date</label>
            <input type="date" name="date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
        </div>
        <div class="form-group">
            <label>Dry Waste (kg)</label>
            <input type="number" step="0.01" name="dry_waste" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Wet Waste (kg)</label>
            <input type="number" step="0.01" name="wet_waste" class="form-control" required>
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
                    <th>Dry Waste (kg)</th>
                    <th>Wet Waste (kg)</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $stmt = $pdo->query("SELECT * FROM waste ORDER BY date DESC LIMIT 20");
                while($row = $stmt->fetch()):
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['date']); ?></td>
                    <td><?php echo htmlspecialchars($row['dry_waste']); ?></td>
                    <td><?php echo htmlspecialchars($row['wet_waste']); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
