window.cmb2DateRange = window.cmb2DateRange || {};

(function(window, document, $, app, undefined){
	'use strict';

	app.init = function() {

		var $body = $( 'body' );

		$( '[data-daterange]' ).each( function() {

			var $this = $( this );
			var data = $this.data( 'daterange' );

			var options = {
				initialText       : data.buttontext,
				altFormat         : data.format,
				datepickerOptions : {
					minDate: null,
					maxDate: null
				},
			};

			$body.trigger( 'cmb2_daterange_init', { '$el' : $this, 'options' : options } );

			$this.daterangepicker( options );
		});

		$( '.cmb-type-date-range .comiseo-daterangepicker-triggerbutton' ).addClass( 'button-secondary' ).removeClass( 'comiseo-daterangepicker-top comiseo-daterangepicker-vfit' );
		$( '.comiseo-daterangepicker' ).addClass( 'cmb2-element' );

	};

	$( app.init );

})(window, document, jQuery, window.cmb2DateRange);
