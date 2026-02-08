<?php
// Example usage of School settings
$school = \App\Models\School::first();
if ($school) {
    // Current settings
    echo "Current Settings: " . json_encode($school->settings) . "\n";
    
    // How we might use it in the future
    $newSettings = [
        'theme_color' => '#3498db',
        'logo_url' => '/uploads/schools/logo_1.png',
        'modules' => [
            'library' => true,
            'transport' => false
        ],
        'contact_email' => 'contact@stjohns.edu'
    ];
    
    echo "Potential Setup: " . json_encode($newSettings, JSON_PRETTY_PRINT) . "\n";
}
