<?php
include 'includes/db.php';
$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, $_POST["email"]);
    $new_password = password_hash($_POST["new_password"], PASSWORD_DEFAULT);

    $check = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
    if (mysqli_num_rows($check) > 0) {
        mysqli_query($conn, "UPDATE users SET password='$new_password' WHERE email='$email'");
        $message = "Password reset successfully!";
    } else {
        $message = "Email not found!";
    }
}
?>

<!DOCTYPE html>
<html>
<head><title>Reset Password</title></head>
<body>
<h2>Reset Password</h2>
<form method="POST">
    Email: <input type="email" name="email" required><br><br>
    New Password: <input type="password" name="new_password" required><br><br>
    <button type="submit">Reset Password</button>
</form>
<?php if ($message) echo "<p>$message</p>"; ?>
</body>
</html>
