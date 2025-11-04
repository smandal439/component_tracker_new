<?php
// includes/navigation.php
function getBasePath() {
    $current_file = $_SERVER['PHP_SELF'];
    $current_dir = dirname($current_file);
    
    // If we're in root directory files (dashboard.php, login.php, etc.)
    if ($current_dir == '/' || $current_dir == '\\' || basename($current_dir) == 'htdocs' || basename($current_dir) == 'component_tracker_new') {
        return '';
    }
    
    // If we're in subdirectories (labs/, components/, etc.)
    return '../';
}

function url($path) {
    $base = getBasePath();
    return $base . $path;
}
?>