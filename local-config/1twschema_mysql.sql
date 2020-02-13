# ************************************************************
# Sequel Pro SQL dump
# Version 4541
#
# http://www.sequelpro.com/
# https://github.com/sequelpro/sequelpro
#
# Host: us-cdbr-iron-east-02.cleardb.net (MySQL 5.5.62-log)
# Database: heroku_c71e6e72f5ee133
# Generation Time: 2020-02-06 02:51:26 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table attendance
# ------------------------------------------------------------

DROP TABLE IF EXISTS `attendance`;

CREATE TABLE `attendance` (
  `memberid` int(11) NOT NULL,
  `attendancedate` date NOT NULL,
  `eventid` int(11) DEFAULT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `teamid` int(11) DEFAULT NULL,
  `type` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

# Dump of table attendanceconsoles
# ------------------------------------------------------------

DROP TABLE IF EXISTS `attendanceconsoles`;

CREATE TABLE `attendanceconsoles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) DEFAULT NULL,
  `ip` varchar(16) NOT NULL,
  `teamid` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

LOCK TABLES `attendanceconsoles` WRITE;
/*!40000 ALTER TABLE `attendanceconsoles` DISABLE KEYS */;

INSERT INTO `attendanceconsoles` (`id`, `name`, `ip`, `teamid`)
VALUES
	(1,'Gym laptop','192.168.0.14',1);

/*!40000 ALTER TABLE `attendanceconsoles` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table audit
# ------------------------------------------------------------

DROP TABLE IF EXISTS `audit`;

CREATE TABLE `audit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table customdata
# ------------------------------------------------------------

DROP TABLE IF EXISTS `customdata`;

CREATE TABLE `customdata` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customfieldid` int(11) DEFAULT NULL,
  `memberid` int(11) DEFAULT NULL,
  `valuelist` int(11) DEFAULT NULL,
  `valueint` int(11) DEFAULT NULL,
  `valuebool` tinyint(1) DEFAULT NULL,
  `valuetext` varchar(80) DEFAULT NULL,
  `valuedate` date DEFAULT NULL,
  `valuefloat` double DEFAULT NULL,
  `teamid` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;


# Dump of table customdatatypes
# ------------------------------------------------------------

DROP TABLE IF EXISTS `customdatatypes`;

CREATE TABLE `customdatatypes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `typename` varchar(80) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

LOCK TABLES `customdatatypes` WRITE;
/*!40000 ALTER TABLE `customdatatypes` DISABLE KEYS */;

INSERT INTO `customdatatypes` (`id`, `typename`)
VALUES
	(1,'Text (up to 80 characters)'),
	(2,'Whole number'),
	(3,'Floating point number'),
	(4,'Boolean (Yes, No)'),
	(5,'Date'),
	(7,'List');

/*!40000 ALTER TABLE `customdatatypes` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table customfields
# ------------------------------------------------------------

DROP TABLE IF EXISTS `customfields`;

CREATE TABLE `customfields` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customdatatypeid` int(11) NOT NULL,
  `name` varchar(80) DEFAULT NULL,
  `teamid` int(11) DEFAULT NULL,
  `displayconditionobject` varchar(80) DEFAULT NULL,
  `displayconditionfield` varchar(80) DEFAULT NULL,
  `displayconditionoperator` varchar(2) DEFAULT NULL,
  `displayconditionvalue` varchar(80) DEFAULT NULL,
  `hasdisplaycondition` tinyint(1) DEFAULT NULL,
  `listorder` int(11) DEFAULT NULL,
  `customlistid` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

LOCK TABLES `customfields` WRITE;
/*!40000 ALTER TABLE `customfields` DISABLE KEYS */;

INSERT INTO `customfields` (`id`, `customdatatypeid`, `name`, `teamid`, `displayconditionobject`, `displayconditionfield`, `displayconditionoperator`, `displayconditionvalue`, `hasdisplaycondition`, `listorder`, `customlistid`)
VALUES
	(1,7,'Weight Class',1,NULL,NULL,NULL,NULL,0,1,1),
	(4,4,'Eligible for women\'s programs',1,'users','gender','=','F',1,3,0),
	(5,7,'Shirt Size',1,NULL,NULL,NULL,NULL,0,2,3);

/*!40000 ALTER TABLE `customfields` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table customlistdata
# ------------------------------------------------------------

DROP TABLE IF EXISTS `customlistdata`;

CREATE TABLE `customlistdata` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customlistid` int(11) DEFAULT NULL,
  `listitemname` varchar(80) DEFAULT NULL,
  `listorder` int(11) DEFAULT NULL,
  `teamid` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8;

LOCK TABLES `customlistdata` WRITE;
/*!40000 ALTER TABLE `customlistdata` DISABLE KEYS */;

INSERT INTO `customlistdata` (`id`, `customlistid`, `listitemname`, `listorder`, `teamid`)
VALUES
	(2,1,'Featherweight (125)',4,1),
	(3,1,'Bantamweight (118)',3,1),
	(4,1,'Welterweight (147)',5,1),
	(5,1,'Lightweight (155)',6,1),
	(6,1,'Middleweight (170)',7,1),
	(7,1,'Light heavyweight (186)',8,1),
	(8,1,'Heavyweight (206)',9,1),
	(9,1,'Super Heavyweight (226)',10,1),
	(10,1,'Super Super Heavyweight (266+)',11,1),
	(17,3,'Medium',2,1),
	(18,3,'Small',1,1),
	(19,3,'Large',1000,1),
	(20,3,'XL',1000,1),
	(22,1,'Strawweight (105)',1,1),
	(23,1,'Flyweight (112)',2,1);

/*!40000 ALTER TABLE `customlistdata` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table customlists
# ------------------------------------------------------------

DROP TABLE IF EXISTS `customlists`;

CREATE TABLE `customlists` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(80) DEFAULT NULL,
  `teamid` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

LOCK TABLES `customlists` WRITE;
/*!40000 ALTER TABLE `customlists` DISABLE KEYS */;

INSERT INTO `customlists` (`id`, `name`, `teamid`)
VALUES
	(1,'Weight Class',1),
	(3,'Shirt size',1);

/*!40000 ALTER TABLE `customlists` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table epayments
# ------------------------------------------------------------

DROP TABLE IF EXISTS `epayments`;

CREATE TABLE `epayments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `source` int(11) DEFAULT NULL,
  `txid` varchar(128) DEFAULT NULL,
  `reconciled` tinyint(1) DEFAULT '0',
  `teamid` int(11) DEFAULT NULL,
  `amount` decimal(6,2) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `item` varchar(128) DEFAULT NULL,
  `payeremail` varchar(128) DEFAULT NULL,
  `skuname` varchar(128) DEFAULT NULL,
  `fee` decimal(6,2) DEFAULT NULL,
  `userid` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

# Dump of table events
# ------------------------------------------------------------

DROP TABLE IF EXISTS `events`;

CREATE TABLE `events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(80) NOT NULL,
  `eventdate` date DEFAULT NULL,
  `location` varchar(80) DEFAULT NULL,
  `listorder` int(11) DEFAULT NULL,
  `teamid` int(11) DEFAULT NULL,
  `scannable` tinyint(1) DEFAULT NULL,
  `programid` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8;

LOCK TABLES `events` WRITE;
/*!40000 ALTER TABLE `events` DISABLE KEYS */;

INSERT INTO `events` (`id`, `name`, `eventdate`, `location`, `listorder`, `teamid`, `scannable`, `programid`)
VALUES
	(1,'Grappling Class',NULL,'Austin Jiu-Jitsu',2,1,1,1),
	(2,'Kids Grappling Class',NULL,'Austin Jiu-Jitsu',1,1,1,8),
	(4,'Grappling Seminar',NULL,'any location',7,1,0,NULL),
	(5,'Class',NULL,'Other gym',9,1,NULL,NULL),
	(6,'Competition',NULL,'any location',6,1,NULL,NULL),
	(8,'Open mat',NULL,'Any gym',5,1,NULL,NULL),
	(9,'Class',NULL,'Travis Tooke\'s Gym',8,1,NULL,NULL),
	(15,'Private lesson ',NULL,'Austin Jiu-Jitsu',4,1,1,1),
	(16,'Kickboxing Class',NULL,'Austin Jiu-Jitsu',3,1,1,7),
	(19,'Team Outing',NULL,'any location',10,1,NULL,NULL),
	(40,'Training Group',NULL,'RunTex Riverside',NULL,8,NULL,NULL);

/*!40000 ALTER TABLE `events` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table feedback
# ------------------------------------------------------------

DROP TABLE IF EXISTS `feedback`;

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `summary` varchar(64) DEFAULT NULL,
  `detail` varchar(512) DEFAULT NULL,
  `userid` int(11) DEFAULT NULL,
  `datefeedback` date DEFAULT NULL,
  `teamid` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;


# Dump of table games
# ------------------------------------------------------------

DROP TABLE IF EXISTS `games`;

CREATE TABLE `games` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gamedate` date DEFAULT NULL,
  `scorea` int(11) DEFAULT NULL,
  `scoreb` int(11) DEFAULT NULL,
  `matchid` int(11) NOT NULL,
  `teamid` int(11) DEFAULT NULL,
  `listorder` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;


# Dump of table images
# ------------------------------------------------------------

DROP TABLE IF EXISTS `images`;

CREATE TABLE `images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `url` varchar(255) DEFAULT NULL,
  `filename` varchar(255) DEFAULT NULL,
  `teamid` int(11) DEFAULT NULL,
  `type` int(11) DEFAULT NULL,
  `objid` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

# Dump of table levels
# ------------------------------------------------------------

DROP TABLE IF EXISTS `levels`;

CREATE TABLE `levels` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  `programid` int(11) DEFAULT NULL,
  `listorder` int(11) DEFAULT NULL,
  `teamid` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=76 DEFAULT CHARSET=utf8;

LOCK TABLES `levels` WRITE;
/*!40000 ALTER TABLE `levels` DISABLE KEYS */;

INSERT INTO `levels` (`id`, `name`, `programid`, `listorder`, `teamid`)
VALUES
	(1,'u16 White',8,22,1),
	(2,'u16 Yellow',8,26,1),
	(3,'u16 Orange',8,30,1),
	(4,'u16 Green',8,34,1),
	(16,'u16 White 1',8,23,1),
	(17,'u16 White 2',8,24,1),
	(18,'u16 White 3',8,25,1),
	(20,'u16 Yellow 1',8,27,1),
	(21,'u16 Yellow 2',8,28,1),
	(22,'u16 Yellow 3',8,29,1),
	(24,'u16 Orange 1',8,31,1),
	(25,'u16 Orange 2',8,32,1),
	(26,'u16 Orange 3',8,33,1),
	(28,'u16 Green 1',8,35,1),
	(29,'u16 Green 2',8,36,1),
	(30,'u16 Green 3',8,37,1),
	(32,'White',1,1,1),
	(33,'White 1',1,2,1),
	(34,'White 2',1,3,1),
	(35,'White 3',1,4,1),
	(36,'White 4',1,5,1),
	(37,'Blue',1,6,1),
	(38,'Blue 1',1,7,1),
	(39,'Blue 2',1,8,1),
	(40,'Blue 3',1,9,1),
	(41,'Blue 4',1,10,1),
	(42,'Purple',1,11,1),
	(43,'Purple 1',1,12,1),
	(44,'Purple 2',1,13,1),
	(46,'Purple 3',1,14,1),
	(47,'Purple 4',1,15,1),
	(69,'Beginner',17,NULL,8),
	(70,'Brown',1,16,1),
	(71,'Brown 1',1,17,1),
	(72,'Brown 2',1,18,1),
	(73,'Brown 3',1,19,1),
	(74,'Brown 4',1,20,1),
	(75,'Black',1,21,1);

/*!40000 ALTER TABLE `levels` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table matches
# ------------------------------------------------------------

DROP TABLE IF EXISTS `matches`;

CREATE TABLE `matches` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `challengedate` date DEFAULT NULL,
  `userida` int(11) DEFAULT NULL,
  `useridb` int(11) DEFAULT NULL,
  `winnerid` int(11) DEFAULT NULL,
  `teamid` int(11) DEFAULT NULL,
  `rankpointsa` double DEFAULT NULL,
  `rankpointsb` double DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;


# Dump of table orderitems
# ------------------------------------------------------------

DROP TABLE IF EXISTS `orderitems`;

CREATE TABLE `orderitems` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `programid` int(11) DEFAULT NULL,
  `paymentdate` date DEFAULT NULL,
  `userid` int(11) DEFAULT NULL,
  `teamid` int(11) DEFAULT NULL,
  `paymentmethod` int(11) DEFAULT NULL,
  `amount` decimal(6,2) DEFAULT NULL,
  `skuid` int(11) DEFAULT NULL,
  `numeventsremaining` int(11) DEFAULT NULL,
  `fee` decimal(6,2) DEFAULT NULL,
  `ispaid` tinyint(1) DEFAULT NULL,
  `isrefunded` tinyint(1) DEFAULT NULL,
  `orderid` int(11) DEFAULT NULL,
  `comment` varchar(120) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;


# Dump of table orders
# ------------------------------------------------------------

DROP TABLE IF EXISTS `orders`;

CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) DEFAULT NULL,
  `teamid` int(11) DEFAULT NULL,
  `orderdate` date DEFAULT NULL,
  `duedate` date DEFAULT NULL,
  `discount` decimal(6,2) DEFAULT NULL,
  `ispaid` tinyint(1) DEFAULT '0',
  `paymentmethod` int(11) DEFAULT NULL,
  `comment` varchar(120) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;


# Dump of table paymentmethods
# ------------------------------------------------------------

DROP TABLE IF EXISTS `paymentmethods`;

CREATE TABLE `paymentmethods` (
  `id` int(11) NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  `teamid` int(11) DEFAULT NULL,
  `listorder` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `paymentmethods` WRITE;
/*!40000 ALTER TABLE `paymentmethods` DISABLE KEYS */;

INSERT INTO `paymentmethods` (`id`, `name`, `teamid`, `listorder`)
VALUES
	(15,'Gift Certificate',1,5),
	(2,'Check',1,3),
	(25,'Square Up',1,4),
	(1,'PayPal',1,2),
	(16,'Barter',1,6),
	(24,'Pro Bono',1,7),
	(3,'Cash',1,1);

/*!40000 ALTER TABLE `paymentmethods` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table profiles
# ------------------------------------------------------------

DROP TABLE IF EXISTS `profiles`;

CREATE TABLE `profiles` (
  `profilename` varchar(80) NOT NULL,
  `productname` varchar(80) NOT NULL,
  `logofile` varchar(255) DEFAULT NULL,
  `cssfile` varchar(255) DEFAULT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sessiontimeout` int(11) DEFAULT '20',
  `activityname` varchar(80) DEFAULT NULL,
  `membertitle` varchar(80) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

LOCK TABLES `profiles` WRITE;
/*!40000 ALTER TABLE `profiles` DISABLE KEYS */;

INSERT INTO `profiles` (`profilename`, `productname`, `logofile`, `cssfile`, `id`, `sessiontimeout`, `activityname`, `membertitle`)
VALUES
	('default','One Team Web','default.gif','default.css',1,10,'Jiu-Jitsu','Student');

/*!40000 ALTER TABLE `profiles` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table programs
# ------------------------------------------------------------

DROP TABLE IF EXISTS `programs`;

CREATE TABLE `programs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(80) NOT NULL,
  `teamid` int(11) DEFAULT NULL,
  `listorder` int(11) DEFAULT NULL,
  `eventid` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8;

LOCK TABLES `programs` WRITE;
/*!40000 ALTER TABLE `programs` DISABLE KEYS */;

INSERT INTO `programs` (`id`, `name`, `teamid`, `listorder`, `eventid`)
VALUES
	(1,'Adult Grappling',1,2,1),
	(2,'Annual Membership',1,1,NULL),
	(3,'Women\'s',1,6,NULL),
	(7,'Kickboxing',1,4,16),
	(8,'Kids Grappling',1,3,2),
	(17,'Bucky and Bob',8,1,40),
	(18,'Boys2Men Track Club',8,2,NULL),
	(19,'The Morning Group',8,3,NULL);

/*!40000 ALTER TABLE `programs` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table promotions
# ------------------------------------------------------------

DROP TABLE IF EXISTS `promotions`;

CREATE TABLE `promotions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `memberid` int(11) DEFAULT NULL,
  `promotiondate` date DEFAULT NULL,
  `newlevel` int(11) DEFAULT NULL,
  `teamid` int(11) DEFAULT NULL,
  `imageid` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

# Dump of table rankings
# ------------------------------------------------------------

DROP TABLE IF EXISTS `rankings`;

CREATE TABLE `rankings` (
  `userid` int(11) NOT NULL,
  `teamid` int(11) NOT NULL,
  `points` double DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Dump of table recognizeduserlocations
# ------------------------------------------------------------

DROP TABLE IF EXISTS `recognizeduserlocations`;

CREATE TABLE `recognizeduserlocations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL,
  `teamid` int(11) DEFAULT NULL,
  `ipaddr` char(16) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

LOCK TABLES `recognizeduserlocations` WRITE;
/*!40000 ALTER TABLE `recognizeduserlocations` DISABLE KEYS */;

INSERT INTO `recognizeduserlocations` (`id`, `userid`, `teamid`, `ipaddr`)
VALUES
	(1,95,1,'192.168.0.4');

/*!40000 ALTER TABLE `recognizeduserlocations` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table redemptioncards
# ------------------------------------------------------------

DROP TABLE IF EXISTS `redemptioncards`;

CREATE TABLE `redemptioncards` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `teamid` int(11) DEFAULT NULL,
  `userid` int(11) DEFAULT NULL,
  `skuid` int(11) DEFAULT NULL,
  `createdate` date DEFAULT NULL,
  `amountpaid` decimal(6,2) DEFAULT NULL,
  `numeventsremaining` int(11) DEFAULT NULL,
  `expires` date DEFAULT NULL,
  `paymentmethod` int(11) DEFAULT '0',
  `description` varchar(128) DEFAULT NULL,
  `type` int(11) DEFAULT NULL,
  `facevalue` decimal(6,2) DEFAULT NULL,
  `code` char(12) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;


# Dump of table sessions
# ------------------------------------------------------------

DROP TABLE IF EXISTS `sessions`;

CREATE TABLE `sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ipaddr` char(16) DEFAULT NULL,
  `userid` int(11) DEFAULT NULL,
  `sessionkey` char(8) DEFAULT NULL,
  `timecreated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `timeexpires` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `login` varchar(50) DEFAULT NULL,
  `roleid` int(11) DEFAULT NULL,
  `fullname` varchar(50) DEFAULT NULL,
  `teamid` int(11) DEFAULT NULL,
  `isbillable` tinyint(1) DEFAULT NULL,
  `status` int(11) DEFAULT NULL,
  `authsms` int(11) DEFAULT NULL,
  `authsmsretries` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

# Dump of table skus
# ------------------------------------------------------------

DROP TABLE IF EXISTS `skus`;

CREATE TABLE `skus` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) DEFAULT NULL,
  `programid` int(11) DEFAULT NULL,
  `listorder` int(11) DEFAULT NULL,
  `teamid` int(11) DEFAULT NULL,
  `price` decimal(6,2) DEFAULT NULL,
  `description` varchar(1028) DEFAULT NULL,
  `numevents` int(11) DEFAULT NULL,
  `expires` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=72 DEFAULT CHARSET=utf8;

LOCK TABLES `skus` WRITE;
/*!40000 ALTER TABLE `skus` DISABLE KEYS */;

INSERT INTO `skus` (`id`, `name`, `programid`, `listorder`, `teamid`, `price`, `description`, `numevents`, `expires`)
VALUES
	(1,'adult-grapple-18-3m',1,4,1,357.00,'18 adult group grappling classes to be used within 3 months of the date of purchase. ',18,'3 month'),
	(2,'adult-grapple-36-6m',1,8,1,664.00,'36 adult group grappling classes to be used within 6 months of the date of purchase. ',36,'6 month'),
	(3,'adult-grapple-24-3m',1,5,1,399.00,'24 adult group grappling classes to be used within 3 months of the date of purchase. ',24,'3 month'),
	(4,'adult-grapple-48-6m',1,9,1,720.00,'48 adult group grappling classes to be used within 6 months of the date of purchase. ',48,'6 month'),
	(5,'adult-grapple-36-3m',1,6,1,526.00,'36 adult group grappling classes to be used within 3 months of the date of purchase. ',36,'3 month'),
	(6,'adult-grapple-72-6m',1,10,1,978.00,'72 adult group grappling classes to be used within 6 months of the date of purchase. ',72,'6 month'),
	(7,'adult-kbmma-12-3m',7,16,1,216.00,'12 adult group kickboxing or MMA classes to be used within 3 months of the date of purchase. ',12,'3 month'),
	(8,'kids-grapple-12-3m',8,18,1,240.00,'12 kids group grappling classes to be used within 3 months of the date of purchase. ',12,'3 month'),
	(9,'kids-grapple-24-6m',8,19,1,430.00,'24 kids group grappling classes to be used within 6 months of the date of purchase. ',24,'6 month'),
	(10,'kids-grapple-48-12m',8,20,1,847.00,'48 kids group grappling classes to be used within 12 months of the date of purchase. ',48,'1 year'),
	(11,'adult-private-1',1,13,1,95.00,'',1,'14 day'),
	(12,'all-annualmembership',2,15,1,80.00,'Annual membership fee required of all members of Austin Jiu-Jitsu.',-1,'1 year'),
	(13,'adult-matfee',1,12,1,25.00,'',1,'7 day'),
	(14,'kids-matfee',8,21,1,20.00,'',1,'1 day'),
	(15,'adult-grapple-6-1m',1,1,1,136.00,'',6,'1 month'),
	(16,'adult-grapple-24-6m',1,7,1,541.00,'24 adult group grappling classes to be used within 6 months of the date of purchase. ',24,'6 month'),
	(17,'adult-grapple-8-1m',1,2,1,171.00,'',8,'1 month'),
	(18,'Donation for BJJTech.com',1,14,1,1.00,NULL,NULL,NULL),
	(24,'adult-matfee-kbmma',7,17,1,20.00,'',1,'7 day'),
	(25,'Bucky and Bob monthly',17,NULL,8,50.00,'',-1,'1 month'),
	(26,'adult-grapple-72-12m',1,11,1,1100.00,'',72,'1 year'),
	(27,'adult-grapple-12-3m',1,3,1,291.00,'',12,'3 mon'),
	(28,'adult-grapple-4-1m',1,NULL,1,136.00,NULL,NULL,NULL),
	(31,'adult-grapple-u-3m',1,NULL,1,300.00,'3 Months Unlimited',-1,'3 mon'),
	(41,'adult-grapple-u-1m',1,NULL,1,110.00,'',-1,'1 mon'),
	(51,'adult-grapple-u-2m',1,NULL,1,210.00,'',-1,'2 mon'),
	(61,'adult-grapple-u-6m',1,NULL,1,590.00,'',-1,'6 mon'),
	(71,'adult-grapple-u-12m',1,NULL,1,1120.00,'1 year unlimited',-1,'12 mon');

/*!40000 ALTER TABLE `skus` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table teamaccountinfo
# ------------------------------------------------------------

DROP TABLE IF EXISTS `teamaccountinfo`;

CREATE TABLE `teamaccountinfo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `teamid` int(11) DEFAULT NULL,
  `status` int(11) DEFAULT NULL,
  `plan` int(11) DEFAULT NULL,
  `planduration` int(11) DEFAULT NULL,
  `isbillable` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

LOCK TABLES `teamaccountinfo` WRITE;
/*!40000 ALTER TABLE `teamaccountinfo` DISABLE KEYS */;

INSERT INTO `teamaccountinfo` (`id`, `teamid`, `status`, `plan`, `planduration`, `isbillable`)
VALUES
	(1,1,1,1000,-1,0);

/*!40000 ALTER TABLE `teamaccountinfo` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table teampayments
# ------------------------------------------------------------

DROP TABLE IF EXISTS `teampayments`;

CREATE TABLE `teampayments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `paymentdate` date DEFAULT NULL,
  `teamid` int(11) DEFAULT NULL,
  `payment` decimal(6,2) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table teams
# ------------------------------------------------------------

DROP TABLE IF EXISTS `teams`;

CREATE TABLE `teams` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(80) DEFAULT NULL,
  `city` varchar(80) DEFAULT NULL,
  `state` varchar(2) DEFAULT NULL,
  `address1` varchar(80) DEFAULT NULL,
  `address2` varchar(80) DEFAULT NULL,
  `postalcode` varchar(80) DEFAULT NULL,
  `phone` varchar(80) DEFAULT NULL,
  `email` varchar(80) DEFAULT NULL,
  `website` varchar(80) DEFAULT NULL,
  `adminid` int(11) DEFAULT NULL,
  `activityname` varchar(80) DEFAULT NULL,
  `notes` varchar(180) DEFAULT NULL,
  `startdate` date DEFAULT NULL,
  `logofile` varchar(255) DEFAULT NULL,
  `paymenturl` varchar(128) DEFAULT NULL,
  `eventidattendance` int(11) DEFAULT '1',
  `imageid` int(11) DEFAULT NULL,
  `introtext` varchar(1024) DEFAULT NULL,
  'api_username' varchar(128) DEFAULT NULL,
  'api_password' varchar(128) DEFAULT NULL,
  'api_signature' varchar(128) DEFAULT NULL,
  'payment_provider' varchar(128) DEFAULT "paypal",
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

LOCK TABLES `teams` WRITE;
/*!40000 ALTER TABLE `teams` DISABLE KEYS */;

INSERT INTO `teams` (`id`, `name`, `city`, `state`, `address1`, `address2`, `postalcode`, `phone`, `email`, `website`, `adminid`, `activityname`, `notes`, `startdate`, `logofile`, `paymenturl`, `eventidattendance`, `imageid`, `introtext`)
VALUES
	(1,'Ivan Salaverry MMA','Seattle','WA','8th Ave N','','98109','','davidcthomas@gmail.com','http://ivansalaverrymma.com',2,'Brazilian Jiu-Jitsu','','2020-02-01',NULL,'http://ivansalaverrymma.com/signup/',NULL,1,'Welcome to ISMMA! We are happy to have you as a new member. One benefit of your membership is \r\nyour access to 1TeamWeb, a team management website that helps you get the most of your time with us. ');

/*!40000 ALTER TABLE `teams` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table teamterms
# ------------------------------------------------------------

DROP TABLE IF EXISTS `teamterms`;

CREATE TABLE `teamterms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `termteam` varchar(32) DEFAULT NULL,
  `termuser` varchar(32) DEFAULT NULL,
  `termadmin` varchar(32) DEFAULT NULL,
  `termcoach` varchar(32) DEFAULT NULL,
  `termmember` varchar(32) DEFAULT NULL,
  `teamid` int(11) DEFAULT NULL,
  `termclass` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

LOCK TABLES `teamterms` WRITE;
/*!40000 ALTER TABLE `teamterms` DISABLE KEYS */;

INSERT INTO `teamterms` (`id`, `termteam`, `termuser`, `termadmin`, `termcoach`, `termmember`, `teamid`, `termclass`)
VALUES
	(1,'Team',NULL,'Team Admin','Coach','Student',1,'Class');

/*!40000 ALTER TABLE `teamterms` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table useraccountinfo
# ------------------------------------------------------------

DROP TABLE IF EXISTS `useraccountinfo`;

CREATE TABLE `useraccountinfo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(80) DEFAULT NULL,
  `status` int(11) DEFAULT '1',
  `userid` int(11) DEFAULT NULL,
  `teamid` int(11) DEFAULT NULL,
  `isbillable` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

LOCK TABLES `useraccountinfo` WRITE;
/*!40000 ALTER TABLE `useraccountinfo` DISABLE KEYS */;

INSERT INTO `useraccountinfo` (`id`, `email`, `status`, `userid`, `teamid`, `isbillable`)
VALUES
	(1,'davidcthomas@gmail.com',1,1,0,0),
  (2,'seattlejits@gmail.com',1,2,1,0);


/*!40000 ALTER TABLE `useraccountinfo` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table users
# ------------------------------------------------------------

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `firstname` varchar(50) DEFAULT NULL,
  `lastname` varchar(50) DEFAULT NULL,
  `startdate` date DEFAULT NULL,
  `address` varchar(254) DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `state` varchar(20) DEFAULT NULL,
  `postalcode` varchar(20) DEFAULT NULL,
  `smsphone` varchar(30) DEFAULT NULL,
  `phone2` varchar(30) DEFAULT NULL,
  `login` varchar(50) NOT NULL,
  `birthdate` date DEFAULT NULL,
  `referredby` varchar(50) DEFAULT NULL,
  `notes` text,
  `coachid` int(11) DEFAULT NULL,
  `emergencycontact` varchar(50) DEFAULT NULL,
  `ecphone1` varchar(50) DEFAULT NULL,
  `ecphone2` varchar(30) DEFAULT NULL,
  `gender` char(1) DEFAULT NULL,
  `stopdate` date DEFAULT NULL,
  `stopreason` varchar(80) DEFAULT NULL,
  `teamid` int(11) DEFAULT NULL,
  `roleid` int(11) DEFAULT NULL,
  `address2` varchar(80) DEFAULT NULL,
  `useraccountinfo` int(11) DEFAULT NULL,
  `salt` char(9) DEFAULT NULL,
  `passwd` varchar(64) DEFAULT NULL,
  `imageid` int(11) DEFAULT NULL,
  `smsphonecarrier` varchar(48) DEFAULT NULL,
  `ipaddr` char(16) DEFAULT NULL,
  `timelockoutexpires` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;

INSERT INTO `users` (`id`, `firstname`, `lastname`, `startdate`, `address`, `city`, `state`, `postalcode`, `smsphone`, `phone2`, `login`, `birthdate`, `referredby`, `notes`, `coachid`, `emergencycontact`, `ecphone1`, `ecphone2`, `gender`, `stopdate`, `stopreason`, `teamid`, `roleid`, `address2`, `useraccountinfo`, `salt`, `passwd`, `imageid`, `smsphonecarrier`, `ipaddr`, `timelockoutexpires`)
VALUES
  (1,'OneTeamWeb','Admin','2020-02-04','2722 Queen Anne Ave N','Seattle','WA','98109-1823','6508457558','','seattlejits@gmail.com','1965-01-01','','',0,'','','','M',NULL,NULL,0,1,'',1,'f417895de','9d54cdc70b595ffb223078ba3b0e42a26b57d55c',NULL,'verizon',NULL,'2020-02-05 02:48:23'),
	(2,'David','Thomas','2003-01-01','2722 Queen Anne Ave N','Seattle','WA','98109-1823','6508457558','','davidcthomas@gmail.com','1965-01-01','David Thomas','',0,'','','','0',NULL,'\'\'\'\'\'\'\'\'\'\'\'\'\'\'',1,2,NULL,2,'f417895de','9d54cdc70b595ffb223078ba3b0e42a26b57d55c',NULL,NULL,NULL,'2011-12-12 02:10:29');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;



/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
