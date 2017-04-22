<?php
	
	// Cron Document Conversion - performs document conversion async.
	
	// change dir to API root
	@chdir( "../" );
	
	
	// includes
	require_once "src/includecore.php";
	
	
	
	// CronDocumentConversion
	class CronDocumentConversion
	{
		// members
		protected $workerId = 1;
		
		
		// run
		public function run()
		{		
			global $argv;	
			global $dateTimeZone;
			
			// worker id
			if( isset( $argv[1] ) )
				$this->workerId = $argv[1];
			
			// verify if process already running
			if( $this->isProcess() )
				return;
			
			// is alive
			ApiLogging::isAlive( "crondocumentconversion" . $this->workerId );
			
			// logging
			ApiLogging::logApp( "[CronDocumentConversion " . $this->workerId . "] Start Document Conversion cron" );
			
			while(1)
			{
				// process job
				$status = $this->processJob( $isJob );
				if( !$isJob )
				{
					// pause
					sleep(1);
					continue;
				}
			}
			
			// logging
			ApiLogging::logApp( "[CronDocumentConversion " . $this->workerId . "] Completed Document Conversion cron" );
		}
		
		
		// initialization
		protected function initialization()
		{
			global $dblink;
			
			// conf file
			if( !ApiConfFile::load() )
			{
				ApiLogging::logError( "[CronDocumentConversion " . $this->workerId . "] Failed to load config file" );					
				return false;
			}
			
			// connect db
			$dblink = ApiDb::connect();
			if( is_bool( $dblink ) )
			{
				ApiLogging::logError( "[CronDocumentConversion " . $this->workerId . "] Failed to connect to database server" );				
				return false;
			}
				
			return true;
		}
		
		
		// process job
		protected function processJob( &$isJob )
		{
			global $user;
			global $documentID;
			
			$isJob = true;
				
			// initialization
			if( !$this->initialization() )
			{
				ApiLogging::logError( "[CronDocumentConversion " . $this->workerId . "] Failed to initialize engine" );
				return false;
			}
			
			// get job (if any)
			$job = ApiDb::jobTableGetNext( $this->workerId );
			if( is_bool( $job ) )
			{
				$isJob = false;
				ApiDb::disconnect();
				return false;
			}			
			
			$filename = $job["filename"];
			$filepath = $job["filepath"];
			
			// logging
			ApiLogging::logApp( "[CronDocumentConversion " . $this->workerId . "] Processing job " . $job["id"] );
							
			// load user
			$user = ApiDb::userTableGetById( $job["userID"] );
			if( is_bool( $user ) )
			{
				// set response
				if( !ApiDb::jobTableUpdate( $job["id"], 0, "", 0 ) )
					ApiLogging::logError( "[CronDocumentConversion " . $this->workerId . "] Failed to update job " . $job["id"] );				
				
				ApiLogging::logError( "[CronDocumentConversion " . $this->workerId . "] Failed to load user " . $job["userID"] );
				ApiDb::cronTableUpdateStatus( "CronDocumentConversion" . $this->workerId, false );				
				ApiDb::disconnect();
				return false;
			}
			
			// document conversion
			$documentID = ApiDb::documentConversionTableAdd( $user["id"], $filename, $filepath, "", 0, "", 2 );
			if( is_bool( $documentID ) )
			{
				// set response
				if( !ApiDb::jobTableUpdate( $job["id"], 0, "", 0 ) )
					ApiLogging::logError( "[CronDocumentConversion " . $this->workerId . "] Failed to update job " . $job["id"] );	
				
				ApiLogging::logError( "[CronDocumentConversion " . $this->workerId . "] Failed to create document conversion for filename: " . $filename );		
				ApiDb::cronTableUpdateStatus( "CronDocumentConversion" . $this->workerId, false );				
				ApiDb::disconnect();
				return false;				
			}
			
			// set document ID
			if( !ApiDb::jobTableUpdateDocument( $job["id"], $documentID ) )
			{
				// set response
				if( !ApiDb::jobTableUpdate( $job["id"], 0, "", 0 ) )
					ApiLogging::logError( "[CronDocumentConversion " . $this->workerId . "] Failed to update job " . $job["id"] );	
				
				ApiLogging::logError( "[CronDocumentConversion " . $this->workerId . "] Failed to update job documentID " . $job["id"] );
				ApiDb::cronTableUpdateStatus( "CronDocumentConversion" . $this->workerId, false );				
				ApiDb::disconnect();
				return false;
			}
			
			$startTime = time();
			
			// core function
			$response = ApiModuleDocumentCore::processDocument( $filename, $filepath );
			
			$duration = time() - $startTime;
			if( $duration < 0 )
				$duration = 0;
			
			// encode response
			$response = @json_encode( $response );

			// reconnect to db
			ApiDb::reconnect();
			
			// set response
			if( !ApiDb::jobTableUpdate( $job["id"], 1, $response, $duration ) )
			{
				ApiLogging::logError( "[CronDocumentConversion " . $this->workerId . "] Failed to update job " . $job["id"] );				
				ApiDb::cronTableUpdateStatus( "CronDocumentConversion" . $this->workerId, false );				
				ApiDb::disconnect();
				return false;
			}
			
			// update cron status
			ApiDb::cronTableUpdateStatus( "CronDocumentConversion" . $this->workerId, true );				
			
			// logging
			ApiLogging::logApp( "[CronDocumentConversion " . $this->workerId . "] Job " . $job["id"] . " completed" );
			
			// disconnect db
			ApiDb::disconnect();
			
			return true;		
		}
		
		
		// whether process exists
		protected function isProcess()
		{
			global $osType;
			
			if( strtolower( $osType ) != "linux" )
				return false;
				
			$processName = "/api/cron && php ./CronDocumentConversion.php " . $this->workerId;
			
			// list process
			$cmd = "ps ax | grep '" . $processName . "' | grep -v 'grep' | wc -l";
			$output = array();
			$result = exec($cmd, $output, $status);
			if( $status != 0 )
				return true;
			
			if( count($output) != 1 )
				return true;
			
			$noInst = intval($output[0]);
			if( $noInst > 1 )
				return true;
				
			return false;
		}
		
	};
	
	
	// run
	$cron = new CronDocumentConversion();
	$cron->run();	

?>