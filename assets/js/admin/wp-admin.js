jQuery(document).ready(function () {

  if (inboundCounts.counts.extensions > 0 ) {
      var outterSpan = jQuery('<span>').addClass('update-plugins count-5');
      var innerSpan = jQuery('<span>').addClass('plugin-count').text(inboundCounts.counts.extensions);
      outterSpan.append(innerSpan);
      outterSpan.css('margin-left','5px');
      outterSpan.appendTo( 'a[href$="admin.php?page=inbound-manage-extensions"]');
      jQuery('input[value="needs-update"]').click();
  }

  if (inboundCounts.counts.templates > 0 ) {
      var outterSpan = jQuery('<span>').addClass('update-plugins count-5');
      var innerSpan = jQuery('<span>').addClass('plugin-count').text(inboundCounts.counts.extensions);
      outterSpan.append(innerSpan);
      outterSpan.css('margin-left','5px');
      outterSpan.appendTo( 'a[href$="admin.php?page=inbound-manage-templates"]');
      jQuery('input[value="needs-update"]').click();
  }

});