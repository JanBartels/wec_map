<?php
/***************************************************************
* Copyright notice
*
* (c) 2005-2009 Christian Technology Ministries International Inc.
* (c) 2010-2015 J. Bartels
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

namespace JBartels\WecMap\Plugin\SimpleMap;

/**
 * Simple frontend plugin for displaying an address on a map.
 */
class SimpleMap extends \TYPO3\CMS\Frontend\Plugin\AbstractPlugin {
	var $prefixId = 'tx_wecmap_pi1';		// Same as class name
	var $scriptRelPath = 'Classes/Plugin/SimpleMap/SimpleMap.php';	// Path to this script relative to the extension dir.
	var $extKey = 'wec_map';	// The extension key.
	var $pi_checkCHash = TRUE;
	var $sidebarLinks = array();

	/**
	 * Draws a Google map based on an address entered in a Flexform.
	 * @param	array		Content array.
	 * @param	array		Conf array.
	 * @return	string	HTML / Javascript representation of a Google map.
	 */
	function main($content,$conf)	{
		$this->conf=$conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL('EXT:wec_map/Resources/Private/Languages/Plugin/FEUserMap/locallang.xlf');
//		$LANG->includeLLFile('EXT:wec_map/Resources/Private/Languages/locallang_db.xml');

		$out = '';

		// check for WEC Map API static template inclusion
		if(empty($conf['output']) && !(empty($conf['marker.']['title']) && empty($conf['marker.']['description']))) {
			$out .= $this->pi_getLL('wecApiTemplateNotIncluded');
			\TYPO3\CMS\Core\Utility\GeneralUtility::sysLog('WEC Map API template not included on page id '.$GLOBALS['TSFE']->id, 'wec_map', 3);
			return $out;
		}

		// check for WEC Simple Map static template inclusion
		if(empty($conf['marker.']['title']) && empty($conf['marker.']['description'])) {
			$out .= $this->pi_getLL('pi1TemplateNotIncluded');
			\TYPO3\CMS\Core\Utility\GeneralUtility::sysLog('WEC Simple Map template not included on page id '.$GLOBALS['TSFE']->id, 'wec_map', 3);
			return $out;
		}

		/* Initialize the Flexform and pull the data into a new object */
		$this->pi_initPIflexform();
		$piFlexForm = $this->cObj->data['pi_flexform'];

		// get configuration from flexform or TS. Flexform values take
		// precedence.
		$width = $this->pi_getFFvalue($piFlexForm, 'mapWidth', 'mapConfig');
		empty($width) ? $width = $this->cObj->stdWrap($conf['width'], $conf['width.']):null;

		$height = $this->pi_getFFvalue($piFlexForm, 'mapHeight', 'mapConfig');
		empty($height) ? $height = $this->cObj->stdWrap($conf['height'], $conf['height.']):null;
		$this->height = $height;

		$mapControlSize = $this->pi_getFFvalue($piFlexForm, 'mapControlSize', 'mapControls');
		(empty($mapControlSize) || $mapControlSize == 'none') ? $mapControlSize = $this->cObj->stdWrap($conf['controls.']['mapControlSize'], $conf['controls.']['mapControlSize.']):null;

		$overviewMap = $this->pi_getFFvalue($piFlexForm, 'overviewMap', 'mapControls');
		empty($overviewMap) ? $overviewMap = $this->cObj->stdWrap($conf['controls.']['showOverviewMap'], $conf['controls.']['showOverviewMap.']):null;

		$mapType = $this->pi_getFFvalue($piFlexForm, 'mapType', 'mapControls');
		empty($mapType) ? $mapType = $this->cObj->stdWrap($conf['controls.']['showMapType'], $conf['controls.']['showMapType.']):null;

		$googleEarth = $this->pi_getFFvalue($piFlexForm, 'googleEarth', 'mapControls');
		empty($googleEarth) ? $googleEarth = $this->cObj->stdWrap($conf['controls.']['showGoogleEarth'], $conf['controls.']['showGoogleEarth.']):null;

		$initialMapType = $this->pi_getFFvalue($piFlexForm, 'initialMapType', 'mapConfig');
		empty($initialMapType) ? $initialMapType = $this->cObj->stdWrap($conf['initialMapType'], $conf['initialMapType.']):null;

		$scale = $this->pi_getFFvalue($piFlexForm, 'scale', 'mapControls');
		empty($scale) ? $scale = $this->cObj->stdWrap($conf['controls.']['showScale'], $conf['controls.']['showScale.']):null;

		$showInfoOnLoad = $this->pi_getFFvalue($piFlexForm, 'showInfoOnLoad', 'mapConfig');
		empty($showInfoOnLoad) ? $showInfoOnLoad = $this->cObj->stdWrap($conf['showInfoOnLoad'], $conf['showInfoOnLoad.']):null;

		$showDirs = $this->pi_getFFvalue($piFlexForm, 'showDirections', 'mapConfig');
		empty($showDirs) ? $showDirs = $this->cObj->stdWrap($conf['showDirections'], $conf['showDirections.']):null;
		$this->showDirections = $showDirs;

		$showWrittenDirs = $this->pi_getFFvalue($piFlexForm, 'showWrittenDirections', 'mapConfig');
		empty($showWrittenDirs) ? $showWrittenDirs = $this->cObj->stdWrap($conf['showWrittenDirections'], $conf['showWrittenDirections.']):null;

		$prefillAddress = $this->pi_getFFvalue($piFlexForm, 'prefillAddress', 'mapConfig');
		empty($prefillAddress) ? $prefillAddress = $this->cObj->stdWrap($conf['prefillAddress'], $conf['prefillAddress.']):null;

		$this->showSidebar = $this->cObj->stdWrap($conf['showSidebar'], $conf['showSidebar.']);

		$centerLat = $this->cObj->stdWrap($conf['centerLat'], $conf['centerLat.']);

		$centerLong = $this->cObj->stdWrap($conf['centerLong'], $conf['centerLong.']);

		$zoomLevel = $this->cObj->stdWrap($conf['zoomLevel'], $conf['zoomLevel.']);

		$maxAutoZoom = $this->cObj->stdWrap($conf['maxAutoZoom'], $conf['maxAutoZoom.']);

		$enableOverlappingMarkerManager = $this->cObj->stdWrap($conf['enableOverlappingMarkerManager'], $conf['enableOverlappingMarkerManager.']);
		$overlappingMarkerLatDev = $this->cObj->stdWrap($conf['overlappingMarkerLatDev'], $conf['overlappingMarkerLatDev.']);
		$overlappingMarkerLongDev = $this->cObj->stdWrap($conf['overlappingMarkerLongDev'], $conf['overlappingMarkerLongDev.']);

		$static = $this->cObj->stdWrap($conf['static.']['enabled'], $conf['static.']['enabled.']);
		$staticMode = $this->cObj->stdWrap($conf['static.']['mode'], $conf['static.']['mode.']);
		$staticExtent = $this->cObj->stdWrap($conf['static.']['extent'], $conf['static.']['extent.']);
		$staticUrlParam = $this->cObj->stdWrap($conf['static.']['urlParam'], $conf['static.']['urlParam.']);
		$staticLimit = $this->cObj->stdWrap($conf['static.']['limit'], $conf['static.']['limit.']);

		$mapName = $this->cObj->stdWrap($conf['mapName'], $conf['mapName.']);
		if(empty($mapName)) $mapName = 'map'.$this->cObj->data['uid'];
		$this->mapName = $mapName;

		// get this from flexform only. If empty, we check the TS, see below.
		$street      = $this->pi_getFFvalue($piFlexForm, 'street', 'default');
		$city        = $this->pi_getFFvalue($piFlexForm, 'city', 'default');
		$state       = $this->pi_getFFvalue($piFlexForm, 'state', 'default');
		$zip         = $this->pi_getFFvalue($piFlexForm, 'zip', 'default');
		$country     = $this->pi_getFFvalue($piFlexForm, 'country', 'default');
		$title       = $this->pi_getFFvalue($piFlexForm, 'title', 'default');
		$description = $this->pi_getFFvalue($piFlexForm, 'description', 'default');

		/* Create the map class and add markers to the map */
		/** @var \JBartels\WecMap\MapService\Google\Map $map */
		$map = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\JBartels\WecMap\MapService\Google\Map::class, null, $width, $height, $centerLat, $centerLong, $zoomLevel, $mapName);

		// evaluate config to see which map controls we need to show
		if($mapControlSize == 'large') {
			$map->addControl('largeMap');
		} else if ($mapControlSize == 'small') {
			$map->addControl('smallMap');
		} else if ($mapControlSize == 'zoomonly') {
			$map->addControl('smallZoom');
		}

		$map->setMaxAutoZoom($maxAutoZoom);
		if($enableOverlappingMarkerManager) $map->addOption('enableOverlappingMarkerManager',true);

		if($scale) $map->addControl('scale');
		if($overviewMap) $map->addControl('overviewMap');
		if($mapType) $map->addControl('mapType');
		if($initialMapType) $map->setType($initialMapType);
		if($googleEarth) $map->addControl('googleEarth');
		if($static) $map->enableStatic($staticMode, $staticExtent, $staticUrlParam, $staticLimit);

		// check whether to show the directions tab and/or prefill addresses and/or written directions
		if($showDirs && $showWrittenDirs && $prefillAddress) $map->enableDirections(true, $mapName.'_directions');
		if($showDirs && $showWrittenDirs && !$prefillAddress) $map->enableDirections(false, $mapName.'_directions');
		if($showDirs && !$showWrittenDirs && $prefillAddress) $map->enableDirections(true);
		if($showDirs && !$showWrittenDirs && !$prefillAddress) $map->enableDirections();

		// see if we need to open the marker bubble on load
		if($showInfoOnLoad) $map->showInfoOnLoad();

		// add icons regardless of whether the ext is configured in TS or Flexform.
		if(  !empty($conf['icons.'])) {
			foreach( $conf['icons.'] as $key => $value ) {
				$map->addMarkerIcon($value, $this->cObj);
			}

		} else {
			$iconID = '';
		}


		// determine if an address has been set through flexforms. If not, process TS
		if(empty($zip) && empty($state) && empty($city)) {

			// loop through markers
			foreach($conf['markers.'] as $marker) {

				// use the icon specified in the marker config
				$iconID = $this->cObj->stdWrap($marker['iconID'], $marker['iconID.']);

				// determine if address was entered by string or separated
				if(array_key_exists('address', $marker) || array_key_exists('address.', $marker)) {

					$title = \JBartels\WecMap\Utility\Shared::render($marker, $conf['marker.']['title.']);
					$description = \JBartels\WecMap\Utility\Shared::render($marker, $conf['marker.']['description.']);

					// add address by string
					$markerObj = $map->addMarkerByString($this->cObj->stdWrap($marker['address'], $marker['address.']), $title, $description, 0, 18, $iconID);
					if ( $overlappingMarkerLatDev && $overlappingMarkerLongDev )
						$map->handleOverlappingMarker( $markerObj, $overlappingMarkerLatDev, $overlappingMarkerLongDev );

					$this->addSidebarItem($markerObj, $marker);
					$this->addDirectionsMenu($markerObj);

				// add address by lat and long only
				} else if(  ( array_key_exists('lat', $marker) || array_key_exists('lat.', $marker) )
				         && ( array_key_exists('long', $marker) || array_key_exists('long.', $marker) )
				         ) {

					$title = \JBartels\WecMap\Utility\Shared::render($marker, $conf['marker.']['title.']);
					$description = \JBartels\WecMap\Utility\Shared::render($marker, $conf['marker.']['description.']);
					$lat     = $this->cObj->stdWrap($marker['lat'], $marker['lat.']);
					$long    = $this->cObj->stdWrap($marker['long'], $marker['long.']);

					// add the marker to the map
					$markerObj = $map->addMarkerByLatLong($lat, $long, $title, $description, 0, 18, $iconID);
					if ( $overlappingMarkerLatDev && $overlappingMarkerLongDev )
						$map->handleOverlappingMarker( $markerObj, $overlappingMarkerLatDev, $overlappingMarkerLongDev );

					$this->addSidebarItem($markerObj, $marker);
					$this->addDirectionsMenu($markerObj);

				} else {

					$title = \JBartels\WecMap\Utility\Shared::render($marker, $conf['marker.']['title.']);
					$description = \JBartels\WecMap\Utility\Shared::render($marker, $conf['marker.']['description.']);

					// add the marker to the map
					$markerObj = $map->addMarkerByAddress( $this->cObj->stdWrap($marker['street'], $marker['street.']),
					                                       $this->cObj->stdWrap($marker['city'], $marker['city.']),
					                                       $this->cObj->stdWrap($marker['state'], $marker['state.']),
					                                       $this->cObj->stdWrap($marker['zip'], $marker['zip.']),
					                                       $this->cObj->stdWrap($marker['country'], $marker['country.']),
											               $title, $description, 0, 18, $iconID);
					if ( $overlappingMarkerLatDev && $overlappingMarkerLongDev )
						$map->handleOverlappingMarker( $markerObj, $overlappingMarkerLatDev, $overlappingMarkerLongDev );
					$this->addSidebarItem($markerObj, $marker);
					$this->addDirectionsMenu($markerObj);
				}
			}
		} else {
			// put all the data into an array
			$marker['city']        = $city;
			$marker['state']       = $state;
			$marker['street']      = $street;
			$marker['zip']         = $zip;
			$marker['country']     = $country;
			$marker['title']       = $title;
			$marker['description'] = $description;

			$title = \JBartels\WecMap\Utility\Shared::render($marker, $conf['marker.']['title.']);
			$description = \JBartels\WecMap\Utility\Shared::render($marker, $conf['marker.']['description.']);

			// add the marker to the map
			$markerObj = $map->addMarkerByAddress($street, $city, $state, $zip, $country, $title, $description, 0, 18, $this->cObj->stdWrap($conf['marker.']['iconID'], $conf['marker.']['iconID.']));
			$this->addSidebarItem($markerObj, $marker);
			$this->addDirectionsMenu($markerObj);
		}

		// gather all the content together
		$content = array();
		$content['map'] = $map->drawMap();
		if($showWrittenDirs) $content['directions'] = $this->getDirections();
		$content['sidebar'] = $this->getSidebar();

		// run all the content pieces through TS to assemble them
		$output = \JBartels\WecMap\Utility\Shared::render($content, $conf['output.']);

		return $this->pi_wrapInBaseClass($output);
	}

	function getDirections() {
		$out = \JBartels\WecMap\Utility\Shared::render(array('map_id' => $this->mapName), $this->conf['directions.']);
		return $out;
	}

	/**
	 * adds a sidebar item corresponding to the given marker.
	 * Does so only if the sidebar is enabled.
	 *
	 * @return void
	 **/
	function addSidebarItem($marker, $data) {
		if(!($this->showSidebar && is_object($marker))) return;
		$data['onclickLink'] = $marker->getClickJS();
		$this->sidebarLinks[] = \JBartels\WecMap\Utility\Shared::render($data, $this->conf['sidebarItem.']);
	}

	function getSidebar() {
		if(empty($this->sidebarLinks)) return null;

		$c = '';
		foreach( $this->sidebarLinks as $link ) {
			$c .= $link;
		}
		$out = \JBartels\WecMap\Utility\Shared::render(array('map_height' => $this->height, 'map_id' => $this->mapName, 'content' => $c), $this->conf['sidebar.']);

		return $out;
	}

	/**
	 * adds a directions menu corresponding to the given marker.
	 * Does so only if the showDirections is enabled.
	 *
	 * @return void
	 **/
	function addDirectionsMenu($marker) {
		if(!($this->showDirections && is_object($marker))) return;
		$marker->setDirectionsMenuConf( $this->conf['directionsMenu.'] );
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/pi1/class.tx_wecmap_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/pi1/class.tx_wecmap_pi1.php']);
}

?>
