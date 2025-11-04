<?php
// users/add.php
require_once '../includes/config.php';
require_once '../includes/auth.php';
redirectIfNotLoggedIn();
redirectIfNotAdmin();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = md5($_POST['password']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    $lab_id = mysqli_real_escape_string($conn, $_POST['lab_id']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    
    $sql = "INSERT INTO users (name, email, password, role, lab_id, phone) 
            VALUES ('$name', '$email', '$password', '$role', " . ($lab_id ? "'$lab_id'" : "NULL") . ", '$phone')";
    
    if (mysqli_query($conn, $sql)) {
        $_SESSION['success'] = "User added successfully!";
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
    <title>Add User - Component Tracker</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <div class="header-actions">
            <h1>Add New User</h1>
            <a href="index.php" class="btn btn-secondary">Back to Users</a>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="" class="form">
            <div class="form-row">
                <div class="form-group">
                    <label>Name *</label>
                    <input type="text" name="name" required class="form-control">
                </div>

                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="email" required class="form-control">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Password *</label>
                    <input type="password" name="password" required class="form-control">
                </div>

                <div class="form-group">
                    <label>Role *</label>
                    <select name="role" required class="form-control">
                        <option value="lab_incharge">Lab Incharge</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Assigned Lab</label>
                    <select name="lab_id" class="form-control">
                        <option value="">-- Select Lab --</option>
                        <?php while($lab = mysqli_fetch_assoc($labs)): ?>
                            <option value="<?php echo $lab['id']; ?>">
                                <?php echo $lab['lab_name']; ?> (<?php echo $lab['lab_code']; ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Phone</label>
                    <input type="text" name="phone" class="form-control">
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Add User</button>
                <a href="index.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>