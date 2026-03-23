<?php
require_once 'config.php';
require_once 'includes/header.php';

$msg = '';
$msgType = '';

// Handle Registration
if (isset($_POST['register_event'])) {
    $event_id = intval($_POST['event_id']);
    $user_id = $_SESSION['user_id'];

    // Check if already registered
    $check = $pdo->prepare("SELECT id FROM event_registrations WHERE user_id = ? AND event_id = ?");
    $check->execute([$user_id, $event_id]);
    
    if ($check->fetch()) {
        $msg = "You are already registered for this event.";
        $msgType = "error";
    } else {
        $stmt = $pdo->prepare("INSERT INTO event_registrations (user_id, event_id) VALUES (?, ?)");
        if ($stmt->execute([$user_id, $event_id])) {
            $msg = "Successfully registered for the event!";
            $msgType = "success";
        } else {
            $msg = "Registration failed. Please try again.";
            $msgType = "error";
        }
    }
}

// Fetch events
$stmt = $pdo->query("SELECT * FROM events ORDER BY event_date ASC");
$events = $stmt->fetchAll();

// Fetch my registrations
$my_regs = [];
if (isLoggedIn()) {
    $regStmt = $pdo->prepare("SELECT event_id FROM event_registrations WHERE user_id = ?");
    $regStmt->execute([$_SESSION['user_id']]);
    $my_regs = $regStmt->fetchAll(PDO::FETCH_COLUMN);
}
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

<div class="page-title" style="font-size: 1.4rem; color: #666; margin-top: 2rem;">Upcoming Green Events</div>

<?php if ($msg): ?>
    <div class="alert alert-<?php echo $msgType; ?>"><?php echo htmlspecialchars($msg); ?></div>
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
            <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 1.5rem; line-height: 1.6;">
                <?php echo htmlspecialchars($event['description']); ?>
            </p>
            <div style="display: flex; align-items: center; gap: 8px; color: var(--text-muted); font-size: 0.85rem; margin-bottom: 1.5rem;">
                <span class="material-symbols-outlined" style="font-size: 1.1rem;">location_on</span>
                <?php echo htmlspecialchars($event['location']); ?>
            </div>

            <?php if (!empty($event['google_form_url'])): ?>
                <a href="<?php echo htmlspecialchars($event['google_form_url']); ?>" target="_blank" class="btn-primary" style="text-decoration: none;">Register Now</a>
            <?php elseif (in_array($event['id'], $my_regs)): ?>
                <button class="btn-primary" disabled style="background: var(--light-green); color: var(--primary-green); cursor: default;">
                    Already Registered
                </button>
            <?php else: ?>
                <form method="POST" action="">
                    <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                    <button type="submit" name="register_event" class="btn-primary">Register Now</button>
                </form>
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
