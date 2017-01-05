// -------------------------
// 		search functions
// -------------------------

function resetSearch() {
	var $searchBox = TYPO3.jQuery( '#recordSearchbox' );
	$searchBox.val( '' );
	var $resetButton = TYPO3.jQuery( '#resetSearchboxButton' ).first();
	$resetButton.hide();

	var $rows = TYPO3.jQuery( '#tx-wecmap-cache tr.address' );
	$rows.each( function( index ) {
		TYPO3.jQuery( this ).show();
	} );
	TYPO3.jQuery( '#recordCount' ).html( $rows.length );
}

function filter() {
	var $searchBox = TYPO3.jQuery( '#recordSearchbox' );
	var sword = $searchBox.val();
	if ( sword === '' ) {
		resetSearch();
		return;
	}
	var swordArray = sword.toLowerCase().split(' ');
	var $resetButton = TYPO3.jQuery( '#resetSearchboxButton' ).first();
	$resetButton.show();

	var $rows = TYPO3.jQuery( '#tx-wecmap-cache tr.address' );
	var visible = 0;
	$rows.each( function( index ) {
		var $row = TYPO3.jQuery( this );
		var $addr = TYPO3.jQuery( 'td.address', $row );
		var addr = $row.html();
		if ( swordArray.some( function( swordPart ) {
			return ( addr.toLowerCase().indexOf( swordPart ) >= 0 );
 			} ) ) {
			$row.show();
			++visible;
		} else {
			$row.hide();
		}
	} );
	TYPO3.jQuery( '#recordCount' ).html( visible + '/' + $rows.length );
}

function refreshRows() {
//### todo
return;
	var table = TYPO3.jQuery('tx-wecmap-cache');
	var rows = SortableTable.getBodyRows(table);
	rows.each(function(r,i) {
		SortableTable.addRowClass(r,i);
	});
}

function addRowClass(r,i) {
//### todo
return;
	r = TYPO3.jQuery(r)
	r.removeClassName(SortableTable.options.rowEvenClass);
	r.removeClassName(SortableTable.options.rowOddClass);
	r.addClassName(((i+1)%2 == 0 ? SortableTable.options.rowEvenClass : SortableTable.options.rowOddClass));
}

// -------------------------
// record handling functions
// -------------------------

function deleteAll() {
	// Setup the parameters and make the ajax call
	TYPO3.jQuery.ajax( {
		url: TYPO3.settings.ajaxUrls[ 'txwecmapM1::deleteAll' ],
		method: 'POST',
		success: function( response ) {
			clearTable();
	   }
	} );
}

function deleteRecord( id ) {
	// Setup the parameters and make the ajax call
	TYPO3.jQuery.ajax( {
		url: TYPO3.settings.ajaxUrls[ 'txwecmapM1::deleteSingle' ],
		method: 'POST',
		data: { record: id },
		success: function( response ) {
			clearRow( id );
	   }
	} );
}

function editRecord( id ) {
	var $elRow = TYPO3.jQuery( '#item_' + id );
	var $latEl = TYPO3.jQuery( '.latitude', $elRow ).first();
	var $longEl = TYPO3.jQuery( '.longitude', $elRow ).first();
	var latValue = $latEl.html();
	var longValue = $longEl.html();

	$latEl.data( 'orgVal', latValue );
	$longEl.data( 'orgVal', longValue );
	$latEl.html( '<input class="latForm" type="text" size="17" value="' + latValue + '"/>' );
	$longEl.html( '<input class="longForm" type="text" size="17" value="' + longValue + '"/>' );

	var $editButton = TYPO3.jQuery( 'td.editButton span.editButton', $elRow ).first();
	var $saveButton = TYPO3.jQuery( 'td.editButton span.saveButton', $elRow ).first();
	var $cancelButton = TYPO3.jQuery( 'td.editButton span.cancelButton', $elRow ).first();
	$editButton.hide();
	$saveButton.show();
	$cancelButton.show();
}

function unEdit( id, latVal, longVal ) {
	var $elRow = TYPO3.jQuery( '#item_' + id );
	var $latEl = TYPO3.jQuery( '.latitude', $elRow ).first();
	var $longEl = TYPO3.jQuery( '.longitude', $elRow ).first();

	$latEl.removeData( 'orgVal' );
	$longEl.removeData( 'orgVal' );
	$latEl.html( latVal );
	$longEl.html( longVal );

	var $editButton = TYPO3.jQuery( 'td.editButton span.editButton', $elRow ).first();
	var $saveButton = TYPO3.jQuery( 'td.editButton span.saveButton', $elRow ).first();
	var $cancelButton = TYPO3.jQuery( 'td.editButton span.cancelButton', $elRow ).first();
	$editButton.show();
	$saveButton.hide();
	$cancelButton.hide();
}


function cancelEditRecord( id ) {
	var $elRow = TYPO3.jQuery( '#item_' + id );
	var $latEl = TYPO3.jQuery( '.latitude', $elRow ).first();
	var $longEl = TYPO3.jQuery( '.longitude', $elRow ).first();

	var latValue = $latEl.data( 'orgVal' );
	var longValue = $longEl.data( 'orgVal' );
	unEdit( id, latValue, longValue );
}

function saveRecord( id ) {
	var $elRow = TYPO3.jQuery( '#item_' + id );
	var $latEl = TYPO3.jQuery( '.latForm', $elRow ).first();
	var $longEl = TYPO3.jQuery( '.longForm', $elRow ).first();

	var latValue = $latEl.val();
	var longValue = $longEl.val();
	// Setup the parameters and make the ajax call
	TYPO3.jQuery.ajax({
		url: TYPO3.settings.ajaxUrls[ 'txwecmapM1::saveRecord' ],
		method: 'POST',
		data: { record: id, latitude: latValue, longitude: longValue },
		success: function( response ) {
			unEdit( id, latValue, longValue );
		}
	} );
}

function clearRow( id ) {
	TYPO3.jQuery( '#item_' + id ).remove();
	var number = TYPO3.jQuery( '#recordCount' ).html();
	TYPO3.jQuery( '#recordCount' ).html( number - 1 );
	if ( number === 1 ) {
		TYPO3.jQuery( '#tx-wecmap-cache' ).hide();
		TYPO3.jQuery( '#noRecords' ).show();
	}
	refreshRows();
}

function clearTable() {
	TYPO3.jQuery( '#tx-wecmap-cache tbody' ).html( '' );
	TYPO3.jQuery( '#recordCount' ).html( '0' );
	TYPO3.jQuery( '#tx-wecmap-cache' ).hide();
	TYPO3.jQuery( '#noRecords' ).show();
}

function updatePagination( page ) {
	var countEl = TYPO3.jQuery( '#recordCount' );
	var number = countEl.html();

	var updater = new Ext.Updater( 'pagination' );
	updater.showLoadIndicator = false;
	updater.startAutoRefresh( 1,
	                          TYPO3.settings.ajaxUrls['txwecmapM1::updatePagination']
//### todo itemsPerPage
//	                          { page: page, itemsPerPage:'. $this->itemsPerPage .', count: number }
	                        );

}

function startGeocode() {
	Ext.get('startGeocoding').setDisplayed( false );
	Ext.get('status').setDisplayed( true );

	var updater = new Ext.Updater( 'status' );
	updater.showLoadIndicator = false;
	updater.startAutoRefresh( 1, TYPO3.settings.ajaxUrls['txwecmapM1::batchGeocode'] );
}

TYPO3.jQuery(function() {

	TYPO3.jQuery( '#tx-wecmap-cache' ).on( 'click', 'span.editButton', function( event ) {
		var $row = TYPO3.jQuery( event.target ).closest( 'tr' );
		var id = $row.data( 'cacheid' );
		editRecord( id );
	} );

	TYPO3.jQuery( '#tx-wecmap-cache' ).on( 'click', 'span.deleteButton', function( event ) {
		var $row = TYPO3.jQuery( event.target ).closest( 'tr' );
		var id = $row.data( 'cacheid' );
		deleteRecord( id );
	} );

	TYPO3.jQuery( '#tx-wecmap-cache' ).on( 'click', 'span.saveButton', function( event ) {
		var $row = TYPO3.jQuery( event.target ).closest( 'tr' );
		var id = $row.data( 'cacheid' );
		saveRecord( id );
	} );

	TYPO3.jQuery( '#tx-wecmap-cache' ).on( 'click', 'span.cancelButton', function( event ) {
		var $row = TYPO3.jQuery( event.target ).closest( 'tr' );
		var id = $row.data( 'cacheid' );
		cancelEditRecord( id );
	} );

	TYPO3.jQuery( '#deleteCache' ).on( 'click', function( event ) {
		deleteAll();
	} );

	TYPO3.jQuery( '#recordSearchbox' ).on( 'change', function( event ) {
		event.preventDefault();
		filter();
	} );

	TYPO3.jQuery( '#resetSearchboxButton' ).on( 'click', function( event ) {
		event.preventDefault();
		resetSearch();
	} );

	var $rows = TYPO3.jQuery( '#tx-wecmap-cache tr.address' );
	TYPO3.jQuery( '#recordCount' ).html( $rows.length );
	if ( $rows.length === 0 ) {
		TYPO3.jQuery( '#tx-wecmap-cache' ).hide();
		TYPO3.jQuery( '#noRecords' ).show();
	} else {
		TYPO3.jQuery( '#tx-wecmap-cache' ).show();
		TYPO3.jQuery( '#noRecords' ).hide();
	}

});
