<?php
require_once 'config.php';
require_once 'includes/auth.php';

$msg = '';
$msgType = '';

if (isset($_GET['deleted']) && $_GET['deleted'] == 1) {
    $msg = "Notice deleted successfully.";
    $msgType = "success";
}

// Handle actions if Staff (Admin)
if (isAdmin()) {
    // Delete action
    if (isset($_GET['delete'])) {
        $id = intval($_GET['delete']);
        try {
            // First, delete any lingering student registrations tied to this event to prevent FOREIGN KEY constraint failures!
            $pdo->prepare("DELETE FROM event_registrations WHERE event_id = ?")->execute([$id]);
        } catch (Exception $e) {
            // Ignore if the table doesnt exist or fails
        }
        
        try {
            $stmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
            if ($stmt->execute([$id])) {
                header("Location: events.php?deleted=1");
                exit;
            } else {
                $msg = "Failed to permanently delete the notice.";
                $msgType = "error";
            }
        } catch (Exception $e) {
            $msg = "Database Error: " . $e->getMessage();
            $msgType = "error";
        }
    }

    // Add/Edit action
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $event_date = $_POST['event_date'];
        $location = trim($_POST['location'] ?? 'Green Campus');
        $record_id = isset($_POST['record_id']) ? intval($_POST['record_id']) : 0;

        try {
            if ($record_id > 0) {
                $stmt = $pdo->prepare("UPDATE events SET title=?, description=?, event_date=?, location=? WHERE id=?");
                if ($stmt->execute([$title, $description, $event_date, $location, $record_id])) {
                    $msg = "Notice updated successfully!";
                    $msgType = "success";
                }
            } else {
                $stmt = $pdo->prepare("INSERT INTO events (title, description, event_date, location) VALUES (?, ?, ?, ?)");
                if ($stmt->execute([$title, $description, $event_date, $location])) {
                    $msg = "Notice added successfully!";
                    $msgType = "success";
                } else {
                    $msg = "Failed to add notice.";
                    $msgType = "error";
                }
            }
        } catch (Exception $e) {
            $msg = "Database Error: " . $e->getMessage();
            $msgType = "error";
        }
    }
}

// Now include header which outputs HTML
require_once 'includes/header.php';

// Fetch events
$stmt = $pdo->query("SELECT * FROM events ORDER BY event_date ASC");
$events = $stmt->fetchAll();
?>

<div class="page-title">Environmental awareness & Events</div>

<!-- Awareness Section -->
<div class="grid-cards" style="margin-bottom: 3rem; grid-template-columns: 1fr 1fr;">
    <div class="card" style="background: #000; color: #fff; border: none;">
        <span class="material-symbols-outlined card-icon" style="color: #fff; font-size: 3rem;">forest</span>
        <h2 style="margin-top: 1rem; font-size: 1.8rem; font-weight: 800; letter-spacing: -1px;">Importance of Plantation</h2>
        <p style="margin-top: 1rem; color: rgba(255,255,255,0.8); line-height: 1.6; font-size: 1.05rem;">
            Trees are the lungs of our campus. They reduce carbon footprints, provide shade, and enhance biodiversity. Every tree planted is a step toward a sustainable future.
        </p>
    </div>
    <div class="card">
        <span class="material-symbols-outlined card-icon" style="font-size: 3rem;">filter_vintage</span>
        <h2 style="margin-top: 1rem; font-size: 1.8rem; font-weight: 800; letter-spacing: -1px;">Management of Surroundings</h2>
        <p style="margin-top: 1rem; color: #666; line-height: 1.6; font-size: 1.05rem;">
            Clean and green surroundings boost mental well-being and productivity. Proper waste management and landscape upkeep ensure a healthy ecosystem for everyone.
        </p>
    </div>
</div>

<div style="display: flex; justify-content: space-between; align-items: center; margin-top: 2rem; margin-bottom: 1.5rem;">
    <div class="page-title" style="font-size: 1.4rem; color: #666; margin: 0;">Notice Board</div>
    <?php if (isAdmin()): ?>
    <button onclick="toggleNoticeForm()" class="btn-primary" style="width: auto; display: flex; align-items: center; gap: 8px; font-size: 0.9rem; padding: 10px 20px;">
        <span class="material-symbols-outlined" style="font-size: 1.2rem;">add</span> Add Notice
    </button>
    <?php endif; ?>
</div>

<?php if ($msg): ?>
    <div class="alert alert-<?php echo $msgType; ?>"><?php echo htmlspecialchars($msg); ?></div>
<?php endif; ?>

<?php if (isAdmin()): ?>
<!-- Admin Notice Form -->
<div id="notice-form-container" class="form-card" style="display: none; margin-bottom: 3rem; max-width: 800px; margin-left: 0;">
    <h3 id="form-title" style="margin-top: 0; margin-bottom: 1.5rem; color: var(--dark-green);">Add New Notice</h3>
    <form method="POST" action="">
        <input type="hidden" name="record_id" id="record_id" value="">
        <div class="form-group">
            <label>Notice Title</label>
            <input type="text" name="title" id="title" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Description</label>
            <textarea name="description" id="description" class="form-control" rows="4" required></textarea>
        </div>
        <div class="form-group" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div>
                <label>Date</label>
                <input type="date" name="event_date" id="event_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
            </div>
            <div>
                <label>Location / Department (Optional)</label>
                <input type="text" name="location" id="location" class="form-control" placeholder="e.g. Main Lobby">
            </div>
        </div>
        <button type="submit" id="submit-btn" class="btn-primary" style="margin-top: 1rem;">Save Notice</button>
        <button type="button" id="cancel-btn" onclick="cancelEdit()" class="btn-danger" style="display: none; background: transparent; border: 1px solid var(--border-color); color: var(--text-muted); width: 100%; margin-top: 0.5rem;">Cancel Edit</button>
    </form>
</div>

<script>
function toggleNoticeForm() {
    const container = document.getElementById('notice-form-container');
    container.style.display = container.style.display === 'none' ? 'block' : 'none';
}

function editNotice(btn) {
    document.getElementById('notice-form-container').style.display = 'block';
    document.getElementById('form-title').innerText = 'Edit Notice';
    document.getElementById('record_id').value = btn.getAttribute('data-id');
    document.getElementById('title').value = btn.getAttribute('data-title');
    document.getElementById('description').value = btn.getAttribute('data-description');
    document.getElementById('event_date').value = btn.getAttribute('data-date');
    document.getElementById('location').value = btn.getAttribute('data-location');
    document.getElementById('submit-btn').innerText = 'Update Notice';
    document.getElementById('cancel-btn').style.display = 'block';
    window.scrollTo({ top: document.getElementById('notice-form-container').offsetTop - 100, behavior: 'smooth' });
}

function cancelEdit() {
    document.getElementById('form-title').innerText = 'Add New Notice';
    document.getElementById('record_id').value = '';
    document.getElementById('title').value = '';
    document.getElementById('description').value = '';
    document.getElementById('event_date').value = '<?php echo date('Y-m-d'); ?>';
    document.getElementById('location').value = '';
    document.getElementById('submit-btn').innerText = 'Save Notice';
    document.getElementById('cancel-btn').style.display = 'none';
    document.getElementById('notice-form-container').style.display = 'none';
}
</script>
<?php endif; ?>

<div class="grid-cards" style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));">
    <?php foreach ($events as $event): ?>
        <div class="card event-card">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                <span class="material-symbols-outlined" style="font-size: 2.5rem; color: var(--primary-green);">event_available</span>
                <span class="event-date" style="font-size: 0.85rem; background: var(--light-green); color: var(--primary-green); padding: 4px 10px; border-radius: 20px; font-weight: 600;">
                    <?php echo date('d M Y', strtotime($event['event_date'])); ?>
                </span>
            </div>
            <h3 style="margin-bottom: 0.5rem; color: var(--white);"><?php echo htmlspecialchars($event['title']); ?></h3>
            
            <div style="margin-bottom: 1.5rem; padding: 1rem; background: rgba(16, 185, 129, 0.05); border-left: 3px solid var(--primary-green); border-radius: 6px;">
                <div style="display: flex; align-items: center; gap: 8px; color: var(--primary-green); font-size: 0.9rem; font-weight: 600; margin-bottom: 0.5rem;">
                    <span class="material-symbols-outlined" style="font-size: 1.1rem;">schedule</span>
                    Timing: 10:00 AM - 01:00 PM
                </div>
                <div style="display: flex; align-items: center; gap: 8px; color: var(--primary-green); font-size: 0.9rem; font-weight: 600; margin-bottom: 0.5rem;">
                    <span class="material-symbols-outlined" style="font-size: 1.1rem;">location_on</span>
                    Location: <?php echo htmlspecialchars($event['location']); ?>
                </div>
                <p style="color: var(--text-main); font-size: 0.9rem; margin-top: 0.8rem; margin-bottom: 0; line-height: 1.5;">
                    <strong>Work Details:</strong> <?php echo htmlspecialchars($event['description']); ?>
                </p>
            </div>

            <div style="display: flex; align-items: center; gap: 8px; color: var(--text-muted); font-size: 0.85rem; margin-bottom: 0.5rem;">
                <span class="material-symbols-outlined" style="font-size: 1.1rem; color: #f59e0b;">volunteer_activism</span>
                Participation is completely voluntary based on willingness.
            </div>
            
            <div style="display: flex; align-items: center; gap: 8px; color: var(--text-muted); font-size: 0.85rem; margin-bottom: 1.5rem;">
                <span class="material-symbols-outlined" style="font-size: 1.1rem; color: #f59e0b;">redeem</span>
                Rewards will be provided for all participants!
            </div>

            <?php if (isAdmin()): ?>
            <div style="margin-top: 1rem; display: flex; gap: 0.5rem; border-top: 1px solid var(--border-color); padding-top: 1rem;">
                <button 
                    onclick="editNotice(this)" 
                    data-id="<?php echo $event['id']; ?>" 
                    data-title="<?php echo htmlspecialchars($event['title'], ENT_QUOTES, 'UTF-8'); ?>"
                    data-description="<?php echo htmlspecialchars($event['description'], ENT_QUOTES, 'UTF-8'); ?>"
                    data-date="<?php echo $event['event_date']; ?>"
                    data-location="<?php echo htmlspecialchars($event['location'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                    class="btn-primary" 
                    style="padding: 6px 15px; width: auto; font-size: 0.85rem;">Edit</button>
                <a href="?delete=<?php echo $event['id']; ?>" class="btn-danger" onclick="return confirm('Are you sure you want to delete this notice?')" style="padding: 6px 15px; font-size: 0.85rem; text-decoration: none; display: inline-block;">Delete</a>
            </div>
            <?php endif; ?>

        </div>
    <?php endforeach; ?>
</div>

<style>
.event-card {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}
</style>

<?php require_once 'includes/footer.php'; ?>
