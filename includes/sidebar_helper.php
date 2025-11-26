<?php
function getSidebarClass() {
    $current_page = basename($_SERVER['PHP_SELF']);
    $current_dir = dirname($_SERVER['PHP_SELF']);
    
    // Determine if we're in the STL directory
    $isSTL = strpos($current_dir, '/stl') !== false;
    
    return [
        'contrib' => !$isSTL ? 'active' : '',
        'stl' => $isSTL ? 'active' : ''
    ];
}
?>
