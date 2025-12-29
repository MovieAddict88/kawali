<?php
// A simple script to check the current value of the 'max_file_uploads' PHP directive.
// This helps diagnose whether server settings are being applied correctly.

// Get the current value of max_file_uploads
$max_uploads = ini_get('max_file_uploads');

// Output the value in a clear, human-readable format
echo "<h1>PHP Configuration Check</h1>";
echo "<p>The current value of <strong>max_file_uploads</strong> is: " . htmlspecialchars($max_uploads) . "</p>";

// Provide some context for the value
if ($max_uploads == 20) {
    echo "<p><strong>Diagnosis:</strong> The value is still at the default of 20. This indicates that the previous change in the <code>.user.ini</code> file was not effective.</p>";
} elseif ($max_uploads > 20) {
    echo "<p><strong>Diagnosis:</strong> The setting has been successfully increased. The issue might be elsewhere.</p>";
} else {
    echo "<p><strong>Diagnosis:</strong> The value is set to an unexpected value.</p>";
}
?>
