/* Load listeners after document loaded */
var Downloads = {
	Modules : {}
};

Downloads.Modules.Templates = (function($, undefined) {
	var $grid,
	$meta,
	$plugins,
	$template,
	meta_filter = [],
	plugins = 'all' ;

	/**
	 *  Initialize Script
	 */
	init = function() {
		setVars();
		initFilters();
		initShuffle();
		setupSearching();
		initDetails();

		/* initiate tooltips */
		jQuery('[data-toggle="tooltip"]').tooltip();

		/* preset filter */
		jQuery("input[name=meta][value=" + downloads.meta_filter + "]").prop('checked', true).trigger('change');

	},

	/**
	 *  Setups Searching
	 */
	setupSearching = function() {
		// Advanced filtering
		jQuery('.filter-search').on('keyup change', function() {
			var val = this.value.toLowerCase();
			$grid.shuffle('shuffle', function($el, shuffle) {

				/* honor current plugin selection */
				if ( plugins !== 'all' && $.inArray( plugins, $el.data('plugins')) === -1) {
				  return false;
				}

				/* make sure we honor meta selection */
				if ($.inArray( meta_filter, $el.data('meta')) === -1) {
				  return false;
				}

				var text = $.trim( $el.find('.col-template-info-title').text() ).toLowerCase();
				return text.indexOf(val) !== -1;
			});
		});
	},

	/**
	 *  Define main elements to listen to when filtering
	 */
	setVars = function() {
		$grid = jQuery('#grid');
		$meta = jQuery('.radio-filters');
		$plugins = jQuery('.templates-filter-group');

	},


	/**
	 *
	 */
	initShuffle = function() {
		// instantiate the plugin
		$grid.shuffle({
			speed : 500,
			easing : 'cubic-bezier(0.165, 0.840, 0.440, 1.000)', // easeOutQuart
		});
	},

	initFilters = function() {
		// meta
		$meta.find('input').on('change', function() {
			var $checked = $meta.find('input:checked');
			meta_filter = $checked.val();

			filter();

			updatePreset();
		});

		/* listen for slected plugins */
		$plugins.find('button').on('click', function() {
			var $this = jQuery(this);

			plugins = $this.data( 'filter-value' )

			filter();

		});
	},

	filter = function() {

		if ( hasActiveFilters() ) {
			$grid.shuffle('shuffle', function($el) {
				return itemPassesFilters( $el.data() , $el );
			});
		} else {
			$grid.shuffle( 'shuffle', 'all' );
		}
	},

	itemPassesFilters = function(data , $el) {

		// If a meta_filter filter is active
		if ( meta_filter.length > 0 && !valueInArray( meta_filter , data.meta) ) {
			return false;
		}

		/* If a plugins filter is active */
		if ( plugins.length > 0 && !valueInArray(plugins , data.plugins ) ) {
			return false;
		}

		/* If a search filter is present */
		if ( jQuery('.filter-search').val().toLowerCase().length > 0 ) {

			var text = $.trim( $el.find('.col-template-info-title').text() ).toLowerCase();
			if (text.indexOf(jQuery('.filter-search').val().toLowerCase()) === -1) {
				return false;
			}
		}

		return true;
	},

	hasActiveFilters = function() {
		return plugins.length > 0 || meta_filter.length > 0;
	},

	valueInArray = function(value, arr) {
		return $.inArray(value, arr) !== -1;
	},

	updatePreset = function() {
		jQuery.ajax({
			type: "POST",
			url: ajaxurl ,
			data: {
				action: 'inbound_update_download_filter_preferences',
				meta_filter: meta_filter,
			},
			dataType: 'html',
			timeout: 60000,
			success: function (response) {

			},
			error: function(request, status, err) {
				alert(status);
			}
		});
	},

	/**
	 *  adds listeners to fire the overlow
	 */
	initDetails = function() {
		$template = getDetailsTemplate();
        console.log(downloads);

		/* on more details click */
		jQuery('.more-details').on('click', function() {
			/* get selected template */
			var download_name = jQuery( this ).data('download');
			openMoreDetails( download_name );
		});

		/* on overlay close */
		jQuery('body').on( 'click' , '.overlay-close' , function() {
			var download = jQuery( this ).data('download');
			console.log(download);
			jQuery('.download-overlay[data-download="'+download+'"]').remove();
		});


		/* on next */
		jQuery('body').on( 'click' , '.overlay-next' , function() {
			jQuery('.download-overlay').remove();
			var download = jQuery( this ).data('download');
			openMoreDetails( download );
		});

		/* on previous */
		jQuery('body').on( 'click' , '.overlay-previous' , function() {
			jQuery('.download-overlay').remove();
			var download = jQuery( this ).data('download');
			openMoreDetails( download );
		});

		/* on install */
		jQuery('body').on( 'click' , '.overlay-install' , function() {
            var download = jQuery( this ).data('download');
            var filename = jQuery( this ).data('filename');
            var download_type = jQuery( this ).data('download-type');
            window.location.href = "admin.php?page=" + downloads.current_page + "&action=install&download_name=" + download + '&filename=' + filename + '&download_type=' + download_type;
		});

		/* on uninstall */
		jQuery('body').on( 'click' , '.overlay-uninstall' , function() {
			var download = jQuery( this ).data('download');
			window.location.href = "admin.php?page=" + downloads.current_page + "&action=uninstall&download_name=" + download ;
		});

		/* on update */
		jQuery('body').on( 'click' , '.overlay-update' , function() {
            var download = jQuery( this ).data('download');
            var filename = jQuery( this ).data('filename');
            var download_type = jQuery( this ).data('download-type');
            window.location.href = "admin.php?page=" + downloads.current_page + "&action=install&download_name=" + download + '&filename=' + filename + '&download_type=' + download_type;
		});

	},

	/**
	 *  Method to open the more details box
	 */
	openMoreDetails = function( download_name ) {

		/* select correct download object key given template name */
		var download_object =  _.findWhere( downloads.dataset , { post_name : download_name });

		/* build image url property */
		download_object.image = downloads.plugin_url_path + 'assets/images/downloads/' + download_name + '.jpg';

		/* clean content of shortcodes */
		download_object.post_content = download_object.post_content.replace(/\[(\S+)[^\]]*][^\[]*\[\/\1\]/g, '');

		/* clean iframe html   */
		download_object.post_content = download_object.post_content.replace(/(<iframe.*?>.*?<\/iframe>)/g, '');

		/* build template */
		var parsedTemplate = _.template($template)( download_object );

		$grid.append( parsedTemplate );

	}

	/**
	 *  Gets template file for download details
	 */
	getDetailsTemplate = function() {

		return jQuery.ajax({
			type: 'GET',
			async: false,
			url: downloads.plugin_url_path + 'assets/templates/admin/download-details.html'
		}).responseText;
	};

	return {
		init: init
	};
}(jQuery));



jQuery(document).ready(function() {
	/* Initiate filters */
	Downloads.Modules.Templates.init();
});