"use strict";

(function($) {
	$(document).ready(function() {
		MobileNav.init();

    if ($.fn.datetimepicker) {
      $(".mf-date").each(function() {
        $(this).datetimepicker({
          locale: "de-at",
          format: "YYYY-MM-DD HH:mm:ss"
        });
      });
    }
	});
})(jQuery);

var MobileNav = (function($) {
	var MOD = {};

	MOD.init = function() {
		var $mobileNav = $(".mf-mobile-nav");
		var $pageWrap = $(".mf-page-wrap");
		var $hamburger = $(".mf-menu .mf-hamburger");

		$hamburger.on("click", function() {
			$mobileNav.toggleClass("mf-active");
			$pageWrap.toggleClass("mf-page-toright");
		});
	};

	return MOD;
})(jQuery);

var TouchMeister = (function($) {
  var MOD = {};

  var touchStartPos = { x: 0, y: 0 };

  $(document).on("touchstart", function(e) {
    var touch = e.changedTouches[0];
    alert(touch.pageX);
  })
})(jQuery);