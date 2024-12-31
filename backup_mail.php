<?php
// Define the path to the mail directory in cPanel
$mailDir = '/home/your_cpanel_user/mail'; // Adjust this path if needed
$zipFile = '/home/your_cpanel_user/mail_backup_' . date('Y-m-d') . '.zip'; // Zip file with date

// List of directories to include in the zip
$directoriesToCompress = [
    'dir1',
    'dir2',
    'dir3'
];

// Create a bash script to handle the zipping process
$bashScript = "/home/your_cpanel_user/mail_backup.sh";
$directories = implode(' ', array_map('escapeshellarg', $directoriesToCompress));

// Generate the bash script content
$scriptContent = <<<EOT
#!/bin/bash
zip -r $zipFile $(echo $directories | sed "s|^|$mailDir/|")
EOT;

// Write the bash script
file_put_contents($bashScript, $scriptContent);

// Make the bash script executable
chmod($bashScript, 0755);

// Run the bash script in the background
exec("nohup bash $bashScript > /dev/null 2>&1 & echo $!", $output);

// Return success message with Process ID (PID)
echo "Mail directories compression started in the background. Process ID: " . $output[0];
?>
