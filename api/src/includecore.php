<?php

	// File Includes - Core Service

	
	// configuration
	require_once "conf/appconf.php";	

	
	// src
	require_once "src/ApiConfFile.php";	
	require_once "src/ApiDb.php";
	require_once "src/ApiLogging.php";
	require_once "src/ApiModuleDocumentCore.php";			
	require_once "src/ApiUtils.php";
	
	
	// document
	require_once "document/DocumentParser.php";
	require_once "document/Document.php";
	require_once "document/DocumentSection.php";
	require_once "document/DocumentSectionField.php";
	require_once "document/DocumentSectionRow.php";	
	require_once "document/DocumentUtils.php";
	
	
	// parsers
	require_once "parsers/DocParser.php";
	require_once "parsers/DocxParser.php";
	require_once "parsers/ImageParser.php";
	require_once "parsers/PdfParser.php";
	require_once "parsers/XlsParser.php";
	require_once "parsers/XlsxParser.php";
	require_once "parsers/TestParser.php";
	
	
	// fatfinger	
	require_once "fatfinger/FatFingerManager.php";	
	require_once "fatfinger/FatFingerApi.php";
	require_once "fatfinger/FatFingerRequest.php";
	require_once "fatfinger/FatFingerForm.php";
	require_once "fatfinger/FatFingerSection.php";
	require_once "fatfinger/FatFingerSectionField.php";
	require_once "fatfinger/FatFingerResponse.php";
	require_once "fatfinger/FatFingerUtils.php";
	
	
	// lib
	
	// Zend
	require_once 'lib/Zend/library/Zend/Validator/Translator/TranslatorAwareInterface.php';		
	require_once 'lib/Zend/library/Zend/Validator/ValidatorInterface.php';			
	require_once 'lib/Zend/library/Zend/Validator/AbstractValidator.php';	
	require_once 'lib/Zend/library/Zend/Validator/InArray.php';
	
	// PHP Office Common library
	require_once 'lib/Common/src/Common/Autoloader.php';
	\PhpOffice\Common\Autoloader::register();
	
	// PHP Word library
	require_once 'lib/PhpWord/src/PhpWord/Autoloader.php';
	\PhpOffice\PhpWord\Autoloader::register();
	
	// PHP Excel library
	require_once "lib/PhpExcel/Classes/PHPExcel.php";
	
	
	// googleapi
	require_once "googleapi/GoogleVision.php";
	
	
	// date time zone
	date_default_timezone_set( $dateTimeZone );			
	
?>