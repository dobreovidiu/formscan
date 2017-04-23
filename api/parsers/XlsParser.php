<?php

	// Xls Parser - parser for .xls files


	// XlsParser
	class XlsParser
	{
		// globals
		protected $document = false;
		
		
		// parse
		public function parse( $filename, $filepath )
		{
			global $viewParserOutput;

			// DETERMINE FORMAT
			$pos = strrpos( $filename, "." );
			if( is_bool( $pos ) )
			{
				// set response
				ApiLogging::logError( "[XlsParser::parse] Missing format for file: " . $filename );
				return false;
			}
			
			$ext = strtolower( substr( $filename, $pos + 1 ) );
			
			
			// CREATE READER
			if( $ext == "xls" )
				$objReader = PHPExcel_IOFactory::createReader( "Excel5" );				
			else
				$objReader = PHPExcel_IOFactory::createReader( "Excel2007" );					
			
			// load document
			$excel = $objReader->load( $filepath );
			if( !$excel )
			{
				ApiLogging::logError( "[XlsParser::parse] Failed to parse file " . $filename );
				return false;				
			}
						
			// logging
			if( isset( $viewParserOutput ) && $viewParserOutput )
				$this->outputParser( $excel );
			
			if( $ext == "xls" )
				DocumentUtils::logFile( "xls-parser", $excel );
			else
				DocumentUtils::logFile( "xlsx-parser", $excel );				
			
			
			// INITIALIZE DOCUMENT			
			$this->document = new Document();
			
			
			// GET TITLE
			$prop = $excel->getProperties();
			if( $prop )
			{
				$this->document->setTitle( $prop->getTitle() );
			}
			
			
			// GET CONTENT
			$sheets = $excel->getAllSheets();
			if( !$sheets )
				return false;			
				
			// traverse sheets
			foreach( $sheets as $sheet )
			{
				// process sheet
				$this->processSheet( $sheet );
				
				// first sheet only
				break;
			}
			
			// logging
			if( $ext == "xls" )
				DocumentUtils::logFile( "xls-result", $this->document->outputRows() );
			else
				DocumentUtils::logFile( "xlsx-result", $this->document->outputRows() );
			
			return $this->document;
		}	
		
		
		// process sheet
		protected function processSheet( &$sheet )
		{
			// get cells
			$cells = $sheet->getCellCollection();
			if( !$cells )
				return true;
			
			$sections 		= array();
			$curSection		= false;
			
			// traverse cells
			foreach( $cells as $cellName )
			{
				// get cell
				$cell = $sheet->getCell( $cellName );
				if( !$cell )
					continue;
				
				// verify value
				$val = $cell->getValue();
				if( empty( $val ) )
					continue;
					//$val = "";
					
				// get value
				if( is_object( $val ) )
				{
					if( method_exists( $val, "getPlainText" ) )
						$val = $val->getPlainText();
					else
						continue;
				}
				
				if( strlen( $val ) > 0 )
				{
					if( !DocumentUtils::isValidText( $val ) )
						continue;
				}
				
				$colID = trim( $cellName, "0123456789" );			
				$rowID = str_replace( $colID, "", $cellName );
				
				if( $cellName == "T6" )
				{
					echo $cellName . "\n";
				}
				
				// create section
				if( is_bool( $curSection ) )
					$curSection = array( "rows" => array() );
				
				// create row				
				if( !isset( $curSection["rows"][ $rowID ] ) )
					$curSection["rows"][ $rowID ] = array();					
				
				// add row cell
				array_push( $curSection["rows"][ $rowID ], array( $colID, $val ) );
			}
			
			// add last section
			if( !is_bool( $curSection ) )
				array_push( $sections, $curSection );
			
			// build document
			$this->document->buildFromArray( $sections );
			
			return true;
		}
		

		// output parser
		protected function outputParser( &$excel )
		{
			//var_dump( $excel );
			
			echo "\n\nPARSER OUTPUT\n\n";
			
			// properties
			$prop = $excel->getProperties();
			if( $prop )
			{
				echo "- Properties\n";
				echo "Creator: " . $prop->getCreator() . "\n";
				echo "Title: " . $prop->getTitle() . "\n";				
				echo "Description: " . $prop->getDescription() . "\n";	
				echo "Subject: " . $prop->getSubject() . "\n";			
				echo "Keywords: " . $prop->getKeywords() . "\n";		
				echo "Category: " . $prop->getCategory() . "\n";		
				echo "Company: " . $prop->getCompany() . "\n";	
				echo "Manager: " . $prop->getManager() . "\n";					
				echo "\n";
			}
			else
			{
				echo "- No properties found\n\n";
			}
			
			
			// sheets
			$sheets = $excel->getAllSheets();
			if( $sheets )
			{
				$idx = 1;
				
				// traverse sheets
				foreach( $sheets as $sheet )
				{
					echo "- Sheet " . ($idx++) . "\n";
					echo "Title: " . $sheet->getTitle() . "\n";
					
					$cells = $sheet->getCellCollection();
					if( !$cells )
						continue;
					
					// traverse cells
					foreach( $cells as $cellID )
					{
						$cell = $sheet->getCell( $cellID, false );
						if( is_bool( $cell ) )
							continue;
						
						$value = $cell->getValue();
						if( null === $value )
							continue;
						
						echo $cellID . " " . $cell->getValue() . " " . $cell->getDataType() . "\n";
					}
					
					echo "\n";					
				}				
			}
			else
			{
				echo "- No sheets found\n\n";				
			}
		}
		
	};


?>