<?php

	// Document Parser - document parser (various formats supported).


	// DocumentParser
	class DocumentParser
	{
		// globals
		static protected $useTestParser	= true;
		
		
		
		// parse
		static public function parse( $filename, $filepath, &$format )
		{
			global $viewParserOutput;
			global $fatFingerEnabled;
			
			// determine format
			$format = self::determineFormat( $filename );
			if( is_bool( $format ) )
			{
				ApiLogging::logError( "[DocumentParser::parse] Missing format for file: " . $filename );
				$format = "";
				return false;
			}
			
			// parse format
			$document = self::parseFormat( $format, $filename, $filepath );
			if( is_bool( $document ) )
				return false;
			
			// logging
			if( $viewParserOutput )
				$document->outputRows( false );
			
			// convert document to form
			if( !self::convertDocumentToForm( $document ) )
				return false;
			
			// logging
			DocumentUtils::logAnalysis( "Document title: " . $document->getTitle() );
			DocumentUtils::logAnalysis( "Form elements found: " . $document->getFieldCount() );			
			
			// logging
			if( $viewParserOutput )
				$document->outputFields();
			else
			if( !$fatFingerEnabled )
				$document->outputFields(1);			
			
			return $document;
		}
		
		
		// determine format
		static protected function determineFormat( $filename )
		{
			$pos = strrpos( $filename, "." );
			if( is_bool( $pos ) )
			{
				// set response
				ApiLogging::logError( "[DocumentParser::determineFormat] Missing format for file: " . $filename );
				return false;
			}
			
			$ext = strtolower( substr( $filename, $pos + 1 ) );
			
			return $ext;
		}
		
		
		// parse format
		static protected function parseFormat( $format, $filename, $filepath )
		{
			$parser = false;
			
			// initialize parser
			switch( $format )
			{
				case "pdf":
					$parser = new PdfParser();
					break;
					
				case "doc":
					$parser = new DocParser();
					break;			
					
				case "docx":
					$parser = new DocxParser();
					break;			
					
				case "xls":
					$parser = new XlsParser();
					break;			
					
				case "xlsx":
					$parser = new XlsxParser();
					break;	
					
				case "png":
				case "jpg":
				case "gif":				
					$parser = new ImageParser();
					break;					
			}
			
			// test parser
			if( self::$useTestParser && $format == "test" )
			{
				$parser = new TestParser();
			}
			
			// unknown format
			if( is_bool( $parser ) )
			{
				ApiLogging::logError( "[DocumentParser::parseFormat] Unknown format " . $format . " for file: " . $filename );
				return false;
			}
			
			// logging
			DocumentUtils::logAnalysis( "Parsing " . strtoupper( $format ) . " file" );
			
			// parse
			$document = $parser->parse( $filename, $filepath );
			if( is_bool( $document ) )
			{
				ApiLogging::logError( "[DocumentParser::parseFormat] Failed to parse format " . $format . " for file: " . $filename );
				return false;
			}			
			
			// logging
			DocumentUtils::logAnalysis( "File parsed successfully" );
			
			// set type
			$document->setType( $format );
			
			// verify title
			$title = $document->getTitle();
			if( strlen( $title ) <= 0 )
			{
				$title = DocumentUtils::normalizeFilenameTitle( $filename );
				$document->setTitle( $title );
			}
			
			return $document;
		}
		
		
		// convert document to form
		static public function convertDocumentToForm( &$document )
		{
			// parse questions
			self::matchQuestionFields( $document );			
			
			// match keywords fields
			self::matchKeywordFields( $document );
			
			// create form fields
			self::createFormFields( $document );
			
			return true;
		}
		
		
		// parse questions
		protected function matchQuestionFields( &$document )
		{
			$sections = $document->getSections();
			
			// traverse sections
			foreach( $sections as $section )
			{
				$rows = $section->getRows();
					
				// traverse rows
				foreach( $rows as $row )
				{
					$cells = $row->getCells();
					
					$fieldFound = false;
					
					// traverse cells
					$no = count( $cells );
					for( $i = 0; $i < $no; $i++ )
					{
						$cell = $cells[$i];
						
						// cell used
						if( $cell["used"] )
							continue;
						
						$value = trim( $cell["value"] );
						
						// question
						if( substr( $value, -1 ) != "?" )
							continue;
						
						// following 2 cells are Yes/No
						if( ( $i + 2 ) < $no )
						{
							if( in_array( strtolower( $cells[$i+1]["value"] ), array( "yes", "no" ) ) &&
								in_array( strtolower( $cells[$i+2]["value"] ), array( "yes", "no" ) ) )
							{						
								$cells[$i+1]["used"] = 1;
								$cells[$i+2]["used"] = 1;		

								$cell["used"] 			= 1;
								$cell["fieldFound"]		= 1;
								$cell["fieldValue"]		= $cell["value"];
								$cell["fieldType"]		= DocumentSectionField::BOOLEAN;
								$cell["fieldAllowed"]	= $cells[$i+1]["value"] . "|" . $cells[$i+2]["value"];
								
								$cells[$i] 	= $cell;
								$fieldFound	= true;
								break;								
							}	
						}
						
						// following 1 cell is Yes/No
						if( ( $i + 1 ) < $no )
						{
							if( !is_bool( stripos( $cells[$i+1]["value"], "yes" ) ) &&
								!is_bool( stripos( $cells[$i+1]["value"], "no" ) ) )
							{						
								$cells[$i+1]["used"] = 1;
								
								$allowed = trim( $cells[$i+1]["value"] );
								$allowed = preg_replace( '!\s+!', '|', $allowed );								

								$cell["used"] 			= 1;
								$cell["fieldFound"]		= 1;
								$cell["fieldValue"]		= $cell["value"];
								$cell["fieldType"]		= DocumentSectionField::BOOLEAN;
								$cell["fieldAllowed"]	= $allowed;
								
								$cells[$i] 	= $cell;
								$fieldFound	= true;
								break;								
							}
						}
						
						// single question cell
						if( ( $i + 1 ) >= $no )
						{
							$cell["used"] 		= 1;
							$cell["fieldFound"]	= 1;
							$cell["fieldValue"]	= $cell["value"];
							$cell["fieldType"]	= DocumentSectionField::TEXTBOX;
							
							$cells[$i] 	= $cell;
							$fieldFound	= true;
							break;								
						}
					}
					
					// update cells
					if( $fieldFound )
						$row->setCells( $cells );					
				}
			}

			return true;
		}		
		
		
		// match keywords fields
		protected function matchKeywordFields( &$document )
		{
			global $keywords;
			
			$sections = $document->getSections();
			
			// traverse sections
			foreach( $sections as $section )
			{
				$rows = $section->getRows();
					
				// traverse rows
				foreach( $rows as $row )
				{
					$cells = $row->getCells();
					
					$fieldFound = false;
					
					// traverse cells
					$no = count( $cells );
					for( $i = 0; $i < $no; $i++ )
					{
						$cell = $cells[$i];
						
						// cell used
						if( $cell["used"] )
							continue;
						
						$value 	= rtrim( $cell["value"], ":( " );
						$value 	= ltrim( $value, "0123456789. " );
						$value2 = strtolower( $value );
						
						// verify keywords
						foreach( $keywords as $keyword )
						{
							// field found
							if( $value2 == $keyword["name"] )
							{
								$cell["used"] 		= 1;
								$cell["fieldFound"]	= 1;
								$cell["fieldValue"]	= $value;
								$cell["fieldType"]	= $keyword["type"];
								
								if( strlen( $keyword["allowedValues"] ) > 0 )
									$cell["fieldAllowed"] = $keyword["allowedValues"];
								
								$cells[$i] 	= $cell;
								$fieldFound	= true;
								break;
							}
						}
					}
					
					// update cells
					if( $fieldFound )
						$row->setCells( $cells );
				}
			}

			return true;
		}
		
		
		// create form fields
		static protected function createFormFields( &$document )
		{
			$sections = $document->getSections();
			
			// traverse sections
			foreach( $sections as $section )
			{
				$rows = $section->getRows();
					
				// traverse rows
				foreach( $rows as $row )
				{
					$cells = $row->getCells();
					
					// traverse cells
					foreach( $cells as $cell )
					{
						// field found
						if( !isset( $cell["fieldFound"] ) )
							continue;
						
						$allowedValues = "";
						if( isset( $cell["fieldAllowed"] ) )
							$allowedValues = $cell["fieldAllowed"];
						
						// add field
						$section->addField( $cell["fieldValue"], $cell["fieldType"], $allowedValues );
					}
				}
			}

			return true;
		}
		
	};


?>