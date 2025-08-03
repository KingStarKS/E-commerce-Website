<?php
session_start();
include 'includes/db.php';

// For demo, assume logged in user id is stored in session
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    
    // Password update is optional
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];
    
    if ($password !== $password_confirm) {
        $message = "Passwords do not match.";
    } else {
        // Check if email is taken by another user
        $check_email = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $check_email->bind_param("si", $email, $user_id);
        $check_email->execute();
        $check_email->store_result();
        if ($check_email->num_rows > 0) {
            $message = "Email already in use by another account.";
        } else {
            if (!empty($password)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, password = ? WHERE id = ?");
                $stmt->bind_param("sssi", $name, $email, $hashed_password, $user_id);
            } else {
                $stmt = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
                $stmt->bind_param("ssi", $name, $email, $user_id);
            }
            if ($stmt->execute()) {
                $message = "Profile updated successfully.";
                $_SESSION['user'] = $name; // update session username if used
            } else {
                $message = "Error updating profile.";
            }
        }
        $check_email->close();
    }
}

// Fetch current user info
$stmt = $conn->prepare("SELECT username, email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($name, $email);
$stmt->fetch();
$stmt->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>User Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="container mt-5" style="max-width: 600px;">
    <h2>Your Profile</h2>

    <?php if ($message): ?>
        <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="POST" action="profile.php">
        <div class="mb-3">
            <label for="name" class="form-label">Full Name</label>
            <input type="text" id="name" name="name" value="<?= htmlspecialchars($name) ?>" required class="form-control" />
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email Address</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required class="form-control" />
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">New Password (leave blank to keep current)</label>
            <input type="password" id="password" name="password" class="form-control" />
        </div>

        <div class="mb-3">
            <label for="password_confirm" class="form-label">Confirm New Password</label>
            <input type="password" id="password_confirm" name="password_confirm" class="form-control" />
        </div>

        <button type="submit" class="btn btn-primary">Update Profile</button>
    </form>
</div>

<?php include 'includes/footer.php'; ?>

</body>
</html>
