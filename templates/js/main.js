"use strict";

(function($) {
	$(document).ready(function() {
		MobileNav.init();

    if ($.fn.datetimepicker) {
      $(".mf-date").each(function() {
        $(this).parents("form").css("position", "relative");

        $(this).datetimepicker({
          locale: "de-at",
          format: "YYYY-MM-DD HH:mm:ss",
          /*widgetPositioning: {
            horizontal: "right",
            vertical: "bottom"
          },*/
          widgetParent: $(this).parents("form")
        });
      });
    }
	});
})(jQuery);

var TouchMeister = (function($) {
  var MOD = {};

  var touchStartPos = { x: 0, y: 0 };
  var touchEndPos = { x: 0, y: 0 };

  var onMoveHandlers = [];

  $(document).on("touchstart", function(e) {
    var touch = e.originalEvent.touches[0];
    touchStartPos = {
      x: touch.pageX,
      y: touch.pageY
    };
  });

  $(document).on("touchend", function(e) {
    var touch = e.originalEvent.changedTouches[0];
    touchEndPos = {
      x: touch.pageX,
      y: touch.pageY
    };

    var dir = {
      x: touchEndPos.x - touchStartPos.x,
      y: touchEndPos.y - touchStartPos.y
    };

    var magn = dir.x * dir.x + dir.y * dir.y;
    dir.x /= magn;
    dir.y /= magn;

    for (var i = 0; i < onMoveHandlers.length; ++i) {
      onMoveHandlers[i](dir);
    }
  });

  MOD.onMove = function(handler) {
    onMoveHandlers.push(handler);
  };

  return MOD;
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

    TouchMeister.onMove(function(dir) {
      if (dir.x < 0 && Math.abs(dir.x) > Math.abs(dir.y)) {
        $mobileNav.removeClass("mf-active");
        $pageWrap.removeClass("mf-page-toright");
      }
    });
  };

  return MOD;
})(jQuery);