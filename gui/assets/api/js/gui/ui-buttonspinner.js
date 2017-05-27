	
var UISpinButtons = function () {

	//function to initiate LaddaButtons
	var runLeddaButtons = function () {
		// Bind normal buttons
		Ladda.bind('.ladda-button', {
			timeout: 1000000
		});
	};
	
	var stopLeddaButtons = function () {
		Ladda.stopAll();
	};
	
	return {
		//main function to initiate template pages
		init: function () {
			runLeddaButtons();
		},
		
		stop: function () {
			stopLeddaButtons();
		}
	};
}();
	
			
function stopSpins()
{			
	window.setTimeout( stopSpinsPerform, 200 );		
}

function stopSpinsPerform()
{
	Ladda.stopAll();
}
