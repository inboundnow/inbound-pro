jQuery(document).ready(function($) {


jQuery('.lp_tooltip').on('mouseover', function(event) {
	// Bind the qTip within the event handler
	jQuery(this).qtip({
		overwrite: false, // Make sure the tooltip won't be overridden once created
			content: {
					title: {
						text: 'What does this do?'
					}
				},
		position: {
					my: 'bottom center', // Use the corner...
					at: 'top center', // ...and opposite corner
					viewport: jQuery(window)
				},
		style: {
					classes: 'qtip-shadow qtip-jtools',
				},
		show: {
			event: event.type, // Use the same show event as the one that triggered the event handler
			ready: true, // Show the tooltip as soon as it's bound, vital so it shows up the first time you hover!
			solo: true
		},
		hide: 'unfocus'
	}, event); // Pass through our original event to qTip
})

jQuery('.lp_tooltip_templates').on('mouseover', function(event) {
	// Bind the qTip within the event handler
	jQuery(this).qtip({
		overwrite: false, // Make sure the tooltip won't be overridden once created
			content: {
					title: {
						text: 'About this Template'
					}
				},
		position: {
					my: 'bottom center', // Use the corner...
					at: 'top center', // ...and opposite corner
					viewport: jQuery(window)
				},
		style: {
					classes: 'qtip-shadow qtip-jtools',
				},
		show: {
			event: event.type, // Use the same show event as the one that triggered the event handler
			ready: true, // Show the tooltip as soon as it's bound, vital so it shows up the first time you hover!
			solo: true
		},
		hide: 'unfocus'
	}, event); // Pass through our original event to qTip
})
  // put all your jQuery goodness in here.
});