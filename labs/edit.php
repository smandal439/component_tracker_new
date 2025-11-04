<?php
// labs/edit.php
require_once '../includes/config.php';
require_once '../includes/auth.php';
redirectIfNotLoggedIn();
redirectIfNotAdmin();

$id = $_GET['id'];
$lab = mysqli_query($conn, "SELECT * FROM labs WHERE id = $id")->fetch_assoc();

if (!$lab) {
    die("Lab not found");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $lab_name = mysqli_real_escape_string($conn, $_POST['lab_name']);
    $lab_code = mysqli_real_escape_string($conn, $_POST['lab_code']);
    $location = mysqli_real_escape_string($conn, $_POST['location']);
    $capacity = mysqli_real_escape_string($conn, $_POST['capacity']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $lab_incharge_id = mysqli_real_escape_string($conn, $_POST['lab_incharge_id']);
    
    $sql = "UPDATE labs SET 
            lab_name = '$lab_name', 
            lab_code = '$lab_code', 
            location = '$location', 
            capacity = '$capacity', 
            description = '$description',
            lab_incharge_id = " . ($lab_incharge_id ? "'$lab_incharge_id'" : "NULL") . "
            WHERE id = $id";
    
    if (mysqli_query($conn, $sql)) {
        $_SESSION['success'] = "Lab updated successfully!";
        header("Location: index.php");
        exit();
    } else {
        $error = "Error: " . mysqli_error($conn);
    }
}

// Get available lab incharges
$incharges = mysqli_query($conn, "SELECT * FROM users WHERE role = 'lab_incharge' AND (lab_id IS NULL OR lab_id = $id)");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Lab - Component Tracker</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <div class="header-actions">
            <h1>Edit Lab: <?php echo $lab['lab_name']; ?></h1>
            <a href="index.php" class="btn btn-secondary">Back to Labs</a>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="" class="form">
            <div class="form-group">
                <label>Lab Name *</label>
                <input type="text" name="lab_name" value="<?php echo $lab['lab_name']; ?>" required class="form-control">
            </div>

            <div class="form-group">
                <label>Lab Code *</label>
                <input type="text" name="lab_code" value="<?php echo $lab['lab_code']; ?>" required class="form-control">
            </div>

            <div class="form-group">
                <label>Location</label>
                <input type="text" name="location" value="<?php echo $lab['location']; ?>" class="form-control">
            </div>

            <div class="form-group">
                <label>Capacity</label>
                <input type="number" name="capacity" value="<?php echo $lab['capacity']; ?>" class="form-control">
            </div>

            <div class="form-group">
                <label>Lab Incharge</label>
                <select name="lab_incharge_id" class="form-control">
                    <option value="">-- Select Incharge --</option>
                    <?php while($incharge = mysqli_fetch_assoc($incharges)): ?>
                        <option value="<?php echo $incharge['id']; ?>" 
                            <?php echo ($lab['lab_incharge_id'] == $incharge['id']) ? 'selected' : ''; ?>>
                            <?php echo $incharge['name']; ?> (<?php echo $incharge['email']; ?>)
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea name="description" class="form-control" rows="3"><?php echo $lab['description']; ?></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Update Lab</button>
                <a href="index.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>