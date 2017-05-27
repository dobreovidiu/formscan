<?php

	// Fat Finger Api - Fat Finger API Functions.


	// FatFingerApi
	class FatFingerApi
	{
		// globals		
		static protected $cookies 		= "";
		static protected $authToken		= "";
		
		
		
		// login
		static public function login()
		{
			global $fatFingerUsername;
			global $fatFingerPassword;
			global $fatFingerLoginUrl;
			
			
			// STEP 1: LOAD PAGE
			$page = ApiUtils::getPageEx( $fatFingerLoginUrl, ApiUtils::USER_AGENT, "", "", "", $isTimeout, $isProxyError );

			if( is_bool( $page ) )
			{
				ApiLogging::logError( "[FatFingerApi::login] Empty page load response from Fat Finger" );
				return false;
			}
			
			// logging
			FatFingerUtils::logFile( "fatfinger-loginpage1", $page );
			
			// get cookies
			self::$cookies = ApiUtils::getAllCookies( $page );

			// get token
			$token = ApiUtils::getFormField( $page, "__RequestVerificationToken" );
			if( is_bool( $token ) )
			{
				ApiLogging::logError( "[FatFingerApi::login] Missing token from page load response from Fat Finger" );
				return false;
			}
			
			
			// STEP 2: LOGIN
			$postData = "__RequestVerificationToken=" . 	urlencode( $token ) . 
						"&Email=" . 						urlencode( $fatFingerUsername ) . 
						"&Password=" . 						urlencode( $fatFingerPassword ) . 
						"&RememberMe=false";
						
			// send request
			$page = ApiUtils::postPageEx( $fatFingerLoginUrl, "", ApiUtils::USER_AGENT, "", "", "", $postData, 
										  $isTimeout, $isProxyError, $httpCode, $headerSent,
										  1, "application/x-www-form-urlencoded", self::$cookies );
			if( is_bool( $page ) )
			{
				ApiLogging::logError( "[FatFingerApi::login] Empty login response from Fat Finger" );
				return false;
			}
			
			// logging
			FatFingerUtils::logFile( "fatfinger-loginpage2", $page );
			
			// verify HTTP code
			$val = intval( $httpCode );
			if( $val != 302 )
			{
				ApiLogging::logError( "[FatFingerApi::login] Invalid login HTTP Code from Fat Finger: " . $httpCode );
				return false;
			}
			
			// get cookies
			$cookies2 = ApiUtils::getAllCookies( $page );
			if( !empty( $cookies2 ) )
				self::$cookies .= ";" . $cookies2;
			
			
			// STEP 3: AUTHORIZE
			$authUrl = "https://ccc.seeforge.com/Account/Authorize?client_id=web&response_type=token&state=%2F%2Fccc.seeforge.com%2F";
			
			$page = ApiUtils::getPageEx( $authUrl, ApiUtils::USER_AGENT, "", "", "", $isTimeout, $isProxyError, 1, 
										 "https://ccc.seeforge.com/", self::$cookies, 0 );

			if( is_bool( $page ) )
			{
				ApiLogging::logError( "[FatFingerApi::login] Empty page authorize login from Fat Finger" );
				return false;
			}
			
			// logging
			FatFingerUtils::logFile( "fatfinger-loginpage3", $page );
			
			// get header field
			$location = ApiUtils::getHeaderField( $page, "Location:" );
			if( is_bool( $location ) )
			{
				ApiLogging::logError( "[FatFingerApi::login] Missing auth token from authorize login response from Fat Finger" );
				return false;
			}				
			
			self::$authToken = ApiUtils::getUrlField( $location, "access_token" );
			if( is_bool( self::$authToken ) )
			{
				ApiLogging::logError( "[FatFingerApi::login] Missing auth token from login redirect from Fat Finger: " . $location );
				return false;
			}	

			
			// STEP 4: AUTH REDIRECT
			$page = ApiUtils::getPageEx( $location, ApiUtils::USER_AGENT, "", "", "", $isTimeout, $isProxyError, 1, 
										 "https://ccc.seeforge.com/", self::$cookies, 0 );

			if( is_bool( $page ) )
			{
				ApiLogging::logError( "[FatFingerApi::login] Empty page auth redirect from Fat Finger" );
				return false;
			}
			
			// logging
			FatFingerUtils::logFile( "fatfinger-loginpage4", $page );
			
			return true;
		}
		
		
		// create app
		static public function createApp( &$request )
		{
			global $fatFingerReportUrl;
			global $fatFingerReportApiUrl;
			
			// serialize
			$data = $request->serialize();
			
			// post data
			$postData = @json_encode( $data );
			
			// logging
			FatFingerUtils::logFile( "fatfinger-createapp1", $postData );		
			
			
			// STEP 1: CREATE APP
			$page = ApiUtils::postPageEx( $fatFingerReportApiUrl, $fatFingerReportUrl, ApiUtils::USER_AGENT, "", "", "", 
										  $postData, $isTimeout, $isProxyError, $httpCode, $headerSent, 
										  1, "", self::$cookies, 0,
										  array( "Content-Type: application/json;charset=utf-8", "Authorization: Bearer " . self::$authToken ) );
			
			if( is_bool( $page ) )
			{
				ApiLogging::logError( "[FatFingerApi::createApp] Empty API reports response from Fat Finger" );
				return false;
			}
			
			// logging
			FatFingerUtils::logFile( "fatfinger-createapp2", $page );
			
			// verify HTTP code
			$val = intval( $httpCode );
			if( $val != 201 )
			{
				ApiLogging::logError( "[FatFingerApi::createApp] Invalid API reports HTTP Code from Fat Finger: " . $httpCode );
				return false;
			}
			
			// get header field
			$location = ApiUtils::getHeaderField( $page, "Location:" );
			if( is_bool( $location ) )
			{
				ApiLogging::logError( "[FatFingerApi::createApp] Missing location redirect from API form create from Fat Finger" );
				return false;
			}		

			$location = trim( $location, "/" );
			
			$pos = strrpos( $location, "/" );
			if( is_bool( $pos ) )
			{
				ApiLogging::logError( "[FatFingerApi::createApp] Missing form ID from API form create from Fat Finger: " . $location );
				return false;
			}
			
			$formId = substr( $location, $pos + 1 );
			
			return $formId;			
		}
		
		
		// publish app
		static public function publishApp( $formId )
		{
			global $fatFingerPublishUrl;
			global $fatFingerReportUrl;

			// STEP 2: PUBLISH APP
			$publishUrl = $fatFingerPublishUrl . $formId;
			
			$page = ApiUtils::getPageEx( $publishUrl, ApiUtils::USER_AGENT, "", "", "", $isTimeout, $isProxyError, 1, 
										 $fatFingerReportUrl, self::$cookies, 0, array( "Authorization: Bearer " . self::$authToken ) );

			if( is_bool( $page ) )
			{
				ApiLogging::logError( "[FatFingerApi::publishApp] Empty page publish form from Fat Finger" );
				return false;
			}
			
			// logging
			FatFingerUtils::logFile( "fatfinger-publishapp", $page );
			
			return true;
		}
		
	};


?>