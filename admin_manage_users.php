<?php
require_once 'config.php';
require_once 'includes/auth.php';
requireAdmin();

$msg = '';
$msgType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $msg = "Email already exists!";
        $msgType = "error";
    } else {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'staff')");
        if ($stmt->execute([$name, $email, $hash])) {
            $msg = "Staff account created successfully!";
            $msgType = "success";
        }
    }
}

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'staff'");
    if ($stmt->execute([$id])) {
        $msg = "Staff member deleted.";
        $msgType = "success";
    }
}

$stmt = $pdo->query("SELECT id, name, email FROM users WHERE role = 'staff'");
$staffList = $stmt->fetchAll();

require_once 'includes/header.php';
?>

<div class="page-title">Manage Staff Users</div>

<?php if ($msg): ?>
    <div class="alert alert-<?php echo $msgType; ?>"><?php echo htmlspecialchars($msg); ?></div>
<?php endif; ?>

<div class="grid-cards" style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));">
    <!-- Add Staff Form -->
    <div class="form-card" style="margin: 0; width: 100%;">
        <h3 style="margin-bottom: 1.5rem; color: var(--dark-green);">Add New Staff</h3>
        <form method="POST" action="">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn-primary">Create Staff Account</button>
        </form>
    </div>

    <!-- Staff List -->
    <div class="table-card" style="margin-top: 0; width: 100%;">
        <div class="table-header">
            <h3 style="color: var(--dark-green);">Existing Staff</h3>
        </div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($staffList) > 0): ?>
                        <?php foreach($staffList as $staff): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($staff['name']); ?></td>
                            <td><?php echo htmlspecialchars($staff['email']); ?></td>
                            <td>
                                <a href="?delete=<?php echo $staff['id']; ?>" class="btn-danger" onclick="return confirm('Delete this staff member?');">Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="3" style="text-align: center;">No staff found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
