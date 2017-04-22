<?php

	// Fat Finger Manager - Fat Finger Manager Functions.


	// FatFingerManager
	class FatFingerManager
	{
		
		
		// create app
		static public function createApp( &$document )
		{			
			global $viewParserOutput;
			global $fatFingerEnabled;
			
			// logging
			DocumentUtils::logAnalysis( "Converting document form to Fat Finger elements" );
			
			// build request
			$request = self::buildRequest( $document );
			if( is_bool( $request ) )
			{
				ApiLogging::logError( "[FatFingerManager::createApp] Failed to build Fat Finger request" );
				return false;
			}
			
			// logging
			if( isset( $viewParserOutput ) && $viewParserOutput )
				self::outputParser( $request );
			
			FatFingerUtils::logFile( "fatfinger-request", $request->serialize() );
			
			// API disabled
			if( !$fatFingerEnabled )
				return true;
			
			// logging
			DocumentUtils::logAnalysis( "Conversion completed" );
			DocumentUtils::logAnalysis( "Logging into Fat Finger account" );
			
			// login
			if( !FatFingerApi::login() )
			{
				ApiLogging::logError( "[FatFingerManager::createApp] Failed to login to Fat Finger" );
				return false;
			}
			
			// logging
			DocumentUtils::logAnalysis( "Logged in successfully" );
			DocumentUtils::logAnalysis( "Creating Fat Finger app" );
			
			// create app
			$appId = FatFingerApi::createApp( $request );
			if( is_bool( $appId ) )
			{
				ApiLogging::logError( "[FatFingerManager::createApp] Failed to create Fat Finger app" );
				return false;
			}
			
			// logging
			DocumentUtils::logAnalysis( "Fat Finger app created successfully" );
			DocumentUtils::logAnalysis( "Fat Finger app id: " . $appId );			
			DocumentUtils::logAnalysis( "Publishing Fat Finger app" );			
			
			// create app
			if( !FatFingerApi::publishApp( $appId ) )
			{
				ApiLogging::logError( "[FatFingerManager::createApp] Failed to create Fat Finger app" );
				return false;
			}
			
			// logging
			DocumentUtils::logAnalysis( "Fat Finger app published successfully" );
			DocumentUtils::logAnalysis( "Done!" );
			
			return true;
		}
		
		
		// build request
		static protected function buildRequest( &$document )
		{
			global $fatFingerFormUrl;
			global $fatFingerFormFieldUrl;						
			
			// initialize request
			$request = new FatFingerRequest();
			
			
			// intialize form
			$form = new FatFingerForm();
			$form->setFormName( $document->getTitle() );
			$form->setAiLogic( "true|1|day|Oj191qfO3ECrQ-zu_EfXvw2|true" );
			
			
			$order = 1;
			
			// get document sections
			$sections = $document->getSections();
			
			// add default section
			$defaultSection = self::createDefaultSection();
			array_unshift( $sections, $defaultSection );
			
			$sectionIdx = 1;
			
			// traverse sections
			foreach( $sections as $section )
			{
				$typeLogic = "";
				
				// section name
				$sectionName = $section->getName();
				if( empty( $sectionName ) )
				{
					if( $sectionIdx == 1 )
					{
						$sectionName 	= "";
						$typeLogic		= "Invisible";
					}
					else
					{
						$sectionName = "Section " . ( $sectionIdx - 1 );
					}
				}
				
				$sectionIdx++;
				
				// initialize section
				$fatSection = new FatFingerSection();
			
				// generate section id
				$sectionId = FatFingerUtils::generateUniqueId();
							   
				$fatSection->setId( $sectionId );
				$fatSection->setOrder( $order++ );				
				$fatSection->setLabel( $sectionName );
				$fatSection->setTypeLogic( $typeLogic );
				
				
				// get section fields
				$fields = $section->getFields();
			
				// traverse section fields
				foreach( $fields as $field )
				{
					$typeLogic 		= "";
					$extraFields	= array();
					
					// map field
					$fieldType = FatFingerUtils::mapSectionField( $field->getType(), $field->getAllowedValues(), $field->getValue(), $typeLogic, $extraFields );
					if( is_bool( $fieldType ) )
					{
						ApiLogging::logError( "[FatFingerManager::buildRequest] Unknown field type: " . $field->getType() );
						return false;
					}
					
					// initialize field
					$fatField = new FatFingerSectionField();
				
					// generate field id
					$fieldId = FatFingerUtils::generateUniqueId();
					
					$fatField->setFieldType( $fieldType );
					$fatField->setValidation( 0 );
					$fatField->setTypeLogic( $typeLogic );					
					$fatField->setId( $fieldId );
					$fatField->setOrder( $order++ );
					$fatField->setLabel( $field->getLabel() );
					$fatField->setDisableSort( $field->getDisableSort() );
					$fatField->setDisableEdit( $field->getDisableEdit() );
					$fatField->setDisableDelete( $field->getDisableDelete() );
					$fatField->setLocked( $field->getLocked() );
					$fatField->setFieldSecurity( $field->getSecurity() );
					$fatField->setExtraFields( $extraFields );
					
					// add field to section
					$fatSection->addField( $fatField );
				}
				
				// add section to form
				$form->addSection( $fatSection );
			}
			
			// set request form
			$request->setForm( $form );
			
			return $request;
		}
		
		
		// add default section
		static protected function createDefaultSection()
		{
			$section = new DocumentSection();
			
			// add app title								
			$section->addField( "Enter Title", DocumentSectionField::INPUTTEXT, "", false, true, -1, true, true, array( array( "userRole" => "", "userAccess" => "", "isDeleted" => false ) ) );
			
			// add location					
			$section->addField( "Location", DocumentSectionField::LOCATION, "", false, true, true, true, true );
			
			return $section;
		}
		
		
		// output parser
		static protected function outputParser( &$request )
		{
			echo "Fat Finger Request: \n\n";

			echo print_r( $request->serialize(), 1 );
		}
		
	};


?>