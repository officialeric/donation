-- MySQL dump 10.13  Distrib 8.0.42, for Win64 (x86_64)
--
-- Host: localhost    Database: donation
-- ------------------------------------------------------
-- Server version	8.0.42

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Temporary view structure for view `campaign_stats`
--

DROP TABLE IF EXISTS `campaign_stats`;
/*!50001 DROP VIEW IF EXISTS `campaign_stats`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `campaign_stats` AS SELECT 
 1 AS `id`,
 1 AS `orphanage_id`,
 1 AS `title`,
 1 AS `target_amount`,
 1 AS `current_amount`,
 1 AS `progress_percentage`,
 1 AS `deadline`,
 1 AS `days_remaining`,
 1 AS `status`,
 1 AS `priority`,
 1 AS `orphanage_name`,
 1 AS `total_donations`,
 1 AS `unique_donors`*/;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `campaigns`
--

DROP TABLE IF EXISTS `campaigns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `campaigns` (
  `id` int NOT NULL AUTO_INCREMENT,
  `orphanage_id` int NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci NOT NULL,
  `target_amount` decimal(10,2) NOT NULL,
  `current_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `deadline` date NOT NULL,
  `status` enum('active','completed','paused','cancelled') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'active',
  `image` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `priority` enum('low','medium','high','urgent') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'medium',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `orphanage_id` (`orphanage_id`),
  KEY `status` (`status`),
  KEY `deadline` (`deadline`),
  KEY `idx_campaigns_status_deadline` (`status`,`deadline`),
  KEY `idx_campaigns_orphanage_status` (`orphanage_id`,`status`),
  CONSTRAINT `campaigns_ibfk_1` FOREIGN KEY (`orphanage_id`) REFERENCES `orphanages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `campaigns`
--

LOCK TABLES `campaigns` WRITE;
/*!40000 ALTER TABLE `campaigns` DISABLE KEYS */;
INSERT INTO `campaigns` VALUES (1,1,'School Uniforms for 50 Children','We need to provide school uniforms for 50 children starting the new academic year. Each uniform costs $25 including shoes and school supplies.',1250.00,320.00,'2025-08-15','active',NULL,'high','2025-06-17 20:10:55',NULL),(2,1,'Medical Equipment Fund','Essential medical equipment including thermometers, first aid supplies, and basic medications for our health clinic.',800.00,150.00,'2025-07-30','active',NULL,'urgent','2025-06-17 20:10:55',NULL),(3,2,'Computer Lab Setup','Setting up a computer lab with 10 computers to provide digital literacy training for our children aged 12-18.',5000.00,1200.00,'2025-09-01','active',NULL,'medium','2025-06-17 20:10:55',NULL),(4,2,'Playground Equipment','Safe and modern playground equipment to provide recreational activities for children of all ages.',3000.00,450.00,'2025-10-15','active',NULL,'medium','2025-06-17 20:10:55',NULL),(5,3,'Vocational Training Workshop','Tools and equipment for vocational training in carpentry, tailoring, and basic electronics for older children.',2500.00,800.00,'2025-08-30','active',NULL,'high','2025-06-17 20:10:55',NULL),(6,3,'Library Books and Educational Materials','Building a comprehensive library with age-appropriate books, educational materials, and learning resources.',1500.00,600.00,'2025-07-20','active',NULL,'medium','2025-06-17 20:10:55',NULL);
/*!40000 ALTER TABLE `campaigns` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `donations`
--

DROP TABLE IF EXISTS `donations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `donations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `orphanage_id` int NOT NULL,
  `campaign_id` int DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_status` enum('pending','completed','failed','refunded') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'pending',
  `payment_method` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `transaction_id` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `message` text COLLATE utf8mb4_general_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `orphanage_id` (`orphanage_id`),
  KEY `campaign_id` (`campaign_id`),
  KEY `idx_donations_campaign_status` (`campaign_id`,`payment_status`),
  CONSTRAINT `donations_ibfk_3` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `donations`
--

LOCK TABLES `donations` WRITE;
/*!40000 ALTER TABLE `donations` DISABLE KEYS */;
INSERT INTO `donations` VALUES (1,3,1,NULL,10000.00,'completed','bank_transfer','TXN17489506543076','YEAH I LOVE THAT','2025-06-03 08:37:34',NULL),(2,4,1,NULL,1000000.00,'completed','bank_transfer','TXN17489747807244','i just love helping kids','2025-06-03 15:19:40',NULL),(3,2,1,NULL,1000000.00,'completed','credit_card','TXN17490301388870','I would be happy to help childrens with in needs','2025-06-04 09:42:18',NULL),(4,8,1,NULL,56789.00,'completed','credit_card','TXN17502372364976','fxjyxjuxhj','2025-06-18 09:00:36',NULL),(5,10,1,NULL,5000000.00,'completed','credit_card','TXN17502448339906',' I UHUSHGGHJ','2025-06-18 11:07:13',NULL);
/*!40000 ALTER TABLE `donations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orphanages`
--

DROP TABLE IF EXISTS `orphanages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orphanages` (
  `id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `location` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci NOT NULL,
  `contact_person` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `contact_phone` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `contact_email` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `bank_account` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orphanages`
--

LOCK TABLES `orphanages` WRITE;
/*!40000 ALTER TABLE `orphanages` DISABLE KEYS */;
INSERT INTO `orphanages` VALUES (1,'Hope Children Home','Ilala','A loving home for children in need, providing education, healthcare, and emotional support to help them build a brighter future.','Sarah Johnson','0626370989','sarah@hopechildrenshome.org','1234567890',NULL,'active','2023-12-31 18:00:00','2025-06-04 07:26:00'),(2,'Sunshine Orphanage','Temeke','Dedicated to providing a safe and nurturing environment for orphaned children, with focus on education and life skills development.','Michael Chen','07263609878','michael@sunshineorphanage.org','0987654321',NULL,'active','2023-12-31 18:00:00','2025-06-04 07:26:00'),(3,'Little Angels Home','Kinondoni','Caring for children from infancy to adulthood, providing comprehensive support including education, healthcare, and vocational training.','Maria Rodriguez','07236748332','maria@littleangelshome.org','1122334455',NULL,'active','2023-12-31 18:00:00','2025-06-04 07:26:00');
/*!40000 ALTER TABLE `orphanages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `first_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `middle_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `last_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `role` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'donor',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Kadilana','','','','0625290997','donor@gmail.com','$2y$10$Pv.mCdAMCE9fVwWW6DKn4e4TkechO0tZCIoNjkmsq0kX11PbnY4DO','2025-05-28 10:28:39','donor'),(2,'irene','','','','0626370989','admin@gmail.com','$2y$10$DG3oe0GI/1sgLXq5KmVs6ecD3a21K7iS3GCO1Nl/f90bHfyeG48v6','2025-05-28 10:30:14','admin'),(8,'Ernest',' ',' ',' ','0762785985','ernest@gamil.com','$2y$10$pthM7OV5KjEFIY9ggfro6.JRzXZVl25KBc0oNAyyZCz8ZMUQkRhb6','2025-06-18 08:59:56','donor'),(9,'noel',' ',' ',' ','0762785985','noel@gmail.com','$2y$10$SHi7f7SObdAnXPAKKEHV0OVDdNOQoVI2OgGKU2KVtOGke1DvycFNG','2025-06-18 10:51:51','donor'),(10,'samuel',' ',' ',' ','0672567890','samuel@gmail.com','$2y$10$X8P0e77sg7fxmWscWhau6.0Ns.5/lJ7XMYhdxv0nOCbV.R0RYTlsO','2025-06-18 11:06:27','donor');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Final view structure for view `campaign_stats`
--

/*!50001 DROP VIEW IF EXISTS `campaign_stats`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `campaign_stats` AS select `c`.`id` AS `id`,`c`.`orphanage_id` AS `orphanage_id`,`c`.`title` AS `title`,`c`.`target_amount` AS `target_amount`,`c`.`current_amount` AS `current_amount`,round(((`c`.`current_amount` / `c`.`target_amount`) * 100),2) AS `progress_percentage`,`c`.`deadline` AS `deadline`,(to_days(`c`.`deadline`) - to_days(curdate())) AS `days_remaining`,`c`.`status` AS `status`,`c`.`priority` AS `priority`,`o`.`name` AS `orphanage_name`,count(`d`.`id`) AS `total_donations`,count(distinct `d`.`user_id`) AS `unique_donors` from ((`campaigns` `c` left join `orphanages` `o` on((`c`.`orphanage_id` = `o`.`id`))) left join `donations` `d` on(((`c`.`id` = `d`.`campaign_id`) and (`d`.`payment_status` = 'completed')))) group by `c`.`id` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-06-25 12:24:07
