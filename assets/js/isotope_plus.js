(function($){

	var Isotope = {
		init: function () {
			this.initRankingTableSorter();
			this.initPdfViewer();
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
		},
		initPdfViewer: function() {

		}
	};

	$(document).ready(function () {
		Isotope.init();
	});

}(jQuery));
