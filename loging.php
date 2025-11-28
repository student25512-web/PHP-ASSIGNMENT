<?php
session_start();

if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in']) {
    header('Location: admin.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

  
    if ($username === 'admin' && $password === 'password123') {
        $_SESSION['user_logged_in'] = true;
        header('Location: admin.php');
        exit;
    } else {
        $error = 'Invalid username or password!';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - The Reading Nook</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/style-login.css">
  
</head>
<body>

<div class="login-card">
    <div class="login-header">
        <h1>The Reading Nook</h1>
        <p>Staff Login Area (Admin Only)</p>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger text-center">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-4">
            <input type="text" name="username" class="form-control" placeholder="Username" required autofocus>
        </div>
        <div class="mb-4">
            <input type="password" name="password" class="form-control" placeholder="Password" required>
        </div>
        <button type="submit" class="btn-login">
            Login to Dashboard
        </button>
    </form>
    <br/>
    <div class="back-home d-flex justify-content-center gap-2">
        <i class="bi bi-house-door-fill text-white" ></i> <a href="index.php">Back to Home page</a>
    </div>

    <div class="demo-cred">
        <strong>Demo Login:</strong><br>
        Username: <code>admin</code> • Password: <code>password123</code>
    </div>
</div>

</body>
</html>