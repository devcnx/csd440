<?php
header('Content-Type: text/html');

file_put_contents('config.php', '<?php echo "config.php loaded\n"; ?>');
file_put_contents('database.php', '<?php echo "database.php loaded\n"; ?>');
file_put_contents('header.php', '<?php echo "header.php loaded\n"; ?>');
file_put_contents('navigation.php', '<?php echo "navigation.php loaded\n"; ?>');

echo "=== INCLUDE (file exists) ===\n";
include 'config.php';

echo "\n=== REQUIRE (file exists) ===\n";
require 'database.php';

echo "\n=== INCLUDE MULTIPLE FILES ===\n";
include 'header.php';
include 'navigation.php';

echo "\n=== INCLUDE (file missing — warning, script continues) ===\n";
include 'missing_file.php';
echo "Script kept running after missing include\n";

echo "\n=== REQUIRE (file missing — fatal error, script stops) ===\n";
echo "Uncomment the line below to test require with a missing file.\n";
echo "Warning: it will stop execution of everything after it.\n\n";
// require 'another_missing_file.php';

unlink('config.php');
unlink('database.php');
unlink('header.php');
unlink('navigation.php');

?>