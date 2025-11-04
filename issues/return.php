<?php
// issues/return.php
require_once '../includes/config.php';
require_once '../includes/auth.php';
redirectIfNotLoggedIn();

$id = $_GET['id'];
$lab_id = getLabId();

// Get issue details
$issue = mysqli_query($conn, "
    SELECT ci.*, c.name as component_name, c.component_id as comp_id, 
           s.name as student_name, l_to.lab_name as to_lab
    FROM component_issues ci
    JOIN components c ON ci.component_id = c.id
    LEFT JOIN students s ON ci.student_id = s.id
    LEFT JOIN labs l_to ON ci.to_lab_id = l_to.id
    WHERE ci.id = $id
")->fetch_assoc();

if (!$issue) {
    die("Issue record not found");
}

// Check permission
if (!isAdmin() && $issue['issued_from_lab'] != $lab_id) {
    die("Access denied");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $actual_return_date = mysqli_real_escape_string($conn, $_POST['actual_return_date']);
    $condition_returned = mysqli_real_escape_string($conn, $_POST['condition_returned']);
    $return_notes = mysqli_real_escape_string($conn, $_POST['return_notes']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    // Start transaction
    mysqli_begin_transaction($conn);
    
    try {
        // Update issue record
        $sql = "UPDATE component_issues SET 
                actual_return_date = '$actual_return_date',
                condition_returned = '$condition_returned',
                notes = CONCAT(notes, '\nReturn Notes: ', '$return_notes'),
                status = '$status',
                updated_at = NOW()
                WHERE id = $id";
        
        if (!mysqli_query($conn, $sql)) {
            throw new Exception(mysqli_error($conn));
        }
        
        // Update component total_issued (reduce by returned quantity)
        $update_sql = "UPDATE components SET total_issued = total_issued - {$issue['quantity_issued']} WHERE id = {$issue['component_id']}";
        if (!mysqli_query($conn, $update_sql)) {
            throw new Exception(mysqli_error($conn));
        }
        
        mysqli_commit($conn);
        $_SESSION['success'] = "Component returned successfully!";
        header("Location: index.php");
        exit();
        
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $error = "Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Return Component - Component Tracker</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <div class="header-actions">
            <h1>Return Component</h1>
            <a href="index.php" class="btn btn-secondary">Back to Issues</a>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Issue Details -->
        <div class="details-card">
            <h3>Issue Details</h3>
            <div class="details-grid">
                <div class="detail-item">
                    <strong>Issue ID:</strong>
                    <span><?php echo $issue['issue_id']; ?></span>
                </div>
                <div class="detail-item">
                    <strong>Component:</strong>
                    <span><?php echo $issue['component_name']; ?> (<?php echo $issue['comp_id']; ?>)</span>
                </div>
                <div class="detail-item">
                    <strong>Issued To:</strong>
                    <span>
                        <?php if($issue['student_id']): ?>
                            Student: <?php echo $issue['student_name']; ?>
                        <?php else: ?>
                            Lab: <?php echo $issue['to_lab']; ?>
                        <?php endif; ?>
                    </span>
                </div>
                <div class="detail-item">
                    <strong>Quantity:</strong>
                    <span><?php echo $issue['quantity_issued']; ?></span>
                </div>
                <div class="detail-item">
                    <strong>Issue Date:</strong>
                    <span><?php echo date('M j, Y', strtotime($issue['issue_date'])); ?></span>
                </div>
                <div class="detail-item">
                    <strong>Expected Return:</strong>
                    <span class="<?php echo strtotime($issue['expected_return_date']) < time() ? 'text-danger' : ''; ?>">
                        <?php echo date('M j, Y', strtotime($issue['expected_return_date'])); ?>
                    </span>
                </div>
            </div>
        </div>

        <form method="POST" action="" class="form">
            <div class="form-row">
                <div class="form-group">
                    <label>Actual Return Date *</label>
                    <input type="date" name="actual_return_date" required class="form-control" value="<?php echo date('Y-m-d'); ?>">
                </div>

                <div class="form-group">
                    <label>Condition when Returned *</label>
                    <select name="condition_returned" required class="form-control">
                        <option value="Excellent">Excellent</option>
                        <option value="Good">Good</option>
                        <option value="Fair">Fair</option>
                        <option value="Poor">Poor</option>
                        <option value="Damaged">Damaged</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Return Status *</label>
                    <select name="status" required class="form-control">
                        <option value="returned">Returned</option>
                        <option value="lost">Lost/Missing</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Original Condition</label>
                    <input type="text" value="<?php echo $issue['condition_issued']; ?>" class="form-control" readonly>
                </div>
            </div>

            <div class="form-group">
                <label>Return Notes</label>
                <textarea name="return_notes" class="form-control" rows="3" placeholder="Any observations about the returned component..."></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-success">Mark as Returned</button>
                <a href="index.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>