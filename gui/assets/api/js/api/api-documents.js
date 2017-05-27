
	
	// API Documents functions.


	// globals
	var currentJobID			= false;
	var currentTimerID			= false;
	
	
	
	
	// run check
	function onRunCheck()
	{
		document.getElementById("automationLogs").innerHTML = "Please wait while processing document...";
		
		// build request
		var dataString  = '{"action":"check"}';
		//alert(dataString);
		//return false;
		
		// send request
		startAjax();		
		$.ajax({  
			type: "POST", data:{'_gt_json':dataString}, dataType: 'json', url: './cms/controllers/DocumentController.php',
			complete: function(data)
			{
				endAjax();
				
				var result = data.responseText;
				//alert(result);
				
				// parse JSON
				try{
					response = JSON.parse(result);
				}catch(e){
					stopSpins();					
					return;
				}
				
				if( response.success == "false" )
				{
					stopSpins();					
					document.getElementById("automationLogs").innerHTML = "Form automation not running.";
					showStatus( "Oops", response.exception );
					return;
				}

				stopSpins();					
				document.getElementById("automationLogs").innerHTML = response.results;
			}
		});
	}
	
	
	// run check async
	function onRunCheckAsync()
	{
		document.getElementById("processBut").disabled = true;
					
		document.getElementById("automationLogs").innerHTML = "Please wait while processing document...";
		
		// build request
		var dataString  = '{"action":"checkasync"}';
		//alert(dataString);
		//return false;
		
		// send request
		startAjax();		
		$.ajax({  
			type: "POST", data:{'_gt_json':dataString}, dataType: 'json', url: './cms/controllers/DocumentController.php',
			complete: function(data)
			{
				endAjax();
				stopSpins();
				
				var result = data.responseText;
				//alert(result);
				
				// parse JSON
				try{
					response = JSON.parse(result);
				}catch(e){				
					document.getElementById("automationLogs").innerHTML = "Form automation not running.";				
					document.getElementById("processBut").disabled = false;
					return;
				}
				
				if( response.success == "false" )
				{				
					document.getElementById("automationLogs").innerHTML = "Form automation not running.";			
					document.getElementById("processBut").disabled = false;			
					showStatus( "Oops", response.exception );
					return;
				}

				if( currentTimerID != false )
				{
					clearTimeout( currentTimerID );				
					currentTimerID = false;
				}
				
				// display search results
				if( response.jobID != undefined )
				{
					// start timer
					currentJobID 	= response.jobID;
					currentTimerID 	= setTimeout( showCurrentJob, 1000 );
				}
			}
		});
	}
	
	
	// show current job
	function showCurrentJob()
	{
		// build request
		var dataString  = '{"action":"getsearchstatus", "jobID":"' + encodeURIComponent( currentJobID ) + '"}';
						  
		//alert(dataString);
		//return false;
		
		// send request
		startAjax();		
		$.ajax({  
			type: "POST", data:{'_gt_json':dataString}, dataType: 'json', url: './cms/controllers/DocumentController.php',
			complete: function(data)
			{
				endAjax();				
		
				var result = data.responseText;
				//alert(result);
				
				// parse JSON
				try{
					response = JSON.parse(result);
				}catch(e){		
					document.getElementById("automationLogs").innerHTML = "Form automation not running.";			
					document.getElementById("processBut").disabled = false;				
					return;
				}
				
				if( response.success == "false" )
				{						
					document.getElementById("automationLogs").innerHTML = "Form automation not running.";			
					document.getElementById("processBut").disabled = false;			
					showStatus( "Oops", response.exception );
					return;
				}

				// display logs
				if( response.logs != undefined )
				{	
					showCheckLogs( response.logs );
				}
				
				// completed
				if( response.completed != undefined && response.completed == "1" )
				{				
					document.getElementById("processBut").disabled = false;
					showStatus( "Confirmation", "The Fat Finger app has been created successfully!<br><br>App Title: <b>" + response.title + "</b><br><br>Fat Finger account: dobreovidiu@hotmail.com" );
					return;
				}
				
				// reactivate timer
				currentTimerID = setTimeout( showCurrentJob, 1000 );
			}
		});
	}
	
	
	// show check logs
	function showCheckLogs( logs )
	{
		var no = logs.length;
		
		for( var i = 0; i < no; i++ )
		{
			checkResultsAppend( logs[i] );
		}
	}
	
	
	// append check results
	function checkResultsAppend( msg )
	{
		var val = document.getElementById("automationLogs").innerHTML;
		document.getElementById("automationLogs").innerHTML = val + "<br>" + msg;
		
		document.getElementById("automationLogs").scrollTop += 100;
	}


	