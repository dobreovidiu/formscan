<?php

	// Fat Finger Response - structure of Fat Finger Response.


	// FatFingerResponse
	class FatFingerResponse
	{
		// globals
		protected $status 	= "";	
		

		
		//-- SETTERS				
		
		// set status
		public function setStatus( $status )
		{
			$this->status = $status;
		}
		

		
		
		//-- GETTERS				
		
		// get status
		public function getStatus()
		{
			return $this->status;
		}
		
	};


?>