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
        $stmt = $pdo->prepare("DELETE FROM energy_usage WHERE id = ?");
        if ($stmt->execute([$id])) {
            $msg = "Record deleted successfully.";
            $msgType = "success";
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $date = $_POST['date'];
        $electricity = intval($_POST['electricity']);
        $water = intval($_POST['water']);
        $user_id = $_SESSION['user_id'];
        $record_id = isset($_POST['record_id']) ? intval($_POST['record_id']) : 0;

        // Defensive Check for 'date' vs 'month'
        $colCheck = $pdo->query("SHOW COLUMNS FROM energy_usage LIKE 'date'");
        $colName = $colCheck->fetch() ? 'date' : 'month';

        if ($record_id > 0) {
            $stmt = $pdo->prepare("UPDATE energy_usage SET $colName=?, electricity_units=?, water_usage=? WHERE id=?");
            if ($stmt->execute([$date, $electricity, $water, $record_id])) {
                $msg = "Energy record updated successfully!";
                $msgType = "success";
            }
        } else {
            $stmt = $pdo->prepare("INSERT INTO energy_usage ($colName, electricity_units, water_usage, created_by) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$date, $electricity, $water, $user_id])) {
                $msg = "Energy record added successfully!";
                $msgType = "success";
            } else {
                $msg = "Failed to add record.";
                $msgType = "error";
            }
        }
    }
}
?>

<div class="page-title">Energy Management</div>

<?php if ($msg): ?>
    <div class="alert alert-<?php echo $msgType; ?>"><?php echo htmlspecialchars($msg); ?></div>
<?php endif; ?>

<?php if (isAdmin()): ?>
<div class="form-card">
    <div class="form-header" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 2rem;">
        <div style="display: flex; align-items: center; gap: 10px;">
            <span class="material-symbols-outlined" style="color: var(--primary-green); font-size: 2rem;">bolt</span>
            <h3 id="form-title" style="margin: 0; color: var(--white);">Add Energy Record</h3>
        </div>
    </div>
    <form method="POST" action="">
        <input type="hidden" name="record_id" id="record_id" value="">
        <div class="form-group">
            <label>Date</label>
            <input type="date" name="date" id="date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
        </div>
        <div class="form-group">
            <label>Electricity Units (kWh)</label>
            <input type="number" name="electricity" id="electricity" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Water Usage (Liters)</label>
            <input type="number" name="water" id="water" class="form-control" required>
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
                    <th>Electricity Units</th>
                    <th>Water Usage</th>
                    <th>Added On</th>
                    <?php if (isAdmin()): ?>
                    <th>Actions</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php
                $colCheck = $pdo->query("SHOW COLUMNS FROM energy_usage LIKE 'date'");
                $colName = $colCheck->fetch() ? 'date' : 'month';
                $stmt = $pdo->query("SELECT * FROM energy_usage ORDER BY id DESC LIMIT 20");
                while($row = $stmt->fetch()):
                    // Pass the dynamic date column safely
                    $row['date_val'] = $row[$colName];
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($row[$colName]); ?></td>
                    <td><?php echo htmlspecialchars($row['electricity_units']); ?></td>
                    <td><?php echo htmlspecialchars($row['water_usage']); ?></td>
                    <td><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
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
    document.getElementById('form-title').innerText = 'Edit Energy Record';
    document.getElementById('record_id').value = record.id;
    document.getElementById('date').value = record.date_val;
    document.getElementById('electricity').value = record.electricity_units;
    document.getElementById('water').value = record.water_usage;
    document.getElementById('submit-btn').innerText = 'Update Record';
    document.getElementById('cancel-btn').style.display = 'block';
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function resetForm() {
    document.getElementById('form-title').innerText = 'Add Energy Record';
    document.getElementById('record_id').value = '';
    document.getElementById('date').value = '<?php echo date('Y-m-d'); ?>';
    document.getElementById('electricity').value = '';
    document.getElementById('water').value = '';
    document.getElementById('submit-btn').innerText = 'Save Record';
    document.getElementById('cancel-btn').style.display = 'none';
}
</script>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
