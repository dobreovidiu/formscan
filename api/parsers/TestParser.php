<?php

	// Test Parser - parser for .test files


	// TestParser
	class TestParser
	{
		// globals
		protected $document	= false;
		
		
		// parse
		public function parse( $filename, $filepath )
		{			
			// create document
			$this->document = new Document();
			$this->document->setTitle( "Test App " . date( "Y-m-d H:i:s", time() ) );
			
			// add section 1
			$section1 = new DocumentSection();
			$section1->setName( "Personal Information" );
			
			// fields
			$section1->addField( "Cool Label", DocumentSectionField::LABEL );			
			$section1->addField( "Text Input", DocumentSectionField::INPUTTEXT );
			$section1->addField( "Numeric1 Input", DocumentSectionField::NUMERIC );
			$section1->addField( "Numeric2 Input", DocumentSectionField::NUMERIC );		
			$section1->addField( "Multiline Input", DocumentSectionField::TEXTBOX );
			$section1->addField( "Single Select", DocumentSectionField::SINGLESELECT, "choice1\nchoice2\nchoice3\nchoice4", "choice2" );
			$section1->addField( "Multi Select", DocumentSectionField::MULTISELECT, "mchoice1\nmchoice2\nmchoice3\nmchoice4" );
			$section1->addField( "Boolean Select", DocumentSectionField::BOOLEAN, "Da|Nu" );
			$section1->addField( "Trilean Select", DocumentSectionField::TRILEAN, "Da|Nu|Nu stiu" );
			$section1->addField( "Link Input", DocumentSectionField::WEBVIEWER, "http://www.cnn.com" );					
			$section1->addField( "Date Time Input", DocumentSectionField::DATETIME );	
			$section1->addField( "Date Input", DocumentSectionField::DATEONLY );	
			$section1->addField( "Time Input", DocumentSectionField::TIMEONLY );				
			$section1->addField( "Signature Input", DocumentSectionField::SIGNATURE );				
			$section1->addField( "Barcode Input", DocumentSectionField::BARCODE );	
			
			$this->document->addSection( $section1 );
			
			return $this->document;
		}
		
	};


?>