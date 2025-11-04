<?php
// labs/index.php
require_once '../includes/config.php';
require_once '../includes/auth.php';
redirectIfNotLoggedIn();
redirectIfNotAdmin(); // Only admin can manage labs

$labs = mysqli_query($conn, "
    SELECT l.*, u.name as incharge_name 
    FROM labs l 
    LEFT JOIN users u ON l.lab_incharge_id = u.id
    WHERE l.is_active = 1
");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Labs - Component Tracker</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <div class="header-actions">
            <h1>Manage Labs</h1>
            <a href="add.php" class="btn btn-primary">Add New Lab</a>
        </div>

        <?php if (mysqli_num_rows($labs) > 0): ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Lab Code</th>
                    <th>Lab Name</th>
                    <th>Location</th>
                    <th>Incharge</th>
                    <th>Capacity</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($lab = mysqli_fetch_assoc($labs)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($lab['lab_code']); ?></td>
                    <td><?php echo htmlspecialchars($lab['lab_name']); ?></td>
                    <td><?php echo htmlspecialchars($lab['location']); ?></td>
                    <td><?php echo htmlspecialchars($lab['incharge_name'] ?? 'Not Assigned'); ?></td>
                    <td><?php echo htmlspecialchars($lab['capacity']); ?></td>
                    <td class="actions">
                        <a href="view.php?id=<?php echo $lab['id']; ?>" class="btn btn-info btn-sm">View</a>
                        <a href="edit.php?id=<?php echo $lab['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
            <div class="alert alert-info">
                No labs found. <a href="add.php">Add your first lab</a>.
            </div>
        <?php endif; ?>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>