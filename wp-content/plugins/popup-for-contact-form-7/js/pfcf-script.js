jQuery( document ).ready(function() {
  
jQuery(".js-modal-close, .modal-overlay").click(function() {
 jQuery(".modal-box, .modal-overlay").fadeOut(500, function() {
   jQuery(".modal-overlay").remove();
  });
});
 
jQuery(window).resize(function() {
  jQuery(".modal-box").css({
    top: (jQuery(window).height() - jQuery(".modal-box").outerHeight()) / 2,
    left: (jQuery(window).width() - jQuery(".modal-box").outerWidth()) / 2
  });
});
 
jQuery(window).resize();

});
