<?php
require_once '../includes/config.php';

// Function to calculate and store energy data
function calculateAndStoreEnergyData() {
    global $pdo, $pricing_tiers;
    
    try {
        // Get all readings that haven't been processed
        $stmt = $pdo->query("
            SELECT r.* FROM readings r 
            LEFT JOIN energy_calculations ec ON r.id = ec.reading_id 
            WHERE ec.reading_id IS NULL 
            ORDER BY r.reading_time ASC
        ");
        
        $unprocessedReadings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "Found " . count($unprocessedReadings) . " unprocessed readings.\n";
        
        foreach ($unprocessedReadings as $reading) {
            // Calculate power (P = V × I)
            $power = VOLTAGE * $reading['measured_current'];
            
            // Calculate energy for 5-second interval (kWh)
            $energy = ($power * 5) / (1000 * 3600); // 5 seconds to kWh
            
            // Calculate cost using tiered pricing
            $cost = calculateTieredCost($energy, $pricing_tiers);
            
            // Determine which tier was applied
            $tierApplied = getTierName($energy, $pricing_tiers);
            
            // Insert into energy_calculations table
            $insertStmt = $pdo->prepare("
                INSERT INTO energy_calculations 
                (reading_id, current_amps, voltage, power_watts, energy_kwh, cost_npr, tier_applied, calculation_date) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $insertStmt->execute([
                $reading['id'],
                $reading['measured_current'],
                VOLTAGE,
                $power,
                $energy,
                $cost,
                $tierApplied,
                date('Y-m-d', strtotime($reading['reading_time']))
            ]);
        }
        
        echo "Processed " . count($unprocessedReadings) . " readings successfully.\n";
        
        // Update daily summaries
        updateDailySummaries();
        
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}

// Function to get tier name based on energy consumption
function getTierName($energy, $tiers) {
    foreach ($tiers as $tier) {
        if ($energy >= $tier['min'] && $energy <= $tier['max']) {
            if ($tier['min'] == 0) return 'Basic Tier (0-20 kWh)';
            if ($tier['min'] == 21) return 'Standard Tier (21-40 kWh)';
            return 'High Usage Tier (41+ kWh)';
        }
    }
    return 'Basic Tier';
}

// Function to update daily summaries
function updateDailySummaries() {
    global $pdo;
    
    try {
        // Get dates that need summary updates
        $stmt = $pdo->query("
            SELECT DISTINCT calculation_date 
            FROM energy_calculations 
            WHERE calculation_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        ");
        
        $dates = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        foreach ($dates as $date) {
            // Calculate daily totals
            $summaryStmt = $pdo->prepare("
                SELECT 
                    SUM(ec.energy_kwh) as total_energy,
                    SUM(ec.cost_npr) as total_cost,
                    AVG(ec.current_amps) as avg_current,
                    MAX(ec.current_amps) as max_current,
                    MIN(ec.current_amps) as min_current,
                    COUNT(*) as total_readings,
                    SUM(CASE WHEN r.relay_status = 'ON' THEN 5 ELSE 0 END) as on_time_seconds
                FROM energy_calculations ec
                JOIN readings r ON ec.reading_id = r.id
                WHERE ec.calculation_date = ?
            ");
            
            $summaryStmt->execute([$date]);
            $summary = $summaryStmt->fetch(PDO::FETCH_ASSOC);
            
            // Calculate efficiency
            $efficiency = $summary['total_readings'] > 0 ? 
                ($summary['on_time_seconds'] / ($summary['total_readings'] * 5)) * 100 : 0;
            
            // Insert or update daily summary
            $upsertStmt = $pdo->prepare("
                INSERT INTO daily_energy_summary 
                (summary_date, total_energy_kwh, total_cost_npr, avg_current, max_current, min_current, 
                 total_readings, on_time_seconds, efficiency_percent) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                total_energy_kwh = VALUES(total_energy_kwh),
                total_cost_npr = VALUES(total_cost_npr),
                avg_current = VALUES(avg_current),
                max_current = VALUES(max_current),
                min_current = VALUES(min_current),
                total_readings = VALUES(total_readings),
                on_time_seconds = VALUES(on_time_seconds),
                efficiency_percent = VALUES(efficiency_percent),
                updated_at = CURRENT_TIMESTAMP
            ");
            
            $upsertStmt->execute([
                $date,
                $summary['total_energy'] ?? 0,
                $summary['total_cost'] ?? 0,
                $summary['avg_current'] ?? 0,
                $summary['max_current'] ?? 0,
                $summary['min_current'] ?? 0,
                $summary['total_readings'] ?? 0,
                $summary['on_time_seconds'] ?? 0,
                $efficiency
            ]);
        }
        
        echo "Updated daily summaries for " . count($dates) . " dates.\n";
        
    } catch (Exception $e) {
        echo "Error updating summaries: " . $e->getMessage() . "\n";
    }
}

// Run the calculation
if (php_sapi_name() === 'cli') {
    calculateAndStoreEnergyData();
} else {
    echo "This script should be run from command line.\n";
}
?>
