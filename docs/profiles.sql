-- MySQL dump 10.13  Distrib 5.7.23, for Linux (x86_64)
--
-- Host: localhost    Database: netwey
-- ------------------------------------------------------
-- Server version	5.7.23-0ubuntu0.16.04.1

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
-- Table structure for table `islim_profile_details`
--

DROP TABLE IF EXISTS `islim_profile_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `islim_profile_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_profile` int(11) NOT NULL,
  `user_email` text NOT NULL,
  `status` enum('A','I','T') NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `islim_profile_details`
--

LOCK TABLES `islim_profile_details` WRITE;
/*!40000 ALTER TABLE `islim_profile_details` DISABLE KEYS */;
/*!40000 ALTER TABLE `islim_profile_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `islim_profiles`
--

DROP TABLE IF EXISTS `islim_profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `islim_profiles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `description` text,
  `status` enum('A','I','T') NOT NULL,
  `type` enum('master','operation','commercial') NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=latin1 COMMENT='Perfiles de usuario';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `islim_profiles`
--

LOCK TABLES `islim_profiles` WRITE;
/*!40000 ALTER TABLE `islim_profiles` DISABLE KEYS */;
INSERT INTO `islim_profiles` VALUES (1,'Super Usuario','Todos los privilegios dentro de la plataforma. ','A','master'),(2,'Maestro de Usuarios','Gestiona de manera general todos los usuarios registrados. ','A','master'),(3,'Maestro de Bodegas','Gestiona de manera general todas las bodegas registradas. ','A','master'),(4,'Maestro de Productos','Gestiona todos los Servicios, Paquetes, Productos y Detalle de productos registrados.','A','master'),(5,'Maestro de Concentradores','Gestión y asignación de saldo para concentradores.','A','master'),(6,'Operador de Usuarios','Gestiona los Coordinadores y Vendedores pertenecientes a la organización.','A','operation'),(7,'Operador de Inventario','Gestiona el movimiento del inventario dentro de la organización. ','A','operation'),(8,'Operador de Efectivo','Gestiona la conciliación de efectivo de una organización obtenido de las ventas en terreno.','A','operation');
/*!40000 ALTER TABLE `islim_profiles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping events for database 'netwey'
--

--
-- Dumping routines for database 'netwey'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2018-10-08 18:40:24
