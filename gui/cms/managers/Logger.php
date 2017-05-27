<?php

	// Logger functions.
	

	// Logger
	class Logger
	{
		// members
		static protected $customLogFilename 		= "";
		static protected $customLogErrorFilename 	= "";
		static protected $db						= false;
		
		
		// set log files
		static public function setLogFiles( $logFilename, $logErrorFilename )
		{
			self::$customLogFilename		= $logFilename;
			self::$customLogErrorFilename	= $logErrorFilename;
		}
		
		
		// set page logging
		static public function setPageLogging()
		{
			global $logPageFilename;
			global $logPageErrorFilename;
			
			self::setLogFiles( $logPageFilename, $logPageErrorFilename );
		}
	
	
		// log app
		static public function logApp( $module, $userID, $msg )
		{
			global $logAppEnabled;
			global $logNewLine;
			global $logFilename;
			
			if( !$logAppEnabled )
				return;
			
			$fileMsg = "[" . gmdate("Y-m-d H:i:s", time() ) . "][" . $module . "][APP] " . $msg . $logNewLine;
			
			// file logging
			if( self::$customLogFilename != "" )
				@file_put_contents( self::$customLogFilename, $fileMsg, FILE_APPEND );
			else
			if( $logFilename != "" )
				@file_put_contents( $logFilename, $fileMsg, FILE_APPEND );

			// add db log
			self::addDbLog( $module, $userID, $msg, 1 );
		}
		
		
		// log info
		static public function logInfo( $module, $userID, $msg )
		{
			global $logInfoEnabled;
			global $logNewLine;			
			global $logFilename;			
			
			if( !$logInfoEnabled )
				return;
			
			$fileMsg = "[" . gmdate("Y-m-d H:i:s", time() ) . "][" . $module . "][INFO] " . $msg . $logNewLine;
			
			// file logging
			if( self::$customLogFilename != "" )
				@file_put_contents( self::$customLogFilename, $fileMsg, FILE_APPEND );
			else
			if( $logFilename != "" )			
				@file_put_contents( $logFilename, $fileMsg, FILE_APPEND );	

			// add db log
			self::addDbLog( $module, $userID, $msg, 2 );				
		}
		
		
		// log error
		static public function logError( $module, $userID, $msg )
		{
			global $logErrorEnabled;
			global $logNewLine;			
			global $logFilename;	
			global $logErrorFilename;
			
			if( !$logErrorEnabled )
				return;
				
			$fileMsg = "[" . gmdate("Y-m-d H:i:s", time() ) . "][" . $module . "][ERROR] " . $msg . $logNewLine;
			
			// file logging				
			if( self::$customLogFilename != "" )
				@file_put_contents( self::$customLogFilename, $fileMsg, FILE_APPEND );
			else
			if( $logFilename != "" )			
				@file_put_contents( $logFilename, $fileMsg, FILE_APPEND );

			if( self::$customLogErrorFilename != "" )
				@file_put_contents( self::$customLogErrorFilename, $fileMsg, FILE_APPEND );
			else
			if( $logErrorFilename != "" )			
				@file_put_contents( $logErrorFilename, $fileMsg, FILE_APPEND );

			// add db log
			self::addDbLog( $module, $userID, $msg, 3 );				
		}
		
		
		// add db log
		static public function addDbLog( $module, $userID, $msg, $type )
		{				
			// db connect
			if( is_bool( self::$db ) )
				self::$db = new Db();
				
			// init log
			$log = new SystemLog();
			$log->system	= $module;
			$log->userID	= $userID;
			$log->message	= $msg;
			$log->type		= $type;
			
			// save log
			if( !$log->save() )
				return false;
				
			return true;
		}
		
		
		// log mysql error
		static public function logMysqlError( $query )
		{
			self::logError( "Database", "", "MySQL error no " . @mysql_errno() . "  error: " . @mysql_error() . "  query: " . $query );
		}
		
		
		// log php info
		static public function logPhpInfo( $app )
		{
			// get info
			ob_start();
			phpinfo();
			$info = ob_get_contents();
			ob_end_clean();
			
			// get IP address
			$ipAddress = "";
			if( isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != "" )
				$ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
			else
			if( isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] != "" )
				$ipAddress = $_SERVER['REMOTE_ADDR'];			
			
			// output info
			@file_put_contents( "phpinfo/" . $app . "-phpinfo-" . time() . "-" . $ipAddress . ".html", $info );
		}
	
	};


?>