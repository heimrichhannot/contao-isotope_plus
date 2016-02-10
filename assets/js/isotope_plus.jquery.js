(function($){

	ISOTOPE_PLUS = {
		init: function () {
			this.initRankingTableSorter();
		},
		initRankingTableSorter: function() {
			$('.mod_iso_product_ranking table').tablesorter();
		}
	};

	$(document).ready(function () {
		ISOTOPE_PLUS.init();
	});

}(jQuery));
