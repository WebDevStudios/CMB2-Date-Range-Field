window.cmb2DateRange = window.cmb2DateRange || {};

(function(window, document, $, app, undefined){
	'use strict';

	app.init = function() {
		$( '[data-daterange]' ).each( function() {

			var $this = $( this );
			var data = $this.data();

			$this.daterangepicker({
				datepickerOptions: {
					minDate: null,
					maxDate: null,
					initialText: data.buttontext
				},
				altFormat: data.format
			});

		});

		$( '.cmb-type-date-range .comiseo-daterangepicker-triggerbutton' ).addClass( 'button-secondary' ).removeClass( 'comiseo-daterangepicker-top comiseo-daterangepicker-vfit' );
		$( '.comiseo-daterangepicker' ).wrap('<div class="cmb2-element" />');

	};

	$( app.init );

})(window, document, jQuery, window.cmb2DateRange);
