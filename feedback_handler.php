<?php
require_once 'config.php';
require_once 'includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isLoggedIn()) {
    $user_id = $_SESSION['user_id'];
    $message = trim($_POST['message'] ?? '');
    $feedback_id = isset($_POST['feedback_id']) ? intval($_POST['feedback_id']) : 0;
    $action = $_POST['action'] ?? 'create';

    if ($action === 'delete') {
        $id = intval($_POST['id']);
        // Verify ownership or admin
        $stmt = $pdo->prepare("SELECT user_id FROM feedback WHERE id = ?");
        $stmt->execute([$id]);
        $fb = $stmt->fetch();
        if ($fb && ($fb['user_id'] == $user_id || isAdmin())) {
            $pdo->prepare("DELETE FROM feedback WHERE id = ?")->execute([$id]);
            header("Location: dashboard.php?msg=Feedback deleted.");
        }
        exit;
    }

    if (!empty($message)) {
        if ($feedback_id > 0) {
            // Update
            $stmt = $pdo->prepare("SELECT user_id FROM feedback WHERE id = ?");
            $stmt->execute([$feedback_id]);
            $fb = $stmt->fetch();
            if ($fb && ($fb['user_id'] == $user_id || isAdmin())) {
                $pdo->prepare("UPDATE feedback SET message = ? WHERE id = ?")->execute([$message, $feedback_id]);
                header("Location: dashboard.php?msg=Feedback updated.");
            }
        } else {
            // Create
            $stmt = $pdo->prepare("INSERT INTO feedback (user_id, message) VALUES (?, ?)");
            $stmt->execute([$user_id, $message]);
            header("Location: dashboard.php?msg=Feedback received! Thank you.");
        }
        exit;
    }
}
header("Location: dashboard.php");
exit;
?>
