define(['jquery'], function($) {

	var Module = {

		// -------------------------
		// 		search functions
		// -------------------------
		resetSearch: function () {
			var $searchBox = $( '#recordSearchbox' ).val( '' );
			$( '#resetSearchboxButton' ).hide();

			var $rows = $( '#tx-wecmap-cache tr.address' );
			$rows.show();
			$( '#recordCount' ).html( $rows.length );
		},

		filter: function() {
			var $searchBox = $( '#recordSearchbox' );
			var sword = $searchBox.val();
			if ( sword === '' ) {
				this.resetSearch();
				return;
			}
			var swordArray = sword.toLowerCase().split(' ');
			$( '#resetSearchboxButton' ).show();
		
			var $rows = $( '#tx-wecmap-cache tr.address' );
			var visible = 0;
			$rows.each( function( index ) {
				var $row =$( this );
				var $addr = $( 'td.address', $row );
				var addr = $addr.html();
				if ( swordArray.some( function( swordPart ) {
					return ( addr.toLowerCase().indexOf( swordPart ) >= 0 );
					 } ) ) {
					$row.show();
					++visible;
				} else {
					$row.hide();
				}
			} );
			$( '#recordCount' ).html( visible + '/' + $rows.length );
		},
		
		// -------------------------
		// record handling functions
		// -------------------------

		deleteAll: function () {
			// Setup the parameters and make the ajax call
			var me = this;
			$.ajax( {
				url: TYPO3.settings.ajaxUrls[ 'txwecmapM1::deleteAll' ],
				method: 'POST',
				success: function( response ) {
					me.clearTable();
			   }
			} );
		},
		
		deleteRecord: function( id ) {
			// Setup the parameters and make the ajax call
			var me = this;
			$.ajax( {
				url: TYPO3.settings.ajaxUrls[ 'txwecmapM1::deleteSingle' ],
				method: 'POST',
				data: { record: id },
				success: function( response ) {
					me.clearRow( id );
			   }
			} );
		},
		
		editRecord: function( id ) {
			var $elRow = $( '#item_' + id );
			var $latEl = $( '.latitude', $elRow ).first();
			var $longEl = $( '.longitude', $elRow ).first();
			var latValue = $latEl.html();
			var longValue = $longEl.html();
		
			$latEl.data( 'orgVal', latValue );
			$longEl.data( 'orgVal', longValue );
			$latEl.html( '<input class="latForm" type="text" size="17" value="' + latValue + '"/>' );
			$longEl.html( '<input class="longForm" type="text" size="17" value="' + longValue + '"/>' );
		
			var $editButton = $( 'td.editButton div.editButton', $elRow ).first();
			var $saveButton = $( 'td.editButton div.saveButton', $elRow ).first();
			var $cancelButton = $( 'td.editButton div.cancelButton', $elRow ).first();
			$editButton.hide();
			$saveButton.show();
			$cancelButton.show();
		},
		
		unEdit: function( id, latVal, longVal ) {
			var $elRow = $( '#item_' + id );
			var $latEl = $( '.latitude', $elRow ).first();
			var $longEl = $( '.longitude', $elRow ).first();
		
			$latEl.removeData( 'orgVal' );
			$longEl.removeData( 'orgVal' );
			$latEl.html( latVal );
			$longEl.html( longVal );
		
			var $editButton = $( 'td.editButton div.editButton', $elRow ).first();
			var $saveButton = $( 'td.editButton div.saveButton', $elRow ).first();
			var $cancelButton = $( 'td.editButton div.cancelButton', $elRow ).first();
			$editButton.show();
			$saveButton.hide();
			$cancelButton.hide();
		},
		
		
		cancelEditRecord: function( id ) {
			var $elRow = $( '#item_' + id );
			var $latEl = $( '.latitude', $elRow ).first();
			var $longEl = $( '.longitude', $elRow ).first();
		
			var latValue = $latEl.data( 'orgVal' );
			var longValue = $longEl.data( 'orgVal' );
			this.unEdit( id, latValue, longValue );
		},
		
		saveRecord: function( id ) {
			var $elRow = $( '#item_' + id );
			var $latEl = $( '.latForm', $elRow ).first();
			var $longEl = $( '.longForm', $elRow ).first();
		
			var latValue = $latEl.val();
			var longValue = $longEl.val();
			// Setup the parameters and make the ajax call
			var me = this;
			$.ajax({
				url: TYPO3.settings.ajaxUrls[ 'txwecmapM1::saveRecord' ],
				method: 'POST',
				data: { record: id, latitude: latValue, longitude: longValue },
				success: function( response ) {
					me.unEdit( id, latValue, longValue );
				}
			} );
		},
		
		clearRow: function( id ) {
			$( '#item_' + id ).remove();
			var number = $( '#recordCount' ).html();
			$( '#recordCount' ).html( number - 1 );
			if ( number === 1 ) {
				$( '#tx-wecmap-cache' ).hide();
				$( '#noRecords' ).show();
			}
			this.refreshRows();
		},
		
		clearTable: function() {
			$( '#tx-wecmap-cache tbody' ).html( '' );
			$( '#recordCount' ).html( '0' );
			$( '#tx-wecmap-cache' ).hide();
			$( '#noRecords' ).show();
		},
		
		startGeocode: function() {
			$( '#startGeocoding' ).hide();
			$( '#status' ).show();
			$( '#bar' ).show();

			var me = this;
			$.ajax({
				url: TYPO3.settings.ajaxUrls[ 'txwecmapM1::batchGeocode' ],
				method: 'POST',
				success: function( response ) {
					var processed = response.processed;
					var total = response.total;
					var progress = Math.round( processed / ( total ? total : 1 ) * 100 ) ;
					$( '#progress' ).width( progress + '%' );
					$( '#processed' ).html( processed );
					if ( total > processed ) {
						window.setTimeout( me.startGeocode, 1000 );
					} else {
						$( '#bar' ).hide();
					}
				}
			} );
		},
		
		init: function() {
			var me = this;

			$( 'div.saveButton' ).hide();
			$( 'div.cancelButton' ).hide();

			$( '#tx-wecmap-cache' ).on( 'click', 'div.editButton', function( event ) {
				var $row = $( event.target ).closest( 'tr' );
				var id = $row.data( 'cacheid' );
				me.editRecord( id );
			} );
		
			$( '#tx-wecmap-cache' ).on( 'click', 'div.deleteButton', function( event ) {
				var $row = $( event.target ).closest( 'tr' );
				var id = $row.data( 'cacheid' );
				me.deleteRecord( id );
			} );
		
			$( '#tx-wecmap-cache' ).on( 'click', 'div.saveButton', function( event ) {
				var $row = $( event.target ).closest( 'tr' );
				var id = $row.data( 'cacheid' );
				me.saveRecord( id );
			} );
		
			$( '#tx-wecmap-cache' ).on( 'click', 'div.cancelButton', function( event ) {
				var $row = $( event.target ).closest( 'tr' );
				var id = $row.data( 'cacheid' );
				me.cancelEditRecord( id );
			} );
		
			$( '#deleteCache' ).on( 'click', function( event ) {
				me.deleteAll();
			} );
		
			$( '#recordSearchbox' ).on( 'change', function( event ) {
				event.preventDefault();
				me.filter();
			} );
		
			$( '#resetSearchboxButton' ).on( 'click', function( event ) {
				event.preventDefault();
				me.resetSearch();
			} );

			var $rows = $( '#tx-wecmap-cache tr.address' );
			$( '#recordCount' ).html( $rows.length );
			if ( $rows.length === 0 ) {
				$( '#tx-wecmap-cache' ).hide();
				$( '#noRecords' ).show();
			} else {
				$( '#tx-wecmap-cache' ).show();
				$( '#noRecords' ).hide();
			}
		
			$( '#startGeocoding' ).on( 'click', function( event ) {
				event.preventDefault();
				me.startGeocode();
			} );
		},
	};

    $( Module.init() );

	return Module;
}); 
