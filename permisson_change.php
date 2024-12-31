<?php
$targetDir = '/home/your_cpanel_user/public_html'; // Set your target directory

/**
 * Recursively set permissions for files and directories
 *
 * @param string $path The path to start changing permissions
 */
function setPermissions($path) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $item) {
        if ($item->isDir()) {
            chmod($item->getPathname(), 0755); // Set directory permission
            echo "Directory: " . $item->getPathname() . " -> 0755\n";
        } elseif ($item->isFile()) {
            chmod($item->getPathname(), 0644); // Set file permission
            echo "File: " . $item->getPathname() . " -> 0644\n";
        }
    }

    // Set the root directory permission
    chmod($path, 0755);
    echo "Root Directory: $path -> 0755\n";
}

// Check if the target directory exists
if (!is_dir($targetDir)) {
    die("Error: Directory does not exist: $targetDir");
}

// Execute the permission change
setPermissions($targetDir);

echo "Permissions updated successfully!";
?>
