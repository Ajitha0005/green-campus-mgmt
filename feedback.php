<?php
require_once 'config.php';
require_once 'includes/header.php';

$msg = '';
$msgType = '';

// Handle Feedback Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_feedback'])) {
    $message = trim($_POST['message']);
    $user_id = $_SESSION['user_id'];

    if (!empty($message)) {
        $stmt = $pdo->prepare("INSERT INTO feedback (user_id, message) VALUES (?, ?)");
        if ($stmt->execute([$user_id, $message])) {
            $msg = "Feedback posted successfully!";
            $msgType = "success";
        } else {
            $msg = "Failed to post feedback. Please try again.";
            $msgType = "error";
        }
    } else {
        $msg = "Please enter a message.";
        $msgType = "error";
    }
}

// Fetch feedback based on role
if (isAdmin()) {
    $stmt = $pdo->query("SELECT f.*, u.name as user_name FROM feedback f JOIN users u ON f.user_id = u.id ORDER BY f.created_at DESC");
} else {
    $stmt = $pdo->prepare("SELECT f.*, u.name as user_name FROM feedback f JOIN users u ON f.user_id = u.id WHERE f.user_id = ? ORDER BY f.created_at DESC");
    $stmt->execute([$_SESSION['user_id']]);
}
$feedbacks = $stmt->fetchAll();
?>

<div class="page-title">Feedback & Suggestions</div>

<?php if ($msg): ?>
    <div class="alert alert-<?php echo $msgType; ?>"><?php echo htmlspecialchars($msg); ?></div>
<?php endif; ?>

<div class="grid-cards" style="grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); align-items: flex-start;">
    <!-- Feedback Form -->
    <div class="table-card" style="margin-top: 0;">
        <div class="table-header">
            <h3>Post Your Feedback</h3>
            <p style="color: var(--text-muted); font-size: 0.9rem;">We value your thoughts on campus green initiatives.</p>
        </div>
        <form method="POST" action="" style="margin-top: 1.5rem;">
            <div class="form-group">
                <textarea name="message" class="form-control" rows="5" placeholder="Share your feedback or suggestions here..." required style="resize: none;"></textarea>
            </div>
            <button type="submit" name="submit_feedback" class="btn-primary">Post Feedback</button>
        </form>
    </div>

    <!-- Feedback List -->
    <div class="table-card" style="margin-top: 0;">
        <div class="table-header">
            <h3><?php echo isAdmin() ? 'All Feedbacks' : 'My Feedbacks'; ?></h3>
        </div>
        <div class="feedback-list" style="max-height: 500px; overflow-y: auto; padding-right: 10px;">
            <?php if (empty($feedbacks)): ?>
                <p style="text-align: center; color: var(--text-muted); padding: 2rem;">No feedback found yet.</p>
            <?php else: ?>
                <?php foreach ($feedbacks as $fb): ?>
                    <div class="card" style="margin-bottom: 1rem; padding: 1.5rem; border-color: #eee; box-shadow: none; border-radius: 12px; background: #fff;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.8rem;">
                            <strong style="font-size: 1rem; color: #000;"><?php echo htmlspecialchars($fb['user_name']); ?></strong>
                            <span style="font-size: 0.8rem; color: #999;"><?php echo date('d M Y, H:i', strtotime($fb['created_at'])); ?></span>
                        </div>
                        <p style="color: #444; font-size: 0.95rem; line-height: 1.5;"><?php echo nl2br(htmlspecialchars($fb['message'])); ?></p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
