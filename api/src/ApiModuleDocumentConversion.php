<?php
	
	// Api Module Document Conversion - Conversion of documents to FatFinger app API functions.
	
	
	// ApiModuleDocumentConversion
	class ApiModuleDocumentConversion
	{
			 
		// Document2App API
		static public function processDocument( $request )
		{
			global $user;
			
			// parameters
			if( !isset( $request["key"] ) || empty( $request["key"] ) )
			{
				// set response
				ApiLogging::logError( "[ApiModuleDocumentConversion::processDocument] Missing request key" );					
				$response = array( "status" => "error", "reason" => "Missing key." );
				return $response;
			}
			
			if( !isset( $request["filename"] ) || empty( $request["filename"] ) )
			{
				// set response
				ApiLogging::logError( "[ApiModuleDocumentConversion::processDocument] Missing request filename" );					
				$response = array( "status" => "error", "reason" => "Missing filename." );
				return $response;
			}
			
			if( !isset( $request["filepath"] ) || empty( $request["filepath"] ) )
			{
				// set response
				ApiLogging::logError( "[ApiModuleDocumentConversion::processDocument] Missing request filepath" );					
				$response = array( "status" => "error", "reason" => "Missing filepath." );
				return $response;
			}
			
			$key		= $request["key"];
			$filename	= $request["filename"];
			$filepath	= $request["filepath"];
			
			$onlyparser = 0;
			if( isset( $request["onlyparser"] ) || empty( $request["onlyparser"] ) )
				$onlyparser = intval( $request["onlyparser"] );
			
			// verify key
			$user = ApiDb::userTableGetByKey( $key );
			if( is_bool( $user ) )
			{
				// set response
				ApiLogging::logError( "[ApiModuleDocumentConversion::processDocument] Invalid user key " . $key );
				$response = array( "status" => "error", "reason" => "Invalid key." );
				return $response;
			}
			
			// core function
			$response = ApiModuleDocumentCore::processDocument( $filename, $filepath, $onlyparser );

			return $response;
		}
		
	
		// Document2App Asynchronous API
		static public function processDocumentAsync( $request )
		{
			global $documentWorkerNo;
			
			// parameters
			if( !isset( $request["key"] ) || empty( $request["key"] ) )
			{
				// set response
				ApiLogging::logError( "[ApiModuleDocumentConversion::processDocumentAsync] Missing request key" );					
				$response = array( "status" => "error", "reason" => "Missing key." );
				return $response;
			}
			
			if( !isset( $request["filename"] ) || empty( $request["filename"] ) )
			{
				// set response
				ApiLogging::logError( "[ApiModuleDocumentConversion::processDocumentAsync] Missing request filename" );					
				$response = array( "status" => "error", "reason" => "Missing filename." );
				return $response;
			}
			
			if( !isset( $request["filepath"] ) || empty( $request["filepath"] ) )
			{
				// set response
				ApiLogging::logError( "[ApiModuleDocumentConversion::processDocumentAsync] Missing request filepath" );					
				$response = array( "status" => "error", "reason" => "Missing filepath." );
				return $response;
			}
			
			$key		= $request["key"];
			$filename	= $request["filename"];
			$filepath	= $request["filepath"];
			
			// verify key
			$user = ApiDb::userTableGetByKey( $key );
			if( is_bool( $user ) )
			{
				// set response
				ApiLogging::logError( "[ApiModuleDocumentConversion::processDocumentAsync] Invalid user key " . $key );
				$response = array( "status" => "error", "reason" => "Invalid key." );
				return $response;
			}
			
			// get available process
			$processList = array();
			for( $i = 1; $i <= $documentWorkerNo; $i++ )
				array_push( $processList, $i );
			
			shuffle( $processList );
			
			$processID = $processList[0];
			for( $i = 1; $i <= $documentWorkerNo; $i++ )
			{
				if( ApiDb::jobTableIsAvailable( $processList[$i-1] ) )
				{
					$processID = $processList[$i-1];
					break;
				}
			}
			
			if( !isset( $request["ipaddress"] ) || empty( $request["ipaddress"] ) )
				$ipAddress = ApiLogging::getIpAddress();
			else
				$ipAddress = $request["ipaddress"];
			
			// add job
			$jobID = ApiDb::jobTableAdd( $user["id"], $filename, $filepath, $processID, $ipAddress );
			if( is_bool( $jobID ) )
			{
				// set response
				ApiLogging::logError( "[ApiModuleDocumentConversion::processDocumentAsync] Failed to add job" );
				$response = array( "status" => "error", "reason" => "Failed to set search job." );
				return $response;
			}

			// success
			$response = array( 	"status" 	=> "ok",
								"jobID"		=> $jobID
							 );
							 
			// return response
			return $response;
		}
		
		
		// Document2App Status API
		static public function processCheckStatus( $request )
		{
			// parameters
			if( !isset( $request["key"] ) || empty( $request["key"] ) )
			{
				// set response
				ApiLogging::logError( "[ApiModuleDocumentConversion::processCheckStatus] Missing request key" );					
				$response = array( "status" => "error", "reason" => "Missing key." );
				return $response;
			}
			
			if( !isset( $request["jobID"] ) || empty( $request["jobID"] ) )
			{
				// set response
				ApiLogging::logError( "[ApiModuleDocumentConversion::processCheckStatus] Missing request jobID" );					
				$response = array( "status" => "error", "reason" => "Missing jobID." );
				return $response;
			}
			
			$key	= $request["key"];
			$jobID	= $request["jobID"];		
			
			// verify key
			$user = ApiDb::userTableGetByKey( $key );
			if( is_bool( $user ) )
			{
				// set response
				ApiLogging::logError( "[ApiModuleDocumentConversion::processCheckStatus] Invalid user key " . $key );
				$response = array( "status" => "error", "reason" => "Invalid key." );
				return $response;
			}
			
			// verify if job exists
			$job = ApiDb::jobTableGet( $user["id"], $jobID );
			if( is_bool( $job ) )
			{
				// set response
				ApiLogging::logError( "[ApiModuleDocumentConversion::processCheckStatus] Invalid job " . $jobID );
				$response = array( "status" => "error", "reason" => "Invalid job." );
				return $response;
			}
				
			$msgList = array();
			
			// document under processing/completed
			if( !empty( $job["documentID"] ) )
			{
				// get logs
				$logs = ApiDb::documentAnalysisTableGetUnread( $job["documentID"] );
				if( is_bool( $logs ) )
				{
					// set response
					ApiLogging::logError( "[ApiModuleDocumentConversion::processCheckStatus] Failed to get document logs " . $job["documentID"] );
					$response = array( "status" => "error", "reason" => "Failed to get document logs." );
					return $response;
				}
				
				$ids = array();
				foreach( $logs as $item )
				{
					array_push( $ids, 		$item["id"] );
					array_push( $msgList,	$item["text"] );
				}
				
				// mark logs as read
				if( !ApiDb::documentAnalysisTableMarkRead( $ids ) )
				{
					// set response
					ApiLogging::logError( "[ApiModuleDocumentConversion::processCheckStatus] Failed to mark logs as read for " . $job["documentID"] );
					$response = array( "status" => "error", "reason" => "Failed to mark logs as read." );
					return $response;
				}				
			}
			
			// completed
			if( intval( $job["status"] ) == 1 )
			{
				$response = @json_decode( $job["response"], true );
				
				$response["jobStatus"]	= $job["status"];
				$response["duration"]	= $job["duration"];
				$response["logs"] 		= $msgList;
			}
			else
			// in progress
			{
				$response = array( 	"status" 	=> "ok",
									"jobStatus"	=> $job["status"],
									"logs"		=> $msgList
								 );
			}
							 
			// return response
			return $response;
		}
		
	};
	
	
?>