<?php
	
	// Fat Finger Utils - utility functions for Fat Finger app creation.
	
	
	// FatFingerUtils
	class FatFingerUtils
	{		
		
		// map field type
		static public function mapSectionField( $type, $allowedValues, $defaultValue, &$typeLogic, &$extraFields )
		{
			$typeLogic 		= "";
			$extraFields	= array();
			
			// SingleLineText
			if( $type == DocumentSectionField::INPUTTEXT )
			{
				return "SingleLineText";	
			}
			
			// MultiLineText
			if( $type == DocumentSectionField::TEXTBOX )
			{
				return "MultiLineText";	
			}
			
			// Numeric
			if( $type == DocumentSectionField::NUMERIC )
			{
				$extraFields["aiLogic"] 				= "|false";
				$extraFields["typeLogicHigh"] 			= "";
				$extraFields["typeLogicLow"] 			= "";
				$extraFields["typeLogicTarget"] 		= "";
				$extraFields["triggerMessageHigh"] 		= "";
				$extraFields["triggerMessageLow"] 		= "";
				$extraFields["triggerMessageTarget"] 	= "";
				$extraFields["isVariableInUse"] 		= false;
				$extraFields["pct_avg_tolerance"]		= "";
				$extraFields["alert_new_min_max"] 		= false;
				$extraFields["variableName"] 			= "@NUMERIC" . rand(1, 100000);
				
				return "Numeric";	
			}
			
			// SingleSelect
			if( $type == DocumentSectionField::SINGLESELECT )
			{
				$typeLogic = $allowedValues;
				
				$extraFields["fieldTriggers"] = array();
				
				if( !is_bool( $defaultValue ) )
					$extraFields["defaultValue"] = $defaultValue;
				
				return "SingleSelect";
			}
			
			// MultiSelect
			if( $type == DocumentSectionField::MULTISELECT )
			{
				$typeLogic = $allowedValues;			
				
				if( !is_bool( $defaultValue ) )
					$extraFields["defaultValue"] = $defaultValue;
				
				return "MultiSelect";
			}
			
			// DateTime
			if( $type == DocumentSectionField::DATETIME )
			{
				return "DateTime";
			}
			
			// DateTime
			if( $type == DocumentSectionField::DATEONLY )
			{
				$typeLogic = "1";
				
				return "DateTime";
			}
			
			// DateTime
			if( $type == DocumentSectionField::TIMEONLY )
			{
				$typeLogic = "2";
				
				return "DateTime";
			}
			
			// Label
			if( $type == DocumentSectionField::LABEL )
			{
				return "Label";
			}
			
			// Boolean
			if( $type == DocumentSectionField::BOOLEAN )
			{
				$typeLogic = "Yes|No";
				
				$info = explode( "|", $allowedValues );
				if( count( $info ) >= 2 )
				{
					$extraFields["typeLogicTrue"]	= trim( $info[0] );
					$extraFields["typeLogicFalse"]	= trim( $info[1] );
					
					$typeLogic = $extraFields["typeLogicTrue"] . "|" . $extraFields["typeLogicFalse"];
				}
				
				$extraFields["fieldTriggers"] = array();				
				
				return "Boolean";	
			}
			
			// Trilean
			if( $type == DocumentSectionField::TRILEAN )
			{
				$typeLogic = "Yes|No|N/A";
				
				$info = explode( "|", $allowedValues );
				if( count( $info ) >= 3 )
				{
					$extraFields["typeLogicTrue"]			= trim( $info[0] );
					$extraFields["typeLogicFalse"]			= trim( $info[1] );
					$extraFields["typeLogicIndeterminate"]	= trim( $info[2] );		

					$typeLogic = $extraFields["typeLogicTrue"] . "|" . $extraFields["typeLogicFalse"] . "|" . $extraFields["typeLogicIndeterminate"];
				}
				
				$extraFields["fieldTriggers"] = array();
				
				return "Trilean";
			}
			
			// Location
			if( $type == DocumentSectionField::LOCATION )
			{
				return "Location";		
			}
			
			// Photo
			if( $type == DocumentSectionField::PHOTO )
			{
				return "Photo";		
			}
			
			// Signature
			if( $type == DocumentSectionField::SIGNATURE )
			{
				return "Signature";			
			}
			
			// ImageViewer
			if( $type == DocumentSectionField::IMAGEVIEWER )
			{
				return "ImageViewer";				
			}
			
			// WebViewer
			if( $type == DocumentSectionField::WEBVIEWER )
			{
				$typeLogic = $allowedValues;	
				
				return "WebViewer";
			}
			
			// TriggerSection
			if( $type == DocumentSectionField::TRIGGERSECTION )
			{
				return "TriggerSection";			
			}
			
			// Barcode
			if( $type == DocumentSectionField::BARCODE )
			{
				return "Barcode";			
			}			
			
			return false;			
		}

		
		// generate unique id
		static public function generateUniqueId()
		{
			$chars = array( "0", "1", "2", "3", "4", "5", "6", "7", "8", "9",
							"a", "b", "c", "d", "e", "f" );
							
			$id = "";				
							
			for( $i = 1; $i <= 32; $i++ )
			{
				if( $i == 1 )
					$char = $chars[ rand( 1, 15 ) ];
				else
					$char = $chars[ rand( 0, 15 ) ];
				
				$id .= $char;
				
				if( $i == 8 || $i == 12 || $i == 16 || $i == 20 )
					$id .= "-";
			}
			
			return $id;
		}

		
		// log file
		static public function logFile( $path, $data )
		{
			@file_put_contents( "log/fatfinger/" . $path . "-" . date( "YmdHis", time() ) . "_" . rand() . ".log", print_r( $data, 1 ) );				
		}
		
	};
	
	
?>