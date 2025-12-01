-- MariaDB dump 10.19  Distrib 10.4.24-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: thodz
-- ------------------------------------------------------
-- Server version	10.4.24-MariaDB

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
-- Table structure for table `likes`
--

DROP TABLE IF EXISTS `likes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `likes` (
  `lid` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(10) NOT NULL,
  `likes` text NOT NULL,
  `contentid` int(11) NOT NULL,
  PRIMARY KEY (`lid`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `likes`
--

LOCK TABLES `likes` WRITE;
/*!40000 ALTER TABLE `likes` DISABLE KEYS */;
INSERT INTO `likes` VALUES (1,'post','[{\"uid\":1,\"date\":\"2022-05-21 14:42:31\"}]',1),(2,'post','[{\"uid\":2,\"date\":\"2022-05-21 14:43:38\"}]',4),(3,'post','[{\"uid\":2,\"date\":\"2022-05-21 14:43:39\"}]',3),(4,'post','[{\"uid\":3,\"date\":\"2022-05-21 14:45:12\"}]',7),(5,'post','[{\"uid\":3,\"date\":\"2022-05-21 14:45:25\"}]',8),(6,'user','[{\"uid\":1,\"date\":\"2022-05-21 14:45:47\"},{\"uid\":2,\"date\":\"2022-05-21 14:46:57\"},{\"uid\":3,\"date\":\"2022-05-21 15:39:27\"}]',2),(7,'user','[{\"uid\":1,\"date\":\"2022-05-21 14:45:57\"},{\"uid\":3,\"date\":\"2022-05-21 16:14:26\"},{\"uid\":2,\"date\":\"2022-05-28 18:22:41\"}]',3),(8,'user','[{\"uid\":1,\"date\":\"2022-05-21 14:46:09\"},{\"uid\":3,\"date\":\"2022-05-21 15:39:14\"}]',1),(9,'post','[{\"uid\":1,\"date\":\"2022-05-23 18:21:34\"}]',9);
/*!40000 ALTER TABLE `likes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `messages`
--

DROP TABLE IF EXISTS `messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `messages` (
  `mid` int(11) NOT NULL AUTO_INCREMENT,
  `incoming_msg_id` int(11) NOT NULL,
  `outgoing_msg_id` int(11) NOT NULL,
  `msg` text NOT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`mid`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `messages`
--

LOCK TABLES `messages` WRITE;
/*!40000 ALTER TABLE `messages` DISABLE KEYS */;
INSERT INTO `messages` VALUES (1,2,1,'hello','2022-05-27 16:07:43'),(2,2,1,'what are you doing','2022-05-27 16:48:29'),(3,2,1,'doing fine','2022-05-27 16:49:52'),(4,2,1,'?','2022-05-27 16:50:48'),(5,2,1,'.','2022-05-27 16:53:36'),(6,1,2,'hola','2022-05-27 17:05:37'),(7,1,2,'i&#039;m doing fine','2022-05-27 17:06:06'),(8,1,2,'what about you ?','2022-05-27 17:07:14'),(9,2,1,'fine','2022-05-27 17:09:01'),(10,1,2,'we are making great advance in our chat','2022-05-27 17:10:14'),(11,2,1,'yeah i see','2022-05-27 17:10:43'),(12,2,1,'did it scroll down when you get the message','2022-05-27 17:12:14'),(13,1,2,'yes it work fine','2022-05-27 17:13:00'),(14,1,2,'we need now to get the message crypted','2022-05-27 17:14:51'),(15,2,1,'ok let&#039;s work on it tomorrow i&#039;m so tired','2022-05-27 17:15:39');
/*!40000 ALTER TABLE `messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `posts`
--

DROP TABLE IF EXISTS `posts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `posts` (
  `pid` int(11) NOT NULL AUTO_INCREMENT,
  `post` text NOT NULL,
  `postimg` varchar(256) NOT NULL,
  `has_image` tinyint(1) NOT NULL,
  `is_profileimg` tinyint(1) NOT NULL,
  `parent` int(11) NOT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp(),
  `owner` int(11) NOT NULL,
  `likes` int(11) NOT NULL,
  `comments` int(11) NOT NULL,
  PRIMARY KEY (`pid`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `posts`
--

LOCK TABLES `posts` WRITE;
/*!40000 ALTER TABLE `posts` DISABLE KEYS */;
INSERT INTO `posts` VALUES (1,'','THODZ_6288de33c464f6.53517784.jpg',1,1,0,'2022-05-21 12:42:28',1,1,0),(2,'hello there','',0,0,0,'2022-05-21 12:42:40',1,0,0),(3,'','THODZ_6288de6a0a9555.11391244.jpg',1,1,0,'2022-05-21 12:43:22',2,1,0),(4,'my desktop','THODZ_6288de74d64d46.50940759.jpg',1,0,0,'2022-05-21 12:43:33',2,1,0),(5,'','THODZ_6288dea30113b9.18284055.jpg',1,1,0,'2022-05-21 12:44:19',3,0,0),(6,'','THODZ_6288dec9ce97a7.08726286.jpg',1,1,0,'2022-05-21 12:44:58',3,0,0),(7,'','THODZ_6288decddea9f9.89234520.jpg',1,0,0,'2022-05-21 12:45:02',3,1,1),(8,'University','',0,0,7,'2022-05-21 12:45:20',3,1,0),(9,'','THODZ_6288edd4e58e93.18530051.jpg',1,1,0,'2022-05-21 13:49:09',3,1,0);
/*!40000 ALTER TABLE `posts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `fname` varchar(20) NOT NULL,
  `lname` varchar(20) NOT NULL,
  `gender` varchar(10) NOT NULL,
  `email` varchar(256) NOT NULL,
  `password` varchar(256) NOT NULL,
  `profileimg` varchar(256) NOT NULL,
  `isEmailConfirmed` tinyint(1) NOT NULL,
  `token` varchar(10) NOT NULL,
  `likes` int(11) NOT NULL,
  `about` text NOT NULL,
  `status` varchar(8) NOT NULL,
  `datecreate` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Hatem','Telli','male','ramiro-ramos@live.fr','THODZ81dc9bdb52d04dc20036dbd8313ed055','THODZ_6288de33c464f6.53517784.jpg',1,'',2,'','offline','2022-05-21 12:40:45'),(2,'Gladius','Dz','male','azomaki07@gmail.com','THODZ81dc9bdb52d04dc20036dbd8313ed055','THODZ_6288de6a0a9555.11391244.jpg',1,'ew1cfn8*Hi',3,'','offline','2022-05-21 12:41:00'),(3,'Ghoust','Dz','male','7atemtelli@gmail.com','THODZ81dc9bdb52d04dc20036dbd8313ed055','THODZ_6288edd4e58e93.18530051.jpg',1,'',3,'','offline','2022-05-21 12:41:20');
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

-- Dump completed on 2022-05-28 23:29:10
