<?php
// labs/view.php
require_once '../includes/config.php';
require_once '../includes/auth.php';
redirectIfNotLoggedIn();

$id = $_GET['id'];
$lab = mysqli_query($conn, "
    SELECT l.*, u.name as incharge_name, u.email as incharge_email 
    FROM labs l 
    LEFT JOIN users u ON l.lab_incharge_id = u.id 
    WHERE l.id = $id
")->fetch_assoc();

if (!$lab) {
    die("Lab not found");
}

// Get components count for this lab
$components_count = mysqli_query($conn, "SELECT COUNT(*) as count FROM components WHERE lab_id = $id")->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>View Lab - Component Tracker</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <div class="header-actions">
            <h1>Lab Details: <?php echo $lab['lab_name']; ?></h1>
            <div>
                <?php if (isAdmin()): ?>
                    <a href="edit.php?id=<?php echo $id; ?>" class="btn btn-warning">Edit Lab</a>
                <?php endif; ?>
                <a href="../components/index.php?lab_id=<?php echo $id; ?>" class="btn btn-info">View Components</a>
                <a href="index.php" class="btn btn-secondary">Back to Labs</a>
            </div>
        </div>

        <div class="details-card">
            <div class="details-grid">
                <div class="detail-item">
                    <strong>Lab Code:</strong>
                    <span><?php echo $lab['lab_code']; ?></span>
                </div>
                <div class="detail-item">
                    <strong>Location:</strong>
                    <span><?php echo $lab['location']; ?></span>
                </div>
                <div class="detail-item">
                    <strong>Capacity:</strong>
                    <span><?php echo $lab['capacity']; ?> students</span>
                </div>
                <div class="detail-item">
                    <strong>Lab Incharge:</strong>
                    <span><?php echo $lab['incharge_name'] ? $lab['incharge_name'] . ' (' . $lab['incharge_email'] . ')' : 'Not Assigned'; ?></span>
                </div>
                <div class="detail-item">
                    <strong>Total Components:</strong>
                    <span><?php echo $components_count; ?></span>
                </div>
                <div class="detail-item">
                    <strong>Status:</strong>
                    <span class="status-<?php echo $lab['is_active'] ? 'active' : 'inactive'; ?>">
                        <?php echo $lab['is_active'] ? 'Active' : 'Inactive'; ?>
                    </span>
                </div>
            </div>

            <?php if ($lab['description']): ?>
            <div class="detail-section">
                <strong>Description:</strong>
                <p><?php echo $lab['description']; ?></p>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>