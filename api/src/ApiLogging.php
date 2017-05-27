<?php
	
	// Api Logging - logging functions.
	
	
	// ApiLogging
	class ApiLogging
	{
		// globals
		static public $isAliveFilename	= false;
		

		// log app
		static public function logApp( $msg )
		{
			global $logAppEnabled;
			global $logFilename;
			
			if( !$logAppEnabled )
				return;
				
			$msg = "[" . date("Y-m-d H:i:s", time() ) . "][API][APP][" . self::getIpAddress() . "] " . $msg . "\n";
		
			@file_put_contents( $logFilename, $msg, FILE_APPEND );		
		}

		
		// log error
		static public function logError( $msg )
		{
			global $logErrorEnabled;
			global $logFilename;
			global $logFilenameError;
			
			if( !$logErrorEnabled )
				return;
				
			$msg = "[" . date("Y-m-d H:i:s", time() ) . "][API][ERROR][" . self::getIpAddress() . "] " . $msg . "\n";
		
			@file_put_contents( $logFilename, $msg, FILE_APPEND );	
			@file_put_contents( $logFilenameError, $msg, FILE_APPEND );			
		}
		
		
		// get IP address
		static public function getIpAddress()
		{
			$ipAddress = "No IP Address";
		
			// get ip address
			if( isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != "" )
				$ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
			else
			if( isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] != "" )
				$ipAddress = $_SERVER['REMOTE_ADDR'];

			return $ipAddress;
		}
		
		
		// is alive		
		static public function isAlive( $name = false )
		{
			if( !is_bool( $name ) )
			{
				self::$isAliveFilename = $name;
				@file_put_contents( "log/cron/" . self::$isAliveFilename . ".isalive", "" );
			}
			
			if( is_bool( self::$isAliveFilename ) )
				return;
			
			@file_put_contents( "log/cron/" . self::$isAliveFilename . ".isalive", date( "Y-m-d H:i:s", time() ) . "\n", FILE_APPEND );
		}
		
	};
	
?>