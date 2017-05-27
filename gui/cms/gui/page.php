<?php

	// General purpose page functions.


	// fill page global
	function fillPageGlobal()
	{		
		// fill menu menu
		fillMainMenu();
		
		// fill globals
		fillPageGlobals();		
	}
	
	
	// fill globals
	function fillPageGlobals()
	{
		global $page;	
		global $isLoggedIn;		
		global $currentPageUrl;
		global $jsScriptVersion;
		global $userLogged;
		global $websiteTitle;
		global $companyTitle;
		global $companyUrl;
		global $frontendUrl;
		
		// set globals
		SessionManager::setCurrentPage( $currentPageUrl );
		
		$username 		= "";
		$isLoggedIn 	= 0;		
		$userEmail		= "";
			
		// username
		if( isset( $userLogged ) && !is_bool( $userLogged ) )
		{
			$isLoggedIn 	= 1;
			$username 		= $userLogged->username;
			$userEmail		= $userLogged->email;
		}
		
		
		// fill tags
		$page = str_replace( "<!--USER ISLOGGEDIN-->", 			$isLoggedIn, 				$page );
		$page = str_replace( "<!--USER EMAIL-->", 				$userEmail, 				$page );				
		$page = str_replace( "<!--WEBSITE TITLE-->", 			$websiteTitle, 				$page );		
		$page = str_replace( "<!--COMPANY TITLE-->", 			$companyTitle, 				$page );		
		$page = str_replace( "<!--COMPANY URL-->", 				$companyUrl, 				$page );
		$page = str_replace( "<!--FRONTEND URL-->", 			$frontendUrl, 				$page );		
		$page = str_replace( "<!--JSVER-->", 					$jsScriptVersion, 			$page );
		$page = str_replace( "<!--CURUSERNAME-->", 				$username, 					$page );	
		$page = str_replace( "<!--PAGE RETURN URL-->", 			$currentPageUrl, 			$page );						
		$page = str_replace( "<!--CURDATE-->", 					date( "d/m/Y", time() ), 	$page );			
		$page = str_replace( "<!--CURYEAR-->", 					date( "Y", time() ), 		$page );		
	}
	
	
	// fill menu menu
	function fillMainMenu()
	{
		global $page;
		global $currentPageUrl;
		global $isLoggedIn;		
		global $userLogged;
		
		if( !$isLoggedIn )
			return;
			
		// admin
		$menu = @file_get_contents( "pages/adminmainmenu.html" );
		
		// active menu
		$menuItems = array(	array( "<!--DASHBOARDMENU ACTIVE-->", 		array( "index.php" ) )
						  );

						  
		foreach( $menuItems as $item )
		{
			$val = "";
			foreach( $item[1] as $url )
			{
				if( strtolower( $currentPageUrl ) == strtolower( $url ) )
				{
					$val = "active";
					break;
				}
				
				$pos = stripos( $currentPageUrl, $url );
				if( !is_bool( $pos ) && $pos == 0 )
				{
					$val = "active";
					break;
				}				
			}
			
			$menu = str_replace( $item[0], $val, $menu );
		}
		
		// fields
		$page = str_replace( "<!--MAIN MENU-->", 	$menu, 	$page );			
	}
	
		
?>