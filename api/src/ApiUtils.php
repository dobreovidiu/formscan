<?php
	
	// API utils - HTTP requests and other misc functions.
	
	
	// ApiUtils class
	class ApiUtils
	{
		// globals
		const USER_AGENT = "Mozilla/5.0 (Windows NT 6.3; WOW64; rv:52.0) Gecko/20100101 Firefox/52.0";
		
		
		
		// GET page
		static public function getPage( $url, $headers, &$httpCode )
		{
			global $userAgentList;
			
			$userAgent = self::USER_AGENT;
		
			// allocate curl
			$curl = curl_init( $url );
			
			// initialize curl
			curl_setopt($curl, CURLOPT_HEADER, 				0);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 		1);
			curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 		1);
			curl_setopt($curl, CURLOPT_TIMEOUT, 			120 );
			curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 		30 );			
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 		0);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 		0);
			curl_setopt($curl, CURLOPT_USERAGENT, 			$userAgent);			
			curl_setopt($curl, CURLINFO_HEADER_OUT, 		true);
			
			if( !empty( $headers ) )
				curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
				
			// get data
			$page = @curl_exec( $curl );
			
			// get headers sent
			$headerSent = curl_getinfo($curl, CURLINFO_HEADER_OUT ); 		
			
			// get returned HTTP code
			$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE ); 		
			
			// failed request
			if( is_bool( $page ) )
			{
				curl_close( $curl );
				return false;
			}
			
			// close curl
			@curl_close($curl);
			
			return $page;
		}	
		
		
		// GET page (parameters extended)
		static public function getPageEx( $url, $userAgent, $proxyIp, $proxyPort, $proxyAuth, &$isTimeout, &$isProxyError, 
										  $isHeader = 1, $referrer = "", $cookies = "", $isFollowLocation = 1, $customHeaders = array() )
		{
			global $userAgentList;

			if( is_bool( $userAgent ) || empty( $useAgent ) )
				$userAgent = $userAgentList[ rand( 0, count( $userAgentList ) - 1 ) ];
			
			$includeRange = true;
			
			// allocate curl
			$curl = curl_init( $url );
			
			// initialize curl
			curl_setopt( $curl, CURLOPT_HEADER, 				$isHeader );
			curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 		1 );
			curl_setopt( $curl, CURLOPT_FOLLOWLOCATION, 		$isFollowLocation );
			
			if( $referrer != "" )
				curl_setopt( $curl, CURLOPT_REFERER, 			$referrer );
				
			if( $cookies != "" )
				curl_setopt( $curl, CURLOPT_COOKIE, 			$cookies );
				
			if( $includeRange )
				curl_setopt( $curl, CURLOPT_RANGE, 				"0-524288" );
				
			curl_setopt( $curl, CURLOPT_TIMEOUT, 				120 );
			curl_setopt( $curl, CURLOPT_CONNECTTIMEOUT, 		30 );
			curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, 		0 );
			curl_setopt( $curl, CURLOPT_SSL_VERIFYHOST, 		0 );
			curl_setopt( $curl, CURLOPT_USERAGENT, 				$userAgent );			
				
			if( count( $customHeaders ) > 0 )
				curl_setopt( $curl, CURLOPT_HTTPHEADER, 		$customHeaders ); 
			
			// use proxy
			if( $proxyIp != "" )
			{
				curl_setopt( $curl, CURLOPT_PROXY, 				$proxyIp );
				curl_setopt( $curl, CURLOPT_PROXYPORT, 			$proxyPort );
				
				if( $proxyAuth != ":" && $proxyAuth != "" )
					curl_setopt( $curl, CURLOPT_PROXYUSERPWD, 	$proxyAuth );
			}
			
			// get data
			$page = curl_exec( $curl );
			
			// verify if timeout
			if( CURLE_OPERATION_TIMEOUTED == curl_errno( $curl ) )
				$isTimeout = 1;
			
			// close curl
			@curl_close( $curl );
			
			// gzdecode
			if( function_exists( "gzinflate" ) )
			{
				$pos = stripos( $page, "Content-Encoding: gzip" );
				if( !is_bool( $pos ) )
				{
					$pos = stripos( $page, "\r\n\r\n", $pos );
					if( !is_bool( $pos ) )
					{
						$converted = @gzinflate( substr( substr( $page, $pos + 4 ), 10, -8) );
						if( !is_bool( $converted ) )
							$page = $converted;					
					}
				}
			}
			
			return $page;
		}
		
	
		// POST page
		static public function postPage( $url, $postData, &$httpCode, &$headerSent )
		{
			global $userAgentList;
			
			$userAgent = "";
			if( isset( $userAgentList ) )
				$userAgent = $userAgentList[ rand( 0, count( $userAgentList ) - 1 ) ];
			
			// allocate curl
			$curl = curl_init( $url );
			
			// initialize curl
			curl_setopt( $curl, CURLOPT_HEADER, 				0);
			curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 		1);
			curl_setopt( $curl, CURLOPT_FOLLOWLOCATION, 		1);			
			curl_setopt( $curl, CURLOPT_POST, 					1);
			curl_setopt( $curl, CURLOPT_POSTFIELDS, 			$postData);			
			curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, 		0);
			curl_setopt( $curl, CURLOPT_SSL_VERIFYHOST, 		0);		
			curl_setopt( $curl, CURLOPT_USERAGENT, 				$userAgent);
			curl_setopt( $curl, CURLINFO_HEADER_OUT, 			true);
				
			// get data
			$page = @curl_exec( $curl );
			
			// get headers sent
			$headerSent = curl_getinfo( $curl, CURLINFO_HEADER_OUT ); 		
			
			// get returned HTTP code
			$httpCode = curl_getinfo( $curl, CURLINFO_HTTP_CODE ); 		

			// failed request
			if( is_bool( $page ) )
			{
				curl_close( $curl );
				return false;
			}
			
			// close curl
			@curl_close($curl);
			
			return $page;
		}	
		

		// POST page (paramters extended)
		static public function postPageEx( $url, $referrer, $userAgent, $proxyIp, $proxyPort, $proxyAuth, $postData, &$isTimeout, &$isProxyError, &$httpCode, &$headerSent,
										   $isHeader = 1, $contentType = "", $cookies = "", $followLocation = 0, $customHeaders = array(), $connectTimeout = 30, $timeout = 60 )
		{
			// allocate curl
			$curl = curl_init( $url );
			
			// initialize curl
			curl_setopt( $curl, CURLOPT_HEADER, 				$isHeader );
			curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 		1 );
			curl_setopt( $curl, CURLOPT_FOLLOWLOCATION, 		$followLocation );
			
			if( !empty( $referrer ) )
				curl_setopt( $curl, CURLOPT_REFERER, 			$referrer );
				
			curl_setopt( $curl, CURLOPT_TIMEOUT, 				$timeout );
			curl_setopt( $curl, CURLOPT_CONNECTTIMEOUT, 		$connectTimeout );
			curl_setopt( $curl, CURLOPT_POST, 					1 );
			curl_setopt( $curl, CURLOPT_POSTFIELDS, 			$postData );			
			curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, 		0 );
			curl_setopt( $curl, CURLOPT_SSL_VERIFYHOST, 		0 );
			curl_setopt( $curl, CURLOPT_USERAGENT, 				$userAgent );
				
			if( $cookies != "" )
				curl_setopt( $curl, CURLOPT_COOKIE, 			$cookies );
				
			if( !empty( $contentType ) )
				curl_setopt( $curl, CURLOPT_HTTPHEADER, 		array( 'Content-Type: ' . $contentType ) ); 
				
			if( count( $customHeaders ) > 0 )
				curl_setopt( $curl, CURLOPT_HTTPHEADER, 		$customHeaders ); 
			
			// use proxy
			if( $proxyIp != "" )
			{
				//curl_setopt($curl, CURLOPT_HTTPPROXYTUNNEL, 	true);
				curl_setopt( $curl, CURLOPT_PROXY, 				$proxyIp );
				curl_setopt( $curl, CURLOPT_PROXYPORT, 			$proxyPort );
				
				if( $proxyAuth != ":" && $proxyAuth != "" )
					curl_setopt( $curl, CURLOPT_PROXYUSERPWD, 	$proxyAuth );
			}
			
			// get data
			$page = curl_exec( $curl );
			
			// verify if time-out
			if( CURLE_OPERATION_TIMEOUTED == curl_errno( $curl ) )
				$isTimeout = 1;
			
			// get headers sent
			$headerSent = curl_getinfo( $curl, CURLINFO_HEADER_OUT );
			
			// get returned HTTP code
			$httpCode = curl_getinfo( $curl, CURLINFO_HTTP_CODE );
			
			// close curl
			curl_close( $curl );
			
			return $page;
		}
		
	
		// PUT page
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
			$page = @curl_exec( $curl );
			
			// get headers sent
			$headerSent = curl_getinfo($curl, CURLINFO_HEADER_OUT );
			
			// get returned HTTP code
			$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE );
			
			// failed request
			if( is_bool( $page ) )
			{
				curl_close( $curl );
				return false;
			}
			
			// close curl
			@curl_close($curl);
			
			return $page;
		}	
		
		
		// DELETE page
		static public function deletePage( $url, &$httpCode )
		{
			return self::customPage( $url, "DELETE", $httpCode );
		}
		
	
		// CUSTOM page request
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
			$page = @curl_exec($curl);
			
			// get headers sent			
			$headerSent = curl_getinfo($curl, CURLINFO_HEADER_OUT ); 		
			
			// get returned HTTP code			
			$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE ); 		
			
			// failed request			
			if( is_bool($page) )
			{
				curl_close($curl);
				return false;
			}
			
			// close curl
			@curl_close($curl);
			
			return $page;
		}
		
		
		// get all cookies
		static public function getAllCookies( &$page )
		{
			$cookies 	= "";
			$pos 		= 0;
			
			// get cookies
			while(1)
			{
				// get cookie
				$pos = stripos( $page, "Set-Cookie: ", $pos );
				if( is_bool( $pos ) )
					break;
					
				$endPos = stripos( $page, "\n", $pos );
				if( is_bool( $endPos ) )
					break;
					
				$cookie = trim( substr( $page, $pos + 12, $endPos - $pos - 12 ) );
				
				// keep only cookie value
				$posVal = stripos( $cookie, ";" );
				if( !is_bool( $posVal ) )
					$cookie = substr( $cookie, 0, $posVal );
					
				// append cookie
				if( $cookies != "" )
					$cookies .= "; ";
					
				$cookies .= $cookie;
				$pos = $endPos + 1;
			}
			
			return $cookies;			
		}
		
		
		// get form field
		static public function getFormField( &$page, $name, $separator = "\"" )
		{
			// name
			$pos = strpos( $page, "name=" . $separator . $name . $separator );
			if( is_bool($pos) )
				return false;
				
			$pos = strpos( $page, "value=" . $separator, $pos );
			if( is_bool($pos) )
				return false;

			$endPos = strpos( $page, $separator, $pos + 7 );
			if( is_bool($endPos) )
				return false;
				
			$val = substr( $page, $pos + 7, $endPos - $pos - 7 );
			
			return $val;
		}
		
		
		// get header field
		public function getHeaderField( &$page, $name )
		{
			// name
			$pos = strpos( $page, $name );
			if( is_bool($pos) )
				return false;
				
			$len = strlen( $name );

			$endPos = strpos( $page, "\r\n", $pos + $len );
			if( is_bool($endPos) )
				return false;
				
			$val = trim( substr( $page, $pos + $len, $endPos - $pos - $len ) );
			
			return $val;
		}
		
		
		// get url field
		public function getUrlField( $url, $name )
		{
			// name
			$pos = strpos( $url, $name . "=" );
			if( is_bool($pos) )
				return false;
				
			$pos += strlen($name) + 1;
				
			// value
			$pos2 = strpos( $url, "&", $pos );
			if( is_bool( $pos2 ) )
				$val = substr( $url, $pos );
			else
				$val = substr( $url, $pos, $pos2 - $pos );
				
			// url decode
			$val = urldecode( $val );
			
			return $val;
		}
		
	};


?>