<?php
	
	// Google Vision - utility function for Google Vision API (text extraction from images).
	
	
	// GoogleVision
	class GoogleVision
	{
		
		// extract text
		static public function extractText( &$image, &$fullText )
		{
			global $googleNLPKey;
						
			// encode image
			$image2 = @base64_encode( $image );
			
			// service URL
			$url = "https://vision.googleapis.com/v1/images:annotate?key=" . urlencode( $googleNLPKey );
			
			// POST data
			$postData = array( 	"requests" => array( array( "image" 	=> array( "content" => $image2 ),
			
															"features" 	=> array( array( "type" 		=> "TEXT_DETECTION", 
																						 "maxResults" 	=> 10 ) ) ) )
							 );
			
			
			// encode data
			$postData = @json_encode( $postData );
			
			$isTimeout 		= 0;
			$isProxyError 	= 0;
			
			// service request
			$response = ApiUtils::postPageEx( $url, "", ApiUtils::USER_AGENT, "", "", "", $postData, $isTimeout, $isProxyError, $httpCode, $headerSent, 
											  0, "application/json", "", 1, array(), 30, 600 );
									
			//var_dump($response);				
			//var_dump($headerSent);					
			
			if( is_bool( $response ) || empty( $response ) )
			{
				ApiLogging::logError( "[GoogleVision::extractText] No response from Google Vision API" );				
				return false;
			}
			
			//echo $response;
			
			// decode data
			$result = @json_decode( $response, true );
			if( is_bool( $result ) )
				return false;
			
			if( !isset( $result["responses"] ) )
				return false;
			
			$data 		= array();
			$fullText	= false;
			
			// get results
			foreach( $result["responses"] as $row )
			{
				if( !isset( $row["textAnnotations"] ) )
					continue;
				
				$idx = 0;
				
				// get annotations
				foreach( $row["textAnnotations"] as $item )
				{				
					if( !isset( $item["description"] ) )
						continue;
				
					$idx++;
					if( $idx <= 1 )
					{
						$fullText = $item["description"];
						continue;
					}
					
					// text
					$text = $item["description"];
					
					$pos = array( "x" => false, "y" => false, "width" => false );
					
					// position
					if( isset( $item["boundingPoly"] ) &&  isset( $item["boundingPoly"]["vertices"] ) &&
						is_array( $item["boundingPoly"]["vertices"] ) && count( $item["boundingPoly"]["vertices"] ) > 0 )
					{
						$vertice = $item["boundingPoly"]["vertices"][0];
						if( isset( $vertice["x"] ) && isset( $vertice["y"] ) )
						{
							// position
							$pos = array( "x" => $vertice["x"], "y" => $vertice["y"], "width" => false );
							
							// width
							if( count( $item["boundingPoly"]["vertices"] ) > 1 )
							{
								$vertice2 = $item["boundingPoly"]["vertices"][1];
								if( isset( $vertice2["x"] ) && isset( $vertice2["y"] ) )
									$pos["width"] = $vertice2["x"] - $vertice["x"];
							}
						}
					}
					
					if( is_bool( $pos["x"] ) || is_bool( $pos["y"] ) || is_bool( $pos["width"] ) )
						continue;
					
					// add text
					array_push( $data, array( "text" => $text, "pos" => $pos ) );
				}
			}
			
			// full text
			if( !is_bool( $fullText ) )
				$fullText = explode( "\n", $fullText );
			
			// sort by position
			usort( $data, "GoogleVision::sortText" );
			
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
		
	};
	
?>