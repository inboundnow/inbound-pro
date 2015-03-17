(function($){
	
	acf.fields.google_map = acf.field.extend({
		
		type: 'google_map',
		$el: null,
		$input : null,
		
		status : '', // '', 'loading', 'ready'
		geocoder : false,
		map : false,
		maps : {},
		pending: $(),
		
		actions: {
			'ready':	'initialize',
			'append':	'initialize',
			'show':		'show'
		},
		
		events: {
			'click a[data-name="clear-location"]': 	'clear',
			'click a[data-name="find-location"]': 	'locate',
			'click .title h4': 						'edit',
			'keydown .search': 						'keydown',
			'blur .search': 						'blur',
		},
		
		focus: function(){
			
			// get elements
			this.$el = this.$field.find('.acf-google-map');
			this.$input = this.$el.find('.value');
			
			
			// get options
			this.o = acf.get_data( this.$el );
			
			
			// get map
			if( this.maps[ this.o.id ] ) {
				
				this.map = this.maps[ this.o.id ];
				
			}
			
		},
		
		/*
		*  is_ready
		*
		*  This function will ensure google API is available and return a boolean for the current status
		*
		*  @type	function
		*  @date	19/11/2014
		*  @since	5.0.9
		*
		*  @param	n/a
		*  @return	(boolean)
		*/
		
		is_ready: function(){ 
			
			// reference
			var self = this;
			
			
			// debug
			//console.log('is_ready: %o', this.status);
			
			// check
			if( this.status == 'ready' ) {
				
				return true;
				
			} else if( this.status == 'loading' ) {
				
				return false;
				
			} else if( typeof google === 'undefined' ) {
				
				// set status
				self.status = 'loading';
				
				
				// load API
				$.getScript('https://www.google.com/jsapi', function(){
					
					// load maps
				    google.load('maps', '3', { other_params: 'sensor=false&libraries=places', callback: function(){
				    	
				    	// set status
				    	self.status = 'ready';
				    	
				    	
				    	// initialize pending
				    	self.initialize_pending();
				        
				    }});
				    
				});
				
				return false;
					
			} else if( typeof google.maps === 'undefined' ) {
				
				
				// set status
				self.status = 'loading';
				
				
				// load maps
			    google.load('maps', '3', { other_params: 'sensor=false&libraries=places', callback: function(){
			    	
			    	// set status
			    	self.status = 'ready';
			    	
			    	
			    	// initialize pending
			    	self.initialize_pending();
			        
			    }});
				
				return false;
					
			}
			
			
			// google must exist already
			this.status = 'ready';
			
			
			// return
			return true;
			
		},
		
		initialize_pending: function(){
			
			// debug
			//console.log('initialize_pending', this.status);
			
			// reference
			var self = this;
			
			this.pending.each(function(){
				
				self.doFocus( $(this) ).initialize();
				
			});
			
			
			// reset
			this.pending = $();
			
		},
		
		initialize: function(){
			
			// add to pending
			if( !this.is_ready() ) {
				
				this.pending = this.pending.add( this.$field );
				
				return false;
				
			}
			
			
			// load geocode
			if( !this.geocoder ) {
				
				this.geocoder = new google.maps.Geocoder();
				
			}
			
			
			// reference
			var self = this,
				$field = this.$field,
				$el = this.$el;
			
			
			// vars
			var args = {
        		zoom		: parseInt(this.o.zoom),
        		center		: new google.maps.LatLng(this.o.lat, this.o.lng),
        		mapTypeId	: google.maps.MapTypeId.ROADMAP
        	};
			
			// create map	        	
        	this.map = new google.maps.Map( this.$el.find('.canvas')[0], args);
	        
	        
	        // add search
			var autocomplete = new google.maps.places.Autocomplete( this.$el.find('.search')[0] );
			autocomplete.map = this.map;
			autocomplete.bindTo('bounds', this.map);
			
			
			// add dummy marker
	        this.map.marker = new google.maps.Marker({
		        draggable	: true,
		        raiseOnDrag	: true,
		        map			: this.map,
		    });
		    
		    
		    // add references
		    this.map.$el = this.$el;
		    this.map.$field = this.$field;
		    
		    
		    // value exists?
		    var lat = this.$el.find('.input-lat').val(),
		    	lng = this.$el.find('.input-lng').val();
		    
		    if( lat && lng ) {
			    
			    this.update(lat, lng).center();
			    
		    }
		    
		    
			// events
			google.maps.event.addListener(autocomplete, 'place_changed', function( e ) {
			    
			    // reference
			    var $el = this.map.$el,
			    	$field = this.map.$field;
					
					
			    // manually update address
			    var address = $el.find('.search').val();
			    $el.find('.input-address').val( address );
			    $el.find('.title h4').text( address );
			    
			    
			    // vars
			    var place = this.getPlace();
			    
			    
			    // if place exists
			    if( place.geometry ) {
				    
			    	var lat = place.geometry.location.lat(),
						lng = place.geometry.location.lng();
						
					
					self.doFocus( $field ).update( lat, lng ).center();
				    
				    // bail early
				    return;
			    }
			    
			    
			    // client hit enter, manually get the place
			    self.geocoder.geocode({ 'address' : address }, function( results, status ){
			    	
			    	// validate
					if( status != google.maps.GeocoderStatus.OK ) {
						
						console.log('Geocoder failed due to: ' + status);
						return;
						
					} else if( !results[0] ) {
						
						console.log('No results found');
						return;
						
					}
					
					
					// get place
					place = results[0];
					
					var lat = place.geometry.location.lat(),
						lng = place.geometry.location.lng();
						
					
					self.doFocus( $field ).update( lat, lng ).center();
				    
				});
			    
			});
		    
		    
		    google.maps.event.addListener( this.map.marker, 'dragend', function(){
		    	
		    	// reference
			    var $field = this.map.$field;
			    
			    
		    	// vars
				var position = this.map.marker.getPosition(),
					lat = position.lat(),
			    	lng = position.lng();
			    	
				self.doFocus( $field ).update( lat, lng ).sync();
			    
			});
			
			
			google.maps.event.addListener( this.map, 'click', function( e ) {
				
				// reference
			    var $field = this.$field;
			    
			    
				// vars
				var lat = e.latLng.lat(),
					lng = e.latLng.lng();
				
				
				self.doFocus( $field ).update( lat, lng ).sync();
			
			});
			
			
	        // add to maps
	        this.maps[ this.o.id ] = this.map;
	        
		},
		
		update : function( lat, lng ){
			
			// vars
			var latlng = new google.maps.LatLng( lat, lng );
		    
		    
		    // update inputs
		    acf.val( this.$el.find('.input-lat'), lat );
		    acf.val( this.$el.find('.input-lng'), lng );
		    
			
		    // update marker
		    this.map.marker.setPosition( latlng );
		    
		    
			// show marker
			this.map.marker.setVisible( true );
		    
		    
	        // update class
	        this.$el.addClass('active');
	        
	        
	        // validation
			this.$field.removeClass('error');
			
			
	        // return for chaining
	        return this;
		},
		
		center : function(){
			
			// vars
			var position = this.map.marker.getPosition(),
				lat = this.o.lat,
				lng = this.o.lng;
			
			
			// if marker exists, center on the marker
			if( position ) {
				
				lat = position.lat();
				lng = position.lng();
				
			}
			
			
			var latlng = new google.maps.LatLng( lat, lng );
				
			
			// set center of map
	        this.map.setCenter( latlng );
	        
		},
		
		sync : function(){
			
			// reference
			var $el	= this.$el;
				
			
			// vars
			var position = this.map.marker.getPosition(),
				latlng = new google.maps.LatLng( position.lat(), position.lng() );
			
			
			this.geocoder.geocode({ 'latLng' : latlng }, function( results, status ){
				
				// validate
				if( status != google.maps.GeocoderStatus.OK ) {
					
					console.log('Geocoder failed due to: ' + status);
					return;
					
				} else if( !results[0] ) {
					
					console.log('No results found');
					return;
					
				}
				
				
				// get location
				var location = results[0];
				
				
				// update h4
				$el.find('.title h4').text( location.formatted_address );

				
				// update input
				acf.val( $el.find('.input-address'), location.formatted_address );
				
			});
			
			
			// return for chaining
	        return this;
	        
		},
		
		locate : function(){
			
			// reference
			var self = this,
				$field = this.$field;
			
			
			// Try HTML5 geolocation
			if( ! navigator.geolocation ) {
				
				alert( acf.l10n.google_map.browser_support );
				return this;
				
			}
			
			
			// show loading text
			this.$el.find('.title h4').text(acf.l10n.google_map.locating + '...');
			this.$el.addClass('active');
			
		    navigator.geolocation.getCurrentPosition(function(position){
		    	
		    	// vars
				var lat = position.coords.latitude,
			    	lng = position.coords.longitude;
			    	
				self.doFocus( $field ).update( lat, lng ).sync().center();
				
			});

				
		},
		
		
		clear : function(){
			
			// update class
	        this.$el.removeClass('active');
			
			
			// clear search
			this.$el.find('.search').val('');
			
			
			// clear inputs
			acf.val( this.$el.find('.input-address'), '' );
			acf.val( this.$el.find('.input-lat'), '' );
			acf.val( this.$el.find('.input-lng'), '' );
						
			
			// hide marker
			this.map.marker.setVisible( false );
		},
		
		edit : function(){
			
			// update class
	        this.$el.removeClass('active');
			
			
			// clear search
			var val = this.$el.find('.title h4').text();
			
			
			this.$el.find('.search').val( val ).focus();
			
		},
		
		refresh : function(){
			
			// trigger resize on div
			google.maps.event.trigger(this.map, 'resize');
			
			// center map
			this.center();
			
		},
		
		keydown: function( e ){
			
			// prevent form from submitting
			if( e.which == 13 ) {
				
				e.preventDefault();
			    
			}
			
		},
		
		blur: function(){
			
			// has a value?
			if( this.$el.find('.input-lat').val() ) {
				
				this.$el.addClass('active');
				
			}
			
		},
		
		show: function(){
			
			if( this.is_ready() ) {
				
				this.refresh();
				
			}
			
		}
		
	});

})(jQuery);
