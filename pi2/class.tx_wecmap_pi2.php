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
/**
 * Plugin 'Frontend User Map' for the 'wec_map' extension.
 *
 * @author	Web-Empowered Church Team <map@webempoweredchurch.org>
 */


require_once(PATH_tslib.'class.tslib_pibase.php');
require_once(t3lib_extMgm::extPath('wec_map').'class.tx_wecmap_shared.php');

/**
 * Frontend User Map plugin for displaying all frontend users on a map.
 *
 * @author Web-Empowered Church Team <map@webempoweredchurch.org>
 * @package TYPO3
 * @subpackage tx_wecmap
 */
class tx_wecmap_pi2 extends tslib_pibase {
	var $prefixId = 'tx_wecmap_pi2';		// Same as class name
	var $scriptRelPath = 'pi2/class.tx_wecmap_pi2.php';	// Path to this script relative to the extension dir.
	var $extKey = 'wec_map';	// The extension key.
	var $pi_checkCHash = TRUE;
	var $sidebarLinks = array();

	/**
	 * Draws a Google map containing all frontend users of a website.
	 *
	 * @param	array		The content array.
	 * @param	array		The conf array.
	 * @return	string	HTML / Javascript representation of a Google map.
	 */
	function main($content,$conf)	{
		$this->conf=$conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();

		// check for WEC Map API static template inclusion
		if(empty($conf['output']) && !(empty($conf['marker.']['title']) && empty($conf['marker.']['description']))) {
			global $LANG;
			if(!is_object($LANG)) {
				require_once(t3lib_extMgm::extPath('lang').'lang.php');
				$LANG = t3lib_div::makeInstance('language');
				$LANG->init($GLOBALS['TSFE']->config['config']['language']);
			}
			$LANG->includeLLFile('EXT:wec_map/locallang_db.xml');
			$out .= $LANG->getLL('wecApiTemplateNotIncluded');
			// syslog start
				t3lib_div::sysLog('WEC Map API template not included on page id '.$GLOBALS['TSFE']->id, 'wec_map', 3);
			// syslog end
			return $out;
		}

		// check for WEC FE Map static template inclusion
		if(empty($conf['marker.']['title']) && empty($conf['marker.']['description'])) {
			global $LANG;
			if(!is_object($LANG)) {
				require_once(t3lib_extMgm::extPath('lang').'lang.php');
				$LANG = t3lib_div::makeInstance('language');
				$LANG->init($GLOBALS['TSFE']->config['config']['language']);
			}
			$LANG->includeLLFile('EXT:wec_map/locallang_db.xml');
			$out .= $LANG->getLL('pi2TemplateNotIncluded');
			// syslog start
				t3lib_div::sysLog('WEC FE User template not included on page id '.$GLOBALS['TSFE']->id, 'wec_map', 3);
			// syslog end
			return $out;
		}

		/* Initialize the Flexform and pull the data into a new object */
		$this->pi_initPIflexform();
		$piFlexForm = $this->cObj->data['pi_flexform'];

		// get config from flexform or TS. Flexforms take precedence.
		$width = $this->pi_getFFvalue($piFlexForm, 'mapWidth', 'default');
		empty($width) ? $width = $this->cObj->stdWrap($conf['width'], $conf['width.']):null;

		$height = $this->pi_getFFvalue($piFlexForm, 'mapHeight', 'default');
		empty($height) ? $height = $this->cObj->stdWrap($conf['height'], $conf['height.']):null;
		$this->height = $height;

		$userGroups = $this->pi_getFFvalue($piFlexForm, 'userGroups', 'default');
		empty($userGroups) ? $userGroups = $this->cObj->stdWrap($conf['userGroups'], $conf['userGroups.']):null;

		$pid = $this->pi_getFFvalue($piFlexForm, 'pid', 'default');
		empty($pid) ? $pid = $this->cObj->stdWrap($conf['pid'], $conf['pid.']):null;

		$mapControlSize = $this->pi_getFFvalue($piFlexForm, 'mapControlSize', 'mapControls');
		(empty($mapControlSize) || $mapControlSize == 'none') ? $mapControlSize = $this->cObj->stdWrap($conf['controls.']['mapControlSize'], $conf['controls.']['mapControlSize.']):null;

		$overviewMap = $this->pi_getFFvalue($piFlexForm, 'overviewMap', 'mapControls');
		empty($overviewMap) ? $overviewMap = $this->cObj->stdWrap($conf['controls.']['showOverviewMap'], $conf['controls.']['showOverviewMap.']):null;

		$mapType = $this->pi_getFFvalue($piFlexForm, 'mapType', 'mapControls');
		empty($mapType) ? $mapType = $this->cObj->stdWrap($conf['controls.']['showMapType'], $conf['controls.']['showMapType.']):null;

		$googleEarth = $this->pi_getFFvalue($piFlexForm, 'googleEarth', 'mapControls');
		empty($googleEarth) ? $googleEarth = $this->cObj->stdWrap($conf['controls.']['showGoogleEarth'], $conf['controls.']['showGoogleEarth.']):null;

		$initialMapType = $this->pi_getFFvalue($piFlexForm, 'initialMapType', 'default');
		empty($initialMapType) ? $initialMapType = $this->cObj->stdWrap($conf['initialMapType'], $conf['initialMapType.']):null;

		$scale = $this->pi_getFFvalue($piFlexForm, 'scale', 'mapControls');
		empty($scale) ? $scale = $this->cObj->stdWrap($conf['controls.']['showScale'], $conf['controls.']['showScale.']):null;

		$private = $this->pi_getFFvalue($piFlexForm, 'privacy', 'default');
		empty($private) ? $private = $this->cObj->stdWrap($conf['private'], $conf['private.']):null;

		$showDirs = $this->pi_getFFvalue($piFlexForm, 'showDirections', 'default');
		empty($showDirs) ? $showDirs = $this->cObj->stdWrap($conf['showDirections'], $conf['showDirections.']):null;
		$this->showDirections = $showDirs;

		$showWrittenDirs = $this->pi_getFFvalue($piFlexForm, 'showWrittenDirections', 'default');
		empty($showWrittenDirs) ? $showWrittenDirs = $this->cObj->stdWrap($conf['showWrittenDirections'], $conf['showWrittenDirections.']):null;

		$prefillAddress = $this->pi_getFFvalue($piFlexForm, 'prefillAddress', 'default');
		empty($prefillAddress) ? $prefillAddress = $this->cObj->stdWrap($conf['prefillAddress'], $conf['prefillAddress.']):null;

		$showRadiusSearch = $this->pi_getFFvalue($piFlexForm, 'showRadiusSearch', 'default');
		empty($showRadiusSearch) ? $showRadiusSearch = $this->cObj->stdWrap($conf['showRadiusSearch'], $conf['showRadiusSearch.']):null;

		$showSidebar = $this->pi_getFFvalue($piFlexForm, 'showSidebar', 'default');
		empty($showSidebar) ? $showSidebar = $this->cObj->stdWrap($conf['showSidebar'], $conf['showSidebar.']):null;
		$this->showSidebar = $showSidebar;

		$kml = $this->cObj->stdWrap($conf['kml'], $conf['kml.']);

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

		/* Create the Map object */
		include_once(t3lib_extMgm::extPath('wec_map').'map_service/google/class.tx_wecmap_map_google.php');
		$map = t3lib_div::makeInstance('tx_wecmap_map_google', null, $width, $height, $centerLat, $centerLong, $zoomLevel, $mapName);

		// get kml urls for each included record
		if(!empty($kml)) {
			$where = 'uid IN ('.$GLOBALS['TYPO3_DB']->cleanIntList($kml).')';
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('url', 'tx_wecmap_external', $where);
			foreach( $res as $key => $url ) {
				$link = trim($url['url']);
				$oldAbs = $GLOBALS['TSFE']->absRefPrefix;
				$GLOBALS['TSFE']->absRefPrefix = t3lib_div::getIndpEnv('TYPO3_SITE_URL');
				$linkConf = Array(
					'parameter' => $link,
					'returnLast' => 'url'
				);
				$link = $this->cObj->typolink('', $linkConf);
				$GLOBALS['TSFE']->absRefPrefix = $oldAbs;
				$map->addKML($link);
			}
		}

		// evaluate map controls based on configuration
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

		// set up groups:
		// country
		if (is_array($conf['groups.']['country.']) || $private) {
			$countryConf = array();
			$countryConf['icon'] = $conf['groups.']['country.']['icon.'];
			$countryConf['minzoom'] = $this->cObj->stdWrap($conf['groups.']['country.']['zoom.']['min'], $conf['groups.']['country.']['zoom.']['min.']);
			$countryConf['maxzoom'] = $this->cObj->stdWrap($conf['groups.']['country.']['zoom.']['max'], $conf['groups.']['country.']['zoom.']['max.']);
			// country icon, if configured
			if(  !empty($countryConf['icon']['imagepath'])
			  || !empty($countryConf['icon']['imagepath.'])
			  ) {
				$map->addMarkerIcon($countryConf['icon'], $this->cObj);
			} else {
				$countryConf['icon']['iconID'] ? null : $countryConf['icon']['iconID'] = null;
			}
			$showCountries = true;
		} else {
			$showCountries = false;
		}


		// city
		if (is_array($conf['groups.']['city.']) || $private) {
			$cityConf = array();
			$cityConf['icon'] = $conf['groups.']['city.']['icon.'];
			$cityConf['minzoom'] = $this->cObj->stdWrap($conf['groups.']['city.']['zoom.']['min'], $conf['groups.']['city.']['zoom.']['min.']);
			$cityConf['maxzoom'] = $this->cObj->stdWrap($conf['groups.']['city.']['zoom.']['max'], $conf['groups.']['city.']['zoom.']['max.']);
			// city icon, if configured
			if(  !empty($cityConf['icon']['imagepath'])
			  || !empty($cityConf['icon']['imagepath.'])
			  ) {
				$map->addMarkerIcon($cityConf['icon'], $this->cObj);
			} else {
				$cityConf['icon']['iconID'] ? null : $cityConf['icon']['iconID'] = null;
			}
			$showCities = true;
		} else {
			$showCities = false;
		}

		// single
		$singleConf = array();
		$singleConf['icon'] = $conf['groups.']['single.']['icon.'];
		$singleConf['minzoom'] = $this->cObj->stdWrap($conf['groups.']['single.']['zoom.']['min'], $conf['groups.']['single.']['zoom.']['min.']);
		$singleConf['maxzoom'] = $this->cObj->stdWrap($conf['groups.']['single.']['zoom.']['max'], $conf['groups.']['single.']['zoom.']['max.']);

		// country icon, if configured
		if(  !empty($singleConf['icon']['imagepath'])
		  || !empty($singleConf['icon']['imagepath.'])
		  ) {
			$map->addMarkerIcon($singleConf['icon'], $this->cObj);
		} else {
			$singleConf['icon']['iconID'] ? null : $singleConf['icon']['iconID'] = null;
		}


		// check whether to show the directions tab and/or prefill addresses and/or written directions
		if($showDirs && $showWrittenDirs && $prefillAddress) $map->enableDirections(true, $mapName.'_directions');
		if($showDirs && $showWrittenDirs && !$prefillAddress) $map->enableDirections(false, $mapName.'_directions');
		if($showDirs && !$showWrittenDirs && $prefillAddress) $map->enableDirections(true);
		if($showDirs && !$showWrittenDirs && !$prefillAddress) $map->enableDirections();

		// process radius search
		if($showRadiusSearch) {

			// check for POST vars for our map. If there are any, proceed.
			$pRadius = intval(t3lib_div::_POST($mapName.'_radius'));

			if(!empty($pRadius)) {
				$pAddress    = strip_tags(t3lib_div::_POST($mapName.'_address'));
				$pCity       = strip_tags(t3lib_div::_POST($mapName.'_city'));
				$pState      = strip_tags(t3lib_div::_POST($mapName.'_state'));
				$pZip        = strip_tags(t3lib_div::_POST($mapName.'_zip'));
				$pCountry    = strip_tags(t3lib_div::_POST($mapName.'_country'));
				$pKilometers = intval(t3lib_div::_POST($mapName.'_kilometers'));

				$data = array(
					'street' => $pAddress,
					'city'	=> $pCity,
					'state' => $pState,
					'zip' => $pZip,
					'country' => $pCountry
				);

				$desc = tx_wecmap_shared::render($data, $conf['defaultdescription.']);
				$map->addMarkerIcon($conf['homeicon.'], $this->cObj);
				$map->addMarkerByAddress($pAddress, $pCity, $pState, $pZip, $pCountry, '', $desc ,0 , 18, 'homeicon');
				$map->setCenterByAddress($pAddress, $pCity, $pState, $pZip, $pCountry);
				$map->setRadius($pRadius, $pKilometers);

			}
		}

		$streetField  = tx_wecmap_shared::getAddressField('fe_users', 'street');
		$cityField    = tx_wecmap_shared::getAddressField('fe_users', 'city');
		$stateField   = tx_wecmap_shared::getAddressField('fe_users', 'state');
		$zipField     = tx_wecmap_shared::getAddressField('fe_users', 'zip');
		$countryField = tx_wecmap_shared::getAddressField('fe_users', 'country');


		// start where clause
		$where = '1=1';

		// if a user group was set, make sure only those users from that group
		// will be selected in the query
		if($userGroups) {
			$where .= tx_wecmap_shared::listQueryFromCSV('usergroup', $userGroups, 'fe_users');
		}

		// if a storage folder pid was specified, filter by that
		if($pid) {
			$where .= tx_wecmap_shared::listQueryFromCSV('pid', $pid, 'fe_users', 'OR');
		}

		// filter out records that shouldn't be shown, e.g. deleted, hidden
		$where .= $this->cObj->enableFields('fe_users');

		/* Select all frontend users */
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'fe_users', $where);

		// create country and zip code array to keep track of which country and state we already added to the map.
		// the point is to create only one marker per country on a higher zoom level to not
		// overload the map with all the markers, and do the same with zip codes.
		$countries = array();
		$cities = array();
		while (($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result))) {

			// add check for country and use different field if empty
			// @TODO: make this smarter with TCA or something
			if(empty($row[$countryField]) && $countryField == 'static_info_country') {
				$countryField = 'country';
			} else if(empty($row[$countryField]) && $countryField == 'country') {
				$countryField = 'static_info_country';
			}

			/* Only try to add marker if there's a city */
			if($row[$cityField] != '') {

				// if we haven't added a marker for this country yet, do so.
				if ($showCountries && !in_array($row[$countryField], $countries) && !empty($row[$countryField])) {

					// add this country to the array
					$countries[] = $row[$countryField];

					// combine title config to pass to render function
					$title_conf = array('title' => $conf['marker.']['title'], 'title.' => $conf['marker.']['title.']);

					// add a little info so users know what to do
					$title = tx_wecmap_shared::render(array('name' => $this->pi_getLL('country_zoominfo_title')), $title_conf);
					$description = sprintf($this->pi_getLL('country_zoominfo_desc'), $row[$countryField]);

					// add a marker for this country and only show it between the configured zoom level.
					$map->addMarkerByAddress(null, $row[$cityField], $row[$stateField], $row[$zipField], $row[$countryField], $title, $description, $countryConf['minzoom'], $countryConf['maxzoom'], $countryConf['icon']['iconID']);
				}


				// if we haven't added a marker for this zip code yet, do so.
				if ($showCities && !in_array($row[$cityField], $cities) && !empty($row[$cityField])) {

					// add this country to the array
					$cities[] = $row[$cityField];

					// combine title config to pass to render function
					$title_conf = array('title' => $conf['marker.']['title'], 'title.' => $conf['marker.']['title.']);

					// add a little info so users know what to do
					$title = tx_wecmap_shared::render(array('name' => 'Info'), $title_conf);

					$countWhere = $cityField.'='. $GLOBALS['TYPO3_DB']->fullQuoteStr( $row[$cityField], 'fe_users' );
					if ( $zipField )
						$countWhere .= ' AND ' . $zipField.'='. $GLOBALS['TYPO3_DB']->fullQuoteStr( $row[$zipField], 'fe_users' );
					$count = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('count(*)', 'fe_users', $countWhere);
					$count = $count[0]['count(*)'];

					// extra processing if private is turned on
					if($private) {
						$maxzoom = $singleConf['maxzoom'];
						if($count == 1) {
							$description = sprintf($this->pi_getLL('citycount_si'),$row[$cityField]);
						} else {
							$description = sprintf($this->pi_getLL('citycount_pl'),$count, $row[$cityField]);
						}

					} else {
						$maxzoom = $cityConf['maxzoom'];
						$description = sprintf($this->pi_getLL('city_zoominfo_desc'), $row[$cityField]);
					}

					// add a marker for the city level and only show it
					// either from city-min to single-max or city-min to city-max, depending on privacy settings
					$marker = $map->addMarkerByAddress(null, $row[$cityField], $row[$stateField], $row[$zipField], $row[$countryField], $title, $description, $cityConf['minzoom'],$maxzoom, $cityConf['icon']['iconID']);
				}

				// make title and description
				$title = tx_wecmap_shared::render($row, $conf['marker.']['title.']);
				$description = tx_wecmap_shared::render($row, $conf['marker.']['description.']);

				// unless we are using privacy, add individual markers as well
				if(!$private) {
					$marker = $map->addMarkerByAddress($row[$streetField], $row[$cityField], $row[$stateField], $row[$zipField], $row[$countryField], $title, $description, $singleConf['minzoom'], $singleConf['maxzoom'], $singleConf['icon']['iconID']);
					if ( $overlappingMarkerLatDev && $overlappingMarkerLongDev )
						$map->handleOverlappingMarker( $marker, $overlappingMarkerLatDev, $overlappingMarkerLongDev );
					$row['info_title'] = $title;
					$row['info_description'] = $description;
					$this->addSidebarItem($marker, $row);
					$this->addDirectionsMenu($marker);
				}
			}

		}

		// gather all the content together
		$content = array();
		$content['map'] = $map->drawMap();
		if($showRadiusSearch) 	$content['addressForm'] = $this->getAddressForm();
		if($showWrittenDirs)  	$content['directions']  = $this->getDirections();
		if($showSidebar)		$content['sidebar']     = $this->getSidebar();

		// run all the content pieces through TS to assemble them
		$output = tx_wecmap_shared::render($content, $conf['output.']);

		return $this->pi_wrapInBaseClass($output);
	}

	/**
	 * adds a sidebar item corresponding to the given marker.
	 * Does so only if the sidebar is enabled.
	 *
	 * @return void
	 **/
	function addSidebarItem(&$marker, $data) {
		if(!($this->showSidebar && is_object($marker))) return;
		$data['onclickLink'] = $marker->getClickJS();
		$this->sidebarLinks[] = tx_wecmap_shared::render($data, $this->conf['sidebarItem.']);
	}

	function getAddressForm() {
		$out = tx_wecmap_shared::render(array('map_id' => $this->mapName), $this->conf['addressForm.']);
		return $out;
	}

	function getDirections() {
		$out = tx_wecmap_shared::render(array('map_id' => $this->mapName), $this->conf['directions.']);
		return $out;
	}

	function getSidebar() {
		if(empty($this->sidebarLinks)) return null;

		$c = '';

		foreach( $this->sidebarLinks as $link ) {
			$c .= $link;
		}
		$out = tx_wecmap_shared::render(array('map_height' => $this->height, 'map_id' => $this->mapName, 'content' => $c), $this->conf['sidebar.']);

		return $out;
	}

	/**
	 * adds a directions menu corresponding to the given marker.
	 * Does so only if the showDirections is enabled.
	 *
	 * @return void
	 **/
	function addDirectionsMenu(&$marker) {
		if(!($this->showDirections && is_object($marker))) return;
		$marker->setDirectionsMenuConf( $this->conf['directionsMenu.'] );
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/pi2/class.tx_wecmap_pi2.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/pi2/class.tx_wecmap_pi2.php']);
}

?>
