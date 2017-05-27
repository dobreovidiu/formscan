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
			
			//$this->outputParser( $excel );
						
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
			$title = false;
			
			$prop = $excel->getProperties();
			if( $prop )
				$title = $prop->getTitle();
			
			
			// GET CONTENT
			$sheets = $excel->getAllSheets();
			if( !$sheets )
				return false;			
				
			// traverse sheets
			foreach( $sheets as $sheet )
			{
				// process sheet
				$this->processSheet( $sheet );
				
				// get title
				$sheetTitle = $sheet->getTitle();
				if( !empty( $sheetTitle ) )
				{
					if( is_bool( $title ) || 
						!is_bool( stripos( $title, "Untitled" ) ) )
					{
						$title = $sheetTitle;
					}
				}
				
				// first sheet only
				break;
			}
			
			
			// set title
			if( !is_bool( $title ) && !empty( $title ) )
				$this->document->setTitle( $title );
			
			
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
			
			// get merge cells
			$mergeCells = $sheet->getMergeCells();
			if( !$mergeCells )
				$mergeCells = array();
			
			// parse merge cells
			$ranges = array();			
			foreach( $mergeCells as $item )
			{
				$range = self::parseCellRange( $item );
				if( !is_bool( $range ) )
					$ranges[ $range["start"] ] = $range;
			}
			
			$sections 		= array();
			$curSection		= false;
			$curRowID		= false;
			$curColRange	= false;
			
			// traverse cells
			foreach( $cells as $cellName )
			{
				// get cell
				$cell = $sheet->getCell( $cellName );
				if( !$cell )
					continue;
				
				$colID = trim( $cellName, "0123456789" );
				$rowID = str_replace( $colID, "", $cellName );
				
				// change row
				if( is_bool( $curRowID ) || $curRowID != $rowID )
				{
					$curColRange 	= false;
					$curRowID 		= $rowID;
				}
				
				// verify if cell in range
				if( !is_bool( $curColRange ) )
				{
					if( self::isCellInRange( $curColRange, $colID, $rowID ) )
					{
						//echo "in range " . $curColRange["range"] . " " . $cellName . "\n";
						continue;
					}
				}
				
				// set current range
				$curColRange = false;
				if( isset( $ranges[ $cellName ] ) )
					$curColRange = $ranges[ $cellName ];
				
				// verify value
				$val = $cell->getValue();
				if( empty( $val ) )
				{
					// verify if input field
					$val = self::verifyInputField( $sheet, $cellName );
					
					// no value determined
					if( is_bool( $val ) || empty( $val ) )
						continue;
				}
				
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
				
				// verify if formula text
				if( self::isFormulaText( $val ) )
				{
					//echo "formula " . $val . "\n";
					$val = "";			
				}
				
				// create section
				if( is_bool( $curSection ) )
					$curSection = array( "rows" => array() );
				
				// create row				
				if( !isset( $curSection["rows"][ $rowID ] ) )
					$curSection["rows"][ $rowID ] = array();	

				//echo $cellName . " " . $val . " " . $cell->getMergeRange() . "\n";
				
				// normalize text
				DocumentUtils::normalizeText( $val );		
			
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
		
		
		// parse cell range
		protected function parseCellRange( $curColRange )
		{
			$info = explode( ":", $curColRange );
			if( count( $info ) != 2 )
				return false;
			
			$start 	= trim( $info[0] );
			$end	= trim( $info[1] );
			
			// start
			$startColID = trim( $start, "0123456789" );
			$startRowID = str_replace( $startColID, "", $start );	
			
			// end
			$endColID = trim( $end, "0123456789" );
			$endRowID = str_replace( $endColID, "", $end );			

			// range
			$range = array( "range"		=> $curColRange,
							"start"		=> $start,
							"end"		=> $end,
							"startY" 	=> $startColID, 
							"startX" 	=> intval( $startRowID ),
							"endY"		=> $endColID,
							"endX"		=> intval( $endRowID )
						  );
						  
			return $range;
		}
		
		
		// verify if cell in range
		protected function isCellInRange( $range, $colID, $rowID )
		{
			$rowID = intval( $rowID );
			
			// inside rows
			if( $rowID < $range["startX"] || $rowID > $range["endX"] )
				return false;
			
			// inside columns
			if( strlen( $colID ) < strlen( $range["startY"] ) ||
				strlen( $colID ) > strlen( $range["endY"] ) )
				return false;
				
			if( strlen( $colID ) == strlen( $range["startY"] ) )
			{
				$lastY1 = substr( $colID, -1 );
				$lastY2 = substr( $range["startY"], -1 );			

				if( ord( $lastY1 ) < ord( $lastY2 ) )
					return false;
			}
			
			if( strlen( $colID ) == strlen( $range["endY"] ) )
			{
				$lastY1 = substr( $colID, -1 );
				$lastY2 = substr( $range["endY"], -1 );
				
				if( ord( $lastY1 ) > ord( $lastY2 ) )
					return false;
			}

			return true;
		}
		
		
		// verify if input field
		protected function verifyInputField( &$sheet, $cellName )
		{
			// fill
			$val = self::verifyInputFieldFill( $sheet, $cellName );
			if( !is_bool( $val ) )
				return $val;
			
			// borders
			$val = self::verifyInputFieldBorders( $sheet, $cellName );
			if( !is_bool( $val ) )
				return $val;
			
			return false;
		}
		
		
		// verify if input field fill
		protected function verifyInputFieldFill( &$sheet, $cellName )
		{
			// get cell style
			$style = $sheet->getStyle( $cellName );
			if( !$style )
				return false;
			
			// get cell fill
			$fill = $style->getFill();
			if( !$fill )
				return false;
				
			// get fill type
			if( $fill->getFillType() == "none" )
				return false;
			
			// get color
			$startColor = $fill->getStartColor();
			if( !$startColor )
				return false;
			
			$color = $startColor->getRGB();
			if( strlen( $color ) < 6 )
				return false;
			
			$color = strtolower( $color );
			
			$red 	= substr( $color, -6, 2 );
			$green 	= substr( $color, -4, 2 );			
			$blue 	= substr( $color, -2, 2 );				

			//echo $fill->getFillType() . " " . $color . "\n";
			//echo $red . " " . $green . " " . $blue . "\n";
			
			// allowed values
			if( $red == $green && $green == $blue )
				return false;
			
			//echo "found\n";
			
			$val = "{ FORMTEXT }";
			
			return $val;
		}
		
		
		// verify if input field borders
		protected function verifyInputFieldBorders( &$sheet, $cellName )
		{
			// get cell style
			$style = $sheet->getStyle( $cellName );
			if( !$style )
				return false;
			
			// get cell borders
			$borders = $style->getBorders();
			if( !$borders )
				return false;
			
			$left 	= $borders->getLeft();
			$right 	= $borders->getRight();
			$top 	= $borders->getTop();
			$bottom = $borders->getBottom();	
			
			if( !$left || !$right || !$top || !$bottom )
				return false;
			
			// get border type
			if( $left->getBorderStyle() == "none" ||
				$right->getBorderStyle() == "none" ||
				$top->getBorderStyle() == "none" ||
				$bottom->getBorderStyle() == "none" )
				return false;
			
			$val = "{ FORMTEXT }";
			
			return $val;
		}
		
		
		// whether formula text
		protected function isFormulaText( $val )
		{
			if( substr( $val, 0, 1 ) == "=" )
				return true;
			
			return false;
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