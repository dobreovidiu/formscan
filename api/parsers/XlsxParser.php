<?php

	// Xlsx Parser - parser for .xlsx files


	// XlsxParser
	class XlsxParser
	{
		
		// parse
		public function parse( $filename, $filepath )
		{
			$parser = new XlsParser();
			
			return $parser->parse( $filename, $filepath );
		}
		
	};


?>