jQuery(document).ready(function () {
	
	/* Load tooltips on labels and links */
	jQuery("a,label,.label").tooltip({
		placement : 'left'
	});  
	
	/* Load Spin.Js & Add spinner to .modal-body */
	var opts = {
		lines: 12, // The number of lines to draw
		length: 40, // The length of each line
		width: 3, // The line thickness
		radius: 30, // The radius of the inner circle
		corners: 1, // Corner roundness (0..1)
		rotate: 0, // The rotation offset
		direction: 1, // 1: clockwise, -1: counterclockwise
		color: '#000', // #rgb or #rrggbb or array of colors
		speed: 1, // Rounds per second
		trail: 60, // Afterglow percentage
		shadow: true, // Whether to render a shadow
		hwaccel: false, // Whether to use hardware acceleration
		className: 'ia-spinner', // The CSS class to assign to the spinner
		zIndex: 2e9, // The z-index (defaults to 2000000000)
		top: '1000%', // Top position relative to parent
		left: '50%' // Left position relative to parent
	};
	
	jQuery('.modal-body').spin(opts);
	
	/* Add click listen to load correct iframe */
	jQuery('.ia-table-summary').delegate( 'a' , 'click' , function() {
		
		/* turn spin off */
		jQuery('.modal-body').spin(false);
		
		/* Get width & height of iframe */
		var width = jQuery( this ) . attr('modal-width');
		//var height = jQuery( this ) . attr('modal-height');
		
		/* get report date range */
		var date_range = jQuery( '.date-range > li.active').attr('data-range');

		/* get report class name */
		var report_class_name = jQuery( this ).attr( 'report-class-name' );

		/* prepare params for url */
		var params = {
			action: 'inbound_generate_report',
			report_class_name: report_class_name,
			date_range: date_range
		};
		
		/* prepare params for url */
		params = jQuery.param( params );

		/* load correct analytics template */
		jQuery(".ia-frame").attr("src", ajaxurl + '?' +params );
		
		/* resize modal to fit contents */
		jQuery(".modal-dialog").animate({width: width }, 500);

	});
});