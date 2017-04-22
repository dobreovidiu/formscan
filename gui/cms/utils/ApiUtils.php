<?php
	
	// API utils.
	
	
	// ApiUtils class
	class ApiUtils
	{
	
	
		// get page
		static public function getPage( $url, &$httpCode )
		{
			// allocate curl
			$curl = curl_init( $url );
			
			// initialize curl
			curl_setopt($curl, CURLOPT_HEADER, 				0);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 		1);
			curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 		1);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 		0);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 		0);
			curl_setopt($curl, CURLINFO_HEADER_OUT, 		true);
				
			// get data
			$page = curl_exec($curl);
			//echo "PAGE: " . $page;
			
			$headerSent = curl_getinfo($curl, CURLINFO_HEADER_OUT ); 		
			//var_dump($headerSent);
			
			$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE ); 		
			//var_dump(httpCode);
			
			if( is_bool($page) )
			{
				curl_close($curl);
				return false;
			}
			
			// close curl
			curl_close($curl);
			
			return $page;
		}	
	
	
		// post page
		static public function postPage( $url, $postData, &$httpCode )
		{
			// allocate curl
			$curl = curl_init( $url );
			
			// initialize curl
			curl_setopt($curl, CURLOPT_HEADER, 				0);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 		1);
			curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 		1);			
			curl_setopt($curl, CURLOPT_POST, 				1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, 			$postData);			
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 		0);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 		0);			
			curl_setopt($curl, CURLINFO_HEADER_OUT, 		true);
				
			// get data
			$page = curl_exec($curl);
			//echo "PAGE: " . $page;
			
			$headerSent = curl_getinfo($curl, CURLINFO_HEADER_OUT ); 		
			//var_dump($headerSent);
			
			$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE ); 		
			//var_dump(httpCode);
			
			if( is_bool($page) )
			{
				curl_close($curl);
				return false;
			}
			
			// close curl
			curl_close($curl);
			
			return $page;
		}	
	
	
		// put page
		static public function putPage( $url, $postData, &$httpCode )
		{
			// allocate curl
			$curl = curl_init( $url );
			
			// initialize curl
			curl_setopt($curl, CURLOPT_HEADER, 				0);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 		1);
			curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 		1);			
			curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 		"PUT");
			curl_setopt($curl, CURLOPT_POSTFIELDS, 			$postData);			
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 		0);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 		0);			
			curl_setopt($curl, CURLINFO_HEADER_OUT, 		true);
				
			// get data
			$page = curl_exec($curl);
			//echo "PAGE: " . $page;
			
			$headerSent = curl_getinfo($curl, CURLINFO_HEADER_OUT ); 		
			//var_dump($headerSent);
			
			$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE ); 		
			//var_dump(httpCode);
			
			if( is_bool($page) )
			{
				curl_close($curl);
				return false;
			}
			
			// close curl
			curl_close($curl);
			
			return $page;
		}	
		
		
		// delete page
		static public function deletePage( $url, &$httpCode )
		{
			return self::customPage( $url, "DELETE", $httpCode );
		}
		
	
		// custom page
		static public function customPage( $url, $method, &$httpCode )
		{
			// allocate curl
			$curl = curl_init( $url );
			
			// initialize curl
			curl_setopt($curl, CURLOPT_HEADER, 				0);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 		1);
			curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 		1);
			curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 		$method);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 		0);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 		0);				
			curl_setopt($curl, CURLINFO_HEADER_OUT, 		true);
			
			// get data
			$page = curl_exec($curl);
			//echo "PAGE: " . $page;
			
			$headerSent = curl_getinfo($curl, CURLINFO_HEADER_OUT ); 		
			//var_dump($headerSent);
			
			$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE ); 		
			//var_dump(httpCode);
			
			if( is_bool($page) )
			{
				curl_close($curl);
				return false;
			}
			
			// close curl
			curl_close($curl);
			
			return $page;
		}
		
		
		// get IP address
		static public function getIpAddress()
		{
			$ipAddress = "No IP Address";
		
			// get ip address
			if( isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != "" )
				$ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
			else
			if( isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] != "" )
				$ipAddress = $_SERVER['REMOTE_ADDR'];

			return $ipAddress;
		}

		
	};


?>