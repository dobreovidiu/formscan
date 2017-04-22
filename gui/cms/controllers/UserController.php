<?php

	// User Controller - management of user table.
	
	
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
	
	
	
	
	// UserController
	class UserController
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
		
			// lockscreen
			if( $action == "lockscreen" )
				self::processLockScreen( $json );				
			else
			// unlockscreen
			if( $action == "unlockscreen" )
				self::processUnlockScreen( $json );		
		}
		
		
		// lock screen
		static protected function processLockScreen( $json )
		{
			// start session
			if( !SessionManager::start() )
				return;
			
			// set lock screen
			$currentPageUrl = SessionManager::getCurrentPage();
			if( $currentPageUrl == "" )
				$currentPageUrl = "index.php";
			
			SessionManager::setLockScreen( 1, $currentPageUrl );
		
			// result
			$ret = array( 	"success" 	=> "true",
							"exception"	=> ""
						 );
			
			echo json_encode( $ret );	
		}
		
		
		// unlock screen
		static protected function processUnlockScreen( $json )
		{
			if( !isset( $json->{'password'} ) )
				return;
			
			$password = $json->{'password'};
			
			// validate names
			if( $password == "" )
			{	
				$ret = array( 	"success" 	=> "false",
								"exception"	=> "Invalid password."
							 );
							
				echo json_encode( $ret );
				return;			
			}
			
			// start session
			if( !SessionManager::start() )
				return;
				
			// user id
			$userID = SessionManager::getUserId();	
			if( empty( $userID ) )
				return;
			
			$logUsername = SessionManager::getUsername();	
			
			// verify password
			$user = new User();
			if( !$user->loadById( $userID ) )
			{
				// logging
				Logger::logError( "UserController::processUnlockScreen", $logUsername, "Failed load user" );
				
				$ret = array( 	"success" 	=> "false",
								"exception"	=> "Error while accessing the user data. Please contact support."
							 );
							
				echo json_encode( $ret );
				return;
			}
				
			if( !$user->isPasswordValid( $password ) )
			{
				// logging
				Logger::logApp( "UserController::processUnlockScreen", $logUsername, "Invalid password for user ID: " . $user->id . " username: " . $user->username . " password: " . $password . " md5_1: " . md5( $password ) . " md5_2: " . $user->password );
				
				// result
				$ret = array( 	"success" 	=> "false",
								"exception"	=> "Wrong password."
							 );
				
				echo json_encode( $ret );
				return;
			}
			
			// get lock screen
			$returnPage = SessionManager::getLockPage();
			if( $returnPage == "" )
				$returnPage = "index.php";
				
			// clear session
			SessionManager::setLockScreen( 0, "" );			
			
			// result
			$ret = array( 	"success" 		=> "true",
							"exception"		=> "",
							"returnPage"	=> $returnPage
						 );
			
			echo json_encode( $ret );	
		}
		
	};
	
	
	// main function
	UserController::process();
	
?>