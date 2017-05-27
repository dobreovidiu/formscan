<?php

	// include
	include "includes.php";	
	
	
	// globals
	$page 				= false;
	$db					= false;
	$isLoggedIn			= false;
	$userLogged			= false;
	$currentPageUrl		= "lockscreen.php";

	
	
	// main function
	function main()
	{
		global $page;
		global $db;
		global $isLoggedIn;
		
		// logging
		Logger::setPageLogging();		
		
		// verify security
		$isLoggedIn = SessionManager::verifySecurityLock( $isLocked, 0, 1 );
		if( !$isLoggedIn )
			return;

		// load page
		$page = @file_get_contents( "pages/lockscreen.html" );

		// fill page global
		fillPageGlobal();
		
		// output page
		echo $page;
	}
	
	
	// main function
	main();
	
?>