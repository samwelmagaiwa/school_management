<?php
$logFile = 'storage/logs/laravel.log';
if (!file_exists($logFile)) {
    echo "Log file not found.\n";
    exit;
}

$lines = file($logFile);
$start = max(0, count($lines) - 50);

echo "--- LAST 50 LINES OF LOG ---\n";
for ($i = $start; $i < count($lines); $i++) {
    echo $lines[$i];
}
