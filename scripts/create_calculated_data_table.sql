-- Create table for calculated energy consumption and costs
CREATE TABLE IF NOT EXISTS `energy_calculations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reading_id` int(11) NOT NULL,
  `power_watts` float NOT NULL,
  `energy_kwh` float NOT NULL DEFAULT 0,
  `daily_cost` decimal(10,2) DEFAULT 0,
  `weekly_cost` decimal(10,2) DEFAULT 0,
  `monthly_cost` decimal(10,2) DEFAULT 0,
  `calculation_time` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`reading_id`) REFERENCES `readings`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create table for pricing tiers
CREATE TABLE IF NOT EXISTS `pricing_tiers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tier_name` varchar(50) NOT NULL,
  `min_units` int(11) NOT NULL,
  `max_units` int(11) NOT NULL,
  `price_per_unit` decimal(10,2) NOT NULL,
  `currency` varchar(10) DEFAULT 'NPR',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert default pricing tiers for Nepal
INSERT INTO `pricing_tiers` (`tier_name`, `min_units`, `max_units`, `price_per_unit`, `currency`) VALUES
('Basic Tier', 0, 20, 8.50, 'NPR'),
('Standard Tier', 21, 40, 12.00, 'NPR'),
('High Usage Tier', 41, 999999, 15.50, 'NPR');
