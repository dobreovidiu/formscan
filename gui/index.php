<?php

	// include
	include "includes.php";	
	
	
	// globals
	$page 					= false;
	$db						= false;
	$isLoggedIn				= false;
	$userLogged				= false;
	$currentPageUrl			= "index.php";

	
	
	
	// main function
	function main()
	{
		global $page;
		global $db;
		global $isLoggedIn;
		global $userLogged;		
		
		// logging
		Logger::setPageLogging();
		
		// verify security
		$isLoggedIn = SessionManager::verifySecurityLock( $isLocked, 1, 0 );
		if( !$isLoggedIn )
		{
			// db
			$db = new Db();
		
			// load page
			$page = @file_get_contents( "pages/index.html" );
			
			// fill page global
			fillPageGlobal();		
		}
		else
		{
			// db
			$db = new Db();
			
			// admin
			$page = @file_get_contents( "pages/admindashboard.html" );			
							
			// fill page global
			fillPageGlobal();		
		}
			
		// output page
		echo $page;
	}
	
	
	// main function
	main();
	
?>