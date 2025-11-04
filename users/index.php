<?php
// users/index.php
require_once '../includes/config.php';
require_once '../includes/auth.php';
redirectIfNotLoggedIn();
redirectIfNotAdmin();

$users = mysqli_query($conn, "
    SELECT u.*, l.lab_name 
    FROM users u 
    LEFT JOIN labs l ON u.lab_id = l.id 
    ORDER BY u.role, u.name
");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Users - Component Tracker</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <div class="header-actions">
            <h1>Manage Users</h1>
            <a href="add.php" class="btn btn-primary">Add New User</a>
        </div>

        <?php if (mysqli_num_rows($users) > 0): ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Assigned Lab</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($user = mysqli_fetch_assoc($users)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['name']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td>
                        <span class="role-<?php echo $user['role']; ?>">
                            <?php echo ucfirst(str_replace('_', ' ', $user['role'])); ?>
                        </span>
                    </td>
                    <td><?php echo htmlspecialchars($user['lab_name'] ?? 'Not Assigned'); ?></td>
                    <td><?php echo htmlspecialchars($user['phone'] ?: 'N/A'); ?></td>
                    <td>
                        <span class="status-<?php echo $user['is_active'] ? 'active' : 'inactive'; ?>">
                            <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                        </span>
                    </td>
                    <td class="actions">
                        <a href="edit.php?id=<?php echo $user['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
            <div class="alert alert-info">
                No users found. <a href="add.php">Add your first user</a>.
            </div>
        <?php endif; ?>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>