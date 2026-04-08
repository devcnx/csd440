<?php
$dailyTemps = ["Monday" => 72, "Tuesday" => 68, "Wednesday" => 75, "Thursday" => 71];

echo "=== sort() ===<br>";
echo "BEFORE:<br>";
foreach ($dailyTemps as $day => $temp) {
    echo "  [$day] => $temp<br>";
}

sort($dailyTemps);
echo "<br>";
echo "AFTER sort():<br>";
foreach ($dailyTemps as $key => $temp) {
    echo "  [$key] => $temp<br>";
}

echo "<br>⚠️  Keys are now 0,1,2,3 — original day labels GONE!<br><br>";
?>


<?php
$shippingRates = [
    "FEDX" => 12.99,
    "USPS" => 7.49,
    "DHL" => 15.99,
    "UPS" => 11.49
];

echo "=== asort() ===<br>";
echo "BEFORE:<br>";
foreach ($shippingRates as $carrier => $price) {
    echo "  [$carrier] => $$price<br>";
}

asort($shippingRates);
echo "<br>";
echo "AFTER asort():<br>";
foreach ($shippingRates as $carrier => $price) {
    echo "  [$carrier] => $$price<br>";
}

echo "<br>✓ Keys intact — can still reference by carrier code!<br>";
?>