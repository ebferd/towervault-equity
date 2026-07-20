-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: 127.0.0.1    Database: nexvest
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `admins`
--

DROP TABLE IF EXISTS `admins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admins` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `email` varchar(191) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('super_admin','compliance','support','finance') NOT NULL DEFAULT 'support',
  `avatar` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_login_at` datetime DEFAULT NULL,
  `last_login_ip` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_admin_email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admins`
--

LOCK TABLES `admins` WRITE;
/*!40000 ALTER TABLE `admins` DISABLE KEYS */;
INSERT INTO `admins` VALUES (1,'Super Admin','admin@nexvest.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','super_admin',NULL,1,'2026-06-18 11:55:49','::1','2026-06-17 18:01:34','2026-06-18 10:55:49');
/*!40000 ALTER TABLE `admins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `announcements`
--

DROP TABLE IF EXISTS `announcements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `announcements` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `subject` varchar(255) NOT NULL,
  `message` longtext NOT NULL,
  `sent_by` int(10) unsigned NOT NULL,
  `sent_to` enum('all','active','verified') NOT NULL DEFAULT 'all',
  `recipient_count` int(10) unsigned NOT NULL DEFAULT 0,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_ann_admin` (`sent_by`),
  CONSTRAINT `fk_ann_admin` FOREIGN KEY (`sent_by`) REFERENCES `admins` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `announcements`
--

LOCK TABLES `announcements` WRITE;
/*!40000 ALTER TABLE `announcements` DISABLE KEYS */;
INSERT INTO `announcements` VALUES (1,'Test Announcement','This is a test announcement',1,'all',0,'2026-06-17 18:53:33');
/*!40000 ALTER TABLE `announcements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `audit_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `admin_id` int(10) unsigned NOT NULL,
  `action` varchar(80) NOT NULL,
  `target_type` varchar(50) DEFAULT NULL,
  `target_id` int(10) unsigned DEFAULT NULL,
  `target_name` varchar(200) DEFAULT NULL,
  `detail` text NOT NULL,
  `old_value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_value`)),
  `new_value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_value`)),
  `severity` enum('low','medium','high') NOT NULL DEFAULT 'medium',
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_al_admin` (`admin_id`),
  KEY `idx_al_action` (`action`),
  KEY `idx_al_severity` (`severity`),
  KEY `idx_al_created` (`created_at`),
  CONSTRAINT `fk_al_admin` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=76 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_logs`
--

LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
INSERT INTO `audit_logs` VALUES (1,1,'admin_login',NULL,NULL,NULL,'Admin logged in',NULL,NULL,'low','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-06-17 18:05:31'),(2,1,'admin_login',NULL,NULL,NULL,'Admin logged in',NULL,NULL,'low','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-06-17 18:12:31'),(3,1,'admin_login',NULL,NULL,NULL,'Admin logged in',NULL,NULL,'low','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-06-17 18:12:43'),(4,1,'kyc_approved','user',1,'liamnet1998@gmail.com','KYC approved for user #1',NULL,NULL,'medium','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-06-17 18:12:57'),(5,1,'ghost_login','user',1,'liamnet1998@gmail.com','Ghost login into investor account #1 (liamnet1998@gmail.com)',NULL,NULL,'high','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-06-17 18:13:58'),(6,1,'admin_login',NULL,NULL,NULL,'Admin logged in',NULL,NULL,'low','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-06-17 18:14:15'),(7,1,'admin_login',NULL,NULL,NULL,'Admin logged in',NULL,NULL,'low','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-06-17 18:15:06'),(8,1,'settings_updated','platform',NULL,'Platform Settings','Platform settings updated',NULL,NULL,'high','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-06-17 18:15:19'),(9,1,'settings_updated','platform',NULL,'Platform Settings','Platform settings updated',NULL,NULL,'high','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-06-17 18:15:39'),(10,1,'ghost_login','user',1,'liamnet1998@gmail.com','Ghost login into investor account #1 (liamnet1998@gmail.com)',NULL,NULL,'high','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-06-17 18:15:59'),(11,1,'admin_login',NULL,NULL,NULL,'Admin logged in',NULL,NULL,'low','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-06-17 18:43:57'),(12,1,'admin_login',NULL,NULL,NULL,'Admin logged in',NULL,NULL,'low','::1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456','2026-06-17 18:44:43'),(13,1,'admin_login',NULL,NULL,NULL,'Admin logged in',NULL,NULL,'low','::1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456','2026-06-17 18:45:17'),(14,1,'admin_login',NULL,NULL,NULL,'Admin logged in',NULL,NULL,'low','::1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456','2026-06-17 18:46:21'),(15,1,'admin_login',NULL,NULL,NULL,'Admin logged in',NULL,NULL,'low','::1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456','2026-06-17 18:47:09'),(16,1,'deposit_approved','user',1,'Danel Mark','Approved deposit NV-04A806 — $500.00',NULL,NULL,'medium','::1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456','2026-06-17 18:47:10'),(17,1,'admin_login',NULL,NULL,NULL,'Admin logged in',NULL,NULL,'low','::1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456','2026-06-17 18:47:19'),(18,1,'deposit_rejected','user',1,'Danel Mark','Rejected deposit NV-6C199E: Payment not received',NULL,NULL,'medium','::1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456','2026-06-17 18:47:20'),(19,1,'admin_login',NULL,NULL,NULL,'Admin logged in',NULL,NULL,'low','::1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456','2026-06-17 18:52:54'),(20,1,'admin_login',NULL,NULL,NULL,'Admin logged in',NULL,NULL,'low','::1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456','2026-06-17 18:53:29'),(21,1,'wallet_credit','user',1,'liamnet1998@gmail.com','Manual credit of $100.00 on user #1. Note: Test credit',NULL,NULL,'high','::1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456','2026-06-17 18:53:32'),(22,1,'investment_created','investment',2,'Test Fund 2','Created investment: Test Fund 2',NULL,NULL,'medium','::1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456','2026-06-17 18:53:33'),(23,1,'ticket_closed','ticket',1,'TKT-1F9AC4','Closed ticket #1',NULL,NULL,'low','::1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456','2026-06-17 18:53:33'),(24,1,'announcement_sent','platform',NULL,'All Investors','Announcement sent to 0 investors: Test Announcement',NULL,NULL,'medium','::1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456','2026-06-17 18:53:33'),(25,1,'admin_login',NULL,NULL,NULL,'Admin logged in',NULL,NULL,'low','::1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456','2026-06-17 18:55:37'),(26,1,'admin_login',NULL,NULL,NULL,'Admin logged in',NULL,NULL,'low','::1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456','2026-06-17 18:56:17'),(27,1,'admin_login',NULL,NULL,NULL,'Admin logged in',NULL,NULL,'low','::1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456','2026-06-17 18:57:17'),(28,1,'withdrawal_approved','user',1,NULL,'Approved withdrawal WD-CC6E3D of $50.00',NULL,NULL,'high','::1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456','2026-06-17 18:57:19'),(29,1,'withdrawal_completed','user',1,NULL,'Completed withdrawal WD-CC6E3D',NULL,NULL,'medium','::1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456','2026-06-17 18:57:19'),(30,1,'ticket_replied','ticket',1,'Test Ticket','Replied to ticket TKT-1F9AC4',NULL,NULL,'low','::1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456','2026-06-17 18:57:19'),(31,1,'admin_login',NULL,NULL,NULL,'Admin logged in',NULL,NULL,'low','::1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456','2026-06-17 19:01:58'),(32,1,'admin_login',NULL,NULL,NULL,'Admin logged in',NULL,NULL,'low','::1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456','2026-06-17 19:02:23'),(33,1,'admin_login',NULL,NULL,NULL,'Admin logged in',NULL,NULL,'low','::1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456','2026-06-17 19:03:09'),(34,1,'admin_login',NULL,NULL,NULL,'Admin logged in',NULL,NULL,'low','::1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456','2026-06-17 19:06:21'),(35,1,'settings_updated','platform',NULL,'Platform Settings','Platform settings updated',NULL,NULL,'high','::1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456','2026-06-17 19:06:21'),(36,1,'admin_login',NULL,NULL,NULL,'Admin logged in',NULL,NULL,'low','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-06-17 19:06:36'),(37,1,'ghost_login','user',2,'johndoe_test99@example.com','Ghost login into investor account #2 (johndoe_test99@example.com)',NULL,NULL,'high','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-06-17 19:07:01'),(38,1,'admin_login',NULL,NULL,NULL,'Admin logged in',NULL,NULL,'low','::1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456','2026-06-17 19:10:42'),(39,1,'admin_login',NULL,NULL,NULL,'Admin logged in',NULL,NULL,'low','::1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456','2026-06-17 19:12:40'),(40,1,'user_suspend','user',2,'johndoe_test99@example.com','User #2 suspendd',NULL,NULL,'high','::1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456','2026-06-17 19:12:41'),(41,1,'user_unsuspend','user',2,'johndoe_test99@example.com','User #2 unsuspendd',NULL,NULL,'high','::1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456','2026-06-17 19:12:41'),(42,1,'admin_login',NULL,NULL,NULL,'Admin logged in',NULL,NULL,'low','::1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456','2026-06-17 19:13:17'),(43,1,'admin_login',NULL,NULL,NULL,'Admin logged in',NULL,NULL,'low','::1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456','2026-06-17 19:24:51'),(44,1,'investment_edited','investment',1,'Test Real Estate Fund','Edited investment #1: Test Real Estate Fund',NULL,NULL,'medium','::1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456','2026-06-17 19:24:51'),(45,1,'investment_deleted','investment',2,'Test Fund 2','Deleted investment #2: Test Fund 2',NULL,NULL,'high','::1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456','2026-06-17 19:24:52'),(46,1,'withdrawal_rejected','user',1,NULL,'Rejected withdrawal WD-F3D8E0',NULL,NULL,'high','::1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456','2026-06-17 19:24:52'),(47,1,'settings_updated','platform',NULL,'Platform Settings','Platform settings updated',NULL,NULL,'high','::1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456','2026-06-17 19:24:53'),(48,1,'admin_login',NULL,NULL,NULL,'Admin logged in',NULL,NULL,'low','::1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456','2026-06-17 19:25:46'),(49,1,'user_updated','user',2,'johndoe_test99@example.com','Updated user #2 status=active kyc=not_submitted','{\"status\":\"active\",\"kyc_status\":\"not_submitted\"}','{\"status\":\"active\",\"kyc_status\":\"not_submitted\"}','medium','::1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456','2026-06-17 19:25:47'),(50,1,'admin_login',NULL,NULL,NULL,'Admin logged in',NULL,NULL,'low','::1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456','2026-06-17 19:26:11'),(51,1,'admin_login',NULL,NULL,NULL,'Admin logged in',NULL,NULL,'low','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-06-17 19:27:24'),(52,1,'admin_login',NULL,NULL,NULL,'Admin logged in',NULL,NULL,'low','::1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456','2026-06-17 19:33:46'),(53,1,'investment_edited','investment',1,'Test Real Estate Fund','Edited investment #1: Test Real Estate Fund',NULL,NULL,'medium','::1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456','2026-06-17 19:33:46'),(54,1,'admin_login',NULL,NULL,NULL,'Admin logged in',NULL,NULL,'low','::1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456','2026-06-17 19:34:32'),(55,1,'admin_login',NULL,NULL,NULL,'Admin logged in',NULL,NULL,'low','::1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456','2026-06-17 19:34:33'),(56,1,'admin_login',NULL,NULL,NULL,'Admin logged in',NULL,NULL,'low','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-06-17 20:28:40'),(57,1,'ghost_login','user',2,'johndoe_test99@example.com','Ghost login into investor account #2 (johndoe_test99@example.com)',NULL,NULL,'high','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-06-17 20:28:51'),(58,1,'admin_login',NULL,NULL,NULL,'Admin logged in',NULL,NULL,'low','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-06-17 20:30:07'),(59,1,'admin_login',NULL,NULL,NULL,'Admin logged in',NULL,NULL,'low','::1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456','2026-06-17 21:02:52'),(60,1,'deposit_approved','user',1,'Liam Test','Approved deposit NV-296622 — $500.00',NULL,NULL,'medium','::1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456','2026-06-17 21:02:54'),(61,1,'admin_login',NULL,NULL,NULL,'Admin logged in',NULL,NULL,'low','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-06-17 22:05:20'),(62,1,'investment_edited','investment',1,'Test Real Estate Fund','Edited investment #1: Test Real Estate Fund',NULL,NULL,'medium','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-06-17 22:05:43'),(63,1,'investment_edited','investment',1,'Test Real Estate Fund','Edited investment #1: Test Real Estate Fund',NULL,NULL,'medium','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-06-17 22:05:50'),(64,1,'ghost_login','user',2,'johndoe_test99@example.com','Ghost login into investor account #2 (johndoe_test99@example.com)',NULL,NULL,'high','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-06-17 22:06:19'),(65,1,'admin_login',NULL,NULL,NULL,'Admin logged in',NULL,NULL,'low','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-06-17 22:13:05'),(66,1,'admin_login',NULL,NULL,NULL,'Admin logged in',NULL,NULL,'low','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-06-17 22:33:04'),(67,1,'admin_login',NULL,NULL,NULL,'Admin logged in',NULL,NULL,'low','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-18 10:36:00'),(68,1,'admin_login',NULL,NULL,NULL,'Admin logged in',NULL,NULL,'low','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-18 10:50:59'),(69,1,'deposit_approved','user',1,'Liam Test','Approved deposit NV-22940B — $300.00',NULL,NULL,'medium','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-18 10:52:05'),(70,1,'investment_activated','user',1,'Liam Test','Activated holding #4 — $300.00',NULL,NULL,'high','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-18 10:52:38'),(71,1,'deposit_rejected','user',1,'Liam Test','Rejected deposit NV-A33C4B: Payment proof not verifiable.',NULL,NULL,'medium','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-18 10:52:46'),(72,1,'admin_login',NULL,NULL,NULL,'Admin logged in',NULL,NULL,'low','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-18 10:53:40'),(73,1,'admin_login',NULL,NULL,NULL,'Admin logged in',NULL,NULL,'low','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-18 10:55:49'),(74,1,'withdrawal_approved','user',1,NULL,'Approved withdrawal WD-594D53 of $100.00',NULL,NULL,'high','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-18 10:56:07'),(75,1,'withdrawal_completed','user',1,NULL,'Completed withdrawal WD-594D53',NULL,NULL,'medium','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-18 10:56:16');
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `deposit_invoices`
--

DROP TABLE IF EXISTS `deposit_invoices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `deposit_invoices` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `reference` varchar(20) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `method` enum('crypto','paypal','wire') NOT NULL,
  `coin` varchar(10) DEFAULT NULL,
  `wallet_address` varchar(255) DEFAULT NULL,
  `holding_id` int(10) unsigned DEFAULT NULL,
  `status` enum('pending','submitted','paid','rejected','expired','cancelled') NOT NULL DEFAULT 'pending',
  `expires_at` datetime NOT NULL,
  `confirmed_at` datetime DEFAULT NULL,
  `admin_note` text DEFAULT NULL,
  `reviewed_by` int(10) unsigned DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_invoice_ref` (`reference`),
  KEY `idx_di_user` (`user_id`),
  KEY `idx_di_status` (`status`),
  KEY `fk_di_holding` (`holding_id`),
  KEY `fk_di_admin` (`reviewed_by`),
  CONSTRAINT `fk_di_admin` FOREIGN KEY (`reviewed_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_di_holding` FOREIGN KEY (`holding_id`) REFERENCES `investment_holdings` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_di_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `deposit_invoices`
--

LOCK TABLES `deposit_invoices` WRITE;
/*!40000 ALTER TABLE `deposit_invoices` DISABLE KEYS */;
INSERT INTO `deposit_invoices` VALUES (1,1,'NV-6C199E',50000.00,'crypto','eth','dbndndbndndbnd',NULL,'rejected','2026-06-17 18:46:11',NULL,'Payment not received',1,'2026-06-17 19:47:20','2026-06-17 18:16:11'),(2,1,'NV-04A806',500.00,'wire',NULL,NULL,NULL,'paid','2026-06-17 19:13:55','2026-06-17 19:47:09',NULL,1,'2026-06-17 19:47:09','2026-06-17 18:43:55'),(3,1,'NV-8A3546',300.00,'crypto','usdt','TTestWalletAddress123456789',NULL,'paid','2026-06-17 19:15:16','2026-06-17 19:45:20',NULL,1,'2026-06-17 19:45:20','2026-06-17 18:45:16'),(4,1,'NV-B2ED7F',200.00,'wire',NULL,NULL,NULL,'pending','2026-06-17 19:23:31',NULL,NULL,NULL,NULL,'2026-06-17 18:53:31'),(5,1,'NV-56B657',150.00,'paypal',NULL,NULL,NULL,'pending','2026-06-17 19:23:32',NULL,NULL,NULL,NULL,'2026-06-17 18:53:32'),(6,1,'NV-6EF56C',200.00,'wire',NULL,NULL,NULL,'submitted','2026-06-17 19:40:43',NULL,NULL,NULL,NULL,'2026-06-17 19:10:43'),(7,1,'NV-413DBC',200.00,'wire',NULL,NULL,3,'pending','2026-06-17 20:00:02',NULL,NULL,NULL,NULL,'2026-06-17 19:30:02'),(8,2,'NV-67D064',6000.00,'crypto','btc','sdbnsavn',NULL,'pending','2026-06-17 20:59:04',NULL,NULL,NULL,NULL,'2026-06-17 20:29:04'),(9,1,'NV-296622',500.00,'wire',NULL,NULL,NULL,'paid','2026-06-17 21:32:53','2026-06-17 22:02:54',NULL,1,'2026-06-17 22:02:54','2026-06-17 21:02:53'),(10,1,'NV-589AF1',300.00,'wire',NULL,NULL,4,'paid','2026-06-17 21:32:55','2026-06-18 11:52:38','Approved',1,'2026-06-18 11:52:38','2026-06-17 21:02:55'),(11,1,'NV-2B6BD1',500.00,'wire',NULL,NULL,NULL,'pending','2026-06-17 22:06:12',NULL,NULL,NULL,NULL,'2026-06-17 21:36:12'),(12,1,'NV-A33C4B',200.00,'paypal',NULL,NULL,NULL,'rejected','2026-06-17 22:06:42',NULL,'Payment proof not verifiable.',1,'2026-06-18 11:52:46','2026-06-17 21:36:42'),(13,1,'NV-CB49FB',500.00,'wire',NULL,NULL,5,'pending','2026-06-17 22:08:04',NULL,NULL,NULL,NULL,'2026-06-17 21:38:04'),(14,1,'NV-9FA4EE',500.00,'wire',NULL,NULL,NULL,'pending','2026-06-17 22:18:28',NULL,NULL,NULL,NULL,'2026-06-17 21:48:28'),(15,1,'NV-49B73F',300.00,'wire',NULL,NULL,NULL,'pending','2026-06-17 22:20:53',NULL,NULL,NULL,NULL,'2026-06-17 21:50:53'),(16,1,'NV-E7D317',300.00,'paypal',NULL,NULL,NULL,'pending','2026-06-17 22:24:18',NULL,NULL,NULL,NULL,'2026-06-17 21:54:18'),(17,1,'NV-139A1F',300.00,'wire',NULL,NULL,NULL,'pending','2026-06-17 22:25:05',NULL,NULL,NULL,NULL,'2026-06-17 21:55:05'),(18,1,'NV-22940B',300.00,'wire',NULL,NULL,NULL,'paid','2026-06-17 22:26:28','2026-06-18 11:52:05',NULL,1,'2026-06-18 11:52:05','2026-06-17 21:56:28'),(19,1,'NV-6CBD45',150.00,'wire',NULL,NULL,6,'pending','2026-06-17 22:32:15',NULL,NULL,NULL,NULL,'2026-06-17 22:02:15');
/*!40000 ALTER TABLE `deposit_invoices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `email_verifications`
--

DROP TABLE IF EXISTS `email_verifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `email_verifications` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `token` varchar(64) NOT NULL,
  `otp` varchar(6) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_ev_user` (`user_id`),
  KEY `idx_ev_token` (`token`),
  CONSTRAINT `fk_ev_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email_verifications`
--

LOCK TABLES `email_verifications` WRITE;
/*!40000 ALTER TABLE `email_verifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `email_verifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fund_holdings`
--

DROP TABLE IF EXISTS `fund_holdings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fund_holdings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `investment_id` int(10) unsigned NOT NULL,
  `holding_name` varchar(200) NOT NULL,
  `sort_order` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_fh_investment` (`investment_id`),
  CONSTRAINT `fk_fh_investment` FOREIGN KEY (`investment_id`) REFERENCES `investments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fund_holdings`
--

LOCK TABLES `fund_holdings` WRITE;
/*!40000 ALTER TABLE `fund_holdings` DISABLE KEYS */;
/*!40000 ALTER TABLE `fund_holdings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `investment_documents`
--

DROP TABLE IF EXISTS `investment_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `investment_documents` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `investment_id` int(10) unsigned NOT NULL,
  `name` varchar(200) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_size` int(10) unsigned DEFAULT NULL,
  `uploaded_by` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_idoc_investment` (`investment_id`),
  KEY `fk_idoc_admin` (`uploaded_by`),
  CONSTRAINT `fk_idoc_admin` FOREIGN KEY (`uploaded_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_idoc_investment` FOREIGN KEY (`investment_id`) REFERENCES `investments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `investment_documents`
--

LOCK TABLES `investment_documents` WRITE;
/*!40000 ALTER TABLE `investment_documents` DISABLE KEYS */;
INSERT INTO `investment_documents` VALUES (1,1,'Investment Prospectus','uploads/investments/09578016_ad01_2025-06-25.pdf',84842,1,'2026-06-17 22:16:44');
/*!40000 ALTER TABLE `investment_documents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `investment_holdings`
--

DROP TABLE IF EXISTS `investment_holdings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `investment_holdings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `investment_id` int(10) unsigned NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `payment_method` enum('crypto','paypal','wire','wallet') NOT NULL,
  `payment_ref` varchar(100) DEFAULT NULL,
  `status` enum('pending','active','matured','cancelled') NOT NULL DEFAULT 'pending',
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `roi` decimal(5,2) NOT NULL,
  `total_earned` decimal(15,2) NOT NULL DEFAULT 0.00,
  `last_payout_at` datetime DEFAULT NULL,
  `certificate_ref` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_cert_ref` (`certificate_ref`),
  KEY `idx_ih_user` (`user_id`),
  KEY `idx_ih_investment` (`investment_id`),
  KEY `idx_ih_status` (`status`),
  CONSTRAINT `fk_ih_investment` FOREIGN KEY (`investment_id`) REFERENCES `investments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ih_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `investment_holdings`
--

LOCK TABLES `investment_holdings` WRITE;
/*!40000 ALTER TABLE `investment_holdings` DISABLE KEYS */;
INSERT INTO `investment_holdings` VALUES (1,1,1,500.00,'wallet',NULL,'active','2026-06-17','2027-06-17',12.50,0.00,NULL,'INV-A1DE89','2026-06-17 18:39:17','2026-06-17 18:39:17'),(2,1,1,100.00,'wallet',NULL,'active','2026-06-17','2027-06-17',12.50,0.00,NULL,'INV-E492B7','2026-06-17 18:53:31','2026-06-17 18:53:31'),(3,1,1,200.00,'wire',NULL,'pending','2026-06-17','2026-06-17',0.00,0.00,NULL,'INV-78BC02','2026-06-17 19:30:02','2026-06-17 19:30:02'),(4,1,1,300.00,'wire','DEP-ADF0E1','cancelled','2026-06-17','2027-06-17',12.00,0.00,NULL,'INV-EF33BE','2026-06-17 21:02:55','2026-06-18 11:04:52'),(5,1,1,500.00,'wire',NULL,'pending','2026-06-17','2027-06-17',12.00,0.00,NULL,'INV-79CC5C','2026-06-17 21:38:04','2026-06-17 21:38:04'),(6,1,1,150.00,'wire',NULL,'pending','2026-06-17','2027-06-17',12.00,0.00,NULL,'INV-0D47F8','2026-06-17 22:02:15','2026-06-17 22:02:15');
/*!40000 ALTER TABLE `investment_holdings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `investments`
--

DROP TABLE IF EXISTS `investments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `investments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `slug` varchar(200) NOT NULL,
  `type` enum('real_estate','index_fund') NOT NULL,
  `status` enum('active','funded','closed','coming_soon') NOT NULL DEFAULT 'active',
  `short_desc` varchar(500) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `roi` decimal(5,2) NOT NULL,
  `duration_value` smallint(6) NOT NULL,
  `duration_unit` enum('days','months','years') NOT NULL DEFAULT 'months',
  `payout_frequency` enum('daily','weekly','monthly','quarterly','semi_annual','at_maturity') NOT NULL DEFAULT 'monthly',
  `min_investment` decimal(15,2) NOT NULL DEFAULT 100.00,
  `max_investment` decimal(15,2) DEFAULT NULL,
  `funding_target` decimal(18,2) DEFAULT NULL,
  `funding_raised` decimal(18,2) NOT NULL DEFAULT 0.00,
  `property_type` varchar(100) DEFAULT NULL,
  `street_address` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state_region` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `postcode` varchar(20) DEFAULT NULL,
  `maps_link` varchar(500) DEFAULT NULL,
  `property_size` varchar(100) DEFAULT NULL,
  `total_units` varchar(100) DEFAULT NULL,
  `occupancy_rate` decimal(5,2) DEFAULT NULL,
  `year_built` smallint(6) DEFAULT NULL,
  `completion_date` date DEFAULT NULL,
  `ticker` varchar(20) DEFAULT NULL,
  `fund_category` varchar(100) DEFAULT NULL,
  `risk_level` enum('low','low_medium','medium','medium_high','high') DEFAULT NULL,
  `management_fee` decimal(5,2) DEFAULT NULL,
  `benchmark` varchar(200) DEFAULT NULL,
  `fund_start_date` date DEFAULT NULL,
  `fund_end_date` date DEFAULT NULL,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `notify_on_launch` tinyint(1) NOT NULL DEFAULT 0,
  `investor_count` int(10) unsigned NOT NULL DEFAULT 0,
  `image` varchar(255) DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_investment_slug` (`slug`),
  KEY `idx_inv_type` (`type`),
  KEY `idx_inv_status` (`status`),
  KEY `fk_inv_admin` (`created_by`),
  CONSTRAINT `fk_inv_admin` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `investments`
--

LOCK TABLES `investments` WRITE;
/*!40000 ALTER TABLE `investments` DISABLE KEYS */;
INSERT INTO `investments` VALUES (1,'Test Real Estate Fund','test-real-estate-fund','real_estate','active','Real estate fund','Premium real estate investment portfolio',12.00,12,'months','monthly',100.00,NULL,NULL,600.00,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'medium',NULL,NULL,NULL,NULL,0,0,0,0,NULL,NULL,'2026-06-17 18:24:36','2026-06-18 11:04:52');
/*!40000 ALTER TABLE `investments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kyc_submissions`
--

DROP TABLE IF EXISTS `kyc_submissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kyc_submissions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `full_legal_name` varchar(200) NOT NULL,
  `date_of_birth` date NOT NULL,
  `id_type` enum('passport','national_id','drivers_license') NOT NULL,
  `doc_front` varchar(255) DEFAULT NULL,
  `doc_back` varchar(255) DEFAULT NULL,
  `doc_selfie` varchar(255) DEFAULT NULL,
  `proof_of_address` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `rejection_reason` text DEFAULT NULL,
  `reviewed_by` int(10) unsigned DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_kyc_user` (`user_id`),
  KEY `idx_kyc_status` (`status`),
  KEY `fk_kyc_reviewer` (`reviewed_by`),
  CONSTRAINT `fk_kyc_reviewer` FOREIGN KEY (`reviewed_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_kyc_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kyc_submissions`
--

LOCK TABLES `kyc_submissions` WRITE;
/*!40000 ALTER TABLE `kyc_submissions` DISABLE KEYS */;
INSERT INTO `kyc_submissions` VALUES (1,1,'Danel Mark','2007-06-17','national_id','C:\\xampp\\htdocs\\nexvest/uploads/kyc/93b308db4063cbb7b4252fb6f023052e.jpeg','C:\\xampp\\htdocs\\nexvest/uploads/kyc/98351e1e634eb887b6f1884694644220.jpeg',NULL,NULL,'approved',NULL,1,'2026-06-17 19:12:57','2026-06-17 18:12:15','2026-06-17 18:12:57');
/*!40000 ALTER TABLE `kyc_submissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `login_attempts`
--

DROP TABLE IF EXISTS `login_attempts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `login_attempts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(191) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `success` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_la_email` (`email`),
  KEY `idx_la_ip` (`ip_address`),
  KEY `idx_la_created` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=55 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `login_attempts`
--

LOCK TABLES `login_attempts` WRITE;
/*!40000 ALTER TABLE `login_attempts` DISABLE KEYS */;
INSERT INTO `login_attempts` VALUES (1,'liamnet1998@gmail.com','::1',0,'2026-06-17 18:26:07'),(2,'liamnet1998@gmail.com','::1',1,'2026-06-17 18:26:29'),(3,'liamnet1998@gmail.com','::1',1,'2026-06-17 18:27:42'),(4,'liamnet1998@gmail.com','::1',1,'2026-06-17 18:35:31'),(5,'liamnet1998@gmail.com','::1',1,'2026-06-17 18:36:25'),(6,'liamnet1998@gmail.com','::1',1,'2026-06-17 18:38:59'),(7,'liamnet1998@gmail.com','::1',1,'2026-06-17 18:39:16'),(8,'liamnet1998@gmail.com','::1',1,'2026-06-17 18:42:28'),(9,'liamnet1998@gmail.com','::1',1,'2026-06-17 18:43:54'),(10,'liamnet1998@gmail.com','::1',1,'2026-06-17 18:44:42'),(11,'liamnet1998@gmail.com','::1',1,'2026-06-17 18:45:16'),(12,'liamnet1998@gmail.com','::1',1,'2026-06-17 18:52:54'),(13,'liamnet1998@gmail.com','::1',1,'2026-06-17 18:53:29'),(14,'liamnet1998@gmail.com','::1',1,'2026-06-17 18:55:36'),(15,'liamnet1998@gmail.com','::1',1,'2026-06-17 18:57:16'),(16,'liamnet1998@gmail.com','::1',1,'2026-06-17 19:00:38'),(17,'liamnet1998@gmail.com','::1',1,'2026-06-17 19:01:29'),(18,'liamnet1998@gmail.com','::1',1,'2026-06-17 19:05:02'),(19,'liamnet1998@gmail.com','::1',1,'2026-06-17 19:10:41'),(20,'liamnet1998@gmail.com','::1',1,'2026-06-17 19:12:39'),(21,'johndoe_test99@example.com','::1',1,'2026-06-17 19:12:40'),(22,'johndoe_test99@example.com','::1',1,'2026-06-17 19:16:57'),(23,'liamnet1998@gmail.com','::1',1,'2026-06-17 19:25:45'),(24,'liamnet1998@gmail.com','::1',1,'2026-06-17 19:29:56'),(25,'liamnet1998@gmail.com','::1',1,'2026-06-17 19:34:32'),(26,'liamnet1998@gmail.com','::1',1,'2026-06-17 21:02:52'),(27,'liamnet1998@gmail.com','::1',1,'2026-06-17 21:03:19'),(28,'liamnet1998@gmail.com','::1',1,'2026-06-17 21:03:50'),(29,'liamnet1998@gmail.com','::1',0,'2026-06-17 21:31:00'),(30,'liamnet1998@gmail.com','::1',0,'2026-06-17 21:33:34'),(31,'liamnet1998@gmail.com','::1',1,'2026-06-17 21:35:50'),(32,'liamnet1998@gmail.com','::1',1,'2026-06-17 21:36:11'),(33,'liamnet1998@gmail.com','::1',1,'2026-06-17 21:36:41'),(34,'liamnet1998@gmail.com','::1',1,'2026-06-17 21:37:06'),(35,'liamnet1998@gmail.com','::1',1,'2026-06-17 21:38:04'),(36,'liamnet1998@gmail.com','::1',1,'2026-06-17 21:38:30'),(37,'liamnet1998@gmail.com','::1',1,'2026-06-17 21:47:44'),(38,'liamnet1998@gmail.com','::1',1,'2026-06-17 21:50:49'),(39,'liamnet1998@gmail.com','::1',1,'2026-06-17 21:54:13'),(40,'liamnet1998@gmail.com','::1',1,'2026-06-17 21:56:22'),(41,'liamnet1998@gmail.com','::1',1,'2026-06-17 21:58:35'),(42,'liamnet1998@gmail.com','::1',1,'2026-06-17 22:02:11'),(43,'liamnet1998@gmail.com','::1',1,'2026-06-17 22:20:43'),(44,'liamnet1998@gmail.com','::1',0,'2026-06-18 09:49:33'),(45,'liamnet1998@gmail.com','::1',1,'2026-06-18 09:52:03'),(46,'liamnet1998@gmail.com','::1',1,'2026-06-18 09:56:46'),(47,'liamnet1998@gmail.com','::1',1,'2026-06-18 10:01:27'),(48,'liamnet1998@gmail.com','::1',1,'2026-06-18 10:07:24'),(49,'liamnet1998@gmail.com','::1',1,'2026-06-18 10:22:00'),(50,'liamnet1998@gmail.com','::1',1,'2026-06-18 10:30:51'),(51,'liamnet1998@gmail.com','::1',1,'2026-06-18 10:32:26'),(52,'liamnet1998@gmail.com','::1',1,'2026-06-18 11:02:59'),(53,'liamnet1998@gmail.com','::1',1,'2026-06-18 11:07:54'),(54,'liamnet1998@gmail.com','::1',1,'2026-06-18 11:13:23');
/*!40000 ALTER TABLE `login_attempts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notifications` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `type` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`data`)),
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `read_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_notif_user` (`user_id`),
  KEY `idx_notif_read` (`is_read`),
  CONSTRAINT `fk_notif_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
INSERT INTO `notifications` VALUES (1,1,'login_alert','New Login Detected','A new login was detected from IP ::1.',NULL,1,'2026-06-17 19:53:32','2026-06-17 18:11:14'),(2,1,'kyc','KYC Submitted','Your identity documents have been received and are under review.',NULL,1,'2026-06-17 19:53:32','2026-06-17 18:12:15'),(3,1,'kyc','KYC Approved','Your identity has been verified. You now have full platform access.',NULL,1,'2026-06-17 19:53:32','2026-06-17 18:12:57'),(4,1,'investment','Investment Activated','Your investment of $500.00 in Test Real Estate Fund is now active.',NULL,1,'2026-06-17 19:53:32','2026-06-17 18:39:17'),(5,1,'deposit','Deposit Confirmed','$300.00 has been credited to your wallet. Reference: DEP-D7F928',NULL,1,'2026-06-17 19:53:32','2026-06-17 18:45:20'),(6,1,'deposit','Deposit Confirmed','$500.00 has been credited to your wallet. Reference: DEP-D74115',NULL,1,'2026-06-17 19:53:32','2026-06-17 18:47:09'),(7,1,'deposit','Deposit Not Confirmed','Your deposit of $50,000.00 could not be confirmed. Reason: Payment not received. Please contact support if you believe this is an error.',NULL,1,'2026-06-17 19:53:32','2026-06-17 18:47:20'),(8,1,'investment','Investment Activated','Your investment of $100.00 in Test Real Estate Fund is now active.',NULL,1,'2026-06-17 19:53:32','2026-06-17 18:53:31'),(9,1,'adjustment','Wallet Credited','$100.00 has been credited to your wallet.',NULL,0,NULL,'2026-06-17 18:53:32'),(10,1,'withdrawal','Withdrawal Requested','Your withdrawal of $100.00 is under review.',NULL,0,NULL,'2026-06-17 18:57:18'),(11,1,'withdrawal','Withdrawal Requested','Your withdrawal of $50.00 is under review.',NULL,0,NULL,'2026-06-17 18:57:18'),(12,1,'withdrawal','Withdrawal Requested','Your withdrawal of $50.00 is under review.',NULL,0,NULL,'2026-06-17 18:57:18'),(13,1,'withdrawal','Withdrawal Approved','Your withdrawal of $50.00 has been approved.',NULL,0,NULL,'2026-06-17 18:57:19'),(14,1,'withdrawal','Withdrawal Completed','Your withdrawal of $50.00 has been processed.',NULL,0,NULL,'2026-06-17 18:57:19'),(15,2,'login_alert','New Login Detected','A new login was detected from IP ::1.',NULL,0,NULL,'2026-06-17 19:00:38'),(16,1,'deposit','Deposit Submitted','Your deposit of $200.00 is under review. Reference: NV-6EF56C',NULL,0,NULL,'2026-06-17 19:10:43'),(17,1,'withdrawal','Withdrawal Rejected','Your withdrawal request was rejected. Funds have been returned to your wallet.',NULL,0,NULL,'2026-06-17 19:24:52'),(18,1,'deposit','Deposit Submitted','Your deposit of $500.00 is under review. Reference: NV-296622',NULL,0,NULL,'2026-06-17 21:02:53'),(19,1,'deposit','Deposit Confirmed','$500.00 has been credited to your wallet. Reference: DEP-C317D1',NULL,0,NULL,'2026-06-17 21:02:54'),(20,1,'deposit','Deposit Submitted','Your deposit of $300.00 is under review. Reference: NV-589AF1',NULL,0,NULL,'2026-06-17 21:02:55'),(21,1,'deposit','Deposit Submitted','Your deposit of $200.00 is under review. Reference: NV-A33C4B',NULL,0,NULL,'2026-06-17 21:36:42'),(22,1,'deposit','Deposit Submitted','Your deposit of $300.00 is under review. Reference: NV-22940B',NULL,0,NULL,'2026-06-17 21:56:42'),(23,1,'deposit','Deposit Confirmed','$300.00 has been credited to your wallet. Reference: DEP-EFA874',NULL,0,NULL,'2026-06-18 10:52:05'),(24,1,'investment','Investment Confirmed','Your investment of $300.00 is now active. Ref: DEP-ADF0E1',NULL,0,NULL,'2026-06-18 10:52:38'),(25,1,'deposit','Deposit Not Confirmed','Your deposit of $200.00 could not be confirmed. Reason: Payment proof not verifiable.. Please contact support if you believe this is an error.',NULL,0,NULL,'2026-06-18 10:52:46'),(26,1,'withdrawal','Withdrawal Approved','Your withdrawal of $100.00 has been approved.',NULL,0,NULL,'2026-06-18 10:56:07'),(27,1,'withdrawal','Withdrawal Completed','Your withdrawal of $100.00 has been processed.',NULL,0,NULL,'2026-06-18 10:56:16'),(28,1,'investment','Investment Terminated','Your investment in Test Real Estate Fund has been terminated. $300.00 (capital + interest) has been returned to your wallet.',NULL,0,NULL,'2026-06-18 11:04:52');
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_resets` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(191) NOT NULL,
  `token` varchar(128) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_pr_email` (`email`),
  KEY `idx_pr_token` (`token`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_resets`
--

LOCK TABLES `password_resets` WRITE;
/*!40000 ALTER TABLE `password_resets` DISABLE KEYS */;
INSERT INTO `password_resets` VALUES (1,'johndoe_test99@example.com','314a842087eef751902ba428136b46d7876bd5075d16cd83beaeece50f716a2f','2026-06-17 19:42:41',0,'2026-06-17 19:12:41');
/*!40000 ALTER TABLE `password_resets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payout_schedules`
--

DROP TABLE IF EXISTS `payout_schedules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payout_schedules` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `holding_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `due_date` date NOT NULL,
  `paid_at` datetime DEFAULT NULL,
  `status` enum('scheduled','paid','failed') NOT NULL DEFAULT 'scheduled',
  `tx_id` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_holding_due` (`holding_id`,`due_date`),
  KEY `idx_ps_user` (`user_id`),
  KEY `idx_ps_due` (`due_date`),
  KEY `idx_ps_status` (`status`),
  KEY `fk_ps_tx` (`tx_id`),
  CONSTRAINT `fk_ps_holding` FOREIGN KEY (`holding_id`) REFERENCES `investment_holdings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ps_tx` FOREIGN KEY (`tx_id`) REFERENCES `transactions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_ps_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payout_schedules`
--

LOCK TABLES `payout_schedules` WRITE;
/*!40000 ALTER TABLE `payout_schedules` DISABLE KEYS */;
/*!40000 ALTER TABLE `payout_schedules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `platform_settings`
--

DROP TABLE IF EXISTS `platform_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `platform_settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_group` varchar(50) NOT NULL DEFAULT 'general',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=135 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `platform_settings`
--

LOCK TABLES `platform_settings` WRITE;
/*!40000 ALTER TABLE `platform_settings` DISABLE KEYS */;
INSERT INTO `platform_settings` VALUES (1,'platform_name','NexVest','branding','2026-06-17 19:24:53'),(2,'platform_tagline','Capital Group','branding','2026-06-17 18:15:39'),(3,'platform_initials','NV','branding','2026-06-17 18:15:39'),(4,'platform_logo',NULL,'branding','2026-06-17 18:01:34'),(5,'platform_email','admin@nexvest.com','branding','2026-06-17 19:24:53'),(6,'platform_support_email','support@nexvest.com','branding','2026-06-17 18:01:34'),(7,'platform_phone','+1 (212) 555-0190','branding','2026-06-17 18:15:39'),(8,'platform_address','350 Fifth Avenue, New York, NY 10118, USA','branding','2026-06-17 18:15:39'),(9,'platform_website','https://nexvest.com','branding','2026-06-17 18:15:39'),(10,'platform_currency','USD','finance','2026-06-17 18:15:39'),(11,'platform_symbol','$','finance','2026-06-17 18:15:39'),(12,'kyc_enabled','1','features','2026-06-17 19:24:53'),(13,'two_fa_enabled','0','features','2026-06-17 19:06:21'),(14,'registration_open','1','features','2026-06-17 19:24:53'),(15,'maintenance_mode','0','features','2026-06-17 18:15:39'),(16,'email_verification_enabled','0','features','2026-06-17 18:15:39'),(17,'payment_crypto','1','payments','2026-06-17 19:24:53'),(18,'payment_paypal','1','payments','2026-06-17 19:24:53'),(19,'payment_wire','1','payments','2026-06-17 19:24:53'),(20,'crypto_btc_address','sdbnsavn','payments','2026-06-17 18:15:39'),(21,'crypto_eth_address','dbndndbndndbnd','payments','2026-06-17 18:15:39'),(22,'crypto_usdt_address','TTestWalletAddress123456789','payments','2026-06-17 18:45:15'),(23,'crypto_usdc_address','sjhdyudhdhdjhd','payments','2026-06-17 18:15:39'),(24,'paypal_email','liamnet1998@gmail.com','payments','2026-06-17 18:15:39'),(25,'paypal_me_link','http://localhost/admin/settings','payments','2026-06-17 18:15:39'),(26,'wire_bank_name','Citibank N.A.','payments','2026-06-17 18:15:39'),(27,'wire_account_name','NexVest Capital LLC','payments','2026-06-17 18:15:39'),(28,'wire_account_number','4821 0093 7751','payments','2026-06-17 18:15:39'),(29,'wire_routing','021000089','payments','2026-06-17 18:15:39'),(30,'wire_swift','CITIUS33','payments','2026-06-17 18:15:39'),(31,'wire_bank_country','United States','payments','2026-06-17 18:15:39'),(32,'referral_commission','5','referrals','2026-06-17 19:24:53'),(33,'deposit_timeout','1800','payments','2026-06-17 19:24:53'),(34,'smtp_host','smtp.nexvest.com','email','2026-06-17 18:15:39'),(35,'smtp_port','587','email','2026-06-17 18:15:39'),(36,'smtp_user','noreply@nexvest.com','email','2026-06-17 18:15:39'),(37,'smtp_pass','','email','2026-06-17 18:15:39'),(38,'smtp_secure','tls','email','2026-06-17 18:15:39'),(39,'smtp_from_name','NexVest Capital Group','email','2026-06-17 18:15:39'),(122,'min_deposit','100','payments','2026-06-17 19:24:53'),(123,'min_withdrawal','50','payments','2026-06-17 19:24:53');
/*!40000 ALTER TABLE `platform_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `referrals`
--

DROP TABLE IF EXISTS `referrals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `referrals` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `referrer_id` int(10) unsigned NOT NULL,
  `referred_id` int(10) unsigned NOT NULL,
  `commission_rate` decimal(5,2) NOT NULL,
  `commission_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `status` enum('registered','invested','commission_paid') NOT NULL DEFAULT 'registered',
  `invested_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `paid_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_referral_pair` (`referrer_id`,`referred_id`),
  KEY `idx_ref_referrer` (`referrer_id`),
  KEY `idx_ref_referred` (`referred_id`),
  CONSTRAINT `fk_ref_referred` FOREIGN KEY (`referred_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ref_referrer` FOREIGN KEY (`referrer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `referrals`
--

LOCK TABLES `referrals` WRITE;
/*!40000 ALTER TABLE `referrals` DISABLE KEYS */;
/*!40000 ALTER TABLE `referrals` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `support_tickets`
--

DROP TABLE IF EXISTS `support_tickets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `support_tickets` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `reference` varchar(12) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `status` enum('open','in_progress','resolved','closed') NOT NULL DEFAULT 'open',
  `priority` enum('low','medium','high','urgent') NOT NULL DEFAULT 'medium',
  `assigned_to` int(10) unsigned DEFAULT NULL,
  `closed_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_ticket_ref` (`reference`),
  KEY `idx_st_user` (`user_id`),
  KEY `idx_st_status` (`status`),
  KEY `fk_st_admin` (`assigned_to`),
  CONSTRAINT `fk_st_admin` FOREIGN KEY (`assigned_to`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_st_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `support_tickets`
--

LOCK TABLES `support_tickets` WRITE;
/*!40000 ALTER TABLE `support_tickets` DISABLE KEYS */;
INSERT INTO `support_tickets` VALUES (1,1,'TKT-1F9AC4','Test Ticket','in_progress','medium',NULL,'2026-06-17 19:53:33','2026-06-17 18:53:32','2026-06-17 18:57:19'),(2,1,'TKT-260097','Open Ticket Test','open','medium',NULL,NULL,'2026-06-17 19:00:39','2026-06-17 19:00:39');
/*!40000 ALTER TABLE `support_tickets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ticket_messages`
--

DROP TABLE IF EXISTS `ticket_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ticket_messages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ticket_id` int(10) unsigned NOT NULL,
  `sender_type` enum('user','admin') NOT NULL,
  `sender_id` int(10) unsigned NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_tm_ticket` (`ticket_id`),
  CONSTRAINT `fk_tm_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `support_tickets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ticket_messages`
--

LOCK TABLES `ticket_messages` WRITE;
/*!40000 ALTER TABLE `ticket_messages` DISABLE KEYS */;
INSERT INTO `ticket_messages` VALUES (1,1,'user',1,'This is a test support message','2026-06-17 18:53:32'),(2,1,'admin',1,'Admin reply test','2026-06-17 18:57:19'),(3,2,'user',1,'Testing replies','2026-06-17 19:00:39'),(4,2,'user',1,'This is my reply message','2026-06-17 19:00:39');
/*!40000 ALTER TABLE `ticket_messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transactions`
--

DROP TABLE IF EXISTS `transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `transactions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `type` enum('deposit','withdrawal','investment','return','referral_commission','adjustment') NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `balance_before` decimal(15,2) NOT NULL,
  `balance_after` decimal(15,2) NOT NULL,
  `status` enum('pending','completed','failed','rejected') NOT NULL DEFAULT 'pending',
  `method` varchar(50) DEFAULT NULL,
  `reference` varchar(60) NOT NULL,
  `description` varchar(500) DEFAULT NULL,
  `holding_id` int(10) unsigned DEFAULT NULL,
  `admin_note` text DEFAULT NULL,
  `processed_by` int(10) unsigned DEFAULT NULL,
  `processed_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_tx_reference` (`reference`),
  KEY `idx_tx_user` (`user_id`),
  KEY `idx_tx_type` (`type`),
  KEY `idx_tx_status` (`status`),
  KEY `idx_tx_holding` (`holding_id`),
  KEY `fk_tx_admin` (`processed_by`),
  CONSTRAINT `fk_tx_admin` FOREIGN KEY (`processed_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_tx_holding` FOREIGN KEY (`holding_id`) REFERENCES `investment_holdings` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_tx_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transactions`
--

LOCK TABLES `transactions` WRITE;
/*!40000 ALTER TABLE `transactions` DISABLE KEYS */;
INSERT INTO `transactions` VALUES (1,1,'investment',500.00,5000.00,4500.00,'completed','wallet','NV-021D9A','Investment: Test Real Estate Fund',NULL,NULL,NULL,NULL,'2026-06-17 18:39:17','2026-06-17 18:39:17'),(2,1,'deposit',300.00,4500.00,4800.00,'completed','crypto','DEP-D7F928','Deposit confirmed by admin',NULL,NULL,1,'2026-06-17 19:45:20','2026-06-17 18:45:20','2026-06-17 18:45:20'),(3,1,'deposit',500.00,4800.00,5300.00,'completed','wire','DEP-D74115','Deposit confirmed by admin',NULL,NULL,1,'2026-06-17 19:47:09','2026-06-17 18:47:09','2026-06-17 18:47:09'),(4,1,'investment',100.00,5300.00,5200.00,'completed','wallet','NV-90F8FA','Investment: Test Real Estate Fund',NULL,NULL,NULL,NULL,'2026-06-17 18:53:31','2026-06-17 18:53:31'),(5,1,'adjustment',100.00,5200.00,5300.00,'completed',NULL,'ADJ-FB4764','Manual Credit by admin',NULL,'Test credit',1,'2026-06-17 19:53:32','2026-06-17 18:53:32','2026-06-17 18:53:32'),(6,1,'withdrawal',100.00,5300.00,5200.00,'completed','wire','WD-594D53','Withdrawal request',NULL,NULL,1,'2026-06-18 11:56:16','2026-06-17 18:57:18','2026-06-18 10:56:16'),(7,1,'withdrawal',50.00,5200.00,5150.00,'rejected','paypal','WD-F3D8E0','Withdrawal request',NULL,'',NULL,NULL,'2026-06-17 18:57:18','2026-06-17 19:24:52'),(8,1,'withdrawal',50.00,5150.00,5100.00,'completed','crypto','WD-CC6E3D','Withdrawal request',NULL,NULL,1,'2026-06-17 19:57:19','2026-06-17 18:57:18','2026-06-17 18:57:19'),(9,1,'deposit',500.00,5150.00,5650.00,'completed','wire','DEP-C317D1','Deposit confirmed by admin',NULL,NULL,1,'2026-06-17 22:02:54','2026-06-17 21:02:54','2026-06-17 21:02:54'),(10,1,'deposit',300.00,5650.00,5950.00,'completed','wire','DEP-EFA874','Deposit confirmed by admin',NULL,NULL,1,'2026-06-18 11:52:05','2026-06-18 10:52:05','2026-06-18 10:52:05'),(11,1,'investment',300.00,5950.00,5950.00,'completed','wire','DEP-ADF0E1','Investment confirmed: Test Real Estate Fund',NULL,NULL,1,'2026-06-18 11:52:38','2026-06-18 10:52:38','2026-06-18 10:52:38'),(12,1,'withdrawal',300.00,5950.00,6250.00,'completed',NULL,'TRM-516E1D','Early termination — Test Real Estate Fund (capital + interest)',NULL,NULL,NULL,NULL,'2026-06-18 11:04:52','2026-06-18 11:04:52');
/*!40000 ALTER TABLE `transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `two_fa_backup_codes`
--

DROP TABLE IF EXISTS `two_fa_backup_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `two_fa_backup_codes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `code` varchar(20) NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT 0,
  `used_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_2fa_user` (`user_id`),
  CONSTRAINT `fk_2fa_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `two_fa_backup_codes`
--

LOCK TABLES `two_fa_backup_codes` WRITE;
/*!40000 ALTER TABLE `two_fa_backup_codes` DISABLE KEYS */;
/*!40000 ALTER TABLE `two_fa_backup_codes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_sessions`
--

DROP TABLE IF EXISTS `user_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_sessions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `session_token` varchar(128) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `device` varchar(120) DEFAULT NULL,
  `last_active` datetime NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_session_token` (`session_token`),
  KEY `idx_us_user` (`user_id`),
  CONSTRAINT `fk_us_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=53 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_sessions`
--

LOCK TABLES `user_sessions` WRITE;
/*!40000 ALTER TABLE `user_sessions` DISABLE KEYS */;
INSERT INTO `user_sessions` VALUES (1,1,'ee86dc53e66f7dc4eda1bd4455edff12bf1dbd4d26757b34c6eb18af58e73d4a','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',NULL,'2026-06-17 19:11:14','2026-06-17 18:13:14','2026-06-17 18:11:14'),(2,1,'288c1e7b9845ae15e3ec100f5fe2778cd92d327936541a2c84b10576750c5030','::1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456',NULL,'2026-06-17 19:26:29','2026-06-17 18:28:29','2026-06-17 18:26:29'),(3,1,'292ce7b950c42ac140c666733753abc1232899b43efca9ecfb28d0628b790c00','::1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456',NULL,'2026-06-17 19:27:42','2026-06-17 18:29:42','2026-06-17 18:27:42'),(4,1,'d0579a0ee23ed69307bd078eb93722f73c37ab4d2a858b1c6a551ca0396ed9db','::1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456',NULL,'2026-06-17 19:35:31','2026-06-17 18:37:31','2026-06-17 18:35:31'),(5,1,'7e0ea1fac61e9f1062cb490ced6c6c272ab4d8b52010569d7b46a42ca9f0c778','::1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456',NULL,'2026-06-17 19:36:25','2026-06-17 18:38:25','2026-06-17 18:36:25'),(6,1,'ea5c7e62fb67cd76c902d95a2d377090ea72c4ad1d524503f73c13ee84528af7','::1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456',NULL,'2026-06-17 19:38:59','2026-06-17 18:40:59','2026-06-17 18:38:59'),(7,1,'1cde06c105b5e9557944828a3962b97b0ce6ce2c86c33acc5b33dbed54371db7','::1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456',NULL,'2026-06-17 19:39:16','2026-06-17 18:41:16','2026-06-17 18:39:16'),(8,1,'5ee69089ea36fcd70e580e846f78a72609f4f6c1e5308bb412cc34347470a305','::1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456',NULL,'2026-06-17 19:42:29','2026-06-17 18:44:29','2026-06-17 18:42:29'),(9,1,'2576a681934f017bef46ce66daa980fa81fd8241638ae187ab64dd5a61034174','::1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456',NULL,'2026-06-17 19:43:55','2026-06-17 18:45:55','2026-06-17 18:43:55'),(10,1,'dbef41e31fd9e4a28be88df1841534737b47bb429890c39aaa17fcfbc380fb24','::1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456',NULL,'2026-06-17 19:44:42','2026-06-17 18:46:42','2026-06-17 18:44:42'),(11,1,'05c4e629f0dd6d3fe8bf8b315be50bf10086b59d3192a29a4463867a012a110c','::1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456',NULL,'2026-06-17 19:45:16','2026-06-17 18:47:16','2026-06-17 18:45:16'),(12,1,'3a5a2bcfe6dd0088d6c7ea4b942b00a23c6f0f93dd506eb35d75aaf7bed5374e','::1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456',NULL,'2026-06-17 19:52:54','2026-06-17 18:54:54','2026-06-17 18:52:54'),(13,1,'94e2a89020ad3a0dbebe4ca205747d56fa8452f8eea579bdc77b7db135256351','::1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456',NULL,'2026-06-17 19:53:29','2026-06-17 18:55:29','2026-06-17 18:53:29'),(14,1,'cab41da4ab5f54fe058dda6dccc45b810679cd31c9306820588c466945e64264','::1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456',NULL,'2026-06-17 19:55:37','2026-06-17 18:57:37','2026-06-17 18:55:37'),(15,1,'08d1f05a8d2416335abbf4c8bd97b62dc55e10dde85912218e1d0eaf002fb1b1','::1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456',NULL,'2026-06-17 19:57:17','2026-06-17 18:59:17','2026-06-17 18:57:17'),(16,2,'00dceb9c8f53e058d117e910365808b73a402ef6637b7634d87c05dd84ab566e','::1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456',NULL,'2026-06-17 20:00:38','2026-06-17 19:02:38','2026-06-17 19:00:38'),(17,1,'cccaced1d7cf5c20f9fe54d459a48a375d4282ef8ad29463775f4df1f5d8fa4f','::1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456',NULL,'2026-06-17 20:00:39','2026-06-17 19:02:39','2026-06-17 19:00:39'),(18,1,'3335174bacfbb6ab0bfcefb4135f87e2f54659a5da713cec9fd36f72c2b12f95','::1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456',NULL,'2026-06-17 20:01:30','2026-06-17 19:03:30','2026-06-17 19:01:30'),(19,1,'f7cded07b9338da9cbc832f42ef1fada236887c427dbdab5febfb0e5267b1cb0','::1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456',NULL,'2026-06-17 20:05:02','2026-06-17 19:07:02','2026-06-17 19:05:02'),(20,1,'c640e7a676b40b0f32360188576c38e1d37a4d67690c57b4c9444c1d96a55a70','::1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456',NULL,'2026-06-17 20:10:42','2026-06-17 19:12:42','2026-06-17 19:10:42'),(21,1,'7eee0f8f2eb6fc06568469e4e6ebf26ee4600e38e000185fc785a69a8115b175','::1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456',NULL,'2026-06-17 20:12:39','2026-06-17 19:14:39','2026-06-17 19:12:39'),(22,2,'3f68d70683fbe80f9c5fe97ca1b259b6ea163a675c513af54176cb2ed1fd9775','::1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456',NULL,'2026-06-17 20:12:40','2026-06-17 19:14:40','2026-06-17 19:12:40'),(23,2,'39e0740118ebe17292da72c2867969583403bd9d210c053ad808233e51152172','::1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456',NULL,'2026-06-17 20:16:57','2026-06-17 19:18:57','2026-06-17 19:16:57'),(24,1,'281fd151667a66a18d31b35210eed50c75cef37640f537a9e4f92cc9ef705972','::1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456',NULL,'2026-06-17 20:25:45','2026-06-17 19:27:45','2026-06-17 19:25:45'),(25,1,'1b38d951ec5b1cf5ccc0776cc3d73b1cd6fefa109631907860d498bfc7d0c216','::1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456',NULL,'2026-06-17 20:29:56','2026-06-17 19:31:56','2026-06-17 19:29:56'),(26,1,'038515529596b50c9793dd5dbfbb8cdc274e15567c4a50eecceeb0815e4f9203','::1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456',NULL,'2026-06-17 20:34:32','2026-06-17 19:36:32','2026-06-17 19:34:32'),(27,1,'3ecb004786a20b67c66205234e4a70eed222aa5134ce18cdac3191faf3b8306b','::1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456',NULL,'2026-06-17 22:02:52','2026-06-17 21:04:52','2026-06-17 21:02:52'),(28,1,'a514929ef5367eafb967761649c77ced4fdbb4356d15cee48ab26c0f966b4172','::1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456',NULL,'2026-06-17 22:03:20','2026-06-17 21:05:20','2026-06-17 21:03:20'),(29,1,'bf42f60792d4ab67af3b0f55cec75c99aa685b76c3e6252f976f1a89648e47ed','::1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456',NULL,'2026-06-17 22:03:50','2026-06-17 21:05:50','2026-06-17 21:03:50'),(30,1,'24ead958169c6730a4c8fb84795c4f7a2cee864bba35c13c8ca20baf95373dec','::1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456',NULL,'2026-06-17 22:35:50','2026-06-17 21:37:50','2026-06-17 21:35:50'),(31,1,'6fd8af76861b1a379727dc058b44c49afb55803823455e98f7f6ff95d80504ea','::1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456',NULL,'2026-06-17 22:36:12','2026-06-17 21:38:12','2026-06-17 21:36:12'),(32,1,'7a82e5501bb87189dce8ef604dc1c4fa48d5eacd9ea228954768c3d03dda2fbe','::1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456',NULL,'2026-06-17 22:36:41','2026-06-17 21:38:41','2026-06-17 21:36:41'),(33,1,'8bb1282ee71431d8d5c74ca21bb59b57f858da5e4fe723f2fdeee4c70f6ddcd3','::1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456',NULL,'2026-06-17 22:37:07','2026-06-17 21:39:07','2026-06-17 21:37:07'),(34,1,'05edb668d0284a0304919f5fe50ae36c6d666559997f7a37f0fd75ca8caa53fd','::1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456',NULL,'2026-06-17 22:38:04','2026-06-17 21:40:04','2026-06-17 21:38:04'),(35,1,'9f23922b3146e62326df32eb29c515129ec10f8b6e83a8089d561ec0e92d8bde','::1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456',NULL,'2026-06-17 22:38:30','2026-06-17 21:40:30','2026-06-17 21:38:30'),(36,1,'c5d943a2a6ee878d5adfe8fce1408a058a1d0812d319b594db5e9178f0f61eeb','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',NULL,'2026-06-17 22:47:44','2026-06-17 21:49:44','2026-06-17 21:47:44'),(37,1,'8e19f23b7f7127556e344628314bbd10c22fd07f2a855f3159769beab2b7b457','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',NULL,'2026-06-17 22:50:50','2026-06-17 21:52:50','2026-06-17 21:50:50'),(38,1,'bfc4fabab2de3cde04b3bc3af151c5d6d31c1814ee82dee0150e5033b9b3f538','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',NULL,'2026-06-17 22:54:13','2026-06-17 21:56:13','2026-06-17 21:54:13'),(39,1,'c82c05f37e60e8812703439e0a97390de96d4014bddfa599a2f1f079e2eda8b8','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',NULL,'2026-06-17 22:56:22','2026-06-17 21:58:22','2026-06-17 21:56:22'),(40,1,'2d9fb09de9456d39f49bade9ab6f70894e4fa35d1716be02fe4fb79ccc8d37e7','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',NULL,'2026-06-17 22:58:35','2026-06-17 22:00:35','2026-06-17 21:58:35'),(41,1,'d2cd28f32f8b44eed9545e527416c91ba300bf61969dd57b5e8bb54a16931bf1','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',NULL,'2026-06-17 23:02:11','2026-06-17 22:04:11','2026-06-17 22:02:11'),(42,1,'269bdf3c926e65c6bd5742e66c2a7df301a3f9c7b27d50daa8830145c7d880bb','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36',NULL,'2026-06-17 23:20:43','2026-06-17 22:22:43','2026-06-17 22:20:43'),(43,1,'6b8981d084c2444e9f36a399d1cb432417a8d316233592475bb3b651a78efa40','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36',NULL,'2026-06-18 10:52:04','2026-06-18 09:54:04','2026-06-18 09:52:04'),(44,1,'25b194035e5a211329b9b432af3dd01da4491e534c639fc18ececa48836c0086','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36',NULL,'2026-06-18 10:56:47','2026-06-18 09:58:47','2026-06-18 09:56:47'),(45,1,'c07fd38ea176b0861bf97908833493e41c52dda8cb5f62ac31390cd915b20bbe','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36',NULL,'2026-06-18 11:01:28','2026-06-18 10:03:28','2026-06-18 10:01:28'),(46,1,'3a9babe263b5158cdc339ba098000557efd4aad84424ee4ce2a140ef34387140','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36',NULL,'2026-06-18 11:07:24','2026-06-18 10:09:24','2026-06-18 10:07:24'),(47,1,'863e1495d632d04dc07d3e9a768549cb7dde1e7adda1166fbd00418f7349e54d','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36',NULL,'2026-06-18 11:22:01','2026-06-18 10:24:01','2026-06-18 10:22:01'),(48,1,'178209727f854cd547c9d4ff2a1a38c3375eab08041be92c6be1e10deac2dc3a','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36',NULL,'2026-06-18 11:30:51','2026-06-18 10:32:51','2026-06-18 10:30:51'),(49,1,'ab7e29cd04237ba1de774b2c78ca820fb8e7c59b9f8b4c64f1e722340e77120b','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36',NULL,'2026-06-18 11:32:27','2026-06-18 10:34:27','2026-06-18 10:32:27'),(50,1,'ba04372d666e6a23877f191bd40d1aee5dd7f5d807ad400000b37619394335ed','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36',NULL,'2026-06-18 12:02:59','2026-06-18 11:04:59','2026-06-18 11:02:59'),(51,1,'a212b64e686829a4e810c32bd860ed61342068686e4c714bcf7646c03ce691b2','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36',NULL,'2026-06-18 12:07:54','2026-06-18 11:09:54','2026-06-18 11:07:54'),(52,1,'473cb7c52477d0058b30bea1c304c1c185747f0e548d70f58374641102f79cc3','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36',NULL,'2026-06-18 12:13:23','2026-06-18 11:15:23','2026-06-18 11:13:23');
/*!40000 ALTER TABLE `user_sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `first_name` varchar(80) NOT NULL,
  `last_name` varchar(80) NOT NULL,
  `email` varchar(191) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `referral_code` varchar(20) NOT NULL,
  `referred_by` int(10) unsigned DEFAULT NULL,
  `wallet_balance` decimal(18,2) NOT NULL DEFAULT 0.00,
  `status` enum('active','suspended','banned') NOT NULL DEFAULT 'active',
  `kyc_status` enum('not_submitted','pending','verified','rejected') NOT NULL DEFAULT 'not_submitted',
  `email_verified` tinyint(1) NOT NULL DEFAULT 0,
  `email_verified_at` datetime DEFAULT NULL,
  `two_fa_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `two_fa_secret` varchar(64) DEFAULT NULL,
  `last_login_at` datetime DEFAULT NULL,
  `last_login_ip` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_user_email` (`email`),
  UNIQUE KEY `uq_referral_code` (`referral_code`),
  KEY `idx_referred_by` (`referred_by`),
  KEY `idx_status` (`status`),
  KEY `idx_kyc_status` (`kyc_status`),
  CONSTRAINT `fk_user_referred_by` FOREIGN KEY (`referred_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Liam','Test','liamnet1998@gmail.com','$2y$10$GbyUUfse8hciApBwmKF2Jet3WspqzD2zOP8lhIyac/5vNG37qA6tW','+1234567890','US',NULL,NULL,'NV-DM91C9',NULL,6250.00,'active','verified',1,'2026-06-17 19:11:14',0,'USPVE6KN7E52XL5G','2026-06-18 12:13:23','::1','2026-06-17 18:11:14','2026-06-18 11:13:23'),(2,'John','Doe','johndoe_test99@example.com','$2y$12$zAWWG4JSf3SVI5D373BXAOLRAtC2s5AfD6wsuHntbNGut0xL5nW2K','','US',NULL,NULL,'NV-JD9883',NULL,0.00,'active','not_submitted',1,'2026-06-17 20:00:38',0,NULL,'2026-06-17 20:16:57','::1','2026-06-17 19:00:38','2026-06-17 19:25:47');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `v_investor_summary`
--

DROP TABLE IF EXISTS `v_investor_summary`;
/*!50001 DROP VIEW IF EXISTS `v_investor_summary`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `v_investor_summary` AS SELECT
 1 AS `id`,
  1 AS `full_name`,
  1 AS `email`,
  1 AS `country`,
  1 AS `wallet_balance`,
  1 AS `status`,
  1 AS `kyc_status`,
  1 AS `joined_at`,
  1 AS `active_investments`,
  1 AS `total_invested`,
  1 AS `total_earned`,
  1 AS `total_referrals` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `v_platform_stats`
--

DROP TABLE IF EXISTS `v_platform_stats`;
/*!50001 DROP VIEW IF EXISTS `v_platform_stats`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `v_platform_stats` AS SELECT
 1 AS `total_users`,
  1 AS `active_users`,
  1 AS `kyc_pending`,
  1 AS `total_wallet_balance`,
  1 AS `total_invested`,
  1 AS `total_returns_paid`,
  1 AS `pending_withdrawals`,
  1 AS `open_tickets`,
  1 AS `active_investments` */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `withdrawal_requests`
--

DROP TABLE IF EXISTS `withdrawal_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `withdrawal_requests` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `reference` varchar(20) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `method` enum('crypto','paypal','wire') NOT NULL,
  `details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`details`)),
  `status` enum('pending','approved','completed','rejected') NOT NULL DEFAULT 'pending',
  `rejection_note` text DEFAULT NULL,
  `reviewed_by` int(10) unsigned DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_wr_reference` (`reference`),
  KEY `idx_wr_user` (`user_id`),
  KEY `idx_wr_status` (`status`),
  KEY `fk_wr_admin` (`reviewed_by`),
  CONSTRAINT `fk_wr_admin` FOREIGN KEY (`reviewed_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_wr_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `withdrawal_requests`
--

LOCK TABLES `withdrawal_requests` WRITE;
/*!40000 ALTER TABLE `withdrawal_requests` DISABLE KEYS */;
INSERT INTO `withdrawal_requests` VALUES (1,1,'WD-594D53',100.00,'wire','{\"bank_name\":\"Chase Bank\",\"account_name\":\"Liam Test\",\"account_number\":\"123456789\",\"routing\":\"021000021\",\"bank_address\":\"123 Main St\"}','completed',NULL,1,'2026-06-18 11:56:07','2026-06-18 11:56:16','2026-06-17 18:57:18','2026-06-18 10:56:16'),(2,1,'WD-F3D8E0',50.00,'paypal','{\"paypal_email\":\"liam@test.com\"}','rejected','',1,'2026-06-17 20:24:52',NULL,'2026-06-17 18:57:18','2026-06-17 19:24:52'),(3,1,'WD-CC6E3D',50.00,'crypto','{\"coin\":\"usdt\",\"wallet_address\":\"TTestAddress123\",\"memo\":\"\"}','completed',NULL,1,'2026-06-17 19:57:19','2026-06-17 19:57:19','2026-06-17 18:57:18','2026-06-17 18:57:19');
/*!40000 ALTER TABLE `withdrawal_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `admin_invoices`
--

DROP TABLE IF EXISTS `admin_invoices`;
CREATE TABLE `admin_invoices` (
  `id`             INT UNSIGNED   NOT NULL AUTO_INCREMENT,
  `user_id`        INT UNSIGNED   NOT NULL,
  `admin_id`       INT UNSIGNED   NOT NULL,
  `reference`      VARCHAR(20)    NOT NULL,
  `title`          VARCHAR(180)   NOT NULL,
  `description`    TEXT           DEFAULT NULL,
  `amount`         DECIMAL(15,2)  NOT NULL,
  `due_date`       DATE           NOT NULL,
  `payment_method` ENUM('any','crypto','paypal','wire') NOT NULL DEFAULT 'any',
  `status`         ENUM('pending','paid','cancelled')   NOT NULL DEFAULT 'pending',
  `deposit_ref`    VARCHAR(20)    DEFAULT NULL,
  `paid_at`        DATETIME       DEFAULT NULL,
  `cancelled_at`   DATETIME       DEFAULT NULL,
  `created_at`     TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_ai_ref` (`reference`),
  KEY `idx_ai_user`   (`user_id`),
  KEY `idx_ai_status` (`status`),
  CONSTRAINT `fk_ai_user`  FOREIGN KEY (`user_id`)  REFERENCES `users`  (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ai_admin` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

LOCK TABLES `admin_invoices` WRITE;
/*!40000 ALTER TABLE `admin_invoices` DISABLE KEYS */;
/*!40000 ALTER TABLE `admin_invoices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Final view structure for view `v_investor_summary`
--

/*!50001 DROP VIEW IF EXISTS `v_investor_summary`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_investor_summary` AS select `u`.`id` AS `id`,concat(`u`.`first_name`,' ',`u`.`last_name`) AS `full_name`,`u`.`email` AS `email`,`u`.`country` AS `country`,`u`.`wallet_balance` AS `wallet_balance`,`u`.`status` AS `status`,`u`.`kyc_status` AS `kyc_status`,`u`.`created_at` AS `joined_at`,count(distinct `ih`.`id`) AS `active_investments`,coalesce(sum(`ih`.`amount`),0) AS `total_invested`,coalesce(sum(`ih`.`total_earned`),0) AS `total_earned`,count(distinct `r`.`id`) AS `total_referrals` from ((`users` `u` left join `investment_holdings` `ih` on(`ih`.`user_id` = `u`.`id` and `ih`.`status` = 'active')) left join `referrals` `r` on(`r`.`referrer_id` = `u`.`id`)) group by `u`.`id` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_platform_stats`
--

/*!50001 DROP VIEW IF EXISTS `v_platform_stats`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_platform_stats` AS select (select count(0) from `users`) AS `total_users`,(select count(0) from `users` where `users`.`status` = 'active') AS `active_users`,(select count(0) from `users` where `users`.`kyc_status` = 'pending') AS `kyc_pending`,(select coalesce(sum(`users`.`wallet_balance`),0) from `users`) AS `total_wallet_balance`,(select coalesce(sum(`investment_holdings`.`amount`),0) from `investment_holdings` where `investment_holdings`.`status` = 'active') AS `total_invested`,(select coalesce(sum(`investment_holdings`.`total_earned`),0) from `investment_holdings`) AS `total_returns_paid`,(select count(0) from `withdrawal_requests` where `withdrawal_requests`.`status` = 'pending') AS `pending_withdrawals`,(select count(0) from `support_tickets` where `support_tickets`.`status` = 'open') AS `open_tickets`,(select count(0) from `investments` where `investments`.`status` = 'active') AS `active_investments` */;
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

-- Dump completed on 2026-06-18 12:29:21
