<?php
session_start();
// include __DIR__ . '/../../includes/db.php';
include '../includes/db.php';
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: ../login.php');
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "Invalid product ID";
    exit;
}

$id = intval($_GET['id']);

$stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    $stmt->close();
    header('Location: index.php?msg=deleted');
    exit;
} else {
    echo "Error deleting product";
}
?>
