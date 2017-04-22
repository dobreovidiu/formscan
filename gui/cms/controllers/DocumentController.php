<?php
	
	// Document Controller - management of user sessions.
	
	
	// includes
	include "../../conf/appconf.php";			
	include "../managers/Db.php";
	include "../managers/SessionManager.php";
	include "../managers/Logger.php";
	include "../models/SystemLog.php";
	include "../models/User.php";
	include "../models/UserSession.php";
	include "../models/UserApiKey.php";
	include "../models/Settings.php";	
	include "../models/DocumentPending.php";			
	include "../utils/ApiUtils.php";			
	
	
	
	// DocumentController
	class DocumentController
	{
		
		// process
		static public function process()
		{
			if( !isset( $_POST["_gt_json"] ) )
				return;
				
			// encoding
			header( "Content-type:text/javascript; charset=UTF-8" );				
			
			// parse request
			$json = json_decode( stripslashes( $_POST["_gt_json"] ) );
			
			// get action
			if( !isset( $json->{'action'} ) )
				return;
				
			$action = $json->{'action'};
			
			// check
			if( $action == "check" )
				self::processCheck( $json );	
			else
			// checkasync
			if( $action == "checkasync" )
				self::processCheckAsync( $json );			
			else
			// getcheckstatus
			if( $action == "getsearchstatus" )
				self::processGetCheckStatus( $json );								
		}
		
		
		// check
		static protected function processCheck( $json )
		{
			global $serviceUrl;
			
			// start session
			if( !SessionManager::start() )
				return;
				
			// user id
			$userID = SessionManager::getUserId();	
			if( empty( $userID ) )
				return;
			
			$logUsername = SessionManager::getUsername();	
			
			// document pending
			$document = new DocumentPending();
			if( !$document->loadPending( $userID ) )
			{
				$ret = array( 	"success" 	=> "false",
								"exception"	=> "There is currently no document pending for processing."
							 );
				
				echo json_encode( $ret );			
				return;
			}
			
			// api key
			$key = new UserApiKey();
			if( !$key->loadAdminKey() )
			{
				$ret = array( 	"success" 	=> "false",
								"exception"	=> "Failed to load service key. Please contact support."
							 );
				
				echo json_encode( $ret );			
				return;
			}

			$serviceKey = $key->key;
			
			// ipAddress
			$ipAddress = ApiUtils::getIpAddress();
			
			// request url
			$requestUrl = $serviceUrl . "/document2app?key=" . urlencode( $serviceKey ) . "&filename=" . urlencode( $document->filename ) . "&filepath=" . urlencode( $document->filepath ) . "&ipaddress=" . urlencode( $ipAddress );													   
			
			$httpCode 	= "";
			$start 		= time();
			
			// run check
			$result = ApiUtils::getPage( $requestUrl, $httpCode );
			if( is_bool( $result ) )
			{
				$ret = array( 	"success" 	=> "false",
								"exception"	=> "Failed to contact Form Scan service. Please contact support."
							 );
				
				echo json_encode( $ret );			
				return;
			}
			
			$data = @json_decode( $result, true );
			if( is_bool( $data ) || !isset( $data["status"] ) )
			{
				$ret = array( 	"success" 	=> "false",
								"exception"	=> "Wrong response from Form Scan service. Please contact support."
							 );
				
				echo json_encode( $ret );			
				return;
			}
			
			// update document processing
			$document->updateProcessed();				
			
			// error			
			if( $data["status"] != "ok" )
			{
				$ret = array( 	"success" 	=> "false",
								"exception"	=> "Unfortunately, the Form Scan was unsuccessful."
							 );
				
				echo json_encode( $ret );			
				return;
			}		

			$time = time() - $start;
			if( $time < 0 )
				$time = 0;
			
			// result
			$ret = array( 	"success" 		=> "true",
							"exception"		=> "",
							"duration"		=> number_format( $time )
						 );
			
			echo json_encode( $ret );
		}
		
		
		// check async
		static protected function processCheckAsync( $json )
		{
			global $serviceUrl;
			
			// start session
			if( !SessionManager::start() )
				return;
				
			// user id
			$userID = SessionManager::getUserId();	
			if( empty( $userID ) )
				return;
			
			$logUsername = SessionManager::getUsername();	
			
			// document pending
			$document = new DocumentPending();
			if( !$document->loadPending( $userID ) )
			{
				$ret = array( 	"success" 	=> "false",
								"exception"	=> "There is currently no document pending for processing."
							 );
				
				echo json_encode( $ret );			
				return;
			}
			
			// verify extension
			$ext = "";
			$pos = strrpos( $document->filename, "." );
			if( !is_bool( $pos ) )
				$ext = strtolower( trim( substr( $document->filename, $pos + 1 ) ) );
			
			if( !in_array( $ext, array( "pdf", "doc", "docx", "xls", "xlsx", "jpg", "jpeg", "png", "gif", "test" ) ) )
			{
				$ret = array( 	"success" 	=> "false",
								"exception"	=> "Unsupported document extension: " . $ext. "<br><br>Formats accepted: PDF, Word (DOC, DOCX), Excel (XLS, XLSX), Image (PNG, JPG/JPEG, GIF)."
							 );
				
				echo json_encode( $ret );			
				return;				
			}
			
			// api key
			$key = new UserApiKey();
			if( !$key->loadAdminKey() )
			{
				$ret = array( 	"success" 	=> "false",
								"exception"	=> "Failed to load service key. Please contact support."
							 );
				
				echo json_encode( $ret );			
				return;
			}

			$serviceKey = $key->key;
			
			// ipAddress
			$ipAddress = ApiUtils::getIpAddress();
			
			// request url
			$requestUrl = $serviceUrl . "/document2appasync?key=" . urlencode( $serviceKey ) . "&filename=" . urlencode( $document->filename ) . "&filepath=" . urlencode( $document->filepath ) . "&ipaddress=" . urlencode( $ipAddress );									   
			
			$httpCode 	= "";
			$start 		= time();
			
			// run check
			$result = ApiUtils::getPage( $requestUrl, $httpCode );
			if( is_bool( $result ) )
			{
				$ret = array( 	"success" 	=> "false",
								"exception"	=> "Failed to contact Form Scan service. Please contact support."
							 );
				
				echo json_encode( $ret );			
				return;
			}
						
			// decode response
			$data = @json_decode( $result, true );
			if( is_bool( $data ) || !isset( $data["status"] ) )
			{
				$ret = array( 	"success" 	=> "false",
								"exception"	=> "Wrong response from Form Scan service. Please contact support. " . $result
							 );
				
				echo json_encode( $ret );			
				return;
			}
			
			// update document processing
			$document->updateProcessed();	
			
			// error
			if( $data["status"] != "ok" )
			{
				if( !isset( $data["reason"] ) )
					$data["reason"] = "not specified";
				
				$ret = array( 	"success" 	=> "false",
								"exception"	=> "Unfortunately, the Form Scan was unsuccessful. Reason: " . $data["reason"]
							 );
				
				echo json_encode( $ret );
				return;
			}
			
			// jobID
			if( !isset( $data["jobID"] ) )
				$data["jobID"] = "";
			
			// result
			$ret = array( 	"success" 	=> "true",
							"jobID"		=> $data["jobID"]
						 );					
			
			// return result
			echo json_encode( $ret );
		}
		
		
		// get check status
		static protected function processGetCheckStatus( $json )
		{
			global $serviceUrl;
			
			if( !isset( $json->{'jobID'} ) )
				return;
			
			// decode
			$jobID = urldecode( $json->{'jobID'} );
			
			// start session
			if( !SessionManager::start() )
				return;
				
			// user id
			$userID = SessionManager::getUserId();	
			if( empty( $userID ) )
				return;
			
			$logUsername = SessionManager::getUsername();	
			
			// api key
			$key = new UserApiKey();
			if( !$key->loadAdminKey() )
			{
				$ret = array( 	"success" 	=> "false",
								"exception"	=> "Failed to load service key. Please contact support."
							 );
				
				echo json_encode( $ret );			
				return;
			}

			$serviceKey = $key->key;
			
			// request url
			$requestUrl = $serviceUrl . "/document2appstatus?key=" . urlencode( $serviceKey ) . "&jobID=" . urlencode( $jobID );
			
			$httpCode 	= "";
			$start 		= time();
			
			// run check
			$result = ApiUtils::getPage( $requestUrl, $httpCode );
			if( is_bool( $result ) )
			{
				$ret = array( 	"success" 	=> "false",
								"exception"	=> "Failed to contact Form Scan service. Please contact support.",
								"completed" => "0"
							 );
				
				echo json_encode( $ret );			
				return;
			}
			
			// decode response
			$data = @json_decode( $result, true );
			if( is_bool( $data ) || !isset( $data["status"] ) )
			{
				$ret = array( 	"success" 	=> "false",
								"exception"	=> "Wrong response from Form Scan service. Please contact support. " . $result,
								"completed" => "0"
							 );
				
				echo json_encode( $ret );			
				return;
			}
			
			// error
			if( $data["status"] != "ok" )
			{
				if( !isset( $data["reason"] ) )
					$data["reason"] = "not specified";
				
				$ret = array( 	"success" 	=> "false",
								"exception"	=> "Unfortunately, the Form Scan was unsuccessful. Reason: " . $data["reason"],
								"completed" => "1"
							 );
				
				echo json_encode( $ret );			
				return;
			}
			
			// logs
			if( !isset( $data["logs"] ) )
				$data["logs"] = array();
			
			$no = count( $data["logs"] );
			for( $i = 0; $i < $no; $i++ )
			{
				$data["logs"][$i] = str_replace( "\r\n", "<br>", $data["logs"][$i] );				
				$data["logs"][$i] = str_replace( "\n", "<br>", $data["logs"][$i] );
				$data["logs"][$i] = str_replace( "\t", "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;", $data["logs"][$i] );								
			}
			
			// title
			if( !isset( $data["title"] ) )
				$data["title"] = 0;
			
			// duration
			if( !isset( $data["duration"] ) )
				$data["duration"] = 0;
			
			// completed
			$data["completed"] = "0";			
			if( intval( $data["jobStatus"] ) == 1 )
				$data["completed"] = "1";
			
			// result
			$ret = array( 	"success" 		=> "true",
							"exception"		=> "",
							"logs"			=> $data["logs"],
							"duration"		=> $data["duration"],
							"completed"		=> $data["completed"],
							"title"			=> $data["title"]
						 );
				
			// return result				
			echo json_encode( $ret );
		}
		
	};
	
	
	// main function
	DocumentController::process();
	
?>