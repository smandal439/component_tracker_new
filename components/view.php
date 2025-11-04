<?php
// components/view.php
require_once '../includes/config.php';
require_once '../includes/auth.php';
redirectIfNotLoggedIn();

$id = $_GET['id'];
$component = mysqli_query($conn, "
    SELECT c.*, l.lab_name 
    FROM components c 
    JOIN labs l ON c.lab_id = l.id 
    WHERE c.id = $id
")->fetch_assoc();

if (!$component) {
    die("Component not found");
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>View Component - Component Tracker</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <div class="header-actions">
            <h1>Component Details: <?php echo $component['name']; ?></h1>
            <div>
                <?php if (isLabIncharge() && $component['lab_id'] == getLabId()): ?>
                    <a href="edit.php?id=<?php echo $id; ?>" class="btn btn-warning">Edit Component</a>
                <?php endif; ?>
                <a href="index.php<?php echo isAdmin() ? '?lab_id=' . $component['lab_id'] : ''; ?>" class="btn btn-secondary">Back to Components</a>
            </div>
        </div>

        <div class="details-card">
            <div class="details-grid">
                <div class="detail-item">
                    <strong>Component ID:</strong>
                    <span><?php echo $component['component_id']; ?></span>
                </div>
                <div class="detail-item">
                    <strong>Lab:</strong>
                    <span><?php echo $component['lab_name']; ?></span>
                </div>
                <div class="detail-item">
                    <strong>Category:</strong>
                    <span><?php echo $component['category']; ?></span>
                </div>
                <div class="detail-item">
                    <strong>Quantity:</strong>
                    <span class="<?php echo $component['quantity'] <= $component['minimum_stock'] ? 'text-danger' : ''; ?>">
                        <?php echo $component['quantity']; ?>
                    </span>
                </div>
                <div class="detail-item">
                    <strong>Minimum Stock:</strong>
                    <span><?php echo $component['minimum_stock']; ?></span>
                </div>
                <div class="detail-item">
                    <strong>Status:</strong>
                    <span class="status-<?php echo strtolower(str_replace(' ', '-', $component['status'])); ?>">
                        <?php echo $component['status']; ?>
                    </span>
                </div>
            </div>

            <div class="details-grid">
                <div class="detail-item">
                    <strong>Manufacturer:</strong>
                    <span><?php echo $component['manufacturer'] ?: 'N/A'; ?></span>
                </div>
                <div class="detail-item">
                    <strong>Purchase Date:</strong>
                    <span><?php echo $component['purchase_date'] ? date('M j, Y', strtotime($component['purchase_date'])) : 'N/A'; ?></span>
                </div>
                <div class="detail-item">
                    <strong>Price:</strong>
                    <span><?php echo $component['price'] ? '₹' . $component['price'] : 'N/A'; ?></span>
                </div>
                <div class="detail-item">
                    <strong>Last Maintenance:</strong>
                    <span><?php echo $component['last_maintenance'] ? date('M j, Y', strtotime($component['last_maintenance'])) : 'N/A'; ?></span>
                </div>
                <div class="detail-item">
                    <strong>Next Maintenance:</strong>
                    <span class="<?php echo $component['next_maintenance'] && strtotime($component['next_maintenance']) < strtotime('+1 month') ? 'text-warning' : ''; ?>">
                        <?php echo $component['next_maintenance'] ? date('M j, Y', strtotime($component['next_maintenance'])) : 'N/A'; ?>
                    </span>
                </div>
            </div>

            <?php if ($component['specification']): ?>
            <div class="detail-section">
                <strong>Specification:</strong>
                <p><?php echo $component['specification']; ?></p>
            </div>
            <?php endif; ?>

            <?php if ($component['notes']): ?>
            <div class="detail-section">
                <strong>Notes:</strong>
                <p><?php echo $component['notes']; ?></p>
            </div>
            <?php endif; ?>

            <div class="detail-section">
                <strong>Last Updated:</strong>
                <span><?php echo date('M j, Y g:i A', strtotime($component['updated_at'])); ?></span>
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>