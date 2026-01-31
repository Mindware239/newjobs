<?php
// Check which php.ini is being used and if GD is loaded
echo "<h2>PHP Configuration Check</h2>";
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";
echo "<p><strong>php.ini Location:</strong> " . php_ini_loaded_file() . "</p>";
echo "<p><strong>GD Extension Loaded:</strong> " . (extension_loaded('gd') ? '<span style="color:green">YES ✓</span>' : '<span style="color:red">NO ✗</span>') . "</p>";

if (extension_loaded('gd')) {
    $gdInfo = gd_info();
    echo "<h3>GD Information:</h3>";
    echo "<pre>";
    print_r($gdInfo);
    echo "</pre>";
} else {
    echo "<p style='color:red;'><strong>ERROR:</strong> GD extension is NOT loaded!</p>";
    echo "<p>Please enable it in: <code>" . php_ini_loaded_file() . "</code></p>";
    echo "<p>Look for: <code>;extension=gd</code> and change it to: <code>extension=gd</code></p>";
    echo "<p>Then restart Apache.</p>";
}

