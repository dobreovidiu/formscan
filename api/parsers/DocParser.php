<?php

	// Doc Parser - parser for .doc files


	// DocParser
	class DocParser
	{
		// globals
		protected $document = false;
		
		
		// parse
		public function parse( $filename, $filepath )
		{			
			global $viewParserOutput;
			
			
			// CREATE READER
			$word = $this->getParserOutput( $filepath );	
			if( is_bool( $word ) )
			{
				ApiLogging::logError( "[DocParser::parse] Failed to parse file " . $filename );
				return false;
			}
			
			// logging
			if( isset( $viewParserOutput ) && $viewParserOutput )
				$this->outputParser( $word );
						
			DocumentUtils::logFile( "doc-parser", $word );
			
			
			// INITIALIZE DOCUMENT
			$this->document = new Document();
			
			
			// GET TITLE
			if( isset( $word["title"] ) && strlen( $word["title"] ) > 0 )
				$this->document->setTitle( $word["title"] );
			else
			if( isset( $word["bookinfo"]["title"] ) && strlen( $word["bookinfo"]["title"] ) > 0 )
				$this->document->setTitle( $word["bookinfo"]["title"] );
		
		
			// GET CONTENT
			if( isset( $word["chapter"] ) )
			{
				if( isset( $word["chapter"]["para"] ) )
					$chapterList = array( $word["chapter"] );
				else
					$chapterList = $word["chapter"];	
				
				$sections 		= array();
				$curSection		= false;
				$curRow			= false;
				$rowID			= 1;
				 
				// traverse chapters
				foreach( $chapterList as $chapter )
				{
					$this->processChapter( $chapter, $curSection, $curRow, $rowID );
				}
			
				// add last section
				if( !is_bool( $curSection ) )
					array_push( $sections, $curSection );
				
				// build document
				$this->document->buildFromArray( $sections );					
			}			
			
			// logging
			DocumentUtils::logFile( "doc-result", $this->document->outputRows() );
		
			return $this->document;
		}
		
		
		// process chapter
		protected function processChapter( &$chapter, &$curSection, &$curRow, &$rowID )
		{
			if( !isset( $chapter["para"] ) )
				return false;

			// traverse paragraphs
			foreach( $chapter["para"] as $item )
			{
				// create section
				if( is_bool( $curSection ) )
					$curSection = array( "rows" => array() );
				
				// emphasis
				if( isset( $item["emphasis"] ) )					
				{
					$this->processChapterEmphasis( $item, $curSection, $curRow, $rowID );
				}
				else
				// table
				if( isset( $item["informaltable"] ) )
				{
					$this->processChapterTable( $item, $curSection, $curRow, $rowID );
				}	
				else
				// string
				if( is_string( $item ) )
				{
					$this->processChapterString( $item, $curSection, $curRow, $rowID );
				}					
				else
				// array
				if( is_array( $item ) )
				{
					$this->processChapterArray( $item, $curSection, $curRow, $rowID );					
				}					
			}
			
			return true;
		}
		
		
		// process chapter emphasis
		static protected function processChapterEmphasis( &$item, &$curSection, &$curRow, &$rowID )
		{
			// create row			
			$curRow = array();
			
			if( is_string( $item["emphasis"] ) )
				$emphasisList = array( $item["emphasis"] );
			else
				$emphasisList = $item["emphasis"];
					
			$colID = 1;
			
			// traverse emphasis
			foreach( $emphasisList as $emphasis )
			{					
				$entryVal = "";
				
				if( is_string( $emphasis ) )
				{										
					$entryVal = $emphasis;
				}
				else
				if( is_array( $emphasis ) )
				{
					foreach( $emphasis as $emphasisItem )
					{
						if( is_string( $emphasisItem ) )
							$entryVal .= $emphasisItem;							
					}										
				}
				
				// normalize text
				DocumentUtils::normalizeText( $entryVal );					
				
				// add row cell
				array_push( $curRow, array( $colID, $entryVal ) );	
				$colID++;					
			}
			
			// add row
			$curSection["rows"][$rowID] = $curRow;
			$curRow = false;
			$rowID++;
			
			return true;
		}
		
		
		// process chapter table
		static protected function processChapterTable( &$item, &$curSection, &$curRow, &$rowID )
		{			
			if( isset( $item["informaltable"]["tgroup"] ) )
				$tableList = array( $item["informaltable"] );
			else
				$tableList = $item["informaltable"];			
			
			// traverse table list
			foreach( $tableList as $table )
			{			
				if( isset( $table["tgroup"]["tbody"] ) )						
					$tgroupList = array( $table["tgroup"] );
				else
					$tgroupList = $table["tgroup"];
				
				// traverse tgroup list
				foreach( $tgroupList as $tgroup )
				{
					if( !isset( $tgroup["tbody"] ) )
						continue;
					
					$colWidths = array();
					
					// get column spec
					if( isset( $tgroup["colspec"] ) )
					{
						if( isset( $tgroup["colspec"]["@attributes"] ) )						
							$colList = array( $tgroup["colspec"] );
						else
							$colList = $tgroup["colspec"];
						
						foreach( $colList as $col )
						{
							if( !isset( $col["@attributes"] ) )
								continue;
							
							$colWidth = false;
							if( isset( $col["@attributes"]["colwidth"] ) )
								$colWidth = $col["@attributes"]["colwidth"];
							
							array_push( $colWidths, $colWidth );
						}
					}
					
					if( isset( $tgroup["tbody"]["row"]["entry"] ) )
						$rows = array( $tgroup["tbody"]["row"] );
					else
						$rows = $tgroup["tbody"]["row"];
					
					// traverse rows
					foreach( $rows as $row )
					{
						// create row			
						$curRow = array();
				
						if( is_string( $row["entry"] ) )
							$entries = array( $row["entry"] );
						else
							$entries = $row;
						
						$colID = 1;

						// traverse entries
						foreach( $entries as $entry )
						{
							if( is_string( $entry ) )
								$entries2 = array( $entry );
							else
								$entries2 = $entry;
							
							foreach( $entries2 as $entry2 )
							{
								// cell width
								$colWidth = false;
								if( $colID  <= count( $colWidths ) )
									$colWidth = $colWidths[ $colID - 1 ];
								
								$entryVal = "";
								
								if( is_string( $entry2 ) )
								{										
									$entryVal = $entry2;
								}
								else
								if( is_array( $entry2 ) )
								{
									foreach( $entry2 as $entry2Item )
									{
										if( is_string( $entry2Item ) )
											$entryVal .= $entry2Item;							
									}										
								}
					
								// normalize text
								DocumentUtils::normalizeText( $entryVal );	
					
								// add row cell
								array_push( $curRow, array( $colID, $entryVal, $colWidth ) );
								$colID++;									
							}
						}
						
						// add row
						$curSection["rows"][$rowID] = $curRow;
						$curRow = false;
						$rowID++;							
					}
				}
			}
			
			return true;
		}
		
		
		// process chapter string
		static protected function processChapterString( &$item, &$curSection, &$curRow, &$rowID )
		{
			// create row			
			$curRow = array();					
				
			// normalize text
			DocumentUtils::normalizeText( $item );	
				
			// add row cell
			array_push( $curRow, array( 1, $item ) );
			
			// add row
			$curSection["rows"][$rowID] = $curRow;
			$curRow = false;
			$rowID++;
			
			return true;
		}
		
		
		// process chapter array
		static protected function processChapterArray( &$item, &$curSection, &$curRow, &$rowID )
		{
			// create row			
			$curRow = array();
			
			$cellID = 1;

			// traverse items
			foreach( $item as $entry )
			{
				if( is_string( $entry ) )
				{						
					// normalize text
					DocumentUtils::normalizeText( $entry );	
			
					// add row cell
					array_push( $curRow, array( $cellID, $entry ) );
					$cellID++;
					continue;
				}							
			}
			
			// add row
			$curSection["rows"][$rowID] = $curRow;
			$curRow = false;
			$rowID++;
			
			return true;
		}
		
		
		// get parser output
		protected function getParserOutput( $filepath )
		{
			global $osType;
			
			// linux
			if( strtolower( $osType ) == "linux" )
			{
				$file = "storage/documents/" . date( "YmdHis", time() ) . "_" . rand() . ".xml";
				
				$cmd = "/root/bin/antiword -x db " . $filepath . " > " . $file;
				
				@passthru( $cmd );
			}
			else
			// other
			{
				$file = "../test/dtd4.xml";
			}
			
			// error
			if( !@file_exists( $file ) )
			{
				ApiLogging::logError( "[DocParser::getParserOutput] Failed to run antiword" );
				return false;
			}				
			
			// read XML file
			$data = @file_get_contents( $file );
			if( is_bool( $data ) || empty( $data ) )
			{
				ApiLogging::logError( "[DocParser::getParserOutput] Failed to read XML DTD data" );
				return false;
			}
			
			// parse XML
			$xml = @simplexml_load_string( $data );
			if( is_bool( $xml ) )
			{
				ApiLogging::logError( "[DocParser::getParserOutput] Failed to parse XML DTD data" );
				return false;
			}
			
			$json = @json_encode( $xml );
			if( is_bool( $json ) )
			{
				ApiLogging::logError( "[DocParser::getParserOutput] Failed to convert XML DTD data to JSON" );			
				return false;
			}

			$result = @json_decode( $json, true );
			if( is_bool( $result ) )
			{
				ApiLogging::logError( "[DocParser::getParserOutput] Failed to parse JSON data" );							
				return false;
			}
			
			return $result;
		}
		
		
		// output parser
		protected function outputParser( &$word )
		{
			echo "Parser Output: \n\n";

			echo print_r( $word, 1 );
		}
		
	};


?>