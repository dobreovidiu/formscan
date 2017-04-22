<?php

	// File Includes - API Protocol
	
	
	// avoid caching
	header("Expires: Sat, 1 Jan 2005 00:00:00 GMT");
	header("Last-Modified: " . gmdate( "D, d M Y H:i:s") . "GMT");
	header("Cache-Control: no-cache, must-revalidate");
	header("Pragma: no-cache");	
	
	
	// api
	require_once "src/ApiEngine.php";
	require_once "src/ApiModuleDocumentConversion.php";
	require_once "src/ApiProtocol.php";
	

	// core
	require_once "src/includecore.php";	
	
	
?>