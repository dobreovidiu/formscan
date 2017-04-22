<?php
	
	// Api Module Document Core - document core functions.
	
	
	// ApiModuleDocumentCore
	class ApiModuleDocumentCore
	{
		
		
		// process check
		static public function processDocument( $filename, $filepath, $onlyparser = 0 )
		{
			global $viewParserOutput;	
			global $documentID;
			
			$viewParserOutput = $onlyparser;
			
			// logging
			DocumentUtils::logAnalysis( "Document processing started" );

			$startTimeTotal = time();
			
			
			// INITIALIZE
			if( !self::initialize() )
			{
				$response = array(	"status"		=> "error",
									"reason"		=> "Failed to initialize system",
									"type"			=> ""
								  );				
				return $response;
			}
			
			$response = false;
			
			
			// PARSE DOCUMENT
			$document = DocumentParser::parse( $filename, $filepath, $type );
			if( is_bool( $document ) )
			{
				$response = array(	"status"		=> "error",
									"reason"		=> "Failed to parse file",
									"type"			=> $type
								  );
			}
			
			
			// CREATE APP
			if( !is_bool( $document ) )
			{
				if( !FatFingerManager::createApp( $document ) )
				{
					$response = array(	"status"		=> "error",
										"reason"		=> "Failed to create Fat Finger app",
										"type"			=> $document->getType()
									  );
				}
			}
			
			
			// duration
			$duration = time() - $startTimeTotal;

			// title
			$title = "";
			if( !is_bool( $document ) )
				$title = $document->getTitle();

				
			// STORE RESULTS
			if( !$onlyparser || ( !isset( $documentID ) || is_bool( $documentID ) ) )
			{				
				self::storeResults( $filename, $filepath, $response, $duration, $title );
			}
			
			
			// SUCCESS
			if( is_bool( $response ) )
			{
				$response = array( 	"status" 	=> "ok",
									"title"		=> $title
								 );
			}
			
			return $response;
		}
		
		
		// initialize
		static protected function initialize()
		{			
			global $keywords;
			
			// keywords
			$keywords = ApiDb::keywordTableGet();
			if( is_bool( $keywords ) )
				return false;
			
			return true;
		}
		
		
		// store results
		static protected function storeResults( $filename, $filepath, $response, $duration, $title )
		{
			global $user;
			global $documentID;
			global $docAnalysis;
			
			// reconnect to db
			ApiDb::reconnect();
			
			$isNewDocument = true;
							 
			// insert document conversion
			if( !isset( $documentID ) || is_bool( $documentID ) )
			{
				// add document
				$documentID = ApiDb::documentConversionTableAdd( $user["id"], $filename, $filepath, $response["type"], $duration, $title );
				
				if( is_bool( $documentID ) )
				{
					// logging
					ApiLogging::logError( "[ApiModuleDocumentCore::storeResults] Failed to add response to document conversion table for file: " . $filename );					
				}
			}
			else
			{
				$isNewDocument = false;
				
				// update document conversion
				$status = ApiDb::documentConversionTableUpdate( $documentID, $response["type"], $duration, $title );
				
				if( !$status )
				{
					// logging
					ApiLogging::logError( "[ApiModuleDocumentCore::storeResults] Failed to update response to document conversion table for file: " . $filename );					
				}				
			}
			
			
			// analysis
			if( isset( $docAnalysis ) && !is_bool( $docAnalysis ) && !is_bool( $documentID ) && $isNewDocument )
			{
				// document analysis
				foreach( $docAnalysis as $item )
				{
					if( !ApiDb::documentAnalysisTableAdd( $documentID, $item ) )
					{
						// logging
						ApiLogging::logError( "[ApiModuleDocumentCore::storeResults] Failed to add document analysis item for file: " . $filename );
					}
				}
			}
			
			
			// user stats
			if( !is_bool( $documentID ) )
			{
				$date = date( "Y-m-d", time() );
				
				// user stats add
				if( !ApiDb::userStatsTableAdd( $user["id"], $date, 1 ) )
				{
					// logging
					ApiLogging::logError( "[ApiModuleDocumentCore::storeResults] Failed to add user stats" );
				}
			}
			
			return true;
		}
		
	};
	
	
?>