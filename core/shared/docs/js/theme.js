(function($) {

  $(document).on('flatdoc:ready', function() {
    $("#misc, #basic").remove();

    $("pre > code").each(function() {
      var $code = $(this);
      var m = $code.text().match(/<body class='([^']*)'/);
      if (m) {
        var $q = $("<blockquote><a href='#"+m[1]+"' class='button light'>Toggle</a></blockquote>");
        $q.find('a').click(function() {
          var klass = $(this).attr('href').substr(1);
          $('body').toggleClass(klass);
          if (klass === 'big-h3') $.anchorjump('#theme-options');
          if (klass === 'large-brief') $.anchorjump('#flatdoc');

        });
        $code.after($q);
      }
    });

    jQuery('#event-list-list a').each(function(){
        var text = $(this).text().replace("()", "");
        $(this).text(text);
        var head = text.replace(/_/g, "-");
        var heading = $('#event-list-' + head).text().replace("()", "")
        $('#event-list-' + head).text(heading);
        //console.log('#event-list-' + heading);
    });

  });

})(jQuery);
