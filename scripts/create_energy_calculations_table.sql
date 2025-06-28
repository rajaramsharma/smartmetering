-- Create energy calculations table with proper structure
CREATE TABLE IF NOT EXISTS `energy_calculations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reading_id` int(11) NOT NULL,
  `current_amps` float NOT NULL,
  `voltage` float DEFAULT 220,
  `power_watts` float NOT NULL,
  `energy_kwh` float NOT NULL,
  `duration_seconds` int DEFAULT 5,
  `cost_npr` decimal(10,4) NOT NULL,
  `tier_applied` varchar(50) DEFAULT NULL,
  `calculation_date` date NOT NULL,
  `calculation_time` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_reading_id` (`reading_id`),
  KEY `idx_calculation_date` (`calculation_date`),
  FOREIGN KEY (`reading_id`) REFERENCES `readings`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create daily summary table
CREATE TABLE IF NOT EXISTS `daily_energy_summary` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `summary_date` date NOT NULL UNIQUE,
  `total_energy_kwh` decimal(10,4) NOT NULL DEFAULT 0,
  `total_cost_npr` decimal(10,2) NOT NULL DEFAULT 0,
  `avg_current` decimal(8,2) NOT NULL DEFAULT 0,
  `max_current` decimal(8,2) NOT NULL DEFAULT 0,
  `min_current` decimal(8,2) NOT NULL DEFAULT 0,
  `total_readings` int NOT NULL DEFAULT 0,
  `on_time_seconds` int NOT NULL DEFAULT 0,
  `efficiency_percent` decimal(5,2) NOT NULL DEFAULT 0,
  `created_at` timestamp DEFAULT current_timestamp(),
  `updated_at` timestamp DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_summary_date` (`summary_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
