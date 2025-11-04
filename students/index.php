<?php
// students/index.php
require_once '../includes/config.php';
require_once '../includes/auth.php';
redirectIfNotLoggedIn();

$students = mysqli_query($conn, "SELECT * FROM students ORDER BY name");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Students - Component Tracker</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <div class="header-actions">
            <h1>Manage Students</h1>
            <a href="add.php" class="btn btn-primary">Add New Student</a>
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th>Student ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Course</th>
                    <th>Semester</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($student = mysqli_fetch_assoc($students)): ?>
                <tr>
                    <td><?php echo $student['student_id']; ?></td>
                    <td><?php echo $student['name']; ?></td>
                    <td><?php echo $student['email']; ?></td>
                    <td><?php echo $student['phone']; ?></td>
                    <td><?php echo $student['course']; ?></td>
                    <td>Sem <?php echo $student['semester']; ?></td>
                    <td>
                        <span class="status-<?php echo $student['is_active'] ? 'active' : 'inactive'; ?>">
                            <?php echo $student['is_active'] ? 'Active' : 'Inactive'; ?>
                        </span>
                    </td>
                    <td class="actions">
                        <a href="edit.php?id=<?php echo $student['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                        <a href="../issues/index.php?student=<?php echo $student['id']; ?>" class="btn btn-info btn-sm">View Issues</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>