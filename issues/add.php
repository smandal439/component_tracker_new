<?php
// issues/add.php
require_once '../includes/config.php';
require_once '../includes/auth.php';
redirectIfNotLoggedIn();

$lab_id = getLabId();
$user_id = $_SESSION['user_id'];

// Debug: Check if lab_id is set
error_log("Issues Add - Lab ID: " . ($lab_id ?: 'Not set'));

if (!$lab_id && isAdmin()) {
    // If admin but no lab selected, redirect to components to select a lab first
    $_SESSION['error'] = "Please select a lab first to issue components.";
    header("Location: ../components/index.php");
    exit();
}

if (!$lab_id) {
    die("Error: No lab assigned to your account. Please contact administrator.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $component_id = mysqli_real_escape_string($conn, $_POST['component_id']);
    $issue_type = mysqli_real_escape_string($conn, $_POST['issue_type']);
    $student_id = mysqli_real_escape_string($conn, $_POST['student_id']);
    $to_lab_id = mysqli_real_escape_string($conn, $_POST['to_lab_id']);
    $quantity_issued = (int)$_POST['quantity_issued'];
    $issue_date = mysqli_real_escape_string($conn, $_POST['issue_date']);
    $expected_return_date = mysqli_real_escape_string($conn, $_POST['expected_return_date']);
    $purpose = mysqli_real_escape_string($conn, $_POST['purpose']);
    $condition_issued = mysqli_real_escape_string($conn, $_POST['condition_issued']);
    $notes = mysqli_real_escape_string($conn, $_POST['notes']);
    
    // Generate unique issue ID
    $issue_id = 'ISS-' . date('Ymd') . '-' . rand(1000, 9999);
    
    // Check component availability
    $component_query = "SELECT * FROM components WHERE id = $component_id AND lab_id = $lab_id";
    $component_result = mysqli_query($conn, $component_query);
    
    if (!$component_result) {
        $error = "Database error: " . mysqli_error($conn);
    } elseif (mysqli_num_rows($component_result) == 0) {
        $error = "Component not found in your lab.";
    } else {
        $component = mysqli_fetch_assoc($component_result);
        $available = $component['available_quantity'];
        
        if ($quantity_issued > $available) {
            $error = "Cannot issue $quantity_issued items. Only $available available.";
        } else if ($quantity_issued <= 0) {
            $error = "Quantity must be greater than 0.";
        } else {
            // Validate issue type
            if ($issue_type === 'student' && empty($student_id)) {
                $error = "Please select a student for student issue.";
            } else if ($issue_type === 'lab' && empty($to_lab_id)) {
                $error = "Please select a lab for lab transfer.";
            } else {
                // Start transaction
                mysqli_begin_transaction($conn);
                
                try {
                    // Insert issue record
                    $student_id_value = ($issue_type === 'student' && !empty($student_id)) ? $student_id : 'NULL';
                    $to_lab_id_value = ($issue_type === 'lab' && !empty($to_lab_id)) ? $to_lab_id : 'NULL';
                    
                    $sql = "INSERT INTO component_issues (issue_id, component_id, student_id, to_lab_id, issued_by, issued_from_lab, quantity_issued, issue_date, expected_return_date, purpose, condition_issued, notes) 
                            VALUES ('$issue_id', $component_id, $student_id_value, $to_lab_id_value, $user_id, $lab_id, $quantity_issued, '$issue_date', '$expected_return_date', '$purpose', '$condition_issued', '$notes')";
                    
                    if (!mysqli_query($conn, $sql)) {
                        throw new Exception("Insert failed: " . mysqli_error($conn));
                    }
                    
                    // Update component total_issued
                    $update_sql = "UPDATE components SET total_issued = total_issued + $quantity_issued WHERE id = $component_id";
                    if (!mysqli_query($conn, $update_sql)) {
                        throw new Exception("Update failed: " . mysqli_error($conn));
                    }
                    
                    mysqli_commit($conn);
                    $_SESSION['success'] = "Component issued successfully! Issue ID: $issue_id";
                    header("Location: index.php");
                    exit();
                    
                } catch (Exception $e) {
                    mysqli_rollback($conn);
                    $error = "Error: " . $e->getMessage();
                }
            }
        }
    }
}

// Get components available in current lab - FIXED QUERY
$components_query = "SELECT * FROM components WHERE lab_id = $lab_id AND available_quantity > 0 AND status = 'Available'";
$components = mysqli_query($conn, $components_query);

if (!$components) {
    $error = "Database error: " . mysqli_error($conn);
}

// Get students
$students = mysqli_query($conn, "SELECT * FROM students WHERE is_active = 1");

// Get other labs (for inter-lab transfer)
$labs_query = "SELECT * FROM labs WHERE id != $lab_id AND is_active = 1";
$labs = mysqli_query($conn, $labs_query);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Issue Component - Component Tracker</title>
    <link rel="stylesheet" href="../css/style.css">
    <script>
        function toggleIssueType() {
            var issueType = document.getElementById('issue_type').value;
            document.getElementById('student_section').style.display = issueType === 'student' ? 'block' : 'none';
            document.getElementById('lab_section').style.display = issueType === 'lab' ? 'block' : 'none';
            
            // Set required attributes
            var studentSelect = document.querySelector('[name="student_id"]');
            var labSelect = document.querySelector('[name="to_lab_id"]');
            
            if (issueType === 'student') {
                studentSelect.required = true;
                labSelect.required = false;
            } else if (issueType === 'lab') {
                studentSelect.required = false;
                labSelect.required = true;
            } else {
                studentSelect.required = false;
                labSelect.required = false;
            }
        }
        
        function updateAvailableQuantity() {
            var componentSelect = document.getElementById('component_id');
            var selectedOption = componentSelect.options[componentSelect.selectedIndex];
            var available = selectedOption.getAttribute('data-available');
            
            if (available) {
                document.getElementById('available_quantity').textContent = available;
                var quantityInput = document.querySelector('[name="quantity_issued"]');
                quantityInput.max = available;
                
                if (parseInt(quantityInput.value) > parseInt(available)) {
                    quantityInput.value = available;
                }
            }
        }
        
        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            toggleIssueType();
            updateAvailableQuantity();
        });
    </script>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <div class="header-actions">
            <h1>Issue Component</h1>
            <a href="index.php" class="btn btn-secondary">Back to Issues</a>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <form method="POST" action="" class="form">
            <div class="form-row">
                <div class="form-group">
                    <label>Component *</label>
                    <select name="component_id" id="component_id" required class="form-control" onchange="updateAvailableQuantity()">
                        <option value="">-- Select Component --</option>
                        <?php if ($components && mysqli_num_rows($components) > 0): ?>
                            <?php while($component = mysqli_fetch_assoc($components)): ?>
                                <option value="<?php echo $component['id']; ?>" data-available="<?php echo $component['available_quantity']; ?>">
                                    <?php echo htmlspecialchars($component['name']); ?> (<?php echo htmlspecialchars($component['component_id']); ?>) - Available: <?php echo $component['available_quantity']; ?>
                                </option>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <option value="" disabled>No available components in your lab</option>
                        <?php endif; ?>
                    </select>
                    <small id="available_quantity_display" style="display: block; margin-top: 5px; color: #666;">
                        Available: <span id="available_quantity">0</span>
                    </small>
                </div>

                <div class="form-group">
                    <label>Issue Type *</label>
                    <select name="issue_type" id="issue_type" required class="form-control" onchange="toggleIssueType()">
                        <option value="">-- Select Type --</option>
                        <option value="student">Issue to Student</option>
                        <option value="lab">Transfer to Another Lab</option>
                    </select>
                </div>
            </div>

            <!-- Student Section -->
            <div id="student_section" style="display: none;">
                <div class="form-group">
                    <label>Student *</label>
                    <select name="student_id" class="form-control">
                        <option value="">-- Select Student --</option>
                        <?php if ($students && mysqli_num_rows($students) > 0): ?>
                            <?php while($student = mysqli_fetch_assoc($students)): ?>
                                <option value="<?php echo $student['id']; ?>">
                                    <?php echo htmlspecialchars($student['name']); ?> (<?php echo htmlspecialchars($student['student_id']); ?>) - <?php echo htmlspecialchars($student['course']); ?>
                                </option>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <option value="" disabled>No students found</option>
                        <?php endif; ?>
                    </select>
                </div>
            </div>

            <!-- Lab Section -->
            <div id="lab_section" style="display: none;">
                <div class="form-group">
                    <label>Transfer to Lab *</label>
                    <select name="to_lab_id" class="form-control">
                        <option value="">-- Select Lab --</option>
                        <?php if ($labs && mysqli_num_rows($labs) > 0): ?>
                            <?php while($lab = mysqli_fetch_assoc($labs)): ?>
                                <option value="<?php echo $lab['id']; ?>">
                                    <?php echo htmlspecialchars($lab['lab_name']); ?> (<?php echo htmlspecialchars($lab['lab_code']); ?>)
                                </option>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <option value="" disabled>No other labs found</option>
                        <?php endif; ?>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Quantity to Issue *</label>
                    <input type="number" name="quantity_issued" required class="form-control" min="1" value="1">
                </div>

                <div class="form-group">
                    <label>Condition when Issued</label>
                    <select name="condition_issued" class="form-control">
                        <option value="Excellent">Excellent</option>
                        <option value="Good" selected>Good</option>
                        <option value="Fair">Fair</option>
                        <option value="Poor">Poor</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Issue Date *</label>
                    <input type="date" name="issue_date" required class="form-control" value="<?php echo date('Y-m-d'); ?>">
                </div>

                <div class="form-group">
                    <label>Expected Return Date *</label>
                    <input type="date" name="expected_return_date" required class="form-control" min="<?php echo date('Y-m-d'); ?>">
                </div>
            </div>

            <div class="form-group">
                <label>Purpose of Issue</label>
                <textarea name="purpose" class="form-control" rows="3" placeholder="e.g., Lab experiment, project work, etc."></textarea>
            </div>

            <div class="form-group">
                <label>Notes</label>
                <textarea name="notes" class="form-control" rows="2"></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Issue Component</button>
                <a href="index.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>