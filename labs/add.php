<?php
// labs/add.php
require_once '../includes/config.php';
require_once '../includes/auth.php';
redirectIfNotLoggedIn();
redirectIfNotAdmin();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $lab_name = mysqli_real_escape_string($conn, $_POST['lab_name']);
    $lab_code = mysqli_real_escape_string($conn, $_POST['lab_code']);
    $location = mysqli_real_escape_string($conn, $_POST['location']);
    $capacity = mysqli_real_escape_string($conn, $_POST['capacity']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    
    $sql = "INSERT INTO labs (lab_name, lab_code, location, capacity, description) 
            VALUES ('$lab_name', '$lab_code', '$location', '$capacity', '$description')";
    
    if (mysqli_query($conn, $sql)) {
        $_SESSION['success'] = "Lab added successfully!";
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
    <title>Add Lab - Component Tracker</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <div class="header-actions">
            <h1>Add New Lab</h1>
            <a href="index.php" class="btn btn-secondary">Back to Labs</a>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="" class="form">
            <div class="form-group">
                <label>Lab Name *</label>
                <input type="text" name="lab_name" required class="form-control">
            </div>

            <div class="form-group">
                <label>Lab Code *</label>
                <input type="text" name="lab_code" required class="form-control">
            </div>

            <div class="form-group">
                <label>Location</label>
                <input type="text" name="location" class="form-control">
            </div>

            <div class="form-group">
                <label>Capacity</label>
                <input type="number" name="capacity" class="form-control">
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea name="description" class="form-control" rows="3"></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Add Lab</button>
                <a href="index.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>