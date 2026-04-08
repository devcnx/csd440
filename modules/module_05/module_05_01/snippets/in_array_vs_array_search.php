<?php
$allowedExtensions = ["jpg", "jpeg", "png", "gif", "webp"];

$testFiles = ["photo.jpg", "document.pdf", "image.png", "script.exe"];

foreach ($testFiles as $filename) {
    $uploadExtension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    $allowed = in_array($uploadExtension, $allowedExtensions);
    $status = $allowed ? "✓ ALLOWED" : "✗ BLOCKED";
    echo "$filename → .$uploadExtension → $status<br>";
}
?>

<?php
$inventory = [
    "SKU-A100" => ["name" => "Wireless Mouse", "qty" => 45],
    "SKU-A200" => ["name" => "Mechanical Keyboard", "qty" => 12],
    "SKU-A300" => ["name" => "USB-C Hub", "qty" => 8]
];

$searchTerms = ["Mechanical Keyboard", "Wireless Mouse", "USB-C Hub", "Monitor Stand"];

foreach ($searchTerms as $searchName) {
    $inventory[$searchName] = ["name" => $searchName];
    $sku = array_search($searchName, array_column($inventory, "name"));

    if ($sku !== false) {
        $inventory[$sku]["qty"] -= 1;
        echo "✓ Found '$searchName' → SKU: $sku → Purchased! Qty: {$inventory[$sku]['qty']}<br>";
    } else {
        echo "✗ '$searchName' not found in inventory<br>";
    }
}
?>