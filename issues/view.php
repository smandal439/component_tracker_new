<?php
// issues/view.php
require_once '../includes/config.php';
require_once '../includes/auth.php';
redirectIfNotLoggedIn();

$id = $_GET['id'];
$lab_id = getLabId();

// Get issue details with related information
$issue = mysqli_query($conn, "
    SELECT ci.*, c.name as component_name, c.component_id as comp_id, c.specification,
           s.name as student_name, s.student_id as stu_id, s.email as student_email, s.phone as student_phone, s.course,
           l_from.lab_name as from_lab, l_to.lab_name as to_lab,
           u.name as issued_by_name, u.email as issued_by_email
    FROM component_issues ci
    JOIN components c ON ci.component_id = c.id
    LEFT JOIN students s ON ci.student_id = s.id
    JOIN labs l_from ON ci.issued_from_lab = l_from.id
    LEFT JOIN labs l_to ON ci.to_lab_id = l_to.id
    JOIN users u ON ci.issued_by = u.id
    WHERE ci.id = $id
")->fetch_assoc();

if (!$issue) {
    die("Issue record not found");
}

// Check permission
if (!isAdmin() && $issue['issued_from_lab'] != $lab_id) {
    die("Access denied");
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>View Issue Details - Component Tracker</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <div class="header-actions">
            <h1>Issue Details: <?php echo $issue['issue_id']; ?></h1>
            <div>
                <?php if($issue['status'] == 'issued' && (isAdmin() || $issue['issued_from_lab'] == $lab_id)): ?>
                    <a href="return.php?id=<?php echo $id; ?>" class="btn btn-success">Mark Return</a>
                <?php endif; ?>
                <a href="index.php" class="btn btn-secondary">Back to Issues</a>
            </div>
        </div>

        <div class="details-card">
            <h3>Component Information</h3>
            <div class="details-grid">
                <div class="detail-item">
                    <strong>Component Name:</strong>
                    <span><?php echo $issue['component_name']; ?></span>
                </div>
                <div class="detail-item">
                    <strong>Component ID:</strong>
                    <span><?php echo $issue['comp_id']; ?></span>
                </div>
                <div class="detail-item">
                    <strong>Quantity Issued:</strong>
                    <span><?php echo $issue['quantity_issued']; ?></span>
                </div>
                <div class="detail-item">
                    <strong>Specification:</strong>
                    <span><?php echo $issue['specification'] ?: 'N/A'; ?></span>
                </div>
            </div>

            <h3>Issue Information</h3>
            <div class="details-grid">
                <div class="detail-item">
                    <strong>Issued From Lab:</strong>
                    <span><?php echo $issue['from_lab']; ?></span>
                </div>
                <div class="detail-item">
                    <strong>Issued By:</strong>
                    <span><?php echo $issue['issued_by_name']; ?> (<?php echo $issue['issued_by_email']; ?>)</span>
                </div>
                <div class="detail-item">
                    <strong>Issue Date:</strong>
                    <span><?php echo date('M j, Y', strtotime($issue['issue_date'])); ?></span>
                </div>
                <div class="detail-item">
                    <strong>Expected Return:</strong>
                    <span class="<?php echo $issue['status'] == 'issued' && strtotime($issue['expected_return_date']) < time() ? 'text-danger' : ''; ?>">
                        <?php echo date('M j, Y', strtotime($issue['expected_return_date'])); ?>
                    </span>
                </div>
            </div>

            <?php if($issue['student_id']): ?>
            <h3>Student Information</h3>
            <div class="details-grid">
                <div class="detail-item">
                    <strong>Student Name:</strong>
                    <span><?php echo $issue['student_name']; ?></span>
                </div>
                <div class="detail-item">
                    <strong>Student ID:</strong>
                    <span><?php echo $issue['stu_id']; ?></span>
                </div>
                <div class="detail-item">
                    <strong>Course:</strong>
                    <span><?php echo $issue['course']; ?></span>
                </div>
                <div class="detail-item">
                    <strong>Contact:</strong>
                    <span><?php echo $issue['student_email']; ?> | <?php echo $issue['student_phone']; ?></span>
                </div>
            </div>
            <?php else: ?>
            <h3>Lab Transfer Information</h3>
            <div class="details-grid">
                <div class="detail-item">
                    <strong>Transferred To Lab:</strong>
                    <span><?php echo $issue['to_lab']; ?></span>
                </div>
            </div>
            <?php endif; ?>

            <h3>Condition & Status</h3>
            <div class="details-grid">
                <div class="detail-item">
                    <strong>Condition when Issued:</strong>
                    <span><?php echo $issue['condition_issued']; ?></span>
                </div>
                <div class="detail-item">
                    <strong>Current Status:</strong>
                    <span class="status-<?php echo $issue['status']; ?>">
                        <?php echo ucfirst($issue['status']); ?>
                    </span>
                </div>
                <?php if($issue['actual_return_date']): ?>
                <div class="detail-item">
                    <strong>Actual Return Date:</strong>
                    <span><?php echo date('M j, Y', strtotime($issue['actual_return_date'])); ?></span>
                </div>
                <div class="detail-item">
                    <strong>Condition when Returned:</strong>
                    <span><?php echo $issue['condition_returned']; ?></span>
                </div>
                <?php endif; ?>
            </div>

            <?php if($issue['purpose']): ?>
            <div class="detail-section">
                <strong>Purpose of Issue:</strong>
                <p><?php echo $issue['purpose']; ?></p>
            </div>
            <?php endif; ?>

            <?php if($issue['notes']): ?>
            <div class="detail-section">
                <strong>Notes:</strong>
                <p><?php echo nl2br($issue['notes']); ?></p>
            </div>
            <?php endif; ?>

            <div class="detail-section">
                <strong>Last Updated:</strong>
                <span><?php echo date('M j, Y g:i A', strtotime($issue['updated_at'])); ?></span>
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>