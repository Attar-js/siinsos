-- MySQL dump 10.13  Distrib 8.0.19, for Win64 (x86_64)
--
-- Host: localhost    Database: simkur
-- ------------------------------------------------------
-- Server version	5.5.5-10.11.6-MariaDB-0+deb12u1

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
-- Table structure for table `study_program`
--

DROP TABLE IF EXISTS `study_program`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `study_program` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `department_id` bigint(20) unsigned DEFAULT NULL,
  `name` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`name`)),
  `id_prodi_gerbang` varchar(255) NOT NULL,
  `study_program_type_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `study_program_study_program_type_id_foreign` (`study_program_type_id`),
  KEY `study_program_department_id_foreign` (`department_id`),
  CONSTRAINT `study_program_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`),
  CONSTRAINT `study_program_study_program_type_id_foreign` FOREIGN KEY (`study_program_type_id`) REFERENCES `study_program_types` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `study_program`
--

LOCK TABLES `study_program` WRITE;
/*!40000 ALTER TABLE `study_program` DISABLE KEYS */;
INSERT INTO `study_program` VALUES (1,4,'{\"id\":\"Arsitektur\",\"en\":\"Architecture\"}','44115',1,'2025-07-10 12:57:17','2025-08-29 11:51:11'),(2,2,'{\"id\":\"Bisnis Digital\",\"en\":\"Digital Business\"}','11120',1,'2025-07-10 12:57:17','2025-08-29 11:51:11'),(3,4,'{\"id\":\"Desain Komunikasi Visual\",\"en\":\"Visual Communication Design\"}','44122',1,'2025-07-10 12:57:17','2025-08-29 11:51:11'),(4,1,'{\"id\":\"Fisika\",\"en\":\"Physics\"}','22101',1,'2025-07-10 12:57:17','2025-08-29 11:51:11'),(5,1,'{\"id\":\"Ilmu Aktuaria\",\"en\":\"Actuarial Science \"}','11117',1,'2025-07-10 12:57:17','2025-08-29 11:51:11'),(6,NULL,'{\"id\":\"Inbound Lintas Prodi\",\"en\":\"Inter Field of Study (FoS) Student Exchange Program\"}','99196',1,'2025-07-10 12:57:17','2025-08-19 10:12:28'),(7,NULL,'{\"id\":\"Inbound Permata Merdeka\",\"en\":\"Permata Merdeka Student Exchange Program\"}','99197',1,'2025-07-10 12:57:17','2025-08-19 10:12:28'),(8,NULL,'{\"id\":\"Inbound Permata Sakti\",\"en\":\"Permata Sakti Inbound\"}','99199',1,'2025-07-10 12:57:17','2025-08-19 10:12:28'),(9,NULL,'{\"id\":\"Inbound Pertukaran Mahasiswa Merdeka-DN\",\"en\":\"PMMDN Student Exchange Program\"}','99195',1,'2025-07-10 12:57:17','2025-08-19 10:12:28'),(10,2,'{\"id\":\"Informatika\",\"en\":\"Informatics\"}','11111',1,'2025-07-10 12:57:17','2025-08-29 11:51:11'),(11,NULL,'{\"id\":\"International Mobility\",\"en\":\"International Mobility\"}','88184',1,'2025-07-10 12:57:17','2025-08-19 10:12:28'),(12,NULL,'{\"id\":\"Kredensial Mikro Mahasiswa Indonesia-KMMI\",\"en\":\"Indonesian Student Micro Credentials\"}','99192',1,'2025-07-10 12:57:17','2025-08-19 10:12:28'),(13,1,'{\"id\":\"Matematika\",\"en\":\"Mathematics\"}','11102',1,'2025-07-10 12:57:17','2025-08-29 11:51:11'),(14,NULL,'{\"id\":\"Outbond Indonesia International Student Mobility Awards\",\"en\":\"Outbond Indonesia International Student Mobility Awards\"}','88187',1,'2025-07-10 12:57:17','2025-08-19 10:12:28'),(15,NULL,'{\"id\":\"Outbond Inisiasi Prodi\",\"en\":\"Study Program Initiation Outbound\"}','99188',1,'2025-07-10 12:57:17','2025-08-19 10:12:28'),(16,NULL,'{\"id\":\"Outbound Merdeka Belajar Indonesia Cyber Education\",\"en\":\"Outbound Freedom of Learning Program of Indonesia Cyber Education\"}','99190',1,'2025-07-10 12:57:17','2025-08-19 10:12:28'),(17,NULL,'{\"id\":\"Outbound Merdeka Belajar Indonesia Cyber Education\",\"en\":\"Outbound Freedom of Learning Program of Indonesia Cyber Education\"}','88189',1,'2025-07-10 12:57:17','2025-08-19 10:12:28'),(18,NULL,'{\"id\":\"Outbound Permata Merdeka\",\"en\":\"Outbound Permata Merdeka\"}','99191',1,'2025-07-10 12:57:17','2025-08-19 10:12:28'),(19,NULL,'{\"id\":\"Outbound Permata Sakti\",\"en\":\"Permata Sakti Outbound\"}','99198',1,'2025-07-10 12:57:17','2025-08-19 10:12:28'),(20,NULL,'{\"id\":\"Outbound Pertukaran Mahasiswa Merdeka-DN\",\"en\":\"Outbound Student Exchange Merdeka-DN\"}','99194',1,'2025-07-10 12:57:17','2025-08-19 10:12:28'),(21,NULL,'{\"id\":\"Outbound PKKM\",\"en\":\"Outbound PKKM\"}','99186',1,'2025-07-10 12:57:17','2025-08-19 10:12:28'),(22,NULL,'{\"id\":\"Outbound Wirausaha Mahasiswa Merdeka\",\"en\":\"Outbound Independent Student Entrepreneurship\"}','99185',1,'2025-07-10 12:57:17','2025-08-19 10:12:28'),(23,4,'{\"id\":\"Perencanaan Wilayah dan Kota\",\"en\":\"Urban and Regional Planning\"}','44108',1,'2025-07-10 12:57:17','2025-08-29 11:51:11'),(24,6,'{\"id\":\"Rekayasa Keselamatan\",\"en\":\"Safety Engineering\"}','33118',1,'2025-07-10 12:57:17','2025-08-29 11:51:11'),(25,2,'{\"id\":\"Sistem Informasi\",\"en\":\"Information Systems\"}','11110',1,'2025-07-10 12:57:17','2025-08-29 11:51:11'),(26,1,'{\"id\":\"Statistika\",\"en\":\"Statistics\"}','11116',1,'2025-07-10 12:57:17','2025-08-29 11:51:11'),(27,2,'{\"id\":\"Teknik Elektro\",\"en\":\"Electrical Engineering\"}','33104',1,'2025-07-10 12:57:17','2025-08-29 11:51:11'),(28,5,'{\"id\":\"Teknik Industri\",\"en\":\"Industrial Engineering\"}','33112',1,'2025-07-10 12:57:17','2025-08-29 11:51:11'),(29,3,'{\"id\":\"Teknik Kelautan\",\"en\":\"Ocean Engineering\"}','22114',1,'2025-07-10 12:57:17','2025-08-29 11:51:11'),(30,6,'{\"id\":\"Teknik Kimia\",\"en\":\"Chemical Engineering\"}','33105',1,'2025-07-10 12:57:17','2025-08-29 11:51:11'),(31,3,'{\"id\":\"Teknik Lingkungan\",\"en\":\"Environmental Engineering\"}','55113',1,'2025-07-10 12:57:17','2025-08-29 11:51:11'),(32,5,'{\"id\":\"Teknik Logistik\",\"en\":\"Logistic Engineering\"}','33121',1,'2025-07-10 12:57:17','2025-08-29 11:51:11'),(33,5,'{\"id\":\"Teknik Material dan Metalurgi\",\"en\":\"Materials and Metallurgical Engineering\"}','55106',1,'2025-07-10 12:57:17','2025-08-29 11:51:11'),(34,5,'{\"id\":\"Teknik Mesin\",\"en\":\"Mechanical Engineering\"}','33103',1,'2025-07-10 12:57:17','2025-08-29 11:51:11'),(35,3,'{\"id\":\"Teknik Perkapalan\",\"en\":\"Naval Architecture\"}','22109',1,'2025-07-10 12:57:17','2025-08-29 11:51:11'),(36,4,'{\"id\":\"Teknik Sipil\",\"en\":\"Civil Engineering\"}','44107',1,'2025-07-10 12:57:17','2025-08-29 11:51:11'),(37,6,'{\"id\":\"Teknologi Pangan\",\"en\":\"Food Technology\"}','22119',1,'2025-07-10 12:57:17','2025-08-29 11:51:11'),(38,NULL,'{\"id\":\"Transfer Kredit Earning\",\"en\":\"Credit Transfer Earning\"}','99193',1,'2025-07-10 12:57:17','2025-08-19 10:12:28'),(39,NULL,'{\"id\":\"Tahap Persiapan Bersama\",\"en\":\"Tahap Persiapan Bersama\"}','00000',2,'2025-07-10 12:57:17','2025-08-19 10:12:28'),(40,2,'{\"id\":\"Manajemen Teknologi\",\"en\":\"Technology Management\"}','67201',1,'2025-11-06 21:53:27','2025-11-06 21:53:27'),(41,2,'{\"id\":\"Teknik Biomedis\",\"en\":\"Biomedical Engineering\"}','11125',1,'2026-08-03 00:33:45','2026-08-03 00:33:45'),(42,3,'{\"id\":\"Teknik Sistem Perkapalan\",\"en\":\"Marine Engineering System\"}','22123',1,'2026-08-03 00:33:45','2026-08-03 00:33:45'),(43,3,'{\"id\":\"Teknik Transportasi Laut\",\"en\":\"Marine Transportation Engineering\"}','22124',1,'2026-08-03 00:33:45','2026-08-03 00:33:45');
/*!40000 ALTER TABLE `study_program` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-08-21 14:37:57
