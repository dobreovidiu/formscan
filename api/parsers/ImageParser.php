<?php

	// Image Parser - parser for image files (.jpg, .png)


	// ImageParser
	class ImageParser
	{
		// globals
		protected $document	= false;
		
		
		// parse
		public function parse( $filename, $filepath )
		{
			global $viewParserOutput;
			
			// READ IMAGE
			$image = @file_get_contents( $filepath );
			if( is_bool( $image ) )
			{
				ApiLogging::logError( "[ImageParser::parse] Failed to read file " . $filename );
				return false;
			}
			
			
			// CALL GOOGLE VISION API
			$result = GoogleVision::extractText( $image, $fullText );
			if( is_bool( $result ) )
			{
				ApiLogging::logError( "[ImageParser::parse] Failed to call Google Vision API for file " . $filename );
				return false;
			}
			
			$logResult = array_merge( array( "fulltext" => $fullText ), $result );
			
			
			// logging
			if( isset( $viewParserOutput ) && $viewParserOutput )
				$this->outputParser( $result );			
			
			DocumentUtils::logFile( "image-parser", $logResult );
			
			
			// INITIALIZE DOCUMENT
			$this->document = new Document();

			
			// PARSE CONTENT
			$this->parseContent( $result, $fullText );
			
			
			// logging
			DocumentUtils::logFile( "image-result", $this->document->outputRows() );
			
			return $this->document;
		}
		
		
		// parse content
		protected function parseContent( &$result, &$fullText )
		{
			$sections 		= array();
			$curSection		= false;
			$curRow			= false;
			$rowID			= 1;		
			$colID			= 1;
			$itemY			= false;
			$itemText		= "";
			$fullTextIdx	= 0;

			// GET CONTENT
			foreach( $result as $item )
			{
				if( strlen( $item["text"] ) <= 0 )
					continue;
				
				// verify if same text
				$isSameText = true;
				if( strlen( $itemText ) > 0 )
					$isSameText = $this->isSameTextFull( $fullText, $fullTextIdx, $itemText, $item["text"] );
				
				// new/different row
				if( is_bool( $itemY ) || ( $itemY != $item["pos"]["y"] && abs( $itemY - $item["pos"]["y"] ) > 10 ) || !$isSameText )
				{
					$itemY = $item["pos"]["y"];
					
					// add existing text
					if( strlen( $itemText ) > 0 )
						array_push( $curRow, array( $colID, $itemText ) );
					
					// add existing row
					if( !is_bool( $curRow ) )
					{
						$curSection["rows"][$rowID] = $curRow;
						$rowID++;
					}
			
					// create row			
					$curRow 	= array();
					$colID		= 1;
					$itemText	= "";
				}
				
				// append text
				if( strlen( $itemText ) > 0 )
					$itemText .= " ";
					
				$itemText .= $item["text"];
			}
			
			// add existing text
			if( strlen( $itemText ) > 0 )
				array_push( $curRow, array( $colID, $itemText ) );			
			
			// add existing row
			if( !is_bool( $curRow ) )
			{
				$curSection["rows"][$rowID] = $curRow;
				$rowID++;
			}
					
			// add last section
			if( !is_bool( $curSection ) )
				array_push( $sections, $curSection );
			
			// build document
			$this->document->buildFromArray( $sections );	

			return true;
		}
		
		
		// is same text
		protected function isSameTextFull( &$fullText, &$fullTextIdx, $text1, $text2 )
		{
			if( !is_array( $fullText ) )
				return true;
			
			// traverse full text
			$no = count( $fullText );
			for( $i = $fullTextIdx; $i < $no; $i++ )
			{		
				// text found
				if( 0 === stripos( $fullText[$i], $text1 ) )
				{
					$fullTextIdx = $i;
					
					// text addition not found							
					if( 0 !== stripos( $fullText[$i], $text1 . " " . $text2 ) )
						return false;
					
					return true;
				}
			}
			
			return true;
		}
		
		
		// output parser
		protected function outputParser( &$result )
		{
			echo "Parser Output: \n\n";

			echo print_r( $result, 1 );
		}
		
	};


?>