function resizer() {
    this.resize = function(element, size) {
	this.init(element);
	element.css('font-size', this.growTo(size) + 'px');
	this.tester.remove();
    }

    this.init = function(element) {
	jQuery('#resizeroo').remove();
	this.tester = element.clone();
	this.tester.css('display', 'none');
	this.tester.css('position', 'absolute');
	this.tester.css('height', 'auto');
	this.tester.css('width', 'auto');
	jQuery('body').append(this.tester);
	this.size = 1;
	this.tester.css('font-size', this.size + 'px');
    }

    this.emitWidth = function() {
	console.log(this.tester.width());
    }

    this.grow = function() {
	this.size++;
	this.setSize();
    }

    this.setSize = function(size) {
	this.size = size;
	this.tester.css('font-size', this.size + 'px');
    }

    this.growTo = function(limit) {
	lower = 1;
	upper = limit-1;

	// do binary search going midway to determine 
	// the best size
	while( lower < upper ) {
	    midpoint = Math.ceil((upper+lower)/2);
	    this.setSize(midpoint);
	    
	    if( Math.abs(limit - this.tester.width()) <= 1) {
		// close enough
		break
	    }

	    if(this.tester.width() >= limit) {
		upper = this.size-1;
	    }
	    else {
		lower = this.size+1;
	    }	    
	}

	while(this.tester.width() > limit) {
	    this.setSize(this.size-1);
	}

	return(this.size);

    }
}


(function( $ ){
  $.fn.widtherize = function( options ) {  
      return this.each(function() {	  
	  var settings = {
              'width' : 500
	  };	  
	  if ( options ) { 
              $.extend( settings, options );
	  }
	  r = new resizer();
	  r.resize(jQuery(this), settings.width);
      });
  };
})( jQuery );