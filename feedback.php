<?php
require_once 'config.php';
require_once 'includes/header.php';

$msg = '';
$msgType = '';

// Handle Feedback Submission / Edit / Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $action = $_POST['action'] ?? 'create';

    if ($action === 'delete') {
        $id = intval($_POST['id']);
        // Verify ownership strict: only owner can delete
        $stmt = $pdo->prepare("SELECT user_id FROM feedback WHERE id = ?");
        $stmt->execute([$id]);
        $fb = $stmt->fetch();
        if ($fb && $fb['user_id'] == $user_id) {
            $pdo->prepare("DELETE FROM feedback WHERE id = ?")->execute([$id]);
            $msg = "Feedback deleted successfully.";
            $msgType = "success";
        } else {
            $msg = "Unauthorized action.";
            $msgType = "error";
        }
    } else {
        $message = trim($_POST['message'] ?? '');
        $feedback_id = isset($_POST['feedback_id']) ? intval($_POST['feedback_id']) : 0;

        if (!empty($message)) {
            if ($feedback_id > 0) {
                // Update
                $stmt = $pdo->prepare("SELECT user_id FROM feedback WHERE id = ?");
                $stmt->execute([$feedback_id]);
                $fb = $stmt->fetch();
                if ($fb && $fb['user_id'] == $user_id) {
                    $pdo->prepare("UPDATE feedback SET message = ? WHERE id = ?")->execute([$message, $feedback_id]);
                    $msg = "Feedback updated successfully.";
                    $msgType = "success";
                } else {
                    $msg = "Unauthorized action.";
                    $msgType = "error";
                }
            } else {
                // Create
                $stmt = $pdo->prepare("INSERT INTO feedback (user_id, message) VALUES (?, ?)");
                if ($stmt->execute([$user_id, $message])) {
                    $msg = "Feedback posted successfully!";
                    $msgType = "success";
                } else {
                    $msg = "Failed to post feedback.";
                    $msgType = "error";
                }
            }
        } else {
            $msg = "Please enter a message.";
            $msgType = "error";
        }
    }
}

// Fetch ALL feedbacks for EVERYONE
$stmt = $pdo->query("SELECT f.*, u.name as user_name FROM feedback f JOIN users u ON f.user_id = u.id ORDER BY f.created_at DESC");
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
            <input type="hidden" name="action" id="feedback-action" value="create">
            <input type="hidden" name="feedback_id" id="feedback-id" value="0">
            <div class="form-group">
                <textarea name="message" id="feedback-msg" class="form-control" rows="5" placeholder="Share your feedback or suggestions here..." required style="resize: none;"></textarea>
            </div>
            <div style="display: flex; gap: 10px;">
                <button type="submit" id="feedback-submit" class="btn-primary">Post Feedback</button>
                <button type="button" id="cancel-edit" onclick="cancelEdit()" class="btn-danger" style="display: none; background: transparent; border: 1px solid var(--border-color); color: var(--text-muted); padding: 0.8rem;">Cancel Edit</button>
            </div>
        </form>
    </div>

    <!-- Feedback List -->
    <div class="table-card" style="margin-top: 0;">
        <div class="table-header">
            <h3>All Feedbacks</h3>
        </div>
        <div class="feedback-list" style="max-height: 600px; overflow-y: auto; padding-right: 10px;">
            <?php if (empty($feedbacks)): ?>
                <p style="text-align: center; color: var(--text-muted); padding: 2rem;">No feedback found yet.</p>
            <?php else: ?>
                <?php foreach ($feedbacks as $fb): ?>
                    <div class="card" style="margin-bottom: 1rem; padding: 1.5rem; border-color: #eee; box-shadow: none; border-radius: 12px; background: #fff; position: relative;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.8rem;">
                            <div>
                                <strong style="font-size: 1rem; color: #000; display: block;"><?php echo htmlspecialchars($fb['user_name']); ?></strong>
                                <span style="font-size: 0.8rem; color: #999;"><?php echo date('d M Y, H:i', strtotime($fb['created_at'])); ?></span>
                            </div>
                            <?php if ($fb['user_id'] == $_SESSION['user_id']): ?>
                            <div style="display: flex; gap: 0.5rem;">
                                <button onclick='editFeedback(<?php echo $fb['id']; ?>, <?php echo json_encode($fb['message']); ?>)' style="background:none; border:none; color: var(--primary-green); cursor:pointer; padding: 2px;"><span class="material-symbols-outlined" style="font-size: 1.2rem;">edit</span></button>
                                <form method="POST" action="" style="display:inline;" onsubmit="return confirm('Delete this feedback?')">
                                    <input type="hidden" name="id" value="<?php echo $fb['id']; ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <button type="submit" style="background:none; border:none; color: #e74c3c; cursor:pointer; padding: 2px;"><span class="material-symbols-outlined" style="font-size: 1.2rem;">delete</span></button>
                                </form>
                            </div>
                            <?php endif; ?>
                        </div>
                        <p style="color: #444; font-size: 0.95rem; line-height: 1.5; margin: 0;"><?php echo nl2br(htmlspecialchars($fb['message'])); ?></p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function editFeedback(id, message) {
    document.getElementById('feedback-id').value = id;
    document.getElementById('feedback-msg').value = message;
    document.getElementById('feedback-action').value = 'update';
    document.getElementById('feedback-submit').innerText = 'Update Feedback';
    document.getElementById('cancel-edit').style.display = 'block';
    document.getElementById('feedback-msg').focus();
    // Scroll to form on mobile
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function cancelEdit() {
    document.getElementById('feedback-id').value = '0';
    document.getElementById('feedback-msg').value = '';
    document.getElementById('feedback-action').value = 'create';
    document.getElementById('feedback-submit').innerText = 'Post Feedback';
    document.getElementById('cancel-edit').style.display = 'none';
}
</script>

<?php require_once 'includes/footer.php'; ?>
