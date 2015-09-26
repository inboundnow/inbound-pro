jQuery( document ).on ('ready' , function() {

    var theHeight = jQuery(".main").height() + 100;
    jQuery('.left,.right,.main').height(theHeight);
});