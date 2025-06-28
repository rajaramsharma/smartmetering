<?php
// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "smartmetering";

// Create connection
try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Constants
define('VOLTAGE', 220); // Constant voltage in Nepal
define('HOURS_PER_DAY', 24);
define('DAYS_PER_WEEK', 7);
define('DAYS_PER_MONTH', 30);

// Pricing tiers (NPR per kWh)
$pricing_tiers = [
    ['min' => 0, 'max' => 20, 'price' => 8.50],
    ['min' => 21, 'max' => 40, 'price' => 12.00],
    ['min' => 41, 'max' => 999999, 'price' => 15.50]
];

// Make pricing tiers globally accessible
$GLOBALS['pricing_tiers'] = $pricing_tiers;

// Function to calculate tiered cost
function calculateTieredCost($units, $tiers) {
    $totalCost = 0;
    $remainingUnits = $units;
    
    foreach ($tiers as $tier) {
        if ($remainingUnits <= 0) break;
        
        $tierUnits = min($remainingUnits, $tier['max'] - $tier['min'] + 1);
        if ($units > $tier['min']) {
            $applicableUnits = min($tierUnits, $remainingUnits);
            $totalCost += $applicableUnits * $tier['price'];
            $remainingUnits -= $applicableUnits;
        }
    }
    
    return $totalCost;
}

// Function to calculate power and energy
function calculatePowerAndEnergy($current, $timeInterval = 5) {
    $power = VOLTAGE * $current; // P = V × I (watts)
    $energy = ($power * $timeInterval) / (1000 * 3600); // Convert to kWh
    return ['power' => $power, 'energy' => $energy];
}
?>
