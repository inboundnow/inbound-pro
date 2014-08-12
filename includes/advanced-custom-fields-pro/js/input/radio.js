(function($){
	
	/*
	*  Radio
	*
	*  static model and events for this field
	*
	*  @type	event
	*  @date	1/06/13
	*
	*/
	
	acf.fields.radio = {
	
		change : function( $radio ){
			
			// vars
			var $ul = $radio.closest('ul'),
				$val = $ul.find('input[type="radio"]:checked'),
				$other = $ul.find('input[type="text"]');
			
			
			if( $val.val() == 'other' )
			{
				$other.removeAttr('disabled');
				$other.attr('name', $val.attr('name'));
			}
			else
			{
				$other.attr('disabled', 'disabled');
				$other.attr('name', '');
			}
		}
	};
	
	
	/*
	*  Events
	*
	*  jQuery events for this field
	*
	*  @type	function
	*  @date	1/03/2011
	*
	*  @param	N/A
	*  @return	N/A
	*/
	
	$(document).on('change', '.acf-radio-list input[type="radio"]', function( e ){
		
		acf.fields.radio.change( $(this) );
		
	});
	

})(jQuery);
