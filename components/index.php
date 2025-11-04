<?php
// components/index.php
require_once '../includes/config.php';
require_once '../includes/auth.php';
redirectIfNotLoggedIn();

$lab_id = getLabId();
$role = $_SESSION['role'];

// Handle lab selection for admin
if ($role === 'admin' && isset($_GET['lab_id'])) {
    $lab_id = $_GET['lab_id'];
    $components_lab = mysqli_query($conn, "SELECT * FROM labs WHERE id = $lab_id")->fetch_assoc();
}

if (!$lab_id && $role === 'lab_incharge') {
    die("Error: Lab not assigned to you. Please contact admin.");
}

// Build query based on user role
if ($role === 'admin' && $lab_id) {
    $components = mysqli_query($conn, "
        SELECT c.*, l.lab_name 
        FROM components c 
        JOIN labs l ON c.lab_id = l.id 
        WHERE c.lab_id = $lab_id
        ORDER BY c.name
    ");
} else if ($role === 'lab_incharge') {
    $components = mysqli_query($conn, "
        SELECT c.*, l.lab_name 
        FROM components c 
        JOIN labs l ON c.lab_id = l.id 
        WHERE c.lab_id = $lab_id
        ORDER BY c.name
    ");
} else {
    // Admin viewing all components or no lab selected
    $components = mysqli_query($conn, "
        SELECT c.*, l.lab_name 
        FROM components c 
        JOIN labs l ON c.lab_id = l.id 
        ORDER BY l.lab_name, c.name
    ");
}

// Get labs for admin dropdown
if ($role === 'admin') {
    $labs = mysqli_query($conn, "SELECT * FROM labs WHERE is_active = 1");
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Components Management - Component Tracker</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <div class="header-actions">
            <?php if ($role === 'admin' && isset($components_lab)): ?>
                <h1>Components - <?php echo $components_lab['lab_name']; ?></h1>
            <?php elseif ($role === 'lab_incharge'): ?>
                <h1>My Lab Components</h1>
            <?php else: ?>
                <h1>All Components</h1>
            <?php endif; ?>
            
            <?php if (isLabIncharge() || (isAdmin() && $lab_id)): ?>
                <a href="add.php<?php echo isAdmin() && $lab_id ? '?lab_id=' . $lab_id : ''; ?>" class="btn btn-primary">Add Component</a>
            <?php endif; ?>
        </div>

        <!-- Lab Selector for Admin -->
        <?php if ($role === 'admin'): ?>
        <div class="lab-selector">
            <form method="GET" class="filter-form">
                <label>Select Lab:</label>
                <select name="lab_id" onchange="this.form.submit()">
                    <option value="">All Labs</option>
                    <?php while($lab = mysqli_fetch_assoc($labs)): ?>
                        <option value="<?php echo $lab['id']; ?>" 
                            <?php echo isset($_GET['lab_id']) && $_GET['lab_id'] == $lab['id'] ? 'selected' : ''; ?>>
                            <?php echo $lab['lab_name']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </form>
        </div>
        <?php endif; ?>

        <?php if (mysqli_num_rows($components) > 0): ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Component ID</th>
                    <th>Name</th>
                    <?php if ($role === 'admin' && !$lab_id): ?>
                        <th>Lab</th>
                    <?php endif; ?>
                    <th>Category</th>
                    <th>Quantity</th>
                    <th>Available</th>
                    <th>Min Stock</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($component = mysqli_fetch_assoc($components)): 
                    $stock_class = $component['available_quantity'] <= $component['minimum_stock'] ? 'low-stock' : '';
                ?>
                <tr class="<?php echo $stock_class; ?>">
                    <td><?php echo $component['component_id']; ?></td>
                    <td><?php echo $component['name']; ?></td>
                    <?php if ($role === 'admin' && !$lab_id): ?>
                        <td><?php echo $component['lab_name']; ?></td>
                    <?php endif; ?>
                    <td><?php echo $component['category']; ?></td>
                    <td><?php echo $component['quantity']; ?></td>
                    <td><?php echo $component['available_quantity']; ?></td>
                    <td><?php echo $component['minimum_stock']; ?></td>
                    <td>
                        <span class="status-<?php echo strtolower(str_replace(' ', '-', $component['status'])); ?>">
                            <?php echo $component['status']; ?>
                        </span>
                    </td>
                    <td class="actions">
                        <a href="view.php?id=<?php echo $component['id']; ?>" class="btn btn-info btn-sm">View</a>
                        <?php if (isLabIncharge() && $component['lab_id'] == $lab_id): ?>
                            <a href="edit.php?id=<?php echo $component['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                        <?php elseif (isAdmin()): ?>
                            <a href="edit.php?id=<?php echo $component['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
            <div class="alert alert-info">
                <?php if ($role === 'admin' && $lab_id): ?>
                    No components found in this lab.
                <?php elseif ($role === 'lab_incharge'): ?>
                    No components found in your lab. <a href="add.php">Add your first component</a>.
                <?php else: ?>
                    No components found in the system.
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>