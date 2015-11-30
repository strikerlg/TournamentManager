(function() {
	$(document).ready(function() {
		MobileNav.init();
	});
})();

var MobileNav = (function() {
	var MOD = {};

	MOD.init = function() {
		var $hamburger = $(".mf-menu .mf-hamburger");
		var $mobileNav = $(".mf-mobile-nav");
		$hamburger.on("click", function() {
			$mobileNav.toggleClass("mf-active");
			$(".mf-page-wrap").toggleClass("mf-page-toright");
		});
	};

	return MOD;
})();