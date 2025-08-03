<?php
session_start();
require '../includes/db.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    die("Access denied.");
}

$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$action = $_GET['action'] ?? '';

if ($user_id > 0 && in_array($action, ['block', 'unblock'])) {
    $new_status = ($action === 'block') ? 1 : 0;
    $stmt = $conn->prepare("UPDATE users SET is_blocked = ? WHERE id = ?");
    $stmt->bind_param("ii", $new_status, $user_id);
    $stmt->execute();
}

// Send back to the page they came from
$redirect = $_SERVER['HTTP_REFERER'] ?? 'manage-users.php';
header("Location: $redirect");
exit;
