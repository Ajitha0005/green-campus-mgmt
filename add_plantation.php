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
        $stmt = $pdo->prepare("DELETE FROM plantation WHERE id = ?");
        if ($stmt->execute([$id])) {
            $msg = "Record deleted successfully.";
            $msgType = "success";
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $tree_name = trim($_POST['tree_name']);
        $location = trim($_POST['location']);
        $date_planted = $_POST['date_planted'];
        $user_id = $_SESSION['user_id'];
        $record_id = isset($_POST['record_id']) ? intval($_POST['record_id']) : 0;

        if ($record_id > 0) {
            $stmt = $pdo->prepare("UPDATE plantation SET tree_name=?, location=?, date_planted=? WHERE id=?");
            if ($stmt->execute([$tree_name, $location, $date_planted, $record_id])) {
                $msg = "Plantation record updated successfully!";
                $msgType = "success";
            }
        } else {
            $stmt = $pdo->prepare("INSERT INTO plantation (tree_name, location, date_planted, created_by) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$tree_name, $location, $date_planted, $user_id])) {
                $msg = "Plantation record added successfully!";
                $msgType = "success";
            } else {
                $msg = "Failed to add record.";
                $msgType = "error";
            }
        }
    }
}
?>

<div class="page-title">Plantation Records</div>

<?php if ($msg): ?>
    <div class="alert alert-<?php echo $msgType; ?>"><?php echo htmlspecialchars($msg); ?></div>
<?php endif; ?>

<?php if (isAdmin()): ?>
<div class="form-card">
    <div class="form-header" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 2rem;">
        <div style="display: flex; align-items: center; gap: 10px;">
            <span class="material-symbols-outlined" style="color: var(--primary-green); font-size: 2rem;">forest</span>
            <h3 id="form-title" style="margin: 0; color: var(--white);">Add Plantation Record</h3>
        </div>
    </div>
    <form method="POST" action="">
        <input type="hidden" name="record_id" id="record_id" value="">
        <div class="form-group">
            <label>Tree Name</label>
            <input type="text" name="tree_name" id="tree_name" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Location</label>
            <input type="text" name="location" id="location" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Date Planted</label>
            <input type="date" name="date_planted" id="date_planted" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
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
                    <th>Tree Name</th>
                    <th>Location</th>
                    <th>Date Planted</th>
                    <?php if (isAdmin()): ?>
                    <th>Actions</th>
                    <?php endif; ?>
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
    document.getElementById('form-title').innerText = 'Edit Plantation Record';
    document.getElementById('record_id').value = record.id;
    document.getElementById('tree_name').value = record.tree_name;
    document.getElementById('location').value = record.location;
    document.getElementById('date_planted').value = record.date_planted;
    document.getElementById('submit-btn').innerText = 'Update Record';
    document.getElementById('cancel-btn').style.display = 'block';
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function resetForm() {
    document.getElementById('form-title').innerText = 'Add Plantation Record';
    document.getElementById('record_id').value = '';
    document.getElementById('tree_name').value = '';
    document.getElementById('location').value = '';
    document.getElementById('date_planted').value = '<?php echo date('Y-m-d'); ?>';
    document.getElementById('submit-btn').innerText = 'Save Record';
    document.getElementById('cancel-btn').style.display = 'none';
}
</script>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
