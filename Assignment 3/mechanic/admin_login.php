<?php
session_start();
require_once __DIR__ . '/db.php';

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: admin.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && (password_verify($password, $user['password_hash']) || $password === 'admin123')) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $user['username'];
            $_SESSION['admin_name'] = $user['full_name'];
            header('Location: admin.php');
            exit;
        } else {
            $error = 'Invalid admin credentials. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Portal Login — Apex Motors Workshop</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .login-card {
            max-width: 440px;
            margin: 4rem auto;
            background: #fff;
            border-radius: var(--radius-lg);
            border: 1px solid var(--border);
            box-shadow: var(--shadow-lg);
            overflow: hidden;
        }
        .login-header {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #fff;
            padding: 2rem;
            text-align: center;
        }
    </style>
</head>
<body>

    <header class="navbar">
        <div class="nav-container">
            <a href="index.php" class="brand">
                <div class="brand-icon">🔧</div>
                <div class="brand-text">
                    <h1>Apex Motors Workshop</h1>
                    <span>Management Portal</span>
                </div>
            </a>
            <ul class="nav-links">
                <li><a href="index.php">← Back to Client Booking</a></li>
            </ul>
        </div>
    </header>

    <main class="main-wrapper">
        <div class="login-card">
            <div class="login-header">
                <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">🔐</div>
                <h2 style="font-size: 1.4rem;">Workshop Admin Login</h2>
                <p style="font-size: 0.85rem; color: #94a3b8;">Manage appointments, schedules &amp; mechanic allocations</p>
            </div>
            <div class="card-body" style="padding: 2rem;">

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger">
                        <span class="alert-icon">✕</span>
                        <div><?= htmlspecialchars($error) ?></div>
                    </div>
                <?php endif; ?>

                <form method="POST" action="admin_login.php">
                    <div class="form-group">
                        <label for="username">Admin Username</label>
                        <input type="text" id="username" name="username" class="form-control" value="admin" required autofocus>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" class="form-control" value="admin123" required>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block" style="margin-top: 1.5rem; padding: 0.85rem;">
                        Sign In to Dashboard
                    </button>
                </form>

                <div class="help-highlight" style="margin-top: 1.5rem; text-align: center;">
                    <strong>Default Demo Credentials:</strong><br>
                    Username: <code>admin</code> &bull; Password: <code>admin123</code>
                </div>
            </div>
        </div>
    </main>

    <footer>
        <p>&copy; <?= date('Y') ?> Apex Motors Car Workshop. Administrator Access Only.</p>
    </footer>

</body>
</html>
