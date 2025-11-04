<?php
// issues/index.php
require_once '../includes/config.php';
require_once '../includes/auth.php';
redirectIfNotLoggedIn();

$lab_id = getLabId();
$role = $_SESSION['role'];

// Build query based on user role
if ($role === 'admin') {
    $query = "SELECT ci.*, c.name as component_name, c.component_id as comp_id, 
                     s.name as student_name, s.student_id as stu_id,
                     l_from.lab_name as from_lab, l_to.lab_name as to_lab,
                     u.name as issued_by_name
              FROM component_issues ci
              JOIN components c ON ci.component_id = c.id
              LEFT JOIN students s ON ci.student_id = s.id
              LEFT JOIN labs l_from ON ci.issued_from_lab = l_from.id
              LEFT JOIN labs l_to ON ci.to_lab_id = l_to.id
              JOIN users u ON ci.issued_by = u.id
              ORDER BY ci.issue_date DESC";
} else {
    $query = "SELECT ci.*, c.name as component_name, c.component_id as comp_id, 
                     s.name as student_name, s.student_id as stu_id,
                     l_from.lab_name as from_lab, l_to.lab_name as to_lab,
                     u.name as issued_by_name
              FROM component_issues ci
              JOIN components c ON ci.component_id = c.id
              LEFT JOIN students s ON ci.student_id = s.id
              LEFT JOIN labs l_from ON ci.issued_from_lab = l_from.id
              LEFT JOIN labs l_to ON ci.to_lab_id = l_to.id
              JOIN users u ON ci.issued_by = u.id
              WHERE ci.issued_from_lab = $lab_id
              ORDER BY ci.issue_date DESC";
}

$issues = mysqli_query($conn, $query);

// Count statistics
if ($role === 'admin') {
    $total_issued = mysqli_query($conn, "SELECT COUNT(*) as count FROM component_issues WHERE status = 'issued'")->fetch_assoc()['count'];
    $overdue = mysqli_query($conn, "SELECT COUNT(*) as count FROM component_issues WHERE status = 'issued' AND expected_return_date < CURDATE()")->fetch_assoc()['count'];
    $total_returned = mysqli_query($conn, "SELECT COUNT(*) as count FROM component_issues WHERE status = 'returned'")->fetch_assoc()['count'];
} else {
    $total_issued = mysqli_query($conn, "SELECT COUNT(*) as count FROM component_issues WHERE issued_from_lab = $lab_id AND status = 'issued'")->fetch_assoc()['count'];
    $overdue = mysqli_query($conn, "SELECT COUNT(*) as count FROM component_issues WHERE issued_from_lab = $lab_id AND status = 'issued' AND expected_return_date < CURDATE()")->fetch_assoc()['count'];
    $total_returned = mysqli_query($conn, "SELECT COUNT(*) as count FROM component_issues WHERE issued_from_lab = $lab_id AND status = 'returned'")->fetch_assoc()['count'];
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Issues & Returns - Component Tracker</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <div class="header-actions">
            <h1>Issue & Return Management</h1>
            <a href="add.php" class="btn btn-primary">Issue Component</a>
        </div>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Currently Issued</h3>
                <p><?php echo $total_issued; ?></p>
            </div>
            <div class="stat-card warning">
                <h3>Overdue</h3>
                <p><?php echo $overdue; ?></p>
            </div>
            <div class="stat-card success">
                <h3>Returned</h3>
                <p><?php echo $total_returned; ?></p>
            </div>
        </div>

        <!-- Filters -->
        <div class="filters">
            <form method="GET" class="filter-form">
                <select name="status" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="issued" <?php echo isset($_GET['status']) && $_GET['status'] == 'issued' ? 'selected' : ''; ?>>Issued</option>
                    <option value="returned" <?php echo isset($_GET['status']) && $_GET['status'] == 'returned' ? 'selected' : ''; ?>>Returned</option>
                    <option value="overdue" <?php echo isset($_GET['status']) && $_GET['status'] == 'overdue' ? 'selected' : ''; ?>>Overdue</option>
                </select>
            </form>
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th>Issue ID</th>
                    <th>Component</th>
                    <th>Issued To</th>
                    <th>Quantity</th>
                    <th>Issue Date</th>
                    <th>Expected Return</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($issue = mysqli_fetch_assoc($issues)): 
                    $is_overdue = $issue['status'] == 'issued' && strtotime($issue['expected_return_date']) < time();
                ?>
                <tr class="<?php echo $is_overdue ? 'overdue-row' : ''; ?>">
                    <td><?php echo $issue['issue_id']; ?></td>
                    <td>
                        <?php echo $issue['component_name']; ?><br>
                        <small>(<?php echo $issue['comp_id']; ?>)</small>
                    </td>
                    <td>
                        <?php if($issue['student_id']): ?>
                            Student: <?php echo $issue['student_name']; ?><br>
                            <small>(<?php echo $issue['stu_id']; ?>)</small>
                        <?php else: ?>
                            Lab: <?php echo $issue['to_lab']; ?>
                        <?php endif; ?>
                    </td>
                    <td><?php echo $issue['quantity_issued']; ?></td>
                    <td><?php echo date('M j, Y', strtotime($issue['issue_date'])); ?></td>
                    <td class="<?php echo $is_overdue ? 'text-danger' : ''; ?>">
                        <?php echo date('M j, Y', strtotime($issue['expected_return_date'])); ?>
                    </td>
                    <td>
                        <span class="status-<?php echo $issue['status']; ?>">
                            <?php echo ucfirst($issue['status']); ?>
                            <?php if($is_overdue): ?>
                                <br><small>(Overdue)</small>
                            <?php endif; ?>
                        </span>
                    </td>
                    <td class="actions">
                        <a href="view.php?id=<?php echo $issue['id']; ?>" class="btn btn-info btn-sm">View</a>
                        <?php if($issue['status'] == 'issued' && (isAdmin() || $issue['issued_from_lab'] == $lab_id)): ?>
                            <a href="return.php?id=<?php echo $issue['id']; ?>" class="btn btn-success btn-sm">Return</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>