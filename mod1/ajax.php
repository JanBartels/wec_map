<?php
/***************************************************************
* Copyright notice
*
* (c) 2014 j.bartels
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

/**
 * Module 'WEC Map Admin' for the 'wec_map' extension.
 *
 * @author	j.bartels
 * @package	TYPO3
 * @subpackage	tx_wecmap
 */
class  tx_wecmap_module1_ajax {

	/*************************************************************************
	 *
	 * 		AJAX functions
	 *
	 ************************************************************************/

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$$params: ...
	 * @param	[type]		$ajaxObj: ...
	 * @return	[type]		...
	 */
	function ajaxDeleteAll($params, &$ajaxObj) {
		tx_wecmap_cache::deleteAll();
		$ajaxObj->addContent('content', '');
	}

	function ajaxDeleteSingle($params, &$ajaxObj) {
		$hash = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('record');
		tx_wecmap_cache::deleteByUID($hash);  // $hash is escaped in deleteByUID()
		$ajaxObj->addContent('content', '');
	}

	function ajaxUpdatePagination($params, &$ajaxObj) {
		$page = intval(\TYPO3\CMS\Core\Utility\GeneralUtility::_GP('page'));
		$itemsPerPage = intval(\TYPO3\CMS\Core\Utility\GeneralUtility::_GP('itemsPerPage'));
		$count = intval(\TYPO3\CMS\Core\Utility\GeneralUtility::_GP('count'));

		$pages = ceil(($count-1)/$itemsPerPage);
		if($pages == 1) {
			$ajaxObj->addContent('content', '');
			return;
		}

		$content = array();
		$content[] = '<div id="pagination">';

		if($page !== 1) {
			$content[] = '<a href="?page='. ($page-1) .'">Previous</a>';
		} else {
			$content[] = '<span style="color: gray;">Previous</span>';
		}

		for ( $i=0; $i < $pages; $i++ ) {
			if($page == ($i+1)) {
				$content[] = '<span style="color: gray;">'.($i+1).'</span>';
			} else {
				$content[] = '<a href="?page='. ($i+1) .'">'. ($i+1) .'</a>';
			}
		}

		if($page !== $pages) {
			$content[] = '<a href="?page='. ($page+1) .'">Next</a>';
		} else {
			$content[] = '<span style="color: gray;">Next</span>';
		}

		$content[] = '</div>';
		$ajaxObj->addContent('content', implode(' ', $content) );
	}

	function ajaxSaveRecord($params, &$ajaxObj) {
		$hash = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('record');
		$latitude = floatval(\TYPO3\CMS\Core\Utility\GeneralUtility::_GP('latitude'));
		$longitude = floatval(\TYPO3\CMS\Core\Utility\GeneralUtility::_GP('longitude'));

		tx_wecmap_cache::updateByUID($hash, $latitude, $longitude);   // $hash is escaped in updateByUID()
		$ajaxObj->addContent('content', '');
	}

	function ajaxBatchGeocode($params, &$ajaxObj) {

		$batchGeocode = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_wecmap_batchgeocode');

		// add all tables to check which ones need geocoding and do it
		$batchGeocode->addAllTables();
		$batchGeocode->geocode();

		$processedAddresses = $batchGeocode->processedAddresses();
		$totalAddresses = $batchGeocode->recordCount();

		$content = self::getStatusBar($processedAddresses, $totalAddresses);
		$ajaxObj->addContent('content', $content);
	}


	/**
	 * Static function for displaying the status bar and related text.
	 *
	 * @param		integer		The number of addresses the Geocoder has processed.
	 * @param		integer		The total number of addresses.
	 * @param		boolean		True/false value for visiblity of the status bar.
	 * @return		string		HTML output.
	 */
	static function getStatusBar($processedAddresses, $totalAddresses, $visible=true) {
		global $LANG, $BE_USER;

		if($totalAddresses == 0) {
			$progressBarWidth = 0;
		} else {
			$progressBarWidth = round($processedAddresses / $totalAddresses * 100);
		}

		if(!is_object($LANG)) {
			#require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('lang').'lang.php');
			$LANG = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('language');
			$LANG->init($BE_USER->uc['lang']);
		}
		$LANG->includeLLFile('EXT:wec_map/mod1/locallang.xml');

		$content = array();
		if($visible) {
			$content[] = '<div id="status" style="margin-bottom: 5px;">';
		} else {
			$content[] = '<div id="status" style="margin-bottom: 5px; display:none;">';

		}


		$content[] = '<div id="bar" style="width:300px; height:20px; border:1px solid black">
						<div id="progress" style="width:'.$progressBarWidth.'%; height:20px; background-color:red"></div>
					</div>
					<p>'.$LANG->getLL('processedStart').' '.$processedAddresses.' '.$LANG->getLL('processedMid').' '.$totalAddresses.'.</p>';

		$content[] = '</div>';

		return implode(chr(10), $content);

	}


}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/mod1/index.php'])	{
include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/mod1/index.php']);
}

?>