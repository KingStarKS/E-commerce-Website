<?php
session_start();
include 'includes/db.php';

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, $_POST["email"]);
    $password_input = $_POST["password"];

    $query = "SELECT * FROM users WHERE email = '$email' LIMIT 1";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);

        if (password_verify($password_input, $user['password'])) {
            if ($user['is_blocked'] == 1) {
                $message = "Your account has been blocked by the admin.";
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user'] = $user['name'];
                $_SESSION['is_admin'] = ($user['is_admin'] == 1);

                if ($_SESSION['is_admin']) {
                    header('Location: admin/index.php');
                } else {
                    header('Location: index.php');
                }
                exit;
            }
        } else {
            $message = "Invalid password!";
        }
    } else {
        $message = "User not found!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login - Shop</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
    body { background-color: #fff; color: #111; display: flex; flex-direction: column; align-items: center; padding: 40px; }
    .top-bar { width: 100%; max-width: 960px; display: flex; justify-content: flex-end; margin-bottom: 40px; }
    .top-bar a { font-size: 14px; text-decoration: none; color: #111; font-weight: 600; }
    .logo-icon { font-size: 36px; margin-bottom: 12px; }
    .title { font-size: 22px; font-weight: 600; margin-bottom: 30px; }
    .login-container { display: flex; border: 1px solid #ddd; border-radius: 8px; overflow: hidden; width: 100%; max-width: 960px; }
    .login-left, .login-right { flex: 1; padding: 40px 30px; }
    .login-left { display: flex; flex-direction: column; justify-content: center; }

    /* Improved form styles */
    .login-left form {
      display: flex;
      flex-direction: column;
      gap: 18px; /* spacing between form elements */
      width: 100%;
    }

    .login-left label {
      font-size: 12px;
      text-transform: uppercase;
      color: #555;
      font-weight: 600;
    }

    .login-left input {
      width: 100%;
      padding: 14px 12px;
      font-size: 15px;
      border: 1px solid #ccc;
      border-radius: 5px;
      outline: none;
      transition: border-color 0.3s ease;
    }

    .login-left input:focus {
      border-color: #111;
    }

    .login-left button {
      width: 100%;
      padding: 14px;
      font-size: 16px;
      border-radius: 5px;
      background-color: #111;
      color: #fff;
      font-weight: 700;
      cursor: pointer;
      border: none;
      transition: background-color 0.3s ease;
    }

    .login-left button:hover {
      background-color: #333;
    }

    .login-divider { width: 1px; background-color: #ddd; position: relative; }
    .login-divider span { position: absolute; top: 50%; transform: translateY(-50%); left: -13px; background: white; padding: 4px 8px; font-size: 12px; color: #999; font-weight: 600; }
    .login-right { display: flex; flex-direction: column; justify-content: center; gap: 16px; }
    .login-right button { display: flex; align-items: center; justify-content: center; border: 1px solid #aaa; padding: 12px; font-size: 14px; font-weight: 600; cursor: pointer; background-color: #fff; }
    .login-right button img { width: 18px; margin-right: 12px; }
    .bottom-link { text-align: center; margin-top: 30px; font-size: 13px; color: #555; }
    .bottom-link a { color: #111; text-decoration: underline; font-weight: 600; }
    .error-message { color: #b00020; text-align: center; font-size: 14px; margin-top: 20px; font-weight: 500; }
    @media (max-width: 768px) {
      .login-container { flex-direction: column; border: none; }
      .login-divider { display: none; }
      .top-bar { justify-content: center; }
    }
  </style>
</head>
<body>
  <div class="top-bar">
    <a href="register.php">CREATE ACCOUNT</a>
  </div>

  <div class="logo-icon">🛍️</div>
  <div class="title">Log into Shop</div>

  <div class="login-container">
    <div class="login-left">
      <form method="POST">
        <label for="email">Email Address</label>
        <input type="email" name="email" id="email" placeholder="name@example.com" required />

        <label for="password">Password</label>
        <input type="password" name="password" id="password" placeholder="Password" required />

        <button type="submit">LOG IN</button>
      </form>
    </div>

    <div class="login-divider"><span>OR</span></div>

    <div class="login-right">
      <button><img src="https://img.icons8.com/color/24/google-logo.png"/> Continue with Google</button>
      <button><img src="https://img.icons8.com/ios-filled/24/mac-os.png"/> Continue with Apple</button>
      <button><img src="https://img.icons8.com/fluency/24/facebook-new.png"/> Continue with Facebook</button>
    </div>
  </div>

  <div class="bottom-link">
    <p><a href="forgot-password.php">CAN’T LOG IN?</a></p>
  </div>

  <?php if ($message) echo "<p class='error-message'>$message</p>"; ?>
</body>
</html>
