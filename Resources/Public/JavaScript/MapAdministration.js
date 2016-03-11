// -------------------------
// 		search functions
// -------------------------

function resetSearchbox() {
	var el = Ext.get('recordSearchbox');
	if ( el.getValue() == '' ) {
		el.set( {value:'Filter records...'});
		Ext.get('resetSearchboxButton').update('');
	}
}

function clearSearchbox() {
	var el = Ext.get('recordSearchbox');
	if ( el.getValue() == 'Filter records...' ) {
		el.set( {value:''});
		Ext.get('resetSearchboxButton').update('<a href="#" onclick="filter(); return false;">&nbsp;Filter</a>');
	}
}

function resetSearch() {
	var el = Ext.get('recordSearchbox');
	el.set( {value:'Filter records...'});
	Ext.get('resetSearchboxButton').update('');

	var rows = $('recordTable').select('tr .address');
	rows.each(function(row) { row.show()});
}

function filter() {
	Ext.get('resetSearchboxButton').update('<a href="#" onclick="resetSearch(); return false;">&nbsp;Clear</a>');
	sword = Ext.get('recordSearchbox').getValue();
	var addresses = Ext.get('recordTable').select('.address');
	result = addresses.partition(function(n) {return matches(n, sword)});
	updateCount(result[0].size());
	result[0].each(function(address) { address.parentNode.show()});
	result[1].each(function(address) { address.parentNode.hide()});
}

function matches(element, sword) {
	var array = sword.split(' ');
	retValue = true;
	array.each(function(swordPart) {
		if(element.innerHTML.toLowerCase().indexOf(swordPart.toLowerCase()) == -1) {
			retValue = false;
			throw $break;
		};
	}
	);
	if(retValue === false ) {
		return false;
	} else {
		return true;
	}
}

function updateCount(count) {
	var countEl = Ext.get('recordCount');
	var number = countEl.dom.innerHTML;
	Ext.get('recordCount').update(count+'/'+number);
}

// -------------------------
// record handling functions
// -------------------------

function deleteAll() {
	// Setup the parameters and make the ajax call
	Ext.Ajax.request({
		url: TYPO3.settings.ajaxUrls['txwecmapM1::deleteAll'],
		method: 'POST',
		success: function(response, opts) {
			clearTable();
	   }
	});
}

function deleteRecord(id) {
	// Setup the parameters and make the ajax call
	Ext.Ajax.request({
		url: TYPO3.settings.ajaxUrls['txwecmapM1::deleteSingle'],
		method: 'POST',
		params: { record: id },
		success: function(response, opts) {
			clearRow(id);
	   }
	});
}

function editRecord(id) {
	var elRow = Ext.get('item_'+id);
	var longEl = elRow.select('.longitude').first();
	var latEl = elRow.select('.latitude').first();
	var editButton = elRow.select('.editButton').first();
	var longValue = longEl.dom.innerHTML;
	var latValue = latEl.dom.innerHTML;
	var links = getSaveCancelLinks(id, latValue, longValue);
	latEl.update( '<input class="latForm" type="text" size="17" value="'+latValue+'"/>' );
	longEl.update( '<input class="longForm" type="text" size="17" value="'+longValue+'"/>' );
	editButton.update( links );
}

function refreshRows() {
//### todo
return;
	var table = $('tx-wecmap-cache');
	var rows = SortableTable.getBodyRows(table);
	rows.each(function(r,i) {
		SortableTable.addRowClass(r,i);
	});
}

function addRowClass(r,i) {
//### todo
return;
	r = $(r)
	r.removeClassName(SortableTable.options.rowEvenClass);
	r.removeClassName(SortableTable.options.rowOddClass);
	r.addClassName(((i+1)%2 == 0 ? SortableTable.options.rowEvenClass : SortableTable.options.rowOddClass));
}

function saveRecord(id) {
	var elRow = Ext.get('item_'+id);
	var longEl = elRow.select('.longForm').first();
	var latEl = elRow.select('.latForm').first();
	var longValue = longEl.getValue();
	var latValue = latEl.getValue();
	// Setup the parameters and make the ajax call
	Ext.Ajax.request({
		url: TYPO3.settings.ajaxUrls['txwecmapM1::saveRecord'],
		method: 'POST',
		params: { record: id, latitude: latValue, longitude: longValue },
		success: function(response, opts) {
			unEdit(id,longValue,latValue);
		}
	});
}

function unEdit(id, longVal, latVal) {
	var elRow = Ext.get('item_'+id);
	var longEl = elRow.select('.longitude').first();
	var latEl = elRow.select('.latitude').first();
	var editButton = elRow.select('.editButton').first();
	var link = getEditLink(id);
	longEl.update( longVal );
	latEl.update( latVal );
	editButton.update( link );
}

function clearRow(id) {
	Ext.get('item_'+id).remove();
	var count = Ext.get('recordCount');
	var number = count.dom.innerHTML;

//### todo itemsPerPage
//	if((number-1)%'. $this->itemsPerPage .' == 0) {
//		var page = Math.floor(number/'. $this->itemsPerPage .');
//		updatePagination(page);
//	}

	Ext.get('recordCount').update(number-1);
	//SortableTable.load();
	refreshRows();

}

function clearTable() {
	Ext.get('recordCount').update('0');
	Ext.get('recordTable').update('No Records Found.');
}

function updatePagination(page) {
	var countEl = Ext.get('recordCount');
	var number = countEl.dom.innerHTML;

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
