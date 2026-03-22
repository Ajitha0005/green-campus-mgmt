<?php
require_once 'config.php';
require_once 'includes/header.php';

$msg = '';
$msgType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tree_name = trim($_POST['tree_name']);
    $location = trim($_POST['location']);
    $date_planted = $_POST['date_planted'];
    $user_id = $_SESSION['user_id'];

    $stmt = $pdo->prepare("INSERT INTO plantation (tree_name, location, date_planted, created_by) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$tree_name, $location, $date_planted, $user_id])) {
        $msg = "Plantation record added successfully!";
        $msgType = "success";
    } else {
        $msg = "Failed to add record.";
        $msgType = "error";
    }
}
?>

<div class="page-title">Plantation Records</div>

<?php if ($msg): ?>
    <div class="alert alert-<?php echo $msgType; ?>"><?php echo htmlspecialchars($msg); ?></div>
<?php endif; ?>

<div class="form-card">
    <div class="form-header" style="display: flex; align-items: center; gap: 10px; margin-bottom: 2rem;">
        <span class="material-symbols-outlined" style="color: var(--primary-green); font-size: 2rem;">forest</span>
        <h3 style="margin: 0; color: var(--white);">Add Plantation Record</h3>
    </div>
    <form method="POST" action="">
        <div class="form-group">
            <label>Tree Name</label>
            <input type="text" name="tree_name" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Location</label>
            <input type="text" name="location" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Date Planted</label>
            <input type="date" name="date_planted" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
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
                    <th>Tree Name</th>
                    <th>Location</th>
                    <th>Date Planted</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $stmt = $pdo->query("SELECT * FROM plantation ORDER BY date_planted DESC LIMIT 20");
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

<?php require_once 'includes/footer.php'; ?>
