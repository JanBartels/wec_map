<?php
/***************************************************************
* Copyright notice
*
* (c) 2005-2009 Christian Technology Ministries International Inc.
* All rights reserved
*
* This file is part of the Web-Empowered Church (WEC)
* (http://WebEmpoweredChurch.org) ministry of Christian Technology Ministries
* International (http://CTMIinc.org). The WEC is developing TYPO3-based
* (http://typo3.org) free software for churches around the world. Our desire
* is to use the Internet to help offer new life through Jesus Christ. Please
* see http://WebEmpoweredChurch.org/Jesus.
*
* You can redistribute this file and/or modify it under the terms of the
* GNU General Public License as published by the Free Software Foundation;
* either version 2 of the License, or (at your option) any later version.
*
* The GNU General Public License can be found at
* http://www.gnu.org/copyleft/gpl.html.
*
* This file is distributed in the hope that it will be useful for ministry,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* This copyright notice MUST APPEAR in all copies of the file!
***************************************************************/

class tx_wecmap_recordhandler {

	var $itemsPerPage = 75;
	var $count;

	/**
	 * PHP4 constructor
	 *
	 * @return void
	 **/
	function tx_wecmap_recordhandler($count) {
		$this->__construct($count);
	}

	/**
	 * PHP5 constructor
	 *
	 * @return void
	 **/
	function __construct($count) {
		$this->count = $count;
	}

	/**
	 * Displays the table with cache records
	 *
	 * @return String
	 **/
	function displayTable() {

		if($this->count == 0) {
			$content = $this->getTotalCountHeader(0).'<br />';
			$content .= 'No Records Found.';
			return $content;
		}

		global $LANG;

		$limit = null;
		// Select rows:
		$displayRows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*','tx_wecmap_cache','', 'address', 'address', $limit);

		foreach($displayRows as $row) {

			// Add icon/title and ID:
			$cells = array();

			$cells[] = '<td class="address">'.$row['address'].'</td>';

			$cells[] = '<td class="latitude">'.$row['latitude'].'</td>';
			$cells[] = '<td class="longitude">'.$row['longitude'].'</td>';

			$cells[] = '<td class="editButton"><a href="#" onclick="editRecord(\''. $row['address_hash'] .'\'); return false;"><img'.t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'],'gfx/edit2.gif','width="11" height="12"').' title="'.$LANG->getLL('editAddress').'" alt="'.$LANG->getLL('editAddress').'" /></a></td>';
			$cells[] = '<td class="deleteButton"><a href="#" onclick="deleteRecord(\''. $row['address_hash'] .'\'); return false;"><img'.t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'],'gfx/garbage.gif','width="11" height="12"').' title="'.$LANG->getLL('deleteAddress').'" alt="'.$LANG->getLL('deleteAddress').'" /></a></td>';

			// Compile Row:
			$output.= '
				<tr id="item_'. $row['address_hash'] .'" class="bgColor'.($cc%2 ? '-20':'-10').'">
					'.implode('
					',$cells).'
				</tr>';
			$cc++;

			$this->countDisplayed++;
		}

		// Create header:
		$headerCells = array();
		$headerCells[] = '<th>'.$LANG->getLL('address').'</th>';
		$headerCells[] = '<th style="width: 100px;">'.$LANG->getLL('latitude').'</th>';
		$headerCells[] = '<th style="width: 100px;">'.$LANG->getLL('longitude').'</th>';
		$headerCells[] = '<th colspan="2">Actions</th>';

		$output = '
			<thead class="bgColor5 tableheader"><tr>
				'.implode('
				',$headerCells).'
			</tr></thead>'.$output;

		$output = $this->getTotalCountHeader($this->count).
		'<br /><div id="recordTable">'.
		// $pager.
		'<br/>'.
		'<table border="0" cellspacing="1" cellpadding="3" id="tx-wecmap-cache" class="sortable">'.$output.'</table></div>';

		return $output;
	}

	/**
	 * Shows a search box to filter cache records
	 *
	 * @return String
	 **/
	function displaySearch() {
		global $LANG;
		$content = '<div><input id="recordSearchbox" type="text" value="'.$LANG->getLL('searchFilter').'" size="20" onblur="resetSearchbox()" onfocus="clearSearchbox()" onkeyup="filter()"/><span id="resetSearchboxButton"></span></div>';
		return $content;
	}

	/**
	 * Returns the JS functions for our AJAX stuff
	 *
	 * @return String
	 **/
	function getJS() {
		global $LANG;
		$recordHandlerPath = t3lib_extMgm::extRelPath('wec_map') . 'mod1/tx_wecmap_recordhandler_ai.php';
		$js = '<script type="text/javascript" src="'.t3lib_div::getIndpEnv('TYPO3_SITE_URL').'typo3/contrib/prototype/prototype.js"></script>'.chr(10).
			  '<script type="text/javascript" src="' . $GLOBALS['BACK_PATH'] . '../' . t3lib_extMgm::siteRelPath('wec_map') . 'contrib/tablesort/fastinit.js"></script>'.chr(10).
			  '<script type="text/javascript" src="' . $GLOBALS['BACK_PATH'] . '../' . t3lib_extMgm::siteRelPath('wec_map') . 'contrib/tablesort/tablesort.js"></script>'.chr(10).
			  '<script type="text/javascript">
				SortableTable.setup({ rowEvenClass : \'bgColor-20\', rowOddClass : \'bgColor-10\'})

			  </script>'.chr(10).
			'<script>

				// -------------------------
				// 		search functions
				// -------------------------

				function resetSearchbox() {
					if($F(\'recordSearchbox\') == "") {
						$(\'recordSearchbox\').value = "Filter records...";
					}
				}

				function clearSearchbox() {
					if($F(\'recordSearchbox\') == "Filter records...") {
						$(\'recordSearchbox\').clear();
					}
				}

				function resetSearch() {
					$(\'recordSearchbox\').value = "Filter records...";
					$(\'resetSearchboxButton\').update();
					var addresses = $(\'recordTable\').getElementsBySelector(\'.address\');
					addresses.each(function(address) { address.parentNode.show()});
				}

				function filter() {
					$(\'resetSearchboxButton\').update("<a href=\"#\" onclick=\"resetSearch(); return false;\">&nbsp;Clear</a>");
					sword = $F(\'recordSearchbox\');
					var addresses = $(\'recordTable\').getElementsBySelector(\'.address\');
					result = addresses.partition(function(n) {return matches(n, sword)});
					// updateCount(result[0].size());
					result[0].each(function(address) { address.parentNode.show()});
					result[1].each(function(address) { address.parentNode.hide()});
				}

				function matches(element, sword) {
					var array = sword.split(" ");
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
					var countEl = $(\'recordCount\');
					var number = countEl.innerHTML;
					$(\'recordCount\').update();
					$(\'recordCount\').update(count+\'/\'+number);
				}

				// -------------------------
				// record handling functions
				// -------------------------

				function deleteAll() {
					// Setup the parameters and make the ajax call
					var pars = \'?cmd=deleteAll\';
				    var myAjax = new Ajax.Updater(\'deleteAllStatus\', \'' . $recordHandlerPath .'\',
				          {method: \'post\', parameters: pars, onComplete:clearTable});
				}

				function deleteRecord(id) {
					// Setup the parameters and make the ajax call
					var pars = \'?cmd=deleteSingle&record=\'+id;
				    var myAjax = new Ajax.Updater(\'deleteAllStatus\', \'' . $recordHandlerPath .'\',
				          {method: \'post\', parameters: pars, onComplete:clearRow(id)});
				}

				function editRecord(id) {
					var longitudes = $(\'item_\'+id).getElementsByClassName(\'longitude\');
					var latitudes = $(\'item_\'+id).getElementsByClassName(\'latitude\');
					var editButtons = $(\'item_\'+id).getElementsByClassName(\'editButton\');
					var longitude = longitudes[0];
					var latitude = latitudes[0];
					var editButton = editButtons[0];
					var links = getSaveCancelLinks(id, latitude.innerHTML, longitude.innerHTML);
					latitude.update(\'<input class="latForm" type="text" size="17" value="\'+latitude.innerHTML+\'"/>\');
					longitude.update(\'<input class="longForm" type="text" size="17" value="\'+longitude.innerHTML+\'"/>\');
					editButton.update(links);
				}

				function refreshRows() {
					var table = $(\'tx-wecmap-cache\');
					var rows = SortableTable.getBodyRows(table);
					rows.each(function(r,i) {
						SortableTable.addRowClass(r,i);
					});
				}

				function addRowClass(r,i) {
					r = $(r)
					r.removeClassName(SortableTable.options.rowEvenClass);
					r.removeClassName(SortableTable.options.rowOddClass);
					r.addClassName(((i+1)%2 == 0 ? SortableTable.options.rowEvenClass : SortableTable.options.rowOddClass));
				}

				function saveRecord(id) {
					var longEl = $(\'item_\'+id).getElementsBySelector(\'.longForm\');
					var longValue = $F(longEl[0]);
					var lat = $(\'item_\'+id).getElementsBySelector(\'.latForm\');
					var latValue = $F(lat[0]);
					var editButtons = $(\'item_\'+id).getElementsByClassName(\'editButton\');
					var editButton = editButtons[0];
					var link = getEditLink(id);
					editButton.update(link);
					// Setup the parameters and make the ajax call
					var pars = \'?cmd=saveRecord&record=\'+id+\'&latitude=\'+latValue+\'&longitude=\'+longValue;
				    var myAjax = new Ajax.Updater(\'deleteAllStatus\', \'' . $recordHandlerPath .'\',
				          {method: \'post\', parameters: pars, onComplete:unEdit(id,longValue,latValue)});
				}

				function unEdit(id, longVal, lat) {
					var longitudes = $(\'item_\'+id).getElementsByClassName(\'longitude\');
					var latitudes = $(\'item_\'+id).getElementsByClassName(\'latitude\');
					var editButtons = $(\'item_\'+id).getElementsByClassName(\'editButton\');
					var longitude = longitudes[0];
					var latitude = latitudes[0];
					var editButton = editButtons[0];
					var link = getEditLink(id);
					longitude.update(longVal);
					latitude.update(lat);
					editButton.update(link);

				}

				function getSaveCancelLinks(id, oldLat, oldLong) {
					var link = \'<a href="#" onclick="saveRecord(\\\'\'+id+\'\\\'); return false;"><img'.t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'],'gfx/savedok.gif','width="11" height="12"').' title="'.$LANG->getLL('updateAddress').'" alt="'.$LANG->getLL('updateAddress').'" /></a><a href="#" onclick="unEdit(\\\'\'+id+\'\\\',\\\'\'+oldLong+\'\\\', \\\'\'+oldLat+\'\\\'); return false;"><img'.t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'],'gfx/closedok.gif','width="11" height="12"').' title="'.$LANG->getLL('cancelUpdate').'" alt="'.$LANG->getLL('cancelUpdate').'" /></a>\';
					return link;
				}

				function getEditLink(id) {
					var link = \'<a href="#" onclick="editRecord(\\\'\'+id+\'\\\'); return false;"><img'.t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'],'gfx/edit2.gif','width="11" height="12"').' title="'.$LANG->getLL('editAddress').'" alt="'.$LANG->getLL('editAddress').'" /></a>\';
					return link;
				}

				function clearRow(id) {
					$(\'item_\'+id).remove();
					var count = $(\'recordCount\');
					var number = count.innerHTML;

					if((number-1)%'. $this->itemsPerPage .' == 0) {
						var page = Math.floor(number/'. $this->itemsPerPage .');
						updatePagination(page);
					}

					$(\'recordCount\').update(number-1);
					//SortableTable.load();
					refreshRows();

				}

				function clearTable() {
					var count = $(\'recordCount\');
					count.update("0");
					var status = $(\'recordTable\');
					status.update("No Records Found.");
				}

				function updatePagination(page) {
					var count = $(\'recordCount\');
					var number = count.innerHTML;
					var pars = \'?cmd=updatePagination&page=\'+page+\'&itemsPerPage='. $this->itemsPerPage .'&count=\'+number;
				    var myAjax = new Ajax.Updater(\'pagination\', \'' . $recordHandlerPath .'\',
				          {method: \'post\', parameters: pars});
				}
			</script>';

		return $js;
	}

	/**
	 * Returns the header part that allows to delete all records and shows the
	 * total number of records
	 *
	 * @return String
	 **/
	function getTotalCountHeader($count) {
		global $LANG;
		$content = $LANG->getLL('totalCachedAddresses') .
			': <strong><span id="recordCount">'.$this->count.'</span></strong> '.
			'<a href="#" onclick="deleteAll(); return false;">'.
			'<img'.t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'],'gfx/garbage.gif','width="11" height="12"').' title="'.$LANG->getLL('deleteCache').'" alt="'.$LANG->getLL('deleteCache').'" />'.
			'</a>';

		return $content;
	}

	function linkSelf($addParams)	{
		return htmlspecialchars('index.php?id='.$this->pObj->id.'&showLanguage='.rawurlencode(t3lib_div::_GP('showLanguage')).$addParams);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/mod1/class.tx_wecmap_recordhandler.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/mod1/class.tx_wecmap_recordhandler.php']);
}

?>