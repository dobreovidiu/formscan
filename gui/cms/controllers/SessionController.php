<?php
	
	// Session Controller - management of user sessions.
	
	
	// includes
	include "../../conf/appconf.php";			
	include "../managers/Db.php";
	include "../managers/SessionManager.php";
	include "../managers/Logger.php";
	include "../models/SystemLog.php";
	include "../models/User.php";
	include "../models/UserSession.php";
	include "../models/Settings.php";			
	include "../gui/utils.php";		
	
	
	
	// SessionController
	class SessionController
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
			
			// login
			if( $action == "login" )
				self::processLogin( $json );			
			else			
			// logout
			if( $action == "logout" )
				self::processLogout( $json );						
		}	

		
		// login
		static protected function processLogin( $json )
		{
			if( !isset( $json->{'username'} ) || !isset( $json->{'password'} ) || !isset( $json->{'remember'} ) )
				return;
			
			// decode
			$username = urldecode( $json->{'username'} );
			$password = urldecode( $json->{'password'} );
			$remember = urldecode( $json->{'remember'} );
			
			// validate names
			if( $username == "" || !SessionManager::isValidName( $username ) )
			{	
				$ret = array( 	"success" 	=> "false",
								"exception"	=> "Invalid username."
							 );
							
				echo json_encode( $ret );
				return;
			}			
			
			// validate password
			if( $password == "" )
			{	
				$ret = array( 	"success" 	=> "false",
								"exception"	=> "Invalid password."
							 );
							
				echo json_encode( $ret );
				return;
			}
			
			// validate remember			
			if( $remember == "" || !is_numeric( $remember ) || ( $remember != "1" && $remember != "0" ) )
			{	
				$ret = array( 	"success" 	=> "false",
								"exception"	=> "Invalid remember me value."
							 );
							
				echo json_encode( $ret );
				return;			
			}			

			$remember = intval( $remember );
			
			// get IP address
			$ipAddress = SessionManager::getCallerIpAddress();
			
			// load user
			$user = new User();
			if( !$user->loadByCredentials( $username, $password ) )
			{	
				$ret = array( 	"success" 	=> "false",
								"exception"	=> "Wrong username or password."
							 );
							
				echo json_encode( $ret );
				return;			
			}
			
			// account disabled
			if( intval( $user->status ) != 1 )
			{
				$ret = array( 	"success" 	=> "false",
								"exception"	=> "Your account has been disabled."
							 );
							
				echo json_encode( $ret );
				return;
			}
			
			$userID 		= $user->id;
			$logUsername 	= $user->username;

			// start session
			if( !SessionManager::start() )
			{
				// logging
				Logger::logError( "SessionController::processLogin", $logUsername, "Failed to open session" );
				
				$ret = array( 	"success" 	=> "false",
								"exception"	=> "Failed to open session. Please contact support."
							 );
							
				echo json_encode( $ret );
				return;			
			}
			
			// set user ID
			SessionManager::setUserId( $userID );
			SessionManager::setUsername( $logUsername );				
			
			// remember session
			if( $remember )
			{
				$tokenExists = false;
			
				// verify if cookie exists
				if( isset( $_COOKIE["rememberme"] ) )
				{
					// get token
					$token = SessionManager::getRememberToken( $_COOKIE["rememberme"], $userID, $cookieUserID );
					
					if( !is_bool( $token ) )
					{
						// verify if valid token
						$session = new UserSession();
						if( $session->loadByUser( $userID, $token ) )
						{
							$tokenExists = true;
							$session->updateLastLogin();
						}
					}
				}
				
				// token not existing
				if( !$tokenExists )
				{
					// set cookie
					SessionManager::remember( $userID, $token );
					
					// set token
					$session = new UserSession();
					$session->userID 		= $userID;
					$session->token			= $token;
					$session->ipAddress		= $ipAddress;
					$session->status		= 1;
					
					// save token
					if( !$session->save() )
					{
						// logging
						Logger::logError( "SessionController::processLogin", $logUsername, "Failed to save session token" );						
					}
				}
			}
			else
			// don't remember session
			{
				// clear session token
				if( !UserSession::clearUser( $userID ) )
				{
					// logging
					Logger::logError( "SessionController::processLogin", $logUsername, "Failed to clear user session tokens" );	
				}
				
				// unremember
				SessionManager::unremember();				
			}
						
			// result
			$ret = array( 	"success" 	=> "true",
							"exception"	=> "",
							"redirect"	=> "index.php"
						 );
			
			echo json_encode( $ret );
		}
		
		
		// logout
		static protected function processLogout( $json )
		{
			// start session
			if( !SessionManager::start() )
			{
				$ret = array( 	"success" 	=> "false",
								"exception"	=> "Failed to open session. Please contact support."
							 );
							
				echo json_encode( $ret );
				return;			
			}
			
			// user id
			$userID = SessionManager::getUserId();
			
			// clear session token
			if( !empty( $userID ) )
			{
				UserSession::clearUser( $userID );
			}
				
			// unremember
			SessionManager::unremember();
			
			// stop session
			SessionManager::stop();
						
			// result
			$ret = array( 	"success" 	=> "true",
							"exception"	=> "",
							"redirect"	=> "index.php"
						 );
			
			echo json_encode( $ret );
		}
		
	};
	
	
	// main function
	SessionController::process();
	
?>