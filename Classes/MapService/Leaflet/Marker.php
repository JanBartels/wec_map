<?php
/***************************************************************
* Copyright notice
*
* (c) 2019 Jan Bartels, j.bartels@arcor.de, Leaflet
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

namespace JBartels\WecMap\MapService\Leaflet;

/**
 * Marker implementation for the Leaflet mapping service.
 *
 * @author j.bartels
 * @package TYPO3
 * @subpackage tx_wecmap
 */
class Marker extends \JBartels\WecMap\MapService\Marker {

	public  $LLFile = 'EXT:wec_map/Resources/Private/Languages/MapService/Leaflet/locallang.xlf';
	public  $prefixId = 'tx_wecmap_map_leaflet';
	
		/**
	 * Constructor for the Leaflet marker class.
	 *
	 * @access	public
	 * @param	integer		Index within the overall array of markers.
	 * @param	float		Latitude of the marker location.
	 * @param	float		Longitude of the marker location.
	 * @param	string		Title of the marker.
	 * @param	string		Description of the marker.
	 * @param 	boolean		Sets whether the directions address should be prefilled with logged in user's address
	 * @param	array 		Labels used on tabs. Optional.
	 * @param	string		Unused for Leaflet.
	 * @param	string		Unused for Leaflet.
	 * @return	none
	 */
	public function __construct($index, $latitude, $longitude, $title, $description, $prefillAddress = false, $tabLabels=null, $color='0xFF0000', $strokeColor='0xFFFFFF', $iconID='') {
		parent::__construct($index, $latitude, $longitude, $title, $description, $prefillAddress, $tabLabels, $color, $strokeColor, $iconID);
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/map_service/leaflet/class.tx_wecmap_marker_leaflet.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/map_service/leaflet/class.tx_wecmap_marker_leaflet.php']);
}


?>