<?php

	// Pdf Parser - parser for .pdf files


	// PdfParser
	class PdfParser
	{
		// globals
		protected $document	= false;
		
		
		// parse
		public function parse( $filename, $filepath )
		{
			global $viewParserOutput;
			
			
			// CREATE READER
			$pdf = $this->getParserOutput( $filepath );
			if( is_bool( $pdf ) )
			{
				ApiLogging::logError( "[PdfParser::parse] Failed to parse file " . $filename );
				return false;
			}		
			
			// logging
			if( isset( $viewParserOutput ) && $viewParserOutput )
				$this->outputParser( $pdf );
			
			DocumentUtils::logFile( "pdf-parser", $pdf );
			
			
			// INITIALIZE DOCUMENT
			$this->document = new Document();
			
			
			// GET CONTENT
			$this->parseContent( $pdf );
			
			
			// logging
			DocumentUtils::logFile( "pdf-result", $this->document->outputRows() );
			
			return $this->document;			
		}
		
		
		// parse content
		protected function parseContent( &$pdf )
		{
			// init sections
			$sections 		= array();
			$curSection		= false;		
			$rowID			= 1;		
			$pageIdx		= 1;

			// traverse pages
			foreach( $pdf["pages"] as $page )
			{
				// parse page
				$data = $this->parsePage( $pageIdx++, $page );
				if( is_bool( $data ) )
					continue;
				
				// process page
				$this->processPage( $data, $sections, $curSection, $rowID );
			}
			
			// add last section
			if( !is_bool( $curSection ) )
				array_push( $sections, $curSection );
			
			// build document
			$this->document->buildFromArray( $sections );				
			
			return true;
		}
		
		
		// parse page
		protected function parsePage( $idx, &$filepath )
		{
			// load file
			$page = @file_get_contents( $filepath );
			if( is_bool( $page ) )
				return false;
			
			// logging
			DocumentUtils::logFile( "pdf-page" . $idx, $page );			
			
			// normalize text
			DocumentUtils::normalizeText( $page );					
			
			// init
			$data 	= array();
			$pos 	= 0;
			$len 	= strlen( $page );
			
			// traverse page
			while(1)
			{
				// div
				$startDiv = stripos( $page, "<div", $pos );
				if( is_bool( $startDiv ) )
					break;
				
				$endDiv = stripos( $page, "</div", $startDiv );
				if( is_bool( $endDiv ) )
					break;		

				$chunk = substr( $page, $startDiv, $endDiv - $startDiv );
				
				
				// left
				$pos = stripos( $chunk, "left:" );
				if( is_bool( $pos ) )
				{
					$pos = $endDiv + 5;
					continue;
				}
				
				$endPos = stripos( $chunk, ";", $pos );
				if( is_bool( $endPos ) )
				{
					$pos = $endDiv + 5;
					continue;
				}
				
				$left = trim( substr( $chunk, $pos + 5, $endPos - $pos - 5 ) );
				$left = intval( str_ireplace( "px", "", $left ) );
				
				
				// top
				$pos = stripos( $chunk, "top:" );
				if( is_bool( $pos ) )
				{
					$pos = $endDiv + 5;
					continue;
				}
				
				$endPos = stripos( $chunk, ";", $pos );
				if( is_bool( $endPos ) )
				{
					$pos = $endDiv + 5;
					continue;
				}
				
				$top = trim( substr( $chunk, $pos + 4, $endPos - $pos - 4 ) );
				$top = intval( str_ireplace( "px", "", $top ) );
				
				
				// text
				$pos = stripos( $chunk, "<span" );
				if( is_bool( $pos ) )
				{
					$pos = $endDiv + 5;
					continue;
				}
				
				$pos = stripos( $chunk, ">", $pos );
				if( is_bool( $pos ) )
				{
					$pos = $endDiv + 5;
					continue;
				}
				
				$endPos = stripos( $chunk, "</span", $pos );
				if( is_bool( $endPos ) )
				{
					$pos = $endDiv + 5;
					continue;
				}
				
				$text = trim( substr( $chunk, $pos + 1, $endPos - $pos - 1 ) );		

				// set pos
				$pos = array( "x" => $left, "y" => $top );
				
				// add text
				array_push( $data, array( "text" => $text, "pos" => $pos ) );
				
				// go to next text
				$pos = $endDiv + 5;
			}
			
			// sort by position
			usort( $data, "PdfParser::sortText" );
			
			//var_dump($data);
			
			return $data;
		}
		
		
		// sort text by position
		static protected function sortText( $a, $b )
		{
			if( abs( $a["pos"]["y"] - $b["pos"]["y"] ) > 10 )
			{
				if( $a["pos"]["y"] < $b["pos"]["y"] )
					return -1;
				
				if( $a["pos"]["y"] > $b["pos"]["y"] )
					return 1;		
			}
			
			if( abs( $a["pos"]["x"] - $b["pos"]["x"] ) > 10 )
			{			
				if( $a["pos"]["x"] < $b["pos"]["x"] )
					return -1;
				
				if( $a["pos"]["x"] > $b["pos"]["x"] )
					return 1;
			}

			return 0;
		}
		
		
		// process page
		protected function processPage( &$page, &$sections, &$curSection, &$rowID )
		{
			$curRow		= false;	
			$colID		= 1;
			$itemY		= false;
			
			// GET CONTENT
			foreach( $page as $item )
			{
				if( strlen( $item["text"] ) <= 0 )
					continue;
				
				// new/different row
				if( is_bool( $itemY ) || ( $itemY != $item["pos"]["y"] && abs( $itemY - $item["pos"]["y"] ) > 10 ) )
				{
					$itemY = $item["pos"]["y"];
					
					// add existing row
					if( !is_bool( $curRow ) )
					{
						$curSection["rows"][$rowID] = $curRow;
						$rowID++;
					}
					
					// create row			
					$curRow 	= array();	
					$colID		= 1;
				}
				
				// append text
				array_push( $curRow, array( $colID, $item["text"] ) );
				$colID++;
			}	
			
			// add existing row
			if( !is_bool( $curRow ) )
			{
				$curSection["rows"][$rowID] = $curRow;
				$rowID++;
			}
			
			return true;
		}
		
		
		// get parser output
		protected function getParserOutput( $filepath )
		{
			global $osType;
			
			// linux
			if( strtolower( $osType ) == "linux" )
			{
				$folder = "storage/documents/" . date( "YmdHis", time() ) . "_" . rand();
				
				$cmd = "./lib/XPdf/bin64/pdftohtml " . $filepath . " " . $folder;
				
				@passthru( $cmd );
			}
			else
			// other
			{
				$folder = "../test/pdf1";
			}
			
			// error
			if( !@file_exists( $folder ) )
			{
				ApiLogging::logError( "[PdfParser::getParserOutput] Failed to run pdftohtml" );
				return false;
			}
			
			// get parser files
			$pages = $this->getParserFiles( $folder );
			if( is_bool( $pages ) )
			{
				ApiLogging::logError( "[PdfParser::getParserOutput] Failed to get parser result files" );
				return false;
			}
			
			// no result
			if( empty( $pages ) )
			{
				ApiLogging::logError( "[PdfParser::getParserOutput] No result page found" );
				return false;
			}
			
			$pdf = array( "pages" => $pages );
			
			return $pdf;
		}
		
		
		// get parser files
		protected function getParserFiles( $folder )
		{
			// get files
			$files = @scandir( $folder );
			if( is_bool( $files ) )
				return false;
			
			$pages = array();
				
			// filter files
			$no = count( $files );
			for( $i = 0; $i < $no; $i++ )
			{
				$file = $files[$i];
				
				// ignore .
				if( $file == "." || $file == ".." )
					continue;
					
				$filePath = $folder . "/" . $file;
				
				// sub-folder
				if( is_dir( $filePath ) )
					continue;
				
				// page file
				if( is_bool( stripos( $filePath, "page" ) ) || is_bool( stripos( $filePath, ".html" ) ) )
					continue;
				
				// add file
				array_push( $pages, $filePath );
			}
			
			return $pages;
		}
		
		
		// output parser
		protected function outputParser( &$pdf )
		{
			echo "Parser Output: \n\n";

			echo print_r( $pdf, 1 );
		}
		
	};


?>