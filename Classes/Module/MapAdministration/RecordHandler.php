<?php
/***************************************************************
* Copyright notice
*
* (c) 2005-2009 Christian Technology Ministries International Inc.
* (c) 2011-2016 J. Bartels
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

namespace JBartels\WecMap\Module\MapAdministration;

class RecordHandler {

	var $itemsPerPage = 75;
	var $count;

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

		$tablebody = '';
		foreach($displayRows as $row) {

			// Add icon/title and ID:
			$cells = array();

			$cells[] = '<td class="address">'.$row['address'].'</td>';

			$cells[] = '<td class="latitude">'.$row['latitude'].'</td>';
			$cells[] = '<td class="longitude">'.$row['longitude'].'</td>';

			$cells[] = '<td class="editButton"><a href="#" onclick="editRecord(\''. $row['address_hash'] . '\'); return false;"><img' . \TYPO3\CMS\Backend\Utility\IconUtility::skinImg($GLOBALS['BACK_PATH'],'gfx/edit2.gif','width="11" height="12"').' title="'.$LANG->getLL('editAddress').'" alt="'.$LANG->getLL('editAddress').'" /></a></td>';
			$cells[] = '<td class="deleteButton"><a href="#" onclick="deleteRecord(\''. $row['address_hash'] . '\'); return false;"><img' . \TYPO3\CMS\Backend\Utility\IconUtility::skinImg($GLOBALS['BACK_PATH'],'gfx/garbage.gif','width="11" height="12"').' title="'.$LANG->getLL('deleteAddress').'" alt="'.$LANG->getLL('deleteAddress').'" /></a></td>';

			// Compile Row:
			$tablebody .= '<tr id="item_'. $row['address_hash'] .'">'.implode('',$cells).'</tr>';

			$this->countDisplayed++;
		}

		$output = $this->getTotalCountHeader($this->count)
		        . '<br />'
		        ;

		// Create header:
		$headerCells = array();
		$headerCells[] = '<th>'.$LANG->getLL('address').'</th>';
		$headerCells[] = '<th style="width: 6em;">'.$LANG->getLL('latitude').'</th>';
		$headerCells[] = '<th style="width: 6em;">'.$LANG->getLL('longitude').'</th>';
		$headerCells[] = '<th colspan="2">Actions</th>';

		$output .= '<table id="tx-wecmap-cache">'
		         . '<thead><tr>'. implode('',$headerCells) . '</tr></thead>'
				 . '<tbody>'.$tablebody.'</tbody>'
				 . '</table>'
				 ;

//		$pageRenderer = $GLOBALS['TBE_TEMPLATE']->getPageRenderer();

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

#			$cells[] = '<td class="editButton"><a href="#" onclick="editRecord(\''. $row['address_hash'] . '\'); return false;"><img' . \TYPO3\CMS\Backend\Utility\IconUtility::skinImg($GLOBALS['BACK_PATH'],'gfx/edit2.gif','width="11" height="12"').' title="'.$LANG->getLL('editAddress').'" alt="'.$LANG->getLL('editAddress').'" /></a></td>';
#			$cells[] = '<td class="deleteButton"><a href="#" onclick="deleteRecord(\''. $row['address_hash'] . '\'); return false;"><img' . \TYPO3\CMS\Backend\Utility\IconUtility::skinImg($GLOBALS['BACK_PATH'],'gfx/garbage.gif','width="11" height="12"').' title="'.$LANG->getLL('deleteAddress').'" alt="'.$LANG->getLL('deleteAddress').'" /></a></td>';

			$js =
			'<script>
				function getSaveCancelLinks(id, oldLat, oldLong) {
					var link = \'<a href="#" onclick="saveRecord(\\\'\'+id+\'\\\'); return false;"><img' . \TYPO3\CMS\Backend\Utility\IconUtility::skinImg($GLOBALS['BACK_PATH'],'gfx/savedok.gif','width="11" height="12"') . ' title="'.$LANG->getLL('updateAddress').'" alt="'.$LANG->getLL('updateAddress').'" /></a><a href="#" onclick="unEdit(\\\'\'+id+\'\\\',\\\'\'+oldLong+\'\\\', \\\'\'+oldLat+\'\\\'); return false;"><img'.\TYPO3\CMS\Backend\Utility\IconUtility::skinImg($GLOBALS['BACK_PATH'],'gfx/closedok.gif','width="11" height="12"') . ' title="'.$LANG->getLL('cancelUpdate') . '" alt="'.$LANG->getLL('cancelUpdate').'" /></a>\';
					return link;
				}

				function getEditLink(id) {
					var link = \'<a href="#" onclick="editRecord(\\\'\'+id+\'\\\'); return false;"><img' . \TYPO3\CMS\Backend\Utility\IconUtility::skinImg($GLOBALS['BACK_PATH'],'gfx/edit2.gif','width="11" height="12"') . ' title="'.$LANG->getLL('editAddress') . '" alt="'.$LANG->getLL('editAddress') . '" /></a>\';
					return link;
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
			'<img' . \TYPO3\CMS\Backend\Utility\IconUtility::skinImg($GLOBALS['BACK_PATH'],'gfx/garbage.gif','width="11" height="12"') . ' title="'.$LANG->getLL('deleteCache') . '" alt="'.$LANG->getLL('deleteCache') . '" />'.
			'</a>';

		return $content;
	}

	function linkSelf($addParams)	{
		return htmlspecialchars('index.php?id='.$this->pObj->id.'&showLanguage='.rawurlencode(\TYPO3\CMS\Core\Utility\GeneralUtility::_GP('showLanguage')).$addParams);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/mod1/class.tx_wecmap_recordhandler.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/mod1/class.tx_wecmap_recordhandler.php']);
}

?>