"use strict";

(function() {
	$(document).ready(function() {
		MobileNav.init();
	});
})();

var MobileNav = (function() {
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
})();