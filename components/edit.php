<?php
// components/edit.php
require_once '../includes/config.php';
require_once '../includes/auth.php';
redirectIfNotLoggedIn();

$id = $_GET['id'];
$component = mysqli_query($conn, "SELECT * FROM components WHERE id = $id")->fetch_assoc();

if (!$component) {
    die("Component not found");
}

// Check if user has permission to edit this component
if (isLabIncharge() && $component['lab_id'] != getLabId()) {
    die("Access denied");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $component_id = mysqli_real_escape_string($conn, $_POST['component_id']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $quantity = mysqli_real_escape_string($conn, $_POST['quantity']);
    $minimum_stock = mysqli_real_escape_string($conn, $_POST['minimum_stock']);
    $specification = mysqli_real_escape_string($conn, $_POST['specification']);
    $manufacturer = mysqli_real_escape_string($conn, $_POST['manufacturer']);
    $purchase_date = mysqli_real_escape_string($conn, $_POST['purchase_date']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $last_maintenance = mysqli_real_escape_string($conn, $_POST['last_maintenance']);
    $next_maintenance = mysqli_real_escape_string($conn, $_POST['next_maintenance']);
    $notes = mysqli_real_escape_string($conn, $_POST['notes']);
    
    $sql = "UPDATE components SET 
            name = '$name', 
            component_id = '$component_id', 
            category = '$category', 
            quantity = '$quantity', 
            minimum_stock = '$minimum_stock', 
            specification = '$specification', 
            manufacturer = '$manufacturer', 
            purchase_date = '$purchase_date', 
            price = '$price', 
            status = '$status', 
            last_maintenance = '$last_maintenance', 
            next_maintenance = '$next_maintenance', 
            notes = '$notes' 
            WHERE id = $id";
    
    if (mysqli_query($conn, $sql)) {
        $_SESSION['success'] = "Component updated successfully!";
        header("Location: index.php");
        exit();
    } else {
        $error = "Error: " . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Component - Component Tracker</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <div class="header-actions">
            <h1>Edit Component: <?php echo $component['name']; ?></h1>
            <a href="index.php" class="btn btn-secondary">Back to Components</a>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="" class="form">
            <div class="form-row">
                <div class="form-group">
                    <label>Component Name *</label>
                    <input type="text" name="name" value="<?php echo $component['name']; ?>" required class="form-control">
                </div>

                <div class="form-group">
                    <label>Component ID *</label>
                    <input type="text" name="component_id" value="<?php echo $component['component_id']; ?>" required class="form-control">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Category *</label>
                    <select name="category" required class="form-control">
                        <option value="Electronics" <?php echo $component['category'] == 'Electronics' ? 'selected' : ''; ?>>Electronics</option>
                        <option value="Mechanical" <?php echo $component['category'] == 'Mechanical' ? 'selected' : ''; ?>>Mechanical</option>
                        <option value="Chemical" <?php echo $component['category'] == 'Chemical' ? 'selected' : ''; ?>>Chemical</option>
                        <option value="Computer" <?php echo $component['category'] == 'Computer' ? 'selected' : ''; ?>>Computer</option>
                        <option value="Other" <?php echo $component['category'] == 'Other' ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="Available" <?php echo $component['status'] == 'Available' ? 'selected' : ''; ?>>Available</option>
                        <option value="In Use" <?php echo $component['status'] == 'In Use' ? 'selected' : ''; ?>>In Use</option>
                        <option value="Under Maintenance" <?php echo $component['status'] == 'Under Maintenance' ? 'selected' : ''; ?>>Under Maintenance</option>
                        <option value="Disposed" <?php echo $component['status'] == 'Disposed' ? 'selected' : ''; ?>>Disposed</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Quantity *</label>
                    <input type="number" name="quantity" value="<?php echo $component['quantity']; ?>" required class="form-control" min="0">
                </div>

                <div class="form-group">
                    <label>Minimum Stock Level</label>
                    <input type="number" name="minimum_stock" value="<?php echo $component['minimum_stock']; ?>" class="form-control" min="0">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Manufacturer</label>
                    <input type="text" name="manufacturer" value="<?php echo $component['manufacturer']; ?>" class="form-control">
                </div>

                <div class="form-group">
                    <label>Purchase Date</label>
                    <input type="date" name="purchase_date" value="<?php echo $component['purchase_date']; ?>" class="form-control">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Last Maintenance</label>
                    <input type="date" name="last_maintenance" value="<?php echo $component['last_maintenance']; ?>" class="form-control">
                </div>

                <div class="form-group">
                    <label>Next Maintenance</label>
                    <input type="date" name="next_maintenance" value="<?php echo $component['next_maintenance']; ?>" class="form-control">
                </div>
            </div>

            <div class="form-group">
                <label>Price (₹)</label>
                <input type="number" name="price" value="<?php echo $component['price']; ?>" class="form-control" step="0.01" min="0">
            </div>

            <div class="form-group">
                <label>Specification</label>
                <textarea name="specification" class="form-control" rows="2"><?php echo $component['specification']; ?></textarea>
            </div>

            <div class="form-group">
                <label>Notes</label>
                <textarea name="notes" class="form-control" rows="3"><?php echo $component['notes']; ?></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Update Component</button>
                <a href="index.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>