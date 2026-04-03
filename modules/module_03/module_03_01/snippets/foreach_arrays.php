<?php
header('Content-Type: text/html');

echo "=== FOREACH — VALUE ONLY ===\n";
$fruits = ['apple', 'banana', 'cherry'];
foreach ($fruits as $fruit) {
    echo $fruit . "\n";
}
echo "\n";

echo "=== FOREACH — KEY AND VALUE (Associative Array) ===\n";
$person = ['name' => 'Brittaney', 'role' => 'IT Specialist', 'city' => 'Springdale'];
foreach ($person as $key => $value) {
    echo "$key: $value\n";
}

?>