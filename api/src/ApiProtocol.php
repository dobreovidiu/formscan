<?php

	// API protocol - request/response utility functions.
	
	
	// ApiProtocol
	class ApiProtocol
	{
	
		// deserialize request
		static public function deserializeRequest()
		{
			global $argv;
			global $showConversionLogs;
			
			// get source IP
			$sourceIp = ApiLogging::getIpAddress();
	
			// set request
			$request["status"] 	 	= 1;
			$request["reason"]	 	= "ok";
			$request["sourceip"] 	= $sourceIp;
			
			// action
			if( isset( $_REQUEST["action"] ) )
				$request["action"] = $_REQUEST["action"];
			else
				$request["action"] = "";
				
			// key
			if( isset( $_REQUEST["key"] ) )
				$request["key"] = $_REQUEST["key"];
			else
				$request["key"] = "";			

			// ipaddress
			if( isset( $_REQUEST["ipaddress"] ) )
				$request["ipaddress"] = $_REQUEST["ipaddress"];
			else
				$request["ipaddress"] = "";				

			// filename
			if( isset( $_REQUEST["filename"] ) )
				$request["filename"] = $_REQUEST["filename"];
			else
				$request["filename"] = "";	

			// filepath
			if( isset( $_REQUEST["filepath"] ) )
				$request["filepath"] = $_REQUEST["filepath"];
			else
				$request["filepath"] = "";	

			// onlyparser
			if( isset( $_REQUEST["onlyparser"] ) )
				$request["onlyparser"] = $_REQUEST["onlyparser"];
			else
				$request["onlyparser"] = "";	
			
			// jobID
			if( isset( $_REQUEST["jobID"] ) )
				$request["jobID"] = $_REQUEST["jobID"];
			else
				$request["jobID"] = "";
			
			// logs
			if( isset( $_REQUEST["logs"] ) )
				$request["logs"] = $_REQUEST["logs"];
			else
				$request["logs"] = "";		
			
			// show logs
			$showConversionLogs = 0;
			if( isset( $request["logs"] ) && !empty( $request["logs"] ) && intval( $request["logs"] ) )
				$showConversionLogs = 1;			
			
			// verify if valid fields
			self::verifyRequest( $request, $request["status"], $request["reason"] );
			
			return $request;
		}
		
		
		// verify request arguments
		static protected function verifyRequest( $request, &$errorCode, &$errorReason )
		{
			global $showConversionLogs;
			
			// init error code
			$errorCode   = 1;
			$errorReason = "";		
			
			// action missing
			if( strlen( $request["action"] ) <= 0 )
			{
				$errorCode   = 0;
				$errorReason = "API action missing.";
				return false;
			}
			
			// wrong action
			if( 0 != strcasecmp( $request["action"], "document2app" ) &&
				0 != strcasecmp( $request["action"], "document2appasync" ) &&
				0 != strcasecmp( $request["action"], "document2appstatus" ) )
			{
				$errorCode   = 0;
				$errorReason = "Wrong API action.";
				return false;
			}
			
			// content type			
			if( isset( $showConversionLogs ) && $showConversionLogs == 1 )
				header( 'Content-type: text/plain; charset=utf-8' );
			
			return true;
		}
		
	
		// serialize response
		static public function serializeResponse( $request, $response )
		{
			global $showConversionLogs;
			
			// no request
			if( false == $request )
			{
				// content type			
				header( 'Content-type: application/json; charset=utf-8' );
			
				// send internal error
				$response = array( "status" => "error", "reason" => "initialization error" );
				$result = json_encode( $response );
				echo $result;
				return;
			}
			
			// no response
			if( false == $response )
			{			
				// content type			
				header( 'Content-type: application/json; charset=utf-8' );
			
				// send protocol error
				$result = json_encode( $request );
				echo $result;
				return;
			}						
			
			// verify if should output response			
			if( isset( $response["output_client_response"] ) && !$response["output_client_response"] )				
				return;
			
			// encode result
			$result = @json_encode( $response );				
			
			// logging
			ApiLogging::logApp( "Response: " . $result );			
			
			// content type			
			if( isset( $showConversionLogs ) && $showConversionLogs == 1 )	
				echo "\n\n\nRESPONSE:\n\n";
			else
				header( 'Content-type: application/json; charset=utf-8' );			
			
			// send response
			echo $result;
		}	
		
	};
	
?>