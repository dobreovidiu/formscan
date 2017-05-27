<?php

	// SessionManager - management of current user session.
 
 
	// SessionManager
	class SessionManager
	{
	
		// constructor
		public function __construct()
		{
		}
		
		
		// start
		static public function start()
		{
			if( !@session_start() )
				return false;
				
			return true;
		}
		
		
		// stop
		static public function stop()
		{
			if( isset( $_COOKIE[ @session_name() ] ) ) 
			{
				@setcookie( @session_name(), '', time() - 42000, '/' );
			}

			@session_destroy();
			
			return true;
		}
		
		
		// get id
		static public function getId()
		{
			return @session_id();
		}
		
		
		// verify security
		static public function verifySecurity( &$userID )
		{
			global $userLogged;
			global $currentPageUrl;
			
			$userID = "";
				
			// start session
			if( !self::start() )
				return false;
				
			// get user ID
			$userID = self::getUserId();
			
			return true;		
		}

		
		// verify security with lock
		static public function verifySecurityLock( &$isLocked, $checkLock = 1, $doRedirect = 1 )
		{
			global $userLogged;
			global $currentPageUrl;
			
			$isLocked = 0;
				
			// start session
			if( !self::start() )
			{
				if( $doRedirect )
					header( "Location: index.php" );
					
				return false;
			}
				
			// get user ID
			$userID = self::getUserId();
			if( empty( $userID ) )
			{	
				// verify if remember cookie exists
				if( isset( $_COOKIE["rememberme"] ) )
				{
					// get token
					$token = self::getRememberToken( $_COOKIE["rememberme"], false, $userID );					
					if( !is_bool( $token ) )
					{
						// verify if valid token
						$session = new UserSession();
						if( $session->loadByUser( $userID, $token ) )
						{
							$session->updateLastLogin();
							self::setUserId( $userID );
							
							$user = new User();
							if( $user->loadById( $userID ) )
								self::setUsername( $user->username );
						}
						else
						{
							$userID = "";
						}
					}
					else
					{
						$userID = "";
					}
				}
			}
			
			if( empty( $userID ) )
			{
				if( $doRedirect )
					header( "Location: index.php" );
					
				return false;			
			}
					
			// load current user
			$userLogged	= new User();
			if( !$userLogged->loadById( $userID ) )
			{
				//logging
				Logger::logError( "SessionManager::verifySecurityLock", $userID, "Failed to load user" );
				
				if( $doRedirect )
					header( "Location: index.php" );
					
				return false;				
			}
				
			// verify if user disabled => logout
			if( intval( $userLogged->type ) == 1 && intval( $userLogged->status ) != 1 )
			{
				UserSession::clearUser( $userLogged->id );
				SessionManager::unremember();
				SessionManager::stop();			
				header( "Location: index.php" );
				return false;				
			}
			
			// verify if locked
			if( $checkLock )
			{
				if( self::isScreenLocked() )
				{
					$isLocked = 1;
					header( "Location: lockscreen.php" );
					return false;
				}
			}
			
			// clear state
			self::clearState( $currentPageUrl );
			
			return true;
		}
		
		
		// remember user
		static public function remember( $userID, &$token )
		{
			// get token
			srand( time() );
			$token = rand();
			
			// set cookie
			$cookie 	 = $userID . ":" . $token;
			$mac 		 = md5( $cookie );
			$cookie 	.= ":" . $mac;
			
			@setcookie( "rememberme", $cookie, time() + 10 * 365 * 24 * 3600, "/" );
		}
		
		
		// unremember user
		static public function unremember()
		{
			@setcookie( "rememberme", "", 0, "/" );
		}
		
		
		// get remember token
		static public function getRememberToken( $cookie, $userID, &$cookieUserID )
		{
			$info = explode( ":", $cookie );
			if( count( $info ) != 3 )
				return false;
				
			$cookieUserID 	= $info[0];
			$token 			= $info[1];
			$mac			= $info[2];
			
			if( !is_bool( $userID ) && $cookieUserID != $userID )
				return false;
			
			if( $mac != md5( $cookieUserID . ":" . $token ) )
				return false;
			
			return $token;
		}
		
		
		// get user id
		static public function getUserId()
		{
			return self::getSessionVar( "sessionadminuserID" );	
		}
		
		
		// set user id
		static public function setUserId( $userID )
		{
			self::setSessionVar( "sessionadminuserID",	$userID );
		}
		
		
		// get username
		static public function getUsername()
		{
			return self::getSessionVar( "sessionusernameadmin" );	
		}
		
		
		// set username
		static public function setUsername( $username )
		{
			self::setSessionVar( "sessionusernameadmin",	$username );
		}
		
		
		// get trans id
		static public function getTransId()
		{
			$transID = self::getSessionVar( "sessiontransIDadmin" );
			if( empty( $transID ) )
				return 1;
				
			return intval( $transID );
		}
		
		
		// set trans id
		static public function setTransId( $transID )
		{
			self::setSessionVar( "sessiontransIDadmin",	$transID );
		}
		
		
		// inc trans id
		static public function incTransId()
		{
			$transID = self::getTransId();
			self::setTransId( $transID + 1 );
		}
		
		
		// clear state
		static public function clearState( $curPage )
		{		
		}
		

		// set current page
		static public function setCurrentPage( $page )
		{
			self::setSessionVar( "pagecurrentadmin",	$page );					
		}
		
		
		// get current screen
		static public function getCurrentPage()
		{
			return self::getSessionVar( "pagecurrentadmin" );		
		}		

		
		// set lock screen
		static public function setLockScreen( $status, $returnPage )
		{
			self::setSessionVar( "lockstatusadmin",		$status );		
			self::setSessionVar( "lockpageadmin",		$returnPage );					
		}
		
		
		// get lock status
		static public function isScreenLocked()
		{
			$val = self::getSessionVar( "lockstatusadmin" );		
			if( $val == "" )
				return 0;
				
			return intval( $val );
		}	

		
		// get lock screen
		static public function getLockPage()
		{
			return self::getSessionVar( "lockpageadmin" );		
		}
		
		
		// set session var
		static protected function setSessionVar( $name, $val )
		{
			$_SESSION[ $name ] = $val;
		}
		
		
		// get session var
		static protected function getSessionVar( $name )
		{
			if( !isset( $_SESSION[ $name ] ) )
				return "";
		
			return $_SESSION[ $name ];
		}
		
		
		// is valid name
		static public function isValidName( $name )
		{
			if( !is_bool( stripos( $name, "<" ) ) )
				return false;
				
			if( !is_bool( stripos( $name, ">" ) ) )
				return false;

			if( !is_bool( stripos( $name, "&lt;" ) ) )
				return false;

			if( !is_bool( stripos( $name, "&gt;" ) ) )
				return false;	

			return true;
		}
		
		
		// is valid password
		static public function isValidPassword( $username, $password )
		{
			// length
			if( strlen( $password ) < 8 )
				return false;
				
			// username
			if( !is_bool( stripos( $password, $username ) ) )
				return false;
				
			$isUpper 	= false;
			$isLower 	= false;
			$isDigit 	= false;
			$isSymbol	= false;
			
			// composition
			$no = strlen( $password );
			for( $i = 0; $i < $no; $i++ )
			{
				$code = ord( $password[$i] );
				if( $code >= ord('A') && $code <= ord('Z') )
					$isUpper = true;
				else
				if( $code >= ord('a') && $code <= ord('z') )
					$isLower = true;	
				else
				if( $code >= ord('0') && $code <= ord('9') )
					$isDigit = true;						
				else
					$isSymbol = true;
			}
			
			if( !$isUpper || !$isLower || !$isDigit || !$isSymbol )
				return false;

			return true;
		}
		
		
		// get caller IP address
		static public function getCallerIpAddress()
		{
			// get ip address
			$ipAddress = "";
			if( isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != "" )
				$ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
			else
			if( isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] != "" )
				$ipAddress = $_SERVER['REMOTE_ADDR'];

			return $ipAddress;			
		}
		
	}
	
?>