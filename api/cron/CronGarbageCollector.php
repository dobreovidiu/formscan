<?php
	
	// Cron Garbage Collector - performs local storage garbage collection.
	
	// change dir to API root
	@chdir( "../" );
	
	
	// includes
	require_once "src/includecore.php";
	
	
	
	// CronGarbageCollector
	class CronGarbageCollector
	{
		// globals	
		protected $fileList			= false;
		protected $fileTotal		= 0;
		protected $filesRemoved		= false;
		
		
		// run
		public function run()
		{
			// verify if process already running
			if( $this->isProcess() )
				return;
			
			// is alive
			ApiLogging::isAlive( "crongarbagecollector" );
			
			// logging
			ApiLogging::logApp( "[CronGarbageCollector] Start Garbage Collector cron" );
			
			// process collect
			$status = $this->processCollect();
			
			// update cron status
			ApiDb::cronTableUpdateStatus( "CronGarbageCollector", $status );
			
			// logging	
			ApiLogging::logApp( "[CronGarbageCollector] Completed Garbage Collector cron" );
		}
		
		
		// garbage collect
		protected function processCollect()
		{
			// log folder
			if( !$this->garbageCollectFolder( "log", 30, 0 ) )
			{
				ApiLogging::logError( "[CronGarbageCollector] Failed to garbage collect folder log" );
				return false;
			}
			
			return true;
		}
		
		
		// garbage collect folder
		protected function garbageCollectFolder( $folder, $days = 3, $removeEmptyFolders = 1 )
		{
			// is alive
			ApiLogging::isAlive();
				
			// init file list
			$this->filesRemoved		= array();
			$this->fileList  		= array();
			$this->fileTotal 		= 0;
			
			// get current time
			$curTime = time();
			
			// get folder files
			if( !$this->getFolderFiles( $folder, $curTime, $days * 24 * 3600 ) )
				return false;
			
			// is alive
			ApiLogging::isAlive();
			
			// logging
			ApiLogging::logApp( "[CronGarbageCollector] Garbage collect " . $this->fileTotal . " files from folder " . $folder );
			
			// delete files
			if( !$this->deleteFiles( $folder, $removeEmptyFolders ) )
				return false;
			
			return true;
		}
		
		
		// get folder files
		protected function getFolderFiles( $folder, $curTime, $period )
		{				
			// get files
			$files = scandir( $folder );
			if( is_bool( $files ) )
				return false;
				
			// init sub-folder
			$subFolder = array();
			array_push( $subFolder, $folder );
				
			// filter files
			$no = count( $files );
			for( $i = 0; $i < $no; $i++ )
			{
				$file = $files[$i];
				
				// ignore .
				if( $file == "." || $file == ".." )
					continue;
					
				// sub-folder
				$filePath = $folder . "/" . $file;
				if( is_dir( $filePath ) )
				{
					// scan sub-folder
					$this->getFolderFiles( $filePath, $curTime, $period );
					continue;
				}
					
				// file must not be recent
				if( ( $curTime - filemtime( $filePath ) ) < $period )
					continue;
					
				// add file
				array_push( $subFolder, $filePath );
				$this->fileTotal++;
			}
			
			// add sub-folder
			array_push( $this->fileList, $subFolder );
			
			return true;
		}
		
		
		// delete files
		protected function deleteFiles( $root, $removeEmptyFolders )
		{
			// traverse subfolders
			$noFolders = count( $this->fileList );
			for( $i = 0; $i < $noFolders; $i++ )
			{
				$subFolder = $this->fileList[$i];
				
				// traverse files
				$noFiles = count( $subFolder );
				for( $j = 1; $j < $noFiles; $j++ )
				{
					// remove file locally
					if( !@unlink( $subFolder[$j] ) )
						ApiLogging::logError( "[CronGarbageCollector] Failed to delete local file " . $subFolder[$j] );
					else
						array_push( $this->filesRemoved, $subFolder[$j] );
				}
					
				// remove folder (if empty)
				if( $removeEmptyFolders )
				{
					if( $noFiles > 0 && $root != $subFolder[0] )
					{
						// remove folder
						@rmdir( $subFolder[0] );
					}
				}
			}
			
			return true;
		}
		
		
		// whether process exists
		protected function isProcess()
		{
			global $osType;
			
			if( strtolower( $osType ) != "linux" )
				return false;
				
			$processName = "/api/cron && php ./CronGarbageCollector.php";
			
			// list process
			$cmd = "ps ax | grep '" . $processName . "' | grep -v 'grep' | wc -l";
			$output = array();
			$result = exec($cmd, $output, $status);
			if( $status != 0 )
				return true;
			
			if( count($output) != 1 )
				return true;
			
			$noInst = intval($output[0]);
			if( $noInst > 1 )
				return true;
				
			return false;
		}
		
	};
	
	
	// run
	$cron = new CronGarbageCollector();
	$cron->run();	

?>