<?php

	// Api Conf File - management of application configuration file.	


	// ApiConfFile
	class ApiConfFile
	{
		// globals
		static protected $appConf = false;
		
	
		// load
		static public function load()
		{
			global $dbHost;
			global $dbUsername;
			global $dbPassword;
			global $dbName;
			
			self::$appConf["dbserver"] 		= $dbHost;
			self::$appConf["dbusername"]	= $dbUsername;
			self::$appConf["dbpassword"]	= $dbPassword;
			self::$appConf["dbname"]		= $dbName;
			
			return true;
		}
		
		
		// get db server
		static public function getDbServer()
		{
			return self::$appConf["dbserver"];
		}
		
		
		// get db username
		static public function getDbUsername()
		{
			return self::$appConf["dbusername"];
		}
		
		
		// get db password
		static public function getDbPassword()
		{
			return self::$appConf["dbpassword"];
		}
		
		
		// get db name
		static public function getDbName()
		{
			return self::$appConf["dbname"];
		}
		
	};


?>