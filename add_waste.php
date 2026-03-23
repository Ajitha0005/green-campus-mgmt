<?php
require_once 'config.php';
require_once 'includes/header.php';

$msg = '';
$msgType = '';

// Handle actions if Staff (Admin)
if (isAdmin()) {
    // Handle Delete
    if (isset($_GET['delete'])) {
        $id = intval($_GET['delete']);
        $stmt = $pdo->prepare("DELETE FROM waste WHERE id = ?");
        if ($stmt->execute([$id])) {
            $msg = "Record deleted successfully.";
            $msgType = "success";
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $date = $_POST['date'];
        $dry = floatval($_POST['dry_waste']);
        $wet = floatval($_POST['wet_waste']);
        $user_id = $_SESSION['user_id'];
        $record_id = isset($_POST['record_id']) ? intval($_POST['record_id']) : 0;

        if ($record_id > 0) {
            // Update
            $stmt = $pdo->prepare("UPDATE waste SET date=?, dry_waste=?, wet_waste=? WHERE id=?");
            if ($stmt->execute([$date, $dry, $wet, $record_id])) {
                $msg = "Waste record updated successfully!";
                $msgType = "success";
            }
        } else {
            // Insert
            $stmt = $pdo->prepare("INSERT INTO waste (date, dry_waste, wet_waste, created_by) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$date, $dry, $wet, $user_id])) {
                $msg = "Waste record added successfully!";
                $msgType = "success";
            } else {
                $msg = "Failed to add record.";
                $msgType = "error";
            }
        }
    }
}
?>

<div class="page-title">Waste Management</div>

<?php if ($msg): ?>
    <div class="alert alert-<?php echo $msgType; ?>"><?php echo htmlspecialchars($msg); ?></div>
<?php endif; ?>

<?php if (isAdmin()): ?>
<div class="form-card">
    <div class="form-header" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 2rem;">
        <div style="display: flex; align-items: center; gap: 10px;">
            <span class="material-symbols-outlined" style="color: var(--primary-green); font-size: 2rem;">delete_outline</span>
            <h3 id="form-title" style="margin: 0; color: var(--white);">Add Waste Record</h3>
        </div>
    </div>
    <form method="POST" action="">
        <input type="hidden" name="record_id" id="record_id" value="">
        <div class="form-group">
            <label>Date</label>
            <input type="date" name="date" id="date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
        </div>
        <div class="form-group">
            <label>Dry Waste (kg)</label>
            <input type="number" step="0.01" name="dry_waste" id="dry_waste" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Wet Waste (kg)</label>
            <input type="number" step="0.01" name="wet_waste" id="wet_waste" class="form-control" required>
        </div>
        <button type="submit" id="submit-btn" class="btn-primary">Save Record</button>
        <button type="button" id="cancel-btn" onclick="resetForm()" class="btn-danger" style="display: none; background: transparent; border: 1px solid var(--border-color); color: var(--text-muted); width: 100%; margin-top: 0.5rem;">Cancel Edit</button>
    </form>
</div>
<?php endif; ?>

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
                    <?php if (isAdmin()): ?>
                    <th>Actions</th>
                    <?php endif; ?>
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
                    <?php if (isAdmin()): ?>
                    <td>
                        <button onclick='editRecord(<?php echo json_encode($row); ?>)' class="btn-primary" style="padding: 5px 10px; width: auto; font-size: 0.8rem; margin-right: 5px;">Edit</button>
                        <a href="?delete=<?php echo $row['id']; ?>" class="btn-danger" onclick="return confirm('Are you sure you want to delete this record?')" style="padding: 5px 10px; font-size: 0.8rem; text-decoration: none; display: inline-block;">Delete</a>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if (isAdmin()): ?>
<script>
function editRecord(record) {
    document.getElementById('form-title').innerText = 'Edit Waste Record';
    document.getElementById('record_id').value = record.id;
    document.getElementById('date').value = record.date;
    document.getElementById('dry_waste').value = record.dry_waste;
    document.getElementById('wet_waste').value = record.wet_waste;
    document.getElementById('submit-btn').innerText = 'Update Record';
    document.getElementById('cancel-btn').style.display = 'block';
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function resetForm() {
    document.getElementById('form-title').innerText = 'Add Waste Record';
    document.getElementById('record_id').value = '';
    document.getElementById('date').value = '<?php echo date('Y-m-d'); ?>';
    document.getElementById('dry_waste').value = '';
    document.getElementById('wet_waste').value = '';
    document.getElementById('submit-btn').innerText = 'Save Record';
    document.getElementById('cancel-btn').style.display = 'none';
}
</script>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
