<?php
	
	// Api Db - database manipulations functions.
	
	// globals
	$dblink = false;
	
	
	// ApiDb
	class ApiDb
	{
		
		// connect to MySQL server
		static public function connect()
		{
			// verify if server defined
			if( strlen( ApiConfFile::getDbServer() ) <= 0 )
			{
				ApiLogging::logError( "Db server not defined in config" );	
				return false;	
			}
			
			// connect to db
			$dblink = @mysql_connect( ApiConfFile::getDbServer(), ApiConfFile::getDbUsername(), ApiConfFile::getDbPassword() );
			if( false == $dblink ) 
			{
				ApiLogging::logError( "Failed to connect to db server " . ApiConfFile::getDbServer() );
				return false;
			}
			
			// set character set to utf8
			@mysql_set_charset( "utf8", $dblink );
			@mysql_query( "SET character_set_results=utf8", $dblink );
            @mb_language( "uni" );
            @mb_internal_encoding( "UTF-8" );						
			
			// select db
			$select_db = @mysql_select_db( ApiConfFile::getDbName(), $dblink );
			if( false == $select_db ) 
			{
				ApiLogging::logError( "Failed to select db name " . ApiConfFile::getDbName() );			
				@mysql_close($dblink);
				return false;
			}
			
			// set character set
            @mysql_query( "SET names 'utf8'", $dblink );
			@mysql_query( "SET character_set_client=utf8", $dblink );
			@mysql_query( "SET character_set_connection=utf8", $dblink );	
			
			return $dblink;
		}
		
		
		// connect to server
		static public function disconnect()
		{
			global $dblink;

			if( !empty( $dblink ) )
			{
				@mysql_close( $dblink );
				$dblink = false;
			}
			
			return true;
		}	
		
		
		// reconnect to server
		static public function reconnect()
		{
			global $dblink;

			if( !empty( $dblink ) )
				@mysql_close( $dblink );
				
			$dblink = self::connect();
			return $dblink;
		}
		
		
		
		
		//-- USER
		
		
		// user table get by key
		static public function userTableGetByKey( $key )
		{
			global $dblink;
			
			// query
			$query = "SELECT A.* FROM `users` A, `userapikey` B WHERE A.`id`=B.`userID` AND B.`key`='" . @mysql_real_escape_string( $key ) . "'";
			
			// query
			$result = @mysql_query( $query, $dblink );
			if( false == $result )
			{
				ApiLogging::logError( "Failed to run query: " . $query );				
				return false;
			}
			
			if( mysql_num_rows( $result ) <= 0 )
			{
				mysql_free_result( $result );
				return false;
			}
			
			// get record
			$record = mysql_fetch_array( $result, MYSQL_ASSOC );
			mysql_free_result( $result );
				
			return $record;
		}
		
		
		// user table get by name
		static public function userTableGetByName( $name )
		{
			global $dblink;
			
			// query
			$query = "SELECT A.* FROM `users` A WHERE A.`username`='" . @mysql_real_escape_string( $name ) . "'";
			
			// query
			$result = @mysql_query( $query, $dblink );
			if( false == $result )
			{
				ApiLogging::logError( "Failed to run query: " . $query );				
				return false;
			}
			
			if( mysql_num_rows( $result ) <= 0 )
			{
				mysql_free_result( $result );
				return false;
			}
			
			// get record
			$record = mysql_fetch_array( $result, MYSQL_ASSOC );
			mysql_free_result( $result );
				
			return $record;
		}
		
		
		// user table get by id
		static public function userTableGetById( $id )
		{
			global $dblink;
			
			// query
			$query = "SELECT A.* FROM `users` A WHERE A.`id`='" . @mysql_real_escape_string( $id ) . "'";
			
			// query
			$result = @mysql_query( $query, $dblink );
			if( false == $result )
			{
				ApiLogging::logError( "Failed to run query: " . $query );				
				return false;
			}
			
			if( mysql_num_rows( $result ) <= 0 )
			{
				mysql_free_result( $result );
				return false;
			}
			
			// get record
			$record = mysql_fetch_array( $result, MYSQL_ASSOC );
			mysql_free_result( $result );
				
			return $record;
		}
		
		
		
		
		//-- DOCUMENT CONVERSION
		
		
		// document conversion table add
		static public function documentConversionTableAdd( $userID, $filename, $filepath, $type, $duration, $title, $status = 1 )
		{
			global $dblink;
			
			$dateAdded = date( "Y-m-d H:i:s", time() );
			
			// query
			$query  = "INSERT INTO `documentconversion` (`userID`, `filename`, `filepath`, `type`, `duration`, `title`, `status`, `dateAdded`) VALUES(";
			$query .= "'" . @mysql_real_escape_string( $userID ) . "',";
			$query .= "'" . @mysql_real_escape_string( $filename ) . "',";
			$query .= "'" . @mysql_real_escape_string( $filepath ) . "',";			
			$query .= "'" . @mysql_real_escape_string( $type ) . "',";
			$query .= "'" . @mysql_real_escape_string( $duration ) . "',";
			$query .= "'" . @mysql_real_escape_string( $title ) . "',";			
			$query .= "'" . @mysql_real_escape_string( $status ) . "',";
			$query .= "'" . @mysql_real_escape_string( $dateAdded ) . "')";
			
			// query
			$result = @mysql_query( $query, $dblink );
			if( false == $result )
			{
				ApiLogging::logError( "Failed to run query: " . $query );				
				return false;
			}
			
			$id = @mysql_insert_id();
			
			return $id;
		}		

		
		// document conversion table update
		static public function documentConversionTableUpdate( $id, $type, $duration, $title, $status = 1 )
		{
			global $dblink;
			
			// query
			$query = "UPDATE `documentconversion` SET " .
					 "`type`='" . 			@mysql_real_escape_string( $type ) . "', " .
					 "`duration`='" . 		@mysql_real_escape_string( $duration ) . "', " .	
					 "`title`='" . 			@mysql_real_escape_string( $title ) . "', " .					 
					 "`status`='" . 		@mysql_real_escape_string( $status ) . "' " .						 
					 "WHERE `id`='" . 		$id . "'";
			
			// query
			$result = @mysql_query( $query, $dblink );
			if( false == $result )
			{
				ApiLogging::logError( "Failed to run query: " . $query );				
				return false;
			}
			
			return true;		
		}

		
		
		
		
		//-- DOCUMENT ANALYSIS
	
		
		// document analysis table add
		static public function documentAnalysisTableAdd( $documentID, $text, $status = 1 )
		{
			global $dblink;
				
			$dateAdded = date( "Y-m-d H:i:s", time() );
			
			// query
			$query  = "INSERT INTO `documentanalysis` (`documentID`, `text`, `status`, `dateAdded`) VALUES(";
			$query .= "'" . @mysql_real_escape_string( $documentID ) . "',";
			$query .= "'" . @mysql_real_escape_string( $text ) . "',";			
			$query .= "'" . @mysql_real_escape_string( $status ) . "',";					
			$query .= "'" . @mysql_real_escape_string( $dateAdded ) . "')";
			
			// query
			$result = @mysql_query( $query, $dblink );
			if( false == $result )
			{
				ApiLogging::logError( "Failed to run query: " . $query );				
				return false;
			}
			
			return true;
		}
		
		
		// document analysis get unread
		static public function documentAnalysisTableGetUnread( $documentID )
		{
			global $dblink;
			
			// query
			$query = "SELECT A.* FROM `documentanalysis` A WHERE A.`documentID`=" . $documentID . " AND A.`status`=2 ORDER BY A.`dateAdded` ASC, A.`id` ASC";
			
			// query
			$result = @mysql_query( $query, $dblink );
			if( false == $result )
			{
				ApiLogging::logError( "Failed to run query: " . $query );				
				return false;
			}
			
			$rows = array();
			while( $row = mysql_fetch_array( $result ) )
				array_push( $rows, $row );
			
			mysql_free_result( $result );
				
			return $rows;
		}

		
		// document analysis mark read
		static public function documentAnalysisTableMarkRead( $idList )
		{
			global $dblink;
			
			if( empty( $idList ) )
				return true;
			
			// query
			$query = "UPDATE `documentanalysis` SET `status`=1 WHERE `id` IN (" . implode( ",", $idList ) . ")";
			
			// query
			$result = @mysql_query( $query, $dblink );
			if( false == $result )
			{
				ApiLogging::logError( "Failed to run query: " . $query );	
				return false;
			}
			
			return true;		
		}
		
		
		
		
		
		//-- USER STATS
		
		
		// user stats add
		static public function userStatsTableAdd( $userID, $date, $status )
		{
			global $dblink;
			
			$conversionCalls 	= 1;
			$conversionFailed	= 0;
			
			if( !$status )
				$conversionFailed = 0;
			
			// query
			$query = "SELECT `id` FROM `userstats` WHERE `userID`=" . $userID . " AND `dateAdded`='" . $date . " 00:00:00'";
			
			// query
			$result = @mysql_query( $query, $dblink );
			if( false == $result )
			{
				ApiLogging::logError( "Failed to run query: " . $query );
				return false;
			}
			
			if( mysql_num_rows( $result ) <= 0 )
			{
				mysql_free_result( $result );
				
				// query
				$query  = "INSERT INTO `userstats` (`userID`, `conversionCalls`, `conversionFailed`, `dateAdded`) VALUES(";
				$query .= "'" . @mysql_real_escape_string( $userID ) . "',";
				$query .= "'" . @mysql_real_escape_string( $conversionCalls ) . "',";
				$query .= "'" . @mysql_real_escape_string( $conversionFailed ) . "',";
				$query .= "'" . @mysql_real_escape_string( $date ) . "')";
			}
			else
			{
				$row = mysql_fetch_array( $result );
				mysql_free_result( $result );

				// query
				$query = "UPDATE `userstats` SET " .
						 "`conversionCalls`=`conversionCalls`+" . $conversionCalls . ", " .
						 "`conversionFailed`=`conversionFailed`+" . $conversionFailed . " " .						 
						 "WHERE `id`='" . $row["id"] . "'";
			}
			
			// query
			$result = @mysql_query( $query, $dblink );
			if( false == $result )
			{
				ApiLogging::logError( "Failed to run query: " . $query );				
				return false;
			}
			
			return true;
		}
		
		
		
		
		
		//-- JOB
		
		
		// job table get
		static public function jobTableGet( $userID, $jobID )
		{
			global $dblink;
			
			// query
			$query = "SELECT A.* FROM `job` A WHERE A.`id`='" . $jobID . "' AND A.`userID`='" . $userID . "'";
			
			// query
			$result = @mysql_query( $query, $dblink );
			if( false == $result )
			{
				ApiLogging::logError( "Failed to run query: " . $query );
				return false;
			}
			
			if( mysql_num_rows( $result ) <= 0 )
			{
				mysql_free_result( $result );
				return false;
			}
			
			// get record
			$record = mysql_fetch_array( $result, MYSQL_ASSOC );
			mysql_free_result( $result );
				
			return $record;
		}	
		
		
		// job table is available
		static public function jobTableIsAvailable( $processID )
		{
			global $dblink;
			
			// query
			$query = "SELECT A.`id` FROM `job` A WHERE A.`processID`='" . $processID . "' AND A.`status`=2 LIMIT 1";
			
			// query
			$result = @mysql_query( $query, $dblink );
			if( false == $result )
			{
				ApiLogging::logError( "Failed to run query: " . $query );
				return false;
			}
			
			if( mysql_num_rows( $result ) <= 0 )
			{
				mysql_free_result( $result );
				return true;
			}

			mysql_free_result( $result );				
			return false;
		}		
		
		
		// job table get next by worker		
		static public function jobTableGetNext( $processID )
		{
			global $dblink;
			
			// query
			$query = "SELECT A.* FROM `job` A WHERE A.`processID`='" . $processID . "' AND A.`status`=2 ORDER BY A.`dateAdded` ASC LIMIT 1";
			
			// query
			$result = @mysql_query( $query, $dblink );
			if( false == $result )
			{
				ApiLogging::logError( "Failed to run query: " . $query );
				return false;
			}
			
			if( mysql_num_rows( $result ) <= 0 )
			{
				mysql_free_result( $result );
				return false;
			}
			
			// get record
			$record = mysql_fetch_array( $result, MYSQL_ASSOC );
			mysql_free_result( $result );
				
			return $record;			
		}		
		
		
		// job table add
		static public function jobTableAdd( $userID, $filename, $filepath, $processID, $ipAddress = "", $response = "", $phase = 1, $status = 2, $subphase = 0 )
		{
			global $dblink;
			
			$dateAdded = date( "Y-m-d H:i:s", time() );
			
			// query
			$query  = "INSERT INTO `job` (`userID`, `filename`, `filepath`, `response`, `phase`, `subphase`, `status`, `processID`, `ipAddress`, `dateAdded`) VALUES(";
			$query .= "'" . @mysql_real_escape_string( $userID ) . "',";
			$query .= "'" . @mysql_real_escape_string( $filename ) . "',";			
			$query .= "'" . @mysql_real_escape_string( $filepath ) . "',";				
			$query .= "'" . @mysql_real_escape_string( $response ) . "',";
			$query .= "'" . @mysql_real_escape_string( $phase ) . "',";
			$query .= "'" . @mysql_real_escape_string( $subphase ) . "',";			
			$query .= "'" . @mysql_real_escape_string( $status ) . "',";		
			$query .= "'" . @mysql_real_escape_string( $processID ) . "',";			
			$query .= "'" . @mysql_real_escape_string( $ipAddress ) . "',";				
			$query .= "'" . @mysql_real_escape_string( $dateAdded ) . "')";
			
			// query
			$result = @mysql_query( $query, $dblink );
			if( false == $result )
			{
				ApiLogging::logError( "Failed to run query: " . $query );				
				return false;
			}
			
			$id = @mysql_insert_id( $dblink );
			
			return $id;
		}

		
		// job table update
		static public function jobTableUpdate( $id, $status, $response, $duration )
		{
			global $dblink;
			
			// query
			$query = "UPDATE `job` SET " .
					 "`status`='" . $status . "', " .
					 "`response`='" . @mysql_real_escape_string( $response ) . "', " .
					 "`duration`='" . @mysql_real_escape_string( $duration ) . "' " .					 
					 "WHERE `id`='" . $id . "'";
			
			// query
			$result = @mysql_query( $query, $dblink );
			if( false == $result )
			{
				ApiLogging::logError( "Failed to run query: " . $query );				
				return false;
			}
			
			return true;		
		}
		
		
		// job table update phase
		static public function jobTableUpdateDocument( $id, $documentID )
		{
			global $dblink;
			
			// query
			$query = "UPDATE `job` SET `documentID`='" . $documentID . "' WHERE `id`='" . $id . "'";
			
			// query
			$result = @mysql_query( $query, $dblink );
			if( false == $result )
			{
				ApiLogging::logError( "Failed to run query: " . $query );				
				return false;
			}
			
			return true;
		}

		
		// job table update phase
		static public function jobTableUpdatePhase( $id, $phase, $subphase = 0 )
		{
			global $dblink;
			
			// query
			$query = "UPDATE `job` SET `phase`='" . $phase . "', `subphase`='" . $subphase . "' WHERE `id`='" . $id . "'";
			
			// query
			$result = @mysql_query( $query, $dblink );
			if( false == $result )
			{
				ApiLogging::logError( "Failed to run query: " . $query );				
				return false;
			}
			
			return true;		
		}
		
		
		
		
		
		//-- KEYWORDS
		
		
		// keyword get
		static public function keywordTableGet()
		{
			global $dblink;
			
			// query
			$query = "SELECT A.* FROM `keyword` A WHERE A.`status`=1 ORDER BY LENGTH(A.`name`) DESC";
			
			// query
			$result = @mysql_query( $query, $dblink );
			if( false == $result )
			{
				ApiLogging::logError( "Failed to run query: " . $query );
				return false;
			}
			
			$rows = array();
			while( $row = mysql_fetch_array( $result ) )
			{
				$row["name"] = strtolower( $row["name"] );
				array_push( $rows, $row );
			}
			
			mysql_free_result( $result );
			
			return $rows;
		}		
		
		
		
		
		
		//-- WILDCARDS
		
		
		// wildcard get
		static public function wildcardTableGet()
		{
			global $dblink;
			
			// query
			$query = "SELECT A.* FROM `wildcard` A WHERE A.`status`=1 ORDER BY LENGTH(A.`name`) DESC";
			
			// query
			$result = @mysql_query( $query, $dblink );
			if( false == $result )
			{
				ApiLogging::logError( "Failed to run query: " . $query );
				return false;
			}
			
			$rows = array();
			while( $row = mysql_fetch_array( $result ) )
			{
				$row["name"] = strtolower( $row["name"] );
				array_push( $rows, $row );
			}
			
			mysql_free_result( $result );
			
			return $rows;
		}	
		
		
		
		
		
		//-- SETTINGS
		
		
		// settings table get
		static public function settingsTableGet( $name )
		{
			global $dblink;
			
			// query
			$query = "SELECT `value` FROM `settings` A WHERE A.`name`='" . @mysql_real_escape_string( $name ) . "'";
			
			// query
			$result = @mysql_query( $query, $dblink );
			if( false == $result )
			{
				ApiLogging::logError( "Failed to run query: " . $query );				
				return false;
			}
			
			if( mysql_num_rows( $result ) <= 0 )
			{
				mysql_free_result( $result );
				return false;
			}
			
			// get record
			$record = mysql_fetch_array( $result, MYSQL_ASSOC );
			mysql_free_result( $result );
				
			return $record["value"];
		}
		
		
		// settings table inc
		static public function settingsTableInc( $name )
		{
			global $dblink;
			
			// query
			$query = "UPDATE `settings` SET `value`=`value` + 1 WHERE `name`='" . @mysql_real_escape_string( $name ) . "'";
			
			// query
			$result = @mysql_query( $query, $dblink );
			if( false == $result )
			{
				ApiLogging::logError( "Failed to run query: " . $query );				
				return false;
			}
			
			return true;
		}
		
		
		// settings table update
		static public function settingsTableUpdate( $name, $value )
		{
			global $dblink;
			
			// query
			$query = "UPDATE `settings` SET `value`='" . @mysql_real_escape_string( $value ) . "' WHERE `name`='" . @mysql_real_escape_string( $name ) . "'";
			
			// query
			$result = @mysql_query( $query, $dblink );
			if( false == $result )
			{
				ApiLogging::logError( "Failed to run query: " . $query );				
				return false;
			}
			
			return true;
		}
		
		
		
		
		//-- CRON
		
		
		// cron table update status
		static public function cronTableUpdateStatus( $name, $status )
		{
			global $dblink;
			
			if( $status )
				$status = 1;
			else
				$status = 0;
			
			$date = date( "Y-m-d H:i:s", time() );
			
			// query
			$query = "UPDATE `cron` SET `status`=" . $status . ", `dateLastRun`='" . $date . "' WHERE `name`='" . $name . "'";
			
			$result = @mysql_query( $query, $dblink );
			if( false == $result )
			{
				ApiLogging::logError( "Failed to run query: " . $query );				
				return false;
			}
			
			return true;				
		}		
		
		
		
		
		
		//-- SYSTEM LOG
		
		
		// system log table add
		static public function systemLogTableAdd( $system, $userID, $message, $type )
		{
			global $dblink;
			
			$dateAdded = date( "Y-m-d H:i:s", time() );
			
			// query
			$query  = "INSERT INTO `systemlog` (`system`, `userID`, `message`, `type`, `dateAdded`) VALUES(";
			$query .= "'" . @mysql_real_escape_string( $system ) . "',";
			$query .= "'" . @mysql_real_escape_string( $userID ) . "',";
			$query .= "'" . @mysql_real_escape_string( $message ) . "',";						
			$query .= "'" . @mysql_real_escape_string( $type ) . "',";		
			$query .= "'" . @mysql_real_escape_string( $dateAdded ) . "')";
			
			// query
			$result = @mysql_query( $query, $dblink );
			if( false == $result )		
				return false;
			
			return true;			
		}

		
	};
	
?>