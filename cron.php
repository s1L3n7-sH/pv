<?php
$process_name = 'client2';
$command = '/etc/bluetooth/bluetooth/pv/client2';

function is_process_running($process_name) {
    $output = [];
    // Check for the process and exclude grep itself
    exec("ps -e | grep " . escapeshellarg($process_name) . " | grep -v grep", $output);
    return count($output) > 0;
}

if (!is_process_running($process_name)) {
    // Start the process in background silently
    exec($command . " > /dev/null 2>&1 &");
    echo "$process_name was not running, started it.\n";
} else {
    echo "$process_name is already running.\n";
}
?>
