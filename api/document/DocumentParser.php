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
			
			// verify filepath
			if( $format != "test" && !@file_exists( $filepath ) )
			{
				// set response
				ApiLogging::logError( "[XlsParser::parse] Missing filepath: " . $filepath );
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
			if( !self::convertDocumentToForm( $format, $document ) )
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
		static protected function convertDocumentToForm( $format, &$document )
		{			
			// determine form tags
			$tags = self::determineFormTags( $format );
		
			// parse inputs
			self::matchInputFields( $document, $tags );
			
			// parse textboxes
			self::matchTextboxFields( $document, $tags );
			
			// parse select
			self::matchSelectFields( $document, $tags );
			
			// parse checkboxes
			self::matchCheckboxFields( $document, $tags );
			
			// parse questions
			self::matchQuestionFields( $document, $tags );			
			
			// match keywords fields
			self::matchKeywordFields( $document, $tags );
			
			// create form fields
			self::createFormFields( $document, $tags );
			
			return true;
		}
		
		
		// determine form tags
		static protected function determineFormTags( $format )
		{	
			// form tags
			$tags = array( "checkbox"	=> false,
						   "textbox"	=> false );
			
			// docX
			if( $format == "docx" )
			{
				$tags = array( "checkbox"	=> "{ formcheckbox }",
							   "textbox"	=> "{ formtext }" );				
			}
			else
			// xls/xlsx
			if( $format == "xls" || $format == "xlsx" )
			{
				$tags = array( "checkbox"	=> "{ formcheckbox }",
							   "textbox"	=> "{ formtext }" );				
			}			
			
			return $tags;
		}
		
		
		// parse inputs
		static protected function matchInputFields( &$document, $tags )
		{
			$sections = $document->getSections();
			
			// traverse sections
			foreach( $sections as $section )
			{
				$rows = $section->getRows();
					
				// traverse rows
				$noRows = count( $rows );
				for( $j = 0; $j < $noRows; $j++ )
				{
					$cells = $rows[$j]->getCells();
					
					$fieldFound = false;
					
					// traverse cells
					$no = count( $cells );
					for( $i = 0; $i < $no; $i++ )
					{
						$cell = $cells[$i];
						
						// cell used
						if( $cell["used"] )
							continue;
						
						// cell value
						$value = trim( $cell["value"] );
						if( DocumentUtils::isStringEmpty( $value ) )
							continue;
						
						if( ( !is_bool( $tags["checkbox"] ) && strtolower( $value ) == $tags["checkbox"] ) || 
							( !is_bool( $tags["textbox"] ) && strtolower( $value ) == $tags["textbox"] ) )
							continue;
						
						// following 1 cell is { FORMTEXT }
						if( ( $i + 1 ) < $no && !is_bool( $tags["textbox"] ) )
						{
							if( !$cells[$i+1]["used"] &&
								strtolower( trim( $cells[$i+1]["value"] ) ) == $tags["textbox"] )
							{						
								if( DocumentUtils::isHeaderElementText( $value ) )
									continue;
							
								$cells[$i+1]["used"] = 1;	

								$cell["used"] 			= 1;
								$cell["fieldFound"]		= 1;
								$cell["fieldValue"]		= $cell["value"];
								$cell["fieldType"]		= DocumentSectionField::INPUTTEXT;
								$cell["fieldTextRow"]	= $j;
								$cell["fieldTextCol"]	= $i + 1;
								
								$cells[$i] 	= $cell;
								$fieldFound	= true;
								continue;
							}
						}
						
						// following 2 cells are : { FORMTEXT }
						if( ( $i + 2 ) < $no && !is_bool( $tags["textbox"] ) )
						{
							if( !$cells[$i+1]["used"] && !$cells[$i+2]["used"] &&
								strtolower( trim( $cells[$i+1]["value"] ) ) == ":" &&
								strtolower( trim( $cells[$i+2]["value"] ) ) == $tags["textbox"] )
							{						
								if( DocumentUtils::isHeaderElementText( $value ) )
									continue;
							
								$cells[$i+1]["used"] = 1;	
								$cells[$i+2]["used"] = 1;								

								$cell["used"] 			= 1;
								$cell["fieldFound"]		= 1;
								$cell["fieldValue"]		= $cell["value"];
								$cell["fieldType"]		= DocumentSectionField::INPUTTEXT;
								$cell["fieldTextRow"]	= $j;
								$cell["fieldTextCol"]	= $i + 2;
								
								$cells[$i] 	= $cell;
								$fieldFound	= true;
								continue;
							}
						}
						
						
						// following 1 cell is :
						if( ( $i + 1 ) < $no )
						{
							if( !$cells[$i+1]["used"] &&
								strtolower( trim( $cells[$i+1]["value"] ) ) == ":" )
							{
								if( DocumentUtils::isHeaderElementText( $value ) )
									continue;
								
								$cells[$i+1]["used"] = 1;	

								$cell["used"] 		= 1;
								$cell["fieldFound"]	= 1;
								$cell["fieldValue"]	= $cell["value"];
								$cell["fieldType"]	= DocumentSectionField::INPUTTEXT;
								
								$cells[$i] 	= $cell;
								$fieldFound	= true;
								continue;
							}
						}
						
						// following 1 cell is empty field
						if( ( $i + 1 ) < $no && is_bool( $tags["textbox"] ) )
						{
							if( !$cells[$i+1]["used"] &&
								DocumentUtils::isStringEmpty( $cells[$i+1]["value"] ) && strlen( $cells[$i+1]["value"] ) > 4 )
							{
								if( DocumentUtils::isHeaderElementText( $value ) )
									continue;
								
								$cells[$i+1]["used"] = 1;	

								$cell["used"] 		= 1;
								$cell["fieldFound"]	= 1;
								$cell["fieldValue"]	= $cell["value"];
								$cell["fieldType"]	= DocumentSectionField::INPUTTEXT;
								
								$cells[$i] 	= $cell;
								$fieldFound	= true;
								continue;
							}
						}
						
						// current cell ends with empty space
						if( is_bool( $tags["textbox"] ) && is_bool( $tags["textbox"] ) )
						{
							$pos = stripos( $value, "_" );
							if( !is_bool( $pos ) && $pos > 0 && ( $pos + 4 ) <= strlen( $value ) )
							{
								$val2 = substr( $value, $pos );
								if( DocumentUtils::isStringEmpty( $val2 ) )
								{
									if( DocumentUtils::isHeaderElementText( $value ) )
										continue;
								
									$cells[$i+1]["used"] = 1;	

									$cell["used"] 		= 1;
									$cell["fieldFound"]	= 1;
									$cell["fieldValue"]	= trim( substr( $value, 0, $pos ) );
									$cell["fieldType"]	= DocumentSectionField::INPUTTEXT;
									
									$cells[$i] 	= $cell;
									$fieldFound	= true;
									continue;								
								}
							}
						}
						
						// following 1 row is { FORMTEXT }
						if( ( $j + 1 ) < $noRows && !is_bool( $tags["textbox"] ) )
						{
							$cells1 = $rows[$j+1]->getCells();		
							
							if( count( $cells1 ) > 0 && !$cells1[0]["used"] && 
								$cells[$i]["name"] == $cells1[0]["name"] &&
								strtolower( trim( $cells1[0]["value"] ) ) == $tags["textbox"] )
							{
								if( DocumentUtils::isHeaderElementText( $value ) )
									continue;
								
								$cells1[0]["used"] = 1;	

								$cell["used"] 			= 1;
								$cell["fieldFound"]		= 1;
								$cell["fieldValue"]		= $cell["value"];
								$cell["fieldType"]		= DocumentSectionField::INPUTTEXT;
								$cell["fieldTextRow"]	= $j + 1;
								$cell["fieldTextCol"]	= 0;								
								
								$cells[$i] 	= $cell;
								$fieldFound	= true;
								continue;
							}
							
							if( count( $cells1 ) > $i && !$cells1[$i]["used"] && 
								$cells[$i]["name"] == $cells1[$i]["name"] &&							
								strtolower( trim( $cells1[$i]["value"] ) ) == $tags["textbox"] )
							{
								if( DocumentUtils::isHeaderElementText( $value ) )
									continue;
								
								$cells1[$i]["used"] = 1;	

								$cell["used"] 			= 1;
								$cell["fieldFound"]		= 1;
								$cell["fieldValue"]		= $cell["value"];
								$cell["fieldType"]		= DocumentSectionField::INPUTTEXT;
								$cell["fieldTextRow"]	= $j + 1;
								$cell["fieldTextCol"]	= $i;									
								
								$cells[$i] 	= $cell;
								$fieldFound	= true;
								continue;
							}							
						}
						
						// following 1 row is empty space
						if( ( $j + 1 ) < $noRows && is_bool( $tags["textbox"] ) )
						{
							$cells1 = $rows[$j+1]->getCells();						
							if( count( $cells1 ) > 0 && !$cells1[0]["used"] && strlen( $cells1[0]["value"] ) > 4 )
							{
								if( DocumentUtils::isStringEmpty( $cells1[0]["value"] ) )
								{
									if( DocumentUtils::isHeaderElementText( $value ) )
										continue;
								
									$cells1[0]["used"] = 1;	

									$cell["used"] 		= 1;
									$cell["fieldFound"]	= 1;
									$cell["fieldValue"]	= $cell["value"];
									$cell["fieldType"]	= DocumentSectionField::INPUTTEXT;
									
									$cells[$i] 	= $cell;
									$fieldFound	= true;
									continue;								
								}
							}
							
							$cells1 = $rows[$j+1]->getCells();						
							if( count( $cells1 ) > $i && !$cells1[$i]["used"] && strlen( $cells1[$i]["value"] ) > 4 )
							{
								if( DocumentUtils::isStringEmpty( $cells1[$i]["value"] ) )
								{
									if( DocumentUtils::isHeaderElementText( $value ) )
										continue;
								
									$cells1[$i]["used"] = 1;	

									$cell["used"] 		= 1;
									$cell["fieldFound"]	= 1;
									$cell["fieldValue"]	= $cell["value"];
									$cell["fieldType"]	= DocumentSectionField::INPUTTEXT;
									
									$cells[$i] 	= $cell;
									$fieldFound	= true;
									continue;								
								}
							}		
						}
					}
					
					// update cells
					if( $fieldFound )
						$rows[$j]->setCells( $cells );
				}
			}

			return true;
		}
		
		
		// parse textboxes
		static protected function matchTextboxFields( &$document, $tags )
		{
			$sections = $document->getSections();
			
			// traverse sections
			foreach( $sections as $section )
			{
				$rows = $section->getRows();
					
				// traverse rows
				$noRows = count( $rows );
				for( $j = 0; $j < $noRows; $j++ )
				{
					$cells = $rows[$j]->getCells();
					
					$fieldFound = false;
					
					// traverse cells
					$no = count( $cells );
					for( $i = 0; $i < $no; $i++ )
					{
						$cell = $cells[$i];
						
						// cell used
						if( !$cell["used"] || !isset( $cell["fieldType"] ) || !isset( $cell["fieldTextRow"] ) )
							continue;
						
						if( $cell["fieldType"] != DocumentSectionField::INPUTTEXT )
							continue;
						

						// following line is { FORMTEXT }
						if( ( $cell["fieldTextRow"] + 1 ) < $noRows && !is_bool( $tags["textbox"] ) )
						{
							$k1 = $cell["fieldTextRow"];
							
							$cells1 = $rows[$k1]->getCells();		
							$cells2 = $rows[$k1+1]->getCells();								
							$noK2 	= count( $cells2 );
							
							$cellType = false;
							
							for( $k2 = 0; $k2 < $noK2; $k2++ )
							{
								if( $cells2[$k2]["used"] == 0 &&
									$cells2[$k2]["name"] == $cells1[ $cell["fieldTextCol"] ]["name"] && 
									strtolower( trim( $cells2[$k2]["value"] ) ) == $tags["textbox"] )
								{
									$cells2[$k2]["used"] = 1;
									$rows[$k1+1]->setCells( $cells2 );
									
									$cellType = DocumentSectionField::TEXTBOX;
									break;
								}
							}
							
							if( !is_bool( $cellType ) )
							{
								$cell["fieldType"] = $cellType;
								
								$cells[$i] 	= $cell;
								$fieldFound	= true;
								continue;
							}
						}
					}
					
					// update cells
					if( $fieldFound )
						$rows[$j]->setCells( $cells );
				}
			}

			return true;
		}
		
		
		// parse select
		static protected function matchSelectFields( &$document, $tags )
		{
			$sections = $document->getSections();
			
			// traverse sections
			foreach( $sections as $section )
			{
				$rows = $section->getRows();
					
				// traverse rows
				$noRows = count( $rows );
				for( $j = 0; $j < $noRows; $j++ )
				{
					$cells = $rows[$j]->getCells();
					
					$fieldFound = false;
					
					// traverse cells
					$no = count( $cells );
					for( $i = 0; $i < $no; $i++ )
					{
						$cell = $cells[$i];
						
						// cell used
						if( $cell["used"] )
							continue;
						
						// cell value
						$value = trim( $cell["value"] );
						if( DocumentUtils::isStringEmpty( $value ) )
							continue;
						
						if( ( !is_bool( $tags["checkbox"] ) && strtolower( $value ) == $tags["checkbox"] ) || 
							( !is_bool( $tags["textbox"] ) && strtolower( $value ) == $tags["textbox"] ) )
							continue;

						
						$options 		= array();
						$optionsEmpty	= array();
						$colIdx			= 0;

						// following cells are { FORMCHECKBOX }/OPTION1/{ FORMCHECKBOX }/OPTION2/...
						for( $k2 = $i + 1; $k2 < $no; $k2++ )
						{
							if( $cells[ $k2 ]["used"] )
								break;

							$colIdx++;
							if( ( $colIdx % 2 == 1 ) )
							{
								if( !is_bool( $tags["checkbox"] ) && strtolower( trim( $cells[ $k2 ]["value"] ) ) != $tags["checkbox"] )
									break;
									
								if( is_bool( $tags["checkbox"] ) )
								{
									if( !DocumentUtils::isStringEmpty( $cells[ $k2 ]["value"] ) )
										break;
									
									if( is_bool( $cells[ $k2 ]["width"] ) || $cells[ $k2 ]["width"] > 30 )
										break;
								}
							}
							
							if( $colIdx % 2 == 0 )
							{
								$found = true;
								array_push( $options, array( $j, $k2 ) );
							}
							else
							{
								array_push( $optionsEmpty, array( $j, $k2 ) );								
							}
						}
						
						
						$isMultiValue = false;
						if( count( $options ) >= 3 || ( !is_bool( $tags["checkbox"] ) && count( $options ) >= 1 ) )
							$isMultiValue = true;

						// multi-value not found
						if( !$isMultiValue )
						{
							$options 		= array();
							$optionsEmpty	= array();
							
							// following rows are { FORMCHECKBOX }/OPTION1/{ FORMCHECKBOX }/OPTION2/...
							for( $k1 = $j + 1; $k1 < $noRows; $k1++ )
							{
								$cellsItem	= $rows[ $k1 ]->getCells();
								$noK2  		= count( $cellsItem );
								$colIdx  	= 0;
								$found		= false;
								
								if( $noK2 <= $i )
									break;
								
								for( $k2 = $i; $k2 < $noK2; $k2++ )
								{
									if( $cellsItem[ $k2 ]["used"] )
										break;

									$colIdx++;
									if( ( $colIdx % 2 == 1 ) )
									{
										if( !is_bool( $tags["checkbox"] ) && strtolower( trim( $cellsItem[ $k2 ]["value"] ) ) != $tags["checkbox"] )
											break;
											
										if( is_bool( $tags["checkbox"] ) )
										{
											if( !DocumentUtils::isStringEmpty( $cellsItem[ $k2 ]["value"] ) )
												break;
											
											if( is_bool( $cellsItem[ $k2 ]["width"] ) || $cellsItem[ $k2 ]["width"] > 30 )
												break;
										}
									}
									
									if( $colIdx % 2 == 0 )
									{
										if( ( !is_bool( $tags["checkbox"] ) && strtolower( trim( $cellsItem[ $k2 ]["value"] ) ) == $tags["checkbox"] ) || 
											( !is_bool( $tags["textbox"] ) && strtolower( trim( $cellsItem[ $k2 ]["value"] ) ) == $tags["textbox"] ) )
											break;
							
										if( DocumentUtils::isStringEmpty( $cellsItem[ $k2 ]["value"] ) )
											break;
											
										$found = true;
										array_push( $options, array( $k1, $k2 ) );
									}
									else
									{
										array_push( $optionsEmpty, array( $k1, $k2 ) );								
									}
								}
								
								if( !$found && $k1 > $j )
									break;
							}
						}
						
						
						$isMultiValue = false;
						if( count( $options ) >= 3 || ( !is_bool( $tags["checkbox"] ) && count( $options ) >= 1 ) )
							$isMultiValue = true;
						
						// multi-value found
						if( $isMultiValue )
						{
							$values = array();
							foreach( $options as $item )
							{
								$cellsItem = $rows[ $item[0] ]->getCells();
								$cellsItem[ $item[1] ]["used"] = 1;
								
								array_push( $values, $cellsItem[ $item[1] ]["value"] );								
								$rows[ $item[0] ]->setCells( $cellsItem );
							}
							
							foreach( $optionsEmpty as $item )
							{
								$cellsItem = $rows[ $item[0] ]->getCells();
								$cellsItem[ $item[1] ]["used"] = 1;
								$rows[ $item[0] ]->setCells( $cellsItem );								
							}
							
							$cells = $rows[$j]->getCells();
							
							$cell["used"] 			= 1;
							$cell["fieldFound"]		= 1;
							$cell["fieldValue"]		= $cell["value"];
							
							if( count( $options ) == 1 )
							{
								$cell["fieldType"]		= DocumentSectionField::SINGLESELECT;
								$cell["fieldAllowed"]	= implode( "\n", $values );
							}
							else								
							if( count( $options ) == 2 )
							{
								$cell["fieldType"]		= DocumentSectionField::BOOLEAN;
								$cell["fieldAllowed"]	= implode( "|", $values );
							}
							else							
							if( count( $options ) == 3 )
							{
								$cell["fieldType"]		= DocumentSectionField::TRILEAN;
								$cell["fieldAllowed"]	= implode( "|", $values );
							}
							else
							{
								$cell["fieldType"]		= DocumentSectionField::SINGLESELECT;
								$cell["fieldAllowed"]	= implode( "\n", $values );								
							}
							
							$cells[$i] 	= $cell;
							$fieldFound	= true;
							continue;								
						}		
					}
					
					// update cells
					if( $fieldFound )
						$rows[$j]->setCells( $cells );
				}
			}

			return true;
		}		
		
		
		// parse checkboxes
		static protected function matchCheckboxFields( &$document, $tags )
		{
			$sections = $document->getSections();
			
			// traverse sections
			foreach( $sections as $section )
			{
				$rows = $section->getRows();
					
				// traverse rows
				$noRows = count( $rows );
				for( $j = 0; $j < $noRows; $j++ )
				{
					$cells = $rows[$j]->getCells();
					
					$fieldFound = false;
					
					// traverse cells
					$no = count( $cells );
					for( $i = 0; $i < $no; $i++ )
					{
						$cell = $cells[$i];
						
						// cell used
						if( $cell["used"] )
							continue;
						
						// cell value
						$value = trim( $cell["value"] );
						if( DocumentUtils::isStringEmpty( $value ) )
							continue;
						
						if( ( !is_bool( $tags["checkbox"] ) && strtolower( $value ) == $tags["checkbox"] ) || 
							( !is_bool( $tags["textbox"] ) && strtolower( $value ) == $tags["textbox"] ) )
							continue;
						
						// following 2 cells are Yes/No
						if( ( $i + 2 ) < $no && is_bool( $tags["checkbox"] ) )
						{
							if( !$cells[$i+1]["used"] && !$cells[$i+2]["used"] &&
								in_array( strtolower( $cells[$i+1]["value"] ), array( "yes", "no" ) ) &&
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
								continue;								
							}
						}
						
						// following 4 cells are Yes/empty/No/empty
						if( ( $i + 4 ) < $no && is_bool( $tags["checkbox"] ) )
						{
							if( !$cells[$i+1]["used"] && !$cells[$i+2]["used"] && !$cells[$i+3]["used"] && !$cells[$i+4]["used"] &&
								in_array( strtolower( $cells[$i+1]["value"] ), array( "yes", "no" ) ) &&							
								DocumentUtils::isStringEmpty( $cells[$i+2]["value"] ) &&								
								in_array( strtolower( $cells[$i+3]["value"] ), array( "yes", "no" ) ) &&							
								DocumentUtils::isStringEmpty( $cells[$i+4]["value"] ) )
							{						
								$cells[$i+1]["used"] = 1;
								$cells[$i+2]["used"] = 1;		
								$cells[$i+3]["used"] = 1;	
								$cells[$i+4]["used"] = 1;									

								$cell["used"] 			= 1;
								$cell["fieldFound"]		= 1;
								$cell["fieldValue"]		= $cell["value"];
								$cell["fieldType"]		= DocumentSectionField::BOOLEAN;
								$cell["fieldAllowed"]	= $cells[$i+1]["value"] . "|" . $cells[$i+3]["value"];
								
								$cells[$i] 	= $cell;
								$fieldFound	= true;
								continue;								
							}	
						}
						
						// following 4 cells are empty/Yes/empty/No
						if( ( $i + 4 ) < $no && is_bool( $tags["checkbox"] ) )
						{
							if( !$cells[$i+1]["used"] && !$cells[$i+2]["used"] && !$cells[$i+3]["used"] && !$cells[$i+4]["used"] &&
								DocumentUtils::isStringEmpty( $cells[$i+1]["value"] ) &&							
								in_array( strtolower( $cells[$i+2]["value"] ), array( "yes", "no" ) ) &&
								DocumentUtils::isStringEmpty( $cells[$i+3]["value"] ) &&								
								in_array( strtolower( $cells[$i+4]["value"] ), array( "yes", "no" ) ) )
							{						
								$cells[$i+1]["used"] = 1;
								$cells[$i+2]["used"] = 1;		
								$cells[$i+3]["used"] = 1;	
								$cells[$i+4]["used"] = 1;									

								$cell["used"] 			= 1;
								$cell["fieldFound"]		= 1;
								$cell["fieldValue"]		= $cell["value"];
								$cell["fieldType"]		= DocumentSectionField::BOOLEAN;
								$cell["fieldAllowed"]	= $cells[$i+2]["value"] . "|" . $cells[$i+4]["value"];
								
								$cells[$i] 	= $cell;
								$fieldFound	= true;
								continue;								
							}	
						}
						
						// following 4 cells are { FORMCHECKBOX }/Yes/{ FORMCHECKBOX }/No
						if( ( $i + 4 ) < $no && !is_bool( $tags["checkbox"] ) )
						{
							if( !$cells[$i+1]["used"] && !$cells[$i+2]["used"] && !$cells[$i+3]["used"] && !$cells[$i+4]["used"] &&
								strtolower( trim( $cells[$i+1]["value"] ) ) == $tags["checkbox"] &&							
								in_array( strtolower( $cells[$i+2]["value"] ), array( "yes", "no" ) ) &&
								strtolower( trim( $cells[$i+3]["value"] ) ) == $tags["checkbox"] &&								
								in_array( strtolower( $cells[$i+4]["value"] ), array( "yes", "no" ) ) )
							{						
								$cells[$i+1]["used"] = 1;
								$cells[$i+2]["used"] = 1;		
								$cells[$i+3]["used"] = 1;	
								$cells[$i+4]["used"] = 1;									

								$cell["used"] 			= 1;
								$cell["fieldFound"]		= 1;
								$cell["fieldValue"]		= $cell["value"];
								$cell["fieldType"]		= DocumentSectionField::BOOLEAN;
								$cell["fieldAllowed"]	= $cells[$i+2]["value"] . "|" . $cells[$i+4]["value"];
								
								$cells[$i] 	= $cell;
								$fieldFound	= true;
								continue;								
							}	
						}
						
						// following 2 rows are Yes/No
						if( ( $j + 2 ) < $noRows && is_bool( $tags["checkbox"] ) )
						{
							$cells1 = $rows[$j+1]->getCells();
							$cells2 = $rows[$j+2]->getCells();							
							
							if( count( $cells1 ) >= 1 && count( $cells2 ) >= 1 &&
								!$cells1[0]["used"] && !$cells2[0]["used"] &&
								in_array( strtolower( $cells1[0]["value"] ), array( "yes", "no" ) ) &&								
								in_array( strtolower( $cells2[0]["value"] ), array( "yes", "no" ) ) )
							{						
								$cells1[0]["used"] = 1;
								$cells2[0]["used"] = 1;		

								$cell["used"] 			= 1;
								$cell["fieldFound"]		= 1;
								$cell["fieldValue"]		= $cell["value"];
								$cell["fieldType"]		= DocumentSectionField::BOOLEAN;
								$cell["fieldAllowed"]	= $cells1[0]["value"] . "|" . $cells2[0]["value"];
								
								$cells[$i] 	= $cell;
								$fieldFound	= true;
								continue;
							}
						}
						
						// following 1 cell is Yes/No
						if( ( $i + 1 ) < $no && is_bool( $tags["checkbox"] ) )
						{
							$val2 = str_ireplace( "yes", "", $cells[$i+1]["value"] );
							$val2 = str_ireplace( "no", "", $val2 );		
							$val2 = str_ireplace( "_", "", $val2 );									
							
							if( !$cells[$i+1]["used"] &&
								!is_bool( stripos( $cells[$i+1]["value"], "yes" ) ) &&
								!is_bool( stripos( $cells[$i+1]["value"], "no" ) ) &&
								DocumentUtils::isStringEmpty( $val2 ) )
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
								continue;								
							}
						}
						
						// following 1 row is Yes/No
						if( ( $j + 1 ) < $noRows && is_bool( $tags["checkbox"] ) )
						{
							$cells1 = $rows[$j+1]->getCells();
							if( count( $cells1 ) >= 1 )
							{
								$val2 = str_ireplace( "yes", "", $cells1[0]["value"] );
								$val2 = str_ireplace( "no", "", $val2 );	
								$val2 = str_ireplace( "_", "", $val2 );									
								
								if( !$cells1[0]["used"] &&
									!is_bool( stripos( $cells1[0]["value"], "yes" ) ) &&
									!is_bool( stripos( $cells1[0]["value"], "no" ) ) &&
									DocumentUtils::isStringEmpty( $val2 ) )
								{						
									$cells1[0]["used"] = 1;
									
									$allowed = trim( $cells1[0]["value"] );
									$allowed = preg_replace( '!\s+!', '|', $allowed );								

									$cell["used"] 			= 1;
									$cell["fieldFound"]		= 1;
									$cell["fieldValue"]		= $cell["value"];
									$cell["fieldType"]		= DocumentSectionField::BOOLEAN;
									$cell["fieldAllowed"]	= "Yes|No";
									
									$cells[$i] 	= $cell;
									$fieldFound	= true;
									continue;								
								}
							}
						}						
					}
					
					// update cells
					if( $fieldFound )
						$rows[$j]->setCells( $cells );
				}
			}

			return true;
		}		
		
		
		// parse questions
		static protected function matchQuestionFields( &$document, $tags )
		{
			$sections = $document->getSections();
			
			// traverse sections
			foreach( $sections as $section )
			{
				$rows = $section->getRows();
					
				// traverse rows
				$noRows = count( $rows );
				for( $j = 0; $j < $noRows; $j++ )
				{
					$cells = $rows[$j]->getCells();
					
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
						
						// single question cell
						if( ( $i + 1 ) >= $no )
						{
							$cell["used"] 		= 1;
							$cell["fieldFound"]	= 1;
							$cell["fieldValue"]	= $cell["value"];
							$cell["fieldType"]	= DocumentSectionField::TEXTBOX;
							
							$cells[$i] 	= $cell;
							$fieldFound	= true;
							continue;								
						}
					}
					
					// update cells
					if( $fieldFound )
						$rows[$j]->setCells( $cells );
				}
			}

			return true;
		}	
		
		
		// match keywords fields
		static protected function matchKeywordFields( &$document, $tags )
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
						{
							if( !isset( $cell["fieldFound"] ) || $cell["fieldType"] != DocumentSectionField::INPUTTEXT )
								continue;
						}
	
						if( isset( $cell["fieldValue"] ) )
							$value = $cell["fieldValue"];
						else
							$value = $cell["value"];			
						
						$value	= DocumentUtils::cleanupKeywordValue( $value );
						$value2 = strtolower( $value );			
						
						// verify keywords
						foreach( $keywords as $keyword )
						{
							// field found
							if( DocumentUtils::isKeywordMatch( $value2, $keyword["name"] ) )
							{
								$cell["used"] 		= 1;
								$cell["fieldFound"]	= 1;
								$cell["fieldValue"]	= $value;
								$cell["fieldType"]	= $keyword["type"];
								
								if( strlen( $keyword["allowedValues"] ) > 0 )
									$cell["fieldAllowed"] = $keyword["allowedValues"];
								
								$cells[$i] 	= $cell;
								$fieldFound	= true;
								continue;
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
					
					$prevCell = false;
					
					// traverse cells
					foreach( $cells as $cell )
					{
						// field found
						if( !isset( $cell["fieldFound"] ) )
							continue;
						
						if( in_array( $cell["fieldValue"], array( ":" ) ) )
							continue;
						
						$allowedValues = "";
						if( isset( $cell["fieldAllowed"] ) )
							$allowedValues = $cell["fieldAllowed"];
						
						$cell["fieldValue"] = str_replace( "\r\n", " ", $cell["fieldValue"] );
						$cell["fieldValue"] = str_replace( "\n", " ", $cell["fieldValue"] );
						$cell["fieldValue"] = str_replace( "\r", " ", $cell["fieldValue"] );
						$cell["fieldValue"] = ucfirst( $cell["fieldValue"] );
						
						if( $prevCell["fieldValue"] == $cell["fieldValue"] &&
							$prevCell["fieldType"] == $cell["fieldType"] )
						{
							$prevCell = $cell;							
							continue;
						}
						
						// add field
						$section->addField( $cell["fieldValue"], $cell["fieldType"], $allowedValues );						
						
						$prevCell = $cell;
					}
				}
			}

			return true;
		}
		
	};


?>