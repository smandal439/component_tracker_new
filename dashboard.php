<?php
// dashboard.php
require_once 'includes/config.php';
require_once 'includes/auth.php';
redirectIfNotLoggedIn();

if (!isset($_SESSION["role"])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$lab_id = getLabId();

// Get statistics based on user role
if ($role === 'admin') {
    // Admin statistics
    $total_labs = mysqli_query($conn, "SELECT COUNT(*) as count FROM labs WHERE is_active = 1")->fetch_assoc()['count'];
    $total_components = mysqli_query($conn, "SELECT COUNT(*) as count FROM components")->fetch_assoc()['count'];
    $total_incharges = mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE role='lab_incharge' AND is_active = 1")->fetch_assoc()['count'];
    $active_issues = mysqli_query($conn, "SELECT COUNT(*) as count FROM component_issues WHERE status = 'issued'")->fetch_assoc()['count'];
} else {
    // Lab Incharge statistics
    $total_components = mysqli_query($conn, "SELECT COUNT(*) as count FROM components WHERE lab_id = $lab_id")->fetch_assoc()['count'];
    $low_stock = mysqli_query($conn, "SELECT COUNT(*) as count FROM components WHERE lab_id = $lab_id AND quantity <= minimum_stock AND quantity > 0")->fetch_assoc()['count'];
    $under_maintenance = mysqli_query($conn, "SELECT COUNT(*) as count FROM components WHERE lab_id = $lab_id AND status = 'Under Maintenance'")->fetch_assoc()['count'];
    $active_issues = mysqli_query($conn, "SELECT COUNT(*) as count FROM component_issues WHERE issued_from_lab = $lab_id AND status = 'issued'")->fetch_assoc()['count'];
    
    // Get recent components for lab incharge
    $recent_components = mysqli_query($conn, "
        SELECT * FROM components 
        WHERE lab_id = $lab_id 
        ORDER BY created_at DESC 
        LIMIT 5
    ");
}

// Get recent issues for dashboard
if ($role === 'admin') {
    $recent_issues = mysqli_query($conn, "
        SELECT ci.*, c.name as component_name, s.name as student_name, l_to.lab_name as to_lab
        FROM component_issues ci
        JOIN components c ON ci.component_id = c.id
        LEFT JOIN students s ON ci.student_id = s.id
        LEFT JOIN labs l_to ON ci.to_lab_id = l_to.id
        ORDER BY ci.created_at DESC 
        LIMIT 5
    ");
} else {
    $recent_issues = mysqli_query($conn, "
        SELECT ci.*, c.name as component_name, s.name as student_name, l_to.lab_name as to_lab
        FROM component_issues ci
        JOIN components c ON ci.component_id = c.id
        LEFT JOIN students s ON ci.student_id = s.id
        LEFT JOIN labs l_to ON ci.to_lab_id = l_to.id
        WHERE ci.issued_from_lab = $lab_id
        ORDER BY ci.created_at DESC 
        LIMIT 5
    ");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Component Tracker</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <h1>Dashboard</h1>
        <p>Welcome back, <?php echo $_SESSION['name']; ?>!</p>
        
        <?php if ($role === 'admin'): ?>
            <!-- Admin Dashboard -->
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Labs</h3>
                    <p><?php echo $total_labs; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Total Components</h3>
                    <p><?php echo $total_components; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Lab Incharges</h3>
                    <p><?php echo $total_incharges; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Active Issues</h3>
                    <p><?php echo $active_issues; ?></p>
                </div>
            </div>
        <?php else: ?>
            <!-- Lab Incharge Dashboard -->
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Components</h3>
                    <p><?php echo $total_components; ?></p>
                </div>
                <div class="stat-card warning">
                    <h3>Low Stock</h3>
                    <p><?php echo $low_stock; ?></p>
                </div>
                <div class="stat-card danger">
                    <h3>Under Maintenance</h3>
                    <p><?php echo $under_maintenance; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Active Issues</h3>
                    <p><?php echo $active_issues; ?></p>
                </div>
            </div>

            <!-- Recent Components -->
            <div class="recent-section">
                <h3>Recent Components</h3>
                <?php if (mysqli_num_rows($recent_components) > 0): ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Component ID</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Quantity</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($component = mysqli_fetch_assoc($recent_components)): ?>
                            <tr>
                                <td><?php echo $component['component_id']; ?></td>
                                <td><?php echo $component['name']; ?></td>
                                <td><?php echo $component['category']; ?></td>
                                <td><?php echo $component['quantity']; ?></td>
                                <td>
                                    <span class="status-<?php echo strtolower(str_replace(' ', '-', $component['status'])); ?>">
                                        <?php echo $component['status']; ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No components found in your lab.</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Recent Issues Section -->
        <div class="recent-section">
            <h3>Recent Issues</h3>
            <?php if (mysqli_num_rows($recent_issues) > 0): ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Issue ID</th>
                            <th>Component</th>
                            <th>Issued To</th>
                            <th>Due Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($issue = mysqli_fetch_assoc($recent_issues)): 
                            $is_overdue = $issue['status'] == 'issued' && strtotime($issue['expected_return_date']) < time();
                        ?>
                        <tr class="<?php echo $is_overdue ? 'overdue-row' : ''; ?>">
                            <td><?php echo $issue['issue_id']; ?></td>
                            <td><?php echo $issue['component_name']; ?></td>
                            <td>
                                <?php if($issue['student_id']): ?>
                                    Student: <?php echo $issue['student_name']; ?>
                                <?php else: ?>
                                    Lab: <?php echo $issue['to_lab']; ?>
                                <?php endif; ?>
                            </td>
                            <td class="<?php echo $is_overdue ? 'text-danger' : ''; ?>">
                                <?php echo date('M j, Y', strtotime($issue['expected_return_date'])); ?>
                            </td>
                            <td>
                                <span class="status-<?php echo $issue['status']; ?>">
                                    <?php echo ucfirst($issue['status']); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No recent issues found.</p>
            <?php endif; ?>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>