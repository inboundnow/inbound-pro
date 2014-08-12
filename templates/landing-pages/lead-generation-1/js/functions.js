function getAffiliateFromAnchor() {

    var hash = window.location.hash.substring(1);

    if (typeof (hash) == 'string'
        && hash != ''
        && hash.substr(0, 2) == 'af'
    ) {
        var expire = new Date();
        expire.setDate(expire.getDate() + 7);
        document.cookie = "af=" + escape(hash.substr(2, hash.length)) + '; expires=' + expire.toUTCString();
    }

    return false;

}

$(document).ready(function() {
    if (window.location.hash) {
        getAffiliateFromAnchor();
    }
    if ($( "#shouts" ).length) {
        $( "#shoutmsg" ).keypress(function( event ) {
            if ( event.which == 13 ) {
                if (RB.shout.active == false) {
                    RB.shout.send();
                }
            }
        });
        RB.shout.update();
    }
    
});


var RB = {
    shout: {
        lastId: 0,
        active: false,
        autoScroll: true
    }
}

RB.shout.hint = function(msg){

    $('#shout-notify-text').text(msg);
    $('#shout-notify').show(400); 
    $('#shout-notify').delay(2000).hide(400); 

}

RB.shout.send = function(){
    var msg = $( "#shoutmsg" ).val();

    if (msg == "") {
        RB.shout.hint('We won\'t post an empty message :)');
    } else {
        $('#shoutbtn').html('...');
        $('#shoutbtn').prop("disabled",true);
        $('#shoutmsg').prop("disabled",true);
        RB.shout.active = true;
        $.post( "index.php?page=shout&action=send&lastid=" + RB.shout.lastId, { message: msg})
        .done(function( data ) {
            $json = $.parseJSON(data);

            if ($json['status'] === 'ERROR') {
                RB.shout.hint($json['errormsg']);
            } else {
                RB.shout.addNewShouts($json['shouts']);

                $('#shoutmsg').val("");
                $('#shoutmsg').focus();
            }
            $('#shoutbtn').prop("disabled",false);
            $('#shoutmsg').prop("disabled",false);
            RB.shout.active = false;
            $('#shoutbtn').html('Shout');
        });
    }
}

function getNameAndTime() {
    alert("med");
}

RB.shout.update = function(){
    $.ajax({
    url: "/shouts/" + RB.shout.lastId,
        cache: false,
        dataType: "json"
    })
    //index.php?page=shout&action=get&lastid=

    .done(function( data ) {

        RB.shout.addNewShouts(data);

        window.setTimeout(RB.shout.update, 6000);

    });
}

RB.shout.update2 = function(){
    $.ajax({
    url: "index.php?page=shout&action=get&lastid=" + RB.shout.lastId,
        cache: false,
        dataType: "json"
    })


    .done(function( data ) {

        RB.shout.addNewShouts(data);

        window.setTimeout(RB.shout.update, 6000);

    });
}

RB.shout.updateScroll = function(){
    if ($('#shoutscroll').is(":checked")){
        RB.shout.autoScroll = false;
    } else {
        RB.shout.autoScroll = true;
    }
}

RB.shout.addNewShouts = function(data){

        countNewShouts = 0;
        for(var key in data) {
            var shout = data[key];

            if (shout.id > RB.shout.lastId) {
                RB.shout.lastId = shout.id;
                shout.date_utc=shout.date_utc.replace(' ','T')+'Z';
                  
                $( "#shouts" ).append('<div class="shout-row"><div class="shout-column" style="min-width:120px; line-height: 1em;"><b class="shout-userstatus-'+ shout.userstatus + '">'+ shout.username + ':</b><br><time id="shout-timestamp-'+ shout.id + '" class="shout-timestamp" datetime="'+ shout.date_utc + '"></time></div><div class="shout-column">' + shout.message + '</div></div>');
                $( "#shout-timestamp-" + shout.id).timeago();
                //$( "#shout-timestamp-" + shout.id).livestamp(moment(shout.date));
                countNewShouts ++;
            }
        }

        if (countNewShouts > 0 && RB.shout.autoScroll == true) {
          $("#shout-container").animate({ scrollTop: $("#shout-container").prop('scrollHeight') }, 1500);
        }

}
