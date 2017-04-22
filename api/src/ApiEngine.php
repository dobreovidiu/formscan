<?php
	
	// Api Engine - web-service engine.
	
	
	// ApiEngine
	class ApiEngine
	{

		// process
		static public function process()
		{				
			global $dblink;		
			
			// initialization
			if( !self::initialization() )
			{
				// send internal error response
				ApiProtocol::serializeResponse( false, false );
				return;
			}
			
			// deserialize request
			$request = ApiProtocol::deserializeRequest();
			
			// protocol error
			if( isset( $request["status"] ) && $request["status"] != 1 )
			{
				// send protocol error response
				ApiProtocol::serializeResponse( $request, false );
				
				// close db
				@mysql_close( $dblink );
				return;
			}
			
			// process request
			$response = self::processRequest( $request );
			
			// serialize response
			ApiProtocol::serializeResponse( $request, $response );
			
			// close db
			@mysql_close( $dblink );
		}
		
		
		// initialization
		static public function initialization()
		{
			global $dblink;
			
			// conf file
			if( !ApiConfFile::load() )
			{
				ApiLogging::logError( "[ApiEngine::initialization] Failed to load config file" );					
				return false;
			}
			
			// connect db
			$dblink = ApiDb::connect();
			if( is_bool( $dblink ) )
			{
				ApiLogging::logError( "[ApiEngine::initialization] Failed to connect to database server" );				
				return false;
			}
				
			return true;
		}


		// process request
		static protected function processRequest( $request )
		{
			// DOCUMENT2APP
			if( 0 == strcasecmp( $request["action"], "document2app" ) )
				return ApiModuleDocumentConversion::processDocument( $request );
			else
			// DOCUMENT2APP ASYNC
			if( 0 == strcasecmp( $request["action"], "document2appasync" ) )
				return ApiModuleDocumentConversion::processDocumentAsync( $request );
			else
			// DOCUMENT2APP STATUS
			if( 0 == strcasecmp( $request["action"], "document2appstatus" ) )
				return ApiModuleDocumentConversion::processCheckStatus( $request );
			
			// invalid action
			ApiLogging::logError( "[ApiEngine::processRequest] Invalid request action " . $request["action"] );				
			$response = array( "status" => "error", "reason" => "Invalid request action." );
			
			return $response;
		}
		
	};
	
?>