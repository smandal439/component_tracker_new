<?php
// components/add.php
require_once '../includes/config.php';
require_once '../includes/auth.php';
redirectIfNotLoggedIn();

$lab_id = getLabId();
$role = $_SESSION['role'];

// For admin, check if lab_id is provided via GET
if ($role === 'admin' && isset($_GET['lab_id']) && !empty($_GET['lab_id'])) {
    $lab_id = (int)$_GET['lab_id'];
}

// Validate lab_id
if (!$lab_id) {
    if ($role === 'admin') {
        $_SESSION['error'] = "Please select a lab first.";
        header("Location: index.php");
        exit();
    } else {
        die("Error: No lab assigned to your account. Please contact administrator.");
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $component_id = mysqli_real_escape_string($conn, $_POST['component_id']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $quantity = (int)$_POST['quantity'];
    $minimum_stock = (int)$_POST['minimum_stock'];
    $specification = mysqli_real_escape_string($conn, $_POST['specification']);
    $manufacturer = mysqli_real_escape_string($conn, $_POST['manufacturer']);
    $purchase_date = mysqli_real_escape_string($conn, $_POST['purchase_date']);
    $price = !empty($_POST['price']) ? floatval($_POST['price']) : NULL;
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $notes = mysqli_real_escape_string($conn, $_POST['notes']);
    
    // Check if component ID already exists
    $check_query = "SELECT id FROM components WHERE component_id = '$component_id'";
    $check_result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($check_result) > 0) {
        $error = "Component ID already exists. Please use a different ID.";
    } else {
        $sql = "INSERT INTO components (name, component_id, lab_id, category, quantity, minimum_stock, specification, manufacturer, purchase_date, price, status, notes, available_quantity) 
                VALUES ('$name', '$component_id', $lab_id, '$category', $quantity, $minimum_stock, '$specification', '$manufacturer', " . 
                ($purchase_date ? "'$purchase_date'" : "NULL") . ", " . 
                ($price ? "$price" : "NULL") . ", '$status', '$notes', $quantity)";
        
        if (mysqli_query($conn, $sql)) {
            $_SESSION['success'] = "Component added successfully!";
            $redirect_url = $role === 'admin' ? "index.php?lab_id=$lab_id" : "index.php";
            header("Location: $redirect_url");
            exit();
        } else {
            $error = "Error: " . mysqli_error($conn);
        }
    }
}

// Get lab name for display
$lab_query = "SELECT lab_name FROM labs WHERE id = $lab_id";
$lab_result = mysqli_query($conn, $lab_query);
$lab_name = "Unknown Lab";
if ($lab_result && mysqli_num_rows($lab_result) > 0) {
    $lab_data = mysqli_fetch_assoc($lab_result);
    $lab_name = $lab_data['lab_name'];
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Component - Component Tracker</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <div class="header-actions">
            <h1>Add New Component - <?php echo htmlspecialchars($lab_name); ?></h1>
            <a href="index.php<?php echo $role === 'admin' ? '?lab_id=' . $lab_id : ''; ?>" class="btn btn-secondary">Back to Components</a>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="" class="form">
            <div class="form-row">
                <div class="form-group">
                    <label>Component Name *</label>
                    <input type="text" name="name" required class="form-control" placeholder="Enter component name">
                </div>

                <div class="form-group">
                    <label>Component ID *</label>
                    <input type="text" name="component_id" required class="form-control" placeholder="Enter unique component ID">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Category *</label>
                    <select name="category" required class="form-control">
                        <option value="">-- Select Category --</option>
                        <option value="Electronics">Electronics</option>
                        <option value="Mechanical">Mechanical</option>
                        <option value="Chemical">Chemical</option>
                        <option value="Computer">Computer</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="Available">Available</option>
                        <option value="In Use">In Use</option>
                        <option value="Under Maintenance">Under Maintenance</option>
                        <option value="Disposed">Disposed</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Quantity *</label>
                    <input type="number" name="quantity" required class="form-control" min="0" value="0">
                </div>

                <div class="form-group">
                    <label>Minimum Stock Level</label>
                    <input type="number" name="minimum_stock" class="form-control" min="0" value="0">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Manufacturer</label>
                    <input type="text" name="manufacturer" class="form-control" placeholder="Enter manufacturer name">
                </div>

                <div class="form-group">
                    <label>Purchase Date</label>
                    <input type="date" name="purchase_date" class="form-control">
                </div>
            </div>

            <div class="form-group">
                <label>Price (₹)</label>
                <input type="number" name="price" class="form-control" step="0.01" min="0" placeholder="0.00">
            </div>

            <div class="form-group">
                <label>Specification</label>
                <textarea name="specification" class="form-control" rows="2" placeholder="Technical specifications..."></textarea>
            </div>

            <div class="form-group">
                <label>Notes</label>
                <textarea name="notes" class="form-control" rows="3" placeholder="Additional notes..."></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Add Component</button>
                <a href="index.php<?php echo $role === 'admin' ? '?lab_id=' . $lab_id : ''; ?>" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>