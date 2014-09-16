/* jQuery functions for the RWD calc */

$('input').keyup(function(){ 
	var firstValue = parseFloat($('#first').val());
	var secondValue = parseFloat($('#second').val());
	var thirdValue = parseFloat($('#third').val());
	$('#added').html(firstValue / secondValue * thirdValue);
});
