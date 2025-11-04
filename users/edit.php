<?php
// users/edit.php
require_once '../includes/config.php';
require_once '../includes/auth.php';
redirectIfNotLoggedIn();
redirectIfNotAdmin();

$id = $_GET['id'];
$user = mysqli_query($conn, "SELECT * FROM users WHERE id = $id")->fetch_assoc();

if (!$user) {
    die("User not found");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    $lab_id = mysqli_real_escape_string($conn, $_POST['lab_id']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    $sql = "UPDATE users SET 
            name = '$name', 
            email = '$email', 
            role = '$role', 
            lab_id = " . ($lab_id ? "'$lab_id'" : "NULL") . ", 
            phone = '$phone', 
            is_active = $is_active 
            WHERE id = $id";
    
    if (mysqli_query($conn, $sql)) {
        $_SESSION['success'] = "User updated successfully!";
        header("Location: index.php");
        exit();
    } else {
        $error = "Error: " . mysqli_error($conn);
    }
}

$labs = mysqli_query($conn, "SELECT * FROM labs WHERE is_active = 1");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit User - Component Tracker</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <div class="header-actions">
            <h1>Edit User: <?php echo $user['name']; ?></h1>
            <a href="index.php" class="btn btn-secondary">Back to Users</a>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="" class="form">
            <div class="form-row">
                <div class="form-group">
                    <label>Name *</label>
                    <input type="text" name="name" value="<?php echo $user['name']; ?>" required class="form-control">
                </div>

                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="email" value="<?php echo $user['email']; ?>" required class="form-control">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Role *</label>
                    <select name="role" required class="form-control">
                        <option value="lab_incharge" <?php echo $user['role'] == 'lab_incharge' ? 'selected' : ''; ?>>Lab Incharge</option>
                        <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Assigned Lab</label>
                    <select name="lab_id" class="form-control">
                        <option value="">-- Select Lab --</option>
                        <?php while($lab = mysqli_fetch_assoc($labs)): ?>
                            <option value="<?php echo $lab['id']; ?>" 
                                <?php echo $user['lab_id'] == $lab['id'] ? 'selected' : ''; ?>>
                                <?php echo $lab['lab_name']; ?> (<?php echo $lab['lab_code']; ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Phone</label>
                    <input type="text" name="phone" value="<?php echo $user['phone']; ?>" class="form-control">
                </div>

                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_active" value="1" <?php echo $user['is_active'] ? 'checked' : ''; ?>>
                        Active User
                    </label>
                </div>
            </div>

            <div class="form-note">
                <p><strong>Note:</strong> To change password, use the reset password feature (to be implemented).</p>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Update User</button>
                <a href="index.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>