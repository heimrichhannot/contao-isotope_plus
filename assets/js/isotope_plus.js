var Isotope = {};


(function($){

	Isotope = {
		init: function () {
			this.initRankingTableSorter();
		},
		initRankingTableSorter: function() {
			$('.mod_iso_product_ranking table').tablesorter();
		},
		/**
		 * Toggle the address fields
		 * @param object
		 * @param string
		 */
		toggleAddressFields : function(el, id) {
			if (el.value == '0' && el.checked) {
				document.getElementById(id).style.display = 'block';
			} else {
				document.getElementById(id).style.display = 'none';
			}
		}
	};

	$(document).ready(function () {
		Isotope.init();
	});

}(jQuery));
