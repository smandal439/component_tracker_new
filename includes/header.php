<?php
// includes/header.php
// No session_start() here - handled by config.php
require_once 'navigation.php'; // Include navigation helper
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Component Tracker</title>
    <link rel="stylesheet" href="<?php echo url('css/style.css'); ?>">
</head>
<body>
    <header>
        <nav>
            <div class="nav-brand">
                <h2><a href="<?php echo url('dashboard.php'); ?>" style="color: white; text-decoration: none;">Component Tracker</a></h2>
            </div>
            <ul class="nav-links">
                <?php if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])): ?>
                    <li><a href="<?php echo url('dashboard.php'); ?>">Dashboard</a></li>
                    
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <li><a href="<?php echo url('labs/index.php'); ?>">Labs</a></li>
                        <li><a href="<?php echo url('users/index.php'); ?>">Users</a></li>
                        <li><a href="<?php echo url('students/index.php'); ?>">Students</a></li>
                    <?php else: ?>
                        <li><a href="<?php echo url('components/index.php'); ?>">My Components</a></li>
                    <?php endif; ?>
                    
                    <li><a href="<?php echo url('issues/index.php'); ?>">Issues & Returns</a></li>
                    
                    <li class="user-menu">
                        <span>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?> (<?php echo htmlspecialchars($_SESSION['role']); ?>)</span>
                        <a href="<?php echo url('logout.php'); ?>" class="btn btn-danger btn-sm">Logout</a>
                    </li>
                <?php else: ?>
                    <li><a href="<?php echo url('login.php'); ?>">Login</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>
    <main>