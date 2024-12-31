<?php
// Path to the .tar.gz file and destination directory
$tarFile = '/home/your_cpanel_user/backup.tar.gz'; // Path to your .tar.gz file
$destination = '/home/your_cpanel_user/extracted_files'; // Destination folder

// Ensure destination directory exists
if (!is_dir($destination)) {
    mkdir($destination, 0755, true);
}

// Create a bash script to handle the extraction process
$bashScript = '/home/your_cpanel_user/extract_tar.sh';
$scriptContent = <<<EOT
#!/bin/bash
tar -xzvf $tarFile -C $destination
EOT;

// Write the bash script
file_put_contents($bashScript, $scriptContent);

// Make the bash script executable
chmod($bashScript, 0755);

// Run the extraction script in the background
exec("nohup bash $bashScript > /dev/null 2>&1 & echo $!", $output);

// Return success message with Process ID (PID)
echo "TAR.GZ extraction started in the background. Process ID: " . $output[0];
?>
