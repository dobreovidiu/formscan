<?php

	// Uploading documents for processing.

	// include
	include "includes.php";
	include "cms/models/DocumentPending.php";		
	
	
	// globals
	$db	= false;

	
	// download error
	function downloadError()
	{
		header( "HTTP/1.0 403 Forbidden" );
		return;
	}
	

	// main function
	function main()
	{
		global $documentStoragePath;
		global $documentStoragePathDb;
		
		// logging
		Logger::setPageLogging();
		
		// verify parameters
		if( !isset( $_FILES["file"]["tmp_name"] ) 		|| empty( $_FILES["file"]["tmp_name"] ) ||
			!isset( $_FILES["file"]["name"] ) 			|| empty( $_FILES["file"]["name"] ) )
		{
			Logger::logError( "DocumentUploadService", "", "Missing upload parameters" );
			downloadError();			
			return false;
		}
		
		$tempFilepath		= $_FILES["file"]["tmp_name"];
		$originalFilename	= $_FILES["file"]["name"];
		
		// file failed to load
		if( !@file_exists( $tempFilepath ) )
		{
			Logger::logError( "DocumentUploadService", "", "File not existing " . $tempFilepath );		
			downloadError();
			return false;
		}
		
		// verify security
		SessionManager::verifySecurity( $userID );
		if( empty( $userID ) )
		{
			Logger::logError( "DocumentUploadService", "", "User not logged in" );		
			downloadError();
			return false;
		}
		
		$logUsername = SessionManager::getUsername();
		
		// storage path
		$storageFilename = date( "YmdHis", time() ) . "_" . rand() . "_" . $originalFilename;
		$storageFilepath = $documentStoragePath . "/" . $storageFilename;
		
		// remove old file
		if( @file_exists( $storageFilepath ) )
			@unlink( $storageFilepath );
		
		// copy file
		if( !@copy( $tempFilepath, $storageFilepath ) )
		{
			Logger::logError( "DocumentUploadService", $logUsername, "Failed to copy file " . $tempFilepath . " to storage " . $storageFilepath );				
			downloadError();
			return false;
		}
		
		$filepath = $documentStoragePathDb . "/" . $storageFilename;
		
		// add document pending
		$doc = new DocumentPending();
		
		$doc->userID	= $userID;
		$doc->filename	= $originalFilename;
		$doc->filepath	= $filepath;
		$doc->status	= 2;
		
		if( !$doc->save() )
		{
			@unlink( $storageFilepath );
			Logger::logError( "DocumentUploadService", $logUsername, "Failed to create pending document" );
			downloadError();
			return false;
		}
		
		return true;
	}


	// main function
	main();

?>