(function( $ ) {
  "use strict";

jQuery(document).ready(function($){

  // Note: window.confirm() here is purely a UX convenience (prevents accidental
  // clicks). The actual protection of the reset action happens server-side in
  // cookies-with-ease.php via wp_nonce_field()/check_admin_referer() and current_user_can()
  // - an attacker can bypass this client-side dialog at will.
  $(".confirm").click(function() {
    return window.confirm("Reset to default settings?");
  });

  $(".cookieswithease-scheme-pill input[type=radio]").change(function() {
    $(".cookieswithease-scheme-option").removeClass("is-active");
    $(this).closest(".cookieswithease-scheme-option").addClass("is-active");
  });

});

})(jQuery);
