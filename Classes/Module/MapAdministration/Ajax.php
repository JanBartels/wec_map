<?php
/***************************************************************
* Copyright notice
*
* (c) 2014-2017 j.bartels
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

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Module 'WEC Map Admin' for the 'wec_map' extension.
 *
 * @author	j.bartels
 * @package	TYPO3
 * @subpackage	tx_wecmap
 */
class  Ajax {

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
	function ajaxDeleteAll(ServerRequestInterface $request, ResponseInterface $response) {
		\JBartels\WecMap\Utility\Cache::deleteAll();
		$response->getBody()->write( json_encode( [ 'status' => 'ok' ] ) );
        return $response;
	}

	function ajaxDeleteSingle(ServerRequestInterface $request, ResponseInterface $response) {
		$hash = $request->getParsedBody()['record'];
		\JBartels\WecMap\Utility\Cache::deleteByUID($hash);  // $hash is escaped in deleteByUID()
		$response->getBody()->write( json_encode( [ 'status' => 'ok' ] ) );
        return $response;
	}

	function ajaxSaveRecord(ServerRequestInterface $request, ResponseInterface $response) {
		$hash = $request->getParsedBody()['record'];
		$latitude = floatval($request->getParsedBody()['latitude']);
		$longitude = floatval($request->getParsedBody()['longitude']);

		\JBartels\WecMap\Utility\Cache::updateByUID($hash, $latitude, $longitude);   // $hash is escaped in updateByUID()
		$response->getBody()->write( json_encode( [ 'status' => 'ok' ] ) );
        return $response;
	}

	function ajaxBatchGeocode(ServerRequestInterface $request, ResponseInterface $response) {

		$batchGeocode = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\JBartels\WecMap\Module\MapAdministration\BatchGeocode::class);

		// add all tables to check which ones need geocoding and do it
		$batchGeocode->addAllTables();
		$batchGeocode->geocode();

		$processedAddresses = $batchGeocode->processedAddresses();
		$totalAddresses = $batchGeocode->recordCount();

		$content = self::getStatusBar($processedAddresses, $totalAddresses);
		$response->getBody()->write($content);
        return $response;
	}

	function ajaxListRecords(ServerRequestInterface $request, ResponseInterface $response) {
		// Select rows:
		$limit = null;
		$displayRows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*','tx_wecmap_cache','', 'address', 'address', $limit);

		$records = array();
		foreach($displayRows as $row) {
			$cells = array(
				'address' => $row['address'],
				'latitude' => $row['latitude'],
				'longitude' => $row['longitude'],
				'address_hash' => $row['address_hash']
			);
			$records[] = $cells;
		}
		$response->getBody()->write( json_encode( $records ) );
        return $response;
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
		if($totalAddresses == 0) {
			$progressBarWidth = 0;
		} else {
			$progressBarWidth = round($processedAddresses / $totalAddresses * 100);
		}

		$langService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Lang\LanguageService::class);
		$langService->init( $GLOBALS['BE_USER']->uc['lang'] );

		$content = array();
		if($visible) {
			$content[] = '<div id="status" style="margin-bottom: 5px;">';
		} else {
			$content[] = '<div id="status" style="margin-bottom: 5px; display:none;">';
		}

		$content[] = '<div id="bar" style="width:300px; height:20px; border:1px solid black">
						<div id="progress" style="width:'.$progressBarWidth.'%; height:20px; background-color:red"></div>
					</div>
					<p>'.$langService->SL('LLL:EXT:wec_map/Resources/Private/Languages/Module/MapAdministration/locallang.xlf:processedStart').' '.$processedAddresses.' '.$langService->SL('LLL:EXT:wec_map/Resources/Private/Languages/Module/MapAdministration/locallang.xlf:processedMid').' '.$totalAddresses.'.</p>';

		$content[] = '</div>';

		return implode(chr(10), $content);
	}


}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/mod1/index.php'])	{
include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/mod1/index.php']);
}

?>