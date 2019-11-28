<?php
/***************************************************************
* Copyright notice
*
* (c) 2019 Jan Bartels
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

namespace JBartels\WecMap\MapService;

/**
 * Main class for the wec_map extension.  This class sits between the various
 * frontend plugins and address lookup service to render map data.  All map
 * services implement this abstract class.
 *
 * @author j.bartels
 * @package TYPO3
 * @subpackage tx_wecmap
 */
class MapFactory {

	/**
	 * Class factory
	 */
	static function createMap( $provider, $key, $width=250, $height=250, $lat='', $long='', $zoom='', $mapName='') {

		// set Default if not provided
		if ( empty( $provider ) ) {
			$extConf = \JBartels\WecMap\MapService\MapFactory::getExtConf();
			if ( is_array( $extConf) ) {
				$provider = $extConf[ 'MapProvider'];
			}
			if ( empty( $provider ) ) {
				$provider = 'Google';
			}
		}

		// create map instance
		if ( $provider == 'Google' ) {
			return \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance( \JBartels\WecMap\MapService\Google\Map::class, $key, $width, $height, $lat, $long, $zoom, $mapName);
		}
		if ( $provider == 'Leaflet' ) {
			return \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance( \JBartels\WecMap\MapService\Leaflet\Map::class, $key, $width, $height, $lat, $long, $zoom, $mapName);
		}
		return null;
	}

    /**
     * @return array extConf
     */
	static function getExtConf()
    {
		if ( \TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) >= 9000000)
        	return GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('wec_map');
        else
        	return unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['wec_map']);
	}

}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/class.tx_wecmap_mapfactory.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/class.tx_wecmap_mapfactory.php']);
}
?>