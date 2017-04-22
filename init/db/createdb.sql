create database if not exists `formscan`;
use `formscan`;


-- drop tables
drop table if exists `cron`;
drop table if exists `systemlog`;
drop table if exists `job`;
drop table if exists `settings`;
drop table if exists `contact`;
drop table if exists `userstats`;
drop table if exists `documentpending`;
drop table if exists `documentanalysis`;
drop table if exists `documentconversion`;
drop table if exists `keyword`;
drop table if exists `userapikey`;
drop table if exists `usersession`;
drop table if exists `users`;



-- users
create table `users` (
	`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	`type` TINYINT UNSIGNED NOT NULL DEFAULT 1,	
	`username` VARCHAR(128) NOT NULL DEFAULT "",	
	`password` VARCHAR(512) NOT NULL DEFAULT "",	
	`name` VARCHAR(512) NOT NULL DEFAULT "",		
	`address` VARCHAR(512) NOT NULL DEFAULT "",		
	`city` VARCHAR(64) NOT NULL DEFAULT "",		
	`county` VARCHAR(64) NOT NULL DEFAULT "",		
	`postcode` VARCHAR(64) NOT NULL DEFAULT "",		
	`state` VARCHAR(512) NOT NULL DEFAULT "",	
	`country` VARCHAR(512) NOT NULL DEFAULT "",	
	`timezone` VARCHAR(512) NOT NULL DEFAULT "",	
	`email` VARCHAR(512) NOT NULL DEFAULT "",			
	`phone` VARCHAR(512) NOT NULL DEFAULT "",		
	`fax` VARCHAR(512) NOT NULL DEFAULT "",		
	`website` VARCHAR(512) NOT NULL DEFAULT "",				
	`ipAddress` VARCHAR(128) NOT NULL DEFAULT "",	
	`lastIpAddress` VARCHAR(128) NOT NULL DEFAULT "",		
	`geoLocation` VARCHAR(512) NOT NULL DEFAULT "",		
	`isEmailNotification` TINYINT UNSIGNED NOT NULL DEFAULT 1,
	`isNewsNotification` TINYINT UNSIGNED NOT NULL DEFAULT 1,	
	`rejectReason` VARCHAR(1024) NOT NULL DEFAULT "",	
	`status` INTEGER UNSIGNED NOT NULL DEFAULT 1,		
	`dateAdded` DATETIME NULL,
	`dateUpdated` DATETIME NULL,
	`dateLastLogin` DATETIME NULL,
	`dateDeletion` DATETIME NULL,	
	`dateTerminated` DATETIME NULL,		
	PRIMARY KEY (`id`),
	UNIQUE(`username`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8;


-- usersession
create table `usersession` (
	`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	`userID` INTEGER UNSIGNED NOT NULL,
	`token` VARCHAR(255) NOT NULL DEFAULT "",
	`ipAddress` VARCHAR(32) NOT NULL DEFAULT "",
	`status` TINYINT UNSIGNED NOT NULL DEFAULT 0,
	`dateAdded` DATETIME NULL,
	PRIMARY KEY (`id`),
	FOREIGN KEY (`userID`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- userapikey
create table `userapikey` (
	`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	`userID` INTEGER UNSIGNED NOT NULL,
	`name` VARCHAR(1024) NOT NULL DEFAULT "",
	`key` VARCHAR(256) NOT NULL DEFAULT "",
	`status` TINYINT UNSIGNED NOT NULL DEFAULT 0,
	`dateAdded` DATETIME NULL,
	PRIMARY KEY (`id`),
	FOREIGN KEY (`userID`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- keyword
create table `keyword` (
	`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(1024) NOT NULL DEFAULT "",
	`type` INTEGER UNSIGNED NULL DEFAULT 0,
	`allowedValues` VARCHAR(1024) NOT NULL DEFAULT "",	
	`defaultValue` VARCHAR(256) NOT NULL DEFAULT "",		
	`status` TINYINT UNSIGNED NOT NULL DEFAULT 0,
	`dateAdded` DATETIME NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- documentconversion
create table `documentconversion` (
	`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	`userID` INTEGER UNSIGNED NULL DEFAULT NULL,
	`filename` VARCHAR(256) NOT NULL DEFAULT "",
	`filepath` VARCHAR(512) NOT NULL DEFAULT "", 	
	`type` VARCHAR(64) NOT NULL DEFAULT "",
	`description` VARCHAR(4096) NOT NULL DEFAULT "",	
	`duration` INTEGER UNSIGNED NULL DEFAULT 0,
	`title` VARCHAR(4096) NOT NULL DEFAULT "",	
	`status` TINYINT UNSIGNED NOT NULL DEFAULT 0,
	`dateAdded` DATETIME NULL,
	PRIMARY KEY (`id`),
	FOREIGN KEY (`userID`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- documentanalysis
create table `documentanalysis` (
	`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,	
	`documentID` INTEGER UNSIGNED NULL DEFAULT NULL,	
	`text` TEXT NOT NULL,	
	`status` TINYINT UNSIGNED NOT NULL DEFAULT 0,	
	`dateAdded` DATETIME NULL,
	PRIMARY KEY (`id`),
	FOREIGN KEY (`documentID`) REFERENCES `documentconversion` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- documentpending
create table `documentpending` (
	`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	`userID` INTEGER UNSIGNED NULL DEFAULT NULL,
	`filename` VARCHAR(256) NOT NULL DEFAULT "",
	`filepath` VARCHAR(512) NOT NULL DEFAULT "", 	
	`status` TINYINT UNSIGNED NOT NULL DEFAULT 0,
	`dateAdded` DATETIME NULL,
	PRIMARY KEY (`id`),
	FOREIGN KEY (`userID`) REFERENCES `users` (`id`),
	INDEX(`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- userstats
create table `userstats` (
	`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	`userID` INTEGER UNSIGNED NULL DEFAULT NULL,
	`conversionCalls` INTEGER UNSIGNED NOT NULL DEFAULT 0,	
	`conversionFailed` INTEGER UNSIGNED NOT NULL DEFAULT 0,				
	`dateAdded` DATETIME NULL,
	PRIMARY KEY (`id`),
	INDEX(`dateAdded`),
	FOREIGN KEY (`userID`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- contact
create table `contact` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(128) NOT NULL DEFAULT "",
  `email` VARCHAR(128) NOT NULL DEFAULT "",
  `phone` VARCHAR(128) NOT NULL DEFAULT "",  
  `message` TEXT NOT NULL,
  `ipAddress` VARCHAR(32) NOT NULL DEFAULT "",  
  `status` TINYINT UNSIGNED NOT NULL DEFAULT 0,    
  `dateAdded` DATETIME NULL,
  `dateUpdated` DATETIME NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- settings
create table `settings` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(128) NOT NULL DEFAULT "",
  `description` VARCHAR(256) NOT NULL DEFAULT "",   
  `value` VARCHAR(16384) NOT NULL DEFAULT "",  
  `dateAdded` DATETIME NULL,
  `dateUpdated` DATETIME NULL,
  PRIMARY KEY (`id`),
  UNIQUE(`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- job
create table `job` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `userID` INTEGER UNSIGNED NULL DEFAULT NULL,  
  `filename` VARCHAR(256) NOT NULL DEFAULT "",     
  `filepath` VARCHAR(512) NOT NULL DEFAULT "",   
  `response` TEXT NOT NULL,  
  `duration` INTEGER UNSIGNED NOT NULL DEFAULT 0,  
  `phase` TINYINT NOT NULL DEFAULT 1,   
  `subphase` INTEGER NOT NULL DEFAULT 0,    
  `status` TINYINT NOT NULL DEFAULT 0,  
  `processID` INTEGER NOT NULL DEFAULT 0,  
  `documentID` INTEGER UNSIGNED NULL DEFAULT NULL,	  
  `ipAddress` VARCHAR(256) NOT NULL DEFAULT "",     
  `dateAdded` DATETIME NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`userID`) REFERENCES `users` (`id`),
  FOREIGN KEY (`documentID`) REFERENCES `documentconversion` (`id`),
  INDEX(`processID`),
  INDEX(`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- systemlog
create table `systemlog` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `system` VARCHAR(255) NOT NULL DEFAULT "",
  `userID` VARCHAR(64) NOT NULL DEFAULT "",
  `message` TEXT NOT NULL,
  `type` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `dateAdded` DATETIME NULL,
  `dateUpdated` DATETIME NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- cron
create table `cron` (
	`id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(64) NOT NULL DEFAULT "",
	`description` VARCHAR(256) NOT NULL DEFAULT "",	
	`status` INTEGER UNSIGNED NOT NULL DEFAULT 1,
	`dateLastRun` DATETIME NULL,
	PRIMARY KEY (`id`),
	INDEX(`name`)
)ENGINE = InnoDB DEFAULT CHARSET=utf8;

