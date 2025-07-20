To get started with the project, make sure to create the required tables in your MySQL database.

CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `mobile` VARCHAR(255) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('user', 'admin') NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE `localities` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `locality` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE `buyProperty` (
  `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `areaInGaj` VARCHAR(255) NOT NULL,
  `locality2` VARCHAR(255) NOT NULL,
  `state` VARCHAR(255) NOT NULL,
  `budget` VARCHAR(255) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `mobile` VARCHAR(255) NOT NULL,
  `user_id` VARCHAR(255) NOT NULL,
  `typeOfProperty` ENUM('1 BHK', '2 BHK', '3 BHK', '4 BHK', 'House', 'Villa', 'Bungalow', 'Pent House', 'Shop', 'Land') NOT NULL,
  `done` ENUM('no', 'yes') NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `upated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE `listProperty` (
  `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `map_data` TEXT NOT NULL,
  `currentLocation` TEXT NOT NULL,
  `areaInGaj` VARCHAR(255) NOT NULL,
  `typeOfProperty` ENUM('1 BHK', '2 BHK', '3 BHK', '4 BHK', 'House', 'Villa', 'Bungalow', 'Pent House', 'Shop', 'Land') NOT NULL,
  `length` VARCHAR(255) NOT NULL,
  `breadth` VARCHAR(255) NOT NULL,
  `address` VARCHAR(255) NOT NULL,
  `locality` VARCHAR(255) NOT NULL,
  `state` VARCHAR(255) NOT NULL,
  `exprectedPrice` VARCHAR(255) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `mobile` VARCHAR(255) NOT NULL,
  `images` TEXT DEFAULT NULL,
  `document` VARCHAR(255) DEFAULT NULL,
  `user_id` VARCHAR(255) NOT NULL,
  `status` ENUM('Pending', 'Published', 'Done') NOT NULL,
  `done` ENUM('no', 'yes') NOT NULL,
  `interestedUsers` TEXT NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `upated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE `landLord` (
  `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT(11) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `mobile` VARCHAR(255) NOT NULL,
  `address` TEXT NOT NULL,
  `locality2` VARCHAR(255) NOT NULL,
  `state` VARCHAR(255) NOT NULL,
  `propertyToLet` ENUM('1 BHK', '2 BHK', '3 BHK', '4 BHK', 'Studio Apartment', 'House', 'Villa', 'Bungalow', 'Pent House', 'Shop', 'Land') NOT NULL,
  `floor` INT(100) NOT NULL,
  `furniture` ENUM('Furnished', 'Semi Furnished', 'Unfurnished') NOT NULL,
  `rent` DECIMAL(10,2) NOT NULL,
  `tenantType` ENUM('Family', 'Unmarried', 'Commercial') NOT NULL,
  `food` ENUM('Veg', 'Non-veg') NOT NULL,
  `images` TEXT NOT NULL,
  `status` ENUM('Pending', 'Published', 'Done') NOT NULL,
  `interestedUsers` TEXT NOT NULL,
  `done` ENUM('no', 'yes') NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE `tenant` (
  `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT(11) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `mobile` VARCHAR(255) NOT NULL,
  `locality2` VARCHAR(255) NOT NULL,
  `state` VARCHAR(255) NOT NULL,
  `typeOfProperty` ENUM('1 BHK', '2 BHK', '3 BHK', '4 BHK', 'Studio Apartment', 'House', 'Villa', 'Bungalow', 'Pent House', 'Shop', 'Land') NOT NULL,
  `typeOfTenant` ENUM('Family', 'Bachelors', 'Commercial') NOT NULL,
  `budget` DECIMAL(10,2) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

Edit the values in the config.php file to match your local or production environment:

<?php
return array(
  'APP_NAME' => 'Property Expert',
  'DB_HOST' => 'localhost',
  'DB_USER' => '', // Your database username
  'DB_PASS' => '', // Your database password
  'DB_NAME' => '', // Your database name
  'ADMIN_EMAIL' => '', // Admin email address
  'ADMIN_MOBILE' => '', // Admin mobile number
  'BUSINESS_ADDRESS' => '', // Business address
);
