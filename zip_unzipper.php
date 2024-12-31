<?php
// Path to the ZIP file and destination directory
$zipFile = '/home/your_cpanel_user/backup.zip'; // Path to your ZIP file
$destination = '/home/your_cpanel_user/extracted_files'; // Destination folder

// Ensure destination directory exists
if (!is_dir($destination)) {
    mkdir($destination, 0755, true);
}

// Create a bash script to handle the extraction process
$bashScript = '/home/your_cpanel_user/extract_zip.sh';
$scriptContent = <<<EOT
#!/bin/bash
unzip -o $zipFile -d $destination
EOT;

// Write the bash script
file_put_contents($bashScript, $scriptContent);

// Make the bash script executable
chmod($bashScript, 0755);

// Run the extraction script in the background
exec("nohup bash $bashScript > /dev/null 2>&1 & echo $!", $output);

// Return success message with Process ID (PID)
echo "ZIP extraction started in the background. Process ID: " . $output[0];
?>
