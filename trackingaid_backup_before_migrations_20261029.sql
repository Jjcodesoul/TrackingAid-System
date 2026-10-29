-- MySQL dump 10.13  Distrib 8.0.45, for Win64 (x86_64)
--
-- Host: localhost    Database: laravel
-- ------------------------------------------------------
-- Server version	8.0.45

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `borrow_releases`
--

DROP TABLE IF EXISTS `borrow_releases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `borrow_releases` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `request_id` bigint unsigned NOT NULL,
  `inventory_id` bigint unsigned NOT NULL,
  `delivery_personnel_id` bigint unsigned DEFAULT NULL,
  `unit` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` int NOT NULL,
  `purpose` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `location` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `released_at` timestamp NOT NULL,
  `delivery_status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Loading',
  `dispatched_at` timestamp NULL DEFAULT NULL,
  `in_transit_at` timestamp NULL DEFAULT NULL,
  `arrived_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `processed_by_user_id` bigint unsigned DEFAULT NULL,
  `idempotency_key` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `borrow_releases_idempotency_key_unique` (`idempotency_key`),
  KEY `borrow_releases_request_id_foreign` (`request_id`),
  KEY `borrow_releases_inventory_id_foreign` (`inventory_id`),
  KEY `borrow_releases_delivery_personnel_id_foreign` (`delivery_personnel_id`),
  KEY `borrow_releases_processed_by_user_id_foreign` (`processed_by_user_id`),
  CONSTRAINT `borrow_releases_delivery_personnel_id_foreign` FOREIGN KEY (`delivery_personnel_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `borrow_releases_inventory_id_foreign` FOREIGN KEY (`inventory_id`) REFERENCES `inventory` (`id`) ON DELETE CASCADE,
  CONSTRAINT `borrow_releases_processed_by_user_id_foreign` FOREIGN KEY (`processed_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `borrow_releases_request_id_foreign` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `borrow_releases`
--

LOCK TABLES `borrow_releases` WRITE;
/*!40000 ALTER TABLE `borrow_releases` DISABLE KEYS */;
/*!40000 ALTER TABLE `borrow_releases` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventory`
--

DROP TABLE IF EXISTS `inventory`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `inventory` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `sku` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('Consumable','Returnable') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` int NOT NULL DEFAULT '0',
  `expiration` date DEFAULT NULL,
  `storage_location` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `inventory_sku_unique` (`sku`)
) ENGINE=InnoDB AUTO_INCREMENT=69 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory`
--

LOCK TABLES `inventory` WRITE;
/*!40000 ALTER TABLE `inventory` DISABLE KEYS */;
INSERT INTO `inventory` VALUES (1,'Rice (50kg)','FOOD','FOOD-RICE-SACK-50KG-ALL','Consumable',152,'2026-12-31','Warehouse A',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:16'),(2,'Medical Kit (Small)','MEDICAL','MEDICAL-MEDKIT-BOX-SM-ALL','Consumable',8,'2026-08-01','Warehouse B',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(3,'N95 Masks','MEDICAL','MEDICAL-N95MASK-PACK-REG-ALL','Consumable',1240,'2026-07-15','Warehouse B',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(4,'Life Vest (Medium)','RESCUE','RESCUE-LIFEVEST-PCS-MD-ADULT','Returnable',12,NULL,'Equipment Bay',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(5,'Instant Noodles','FOOD','FOOD-NOODLES-PACK-REG-ALL','Consumable',2340,'2026-07-20','Warehouse A',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(6,'Blankets','RELIEF','RELIEF-BLANKET-PCS-REG-ALL','Returnable',6,NULL,'Warehouse C',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(7,'Water (500ml)','FOOD','FOOD-WATER-BTL-500ML-ALL','Consumable',3600,'2026-07-10','Warehouse A',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(8,'Rescue Rope (Large)','RESCUE','RESCUE-ROPE-PCS-LG-ADULT','Returnable',24,NULL,'Equipment Bay',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(9,'Family Hygiene Kit','HYGIENE','HYGIENE-KIT-BAG-FAM-ALL','Consumable',85,NULL,'Warehouse B',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(10,'Bath Soap Bars','HYGIENE','HYGIENE-SOAP-PCS-REG-ALL','Consumable',620,'2028-02-28','Warehouse B',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(11,'Sanitary Pads','HYGIENE','HYGIENE-PADS-PACK-REG-WOMEN','Consumable',310,'2028-06-30','Warehouse B',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(12,'Hand Sanitizer (500ml)','HYGIENE','HYGIENE-SANITIZER-BTL-500ML-ALL','Consumable',180,'2027-11-30','Warehouse B',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(13,'Toothbrush Kits','HYGIENE','HYGIENE-TOOTHBRUSH-KIT-PCS-ALL','Consumable',260,NULL,'Warehouse B',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(14,'Reusable Face Masks','HYGIENE','HYGIENE-MASK-PACK-REG-ALL','Returnable',75,NULL,'Warehouse B',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(15,'Water Purification Tablets','HYGIENE','HYGIENE-PURIFICATION-TAB-BOX-ALL','Consumable',140,'2028-09-30','Warehouse B',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(16,'Family Relief Tent','SHELTER','SHELTER-TENT-UNIT-FAM-ALL','Returnable',18,NULL,'Warehouse C',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(17,'Tarpaulin Sheet (3x4m)','SHELTER','SHELTER-TARP-PCS-3X4M-ALL','Returnable',42,NULL,'Warehouse C',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(18,'Sleeping Mats','SHELTER','SHELTER-MAT-ROLL-REG-ALL','Returnable',65,NULL,'Warehouse C',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(19,'Mosquito Nets','SHELTER','SHELTER-MOSQUITO-NET-PCS-REG-ALL','Returnable',50,NULL,'Warehouse C',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(20,'Emergency Lanterns','SHELTER','SHELTER-LANTERN-PCS-LED-ALL','Returnable',32,NULL,'Equipment Bay',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(21,'Utility Rope (10m)','SHELTER','SHELTER-ROPE-COIL-10M-ALL','Returnable',28,NULL,'Equipment Bay',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(22,'Shelter Repair Toolkit','SHELTER','SHELTER-TOOLKIT-BOX-REG-ADULT','Returnable',14,NULL,'Equipment Bay',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(23,'Handheld Radios','COMMUNICATIONS','COMM-RADIO-PCS-VHF-ADULT','Returnable',16,NULL,'Equipment Bay',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(24,'Emergency Power Banks','COMMUNICATIONS','COMM-POWERBANK-PCS-10000MAH-ALL','Returnable',24,NULL,'Equipment Bay',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(25,'Megaphones','COMMUNICATIONS','COMM-MEGAPHONE-PCS-REG-ADULT','Returnable',10,NULL,'Equipment Bay',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(26,'Emergency Whistles','COMMUNICATIONS','COMM-WHISTLE-PCS-REG-ALL','Returnable',90,NULL,'Equipment Bay',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(27,'AA Batteries','COMMUNICATIONS','COMM-BATTERY-PACK-AA-ALL','Consumable',120,'2031-01-31','Warehouse C',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(28,'Portable Solar Chargers','COMMUNICATIONS','COMM-SOLAR-CHARGER-PCS-20W-ALL','Returnable',12,NULL,'Equipment Bay',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(29,'Shampoo (250ml)','HYGIENE','HYGIENE-SHAMPOO-BTL-250ML-ALL','Consumable',150,'2028-04-30','Warehouse B',NULL,'2026-09-28 19:02:16','2026-09-28 19:02:16'),(30,'Toothpaste (100g)','HYGIENE','HYGIENE-TOOTHPASTE-TUBE-100G-ALL','Consumable',175,'2028-05-31','Warehouse B',NULL,'2026-09-28 19:02:16','2026-09-28 19:02:16'),(31,'Wide-Tooth Combs','HYGIENE','HYGIENE-COMB-PCS-REG-ALL','Consumable',120,NULL,'Warehouse B',NULL,'2026-09-28 19:02:16','2026-09-28 19:02:16'),(32,'Nail Clippers','HYGIENE','HYGIENE-NAILCLIPPER-PCS-REG-ALL','Returnable',95,NULL,'Warehouse B',NULL,'2026-09-28 19:02:16','2026-09-28 19:02:16'),(33,'Wash Buckets (20L)','HYGIENE','HYGIENE-BUCKET-PCS-20L-ALL','Returnable',70,NULL,'Warehouse B',NULL,'2026-09-28 19:02:16','2026-09-28 19:02:16'),(34,'Washcloths','HYGIENE','HYGIENE-WASHCLOTH-PACK-REG-ALL','Consumable',110,NULL,'Warehouse B',NULL,'2026-09-28 19:02:16','2026-09-28 19:02:16'),(35,'Laundry Soap Bars','HYGIENE','HYGIENE-LAUNDRY-SOAP-PCS-REG-ALL','Consumable',240,'2029-01-31','Warehouse B',NULL,'2026-09-28 19:02:16','2026-09-28 19:02:16'),(36,'Disposable Diapers (Medium)','HYGIENE','HYGIENE-DIAPER-PACK-M-CHILD','Consumable',80,NULL,'Warehouse B',NULL,'2026-09-28 19:02:16','2026-09-28 19:02:16'),(37,'Disposable Diapers (Large)','HYGIENE','HYGIENE-DIAPER-PACK-L-CHILD','Consumable',75,NULL,'Warehouse B',NULL,'2026-09-28 19:02:16','2026-09-28 19:02:16'),(38,'Baby Wipes','HYGIENE','HYGIENE-BABYWIPES-PACK-ALL','Consumable',130,'2028-07-31','Warehouse B',NULL,'2026-09-28 19:02:16','2026-09-28 19:02:16'),(39,'Insect Repellent (100ml)','HYGIENE','HYGIENE-REPELLENT-BTL-100ML-ALL','Consumable',90,'2028-10-31','Warehouse B',NULL,'2026-09-28 19:02:16','2026-09-28 19:02:16'),(40,'Hair Brushes','HYGIENE','HYGIENE-HAIRBRUSH-PCS-REG-ALL','Consumable',100,NULL,'Warehouse B',NULL,'2026-09-28 19:02:16','2026-09-28 19:02:16'),(41,'Surface Disinfectant (1L)','HYGIENE','HYGIENE-DISINFECTANT-BTL-1L-ALL','Consumable',65,'2028-03-31','Warehouse B',NULL,'2026-09-28 19:02:16','2026-09-28 19:02:16'),(42,'Large Relief Tent','SHELTER','SHELTER-TENT-UNIT-6P-ALL','Returnable',10,NULL,'Warehouse C',NULL,'2026-09-28 19:02:16','2026-09-28 19:02:16'),(43,'Plastic Sheeting Roll','SHELTER','SHELTER-PLASTIC-SHEET-ROLL-ALL','Consumable',30,NULL,'Warehouse C',NULL,'2026-09-28 19:02:16','2026-09-28 19:02:16'),(44,'Sleeping Pillows','SHELTER','SHELTER-PILLOW-PCS-REG-ALL','Returnable',80,NULL,'Warehouse C',NULL,'2026-09-28 19:02:16','2026-09-28 19:02:16'),(45,'Bed Rolls','SHELTER','SHELTER-BEDROLL-PCS-REG-ALL','Returnable',55,NULL,'Warehouse C',NULL,'2026-09-28 19:02:16','2026-09-28 19:02:16'),(46,'Ground Sheets (2x3m)','SHELTER','SHELTER-GROUNDSHEET-PCS-2X3M-ALL','Returnable',45,NULL,'Warehouse C',NULL,'2026-09-28 19:02:16','2026-09-28 19:02:16'),(47,'Tent Stake Sets','SHELTER','SHELTER-TENTSTAKE-SET-ALL','Returnable',36,NULL,'Equipment Bay',NULL,'2026-09-28 19:02:16','2026-09-28 19:02:16'),(48,'Tent Hammers','SHELTER','SHELTER-HAMMER-PCS-REG-ADULT','Returnable',22,NULL,'Equipment Bay',NULL,'2026-09-28 19:02:16','2026-09-28 19:02:16'),(49,'Hand Axes','SHELTER','SHELTER-AXE-PCS-REG-ADULT','Returnable',12,NULL,'Equipment Bay',NULL,'2026-09-28 19:02:16','2026-09-28 19:02:16'),(50,'Utility Brooms','SHELTER','SHELTER-BROOM-PCS-REG-ALL','Returnable',28,NULL,'Warehouse C',NULL,'2026-09-28 19:02:16','2026-09-28 19:02:16'),(51,'Water Containers (20L)','SHELTER','SHELTER-WATERCONTAINER-PCS-20L-ALL','Returnable',60,NULL,'Warehouse C',NULL,'2026-09-28 19:02:16','2026-09-28 19:02:16'),(52,'Canvas Repair Kits','SHELTER','SHELTER-CANVAS-REPAIR-KIT-BOX-ADULT','Consumable',18,NULL,'Equipment Bay',NULL,'2026-09-28 19:02:16','2026-09-28 19:02:16'),(53,'Raincoats','SHELTER','SHELTER-RAINCOAT-PCS-REG-ALL','Returnable',48,NULL,'Warehouse C',NULL,'2026-09-28 19:02:16','2026-09-28 19:02:16'),(54,'Base Radios','COMMUNICATIONS','COMM-BASE-RADIO-PCS-VHF-ADULT','Returnable',6,NULL,'Equipment Bay',NULL,'2026-09-28 19:02:16','2026-09-28 19:02:16'),(55,'Headlamps','COMMUNICATIONS','COMM-HEADLAMP-PCS-LED-ADULT','Returnable',35,NULL,'Equipment Bay',NULL,'2026-09-28 19:02:16','2026-09-28 19:02:16'),(56,'LED Torches','COMMUNICATIONS','COMM-TORCH-PCS-LED-ALL','Returnable',42,NULL,'Equipment Bay',NULL,'2026-09-28 19:02:16','2026-09-28 19:02:16'),(57,'AAA Batteries','COMMUNICATIONS','COMM-BATTERY-PACK-AAA-ALL','Consumable',85,'2031-02-28','Warehouse C',NULL,'2026-09-28 19:02:16','2026-09-28 19:02:16'),(58,'9V Batteries','COMMUNICATIONS','COMM-BATTERY-PCS-9V-ALL','Consumable',40,'2031-03-31','Warehouse C',NULL,'2026-09-28 19:02:16','2026-09-28 19:02:16'),(59,'Charging Cable Sets','COMMUNICATIONS','COMM-CHARGING-CABLE-PACK-ALL','Consumable',30,NULL,'Warehouse C',NULL,'2026-09-28 19:02:16','2026-09-28 19:02:16'),(60,'Power Strips (6-port)','COMMUNICATIONS','COMM-POWERSTRIP-PCS-6PORT-ALL','Returnable',14,NULL,'Equipment Bay',NULL,'2026-09-28 19:02:16','2026-09-28 19:02:16'),(61,'Emergency SIM Cards','COMMUNICATIONS','COMM-SIMCARD-PACK-4G-ALL','Consumable',25,NULL,'Warehouse C',NULL,'2026-09-28 19:02:16','2026-09-28 19:02:16'),(62,'Radio Antennas','COMMUNICATIONS','COMM-ANTENNA-PCS-VHF-ADULT','Returnable',15,NULL,'Equipment Bay',NULL,'2026-09-28 19:02:16','2026-09-28 19:02:16'),(63,'Portable Sirens','COMMUNICATIONS','COMM-SIREN-PCS-12V-ADULT','Returnable',8,NULL,'Equipment Bay',NULL,'2026-09-28 19:02:16','2026-09-28 19:02:16'),(64,'Memory Cards (64GB)','COMMUNICATIONS','COMM-MEMORYCARD-PCS-64GB-ALL','Returnable',20,NULL,'Equipment Bay',NULL,'2026-09-28 19:02:16','2026-09-28 19:02:16'),(65,'Solar Lanterns','COMMUNICATIONS','COMM-SOLAR-LANTERN-PCS-LED-ALL','Returnable',26,NULL,'Equipment Bay',NULL,'2026-09-28 19:02:16','2026-09-28 19:02:16'),(66,'USB Charging Hubs','COMMUNICATIONS','COMM-USB-HUB-PCS-4PORT-ALL','Returnable',12,NULL,'Equipment Bay',NULL,'2026-09-28 19:02:16','2026-09-28 19:02:16'),(67,'Extension Cables (10m)','COMMUNICATIONS','COMM-EXTENSION-CABLE-PCS-10M-ALL','Returnable',18,NULL,'Equipment Bay',NULL,'2026-09-28 19:02:16','2026-09-28 19:02:16'),(68,'Rechargeable Bullhorns','SHELTER','SHELTER-BULLHORN-PCS-REG-ADULT','Returnable',9,NULL,'Equipment Bay',NULL,'2026-09-28 19:02:16','2026-09-28 19:02:16');
/*!40000 ALTER TABLE `inventory` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `items`
--

DROP TABLE IF EXISTS `items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `unit_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `size_weight` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `target_beneficiary` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `variant` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` enum('consumable','returnable') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `storage_location` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expiration_date` date DEFAULT NULL,
  `sku` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `items_sku_unique` (`sku`)
) ENGINE=InnoDB AUTO_INCREMENT=69 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `items`
--

LOCK TABLES `items` WRITE;
/*!40000 ALTER TABLE `items` DISABLE KEYS */;
INSERT INTO `items` VALUES (1,'Rice (50kg)','FOOD','Sack','50KG','ALL','NONE','consumable','Warehouse A','2026-12-31','FOOD-RICE-SACK-50KG-ALL',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(2,'Medical Kit (Small)','MEDICAL','Box','SM','ALL','NONE','consumable','Warehouse B','2026-08-01','MEDICAL-MEDKIT-BOX-SM-ALL',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(3,'N95 Masks','MEDICAL','Pack','REG','ALL','NONE','consumable','Warehouse B','2026-07-15','MEDICAL-N95MASK-PACK-REG-ALL',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(4,'Life Vest (Medium)','RESCUE','PCS','MD','ADULT','NONE','returnable','Equipment Bay',NULL,'RESCUE-LIFEVEST-PCS-MD-ADULT',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(5,'Instant Noodles','FOOD','Pack','REG','ALL','NONE','consumable','Warehouse A','2026-07-20','FOOD-NOODLES-PACK-REG-ALL',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(6,'Blankets','RELIEF','PCS','REG','ALL','NONE','returnable','Warehouse C',NULL,'RELIEF-BLANKET-PCS-REG-ALL',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(7,'Water (500ml)','FOOD','Bottle','500ML','ALL','NONE','consumable','Warehouse A','2026-07-10','FOOD-WATER-BTL-500ML-ALL',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(8,'Rescue Rope (Large)','RESCUE','PCS','LG','ADULT','NONE','returnable','Equipment Bay',NULL,'RESCUE-ROPE-PCS-LG-ADULT',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(9,'Family Hygiene Kit','HYGIENE','Bag','FAM','ALL','STANDARD','consumable','Warehouse B',NULL,'HYGIENE-KIT-BAG-FAM-ALL',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(10,'Bath Soap Bars','HYGIENE','PCS','REG','ALL','UNSCENTED','consumable','Warehouse B','2028-02-28','HYGIENE-SOAP-PCS-REG-ALL',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(11,'Sanitary Pads','HYGIENE','Pack','REG','WOMEN','REGULAR','consumable','Warehouse B','2028-06-30','HYGIENE-PADS-PACK-REG-WOMEN',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(12,'Hand Sanitizer (500ml)','HYGIENE','Bottle','500ML','ALL','70PCT','consumable','Warehouse B','2027-11-30','HYGIENE-SANITIZER-BTL-500ML-ALL',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(13,'Toothbrush Kits','HYGIENE','PCS','REG','ALL','STANDARD','consumable','Warehouse B',NULL,'HYGIENE-TOOTHBRUSH-KIT-PCS-ALL',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(14,'Reusable Face Masks','HYGIENE','Pack','REG','ALL','WASHABLE','returnable','Warehouse B',NULL,'HYGIENE-MASK-PACK-REG-ALL',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(15,'Water Purification Tablets','HYGIENE','Box','50TAB','ALL','CHLORINE','consumable','Warehouse B','2028-09-30','HYGIENE-PURIFICATION-TAB-BOX-ALL',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(16,'Family Relief Tent','SHELTER','Unit','FAM','ALL','4PERSON','returnable','Warehouse C',NULL,'SHELTER-TENT-UNIT-FAM-ALL',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(17,'Tarpaulin Sheet (3x4m)','SHELTER','PCS','3X4M','ALL','BLUE','returnable','Warehouse C',NULL,'SHELTER-TARP-PCS-3X4M-ALL',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(18,'Sleeping Mats','SHELTER','PCS','REG','ALL','FOAM','returnable','Warehouse C',NULL,'SHELTER-MAT-ROLL-REG-ALL',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(19,'Mosquito Nets','SHELTER','PCS','REG','ALL','HANGING','returnable','Warehouse C',NULL,'SHELTER-MOSQUITO-NET-PCS-REG-ALL',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(20,'Emergency Lanterns','SHELTER','PCS','LED','ALL','RECHARGEABLE','returnable','Equipment Bay',NULL,'SHELTER-LANTERN-PCS-LED-ALL',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(21,'Utility Rope (10m)','SHELTER','Coil','10M','ALL','NYLON','returnable','Equipment Bay',NULL,'SHELTER-ROPE-COIL-10M-ALL',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(22,'Shelter Repair Toolkit','SHELTER','Box','REG','ADULT','STANDARD','returnable','Equipment Bay',NULL,'SHELTER-TOOLKIT-BOX-REG-ADULT',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(23,'Handheld Radios','COMMUNICATIONS','PCS','VHF','ADULT','TWO-WAY','returnable','Equipment Bay',NULL,'COMM-RADIO-PCS-VHF-ADULT',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(24,'Emergency Power Banks','COMMUNICATIONS','PCS','10000MAH','ALL','USB-C','returnable','Equipment Bay',NULL,'COMM-POWERBANK-PCS-10000MAH-ALL',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(25,'Megaphones','COMMUNICATIONS','PCS','REG','ADULT','BATTERY','returnable','Equipment Bay',NULL,'COMM-MEGAPHONE-PCS-REG-ADULT',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(26,'Emergency Whistles','COMMUNICATIONS','PCS','REG','ALL','PEALESS','returnable','Equipment Bay',NULL,'COMM-WHISTLE-PCS-REG-ALL',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(27,'AA Batteries','COMMUNICATIONS','Pack','AA','ALL','ALKALINE','consumable','Warehouse C','2031-01-31','COMM-BATTERY-PACK-AA-ALL',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(28,'Portable Solar Chargers','COMMUNICATIONS','PCS','20W','ALL','FOLDABLE','returnable','Equipment Bay',NULL,'COMM-SOLAR-CHARGER-PCS-20W-ALL',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(29,'Shampoo (250ml)','HYGIENE','Bottle','250ML','ALL','MILD','consumable','Warehouse B','2028-04-30','HYGIENE-SHAMPOO-BTL-250ML-ALL',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(30,'Toothpaste (100g)','HYGIENE','Tube','100G','ALL','FLUORIDE','consumable','Warehouse B','2028-05-31','HYGIENE-TOOTHPASTE-TUBE-100G-ALL',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(31,'Wide-Tooth Combs','HYGIENE','PCS','REG','ALL','PLASTIC','consumable','Warehouse B',NULL,'HYGIENE-COMB-PCS-REG-ALL',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(32,'Nail Clippers','HYGIENE','PCS','REG','ALL','STEEL','returnable','Warehouse B',NULL,'HYGIENE-NAILCLIPPER-PCS-REG-ALL',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(33,'Wash Buckets (20L)','HYGIENE','PCS','20L','ALL','PLASTIC','returnable','Warehouse B',NULL,'HYGIENE-BUCKET-PCS-20L-ALL',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(34,'Washcloths','HYGIENE','Pack','REG','ALL','COTTON','consumable','Warehouse B',NULL,'HYGIENE-WASHCLOTH-PACK-REG-ALL',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(35,'Laundry Soap Bars','HYGIENE','PCS','REG','ALL','BAR','consumable','Warehouse B','2029-01-31','HYGIENE-LAUNDRY-SOAP-PCS-REG-ALL',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(36,'Disposable Diapers (Medium)','HYGIENE','Pack','M','CHILD','DAYTIME','consumable','Warehouse B',NULL,'HYGIENE-DIAPER-PACK-M-CHILD',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(37,'Disposable Diapers (Large)','HYGIENE','Pack','L','CHILD','DAYTIME','consumable','Warehouse B',NULL,'HYGIENE-DIAPER-PACK-L-CHILD',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(38,'Baby Wipes','HYGIENE','Pack','80CT','CHILD','UNSCENTED','consumable','Warehouse B','2028-07-31','HYGIENE-BABYWIPES-PACK-ALL',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(39,'Insect Repellent (100ml)','HYGIENE','Bottle','100ML','ALL','SPRAY','consumable','Warehouse B','2028-10-31','HYGIENE-REPELLENT-BTL-100ML-ALL',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(40,'Hair Brushes','HYGIENE','PCS','REG','ALL','PLASTIC','consumable','Warehouse B',NULL,'HYGIENE-HAIRBRUSH-PCS-REG-ALL',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(41,'Surface Disinfectant (1L)','HYGIENE','Bottle','1L','ALL','LIQUID','consumable','Warehouse B','2028-03-31','HYGIENE-DISINFECTANT-BTL-1L-ALL',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(42,'Large Relief Tent','SHELTER','Unit','6P','ALL','FAMILY','returnable','Warehouse C',NULL,'SHELTER-TENT-UNIT-6P-ALL',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(43,'Plastic Sheeting Roll','SHELTER','Roll','4X25M','ALL','CLEAR','consumable','Warehouse C',NULL,'SHELTER-PLASTIC-SHEET-ROLL-ALL',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(44,'Sleeping Pillows','SHELTER','PCS','REG','ALL','FOAM','returnable','Warehouse C',NULL,'SHELTER-PILLOW-PCS-REG-ALL',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(45,'Bed Rolls','SHELTER','PCS','REG','ALL','COTTON','returnable','Warehouse C',NULL,'SHELTER-BEDROLL-PCS-REG-ALL',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(46,'Ground Sheets (2x3m)','SHELTER','PCS','2X3M','ALL','WATERPROOF','returnable','Warehouse C',NULL,'SHELTER-GROUNDSHEET-PCS-2X3M-ALL',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(47,'Tent Stake Sets','SHELTER','Set','12PCS','ALL','STEEL','returnable','Equipment Bay',NULL,'SHELTER-TENTSTAKE-SET-ALL',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(48,'Tent Hammers','SHELTER','PCS','REG','ADULT','CLAW','returnable','Equipment Bay',NULL,'SHELTER-HAMMER-PCS-REG-ADULT',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(49,'Hand Axes','SHELTER','PCS','REG','ADULT','CAMP','returnable','Equipment Bay',NULL,'SHELTER-AXE-PCS-REG-ADULT',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(50,'Utility Brooms','SHELTER','PCS','REG','ALL','OUTDOOR','returnable','Warehouse C',NULL,'SHELTER-BROOM-PCS-REG-ALL',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(51,'Water Containers (20L)','SHELTER','PCS','20L','ALL','STACKABLE','returnable','Warehouse C',NULL,'SHELTER-WATERCONTAINER-PCS-20L-ALL',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(52,'Canvas Repair Kits','SHELTER','Box','REG','ADULT','PATCH','consumable','Equipment Bay',NULL,'SHELTER-CANVAS-REPAIR-KIT-BOX-ADULT',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(53,'Raincoats','SHELTER','PCS','REG','ALL','REUSABLE','returnable','Warehouse C',NULL,'SHELTER-RAINCOAT-PCS-REG-ALL',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(54,'Base Radios','COMMUNICATIONS','PCS','VHF','ADULT','DESKTOP','returnable','Equipment Bay',NULL,'COMM-BASE-RADIO-PCS-VHF-ADULT',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(55,'Headlamps','COMMUNICATIONS','PCS','LED','ADULT','USB','returnable','Equipment Bay',NULL,'COMM-HEADLAMP-PCS-LED-ADULT',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(56,'LED Torches','COMMUNICATIONS','PCS','LED','ALL','HANDHELD','returnable','Equipment Bay',NULL,'COMM-TORCH-PCS-LED-ALL',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(57,'AAA Batteries','COMMUNICATIONS','Pack','AAA','ALL','ALKALINE','consumable','Warehouse C','2031-02-28','COMM-BATTERY-PACK-AAA-ALL',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(58,'9V Batteries','COMMUNICATIONS','PCS','9V','ALL','ALKALINE','consumable','Warehouse C','2031-03-31','COMM-BATTERY-PCS-9V-ALL',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(59,'Charging Cable Sets','COMMUNICATIONS','Pack','3IN1','ALL','USB','consumable','Warehouse C',NULL,'COMM-CHARGING-CABLE-PACK-ALL',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(60,'Power Strips (6-port)','COMMUNICATIONS','PCS','6PORT','ALL','SURGE','returnable','Equipment Bay',NULL,'COMM-POWERSTRIP-PCS-6PORT-ALL',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(61,'Emergency SIM Cards','COMMUNICATIONS','Pack','4G','ALL','PREPAID','consumable','Warehouse C',NULL,'COMM-SIMCARD-PACK-4G-ALL',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(62,'Radio Antennas','COMMUNICATIONS','PCS','VHF','ADULT','FLEX','returnable','Equipment Bay',NULL,'COMM-ANTENNA-PCS-VHF-ADULT',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(63,'Portable Sirens','COMMUNICATIONS','PCS','12V','ADULT','ALARM','returnable','Equipment Bay',NULL,'COMM-SIREN-PCS-12V-ADULT',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(64,'Memory Cards (64GB)','COMMUNICATIONS','PCS','64GB','ALL','MICROSD','returnable','Equipment Bay',NULL,'COMM-MEMORYCARD-PCS-64GB-ALL',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(65,'Solar Lanterns','COMMUNICATIONS','PCS','LED','ALL','SOLAR','returnable','Equipment Bay',NULL,'COMM-SOLAR-LANTERN-PCS-LED-ALL',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(66,'USB Charging Hubs','COMMUNICATIONS','PCS','4PORT','ALL','USB-C','returnable','Equipment Bay',NULL,'COMM-USB-HUB-PCS-4PORT-ALL',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(67,'Extension Cables (10m)','COMMUNICATIONS','PCS','10M','ALL','OUTDOOR','returnable','Equipment Bay',NULL,'COMM-EXTENSION-CABLE-PCS-10M-ALL',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(68,'Rechargeable Bullhorns','SHELTER','PCS','REG','ADULT','RECHARGEABLE','returnable','Equipment Bay',NULL,'SHELTER-BULLHORN-PCS-REG-ADULT',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15');
/*!40000 ALTER TABLE `items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` smallint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_05_05_112646_create_items_table',1),(5,'2026_05_05_112700_create_stock_batches_table',1),(6,'2026_05_11_001902_create_inventory_table',1),(7,'2026_05_11_100544_create_requests_table',1),(8,'2026_05_11_100705_create_borrow_releases_table',1),(9,'2026_05_11_100821_create_returns_table',1),(10,'2026_05_11_131930_add_processing_fields_to_requests_table',1),(11,'2026_05_15_172432_add_role_to_users_table',1),(12,'2026_06_13_101646_add_expiration_date_to_items_table',1),(13,'2026_08_17_155631_create_personal_access_tokens_table',1),(14,'2026_08_26_163115_add_must_change_password_to_users_table',1),(15,'2026_09_07_162636_add_delivery_status_to_borrow_releases_table',1),(16,'2026_09_29_030446_create_resqoperation_forwarded_requests_table',2),(17,'2026_10_08_143548_add_delivery_personnel_id_to_borrow_releases_table',3),(18,'2026_10_10_150000_create_report_requests_table',3),(19,'2026_10_11_055406_repair_missing_resqperation_forwarded_requests_table',3),(20,'2026_10_13_020000_add_external_api_fields_to_report_requests_table',3),(21,'2026_10_17_044247_add_missing_fields_to_report_requests_table',4),(22,'2026_10_17_120000_add_release_tracking_fields',4),(23,'2026_10_18_090000_add_request_approval_and_return_links',4);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `report_requests`
--

DROP TABLE IF EXISTS `report_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `report_requests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `report_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'inventory',
  `member_id` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `requested_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `system_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'queued',
  `format` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `filters` json DEFAULT NULL,
  `file_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `report_requests`
--

LOCK TABLES `report_requests` WRITE;
/*!40000 ALTER TABLE `report_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `report_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `requests`
--

DROP TABLE IF EXISTS `requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `requests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `request_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `inventory_id` bigint unsigned NOT NULL,
  `source` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ResQOperation',
  `quantity` int NOT NULL,
  `approved_quantity` int unsigned DEFAULT NULL,
  `priority` enum('Low','Medium','High') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('Pending','Approved','Partially Released','Released','Rejected','Cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pending',
  `purpose` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `responder_email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notification_status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approval_notes` text COLLATE utf8mb4_unicode_ci,
  `approved_at` timestamp NULL DEFAULT NULL,
  `approved_by_user_id` bigint unsigned DEFAULT NULL,
  `rejected_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `requests_request_code_unique` (`request_code`),
  KEY `requests_inventory_id_foreign` (`inventory_id`),
  KEY `requests_approved_by_user_id_foreign` (`approved_by_user_id`),
  CONSTRAINT `requests_approved_by_user_id_foreign` FOREIGN KEY (`approved_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `requests_inventory_id_foreign` FOREIGN KEY (`inventory_id`) REFERENCES `inventory` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `requests`
--

LOCK TABLES `requests` WRITE;
/*!40000 ALTER TABLE `requests` DISABLE KEYS */;
INSERT INTO `requests` VALUES (1,'REQ-2024-091',2,'Typhoon Carina Relief',200,NULL,'High','Pending','Typhoon Carina Relief','responder1@resqoperation.org',NULL,NULL,NULL,NULL,NULL,'2026-05-26 00:00:00','2026-09-28 19:02:15'),(2,'REQ-2024-090',1,'Flood Ops - Pampanga',150,NULL,'High','Pending','Flood Ops - Pampanga','responder2@resqoperation.org',NULL,NULL,NULL,NULL,NULL,'2026-05-25 00:00:00','2026-09-28 19:02:15'),(3,'REQ-2024-089',6,'Coastal Rescue - Batangas',60,NULL,'High','Approved','Coastal Rescue - Batangas','responder3@resqoperation.org','Responder notified: request Approved',NULL,'2026-09-28 19:02:15',NULL,NULL,'2026-05-24 00:00:00','2026-09-28 19:02:15'),(4,'REQ-2024-088',5,'Barangay Poblacion',500,NULL,'Medium','Rejected','Barangay Poblacion','responder4@resqoperation.org','Responder notified: request Rejected',NULL,NULL,NULL,'2026-09-28 19:02:15','2026-05-23 00:00:00','2026-09-28 19:02:15'),(5,'REQ-2024-087',2,'Community Health Center',30,NULL,'High','Rejected','Community Health Center','responder5@resqoperation.org','Responder notified: request rejected',NULL,NULL,NULL,'2026-09-29 03:34:39','2026-05-23 00:00:00','2026-09-29 03:34:39'),(6,'REQ-2024-086',6,'DSWD Shelter - Cavite',100,NULL,'Low','Approved','DSWD Shelter - Cavite','responder6@resqoperation.org','Responder notified: request Approved',NULL,'2026-09-28 19:02:15',NULL,NULL,'2026-05-22 00:00:00','2026-09-28 19:02:15');
/*!40000 ALTER TABLE `requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `resqoperation_forwarded_requests`
--

DROP TABLE IF EXISTS `resqoperation_forwarded_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `resqoperation_forwarded_requests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tracking_reference` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `resqoperation_request_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `source_reference` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `request_source` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ResQOperation',
  `source_system` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `request_category` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `resource_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `item_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` int unsigned NOT NULL DEFAULT '1',
  `unit` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `urgency` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Medium',
  `area_label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `area_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `resqoperation_forwarded_requests_tracking_reference_index` (`tracking_reference`),
  KEY `resqoperation_forwarded_requests_resqoperation_request_id_index` (`resqoperation_request_id`),
  KEY `resqoperation_forwarded_requests_status_index` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `resqoperation_forwarded_requests`
--

LOCK TABLES `resqoperation_forwarded_requests` WRITE;
/*!40000 ALTER TABLE `resqoperation_forwarded_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `resqoperation_forwarded_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `resqperation_forwarded_requests`
--

DROP TABLE IF EXISTS `resqperation_forwarded_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `resqperation_forwarded_requests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tracking_reference` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `resqperation_request_id` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `source_reference` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `request_source` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `source_system` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'RESQPERATION',
  `request_category` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `resource_type` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `item_name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `unit` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `urgency` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `area_label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `area_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `requested_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `validation_notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `validated_by_user_id` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `forwarded_by_user_id` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `forwarded_by_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `forwarded_by_role` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `resqperation_status` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'forwarded',
  `payload_json` json DEFAULT NULL,
  `forwarded_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `resqperation_forwarded_requests_tracking_reference_unique` (`tracking_reference`),
  KEY `resqperation_forwarded_requests_resqperation_request_id_index` (`resqperation_request_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `resqperation_forwarded_requests`
--

LOCK TABLES `resqperation_forwarded_requests` WRITE;
/*!40000 ALTER TABLE `resqperation_forwarded_requests` DISABLE KEYS */;
INSERT INTO `resqperation_forwarded_requests` VALUES (1,'TA-20260929-005','RR-2026-MR1WMD','EVT-20260827-RTR1DM','hq_desk','ResQperation','resource','medical',NULL,50,'kits','Medium','Area not recorded','Naay bagyo','PN','Naay bagyo','Verified request forwarded to TrackingAid.','USR-HQ-SUPERADMIN-1789925937','USR-HQ-SUPERADMIN-1789925937','Super Admin','super_admin','approved','{\"unit\": \"kits\", \"urgency\": \"Medium\", \"quantity\": 50, \"area_note\": \"Naay bagyo\", \"item_name\": null, \"area_label\": \"Area not recorded\", \"updated_at\": \"2026-09-29 19:14:21\", \"description\": \"Naay bagyo\", \"forwarded_at\": \"2026-09-29 19:14:21\", \"requested_by\": \"PN\", \"resource_type\": \"medical\", \"source_system\": \"ResQperation\", \"request_source\": \"hq_desk\", \"request_category\": \"resource\", \"source_reference\": \"EVT-20260827-RTR1DM\", \"validation_notes\": \"Verified request forwarded to TrackingAid.\", \"forwarded_by_name\": \"Super Admin\", \"forwarded_by_role\": \"super_admin\", \"tracking_reference\": \"TA-20260929-005\", \"resqperation_status\": \"forwarded\", \"forwarded_by_user_id\": \"USR-HQ-SUPERADMIN-1789925937\", \"validated_by_user_id\": \"USR-HQ-SUPERADMIN-1789925937\", \"resqperation_request_id\": \"RR-2026-MR1WMD\"}','2026-09-29 11:14:21','2026-09-29 11:14:21','2026-09-29 03:42:43'),(2,'TA-20260929-006','RR-2026-KF35FP','EVT-20260827-RTR1DM','hq_desk','ResQperation','resource','FOOD',NULL,20,'FOOD','Medium','Area not recorded','TESTING','PN','TESTING','Verified request forwarded to TrackingAid.','USR-HQ-SUPERADMIN-1789925937','USR-HQ-SUPERADMIN-1789925937','Super Admin','super_admin','rejected','{\"unit\": \"FOOD\", \"urgency\": \"Medium\", \"quantity\": 20, \"area_note\": \"TESTING\", \"item_name\": null, \"area_label\": \"Area not recorded\", \"updated_at\": \"2026-09-29 22:08:47\", \"description\": \"TESTING\", \"forwarded_at\": \"2026-09-29 22:08:47\", \"requested_by\": \"PN\", \"resource_type\": \"FOOD\", \"source_system\": \"ResQperation\", \"request_source\": \"hq_desk\", \"request_category\": \"resource\", \"source_reference\": \"EVT-20260827-RTR1DM\", \"validation_notes\": \"Verified request forwarded to TrackingAid.\", \"forwarded_by_name\": \"Super Admin\", \"forwarded_by_role\": \"super_admin\", \"tracking_reference\": \"TA-20260929-006\", \"resqperation_status\": \"forwarded\", \"forwarded_by_user_id\": \"USR-HQ-SUPERADMIN-1789925937\", \"validated_by_user_id\": \"USR-HQ-SUPERADMIN-1789925937\", \"resqperation_request_id\": \"RR-2026-KF35FP\"}','2026-09-29 14:08:47','2026-09-29 14:08:47','2026-10-18 02:52:16');
/*!40000 ALTER TABLE `resqperation_forwarded_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `returns`
--

DROP TABLE IF EXISTS `returns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `returns` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `inventory_id` bigint unsigned NOT NULL,
  `borrow_release_id` bigint unsigned DEFAULT NULL,
  `processed_by_user_id` bigint unsigned DEFAULT NULL,
  `quantity` int NOT NULL,
  `condition` enum('Good','Damaged','Missing') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `idempotency_key` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `returns_idempotency_key_unique` (`idempotency_key`),
  KEY `returns_inventory_id_foreign` (`inventory_id`),
  KEY `returns_borrow_release_id_foreign` (`borrow_release_id`),
  KEY `returns_processed_by_user_id_foreign` (`processed_by_user_id`),
  CONSTRAINT `returns_borrow_release_id_foreign` FOREIGN KEY (`borrow_release_id`) REFERENCES `borrow_releases` (`id`) ON DELETE SET NULL,
  CONSTRAINT `returns_inventory_id_foreign` FOREIGN KEY (`inventory_id`) REFERENCES `inventory` (`id`) ON DELETE CASCADE,
  CONSTRAINT `returns_processed_by_user_id_foreign` FOREIGN KEY (`processed_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `returns`
--

LOCK TABLES `returns` WRITE;
/*!40000 ALTER TABLE `returns` DISABLE KEYS */;
/*!40000 ALTER TABLE `returns` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('Jm9XIgcPLVmuESFZc0h0IlruakLPV9G3q38bstuR',2,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36','eyJfdG9rZW4iOiI4SjBObmRIbVpKZTRISkhTVEZJNWVIVjJabnc3QU5KZE9sRUVnQjdHIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9pbnZlbnRvcnkiLCJyb3V0ZSI6ImludmVudG9yeS5pbmRleCJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX0sImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjoyfQ==',1793275212);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_batches`
--

DROP TABLE IF EXISTS `stock_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `stock_batches` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `item_id` bigint unsigned NOT NULL,
  `quantity` int NOT NULL,
  `supplier` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_received` date NOT NULL,
  `expiration_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `stock_batches_item_id_foreign` (`item_id`),
  CONSTRAINT `stock_batches_item_id_foreign` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=69 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_batches`
--

LOCK TABLES `stock_batches` WRITE;
/*!40000 ALTER TABLE `stock_batches` DISABLE KEYS */;
INSERT INTO `stock_batches` VALUES (1,1,152,'DSWD','2026-01-10','2026-12-31','2026-09-28 19:02:15','2026-09-28 19:02:15'),(2,2,8,'Red Cross','2026-02-15','2026-08-01','2026-09-28 19:02:15','2026-09-28 19:02:15'),(3,3,1240,'DOH','2026-01-20','2026-07-15','2026-09-28 19:02:15','2026-09-28 19:02:15'),(4,4,12,'NDRRMC','2026-03-01',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(5,5,2340,'DSWD','2026-02-01','2026-07-20','2026-09-28 19:02:15','2026-09-28 19:02:15'),(6,6,6,'Red Cross','2026-01-05',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(7,7,3600,'LGU Cebu','2026-03-10','2026-07-10','2026-09-28 19:02:15','2026-09-28 19:02:15'),(8,8,24,'NDRRMC','2026-02-20',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(9,9,85,'DSWD','2026-04-05',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(10,10,620,'UNICEF','2026-04-10','2028-02-28','2026-09-28 19:02:15','2026-09-28 19:02:15'),(11,11,310,'UNFPA','2026-04-12','2028-06-30','2026-09-28 19:02:15','2026-09-28 19:02:15'),(12,12,180,'DOH','2026-04-15','2027-11-30','2026-09-28 19:02:15','2026-09-28 19:02:15'),(13,13,260,'UNICEF','2026-04-18',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(14,14,75,'DOH','2026-04-20',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(15,15,140,'Red Cross','2026-04-22','2028-09-30','2026-09-28 19:02:15','2026-09-28 19:02:15'),(16,16,18,'NDRRMC','2026-04-25',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(17,17,42,'LGU Cebu','2026-04-27',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(18,18,65,'Red Cross','2026-04-29',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(19,19,50,'DSWD','2026-05-01',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(20,20,32,'NDRRMC','2026-05-03',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(21,21,28,'NDRRMC','2026-05-05',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(22,22,14,'LGU Cebu','2026-05-07',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(23,23,16,'NDRRMC','2026-05-10',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(24,24,24,'Red Cross','2026-05-12',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(25,25,10,'LGU Cebu','2026-05-14',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(26,26,90,'NDRRMC','2026-05-16',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(27,27,120,'DOH','2026-05-18','2031-01-31','2026-09-28 19:02:15','2026-09-28 19:02:15'),(28,28,12,'Red Cross','2026-05-20',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(29,29,150,'UNICEF','2026-05-22','2028-04-30','2026-09-28 19:02:15','2026-09-28 19:02:15'),(30,30,175,'UNICEF','2026-05-24','2028-05-31','2026-09-28 19:02:15','2026-09-28 19:02:15'),(31,31,120,'DSWD','2026-05-26',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(32,32,95,'Red Cross','2026-05-28',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(33,33,70,'LGU Cebu','2026-05-30',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(34,34,110,'DSWD','2026-06-01',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15'),(35,35,240,'DSWD','2026-06-03','2029-01-31','2026-09-28 19:02:15','2026-09-28 19:02:15'),(36,36,80,'UNICEF','2026-06-05',NULL,'2026-09-28 19:02:16','2026-09-28 19:02:16'),(37,37,75,'UNICEF','2026-06-07',NULL,'2026-09-28 19:02:16','2026-09-28 19:02:16'),(38,38,130,'UNICEF','2026-06-09','2028-07-31','2026-09-28 19:02:16','2026-09-28 19:02:16'),(39,39,90,'DOH','2026-06-11','2028-10-31','2026-09-28 19:02:16','2026-09-28 19:02:16'),(40,40,100,'DSWD','2026-06-13',NULL,'2026-09-28 19:02:16','2026-09-28 19:02:16'),(41,41,65,'DOH','2026-06-15','2028-03-31','2026-09-28 19:02:16','2026-09-28 19:02:16'),(42,42,10,'NDRRMC','2026-06-17',NULL,'2026-09-28 19:02:16','2026-09-28 19:02:16'),(43,43,30,'LGU Cebu','2026-06-19',NULL,'2026-09-28 19:02:16','2026-09-28 19:02:16'),(44,44,80,'Red Cross','2026-06-21',NULL,'2026-09-28 19:02:16','2026-09-28 19:02:16'),(45,45,55,'DSWD','2026-06-23',NULL,'2026-09-28 19:02:16','2026-09-28 19:02:16'),(46,46,45,'NDRRMC','2026-06-25',NULL,'2026-09-28 19:02:16','2026-09-28 19:02:16'),(47,47,36,'NDRRMC','2026-06-27',NULL,'2026-09-28 19:02:16','2026-09-28 19:02:16'),(48,48,22,'LGU Cebu','2026-06-29',NULL,'2026-09-28 19:02:16','2026-09-28 19:02:16'),(49,49,12,'NDRRMC','2026-07-01',NULL,'2026-09-28 19:02:16','2026-09-28 19:02:16'),(50,50,28,'DSWD','2026-07-03',NULL,'2026-09-28 19:02:16','2026-09-28 19:02:16'),(51,51,60,'LGU Cebu','2026-07-05',NULL,'2026-09-28 19:02:16','2026-09-28 19:02:16'),(52,52,18,'Red Cross','2026-07-07',NULL,'2026-09-28 19:02:16','2026-09-28 19:02:16'),(53,53,48,'DSWD','2026-07-09',NULL,'2026-09-28 19:02:16','2026-09-28 19:02:16'),(54,54,6,'NDRRMC','2026-07-11',NULL,'2026-09-28 19:02:16','2026-09-28 19:02:16'),(55,55,35,'Red Cross','2026-07-13',NULL,'2026-09-28 19:02:16','2026-09-28 19:02:16'),(56,56,42,'NDRRMC','2026-07-15',NULL,'2026-09-28 19:02:16','2026-09-28 19:02:16'),(57,57,85,'DOH','2026-07-17','2031-02-28','2026-09-28 19:02:16','2026-09-28 19:02:16'),(58,58,40,'DOH','2026-07-19','2031-03-31','2026-09-28 19:02:16','2026-09-28 19:02:16'),(59,59,30,'Red Cross','2026-07-21',NULL,'2026-09-28 19:02:16','2026-09-28 19:02:16'),(60,60,14,'LGU Cebu','2026-07-23',NULL,'2026-09-28 19:02:16','2026-09-28 19:02:16'),(61,61,25,'LGU Cebu','2026-07-25',NULL,'2026-09-28 19:02:16','2026-09-28 19:02:16'),(62,62,15,'NDRRMC','2026-07-27',NULL,'2026-09-28 19:02:16','2026-09-28 19:02:16'),(63,63,8,'NDRRMC','2026-07-29',NULL,'2026-09-28 19:02:16','2026-09-28 19:02:16'),(64,64,20,'Red Cross','2026-07-31',NULL,'2026-09-28 19:02:16','2026-09-28 19:02:16'),(65,65,26,'Red Cross','2026-08-02',NULL,'2026-09-28 19:02:16','2026-09-28 19:02:16'),(66,66,12,'LGU Cebu','2026-08-04',NULL,'2026-09-28 19:02:16','2026-09-28 19:02:16'),(67,67,18,'LGU Cebu','2026-08-06',NULL,'2026-09-28 19:02:16','2026-09-28 19:02:16'),(68,68,9,'NDRRMC','2026-08-08',NULL,'2026-09-28 19:02:16','2026-09-28 19:02:16');
/*!40000 ALTER TABLE `stock_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `must_change_password` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Test User','test@example.com','staff',NULL,'$2y$12$.WHK.Ou7htDBCpaDsuATC.uu3DccW7gIywt9USoOVr9/ClnbrFGsq',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15',0),(2,'Admin User','admin@trackingaid.org','admin',NULL,'$2y$12$CQBPeHwBKXqeN2WTwWzsLuiuT72.Gz4VndLoaGwMVrDLx90.kmOlW',NULL,'2026-09-28 19:02:15','2026-09-28 19:02:15',0),(3,'queen','mine@gmail.com','staff',NULL,'$2y$12$nK3HAEzBfsExInDFdKlnoOM2mS8Mq1HXfNDRBu2c.2UNzCOKGR8Gy',NULL,'2026-09-28 21:54:26','2026-09-28 21:55:29',0);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-29 20:05:08
