<?php
require_once 'config.php';
require_once 'includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isLoggedIn()) {
    $message = trim($_POST['message']);
    $user_id = $_SESSION['user_id'];

    if (!empty($message)) {
        $stmt = $pdo->prepare("INSERT INTO feedback (user_id, message) VALUES (?, ?)");
        $stmt->execute([$user_id, $message]);
        header("Location: dashboard.php?msg=Feedback received! Thank you.");
        exit;
    }
}
header("Location: dashboard.php");
exit;
?>
