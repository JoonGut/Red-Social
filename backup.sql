-- MySQL dump 10.13  Distrib 8.0.44, for Win64 (x86_64)
--
-- Host: 18.208.57.228    Database: bd_social
-- ------------------------------------------------------
-- Server version	8.0.44-0ubuntu0.24.04.2

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
-- Table structure for table `chat`
--

DROP TABLE IF EXISTS `chat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `chat` (
  `id_chat` int NOT NULL AUTO_INCREMENT,
  `miembros` int DEFAULT '2',
  `creado_en` datetime DEFAULT CURRENT_TIMESTAMP,
  `nombre` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id_chat`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chat`
--

LOCK TABLES `chat` WRITE;
/*!40000 ALTER TABLE `chat` DISABLE KEYS */;
INSERT INTO `chat` VALUES (1,2,'2026-01-20 09:44:20',NULL),(2,3,'2026-01-21 12:19:43','GRUPO$');
/*!40000 ALTER TABLE `chat` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chat_lectura`
--

DROP TABLE IF EXISTS `chat_lectura`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `chat_lectura` (
  `id_chat` int NOT NULL,
  `id_usuario` int NOT NULL,
  `ultimo_leido_id_mensaje` int DEFAULT '0',
  PRIMARY KEY (`id_chat`,`id_usuario`),
  KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `chat_lectura_ibfk_1` FOREIGN KEY (`id_chat`) REFERENCES `chat` (`id_chat`) ON DELETE CASCADE,
  CONSTRAINT `chat_lectura_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chat_lectura`
--

LOCK TABLES `chat_lectura` WRITE;
/*!40000 ALTER TABLE `chat_lectura` DISABLE KEYS */;
INSERT INTO `chat_lectura` VALUES (1,4,5),(1,6,3),(2,4,10),(2,6,0),(2,7,0);
/*!40000 ALTER TABLE `chat_lectura` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `enviar_mensaje`
--

DROP TABLE IF EXISTS `enviar_mensaje`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `enviar_mensaje` (
  `id_mensaje` int NOT NULL AUTO_INCREMENT,
  `id_chat` int NOT NULL,
  `id_usuario` int NOT NULL,
  `texto` text NOT NULL,
  `creado_en` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_mensaje`),
  KEY `id_chat` (`id_chat`),
  KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `enviar_mensaje_ibfk_1` FOREIGN KEY (`id_chat`) REFERENCES `chat` (`id_chat`) ON DELETE CASCADE,
  CONSTRAINT `enviar_mensaje_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `enviar_mensaje`
--

LOCK TABLES `enviar_mensaje` WRITE;
/*!40000 ALTER TABLE `enviar_mensaje` DISABLE KEYS */;
INSERT INTO `enviar_mensaje` VALUES (1,1,4,'hola','2026-01-20 09:44:24'),(2,1,6,'?><html><body><script>alert(1)</script></body></html><?php','2026-01-21 09:47:02'),(3,1,6,'<script>alert(1)</script>','2026-01-21 09:47:57'),(4,2,4,'Bienvenidos al grupo: GRUPO$','2026-01-21 12:19:44'),(5,1,4,'Hola','2026-01-21 12:23:56'),(6,2,4,'me veis?','2026-01-21 12:24:14'),(7,2,6,'si, te veo!','2026-01-21 12:25:22'),(8,2,7,'no te veo','2026-01-21 12:26:08'),(9,2,7,'hola','2026-01-21 12:26:51'),(10,2,6,'hola','2026-01-21 12:27:09');
/*!40000 ALTER TABLE `enviar_mensaje` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `etiquetas`
--

DROP TABLE IF EXISTS `etiquetas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `etiquetas` (
  `id_etiquetas` int NOT NULL AUTO_INCREMENT,
  `id_usuario` int NOT NULL,
  `id_publicacion` int NOT NULL,
  PRIMARY KEY (`id_etiquetas`),
  KEY `fk_usuario_` (`id_usuario`),
  KEY `fk_publicacion` (`id_publicacion`),
  CONSTRAINT `fk_publicacion` FOREIGN KEY (`id_publicacion`) REFERENCES `publicacion` (`id_publicacion`) ON DELETE CASCADE,
  CONSTRAINT `fk_publicacion_` FOREIGN KEY (`id_publicacion`) REFERENCES `publicacion` (`id_publicacion`),
  CONSTRAINT `fk_usuario_` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `etiquetas`
--

LOCK TABLES `etiquetas` WRITE;
/*!40000 ALTER TABLE `etiquetas` DISABLE KEYS */;
INSERT INTO `etiquetas` VALUES (3,7,91);
/*!40000 ALTER TABLE `etiquetas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `interaccion`
--

DROP TABLE IF EXISTS `interaccion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `interaccion` (
  `id_interaccion` int NOT NULL AUTO_INCREMENT,
  `id_usuario` int NOT NULL,
  `id_publicacion` int NOT NULL,
  `tipo_interaccion` enum('LIKE','COMENTARIO') NOT NULL,
  `comentario` varchar(280) DEFAULT NULL,
  `id_padre` int DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_interaccion`),
  KEY `id_usuario` (`id_usuario`),
  KEY `id_publicacion` (`id_publicacion`),
  KEY `id_padre` (`id_padre`),
  CONSTRAINT `interaccion_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE,
  CONSTRAINT `interaccion_ibfk_2` FOREIGN KEY (`id_publicacion`) REFERENCES `publicacion` (`id_publicacion`) ON DELETE CASCADE,
  CONSTRAINT `interaccion_ibfk_3` FOREIGN KEY (`id_padre`) REFERENCES `interaccion` (`id_interaccion`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `interaccion`
--

LOCK TABLES `interaccion` WRITE;
/*!40000 ALTER TABLE `interaccion` DISABLE KEYS */;
INSERT INTO `interaccion` VALUES (1,4,14,'COMENTARIO','Callabo?',NULL,'2026-01-20 09:49:40'),(2,2,13,'COMENTARIO','noti?',NULL,'2026-01-20 10:05:49'),(3,4,13,'COMENTARIO','si',2,'2026-01-20 10:06:10'),(4,4,48,'COMENTARIO','Si',NULL,'2026-01-21 09:15:45'),(5,4,57,'COMENTARIO','hola',NULL,'2026-01-21 09:47:27'),(6,4,48,'LIKE',NULL,NULL,'2026-01-21 10:09:50'),(7,4,86,'LIKE',NULL,NULL,'2026-01-21 10:18:21');
/*!40000 ALTER TABLE `interaccion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mensaje_privado`
--

DROP TABLE IF EXISTS `mensaje_privado`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mensaje_privado` (
  `id_mensaje_privado` int NOT NULL AUTO_INCREMENT,
  `texto` varchar(250) DEFAULT NULL,
  `id_usuario` int DEFAULT NULL,
  PRIMARY KEY (`id_mensaje_privado`),
  KEY `fk_id_usuario_mensaje` (`id_usuario`),
  CONSTRAINT `fk_id_usuario_mensaje` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mensaje_privado`
--

LOCK TABLES `mensaje_privado` WRITE;
/*!40000 ALTER TABLE `mensaje_privado` DISABLE KEYS */;
/*!40000 ALTER TABLE `mensaje_privado` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mensajes`
--

DROP TABLE IF EXISTS `mensajes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mensajes` (
  `id_mensaje` int NOT NULL AUTO_INCREMENT,
  `id_remitente` int NOT NULL,
  `id_destinatario` int NOT NULL,
  `mensaje` text NOT NULL,
  `leido` tinyint(1) DEFAULT '0',
  `fecha_envio` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_mensaje`),
  KEY `id_remitente` (`id_remitente`),
  KEY `id_destinatario` (`id_destinatario`),
  CONSTRAINT `mensajes_ibfk_1` FOREIGN KEY (`id_remitente`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE,
  CONSTRAINT `mensajes_ibfk_2` FOREIGN KEY (`id_destinatario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mensajes`
--

LOCK TABLES `mensajes` WRITE;
/*!40000 ALTER TABLE `mensajes` DISABLE KEYS */;
/*!40000 ALTER TABLE `mensajes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notificaciones`
--

DROP TABLE IF EXISTS `notificaciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notificaciones` (
  `id_notificacion` int NOT NULL AUTO_INCREMENT,
  `id_usuario` int NOT NULL,
  `id_actor` int NOT NULL,
  `tipo` varchar(50) NOT NULL,
  `referencia_id` int DEFAULT '0',
  `texto_extra` varchar(100) DEFAULT '',
  `leido` tinyint DEFAULT '0',
  `creado_en` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_notificacion`),
  KEY `id_usuario` (`id_usuario`),
  KEY `id_actor` (`id_actor`),
  CONSTRAINT `notificaciones_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE,
  CONSTRAINT `notificaciones_ibfk_2` FOREIGN KEY (`id_actor`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notificaciones`
--

LOCK TABLES `notificaciones` WRITE;
/*!40000 ALTER TABLE `notificaciones` DISABLE KEYS */;
INSERT INTO `notificaciones` VALUES (1,6,4,'mensaje',1,'Hola',1,'2026-01-20 09:30:51'),(2,6,4,'mensaje',1,'hola',1,'2026-01-20 09:44:24'),(3,4,2,'0',13,'0',1,'2026-01-20 10:05:49'),(4,2,4,'0',13,'0',1,'2026-01-20 10:06:10'),(5,6,4,'seguir',0,'Te ha empezado a seguir',1,'2026-01-20 10:18:01'),(6,6,4,'seguir',0,'Te ha empezado a seguir',1,'2026-01-20 10:18:37'),(7,1,4,'seguir',0,'Te ha empezado a seguir',0,'2026-01-20 10:39:53'),(8,88,4,'0',48,'0',0,'2026-01-21 09:15:46'),(9,4,6,'mensaje',1,'?><html><body><scrip...',1,'2026-01-21 09:47:03'),(10,97,4,'0',57,'0',0,'2026-01-21 09:47:27'),(11,4,6,'mensaje',1,'<script>alert(1)</sc...',1,'2026-01-21 09:47:59'),(12,6,4,'etiqueta',87,'te etiquetó en un post',1,'2026-01-21 10:42:15'),(13,7,4,'etiqueta',90,'te etiquetó en un post',1,'2026-01-21 10:56:06'),(14,4,7,'0',90,'0',1,'2026-01-21 10:57:05'),(15,6,4,'mensaje',1,'Hola',1,'2026-01-21 12:23:57'),(16,6,4,'mensaje',2,'me veis?',1,'2026-01-21 12:24:14'),(17,4,6,'mensaje',2,'si, te veo!',1,'2026-01-21 12:25:23'),(18,4,7,'mensaje',2,'no te veo',1,'2026-01-21 12:26:08'),(19,7,6,'etiqueta',91,'te etiquetó en un post',1,'2026-01-21 12:26:17'),(20,4,7,'mensaje',2,'hola',1,'2026-01-21 12:26:52'),(21,4,6,'mensaje',2,'hola',1,'2026-01-21 12:27:10');
/*!40000 ALTER TABLE `notificaciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pertenece_chat`
--

DROP TABLE IF EXISTS `pertenece_chat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pertenece_chat` (
  `id_chat` int NOT NULL,
  `id_usuario` int NOT NULL,
  PRIMARY KEY (`id_chat`,`id_usuario`),
  KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `pertenece_chat_ibfk_1` FOREIGN KEY (`id_chat`) REFERENCES `chat` (`id_chat`) ON DELETE CASCADE,
  CONSTRAINT `pertenece_chat_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pertenece_chat`
--

LOCK TABLES `pertenece_chat` WRITE;
/*!40000 ALTER TABLE `pertenece_chat` DISABLE KEYS */;
INSERT INTO `pertenece_chat` VALUES (1,4),(2,4),(1,6),(2,6),(2,7);
/*!40000 ALTER TABLE `pertenece_chat` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `publicacion`
--

DROP TABLE IF EXISTS `publicacion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `publicacion` (
  `id_publicacion` int NOT NULL AUTO_INCREMENT,
  `id_usuario` int NOT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `fecha_publicacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ubicacion` varchar(250) DEFAULT NULL,
  `pie_foto` varchar(250) DEFAULT NULL,
  `texto` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`id_publicacion`),
  KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `fk_publicacion_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=92 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `publicacion`
--

LOCK TABLES `publicacion` WRITE;
/*!40000 ALTER TABLE `publicacion` DISABLE KEYS */;
INSERT INTO `publicacion` VALUES (5,4,'pub_69678bcb9477c4.91557232.jpg','2026-01-14 12:27:53','Tu corazon','Soltera','Soy guapa?'),(13,4,'pub_6969f50d5689c1.47440665.jpg','2026-01-16 08:21:30','','Historia','He decidido cambiar de foto de perfil. Que os parace?'),(14,6,'pub_696a0e5e4ba5e6.51673945.jpg','2026-01-16 10:09:34','','','Callabo'),(18,7,NULL,'2026-01-19 11:36:32','','','hola'),(20,48,NULL,'2026-01-20 11:29:09',NULL,NULL,'Kakarotto! Vamos a entrenar. ?'),(21,49,NULL,'2026-01-20 11:19:09',NULL,NULL,'Mirad a mi gato durmiendo en el teclado... otra vez. ?'),(22,50,NULL,'2026-01-20 11:04:09',NULL,NULL,'Día de pierna completado. Mañana no podré andar. ??'),(23,51,NULL,'2026-01-20 10:29:09',NULL,NULL,'El secreto de una buena salsa es la paciencia. ?'),(24,52,NULL,'2026-01-20 09:29:09',NULL,NULL,'Casi me rompo un brazo hoy intentando un kickflip. Vale la pena. ?'),(25,54,NULL,'2026-01-20 08:29:09',NULL,NULL,'Bitcoin bajando? MOMENTO DE COMPRAR! ??'),(26,53,NULL,'2026-01-20 07:29:09',NULL,NULL,'Terminando este boceto digital. ¿Qué opinan de los colores? ?'),(27,56,NULL,'2026-01-20 06:29:09',NULL,NULL,'¿Por qué mi código funciona y no sé por qué? El misterio de la programación. ??'),(28,63,NULL,'2026-01-20 05:29:09',NULL,NULL,'Stream on! Jugando Valorant con suscriptores. ??'),(29,69,NULL,'2026-01-20 04:29:09',NULL,NULL,'Cuando el profe dice que el examen es fácil... ?'),(30,72,NULL,'2026-01-20 03:29:09',NULL,NULL,'Tercer café del día y son las 11 AM. Ayuda. ???'),(31,55,NULL,'2026-01-19 11:29:09',NULL,NULL,'Atardecer en Santorini. Sin filtros. ???'),(32,57,NULL,'2026-01-19 11:29:09',NULL,NULL,'Nuevo track subido a Spotify! Link en bio. ??'),(33,60,NULL,'2026-01-19 11:29:09',NULL,NULL,'Acabo de terminar \"Dune\". Increíble world-building. ???'),(34,67,NULL,'2026-01-19 11:29:09',NULL,NULL,'Hamburguesa de lentejas casera. Quien quiere receta? ??'),(35,82,NULL,'2026-01-18 11:29:09',NULL,NULL,'El Samsung S24 Ultra tiene una cámara brutal. Zoom x100 test. ?'),(36,76,NULL,'2026-01-18 11:29:09',NULL,NULL,'Paseo por el parque con Firulais. ??'),(37,80,NULL,'2026-01-18 11:29:09',NULL,NULL,'Viendo \"Hereditary\" por tercera vez. Sigue dando miedo. ???'),(38,85,NULL,'2026-01-17 11:29:09',NULL,NULL,'Esperando el nuevo capítulo de One Piece... ?????'),(39,48,NULL,'2026-01-17 11:29:09',NULL,NULL,'Nadie supera a un Saiyan de élite. ?'),(40,68,NULL,'2026-01-17 11:29:09',NULL,NULL,'Si no estás creando contenido, no existes. ??'),(41,73,NULL,'2026-01-16 11:29:09',NULL,NULL,'Conseguí las Jordan 1 Chicago! Grail acquired. ???'),(42,77,NULL,'2026-01-16 11:29:09',NULL,NULL,'Diseño disponible para tatuar mañana. DM si interesa. ?'),(43,78,NULL,'2026-01-15 11:29:09',NULL,NULL,'Mercurio retrógrado está haciendo de las suyas. Cuidado con los ex. ??'),(44,83,NULL,'2026-01-15 11:29:09',NULL,NULL,'Mis tomates están saliendo por fin! ??'),(45,66,NULL,'2026-01-14 11:29:09',NULL,NULL,'Ese sonido de motor V8... música para mis oídos. ??'),(46,59,NULL,'2026-01-14 11:29:09',NULL,NULL,'Oppenheimer es una obra maestra visual. Nolan lo hizo de nuevo. ??'),(47,61,NULL,'2026-01-13 11:29:09',NULL,NULL,'Levantando ronda de inversión Serie A. Vamos con todo. ??'),(48,88,NULL,'2026-01-20 11:49:38',NULL,NULL,'El solo de November Rain sigue siendo insuperable. ???'),(49,89,NULL,'2026-01-20 11:34:38',NULL,NULL,'Lluvia en la ventana y beats suaves. La paz perfecta. ???'),(50,90,NULL,'2026-01-20 11:19:38',NULL,NULL,'El cine no ha muerto, solo se ha transformado. ???'),(51,91,NULL,'2026-01-20 10:49:38',NULL,NULL,'100 burpees para desayunar. Quién se apunta? ??'),(52,92,NULL,'2026-01-20 09:49:38',NULL,NULL,'El pescado fresco llegó hoy temprano. Preparando el Omakase. ??'),(53,93,NULL,'2026-01-20 09:49:38',NULL,NULL,'Cuando te das cuenta de que mañana es lunes... ??'),(54,94,NULL,'2026-01-20 08:49:38',NULL,NULL,'Perdida en las calles de Kioto. Qué lugar mágico! ????'),(55,95,NULL,'2026-01-20 07:49:38',NULL,NULL,'Primer bug arreglado, aparecen 10 más. Clásico. ??'),(56,96,NULL,'2026-01-20 06:49:38',NULL,NULL,'Encontré esta chaqueta de los 90 en el mercadillo. Joya! ??'),(57,97,NULL,'2026-01-20 05:49:38',NULL,NULL,'Mantengan la calma y HODL. Todo va a subir. ??'),(58,112,NULL,'2026-01-20 04:49:38',NULL,NULL,'Hasta el final! Vamos Real! ???'),(59,113,NULL,'2026-01-20 04:49:38',NULL,NULL,'Visca el Barça! Hoy ganamos seguro. ??'),(60,115,NULL,'2026-01-20 03:49:38',NULL,NULL,'Acabo de instalar refrigeración líquida. Temperaturas bajo cero ????'),(61,118,NULL,'2026-01-20 02:49:38',NULL,NULL,'Ya habéis encontrado todos los Kologs en Zelda? Yo llevo 900... ??'),(62,122,NULL,'2026-01-20 01:49:38',NULL,NULL,'Macarons de frambuesa recién horneados. Cuidado que queman! ??'),(63,123,NULL,'2026-01-20 00:49:38',NULL,NULL,'Doble carne, doble queso, extra bacon. Infarto asegurado pero feliz. ??'),(64,125,NULL,'2026-01-19 23:49:38',NULL,NULL,'Abriendo un Rioja Gran Reserva 2010. Salud! ??'),(65,132,NULL,'2026-01-19 22:49:38',NULL,NULL,'Mi humano piensa que es el jefe. Qué tierno. ??'),(66,135,NULL,'2026-01-19 21:49:38',NULL,NULL,'Lluvia de meteoritos esta noche. Buscad un sitio oscuro! ??'),(67,136,NULL,'2026-01-19 20:49:38',NULL,NULL,'Nadie: \nAbsolutamente nadie: \nNapoleón: Invadamos Rusia en invierno! ???'),(68,140,NULL,'2026-01-19 11:49:38',NULL,NULL,'La gravedad es solo una curvatura del espacio-tiempo. Fácil, no? ??'),(69,144,NULL,'2026-01-19 11:49:38',NULL,NULL,'Estudiar 5 minutos. Descansar 3 horas. El equilibrio perfecto. ???'),(70,145,NULL,'2026-01-19 11:49:38',NULL,NULL,'Quién sale hoy? No acepto un no por respuesta! ??'),(71,106,NULL,'2026-01-19 11:49:38',NULL,NULL,'Juro solemnemente que mis intenciones no son buenas. ??'),(72,108,NULL,'2026-01-18 11:49:38',NULL,NULL,'Wakanda por siempre! ??????'),(73,110,NULL,'2026-01-18 11:49:38',NULL,NULL,'Nuevo MV de Jungkook es ARTE PURO. ??'),(74,120,NULL,'2026-01-18 11:49:38',NULL,NULL,'El ecosistema de Apple es una jaula de oro, pero qué bonita es. ??'),(75,119,NULL,'2026-01-18 11:49:38',NULL,NULL,'Windows ha vuelto a pantallear azul. Por eso uso Linux. ??'),(76,107,NULL,'2026-01-17 11:49:38',NULL,NULL,'Yo soy tu padre. - Noooooo! ??'),(77,127,NULL,'2026-01-17 11:49:38',NULL,NULL,'Nada como un té Matcha para empezar el día con energía. ??'),(78,130,NULL,'2026-01-17 11:49:38',NULL,NULL,'He construido una estantería con palets. Quedó rústica! ??'),(79,133,NULL,'2026-01-16 11:49:38',NULL,NULL,'Hoy aprendimos a dar la pata. Estoy muy orgulloso! ???'),(80,101,NULL,'2026-01-16 11:49:38',NULL,NULL,'Escribiendo el capítulo final. Los aliens ganan? Tal vez... ??'),(81,102,NULL,'2026-01-15 11:49:38',NULL,NULL,'Miles Davis sonando de fondo. Noche perfecta. ??'),(82,104,NULL,'2026-01-13 11:49:38',NULL,NULL,'Pole position! Mañana a por la victoria. ???'),(86,4,'pub_6970a69f10e2a2.77170492.jpg','2026-01-21 10:12:48','','','Hola'),(91,6,NULL,'2026-01-21 12:26:15','','','@JonGut');
/*!40000 ALTER TABLE `publicacion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id_rol` int NOT NULL,
  `rol` varchar(250) NOT NULL,
  PRIMARY KEY (`id_rol`),
  UNIQUE KEY `un_rol` (`rol`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'admin'),(2,'Usuario');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `seguidores`
--

DROP TABLE IF EXISTS `seguidores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `seguidores` (
  `id_seguidor` int NOT NULL,
  `id_usuario` int NOT NULL,
  PRIMARY KEY (`id_usuario`,`id_seguidor`),
  CONSTRAINT `fk_id_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `seguidores`
--

LOCK TABLES `seguidores` WRITE;
/*!40000 ALTER TABLE `seguidores` DISABLE KEYS */;
INSERT INTO `seguidores` VALUES (1,1),(2,1),(6,1),(7,1),(48,1),(49,1),(50,1),(51,1),(52,1),(53,1),(54,1),(55,1),(56,1),(57,1),(58,1),(59,1),(60,1),(61,1),(62,1),(63,1),(64,1),(65,1),(66,1),(67,1),(68,1),(69,1),(70,1),(71,1),(72,1),(73,1),(74,1),(75,1),(76,1),(77,1),(78,1),(79,1),(80,1),(81,1),(82,1),(83,1),(84,1),(85,1),(86,1),(87,1),(88,1),(89,1),(90,1),(91,1),(92,1),(93,1),(94,1),(95,1),(96,1),(97,1),(98,1),(99,1),(100,1),(101,1),(102,1),(103,1),(104,1),(105,1),(106,1),(107,1),(108,1),(109,1),(110,1),(111,1),(112,1),(113,1),(114,1),(115,1),(116,1),(117,1),(118,1),(119,1),(120,1),(121,1),(122,1),(123,1),(124,1),(125,1),(126,1),(127,1),(128,1),(129,1),(130,1),(131,1),(132,1),(133,1),(134,1),(135,1),(136,1),(137,1),(138,1),(139,1),(140,1),(141,1),(142,1),(143,1),(144,1),(145,1),(146,1),(147,1),(1,2),(3,2),(4,2),(5,2),(55,2),(56,2),(57,2),(58,2),(59,2),(62,2),(64,2),(67,2),(87,2),(98,2),(102,2),(112,2),(119,2),(128,2),(131,2),(139,2),(143,2),(2,4),(6,4),(7,4),(49,4),(51,4),(52,4),(55,4),(65,4),(69,4),(71,4),(75,4),(79,4),(80,4),(81,4),(83,4),(88,4),(91,4),(97,4),(100,4),(101,4),(104,4),(109,4),(110,4),(111,4),(116,4),(133,4),(134,4),(140,4),(146,4),(4,6),(7,6),(49,6),(51,6),(52,6),(60,6),(62,6),(64,6),(65,6),(67,6),(69,6),(70,6),(73,6),(80,6),(81,6),(92,6),(105,6),(109,6),(130,6),(141,6),(4,7),(6,7),(49,7),(53,7),(54,7),(62,7),(63,7),(65,7),(66,7),(72,7),(76,7),(79,7),(81,7),(98,7),(111,7),(120,7),(123,7),(126,7),(129,7),(143,7),(2,48),(6,48),(49,48),(51,48),(55,48),(57,48),(58,48),(59,48),(60,48),(61,48),(65,48),(71,48),(76,48),(77,48),(79,48),(81,48),(82,48),(83,48),(84,48),(85,48),(87,48),(88,48),(89,48),(103,48),(108,48),(113,48),(114,48),(132,48),(133,48),(138,48),(141,48),(147,48),(6,49),(7,49),(51,49),(58,49),(62,49),(65,49),(67,49),(70,49),(76,49),(81,49),(82,49),(86,49),(88,49),(96,49),(107,49),(110,49),(114,49),(115,49),(125,49),(132,49),(135,49),(136,49),(143,49),(1,50),(2,50),(4,50),(48,50),(53,50),(55,50),(56,50),(57,50),(58,50),(62,50),(65,50),(66,50),(71,50),(73,50),(74,50),(83,50),(87,50),(91,50),(104,50),(114,50),(121,50),(123,50),(127,50),(131,50),(140,50),(141,50),(142,50),(1,51),(49,51),(52,51),(59,51),(63,51),(66,51),(69,51),(70,51),(71,51),(75,51),(79,51),(81,51),(84,51),(88,51),(92,51),(96,51),(108,51),(111,51),(120,51),(142,51),(4,52),(49,52),(57,52),(61,52),(63,52),(65,52),(66,52),(69,52),(70,52),(75,52),(78,52),(82,52),(85,52),(92,52),(103,52),(109,52),(117,52),(121,52),(133,52),(142,52),(145,52),(147,52),(2,53),(7,53),(54,53),(60,53),(62,53),(65,53),(66,53),(68,53),(74,53),(75,53),(78,53),(79,53),(83,53),(84,53),(95,53),(97,53),(98,53),(115,53),(122,53),(125,53),(147,53),(2,54),(50,54),(57,54),(58,54),(62,54),(64,54),(66,54),(67,54),(68,54),(69,54),(70,54),(71,54),(74,54),(76,54),(81,54),(82,54),(84,54),(85,54),(89,54),(95,54),(109,54),(116,54),(117,54),(126,54),(132,54),(139,54),(1,55),(50,55),(53,55),(54,55),(57,55),(68,55),(79,55),(80,55),(83,55),(84,55),(93,55),(99,55),(100,55),(113,55),(115,55),(116,55),(124,55),(125,55),(128,55),(141,55),(1,56),(6,56),(48,56),(51,56),(52,56),(53,56),(54,56),(60,56),(64,56),(72,56),(74,56),(75,56),(81,56),(83,56),(85,56),(91,56),(102,56),(108,56),(119,56),(123,56),(139,56),(142,56),(146,56),(1,57),(7,57),(54,57),(60,57),(62,57),(71,57),(72,57),(74,57),(75,57),(84,57),(86,57),(88,57),(98,57),(99,57),(114,57),(130,57),(132,57),(134,57),(135,57),(139,57),(140,57),(2,58),(7,58),(49,58),(51,58),(55,58),(59,58),(61,58),(62,58),(66,58),(72,58),(73,58),(78,58),(81,58),(83,58),(86,58),(87,58),(92,58),(118,58),(120,58),(138,58),(48,59),(50,59),(52,59),(53,59),(54,59),(56,59),(57,59),(58,59),(65,59),(69,59),(73,59),(74,59),(75,59),(76,59),(79,59),(82,59),(102,59),(124,59),(127,59),(141,59),(142,59),(145,59),(2,60),(7,60),(49,60),(53,60),(54,60),(57,60),(58,60),(59,60),(63,60),(66,60),(73,60),(74,60),(75,60),(82,60),(83,60),(84,60),(97,60),(111,60),(127,60),(128,60),(1,61),(53,61),(54,61),(56,61),(63,61),(65,61),(71,61),(73,61),(74,61),(75,61),(78,61),(80,61),(81,61),(83,61),(86,61),(87,61),(90,61),(93,61),(94,61),(99,61),(114,61),(122,61),(127,61),(130,61),(132,61),(135,61),(144,61),(2,62),(4,62),(7,62),(57,62),(60,62),(61,62),(63,62),(64,62),(66,62),(70,62),(71,62),(73,62),(74,62),(84,62),(91,62),(92,62),(96,62),(102,62),(103,62),(108,62),(109,62),(113,62),(114,62),(125,62),(134,62),(144,62),(2,63),(52,63),(53,63),(56,63),(59,63),(70,63),(71,63),(72,63),(76,63),(80,63),(81,63),(86,63),(102,63),(106,63),(107,63),(113,63),(114,63),(118,63),(119,63),(124,63),(138,63),(139,63),(141,63),(147,63),(2,64),(48,64),(52,64),(53,64),(55,64),(56,64),(57,64),(60,64),(67,64),(73,64),(77,64),(89,64),(92,64),(97,64),(105,64),(108,64),(110,64),(116,64),(128,64),(129,64),(133,64),(138,64),(139,64),(140,64),(147,64),(6,65),(48,65),(50,65),(53,65),(54,65),(61,65),(63,65),(68,65),(69,65),(70,65),(71,65),(72,65),(76,65),(78,65),(83,65),(96,65),(97,65),(109,65),(118,65),(128,65),(129,65),(135,65),(139,65),(144,65),(4,66),(6,66),(7,66),(49,66),(54,66),(56,66),(62,66),(63,66),(64,66),(65,66),(69,66),(70,66),(72,66),(73,66),(75,66),(78,66),(79,66),(80,66),(85,66),(90,66),(92,66),(94,66),(95,66),(98,66),(99,66),(107,66),(115,66),(120,66),(121,66),(127,66),(131,66),(133,66),(137,66),(139,66),(7,67),(51,67),(53,67),(58,67),(62,67),(64,67),(66,67),(72,67),(75,67),(76,67),(78,67),(81,67),(85,67),(86,67),(91,67),(95,67),(97,67),(101,67),(105,67),(117,67),(132,67),(142,67),(2,68),(49,68),(50,68),(56,68),(57,68),(60,68),(62,68),(65,68),(70,68),(71,68),(81,68),(85,68),(87,68),(88,68),(90,68),(96,68),(98,68),(108,68),(109,68),(117,68),(121,68),(130,68),(134,68),(135,68),(137,68),(143,68),(2,69),(52,69),(60,69),(62,69),(73,69),(75,69),(78,69),(82,69),(83,69),(85,69),(86,69),(94,69),(96,69),(103,69),(111,69),(114,69),(118,69),(122,69),(124,69),(130,69),(1,70),(4,70),(48,70),(52,70),(56,70),(57,70),(58,70),(59,70),(60,70),(62,70),(65,70),(66,70),(68,70),(69,70),(73,70),(75,70),(76,70),(80,70),(83,70),(85,70),(86,70),(96,70),(103,70),(104,70),(106,70),(125,70),(134,70),(143,70),(144,70),(147,70),(48,71),(51,71),(52,71),(54,71),(63,71),(64,71),(67,71),(70,71),(73,71),(85,71),(90,71),(96,71),(103,71),(113,71),(114,71),(118,71),(121,71),(129,71),(138,71),(140,71),(4,72),(7,72),(48,72),(49,72),(53,72),(55,72),(56,72),(58,72),(59,72),(61,72),(62,72),(66,72),(68,72),(69,72),(73,72),(74,72),(77,72),(78,72),(79,72),(81,72),(83,72),(93,72),(97,72),(106,72),(107,72),(110,72),(119,72),(135,72),(4,73),(7,73),(48,73),(49,73),(51,73),(54,73),(58,73),(59,73),(62,73),(63,73),(64,73),(67,73),(70,73),(77,73),(82,73),(86,73),(94,73),(98,73),(114,73),(123,73),(127,73),(136,73),(1,74),(4,74),(50,74),(53,74),(56,74),(59,74),(61,74),(67,74),(68,74),(69,74),(70,74),(73,74),(75,74),(77,74),(79,74),(80,74),(81,74),(84,74),(85,74),(86,74),(91,74),(109,74),(114,74),(124,74),(125,74),(128,74),(137,74),(138,74),(145,74),(1,75),(7,75),(49,75),(50,75),(51,75),(53,75),(54,75),(59,75),(60,75),(67,75),(74,75),(76,75),(81,75),(83,75),(85,75),(95,75),(98,75),(100,75),(107,75),(113,75),(125,75),(133,75),(134,75),(144,75),(147,75),(1,76),(4,76),(6,76),(7,76),(49,76),(54,76),(55,76),(57,76),(60,76),(63,76),(66,76),(67,76),(69,76),(75,76),(78,76),(80,76),(82,76),(84,76),(85,76),(91,76),(101,76),(102,76),(104,76),(111,76),(117,76),(120,76),(125,76),(131,76),(133,76),(134,76),(137,76),(144,76),(147,76),(1,77),(4,77),(48,77),(53,77),(54,77),(59,77),(60,77),(66,77),(72,77),(76,77),(86,77),(92,77),(93,77),(107,77),(114,77),(127,77),(136,77),(141,77),(7,78),(48,78),(51,78),(52,78),(55,78),(57,78),(65,78),(66,78),(69,78),(71,78),(75,78),(80,78),(86,78),(91,78),(129,78),(135,78),(136,78),(139,78),(48,79),(49,79),(51,79),(52,79),(53,79),(54,79),(57,79),(58,79),(74,79),(75,79),(88,79),(94,79),(97,79),(104,79),(108,79),(119,79),(123,79),(127,79),(131,79),(138,79),(49,80),(53,80),(56,80),(60,80),(62,80),(63,80),(64,80),(65,80),(66,80),(67,80),(72,80),(73,80),(75,80),(78,80),(79,80),(92,80),(101,80),(103,80),(111,80),(113,80),(114,80),(117,80),(126,80),(134,80),(138,80),(141,80),(146,80),(4,81),(48,81),(50,81),(51,81),(52,81),(55,81),(56,81),(63,81),(66,81),(72,81),(75,81),(76,81),(77,81),(79,81),(86,81),(97,81),(104,81),(116,81),(118,81),(124,81),(1,82),(2,82),(4,82),(6,82),(48,82),(49,82),(50,82),(51,82),(52,82),(53,82),(60,82),(64,82),(67,82),(75,82),(76,82),(79,82),(81,82),(86,82),(90,82),(101,82),(105,82),(126,82),(134,82),(1,83),(50,83),(51,83),(52,83),(53,83),(59,83),(60,83),(61,83),(67,83),(69,83),(72,83),(74,83),(75,83),(77,83),(85,83),(87,83),(93,83),(100,83),(114,83),(117,83),(120,83),(132,83),(146,83),(4,84),(49,84),(51,84),(54,84),(57,84),(60,84),(61,84),(65,84),(66,84),(67,84),(71,84),(72,84),(73,84),(74,84),(76,84),(109,84),(120,84),(129,84),(133,84),(138,84),(140,84),(144,84),(1,85),(4,85),(50,85),(55,85),(61,85),(63,85),(64,85),(65,85),(71,85),(74,85),(80,85),(81,85),(92,85),(100,85),(106,85),(109,85),(112,85),(115,85),(120,85),(122,85),(123,85),(128,85),(143,85),(144,85),(4,86),(6,86),(48,86),(50,86),(51,86),(53,86),(55,86),(59,86),(63,86),(68,86),(76,86),(77,86),(79,86),(99,86),(106,86),(107,86),(118,86),(120,86),(140,86),(52,87),(59,87),(61,87),(64,87),(65,87),(76,87),(77,87),(78,87),(81,87),(84,87),(91,87),(104,87),(105,87),(106,87),(110,87),(113,87),(116,87),(120,87),(121,87),(129,87),(131,87),(51,88),(55,88),(58,88),(59,88),(76,88),(78,88),(87,88),(89,88),(94,88),(95,88),(99,88),(106,88),(118,88),(119,88),(121,88),(127,88),(133,88),(137,88),(1,89),(7,89),(56,89),(62,89),(73,89),(74,89),(83,89),(85,89),(94,89),(99,89),(102,89),(103,89),(111,89),(112,89),(116,89),(125,89),(128,89),(135,89),(141,89),(142,89),(143,89),(146,89),(4,90),(6,90),(7,90),(56,90),(69,90),(70,90),(74,90),(77,90),(81,90),(91,90),(104,90),(105,90),(107,90),(111,90),(114,90),(123,90),(51,91),(56,91),(64,91),(72,91),(87,91),(88,91),(100,91),(104,91),(113,91),(116,91),(121,91),(122,91),(126,91),(134,91),(140,91),(146,91),(1,92),(53,92),(67,92),(73,92),(84,92),(104,92),(106,92),(114,92),(145,92),(146,92),(49,93),(59,93),(60,93),(61,93),(65,93),(71,93),(79,93),(104,93),(108,93),(109,93),(112,93),(118,93),(130,93),(133,93),(138,93),(141,93),(48,94),(51,94),(55,94),(59,94),(73,94),(78,94),(100,94),(101,94),(102,94),(106,94),(109,94),(119,94),(136,94),(137,94),(6,95),(50,95),(52,95),(54,95),(66,95),(72,95),(79,95),(81,95),(86,95),(116,95),(120,95),(121,95),(124,95),(125,95),(131,95),(139,95),(140,95),(144,95),(2,96),(7,96),(59,96),(60,96),(78,96),(85,96),(90,96),(101,96),(108,96),(129,96),(131,96),(136,96),(141,96),(143,96),(145,96),(52,97),(60,97),(65,97),(69,97),(74,97),(76,97),(80,97),(92,97),(101,97),(112,97),(124,97),(129,97),(135,97),(137,97),(142,97),(143,97),(144,97),(53,98),(60,98),(65,98),(75,98),(80,98),(87,98),(89,98),(101,98),(116,98),(117,98),(135,98),(142,98),(7,99),(52,99),(57,99),(64,99),(65,99),(69,99),(74,99),(85,99),(90,99),(92,99),(98,99),(102,99),(118,99),(121,99),(137,99),(138,99),(139,99),(50,100),(81,100),(82,100),(83,100),(97,100),(102,100),(105,100),(113,100),(120,100),(121,100),(127,100),(132,100),(138,100),(147,100),(48,101),(51,101),(52,101),(54,101),(56,101),(66,101),(78,101),(83,101),(89,101),(98,101),(99,101),(102,101),(115,101),(117,101),(125,101),(144,101),(2,102),(55,102),(71,102),(76,102),(77,102),(83,102),(93,102),(105,102),(111,102),(130,102),(135,102),(146,102),(1,103),(4,103),(50,103),(56,103),(67,103),(68,103),(74,103),(80,103),(81,103),(95,103),(96,103),(101,103),(106,103),(112,103),(116,103),(131,103),(137,103),(138,103),(140,103),(141,103),(142,103),(49,104),(62,104),(63,104),(64,104),(71,104),(72,104),(76,104),(81,104),(90,104),(92,104),(93,104),(95,104),(101,104),(116,104),(121,104),(125,104),(144,104),(65,105),(70,105),(77,105),(84,105),(106,105),(109,105),(111,105),(115,105),(122,105),(136,105),(145,105),(59,106),(82,106),(87,106),(89,106),(90,106),(94,106),(102,106),(110,106),(111,106),(114,106),(116,106),(118,106),(122,106),(128,106),(134,106),(135,106),(138,106),(145,106),(62,107),(67,107),(76,107),(77,107),(79,107),(82,107),(83,107),(86,107),(91,107),(96,107),(104,107),(111,107),(119,107),(122,107),(123,107),(126,107),(130,107),(134,107),(138,107),(141,107),(53,108),(60,108),(68,108),(69,108),(88,108),(90,108),(91,108),(107,108),(113,108),(114,108),(125,108),(134,108),(140,108),(146,108),(6,109),(50,109),(51,109),(57,109),(58,109),(60,109),(66,109),(80,109),(91,109),(94,109),(108,109),(112,109),(123,109),(136,109),(2,110),(53,110),(70,110),(81,110),(86,110),(90,110),(91,110),(93,110),(102,110),(104,110),(109,110),(122,110),(130,110),(147,110),(57,111),(63,111),(84,111),(86,111),(128,111),(131,111),(132,111),(135,111),(138,111),(141,111),(144,111),(7,112),(52,112),(54,112),(63,112),(67,112),(82,112),(91,112),(108,112),(109,112),(123,112),(125,112),(128,112),(129,112),(131,112),(134,112),(143,112),(1,113),(55,113),(61,113),(72,113),(75,113),(84,113),(94,113),(102,113),(103,113),(117,113),(120,113),(142,113),(146,113),(147,113),(4,114),(57,114),(63,114),(75,114),(83,114),(86,114),(92,114),(98,114),(125,114),(141,114),(146,114),(2,115),(56,115),(75,115),(76,115),(78,115),(80,115),(86,115),(116,115),(132,115),(138,115),(52,116),(58,116),(62,116),(71,116),(73,116),(74,116),(77,116),(78,116),(85,116),(87,116),(97,116),(99,116),(102,116),(109,116),(110,116),(115,116),(120,116),(135,116),(137,116),(140,116),(144,116),(145,116),(56,117),(57,117),(58,117),(71,117),(77,117),(112,117),(113,117),(123,117),(125,117),(137,117),(145,117),(1,118),(7,118),(52,118),(63,118),(73,118),(89,118),(96,118),(110,118),(115,118),(120,118),(137,118),(146,118),(147,118),(61,119),(72,119),(73,119),(75,119),(91,119),(96,119),(101,119),(102,119),(111,119),(112,119),(131,119),(142,119),(144,119),(48,120),(63,120),(65,120),(75,120),(93,120),(94,120),(95,120),(97,120),(98,120),(99,120),(102,120),(103,120),(104,120),(107,120),(116,120),(121,120),(125,120),(127,120),(2,121),(4,121),(58,121),(60,121),(68,121),(70,121),(75,121),(84,121),(90,121),(97,121),(101,121),(113,121),(117,121),(131,121),(139,121),(143,121),(52,122),(55,122),(58,122),(68,122),(71,122),(74,122),(75,122),(84,122),(85,122),(89,122),(98,122),(105,122),(118,122),(124,122),(127,122),(132,122),(52,123),(53,123),(55,123),(63,123),(66,123),(80,123),(86,123),(91,123),(94,123),(95,123),(96,123),(99,123),(101,123),(103,123),(114,123),(115,123),(128,123),(135,123),(136,123),(52,124),(56,124),(61,124),(75,124),(80,124),(84,124),(98,124),(114,124),(120,124),(123,124),(129,124),(131,124),(134,124),(135,124),(140,124),(143,124),(50,125),(52,125),(53,125),(58,125),(60,125),(68,125),(69,125),(71,125),(76,125),(87,125),(102,125),(108,125),(110,125),(111,125),(116,125),(133,125),(2,126),(61,126),(62,126),(63,126),(69,126),(78,126),(79,126),(82,126),(88,126),(97,126),(112,126),(115,126),(124,126),(140,126),(144,126),(4,127),(49,127),(54,127),(57,127),(58,127),(61,127),(65,127),(67,127),(71,127),(72,127),(79,127),(100,127),(108,127),(113,127),(121,127),(135,127),(136,127),(76,128),(80,128),(90,128),(91,128),(104,128),(109,128),(129,128),(143,128),(145,128),(4,129),(54,129),(59,129),(69,129),(70,129),(72,129),(75,129),(78,129),(88,129),(92,129),(93,129),(101,129),(107,129),(112,129),(116,129),(127,129),(139,129),(140,129),(54,130),(67,130),(73,130),(80,130),(96,130),(102,130),(105,130),(111,130),(116,130),(118,130),(123,130),(135,130),(137,130),(138,130),(4,131),(66,131),(78,131),(83,131),(86,131),(90,131),(97,131),(112,131),(118,131),(120,131),(137,131),(2,132),(52,132),(65,132),(73,132),(75,132),(88,132),(91,132),(98,132),(105,132),(112,132),(120,132),(145,132),(48,133),(50,133),(59,133),(62,133),(63,133),(73,133),(76,133),(87,133),(99,133),(105,133),(113,133),(116,133),(122,133),(130,133),(136,133),(137,133),(143,133),(144,133),(6,134),(49,134),(54,134),(58,134),(65,134),(68,134),(72,134),(79,134),(89,134),(115,134),(119,134),(121,134),(126,134),(138,134),(139,134),(142,134),(50,135),(53,135),(62,135),(63,135),(75,135),(83,135),(95,135),(100,135),(101,135),(103,135),(106,135),(108,135),(128,135),(141,135),(2,136),(50,136),(58,136),(59,136),(62,136),(67,136),(76,136),(77,136),(79,136),(80,136),(81,136),(82,136),(83,136),(87,136),(88,136),(98,136),(102,136),(119,136),(122,136),(128,136),(140,136),(143,136),(50,137),(58,137),(60,137),(68,137),(70,137),(76,137),(80,137),(88,137),(103,137),(106,137),(118,137),(121,137),(124,137),(127,137),(130,137),(131,137),(141,137),(54,138),(56,138),(57,138),(61,138),(62,138),(64,138),(69,138),(70,138),(71,138),(75,138),(84,138),(86,138),(88,138),(90,138),(96,138),(102,138),(104,138),(105,138),(122,138),(132,138),(139,138),(145,138),(146,138),(4,139),(6,139),(52,139),(63,139),(70,139),(71,139),(76,139),(77,139),(79,139),(81,139),(95,139),(104,139),(108,139),(113,139),(115,139),(123,139),(128,139),(132,139),(140,139),(4,140),(6,140),(52,140),(58,140),(66,140),(70,140),(71,140),(84,140),(85,140),(88,140),(94,140),(98,140),(100,140),(101,140),(109,140),(112,140),(114,140),(121,140),(125,140),(133,140),(134,140),(136,140),(141,140),(146,140),(54,141),(57,141),(64,141),(72,141),(79,141),(80,141),(81,141),(84,141),(92,141),(97,141),(103,141),(105,141),(106,141),(112,141),(115,141),(117,141),(119,141),(124,141),(126,141),(134,141),(143,141),(147,141),(2,142),(48,142),(52,142),(62,142),(69,142),(73,142),(86,142),(103,142),(104,142),(107,142),(118,142),(125,142),(128,142),(134,142),(141,142),(6,143),(49,143),(51,143),(63,143),(82,143),(88,143),(95,143),(96,143),(97,143),(105,143),(107,143),(112,143),(117,143),(132,143),(134,143),(140,143),(141,143),(2,144),(6,144),(48,144),(51,144),(53,144),(60,144),(62,144),(63,144),(65,144),(66,144),(73,144),(75,144),(86,144),(91,144),(95,144),(100,144),(101,144),(104,144),(105,144),(106,144),(108,144),(114,144),(131,144),(136,144),(140,144),(142,144),(2,145),(48,145),(51,145),(63,145),(70,145),(83,145),(85,145),(87,145),(88,145),(89,145),(91,145),(101,145),(106,145),(119,145),(49,146),(51,146),(55,146),(65,146),(67,146),(74,146),(75,146),(77,146),(78,146),(80,146),(83,146),(90,146),(92,146),(93,146),(100,146),(122,146),(124,146),(131,146),(134,146),(140,146),(48,147),(53,147),(54,147),(63,147),(66,147),(77,147),(78,147),(83,147),(95,147),(103,147),(131,147),(143,147);
/*!40000 ALTER TABLE `seguidores` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuario`
--

DROP TABLE IF EXISTS `usuario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuario` (
  `id_usuario` int NOT NULL AUTO_INCREMENT,
  `usuario` varchar(250) NOT NULL,
  `nombre` varchar(250) NOT NULL,
  `password` varchar(250) NOT NULL,
  `email` varchar(250) NOT NULL,
  `foto_perfil` varchar(250) DEFAULT NULL,
  `biografia` varchar(250) DEFAULT NULL,
  `id_rol` int NOT NULL,
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `un_usuario` (`usuario`),
  UNIQUE KEY `un_email` (`email`),
  KEY `fk_rol` (`id_rol`),
  CONSTRAINT `fk_rol` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`)
) ENGINE=InnoDB AUTO_INCREMENT=148 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuario`
--

LOCK TABLES `usuario` WRITE;
/*!40000 ALTER TABLE `usuario` DISABLE KEYS */;
INSERT INTO `usuario` VALUES (1,'Pacou','Paco','admin','prueba@gmail.com',NULL,NULL,1),(2,'a','a','a','a@gmail.com',NULL,NULL,1),(4,'Iondieguez','Elva','a','elvaginon69@gmail.com','pf_4_1768549273.jpg','Elva| Gym & Progress ⚡️ Viviendo en básicos de Gymshark. Siempre buscando la mejor versión de mí mismo, una serie a la vez. 👟🦾 📍 Basado en España.',1),(6,'PepeLotas420','Ekaitz','contraseña','ekaitz.pascual04@somo.eus','pf_6_1768558241.jpg',NULL,1),(7,'JonGut','Jon','soyjon','dam3.jon.gutierrez@gmail.com',NULL,NULL,1),(48,'vegeta_fan','Principe Saiyan','123456','veg@test.com',NULL,'El mejor guerrero. Entrenando duro. ??',2),(49,'cat_lady_99','Karen Cats','123456','karen@test.com',NULL,'Madre de 7 gatos ???. Me gusta el té.',2),(50,'fitness_alex','Alex Gym','123456','alex@test.com',NULL,'No pain no gain ????? | Coach Online',2),(51,'chef_bon_appetit','Pierre LeChef','123456','pierre@test.com',NULL,'Gastronomía molecular ??. Amante del buen vino.',2),(52,'skater_boi','Tony H.','123456','tony@test.com',NULL,'See you later boy ?. Punk Rock forever.',2),(53,'artist_luna','Luna Art','123456','luna@test.com',NULL,'Acuarelas y digital art ?. Comisiones abiertas.',2),(54,'crypto_shark','Bitcoin King','123456','btc@test.com',NULL,'To the moon ?? #BTC #ETH',2),(55,'travel_couple','Jess & Mike','123456','jess@test.com',NULL,'Viajando por el mundo ?. Próxima parada: Bali.',2),(56,'dev_python','Py Coder','123456','py@test.com',NULL,'import life; while alive: code() ?',2),(57,'music_producer','DJ Drop','123456','dj@test.com',NULL,'Beats & Bass ??. Check mi Soundcloud.',2),(58,'soccer_10','Leo M.','123456','leo@test.com',NULL,'Fútbol es vida ??.',2),(59,'cinema_addict','Cinefilo 3000','123456','cine@test.com',NULL,'Reseñas de películas sin spoilers ??.',2),(60,'book_worm','Ana L.','123456','ana@test.com',NULL,'Un libro al día ?. Harry Potter fan.',2),(61,'startup_guy','Elon W.','123456','elon@test.com',NULL,'CEO de 3 empresas. Durmiendo 4h al día ?.',2),(62,'yoga_life','Shanti Om','123456','yoga@test.com',NULL,'Namaste ????. Paz interior.',2),(63,'gamer_girl_uwu','Sakura','123456','saku@test.com',NULL,'LoL, Valorant y Sims ??. Twitch streamer.',2),(64,'photo_master','Canon Shooter','123456','photo@test.com',NULL,'Capturando momentos ?. Sony Alpha user.',2),(65,'science_nerd','Dr. Sheldon','123456','shel@test.com',NULL,'Física teórica y cómics ???.',2),(66,'car_lover','Fast Furious','123456','car@test.com',NULL,'Gasolina en las venas ???.',2),(67,'vegan_power','Green Life','123456','veg2@test.com',NULL,'Comida basada en plantas ??.',2),(68,'marketing_guru','Gary V.','123456','gary@test.com',NULL,'Hustle 24/7 ?. Marketing digital.',2),(69,'memelord_69','Pepe Frog','123456','pepe@test.com',NULL,'Solo subo memes de calidad ??.',2),(70,'fashion_icon','Bella M.','123456','bella@test.com',NULL,'OOTD ??. Moda y estilo.',2),(71,'retro_80s','Stranger Guy','123456','80s@test.com',NULL,'Nostalgia de los 80 ???.',2),(72,'coffee_addict','Barista Joe','123456','coffee@test.com',NULL,'Sin café no funciono ???.',2),(73,'sneaker_head','Jordans Only','123456','shoe@test.com',NULL,'Coleccionista de zapatillas ?.',2),(74,'makeup_artist','Glamour By Su','123456','su@test.com',NULL,'Tutoriales de maquillaje ??.',2),(75,'hiking_adventures','Mountain Man','123456','hike@test.com',NULL,'La montaña me llama ???.',2),(76,'dog_lover_101','Puppy Love','123456','dog@test.com',NULL,'Mis perros son mis hijos ??.',2),(77,'tatto_ink','Ink Master','123456','ink@test.com',NULL,'Tatuador profesional ??.',2),(78,'astrology_vibes','Zodiac Queen','123456','stars@test.com',NULL,'Escorpio con luna en Piscis ??.',2),(79,'minimalist_design','Less Is More','123456','min@test.com',NULL,'Diseño escandinavo ?.',2),(80,'horror_fan','Scary Movie','123456','boo@test.com',NULL,'Halloween todo el año ??.',2),(81,'surfer_dude','Wave Rider','123456','surf@test.com',NULL,'Esperando la ola perfecta ??.',2),(82,'tech_reviewer','Unbox Therapy','123456','unbox@test.com',NULL,'El último iPhone es... ?.',2),(83,'gardening_pro','Plant Dad','123456','plant@test.com',NULL,'Mis plantas no se mueren ??.',2),(84,'history_buff','Napoleon B.','123456','hist@test.com',NULL,'Aprendiendo del pasado ????.',2),(85,'anime_otaku','Naruto Uzumaki','123456','naru@test.com',NULL,'Dattebayo! ??.',2),(86,'finance_bro','Wall Street','123456','money@test.com',NULL,'Stocks & Bonds ??.',2),(87,'mystery_user','Anonymous','123456','anon@test.com',NULL,'No profile bio.',2),(88,'rock_legend','Slash Fan','123456','rock@test.com',NULL,'Guitarra eléctrica y amplificadores al 11 ??.',2),(89,'chill_lofi','Lofi Girl','123456','lofi@test.com',NULL,'Estudiando 24/7 con música relajante ??.',2),(90,'movie_director','Quentin T.','123456','cine2@test.com',NULL,'Cine de culto y diálogos largos ??.',2),(91,'crossfit_pro','Titan Gym','123456','cross@test.com',NULL,'WOD del día: Sobrevivir ?????.',2),(92,'sushi_master','Jiro Ono','123456','sushi@test.com',NULL,'El arte del arroz y el pescado ??.',2),(93,'meme_queen','Doge Lady','123456','doge@test.com',NULL,'Much wow, very social ??.',2),(94,'travel_blogger','Wanderlust','123456','travel2@test.com',NULL,'Coleccionando sellos en el pasaporte ???.',2),(95,'indie_dev','Pixel Coder','123456','indie@test.com',NULL,'Creando el próximo éxito de Steam en mi sótano ?.',2),(96,'vintage_style','Retro Soul','123456','retro2@test.com',NULL,'Ropa de segunda mano y discos de vinilo ??.',2),(97,'crypto_trader_x','Bull Run','123456','bull@test.com',NULL,'Comprando en rojo, vendiendo en verde (a veces) ??.',2),(98,'nature_boy','Wild Life','123456','wild@test.com',NULL,'Acampada libre y supervivencia ??.',2),(99,'urban_photo','City Lights','123456','city@test.com',NULL,'La ciudad nunca duerme, yo tampoco ??.',2),(100,'poet_society','Dead Poets','123456','poet@test.com',NULL,'Versos libres y café negro ???.',2),(101,'scifi_writer','Isaac A.','123456','scifi@test.com',NULL,'Soñando con robots eléctricos ??.',2),(102,'jazz_vibes','Sax Man','123456','jazz@test.com',NULL,'Improvisando la vida ??.',2),(103,'basket_baller','Air Jordan','123456','nba@test.com',NULL,'Ball is life ??????.',2),(104,'f1_driver','Super Max','123456','f1@test.com',NULL,'Vivir rápido, conducir más rápido ???.',2),(105,'makeup_guru_2','Contour Queen','123456','make2@test.com',NULL,'Brilla más que tu futuro ??.',2),(106,'harry_potter_fan','Gryffindor 07','123456','hp@test.com',NULL,'Esperando mi carta de Hogwarts ??.',2),(107,'star_wars_nerd','Jedi Master','123456','sw@test.com',NULL,'Que la fuerza te acompañe ???.',2),(108,'marvel_stan','Stan Lee','123456','mcu@test.com',NULL,'Excelsior! ??????.',2),(109,'dc_comics_fan','Dark Knight','123456','bat@test.com',NULL,'Soy la venganza. ??.',2),(110,'kpop_stan_bts','Army Forever','123456','bts@test.com',NULL,'Borahae! ??.',2),(111,'blackpink_area','Blink 100%','123456','bp@test.com',NULL,'How you like that? ??.',2),(112,'real_madrid_cf','Hala Madrid','123456','rm@test.com',NULL,'Reyes de Europa ??.',2),(113,'fc_barcelona','Culer','123456','fcb@test.com',NULL,'Més que un club ??.',2),(114,'tennis_pro','Rafa N.','123456','tennis@test.com',NULL,'Vamos! ??.',2),(115,'pc_master_race','RTX 4090','123456','pc@test.com',NULL,'Mis FPS son más altos que tu IQ ????.',2),(116,'console_peasant','Sony Pony','123456','ps5@test.com',NULL,'Exclusivos de calidad ?.',2),(117,'xbox_live','Master Chief','123456','xbox@test.com',NULL,'Game Pass es la vida ?.',2),(118,'nintendo_switch','Mario Jump','123456','nin@test.com',NULL,'Zelda y Mario, qué más necesito? ??.',2),(119,'linux_user','Sudo Root','123456','linux@test.com',NULL,'I use Arch btw ??.',2),(120,'apple_fanboy','Steve Jobs','123456','mac@test.com',NULL,'Simplemente funciona ??.',2),(121,'android_modder','APK Installer','123456','and@test.com',NULL,'Libertad total ??.',2),(122,'chef_pastry','Sweet Tooth','123456','cake@test.com',NULL,'La vida es corta, comete el postre primero ??.',2),(123,'burger_king','Grill Master','123456','bbq@test.com',NULL,'Carne a la parrilla y queso fundido ??.',2),(124,'vegan_activist','Plant Based','123456','veg3@test.com',NULL,'Amigos, no comida ???.',2),(125,'wine_taster','Sommelier','123456','wine@test.com',NULL,'Envejecido en roble francés ??.',2),(126,'beer_lover','Craft Beer','123456','beer@test.com',NULL,'IPA, Stout, Lager... las quiero todas ??.',2),(127,'tea_time','Earl Grey','123456','tea@test.com',NULL,'Es la hora del té, querido ???.',2),(128,'architecture_lover','Modern Lines','123456','arch@test.com',NULL,'El diseño es inteligencia visible ??.',2),(129,'interior_design','Feng Shui','123456','decor@test.com',NULL,'Haciendo de una casa un hogar ???.',2),(130,'diy_expert','Handy Man','123456','diy@test.com',NULL,'Si está roto, yo lo arreglo ??.',2),(131,'gardener_life','Green Thumb','123456','garden@test.com',NULL,'Hablando con mis plantas ??.',2),(132,'cat_memes','Grumpy Cat','123456','meow@test.com',NULL,'Si quepo, me siento ??.',2),(133,'dog_training','Good Boy','123456','woof@test.com',NULL,'Sentado. Quieto. Buen chico! ??.',2),(134,'bird_watcher','Eagle Eye','123456','bird@test.com',NULL,'Mira ese halcón peregrino! ??.',2),(135,'astronomy_club','Space X','123456','space@test.com',NULL,'Mirando a las estrellas ??.',2),(136,'history_meme','Rome Empire','123456','rome@test.com',NULL,'Pensando en el Imperio Romano a diario ????.',2),(137,'philosophy_quote','Socrates','123456','phil@test.com',NULL,'Solo sé que no sé nada ??.',2),(138,'psychology_facts','Mind Games','123456','psy@test.com',NULL,'Entendiendo la mente humana ?.',2),(139,'math_genius','Euler','123456','math@test.com',NULL,'Las matemáticas son el lenguaje del universo ???.',2),(140,'physics_fun','Einstein','123456','phys@test.com',NULL,'E = mc² ??.',2),(141,'biology_lab','DNA Helix','123456','bio@test.com',NULL,'La vida encuentra un camino ??.',2),(142,'chemistry_bad','Heisenberg','123456','chem@test.com',NULL,'Ciencia, perra! ???.',2),(143,'teacher_life','Profe Ana','123456','teach@test.com',NULL,'Educar es dejar huella en el corazón ??.',2),(144,'student_struggle','Exams Stress','123456','study@test.com',NULL,'Café y apuntes a las 3 AM ??.',2),(145,'party_animal','Project X','123456','party@test.com',NULL,'La fiesta no termina hasta que sale el sol ??.',2),(146,'sleepy_head','Nap Time','123456','sleep@test.com',NULL,'5 minutos más, por favor ??.',2),(147,'mystery_user_2','Ghost','123456','ghost@test.com',NULL,'...',2);
/*!40000 ALTER TABLE `usuario` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-01-21 13:41:58
