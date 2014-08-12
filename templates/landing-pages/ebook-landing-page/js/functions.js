jQuery(function() {
	jQuery(document).on('focusin', '.field, textarea', function() {
		if(this.title==this.value) {
			this.value = '';
		}
	}).on('focusout', '.field, textarea', function(){
		if(this.value=='') {
			this.value = this.title;
		}
	});

	jQuery(document).on('click', '.submit-button', function(){
		if(jQuery('select').find('option:selected').index() == 0) {
			alert('Select Your Answer.')
			return false; 
		}

		if(jQuery('select').find('option:selected').index() != 1) {
			alert('Your guess is wrong! Please try again.')
			return false; 
		}

		else { 
			jQuery('.step-1').hide();
			jQuery('.step-2').fadeIn(800);
			return false;
		}
	});
});