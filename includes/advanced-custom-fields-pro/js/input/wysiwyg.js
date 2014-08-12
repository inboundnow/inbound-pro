(function($){
	
	/*
	*  WYSIWYG
	*
	*  jQuery functionality for this field type
	*
	*  @type	object
	*  @date	20/07/13
	*
	*  @param	N/A
	*  @return	N/A
	*/
	
	acf.fields.wysiwyg = {
		
		$el : null,
		$textarea : null,
		
		o : {},
		
		set : function( o ){
			
			// merge in new option
			$.extend( this, o );
			
			
			// find textarea
			this.$textarea = this.$el.find('textarea');
			
			
			// get options
			this.o = acf.get_data( this.$el );
			
			
			// add ID
			this.o.id = this.$textarea.attr('id');
			
			
			// return this for chaining
			return this;
			
		},
		has_tinymce : function(){
		
			var r = false;
			
			if( typeof(tinyMCE) == "object" )
			{
				r = true;
			}
			
			return r;
			
		},
		get_toolbar : function(){
			
			// safely get toolbar
			if( acf.isset( this, 'toolbars', this.o.toolbar ) ) {
				
				return this.toolbars[ this.o.toolbar ];
				
			}
			
			
			// return
			return false;
			
		},
		
		init : function(){
			
			// vars
			var toolbar = this.get_toolbar(),
				command = 'mceAddControl',
				setting = 'theme_advanced_buttons{i}';
			
			
			// backup
			var _settings = $.extend( {}, tinyMCE.settings );
			
			
			// v4 settings
			if( tinymce.majorVersion == 4 ) {
				
				command = 'mceAddEditor';
				setting = 'toolbar{i}';
				
			}
			
			
			// add toolbars
			if( toolbar ) {
					
				for( var i = 1; i < 5; i++ ) {
					
					// vars
					var v = '';
					
					
					// load toolbar
					if( acf.isset( toolbar, 'theme_advanced_buttons' + i ) ) {
						
						v = toolbar['theme_advanced_buttons' + i];
						
					}
					
					
					// update setting
					tinyMCE.settings[ setting.replace('{i}', i) ] = v;
					
				}
				
			}
			
			
			// hook for 3rd party customization
			tinyMCE.settings = acf.apply_filters('wysiwyg_tinymce_settings', tinyMCE.settings, this.o.id);
			
			
			// add editor
			tinyMCE.execCommand( command, false, this.o.id);
			
			
			// add events (click, focus, blur) for inserting image into correct editor
			this.add_events();
				
			
			// restore tinyMCE.settings
			tinyMCE.settings = _settings;
			
			
			// set active editor to null
			wpActiveEditor = null;
					
		},
		
		add_events : function(){
		
			// vars
			var id = this.o.id,
				editor = tinyMCE.get( id );
			
			
			// validate
			if( !editor )
			{
				return;
			}
			
			
			// vars
			var	$container = $('#wp-' + id + '-wrap'),
				$body = $( editor.getBody() ),
				$textarea = $( editor.getElement() );
	
			
			// events
			$container.on('click', function(){
				
				acf.validation.remove_error( $container.closest('.acf-field') );
				
			});
			
			$body.on('focus', function(){
			
				wpActiveEditor = id;
		
				acf.validation.remove_error( $container.closest('.acf-field') );
				
			});
			
			$body.on('blur', function(){
			
				wpActiveEditor = null;
				
				// update the hidden textarea
				// - This fixes a but when adding a taxonomy term as the form is not posted and the hidden textarea is never populated!

				// save to textarea	
				editor.save();
				
				
				// trigger change on textarea
				$textarea.trigger('change');
				
			});
			
			
		},
		destroy : function(){
			
			// vars
			var id = this.o.id,
				command = 'mceRemoveControl';
			
			
			// Remove tinymce functionality.
			// Due to the media popup destroying and creating the field within such a short amount of time,
			// a JS error will be thrown when launching the edit window twice in a row.
			try {
				
				// vars
				var editor = tinyMCE.get( id );
				
				
				// validate
				if( !editor ) {
					
					return;
					
				}
				
				
				// v4 settings
				if( tinymce.majorVersion == 4 ) {
					
					command = 'mceRemoveEditor';
					
				}
				
				
				// store value
				var val = editor.getContent();
				
				
				// remove editor
				tinyMCE.execCommand(command, false, id);
				
				
				// set value
				this.$textarea.val( val );
				
				
			} catch(e) {
				
				//console.log( e );
				
			}
			
			
			// set active editor to null
			wpActiveEditor = null;
			
		}
		
	};
	
	
	/*
	*  acf/setup_fields
	*
	*  run init function on all elements for this field
	*
	*  @type	event
	*  @date	20/07/13
	*
	*  @param	{object}	e		event object
	*  @param	{object}	el		DOM object which may contain new ACF elements
	*  @return	N/A
	*/
	
	acf.add_action('ready', function( $el ){
		
		// validate
		if( ! acf.fields.wysiwyg.has_tinymce() ) {
		
			return;
			
		}
		
		
		// vars
		var $the_content = $('#wp-content-wrap'),
			$acf_content = $('#wp-acf_content-wrap'),
			mode = 'tmce';
		
		
		// move editor to bottom of page
		if( $acf_content.exists() ) {
			
			$acf_content.parent().appendTo('body');
			
		}
				
		
		// events
		acf.add_action('remove', function( $el ){
		
			acf.get_fields({ type : 'wysiwyg'}, $el).each(function(){
				
				acf.fields.wysiwyg.set({ $el : $(this).find('.acf-wysiwyg-wrap') }).destroy();
				
			});
			
		}).add_action('sortstart', function( $el ){
			
			acf.get_fields({ type : 'wysiwyg'}, $el).each(function(){
			
				acf.fields.wysiwyg.set({ $el : $(this).find('.acf-wysiwyg-wrap') }).destroy();
				
			});
			
		}).add_action('sortstop', function( $el ){
		
			acf.get_fields({ type : 'wysiwyg'}, $el).each(function(){
				
				acf.fields.wysiwyg.set({ $el : $(this).find('.acf-wysiwyg-wrap') }).init();
				
			});
			
		}).add_action('append', function( $el ){
		
			acf.get_fields({ type : 'wysiwyg'}, $el).each(function(){
				
				acf.fields.wysiwyg.set({ $el : $(this).find('.acf-wysiwyg-wrap') }).init();
				
			});
			
		}).add_action('load', function( $el ){
			
			// vars
			var $fields = acf.get_fields({ type : 'wysiwyg'}, $el);
			
			
			// define mode
			if( $acf_content.exists() && $acf_content.hasClass('html-active') ) {
				
				mode = 'html';
				
			}
			
			
			// Add events to content editor
			if( $the_content.exists() ) {
			
				acf.fields.wysiwyg.set({ $el : $the_content }).add_events();
				
			}
			
			
			// temp change wysiwyg to visual tab
			setTimeout(function(){
				
				// trigger click on hidden WYSIWYG (to get in HTML mode)
				if( $acf_content.exists() && mode == 'html' ) {
					
					$acf_content.find('#acf_content-tmce').trigger('click');
				}
				
			}, 1);
			
			
			// destroy all WYSIWYG fields
			// This hack will fix a problem when the WP popup is created and hidden, then the ACF popup (image/file field) is opened
			setTimeout(function(){
				
				$fields.each(function(){
					
					acf.fields.wysiwyg.set({ $el : $(this).find('.acf-wysiwyg-wrap') }).destroy();
					
				});
				
			}, 10);
			
			
			// initialize all WYSIWYG fields
			setTimeout(function(){
				
				$fields.each(function(){
					
					acf.fields.wysiwyg.set({ $el : $(this).find('.acf-wysiwyg-wrap') }).init();
					
				});
				
			}, 11);
			
			
			setTimeout(function(){
				
				// trigger click on hidden WYSIWYG (to get in HTML mode)
				if( $acf_content.exists() && mode == 'html' ) {
					
					$acf_content.find('#acf_content-html').trigger('click');
				}				
				
			}, 12);
			
			
		});
		
		
	});
	
	
	/*
	*  Full screen
	*
	*  @description: this hack will hide the 'image upload' button in the WYSIWYG full screen mode if the field has disabled image uploads!
	*  @since: 3.6
	*  @created: 26/02/13
	*/
	
	$(document).on('click', '.acf-wysiwyg-wrap .mce_fullscreen', function(){
		
		// vars
		var $wrap = $(this).closest('.acf-wysiwyg-wrap');
		
		
		if( ! acf.get_data( $wrap, 'upload' ) ) {
		
			$('#mce_fullscreen_container #mce_fullscreen_add_media').hide();
			
		}
		
	});
	

})(jQuery);
