<?php
require_once 'config.php';
require_once 'includes/header.php';
requireAdmin();

$msg = '';
$msgType = '';

// Handle Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
    if ($stmt->execute([$id])) {
        $msg = "Event deleted successfully.";
        $msgType = "success";
    }
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $desc = trim($_POST['description']);
    $date = $_POST['event_date'];
    $loc = trim($_POST['location']);
    $form_url = trim($_POST['google_form_url']);

    if (isset($_POST['event_id']) && !empty($_POST['event_id'])) {
        // Update
        $stmt = $pdo->prepare("UPDATE events SET title=?, description=?, event_date=?, location=?, google_form_url=? WHERE id=?");
        if ($stmt->execute([$title, $desc, $date, $loc, $form_url, intval($_POST['event_id'])])) {
            $msg = "Event updated!";
            $msgType = "success";
        }
    } else {
        // Add
        $stmt = $pdo->prepare("INSERT INTO events (title, description, event_date, location, google_form_url) VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([$title, $desc, $date, $loc, $form_url])) {
            $msg = "Event added!";
            $msgType = "success";
        }
    }
}

$events = $pdo->query("SELECT * FROM events ORDER BY event_date DESC")->fetchAll();
?>

<div class="page-title">Manage Green Events</div>

<?php if ($msg): ?>
    <div class="alert alert-<?php echo $msgType; ?>"><?php echo htmlspecialchars($msg); ?></div>
<?php endif; ?>

<div class="grid-cards" style="grid-template-columns: 1fr 2fr;">
    <!-- Add/Edit Form -->
    <div class="form-card" style="margin: 0;">
        <h3 id="form-title" style="margin-bottom: 2rem;">Add New Event</h3>
        <form method="POST" action="">
            <input type="hidden" name="event_id" id="event_id">
            <div class="form-group">
                <label>Event Title</label>
                <input type="text" name="title" id="title" class="form-control" required placeholder="e.g. Tree Plantation Drive">
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" id="description" class="form-control" required style="height: 100px;"></textarea>
            </div>
            <div class="form-group">
                <label>Date</label>
                <input type="date" name="event_date" id="event_date" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Location</label>
                <input type="text" name="location" id="location" class="form-control" required placeholder="e.g. Main Lobby">
            </div>
            <div class="form-group">
                <label>Google Form URL (for Registration)</label>
                <input type="url" name="google_form_url" id="google_form_url" class="form-control" placeholder="https://forms.gle/...">
            </div>
            <button type="submit" class="btn-primary">Save Event</button>
            <button type="button" onclick="resetForm()" class="btn-danger" style="background: transparent; border: 1px solid var(--border-color); color: var(--text-muted); width: 100%; margin-top: 0.5rem;">Reset</button>
        </form>
    </div>

    <!-- Events List -->
    <div class="table-card" style="margin: 0;">
        <h3>Scheduled Events</h3>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Event</th>
                        <th>Date</th>
                        <th>Location</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($events as $e): ?>
                    <tr>
                        <td style="font-weight: 600;"><?php echo htmlspecialchars($e['title']); ?></td>
                        <td><?php echo date('d M Y', strtotime($e['event_date'])); ?></td>
                        <td><?php echo htmlspecialchars($e['location']); ?></td>
                        <td>
                            <button onclick='editEvent(<?php echo json_encode($e); ?>)' class="btn-primary" style="padding: 5px 10px; width: auto; font-size: 0.8rem;">Edit</button>
                            <a href="?delete=<?php echo $e['id']; ?>" class="btn-danger" onclick="return confirm('Delete this event?')" style="padding: 5px 10px; font-size: 0.8rem; text-decoration: none;">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function editEvent(event) {
    document.getElementById('form-title').innerText = 'Edit Event';
    document.getElementById('event_id').value = event.id;
    document.getElementById('title').value = event.title;
    document.getElementById('description').value = event.description;
    document.getElementById('event_date').value = event.event_date;
    document.getElementById('location').value = event.location;
    document.getElementById('google_form_url').value = event.google_form_url || '';
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function resetForm() {
    document.getElementById('form-title').innerText = 'Add New Event';
    document.getElementById('event_id').value = '';
    document.getElementById('title').value = '';
    document.getElementById('description').value = '';
    document.getElementById('event_date').value = '';
    document.getElementById('location').value = '';
    document.getElementById('google_form_url').value = '';
}
</script>

<?php require_once 'includes/footer.php'; ?>
