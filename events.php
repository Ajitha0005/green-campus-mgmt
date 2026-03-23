<?php
require_once 'config.php';
require_once 'includes/header.php';

$msg = '';
$msgType = '';

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

            <!-- Registration Button -->
            <a href="<?php echo htmlspecialchars(!empty($event['google_form_url']) ? $event['google_form_url'] : "javascript:alert('Please configure your specific Google Form link for this event in the Admin Dashboard.');"); ?>" target="_blank" class="btn-primary" style="text-decoration: none; text-align: center; display: inline-block; box-sizing: border-box; padding: 10px 20px;">
                Register Now
            </a>
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
