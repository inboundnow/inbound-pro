(function() {
  $(function() {
    $('.widget-link-remove').on("click", function() {
      $(this).closest('.widget').slideUp("fast");
      return false;
    });

    return $('.is-dropdown-menu').on("click", function() {
      $(this).next("ul").slideToggle('fast', function() {
        return $(this).closest("li").toggleClass('active');
      });
      return false;
    });
  });

}).call(this);
