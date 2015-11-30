(function() {
	$(document).ready(function() {
		MobileNav.init();
	});
})();

var MobileNav = (function() {
	var MOD = {};

	MOD.init = function() {
		var $hamburger = $(".mf-menu .mf-hamburger");
		$hamburger.on("click", function() {
			alert("hi");
		});

	};

	return MOD;
})();