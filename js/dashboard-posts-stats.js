(function($){
	google.load('visualization', '1.0', {'packages':['corechart']});
	google.setOnLoadCallback( dashboardPostStats );
	// chart styles taken from Antispam-bee :-)
	function dashboardPostStats(){
		$.get( ajaxurl, {
			action: 'get_dashboard_posts_stats'
		}, function(json){
			var data = google.visualization.arrayToDataTable( json );
			var options = {
				height: 120,
				axisTitlesPosition: 'none',
				backgroundColor: {
					fill: 'transparent'
				},
				hAxis: {
					textPosition: 'none'
				},
				vAxis: {
					gridlines: { color: '#ececec' },
					textStyle: { color: '#999', fontSize: 10 },
					baselineColor: '#ececec'
				},
				legend: {
					position: 'none'
				},
				theme: 'maximized',
				lineWidth: 3,
				pointSize: 6,
				colors: [ '#3399CC' ]
			},
			chart = new google.visualization.AreaChart( document.getElementById('dashboard-post-stats-canvas') );
			chart.draw( data, options );
		}, 'json');
	}
})(jQuery);