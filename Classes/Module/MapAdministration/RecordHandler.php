<?php
/***************************************************************
* Copyright notice
*
* (c) 2005-2009 Christian Technology Ministries International Inc.
* (c) 2011-2017 J. Bartels
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

	/**
	 * Displays the table with cache records
	 *
	 * @return String
	 **/
	function displayTable() {

		global $LANG;

		$limit = null;
		// Select rows:
		$displayRows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*','tx_wecmap_cache','', 'address', 'address', $limit);

		$iconEdit = \TYPO3\CMS\Backend\Utility\IconUtility::getSpriteIcon('actions-document-open', array(
			'title' => 'hallo' . $LANG->getLL('editAddress'),
			'alt' => 'welt' . $LANG->getLL('editAddress')
			)
		);
		$iconSave = \TYPO3\CMS\Backend\Utility\IconUtility::getSpriteIcon('actions-document-save', array(
			'title' => $LANG->getLL('editAddress'),
			'alt' => $LANG->getLL('editAddress')
			)
		);
		$iconCancel = \TYPO3\CMS\Backend\Utility\IconUtility::getSpriteIcon('actions-document-close', array(
			'title' => $LANG->getLL('cancelUpdate'),
			'alt' => $LANG->getLL('cancelUpdate')
			)
		);

		$iconDelete = \TYPO3\CMS\Backend\Utility\IconUtility::getSpriteIcon('actions-edit-delete', array(
			'title' => $LANG->getLL('deleteAddress'),
			'alt' => $LANG->getLL('deleteAddress')
			)
		);

		$tablebody = '';
		foreach($displayRows as $row) {

			// Add icon/title and ID:
			$cells = array();

			$cells[] = '<td class="address">'.htmlspecialchars( $row['address'] ).'</td>';

			$cells[] = '<td class="latitude">'.htmlspecialchars( $row['latitude'] ).'</td>';
			$cells[] = '<td class="longitude">'.htmlspecialchars( $row['longitude'] ).'</td>';

			$cells[] = '<td class="editButton">' .
			             '<span class="editButton">' . $iconEdit . '</span>' .
			             '<span class="saveButton" style="display: none;">' . $iconSave . '</span>' .
			             '<span class="cancelButton" style="display: none;">' . $iconCancel . '</span>' .
			           '</td>';
			$cells[] = '<td class="deleteButton"><span class="deleteButton">' . $iconDelete . '</span></td>';

			// Compile Row:
			$tablebody .= '<tr id="item_'. $row['address_hash'] .'" data-cacheid="'. $row['address_hash'] .'" class="address">'.implode('',$cells).'</tr>';
		}

		$output = $this->getTotalCountHeader()
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
				 . '<div id="noRecords" style="display:none">' . $LANG->getLL('noRecords') . '</div>'
				 ;

		return $output;
	}

	/**
	 * Shows a search box to filter cache records
	 *
	 * @return String
	 **/
	function displaySearch() {
		global $LANG;

		$iconFilter = \TYPO3\CMS\Backend\Utility\IconUtility::getSpriteIcon( 'actions-document-save' );

		$content = '<div><input id="recordSearchbox" type="text" placeholder="'.$LANG->getLL('searchFilter').'" size="20"/><button id="resetSearchboxButton" style="display: none;">'.$LANG->getLL('clearFilter').'</button></div>';
		return $content;
	}


	/**
	 * Returns the header part that allows to delete all records and shows the
	 * total number of records
	 *
	 * @return String
	 **/
	function getTotalCountHeader() {
		global $LANG;
		$iconDelete = \TYPO3\CMS\Backend\Utility\IconUtility::getSpriteIcon('actions-edit-delete', array(
			'title' => $LANG->getLL('deleteCache'),
			'alt' => $LANG->getLL('deleteCache')
			)
		);
		$content = $LANG->getLL('totalCachedAddresses') .
			': <strong><span id="recordCount">?</span></strong><span id="deleteCache">' . $iconDelete . '</span>';

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