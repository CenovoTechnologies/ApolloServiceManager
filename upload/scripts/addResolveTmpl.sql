-- MySQL dump 10.13  Distrib 5.7.9, for Win64 (x86_64)
--
-- Host: localhost    Database: asm
-- ------------------------------------------------------
-- Server version	5.7.13-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping data for table `asm_email_template`
--

LOCK TABLES `asm_email_template` WRITE;
/*!40000 ALTER TABLE `asm_email_template` DISABLE KEYS */;
INSERT INTO `asm_email_template` (`id`, `tpl_id`, `code_name`, `subject`, `body`, `notes`, `created`, `updated`) VALUES (20,1,'ticket.resolve.notice','Ticket %{ticket.number} has been resolved',' <h3><strong>Dear %{recipient.name.first},</strong></h3>Our customer care team has resolved, <a href=\"http://localhost/apollo/upload/scp/%%7Brecipient.ticket_link%7D\">#%{ticket.number}</a> , with the following details and summary: <br /><br />Topic: <strong>%{ticket.topic.name}</strong> <br />Subject: <strong>%{ticket.subject}</strong> <br /><br />%{message} <br /><br />If the incident in question has not been resolved, please let us know as soon as possible. The ticket will close in a few days if there is no response.<br />You can let us know of any issues by contacting the help desk.<br /><br />Your %{company.name} Team,<br />%{signature}',NULL,'2017-03-06 20:47:07','2017-03-06 20:56:10');
/*!40000 ALTER TABLE `asm_email_template` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping events for database 'asm'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2017-03-06 21:47:40
