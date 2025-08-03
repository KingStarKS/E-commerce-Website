<?php
session_start();
include 'includes/db.php';

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST["username"]);
    $email = mysqli_real_escape_string($conn, $_POST["email"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    if ($password !== $confirm_password) {
        $message = "Passwords do not match.";
    } else {
        // Check if email exists
        $check = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
        if (mysqli_num_rows($check) > 0) {
            $message = "Email already registered.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            mysqli_query($conn, "INSERT INTO users (username, email, password) VALUES ('$username', '$email', '$hashed_password')");
            $_SESSION['user'] = $username;
            header("Location: index.php");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Register</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            max-width: 400px;
            margin: 40px auto;
            background-color: #fff;
            color: #111;
        }
        h2 {
            text-align: center;
            margin-bottom: 24px;
            font-weight: 700;
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 18px;
        }
        input, button {
            width: 100%;
            padding: 14px 12px;
            font-size: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            outline: none;
            transition: border-color 0.3s ease;
            box-sizing: border-box;
        }
        input:focus {
            border-color: #111;
        }
        button {
            background-color: #111;
            color: #fff;
            font-weight: 700;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        button:hover {
            background-color: #333;
        }
        .message {
            color: #b00020;
            text-align: center;
            font-size: 14px;
            margin-top: 20px;
            font-weight: 500;
        }
        p {
            margin-top: 20px;
            font-size: 14px;
            text-align: center;
        }
        a {
            color: #111;
            text-decoration: underline;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <h2>Register</h2>
    <form method="POST">
        <input type="text" name="username" placeholder="Username" required />
        <input type="email" name="email" placeholder="Email" required />
        <input type="password" name="password" placeholder="Password" required />
        <input type="password" name="confirm_password" placeholder="Confirm Password" required />
        <button type="submit">Register</button>
    </form>
    <?php if ($message) echo "<p class='message'>$message</p>"; ?>
    <p>Already have an account? <a href="login.php">Login here</a>.</p>
</body>
</html>
