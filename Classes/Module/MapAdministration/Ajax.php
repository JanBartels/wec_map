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

		$processedAddresses = $batchGeocode->getProcessedAddresses();
		$totalAddresses = $batchGeocode->getRecordCount();

		$response->getBody()->write( json_encode( [
			'geocoded' => $batchGeocode->geocodedAddresses,
			'processed' => $processedAddresses,
			'total' => $totalAddresses
		] ) );
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

}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/mod1/index.php'])	{
include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/mod1/index.php']);
}

?>