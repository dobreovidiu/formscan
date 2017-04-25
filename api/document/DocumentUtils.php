<?php
	
	// Document Utils - utility functions for document conversion.
	
	
	// DocumentUtils
	class DocumentUtils
	{
		
		// convert text
		static public function convertText( $text )
		{
			$convert = "";
			
			// replace characters
			$i = 0;
			$len = strlen($text);
			while( $i < $len )
			{
				// MS '
				if( ( $i + 2 ) < $len && ord( $text[$i] ) == 226 && ord( $text[$i+1] ) == 128 && ord( $text[$i+2] ) == 152 )
				{
					$convert .= "'";
					$i += 3;
					continue;
				}
				
				// MS '
				if( ( $i + 2 ) < $len && ord( $text[$i] ) == 226 && ord( $text[$i+1] ) == 128 && ord( $text[$i+2] ) == 153 )
				{
					$convert .= "'";
					$i += 3;
					continue;
				}

				// MS "
				if( ( $i + 2 ) < $len && ord( $text[$i] ) == 226 && ord( $text[$i+1] ) == 128 && ord( $text[$i+2] ) == 156 )
				{
					$convert .= "'";
					$i += 3;
					continue;
				}

				// MS "
				if( ( $i + 2 ) < $len && ord( $text[$i] ) == 226 && ord( $text[$i+1] ) == 128 && ord( $text[$i+2] ) == 157 )
				{
					$convert .= "'";
					$i += 3;
					continue;
				}
				
				// MS -
				if( ( $i + 2 ) < $len && ord( $text[$i] ) == 226 && ord( $text[$i+1] ) == 128 && ord( $text[$i+2] ) == 147 )
				{
					$convert .= "-";
					$i += 3;
					continue;
				}
				
				// MS -
				if( ( $i + 2 ) < $len && ord( $text[$i] ) == 226 && ord( $text[$i+1] ) == 128 && ord( $text[$i+2] ) == 148 )
				{
					$convert .= "-";
					$i += 3;
					continue;
				}
				
				// MS -
				if( ( $i + 2 ) < $len && ord( $text[$i] ) == 226 && ord( $text[$i+1] ) == 128 && ord( $text[$i+2] ) == 162 )
				{
					$convert .= "-";
					$i += 3;
					continue;
				}
				
				// MS ...
				if( ( $i + 2 ) < $len && ord( $text[$i] ) == 226 && ord( $text[$i+1] ) == 128 && ord( $text[$i+2] ) == 166 )
				{
					$convert .= "...";
					$i += 3;
					continue;
				}				
				
				// MS Euro
				if( ( $i + 2 ) < $len && ord( $text[$i] ) == 226 && ord( $text[$i+1] ) == 130 && ord( $text[$i+2] ) == 172 )
				{
					$convert .= "€";
					$i += 3;
					continue;
				}
				
				// MS "
				if( ( $i + 1 ) < $len && ord( $text[$i] ) == 226 && ord( $text[$i+1] ) == 128 )
				{
					$convert .= "\"";
					$i += 2;
					continue;
				}				
				
				// MS space
				if( ( $i + 1 ) < $len && ord( $text[$i] ) == 194 && ord( $text[$i+1] ) == 160 )
				{
					$convert .= "\"";
					$i += 2;
					continue;
				}
				
				$convert .= $text[$i];
				$i++;
			}
			
			return $convert;
		}		

		
		// is valid text
		static public function isValidText( $text )
		{
			$text = trim( $text );
			if( strlen( $text ) == 1 )
				return true;
				
			if( $text == "<br>" )
				return false;
		
			$text = str_replace( ".", "", $text );
			$text = str_replace( "_", "", $text );
			$text = str_replace( ";", "", $text );
			$text = str_replace( ":", "", $text );			
			$text = str_replace( "(", "", $text );
			$text = str_replace( ")", "", $text );
			$text = str_replace( "|", "", $text );
			$text = str_replace( " ", "", $text );
			
			if( $text == "" )
				return false;	
				
			return true;
		}
		
		
		// whether header element text
		static public function isHeaderElementText( $text )
		{
			$text = trim( $text );
			
			// SECTION A
			@preg_match( '/SECTION ([A-Z]{1,1})([ ])/', $text, $matches, PREG_OFFSET_CAPTURE );
			if( $matches && count( $matches ) > 0 && ( count( $matches[0] ) == 2 ) )
			{
				$result = $matches[0][0];
				$pos = stripos( $text, $result );
				if( !is_bool( $pos ) && ( $pos == 0 ) )
					return true;
			}
			
			// SECTION 1
			@preg_match( '/SECTION ([0-9]{1,2})([ ])/', $text, $matches, PREG_OFFSET_CAPTURE );
			if( $matches && count( $matches ) > 0 && ( count( $matches[0] ) == 2 ) )
			{
				$result = $matches[0][0];
				$pos = stripos( $text, $result );
				if( !is_bool( $pos ) && ( $pos == 0 ) )
					return true;
			}
			
			return false;
		}
		
		
		// cleanup width		
		static public function cleanupWidth( $width )
		{
			$width = str_ireplace( "pt", "", $width );
			$width = str_ireplace( "px", "", $width );			
			
			$width = floatval( $width );
			
			return $width;
		}
		
		
		// normalize title from filename
		static public function normalizeFilenameTitle( $filename )
		{
			$pos = stripos( $filename, "." );
			if( !is_bool( $pos ) )
				$filename = substr( $filename, 0, $pos );
			
			$filename = str_replace( "-", " ", $filename );
			$filename = str_replace( "_", " ", $filename );
			
			$filename .= " " .rand();			
			
			return $filename;
		}
		
		
		// cleanup keyword value
		static public function cleanupKeywordValue( $value )
		{
			$value = rtrim( $value, ":( " );
			$value = ltrim( $value, "0123456789.* " );
			$value = str_replace( " / ", "/", $value );
			$value = str_replace( " /", "/", $value );						
			$value = str_replace( "/ ", "/", $value );		

			return $value;
		}
		
		
		// is string empty
		static public function isStringEmpty( $value )
		{
			$value = str_replace( " ", "", $value );
			$value = str_replace( "_", "", $value );			
			$value = str_replace( "(", "", $value );
			$value = str_replace( ")", "", $value );			
			$value = str_replace( "\n", "", $value );
			$value = str_replace( "\r", "", $value );			

			if( strlen( $value ) <= 0 )
				return true;
			
			return false;
		}
		
		
		// normalize text
		static public function normalizeText( &$text )
		{		
			$text = mb_convert_encoding( $text, "HTML-ENTITIES", "UTF-8" );
			
			$specialChars = array( 
									array( "&lsquo;", 		"'" ),
									array( "&rsquo;",		"'" ),
									array( "&sbquo;", 		"'" ),
									array( "&ldquo;", 		"\"" ),
									array( "&rdquo;", 		"\"" ),
									array( "&bdquo;", 		"\"" ),
									array( "&lsaquo;", 		"<" ),
									array( "&rsaquo;", 		">" ),
									array( "&quot;", 		"\"" ),
									array( "&amp;", 		"&" ),
									array( "&frasl;", 		"/" ),
									array( "&lt;", 			"<" ),
									array( "&gt;", 			">" ),
									array( "&hellip;", 		"..." ),
									array( "&ndash;", 		"-" ),
									array( "&mdash;", 		"-" ),
									array( "&nbsp;", 		" " ),
									array( "&Agrave;", 		"A" ),
									array( "&Aacute;", 		"A" ),
									array( "&Acirc;", 		"A" ),
									array( "&Atilde;", 		"A" ),
									array( "&Auml;", 		"A" ),
									array( "&Aring;", 		"A" ),									
									array( "&AElig;", 		"E" ),										
									array( "&Ccedil;", 		"C" ),	
									array( "&Egrave;", 		"E" ),	
									array( "&Eacute;", 		"E" ),	
									array( "&Ecirc;", 		"E" ),	
									array( "&Euml;", 		"E" ),										
									array( "&Igrave;", 		"I" ),
									array( "&Iacute;", 		"I" ),
									array( "&Icirc;", 		"I" ),
									array( "&Iuml;", 		"I" ),
									array( "&ETH;", 		"D" ),
									array( "&Ntilde;", 		"N" ),									
									array( "&Ograve;", 		"O" ),
									array( "&Oacute;", 		"O" ),
									array( "&Ocirc;", 		"O" ),
									array( "&Otilde;", 		"O" ),
									array( "&Ouml;", 		"O" ),
									array( "&times;", 		"x" ),			
									array( "&Ugrave;", 		"U" ),	
									array( "&Uacute;", 		"U" ),	
									array( "&Ucirc;", 		"U" ),	
									array( "&Uuml;", 		"U" ),	
									array( "&Yacute;", 		"Y" ),	
									array( "&szlig;", 		"ss" ),	
									array( "&agrave;", 		"a" ),	
									array( "&aacute;", 		"a" ),	
									array( "&acirc;", 		"a" ),	
									array( "&atilde;", 		"a" ),	
									array( "&auml;", 		"a" ),	
									array( "&aring;", 		"a" ),	
									array( "&aelig;", 		"e" ),	
									array( "&ccedil;", 		"c" ),	
									array( "&egrave;", 		"e" ),	
									array( "&eacute;", 		"e" ),	
									array( "&ecirc;", 		"e" ),	
									array( "&euml;", 		"e" ),	
									array( "&igrave;", 		"i" ),
									array( "&iacute;", 		"i" ),	
									array( "&icirc;", 		"i" ),	
									array( "&iuml;", 		"i" ),
									array( "&eth;", 		"d" ),	
									array( "&ntilde;", 		"n" ),	
									array( "&ograve;", 		"o" ),
									array( "&oacute;", 		"o" ),	
									array( "&ocirc;", 		"o" ),	
									array( "&otilde;", 		"o" ),									
									array( "&ouml;", 		"o" ),
									array( "&ugrave;", 		"u" ),	
									array( "&uacute;", 		"u" ),	
									array( "&ucirc;", 		"u" ),	
									array( "&uuml;", 		"u" ),
									array( "&yacute;", 		"y" ),	
									array( "&yuml;", 		"y" ),
									array( "&thinsp;", 		" " ),
									array( "&ensp;", 		" " ),
									array( "&emsp;", 		" " )							
								 );
			
			// replace special characters
			foreach( $specialChars as $char )
				$text = str_ireplace( $char[0], $char[1], $text );
		}
			
		
		// log analysis status		
		static public function logAnalysisStatus( $text )
		{
			$text = "<b>" . $text . "</b>";
			
			self::logAnalysis( $text );
		}
		
		
		// log analysis
		static public function logAnalysis( $text )
		{
			global $docAnalysis;
			global $showConversionLogs;
			global $documentID;
			
			// init log
			if( !isset( $docAnalysis ) )
				$docAnalysis = array();

			// add log
			array_push( $docAnalysis, $text );
			
			// show logs
			if( isset( $showConversionLogs ) && $showConversionLogs == 1 )
				echo $text . "\n";
			
			// add analysis to db
			if( isset( $documentID ) && !is_bool( $documentID ) )
				ApiDb::documentAnalysisTableAdd( $documentID, $text, 2 );
		}
		
		
		// log file
		static public function logFile( $path, $data )
		{
			@file_put_contents( "log/documents/" . $path . "-" . date( "YmdHis", time() ) . "_" . rand() . ".log", print_r( $data, 1 ) );				
		}
		
	};
	
	
?>