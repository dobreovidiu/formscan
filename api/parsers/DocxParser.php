<?php

	// DocX Parser - parser for .doc files


	// DocxParser
	class DocxParser
	{
		// globals
		protected $document = false;
		
		
		// parse
		public function parse( $filename, $filepath )
		{			
			global $viewParserOutput;

			
			// CREATE READER
			$word = \PhpOffice\PhpWord\IOFactory::load( $filepath, 'Word2007' );
			if( !$word )
			{
				ApiLogging::logError( "[DocxParser::parse] Failed to parse file " . $filename );
				return false;
			}				
			
			// logging
			if( isset( $viewParserOutput ) && $viewParserOutput )
				$this->outputParser( $word );
					
			DocumentUtils::logFile( "docx-parser", $word );
			
			
			// INITIALIZE DOCUMENT
			$this->document = new Document();

			
			// GET TITLE
			$title = "";
			if( $word->getDocumentProperties() )
				$title = $word->getDocumentProperties()->getTitle();
				
			$this->document->setTitle( $title );
			
			
			// GET CONTENT
			$wordSections = $word->getSections();			
			if( !$wordSections )
				return false;
			
			$sections 		= array();
			$curSection		= false;
			$curRow			= false;
			$rowID			= 1;			
			
			// traverse sections
			foreach( $wordSections as $wordSection )
			{
				// elements
				$elements = $wordSection->getElements();
				if( $elements )
					$this->processElements( $elements, $curSection, $curRow, $rowID );
			}
			
			// add last section
			if( !is_bool( $curSection ) )
				array_push( $sections, $curSection );
			
			// build document
			$this->document->buildFromArray( $sections );	
			
			// logging
			DocumentUtils::logFile( "docx-result", $this->document->outputRows() );
		
			return $this->document;
		}		
		
		
		// process elements
		protected function processElements( &$elements, &$curSection, &$curRow, &$rowID )
		{
			// traverse elements
			foreach( $elements as $element )
			{
				// create section
				if( is_bool( $curSection ) )
					$curSection = array( "rows" => array() );
				
				// Text
				if( get_class( $element ) == "PhpOffice\PhpWord\Element\Text" )
				{
					$this->processElementText( $element, $curSection, $curRow, $rowID );
				}
				else
				// Title
				if( get_class( $element ) == "PhpOffice\PhpWord\Element\Title" )
				{
					$this->processElementTitle( $element, $curSection, $curRow, $rowID );
				}	
				else
				// ListItem
				if( get_class( $element ) == "PhpOffice\PhpWord\Element\ListItem" )
				{
					$text = $element->getTextObject();
					if( !$text )
						continue;
					
					$this->processElementList( $text, $curSection, $curRow, $rowID );
				}				
				else
				// TextRun
				if( get_class( $element ) == "PhpOffice\PhpWord\Element\TextRun" )
				{
					$elements = $element->getElements();
					if( !$elements )
						continue;
					
					$this->processElementRunText( $elements, $curSection, $curRow, $rowID );
				}	
				else
				// Table
				if( get_class( $element ) == "PhpOffice\PhpWord\Element\Table" )
				{
					$rows = $element->getRows();
					if( !$rows )
						continue;
					
					$this->processElementTable( $rows, $curSection, $curRow, $rowID );
				}	
				else
				// PageBreak
				if( get_class( $element ) == "PhpOffice\PhpWord\Element\PageBreak" )
				{
				}
				else
				// Image
				if( get_class( $element ) == "PhpOffice\PhpWord\Element\Image" )
				{
				}		
				else
				// Link
				if( get_class( $element ) == "PhpOffice\PhpWord\Element\Link" )
				{
				}				
				else
				// PreserveText
				if( get_class( $element ) == "PhpOffice\PhpWord\Element\PreserveText" )
				{
				}			
				else
				// TextBreak
				if( get_class( $element ) == "PhpOffice\PhpWord\Element\TextBreak" )
				{
				}				
				else
				// unknown type				
				{
					echo "Elements: Unprocessed: " . get_class( $element ) . "\n";
				}					
			}
			
			return true;
		}
		
		
		// process element text
		protected function processElementText( &$element, &$curSection, &$curRow, &$rowID )
		{		
			// get text
			$text = $element->getText();
			if( strlen( $text ) <= 0 || !DocumentUtils::isValidText( $text ) )
				return true;

			// convert text
			$text = DocumentUtils::convertText( $text );
	
			// create row			
			$curRow = array();

			// add row cell
			array_push( $curRow, array( 1, $text ) );	
			
			// add row
			$curSection["rows"][$rowID] = $curRow;
			$curRow = false;
			$rowID++;
				
			return true;
		}

		
		// process element title
		protected function processElementTitle( &$element, &$curSection, &$curRow, &$rowID )
		{		
			// get text
			$text = $element->getText();
			if( strlen( $text ) <= 0 || !DocumentUtils::isValidText( $text ) )
				return true;

			// convert text
			$text = DocumentUtils::convertText( $text );
	
			// create row			
			$curRow = array();

			// add row cell
			array_push( $curRow, array( 1, $text ) );	
			
			// add row
			$curSection["rows"][$rowID] = $curRow;
			$curRow = false;
			$rowID++;
				
			return true;
		}
		
		
		// process element list
		protected function processElementList( &$element, &$curSection, &$curRow, &$rowID )
		{		
			// get text
			$text = $element->getText();
			
			// convert text
			$text = DocumentUtils::convertText( $text );
	
			// create row			
			$curRow = array();

			// add row cell
			array_push( $curRow, array( 1, $text ) );	
			
			// add row
			$curSection["rows"][$rowID] = $curRow;
			$curRow = false;
			$rowID++;
				
			return true;
		}
		
		
		// process element run text
		protected function processElementRunText( &$element, &$curSection, &$curRow, &$rowID )
		{
			$text = $this->processElementRunTextContent( $element );
			if( strlen( $text ) <= 0 || !DocumentUtils::isValidText( $text ) )
				return true;
				
			// convert text
			$text = DocumentUtils::convertText( $text );
	
			// create row			
			$curRow = array();

			// add row cell
			array_push( $curRow, array( 1, $text ) );	
			
			// add row
			$curSection["rows"][$rowID] = $curRow;
			$curRow = false;
			$rowID++;
				
			return true;
		}		
		
		
		// process element run text
		protected function processElementRunTextContent( &$elements )
		{
			$result = "";
		
			// traverse elements
			foreach( $elements as $element )
			{
				// Text
				if( get_class( $element ) == "PhpOffice\PhpWord\Element\Text" )
				{
					$text = $element->getText();
					if( strlen( $text ) > 0 )
						$result .= $text;
					else
						$result .= " ";
				}
				else
				// ListItem
				if( get_class( $element ) == "PhpOffice\PhpWord\Element\ListItem" )
				{
					$textObj = $element->getTextObject();
					if( $textObj )
					{
						$text = $textObj->getText();
						if( is_array( $text ) )
							$text = $this->arrayToString( $text );	
							
						if( strlen( $text ) > 0 && strlen( trim( $text ) ) != "" )
							$result .= $text;
						else
							$result .= " ";
					}
				}		
				else					
				// Link
				if( get_class( $element ) == "PhpOffice\PhpWord\Element\Link" )
				{
					$text = $element->getText();
					if( is_array( $text ) )
						$text = $this->arrayToString( $text );	
							
					if( strlen( $text ) > 0 )					
						$result .= $text;						
				}	
				else
				// Image
				if( get_class( $element ) == "PhpOffice\PhpWord\Element\Image" )
				{
				}				
				else					
				// Footnote
				if( get_class( $element ) == "PhpOffice\PhpWord\Element\Footnote" )
				{
					if( $element->getElements() )
					{
						$text = $this->processElementFootnoteContent( $element->getElements() );
						if( trim( $text ) > 0 )
						{
							$result .= "<br><br><i>FOOTNOTE:</i><br>" . $text;
						}
					}						
				}		
				else
				// PreserveText
				if( get_class( $element ) == "PhpOffice\PhpWord\Element\PreserveText" )
				{
					$text = $element->getText();
					if( is_array( $text ) )
						$text = $this->arrayToString( $text );	
					
					$result .= $text;
				}				
				else
				// TextBreak
				if( get_class( $element ) == "PhpOffice\PhpWord\Element\TextBreak" )
				{
				}					
				else
				// unknown type				
				{
					echo "RunTextContent: Unprocessed: " . get_class( $element ) . "\n";
				}					
			}
			
			return $result;
		}		
		
		
		// process element table
		protected function processElementTable( &$rows, &$curSection, &$curRow, &$rowID )
		{
			// traverse rows
			foreach( $rows as $row )
			{
				// get cells
				$cells = $row->getCells();
				if( !$cells )
					continue;
		
				// create row			
				$curRow = array();				
				
				$colID = 1;
					
				// traverse cells
				foreach( $cells as $cell )
				{
					// get elements
					$elements = $cell->getElements();
					if( !$elements )
						continue;			
						
					$cellContent = "";
					
					// traverse elements
					foreach( $elements as $element )
					{
						// Title
						if( get_class( $element ) == "PhpOffice\PhpWord\Element\Title" )
						{
							$text = $element->getText();
							if( is_array( $text ) )
								$text = $this->arrayToString( $text );								
							
							if( strlen( $cellContent ) > 0 )
								$cellContent .= " ";
							
							$cellContent .= $text;
						}		
						else					
						// Text
						if( get_class( $element ) == "PhpOffice\PhpWord\Element\Text" )
						{
							$text = $element->getText();
							if( is_array( $text ) )
								$text = $this->arrayToString( $text );	
					
							if( strlen( $cellContent ) > 0 )
								$cellContent .= " ";
							
							$cellContent .= $text;
						}		
						else
						// TextRun
						if( get_class( $element ) == "PhpOffice\PhpWord\Element\TextRun" )
						{
							if( !$element->getElements() )
								continue;
							
							$text = $this->processElementRunTextContent( $element->getElements() );
							if( strlen( $text ) > 0 )
							{
								if( strlen( $cellContent ) > 0 )
									$cellContent .= "\n";
									
								$cellContent .= $text;
							}
						}
						else					
						// ListItem
						if( get_class( $element ) == "PhpOffice\PhpWord\Element\ListItem" )
						{
							$textObj = $element->getTextObject();
							if( !$textObj )	
								continue;
							
							$text = $textObj->getText();
							if( is_array( $text ) )
								$text = $this->arrayToString( $text );	
							
							if( strlen( $text ) > 0 )
								$cellContent .= "\n" . $text;
						}	
						else					
						// Link
						if( get_class( $element ) == "PhpOffice\PhpWord\Element\Link" )
						{
							$text = $element->getText();
							if( is_array( $text ) )
								$text = $this->arrayToString( $text );	
							
							$cellContent .= $text;		
						}						
						else
						// Image
						if( get_class( $element ) == "PhpOffice\PhpWord\Element\Image" )
						{
						}
						else
						// Table
						if( get_class( $element ) == "PhpOffice\PhpWord\Element\Table" )
						{
						}		
						else
						// PreserveText
						if( get_class( $element ) == "PhpOffice\PhpWord\Element\PreserveText" )
						{
							$text = $element->getText();
							if( is_array( $text ) )
								$text = $this->arrayToString( $text );	
							
							$cellContent .= $text;
						}				
						else
						// TextBreak
						if( get_class( $element ) == "PhpOffice\PhpWord\Element\TextBreak" )
						{
						}					
						else
						// unknown type						
						{
							echo "Table: Unprocessed: " . get_class( $element ) . "\n";
						}							
					}
					
					// add row cell
					array_push( $curRow, array( $colID, $cellContent ) );
					$colID++;
				}		

				// add row
				$curSection["rows"][$rowID] = $curRow;
				$curRow = false;
				$rowID++;				
			}
			
			return true;
		}		
		
		
		// process element footnote
		protected function processElementFootnote( &$element, &$curSection, &$curRow, &$rowID )
		{
			// get content
			$text = $this->processElementFootnoteContent( $element );
			if( strlen( $text ) <= 0 || strlen( trim( $text ) ) <= 0 || !DocumentUtils::isValidText( $text ) )
				return true;
			
			// convert text
			$text = DocumentUtils::convertText( $text );
	
			// create row			
			$curRow = array();

			// add row cell
			array_push( $curRow, array( 1, $text ) );	
			
			// add row
			$curSection["rows"][$rowID] = $curRow;
			$curRow = false;
			$rowID++;
			
			return true;
		}
		
		
		// process element footnote text
		protected function processElementFootnoteContent( &$element, &$curSection, &$curRow, &$rowID )
		{
			$result = "";
			
			// traverse elements
			foreach( $elements as $element )
			{
				// Text
				if( get_class( $element ) == "PhpOffice\PhpWord\Element\Text" )
				{
					$text = $element->getText();
					if( is_array( $text ) )
						$text = $this->arrayToString( $text );	
					
					if( strlen( $text ) > 0 )
						$result .= $text;
					else
						$result .= " ";
				}
				else
				// ListItem
				if( get_class( $element ) == "PhpOffice\PhpWord\Element\ListItem" )
				{
					$textObj = $element->getTextObject();
					if( $textObj )
					{
						$text = $textObj->getText();
						if( is_array( $text ) )
							$text = $this->arrayToString( $text );	
						
						if( strlen( $text ) > 0 )
							$result .= $text;
						else
							$result .= " ";
					}
				}		
				else					
				// Link
				if( get_class( $element ) == "PhpOffice\PhpWord\Element\Link" )
				{
					$text = $element->getText();
					if( is_array( $text ) )
						$text = $this->arrayToString( $text );	
					
					$result .= $text;
				}		
				else
				// Image
				if( get_class( $element ) == "PhpOffice\PhpWord\Element\Image" )
				{
				}					
				else
				// PreserveText
				if( get_class( $element ) == "PhpOffice\PhpWord\Element\PreserveText" )
				{
					$text = $element->getText();
					if( is_array( $text ) )
						$text = $this->arrayToString( $text );	

					$result .= $text;
				}
				else
				// TextBreak
				if( get_class( $element ) == "PhpOffice\PhpWord\Element\TextBreak" )
				{
				}					
				else
				// unknown type				
				{
					echo "Footnote: Unprocessed: " . get_class( $element ) . "\n";
				}					
			}
			
			return $result;
		}
		
		
		// convert array to string
		protected function arrayToString( &$list )
		{
			$val = "";
			
			// traverse array
			foreach( $list as $item )
			{
				if( is_string( $item ) )
					$val .= $item;
			}
			
			return $val;
		}
		

		// output parser
		protected function outputParser( &$word )
		{
			echo "Parser Output: \n\n";
			
			// get title
			$prop = $word->getDocumentProperties();
			if( $prop )
			{
				echo "Properties\n";
				echo "Title: " . $prop->getTitle() . "\n";								
				echo "\n";
			}
			
			// get sections
			$sections = $word->getSections();			
			if( $sections )
			{
				// traverse sections
				foreach( $sections as $section )
				{
					// headers
					$headers = $section->getHeaders();
					if( $headers )
					{
						foreach( $headers as $header )
						{
							$elements = $header->getElements();
							if( $elements )
								$this->outputElements( $elements );
						}
					}
					
					// footers
					$footers = $section->getFooters();
					if( $footers )
					{
						foreach( $footers as $footer )
						{
							$elements = $footer->getElements();
							if( $elements )
								$this->outputElements( $elements );
						}
					}
					
					// elements
					$elements = $section->getElements();
					if( $elements )
						$this->outputElements( $elements );
				}	
			}
		}
		
		
		// output elements
		protected function outputElements( &$elements )
		{
			// traverse elements
			foreach( $elements as $element )
			{
				// Text
				if( get_class( $element ) == "PhpOffice\PhpWord\Element\Text" )
				{
					$text = $element->getText();
					if( !$text )
						continue;
					
					echo "TEXT: " . $text . "\n";
				}
				else
				// Title
				if( get_class( $element ) == "PhpOffice\PhpWord\Element\Title" )
				{
					$text = $element->getText();
					if( !$text )
						continue;
										
					echo "TITLE: " . $text . "\n";	
				}	
				else
				// ListItem
				if( get_class( $element ) == "PhpOffice\PhpWord\Element\ListItem" )
				{
					$textObj = $element->getTextObject();
					if( !$textObj )
						continue;
					
					$text = $textObj->getText();
					if( !$text )
						continue;
					
					echo "LIST: " . $text . "\n";
				}
				else
				// TextRun
				if( get_class( $element ) == "PhpOffice\PhpWord\Element\TextRun" )
				{
					$elements = $element->getElements();
					if( !$elements )
						continue;
					
					$this->outputElementRunText( $elements );
				}
				else
				// Table
				if( get_class( $element ) == "PhpOffice\PhpWord\Element\Table" )
				{
					$rows = $element->getRows();
					if( !$rows )
						continue;
					
					$this->outputElementTable( $rows );
				}	
				else
				// Image
				if( get_class( $element ) == "PhpOffice\PhpWord\Element\Image" )
				{
					echo "IMAGE\n";						
				}		
				else
				// Link
				if( get_class( $element ) == "PhpOffice\PhpWord\Element\Link" )
				{
					$text = $element->getText();
					if( !$text )
						continue;					
					
					echo "LINK: " . $text . "\n";						
				}
				else
				// PageBreak
				if( get_class( $element ) == "PhpOffice\PhpWord\Element\PageBreak" )
				{
					echo "PAGE BREAK\n";	
				}
				else
				// PreserveText
				if( get_class( $element ) == "PhpOffice\PhpWord\Element\PreserveText" )
				{
					$text = $element->getText();
					if( is_array( $text ) )
						$text = $this->arrayToString( $text );
					
					echo "PRESERVE TEXT: " . $text . "\n";
				}
				else
				// TextBreak
				if( get_class( $element ) == "PhpOffice\PhpWord\Element\TextBreak" )
				{
					echo "TEXT BREAK\n";
				}					
				else
				// unknown type				
				{
					echo "Elements: Unprocessed: " . get_class( $element ) . "\n";
				}
			}
			
			return true;
		}
		
		
		// output element run text
		protected function outputElementRunText( &$elements )
		{
			foreach( $elements as $element )
			{
				// Text
				if( get_class( $element ) == "PhpOffice\PhpWord\Element\Text" )
				{
					$text = $element->getText();
					if( !$text )
						continue;
					
					echo "RUNTEXT TEXT: " . $text . "\n";
				}
				else
				// ListItem
				if( get_class( $element ) == "PhpOffice\PhpWord\Element\ListItem" )
				{
					$textObj = $element->getTextObject();
					if( !$textObj )
						continue;
					
					$text = $textObj->getText();
					if( !$text )
						continue;
					
					echo "RUNTEXT LIST: " . $text . "\n";		
				}		
				else					
				// Link
				if( get_class( $element ) == "PhpOffice\PhpWord\Element\Link" )
				{
					$text = $element->getText();
					if( !$text )
						continue;					

					echo "RUNTEXT LINK: " . $text . "\n";						
				}	
				else
				// Image
				if( get_class( $element ) == "PhpOffice\PhpWord\Element\Image" )
				{
					echo "RUNTEXT IMAGE\n";						
				}				
				else					
				// Footnote
				if( get_class( $element ) == "PhpOffice\PhpWord\Element\Footnote" )
				{
					if( $element->getElements() )
						$this->outputElementFootnote( $element->getElements() );					
				}	
				else
				// PreserveText
				if( get_class( $element ) == "PhpOffice\PhpWord\Element\PreserveText" )
				{
					$text = $element->getText();
					if( is_array( $text ) )
						$text = $this->arrayToString( $text );	
					
					echo "RUNTEXT PRESERVE TEXT: " . $text . "\n";	
				}				
				else
				// TextBreak
				if( get_class( $element ) == "PhpOffice\PhpWord\Element\TextBreak" )
				{
					echo "RUNTEXT TEXT BREAK\n";
				}					
				else
				// unknown type				
				{
					echo "RunTextContent: Unprocessed: " . get_class( $element ) . "\n";
				}					
			}
		}		
		
		
		// output element table
		protected function outputElementTable( &$rows, $innerTable = false )
		{
			// traverse rows
			foreach( $rows as $row )
			{
				// get cells
				$cells = $row->getCells();
				if( !$cells )
					continue;
				
				// traverse cells
				foreach( $cells as $cell )
				{
					// get elements
					$elements = $cell->getElements();
					if( !$elements )
						continue;
						
					$cellContent = "";
					foreach( $elements as $element )
					{
						// Title
						if( get_class( $element ) == "PhpOffice\PhpWord\Element\Title" )
						{
							$text = $element->getText();	
							if( !$text )
								continue;
					
							echo "TABLE TITLE: " . $text . "\n";
						}
						else					
						// Text
						if( get_class( $element ) == "PhpOffice\PhpWord\Element\Text" )
						{
							$text = $element->getText();
							if( !$text )
								continue;
					
							echo "TABLE TEXT: " . $text . "\n";
						}		
						else
						// TextRun
						if( get_class( $element ) == "PhpOffice\PhpWord\Element\TextRun" )
						{
							if( $element->getElements() )
								$this->outputElementRunText( $element->getElements() );
						}
						else
						// ListItem
						if( get_class( $element ) == "PhpOffice\PhpWord\Element\ListItem" )
						{
							$textObj = $element->getTextObject();
							if( !$textObj )
								continue;
							
							$text = $textObj->getText();
							if( !$text )
								continue;
					
							echo "TABLE LIST ITEM: " . $text . "\n";														
						}	
						else					
						// Link
						if( get_class( $element ) == "PhpOffice\PhpWord\Element\Link" )
						{
							$text = $element->getText();
							if( !$text )
								continue;
							
							echo "TABLE LINK: " . $text . "\n";						
						}						
						else
						// Image
						if( get_class( $element ) == "PhpOffice\PhpWord\Element\Image" )
						{
							echo "TABLE IMAGE\n";									
						}
						else
						// Table
						if( get_class( $element ) == "PhpOffice\PhpWord\Element\Table" )
						{
							$rows = $element->getRows();
							if( $rows )
								$this->outputElementTable( $rows, true );
						}		
						else
						// PreserveText
						if( get_class( $element ) == "PhpOffice\PhpWord\Element\PreserveText" )
						{
							$text = $element->getText();
							if( is_array( $text ) )
								$text = $this->arrayToString( $text );							
							
							echo "TABLE PRESERVE TEXT: " . $text . "\n";	
						}		
						else
						// TextBreak
						if( get_class( $element ) == "PhpOffice\PhpWord\Element\TextBreak" )
						{
							echo "TABLE TEXT BREAK\n";
						}							
						else
						// unknown type						
						{
							echo "Table: Unprocessed: " . get_class( $element ) . "\n";
						}							
					}
				}					
			}
		}		
		
		
		// output element run text
		protected function outputElementFootnote( &$elements )
		{
			foreach( $elements as $element )
			{
				// Text
				if( get_class( $element ) == "PhpOffice\PhpWord\Element\Text" )
				{
					$text = $element->getText();			
					if( !$text )
						continue;
							
					echo "FOOTNOTE TEXT: " . $text . "\n";		
				}
				else
				// ListItem
				if( get_class( $element ) == "PhpOffice\PhpWord\Element\ListItem" )
				{
					$textObj = $element->getTextObject();
					if( !$textObj )
						continue;
					
					$text = $textObj->getText();
					if( !$text )
						continue;
							
					echo "FOOTNOTE LIST ITEM: " . $text . "\n";		
				}		
				else					
				// Link
				if( get_class( $element ) == "PhpOffice\PhpWord\Element\Link" )
				{
					$text = $element->getText();		
					if( !$text )
						continue;
					
					echo "FOOTNOTE LINK: " . $text . "\n";							
				}		
				else
				// Image
				if( get_class( $element ) == "PhpOffice\PhpWord\Element\Image" )
				{
					echo "FOOTNOTE IMAGE\n";						
				}					
				else
				// PreserveText
				if( get_class( $element ) == "PhpOffice\PhpWord\Element\PreserveText" )
				{
					$text = $element->getText();
					if( is_array( $text ) )
						$text = $this->arrayToString( $text );	
							
					echo "FOOTNOTE PRESERVE TEXT: " . $text . "\n";		
				}					
				else
				// unknown type				
				{
					echo "Footnote: Unprocessed: " . get_class( $element ) . "\n";
				}
			}
			
			return $result;
		}		
		
	};


?>