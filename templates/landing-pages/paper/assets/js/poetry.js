function render() {
    $('#out').html('');
    var text = $('#in').val();
    lines = text.split('\n');
    
    for(i in lines) {
	var p = $('<p></p>');
	p.html(lines[i]);
	$('#out').append(p);
    }	
    $('#out p').widtherize({'width': 600});

    var chars = $('#in').val().length;
    $('#count').html(chars);
    if(chars < 100) {
	$('#counter').hide();
    }
    else {
	$('#counter').show();
    }

    if(chars < 140) {
	$('#count').removeClass('invalid');
	$('#poemit').removeAttr('disabled');
	
    }
    else {
	$('#count').addClass('invalid');
	$('#poemit').attr('disabled', 'disabled');
    }
}

// takes the id of the textarea to render and the outdiv
// to render it to
function srender(textarea, outdiv, size) {
    input = $('#' + textarea);
    output = $('#' + outdiv);
    $('#' + outdiv + ' p').widtherize({'width': size});
}

function render_mini_verse(id) {
    srender('text_'+id, 'rendered_'+id, 300);
}

function render_minis(context) {
    context.each(function() {
	render_mini_verse(this.id);
    });

    // context.live('click', function() {
    // 	document.location.href = $('a', this)[0].href;
    // });

    context.live('mouseover mouseout', function(event) {
	if (event.type == 'mouseover') {
	    $(this).addClass('hover');
	} else {
	    $(this).removeClass('hover');
	}
    });
}

function init_counter() {
}