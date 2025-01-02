<?php
// Configuration
$baseMailDir = '/home/your_cpanel_user/mail';
$archiveDir = '/home/your_cpanel_user/mail_archive';
$logFile = '/home/your_cpanel_user/logs/mail_backup.log';
$days = 1095; // 3 years in days

// Ensure archive and log directories exist
if (!is_dir($archiveDir)) {
    mkdir($archiveDir, 0755, true);
}
if (!file_exists($logFile)) {
    touch($logFile);
}

// Temporary backup directory
$tempBackupDir = "$archiveDir/temp_mail_backup";
if (!is_dir($tempBackupDir)) {
    mkdir($tempBackupDir, 0755, true);
}

$lastFileTimestamp = 0; // Initialize to track the last file's timestamp

// Get all email account directories
$emailAccounts = glob("$baseMailDir/*/*", GLOB_ONLYDIR);

foreach ($emailAccounts as $account) {
    echo "Processing: $account\n";

    // Extract account-specific folder name
    $accountName = str_replace($baseMailDir . '/', '', $account);
    $accountBackupDir = "$tempBackupDir/$accountName";

    // Ensure account-specific temp directory exists
    if (!is_dir($accountBackupDir)) {
        mkdir($accountBackupDir, 0755, true);
    }

    // Find emails older than 3 years and copy them to temp directory
    $findCommand = "find $account -type f -mtime +$days -printf '%T@ %p\n' | sort -n";
    $emailFiles = shell_exec($findCommand);

    if (!$emailFiles) {
        echo "No old emails found in $account\n";
        rmdir($accountBackupDir); // Remove empty temp directory
        continue;
    }

    $lines = explode("\n", trim($emailFiles));
    foreach ($lines as $line) {
        [$timestamp, $filePath] = explode(' ', $line, 2);
        if (!$filePath) continue;

        // Update the last file timestamp
        if ($timestamp > $lastFileTimestamp) {
            $lastFileTimestamp = $timestamp;
        }

        // Copy file to temp backup directory
        $relativePath = str_replace($account, '', $filePath);
        $destinationPath = "$accountBackupDir$relativePath";

        if (!is_dir(dirname($destinationPath))) {
            mkdir(dirname($destinationPath), 0755, true);
        }

        copy($filePath, $destinationPath);
    }

    // Delete old emails from original account
    $deleteCommand = "find $account -type f -mtime +$days -delete";
    shell_exec($deleteCommand);

    echo "Deleted old emails from $account\n";

    // Log the success
    $logEntry = "[" . date('Y-m-d H:i:s') . "] Processed $account and moved old emails to $accountBackupDir\n";
    file_put_contents($logFile, $logEntry, FILE_APPEND);
}

// Create a zip archive with the last email file's date
if ($lastFileTimestamp > 0) {
    $lastFileDate = date('Y-m-d', $lastFileTimestamp);
    $archiveFile = "$archiveDir/mail_backup_{$lastFileDate}.zip";

    // Create zip archive
    $zipCommand = "cd $tempBackupDir && zip -r $archiveFile .";
    shell_exec($zipCommand);

    if (file_exists($archiveFile)) {
        echo "Successfully created archive: $archiveFile\n";

        // Clean up temporary backup directory
        shell_exec("rm -rf $tempBackupDir");

        $logEntry = "[" . date('Y-m-d H:i:s') . "] Successfully archived all accounts into $archiveFile\n";
        file_put_contents($logFile, $logEntry, FILE_APPEND);
    } else {
        echo "Failed to create zip archive.\n";
        $logEntry = "[" . date('Y-m-d H:i:s') . "] Failed to create zip archive.\n";
        file_put_contents($logFile, $logEntry, FILE_APPEND);
    }
} else {
    echo "No emails were backed up; skipping archive creation.\n";
    $logEntry = "[" . date('Y-m-d H:i:s') . "] No old emails to archive.\n";
    file_put_contents($logFile, $logEntry, FILE_APPEND);
}

echo "Backup process completed.\n";
?>
