(function($){
	google.load('visualization', '1.0', {'packages':['corechart']});
	google.setOnLoadCallback( visualizePosts );
	function visualizePosts(){
		$.get( ajaxurl, {
			action: 'get_visualize_post_data'
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
			chart = new google.visualization.AreaChart( document.getElementById('visualize-posts-canvas') );
			chart.draw( data, options );
		}, 'json');
	}
})(jQuery);