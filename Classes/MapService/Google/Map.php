<?php
/***************************************************************
* Copyright notice
*
* (c) 2005-2009 Christian Technology Ministries International Inc.
* All rights reserved
* (c) 2011-2016 Jan Bartels, j.bartels@arcor.de, Google API V3
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

namespace JBartels\WecMap\MapService\Google;

/**
 * Map implementation for the Google Maps mapping service.
 *
 * @author Web-Empowered Church Team <map@webempoweredchurch.org>
 * @package TYPO3
 * @subpackage tx_wecmap
 */
class Map extends \JBartels\WecMap\MapService\Map {
	public  $lat;
	public  $long;
	public  $zoom;
	public  $markers;
	public  $width;
	public  $height;
	public  $mapName;

	public  $js;
	public  $control;
	public  $type;
	public  $directions;
	public  $kml;
	public  $prefillAddress;
	public  $directionsDivID;
	public  $showInfoOnLoad;
	public  $maxAutoZoom = 15;
	public  $static = false;

	// array to hold the different Icons
	public  $icons;

	public  $lang;
	public  $LOCAL_LANG;
	/** @var $langService \TYPO3\CMS\Lang\LanguageService */
	public  $langService;

	public  $markerClassName = 'JBartels\\WecMap\\MapService\\Google\\Marker';

	/**
	 * Class constructor.  Creates javscript array.
	 * @access	public
	 * @param	string		The Google Maps API Key
	 * @param	string		The latitude for the center point on the map.
	 * @param 	string		The longitude for the center point on the map.
	 * @param	string		The initial zoom level of the map.
	 */
	public function __construct($key, $width=250, $height=250, $lat='', $long='', $zoom='', $mapName='') {
		$this->prefixId = 'tx_wecmap_map_google';
		$this->js = array();
		$this->markers = array();
		$this->kml = array();

		// array to hold the different Icons
		$this->icons = array();

		$this->directions = false;
		$this->directionsDivID = null;
		$this->prefillAddress = false;
		$this->showInfoOnLoad = false;
		$this->width = $width;
		$this->height = $height;

		if (($lat != '' && $lat != null) || ($long != '' && $long != null)) {
			$this->setCenter($lat, $long);
		}

		if ($zoom != '' && $zoom != null) {
			$this->setZoom($zoom);
		}

		if(empty($mapName)) $mapName = 'map'.rand();
		$this->mapName = $mapName;

		// Detect language
		if(TYPO3_MODE == 'BE') {
			$this->lang = $GLOBALS['BE_USER']->uc['lang'];
		} else {
			$this->lang = $GLOBALS['TSFE']->config['config']['language'];
		}
		if( $this->lang == 'default')
			$this->lang = 'en';
		else if( empty( $this->lang ) )
			$this->lang = 'en';

		// load language file
		$this->langService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Lang\LanguageService::class);
		$this->langService->init( $this->lang );
		$this->LOCAL_LANG = $this->langService->getParserFactory()->getParsedData('EXT:wec_map/Resources/Private/Languages/MapService/Google/locallang.xlf', $this->lang, '', 2);
	}

	function getLL($index)
	{
		return $this->langService->getLLL( $index, $this->LOCAL_LANG );
	}

	/**
	 * Enables controls for Google Maps, for example zoom level slider or mini
	 * map. Valid controls are largeMap, smallMap, scale, smallZoom,
	 * overviewMap, and mapType.
	 *
	 * @access	public
	 * @param	string	The name of the control to add.
	 * @return	none
	 *
	 **/
	function addControl($name) {
		if(TYPO3_DLOG) {
			\TYPO3\CMS\Core\Utility\GeneralUtility::devLog($this->mapName.': adding control '.$name, 'wec_map_api');
		}
		switch ($name)
		{
			case 'zoom':
			case 'largeMap':	// deprecated
			case 'smallMap':	// deprecated
			case 'smallZoom':	// deprecated
				$this->controls[] .= $this->js_addControl('new GZoomControl()');
				break;

			case 'scale':
				$this->controls[] .= $this->js_addControl('new GScaleControl()');
				break;

			case 'mapType':
				$this->controls[] .= $this->js_addMapType('G_PHYSICAL_MAP');
				$this->controls[] .= $this->js_addMapType('G_SATELLITE_MAP');
				$this->controls[] .= $this->js_addMapType('G_HYBRID_MAP');
				$this->controls[] .= $this->js_addMapType('G_OSM_MAP');
				$this->controls[] .= $this->js_addMapType('G_OCM_MAP');

				$this->controls[] .= $this->js_addControl('new GHierarchicalMapTypeControl()');
				break;

//			case 'googleEarth':
//				$this->controls[] .= 'WecMap.get("' . $this->mapName . '").addMapType(G_SATELLITE_3D_MAP);';
//				break;

			default:
				if(TYPO3_DLOG) {
					\TYPO3\CMS\Core\Utility\GeneralUtility::devLog($this->mapName.': ' . $name . '  not supported for addControl()', 'wec_map_api');
				}
				break;
		}
	}

	/**
	 * Sets the initial map type.  Valid defaults from Google are...
	 *   G_NORMAL_MAP: This is the normal street map type.
	 *   G_SATELLITE_MAP: This map type shows Google Earth satellite images.
	 *   G_HYBRID_MAP: This map type shows transparent street maps over Google Earth satellite images.
	 *	 G_PHYSICAL_MAP: displays physical map tiles based on terrain information.
	 *   G_OSM_MAP: displays OpenStreetMap
	 *   G_OCM_MAP: displays OpenCycleMap
	 */
	function setType($type) {
		$this->type = $type;
	}

	/**
	 * Main function to draw the map.  Outputs all the necessary HTML and
	 * Javascript to draw the map in the frontend or backend.
	 *
	 * @access	public
	 * @return	string	HTML and Javascript markup to draw the map.
	 */
	function drawMap() {

		if(TYPO3_DLOG) {
			\TYPO3\CMS\Core\Utility\GeneralUtility::devLog($this->mapName.': starting map drawing', 'wec_map_api');
			\TYPO3\CMS\Core\Utility\GeneralUtility::devLog($this->mapName.': domain: '.\TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('HTTP_HOST'), 'wec_map_api');
			\TYPO3\CMS\Core\Utility\GeneralUtility::devLog($this->mapName.': map type: '.$this->type, 'wec_map_api');
		}

		$hasThingsToDisplay = $this->hasThingsToDisplay();
		$hasHeightWidth = $this->hasHeightWidth();

		// make sure we have markers to display and an API key
		$domainmgr = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance( \JBartels\WecMap\Utility\DomainMgr::class );
		$browserKey = $domainmgr->getBrowserKey();

		if ($hasThingsToDisplay && $hasHeightWidth && $browserKey ) {

			// auto center and zoom if necessary
			$this->autoCenterAndZoom();

			$htmlContent .= $this->mapDiv();

			$get = \TYPO3\CMS\Core\Utility\GeneralUtility::_GPmerged('tx_wecmap_api');

			// if we're forcing static display, skip the js
			if($this->static && ($this->staticMode == 'force' || ($this->staticUrlParam && intval($get['static']) == 1))) {
				return $htmlContent;
			}

			$scheme = (\TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('TYPO3_SSL') ? 'https://' : 'http://');
			// get the correct API URL
			$apiURL = $scheme . 'maps.googleapis.com/maps/api/js?language=' . $this->lang . '&libraries=places';
			$apiURL = $domainmgr->addKeyToUrl( $apiURL, $browserKey, false );

			if(\JBartels\WecMap\Utility\Backend::getExtConf('useOwnJS'))
			{
				$mmURL  = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::siteRelPath('wec_map') . 'Resources/Public/JavaScript/ContribJS/markermanager.js';
				$ibURL  = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::siteRelPath('wec_map') . 'Resources/Public/JavaScript/ContribJS/infobubble.js';
				$omURL  = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::siteRelPath('wec_map') . 'Resources/Public/JavaScript/ContribJS/oms.min.js';
			}
			else
			{
				$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['wec_map']);
				$mmURL  = $scheme . $extConf['mmURL'];
				$ibURL  = $scheme . $extConf['ibURL'];
				$omURL  = $scheme . $extConf['omURL'];
			}

			if (TYPO3_DLOG) {
				\TYPO3\CMS\Core\Utility\GeneralUtility::devLog($this->mapName.': loading API from URL: '.$apiURL, 'wec_map_api');
			}

			/* If we're in the frontend, use TSFE.  Otherwise, include JS manually. */
			$jsDir = \JBartels\WecMap\Utility\Backend::getExtConf('jsDir');
			if ( empty( $jsDir ) )
				$jsDir = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::siteRelPath('wec_map') . 'Resources/Public/JavaScript/';
			$jsFile  = $jsDir . 'wecmap.js';
			$jsFile2 = $jsDir . 'copyrights.js';
			$jsFile3 = $jsDir . 'wecmap_backend.js';

			if (TYPO3_MODE == 'FE') {
				$GLOBALS['TSFE']->additionalHeaderData['wec_map_googleMaps'] = '<script src="'.$apiURL.'" type="text/javascript"></script>'
				                                                             . '<script src="'.$mmURL .'" type="text/javascript"></script>'
				                                                             . '<script src="'.$ibURL .'" type="text/javascript"></script>'
				                                                             . '<script src="'.$omURL .'" type="text/javascript"></script>'
				                                                             ;
				$GLOBALS['TSFE']->additionalHeaderData['wec_map'] = ( $jsFile  ? '<script src="' . $jsFile  . '" type="text/javascript"></script>' : '' )
				                                                  . ( $jsFile2 ? '<script src="' . $jsFile2 . '" type="text/javascript"></script>' : '' )
				                                                  ;
			} else {
				$htmlContent .= '<script src="'.$apiURL.'" type="text/javascript"></script>';
				if(\JBartels\WecMap\Utility\Backend::getExtConf('useOwnJS'))
				{
					$htmlContent .= '<script src="' . \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('TYPO3_SITE_URL') . $mmURL . '" type="text/javascript"></script>';
					$htmlContent .= '<script src="' . \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('TYPO3_SITE_URL') . $ibURL . '" type="text/javascript"></script>';
					$htmlContent .= '<script src="' . \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('TYPO3_SITE_URL') . $omURL . '" type="text/javascript"></script>';
				}
				else
				{
					$htmlContent .= '<script src="' . $mmURL . '" type="text/javascript"></script>';
					$htmlContent .= '<script src="' . $ibURL . '" type="text/javascript"></script>';
					$htmlContent .= '<script src="' . $omURL . '" type="text/javascript"></script>';
				}
				$htmlContent .= ( $jsFile  ? '<script src="' . \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('TYPO3_SITE_URL') . $jsFile  . '" type="text/javascript"></script>' : '' )
				              . ( $jsFile2 ? '<script src="' . \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('TYPO3_SITE_URL') . $jsFile2 . '" type="text/javascript"></script>' : '' )
				              . ( $jsFile3 ? '<script src="' . \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('TYPO3_SITE_URL') . $jsFile3 . '" type="text/javascript"></script>' : '' )
				              ;
			}

			if ( $this->enableOverlappingMarkerManager )
				$mapOptions['enableOverlappingMarkerManager'] = true;
			$jsContent = array();
			$jsContent[] = $this->js_createLabels();
			$jsContent[] = '';
			$jsContent[] = $this->js_drawMapStart($mapOptions);
			$jsContent[] = $this->js_newGDirections();
			$jsContent[] = $this->js_setCenter($this->lat, $this->long, $this->zoom, $this->type);
			if ( is_array( $this->controls ) )
				$jsContent = array_merge($jsContent, $this->controls);
			$jsContent[] = $this->js_icons();
			if ( is_array( $this->groups ) )
			{
				foreach ($this->groups as $key => $group ) {
					if(TYPO3_DLOG) {
						\TYPO3\CMS\Core\Utility\GeneralUtility::devLog($this->mapName.': adding '. $group->getMarkerCount() .' markers from group '.$group->id, 'wec_map_api');
					}
					$jsContent = array_merge($jsContent, $group->drawMarkerJS());
					$jsContent[] = '';
				}
			}

			$jsContent[] = $this->js_initialOpenInfoWindow();
			$jsContent[] = $this->js_addKMLOverlay();
			$jsContent[] = $this->js_loadCalls();
			$jsContent[] = $this->js_drawMapEnd();

			if(TYPO3_DLOG) {
				\TYPO3\CMS\Core\Utility\GeneralUtility::devLog($this->mapName.': finished map drawing', 'wec_map_api');
			}

			// get our content out of the array into a string
			$jsContentString = implode(chr(10), $jsContent);

			// then return it
			return $htmlContent.\TYPO3\CMS\Core\Utility\GeneralUtility::wrapJS($jsContentString);

		} else if (!$hasThingsToDisplay) {
			$error = '<p>'.$this->getLL( 'error_nothingToDisplay' ).'</p>';
		} else if (!$hasHeightWidth) {
			$error = '<p>'.$this->getLL('error_noHeightWidth' ).'</p>';
		} else if (!$browserKey) {
			$error = '<p>'.$this->getLL($lang, 'error_noBrowserKey' ).'</p>';
		}
		if(TYPO3_DLOG) {
			\TYPO3\CMS\Core\Utility\GeneralUtility::devLog($this->mapName.': finished map drawing with errors', 'wec_map_api', 2);
		}
		return $error;
	}


	/**
	 * Draws the static map if desired
	 *
	 * @return String content
	 **/
	function drawStaticMap() {
		if(!$this->static) return null;


		$index = 0;
		if($this->staticExtent == 'all') {
			$markerString = 'size:small';
			if($this->staticLimit > 50) $this->staticLimit = 50;
			foreach( $this->groups as $key => $group ) {
				foreach( $group->markers as $marker ) {
					if($index >= $this->staticLimit) break 2;
					$index++;
					$markerString .= '|' . $marker->latitude.','.$marker->longitude;
				}
			}
			$img = $this->generateStaticMap($markerString);
			return $img;
		} elseif($this->staticExtent == 'each') {
			foreach( $this->groups as $key => $group ) {
				foreach( $group->markers as $marker ) {
					if($index >= $this->staticLimit) break 2;
					$markerString = 'size:small|' . $marker->latitude.','.$marker->longitude;
					$img .= $this->generateStaticMap($markerString, false);
					$index++;
				}
			}
			return $img;
		} else {
			return null;
		}
	}

	/**
	 * undocumented function
	 *
	 * @return void
	 **/
	function generateStaticMap($markers, $center = true, $alt = '') {
		$scheme = (\TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('TYPO3_SSL') ? 'https://' : 'http://');

		if($center) {
			$url = $scheme . 'maps.googleapis.com/maps/api/staticmap?center='.$this->lat .','.$this->long .'&zoom='.$this->zoom.'&size='.$this->width.'x'.$this->height.'&maptype='.$this->type.'&markers='.urlencode($markers);
		} else {
			$url = $scheme . 'maps.googleapis.com/maps/api/staticmap?size='.$this->width.'x'.$this->height.'&maptype='.$this->type.'&markers='.urlencode($markers);
		}
		$domainmgr = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance( \JBartels\WecMap\Utility\DomainMgr::class );
		$url = $domainmgr->addKeyToUrl( $url, $domainmgr->getBrowserKey(), true );
		return '<img class="tx-wecmap-api-staticmap" alt="'.$alt.'" src="' . $url .'" />';
	}
	/**
	 * Adds an address to the currently list of markers rendered on the map. Support tabs.
	 *
	 * @param	string		The street address.
	 * @param	string		The city name.
	 * @param	string		The state or province.
	 * @param	string		The ZIP code.
	 * @param	string		The country name.
	 * @param 	array 		Array of tab labels. Need to be kept short.
	 * @param	array		Array of titles for the marker popup.
	 * @param	array		Array of descriptions to be displayed in the marker popup.
	 * @param	integer		Minimum zoom level for marker to appear.
	 * @param	integer		Maximum zoom level for marker to appear.
	 * @return	marker object
	 * @todo	Zoom levels are very Google specific.  Is there a generic way to handle this?
	 */
	function addMarkerByAddressWithTabs($street, $city, $state, $zip, $country, $tabLabels = null, $title=null, $description=null, $minzoom = 0, $maxzoom = 18, $iconID = '') {
		/* Geocode the address */
		$latlong = \JBartels\WecMap\Utility\Cache::lookup($street, $city, $state, $zip, $country);

		/* Create a marker at the specified latitude and longitdue */
		return $this->addMarkerByLatLongWithTabs($latlong['lat'], $latlong['long'], $tabLabels, $title, $description, $minzoom, $maxzoom, $iconID);
	}

	/**
	 * Adds an address string to the current list of markers rendered on the map.
	 *
	 * @param	string		The full address string.
	 * @param	array 		Array of strings to be used as labels on the tabs
	 * @param	array		The titles for the tabs of the marker popup.
	 * @param	array		The descriptions to be displayed in the tabs of the marker popup.
	 * @param	integer		Minimum zoom level for marker to appear.
	 * @param	integer		Maximum zoom level for marker to appear.
	 * @return	marker object
	 * @todo	Zoom levels are very Google specific.  Is there a generic way to handle this?
	 **/
	function addMarkerByStringWithTabs($string, $tabLabels, $title=null, $description=null, $minzoom = 0, $maxzoom = 18, $iconID = '') {

		// first split the string into it's components. It doesn't need to be perfect, it's just
		// put together on the other end anyway
		$address = explode(',', $string);
		list($street, $city, $state, $country) = $address;

		/* Geocode the address */
		$latlong = \JBartels\WecMap\Utility\Cache::lookup($street, $city, $state, $zip, $country);

		/* Create a marker at the specified latitude and longitdue */
		return $this->addMarkerByLatLongWithTabs($latlong['lat'], $latlong['long'], $tabLabels, $title, $description, $minzoom, $maxzoom, $iconID);
	}

	/**
	 * Adds a marker from TCA info with tabs
	 *
	 * @param	string		The table name
	 * @param 	integer		The uid of the record to be mapped
	 * @param	array 		Array of strings to be used as labels on the tabs
	 * @param	array		The titles for the tabs of the marker popup.
	 * @param	array		The descriptions to be displayed in the tabs of the marker popup.
	 * @param	integer		Minimum zoom level for marker to appear.
	 * @param	integer		Maximum zoom level for marker to appear.
	 * @return	marker object
	 **/
	function addMarkerByTCAWithTabs($table, $uid, $tabLabels, $title=null, $description=null, $minzoom = 0, $maxzoom = 18, $iconID = '') {
		$uid = intval($uid);

		// first get the mappable info from the TCA
		$tca = $GLOBALS['TCA'][$table]['ctrl']['EXT']['wec_map'];

		if(!$tca) return false;
		if(!$tca['isMappable']) return false;

		$streetfield  = \JBartels\WecMap\Utility\Shared::getAddressField($table, 'street');
		$cityfield    = \JBartels\WecMap\Utility\Shared::getAddressField($table, 'city');
		$statefield   = \JBartels\WecMap\Utility\Shared::getAddressField($table, 'state');
		$zipfield     = \JBartels\WecMap\Utility\Shared::getAddressField($table, 'zip');
		$countryfield = \JBartels\WecMap\Utility\Shared::getAddressField($table, 'country');

		// get address from db for this record
		$record = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', $table, 'uid='.intval($uid));
		$record = $record[0];

		$street = $record[$streetfield];
		$city 	= $record[$cityfield];
		$state 	= $record[$statefield];
		$zip	= $record[$zipfield];
		$country= $record[$countryfield];

		if(empty($country) && $countryfield == 'static_info_country') {
			$country = $record['country'];
		} else if(empty($country) && $countryfield == 'country') {
			$country = $record['static_info_country'];
		}

		/* Geocode the address */
		$latlong = \JBartels\WecMap\Utility\Cache::lookup($street, $city, $state, $zip, $country);

		/* Create a marker at the specified latitude and longitdue */
		return $this->addMarkerByLatLongWithTabs($latlong['lat'], $latlong['long'], $tabLabels, $title, $description, $minzoom, $maxzoom, $iconID);
	}

	/**
	 * Adds a lat/long to the currently list of markers rendered on the map.
	 *
	 * @param	float		The latitude.
	 * @param	float		The longitude.
	 * @param	string		The title for the marker popup.
	 * @param	string		The description to be displayed in the marker popup.
	 * @param	integer		Minimum zoom level for marker to appear.
	 * @param	integer		Maximum zoom level for marker to appear.
	 * @return	marker object
	 * @todo	Zoom levels are very Google specific.  Is there a generic way to handle this?
	 */
	function addMarkerByLatLongWithTabs($lat, $long, $tabLabels = null, $title=null, $description=null, $minzoom = 0, $maxzoom = 18, $iconID = '') {

		if(!empty($this->radius)) {
			$distance = $this->getDistance($this->lat, $this->long, $lat, $long);

			if(!empty($this->lat) && !empty($this->long) &&  $distance > $this->radius) {
				return null;
			}
		}

		if($lat != '' && $long != '') {
			$group = $this->addGroup($minzoom, $maxzoom);
			$marker = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
			                          $this->getMarkerClassName(),
			                          $group->getMarkerCount(),
									  $lat,
									  $long,
									  $title,
									  $description,
									  $this->prefillAddress,
									  $tabLabels,
									  '0xFF0000',
									  '0xFFFFFF',
									  $iconID);
			$marker->setMinZoom($minzoom);
			$marker->setMapName($this->mapName);
			$group->addMarker($marker);
			$group->setDirections($this->directions);

			return $marker;
		}
		return null;
	}

	/**
	 * Adds more custom icons to the Javascript Code
	 * Takes an assoc. array with the following keys:
	 * $iconID, $imagepath, $shadowpath, $width, $height,
	 * $shadowWidth, $shadowHeight, $anchorX, $anchorY,
	 * $infoAnchorX, $infoAnchorY
	 *
	 * @return 		boolean
	 * @access   	public
	 */
	function addMarkerIcon($dataArray, &$cObj=null ) {
		if (empty($dataArray)) {
			return false;
		} else {
			if ( $cObj && is_array($dataArray))
			{
				$sData = $dataArray;
				$dataArray = array();
				foreach($sData as $theKey => $theValue)	{
					if ( substr($theKey, -1, 1 ) == '.') {
						$dataArray[ substr($theKey,0,-1) ] = $cObj->stdWrap($sData[substr($theKey,0,-1)], $sData[$theKey]);
					} else {
						$dataArray[$theKey] = $sData[$theKey];
					}
				}
			}

		  	$this->icons[] = 'WecMap.addIcon("'. $this->mapName . '", "' . $dataArray['iconID'] . '", "' . $dataArray['imagepath'] . '", "' . $dataArray['shadowpath'] . '", new google.maps.Size(' . $dataArray['width'] . ', ' . $dataArray['height'] . '), new google.maps.Size(' . $dataArray['shadowWidth'] . ', ' . $dataArray['shadowHeight'] . '), new google.maps.Point(' . $dataArray['anchorX'] . ', ' . $dataArray['anchorY'] . '), new google.maps.Point(' . $dataArray['infoAnchorX'] . ', ' . $dataArray['infoAnchorY'] . '));';
			return true;
		}

	}

	/**
	 * Adds a KML overlay to the map.
	 *
	 * @return void
	 **/
	function addKML($url) {
		$this->kml[] = $url;
	}

	/**
	 * Sets the map center to a given address' coordinates.
	 *
	 * @return void
	 **/
	function setCenterByAddress($street, $city, $state, $zip, $country = null) {

		/* Geocode the address */
		$latlong = \JBartels\WecMap\Utility\Cache::lookup($street, $city, $state, $zip, $country);
		$this->lat = $latlong['lat'];
		$this->long = $latlong['long'];
	}

	/**
	 * Creates the overall map div.
	 *
	 * @access	private
	 * @return	string		The HTML for the map div tag.
	 */
	function mapDiv() {
		$staticContent = $this->drawStaticMap();
		if ($this->static) {
			$height = '100%';
		} else {
			$height = $this->height . 'px';
		}
		return '<div id="'.$this->mapName.'" class="tx-wecmap-map" style="width:'.$this->width.'px; height:' . $height . ';">'.$staticContent.'</div>';
	}

	/**
	 * Adds some language specific markers to the global WecMap JS object.
	 *
	 * @access	private
	 * @return	string		The Javascript code for the labels.
	 */
	function js_createLabels() {
		$content = '
function InitWecMapGoogleV3Labels() {
	WecMap.labels.startaddress = ' . json_encode( $this->getLL('startaddress') ) .';
	WecMap.labels.endaddress = '   . json_encode( $this->getLL('endaddress') )   .';
	WecMap.labels.OSM = '          . json_encode( $this->getLL('OSM') )          .';
	WecMap.labels.OSM_alt = '      . json_encode( $this->getLL('OSM-alt') )      .';
	WecMap.labels.OSM_bike = '     . json_encode( $this->getLL('OSM-bike') )     .';
	WecMap.labels.OSM_bike_alt = ' . json_encode( $this->getLL('OSM-bike-alt') ) .';
	WecMap.labels.locale =  '      . json_encode( $this->lang ) . ';
	// error messages
	WecMap.labels.INVALID_REQUEST = '        . json_encode( $this->getLL('INVALID_REQUEST') ) .';
	WecMap.labels.MAX_WAYPOINTS_EXCEEDED = ' . json_encode( $this->getLL('MAX_WAYPOINTS_EXCEEDED') ) .';
	WecMap.labels.NOT_FOUND = '              . json_encode( $this->getLL('NOT_FOUND') ) .';
	WecMap.labels.OK = '                     . json_encode( $this->getLL('OK') ) . ';
	WecMap.labels.OVER_QUERY_LIMIT = '       . json_encode( $this->getLL('OVER_QUERY_LIMIT') ) .';
	WecMap.labels.REQUEST_DENIED = '         . json_encode( $this->getLL('REQUEST_DENIED') ) .';
	WecMap.labels.UNKNOWN_ERROR = '          . json_encode( $this->getLL('UNKNOWN_ERROR') ) .';
	WecMap.labels.ZERO_RESULTS = '           . json_encode( $this->getLL('ZERO_RESULTS') ) .';

	WecMap.osmMapType.name = WecMap.labels.OSM;
	WecMap.osmMapType.alt = WecMap.labels.OSM_alt;
	WecMap.osmCycleMapType.name = WecMap.labels.OSM_bike;
	WecMap.osmCycleMapType.alt = WecMap.labels.OSM_bike_alt;
}';
	return $content;
	}


	/**
	 * Creates the beginning of the drawMap function in Javascript.
	 *
	 * @access	private
	 * @return	string	The beginning of the drawMap function in Javascript.
	 */
	function js_drawMapStart() {
		$js =  'google.maps.event.addDomListener(window,"load", function () {
if ( !window["WecMap"] )
	WecMap = createWecMap();
WecMap.init();
InitWecMapGoogleV3Labels();
WecMap.createMap("'. $this->mapName . '" );';

		if ( $this->mapOptions['enableOverlappingMarkerManager'] )
			$js .= 'WecMap.enableOverlappingMarkerManager("'. $this->mapName . '", true );';
		return $js;
	}

	/**
	 * Creates the end of the drawMap function in Javascript.
	 *
	 * @access	private
	 * @return	string	The end of the drawMap function in Javascript.
	 */
	function js_drawMapEnd() {
		return '	WecMap.drawMap( "'. $this->mapName . '" );	} );';
	}

	/**
	 * Creates the Google Directions Javascript object.
	 *
	 * @access	private
	 * @param	string		Name of the map object that the direction overlay will be shown on.
	 * @return	string		Javascript for the Google Directions object.
	 */
	function js_newGDirections() {
		if($this->directionsDivID == null)
			return '    WecMap.createDirections( "' . $this->mapName . '" );';
		else
			return '    WecMap.createDirections( "' . $this->mapName . '", "' . $this->directionsDivID . '" );';
	}


	function js_setMapType($type) {
		return 'WecMap.setMapType("'. $this->mapName . '", ' . $type . ');';
	}

	function js_addMapType($type) {
		return 'WecMap.addMapType("'. $this->mapName . '", ' . $type . ');';
	}



	/**
	 * Creates the map's center point in Javascript.
	 *
	 * @access	private
	 * @param	float		Center latitude.
	 * @param	float		Center longitude.
	 * @param	integer		Initial zoom level.
	 * @return	string		Javascript to center and zoom the specified map.
	 */
	function js_setCenter($lat, $long, $zoom, $type) {
		if ($type) {
			return 'WecMap.setCenter("'. $this->mapName . '", new google.maps.LatLng('.$lat.', '.$long.'), '.$zoom.', '.$type.');';
		} else {
			return 'WecMap.setCenter("'. $this->mapName . '", new google.maps.LatLng('.$lat.', '.$long.'), '.$zoom.');';
		}
	}


	/**
	 * Creates Javascript to add map controls.
	 *
	 * @access	private
	 * @param	string		Javascript to add a control to the map.
	 */
	function js_addControl($control) {
		return 'WecMap.addControl("'. $this->mapName . '", '.$control.');';
	}

	/**
	 * generate the js for kml overlays
	 *
	 * @return string
	 **/
	function js_addKMLOverlay() {
		$out = array();
		foreach ($this->kml as $url) {
			$out[] = 'WecMap.addKML("'. $this->mapName . '", "' . $url . '");';
		}
		return implode("\n", $out);
	}

	/**
	 * Creates Javascript to define marker icons.
	 *
	 * @access	private
	 * @return	string		Javascript definitions for marker icons.
	 */
	function js_icons() {
		/* If we're in the backend, get an absolute path.  Frontend can use a relative path. */
		if (TYPO3_MODE=='BE')	{
			$path = \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('TYPO3_SITE_URL').\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::siteRelPath('wec_map');
		} else {
			$path = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::siteRelPath('wec_map');
		}
		// add default-icon
		$this->addMarkerIcon(array(
		  	'iconID'        => 'default',
		  	'imagepath'     => $path.'Resources/Public/Images/mm_20_red.png',
		  	'shadowpath'    => $path.'Resources/Public/Images/mm_20_shadow.png',
		  	'width'         => 12,
		  	'height'        => 20,
		  	'shadowWidth'   => 22,
		  	'shadowHeight'  => 20,
		  	'anchorX'       => 6,
		  	'anchorY'       => 20,
		  	'infoAnchorX'   => 5,
		  	'infoAnchorY'   => 1,
		) );
		return implode("\n", $this->icons);
	}

	/**
	 * Write the javascript to open the info window if there is only one marker
	 *
	 * @return string 	javascript
	 **/
	function js_initialOpenInfoWindow() {
		$markers = reset($this->markers);
		if (count($markers) == 1 && $this->showInfoOnLoad) {
			foreach($this->groups as $key => $group) {
				foreach( $group->markers as $marker ) {
					return $marker->getInitialOpenInfoWindowJS();  // return 1st marker
				}
			}
		}
		return '';
	}


	/**
	 * Returns the Javascript that is responsible for loading and unloading
	 * the maps.
	 *
	 * @return string The javascript output
	 **/
	function js_loadCalls() {
		$loadCalls  = 'if(document.getElementById("'.$this->mapName.'_radiusform") != null) document.getElementById("'.$this->mapName.'_radiusform").style.display = "";';
		$loadCalls .= 'if(document.getElementById("'.$this->mapName.'_sidebar") != null) document.getElementById("'.$this->mapName.'_sidebar").style.display = "";';
		$loadCalls .= 'document.getElementById("'.$this->mapName.'").style.height="'.$this->height.'px";';
		return $loadCalls;
	}

	/**
	 * Sets the center and zoom values for the current map dynamically, based
	 * on the markers to be displayed on the map.
	 *
	 * @access	private
	 * @return	none
 	 */
	function autoCenterAndZoom() {

		/* Get center and lat/long spans from parent object */
		$latLongData = $this->getLatLongData();

		$lat = $latLongData['lat']; /* Center latitude */
		$long = $latLongData['long']; /* Center longitude */
		$latSpan = $latLongData['latSpan']; /* Total latitude the map covers */
		$longSpan = $latLongData['longSpan']; /* Total longitude the map covers */

		// process center
		if(!isset($this->lat) or !isset($this->long)) {
			if(TYPO3_DLOG) {
				\TYPO3\CMS\Core\Utility\GeneralUtility::devLog($this->mapName.': setting center to '.$lat.', '.$long, 'wec_map_api');
			}
			$this->setCenter($lat, $long);
		}

		// process zoom
		if(!isset($this->zoom) || $this->zoom == '') {
			$this->setZoom($this->getAutoZoom($latSpan, $longSpan));
		}

		// prepare parameters for the center and zoom hook
		$hookParameters = array('lat' => &$this->lat, 'long' => &$this->long, 'zoom' => &$this->zoom);

		// process centerAndZoom hook; allows to manipulate zoom and center before displaying the map
		if (isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tx_wecmap_api']['centerAndZoomHook']))	{
			$hooks =& $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tx_wecmap_api']['centerAndZoomHook'];
			$hookReference = null;
			foreach ($hooks as $hookFunction)	{
				\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($hookFunction, $hookParameters, $hookReference);
				if(TYPO3_DLOG) {
					\TYPO3\CMS\Core\Utility\GeneralUtility::devLog($this->mapName.': Called hook. Potentially new lat/long/zoom', 'wec_map_api', 2);
					\TYPO3\CMS\Core\Utility\GeneralUtility::devLog($this->mapName.': Lat: '.$this->lat.' Long: '.$this->long.' Zoom: '.$this->zoom, 'wec_map_api', 2);
				}
			}
		}
	}

	/**
	 * Calculates the auto zoom
	 *
	 * @return int 	zoom level
	 **/
	function getAutoZoom($latSpan, $longSpan) {

		if ( $longSpan <= 0 || $latSpan <= 0 )
			return $this->maxAutoZoom;

		$wZoom = log($this->width, 2) - log($longSpan, 2);
		$hZoom = log($this->height, 2) - log($latSpan, 2);

		/* Pick the lower of the zoom levels since we'd rather show too much */
		$zoom = floor(($wZoom < $hZoom) ? $wZoom : $hZoom);

		/* Don't zoom in too far if we only have a single marker.*/
		if ($zoom > $this->maxAutoZoom) {
			$zoom = $this->maxAutoZoom;
		}
		if(TYPO3_DLOG) {
			\TYPO3\CMS\Core\Utility\GeneralUtility::devLog($this->mapName.': set zoom '.$zoom, 'wec_map_api');
		}
		return $zoom;
	}

	/**
     * Checks if a map has markers or a
     * specific center.Otherwise, we have nothing
     * to draw.
     * @return        boolean        True/false whether the map is valid or not.
     */
    function hasThingsToDisplay() {
        $valid = false;

        if(sizeof($this->groups) > 0) {
            $validMarkers = false;
			foreach( $this->groups as $key => $group ) {
				if($group->hasMarkers()) {
            		$validMarkers = true;
				}
			}
        } else {
			$validMarkers = false;
		}

        if(isset($this->lat) and isset($this->long)) {
            $validCenter = true;
        }

		// If we have an API key along with markers or a center point, it's valid
        if($validMarkers or $validCenter) {
            $valid = true;
        }

		if (count($this->kml) ) {
			$valid = true;
		}

        return $valid;
    }

	/**
	 * Checks whether the map has a height and width set.
	 *
	 * @return boolean
	 **/
	function hasHeightWidth() {
		if(!empty($this->width) && !empty($this->height)) {
			if(TYPO3_DLOG) {
				\TYPO3\CMS\Core\Utility\GeneralUtility::devLog($this->mapName.': height: '.$this->height.', width: '.$this->width, 'wec_map_api');
			}
			return true;
		} else {
			if(TYPO3_DLOG) {
				\TYPO3\CMS\Core\Utility\GeneralUtility::devLog($this->mapName.': width or height missing', 'wec_map_api', 3);
			}
			return false;
		}
	}

	/**
	 * Enables directions
	 *
	 * @param boolean	Whether or not to prefill the currently logged in FE user's address already
	 * @param string	The id of the container that will show the written directions
	 *
	 * @return void
	 **/
	function enableDirections($prefillAddress = false, $divID = null) {
		$this->prefillAddress = $prefillAddress;
		if(TYPO3_DLOG) {
			if($prefillAddress && $divID) {
				\TYPO3\CMS\Core\Utility\GeneralUtility::devLog($this->mapName.': enabling directions with prefill and written dirs', 'wec_map_api');
			} else if($prefillAddress && !$divID) {
				\TYPO3\CMS\Core\Utility\GeneralUtility::devLog($this->mapName.': enabling directions with prefill and without written dirs', 'wec_map_api');
			} else if(!$prefillAddress && $divID) {
				\TYPO3\CMS\Core\Utility\GeneralUtility::devLog($this->mapName.': enabling directions without prefill but with written dirs', 'wec_map_api');
			} else if(!$prefillAddress && !$divID) {
				\TYPO3\CMS\Core\Utility\GeneralUtility::devLog($this->mapName.': enabling directions without prefill and written dirs', 'wec_map_api');
			}
		}
		$this->directions = true;
		$this->directionsDivID = $divID;
	}

	/**
	 * Enables static maps
	 *
	 * @param $mode String either automatic or force
	 * @param $extent String either all or each
	 * @param $urlParam boolean enable URL parameter to force static map
	 * @param $limit int Limit of markers on a map or marker maps
	 *
	 * @return void
	 **/
	function enableStatic($mode='automatic', $extent='all', $urlParam=false, $limit=50) {
		$this->static = true;
		if(empty($mode)) $mode = 'automatic';
		$this->staticMode = $mode;
		if(empty($extent)) $extent = 'all';
		$this->staticExtent = $extent;
		if(empty($urlParam)) $urlParam = false;
		$this->staticUrlParam = $urlParam;
		if(empty($limit)) $limit = 50;
		$this->staticLimit = $limit;

		if(TYPO3_DLOG) {
			\TYPO3\CMS\Core\Utility\GeneralUtility::devLog($this->mapName.': Enabling static maps: '.$mode.':'.$extent.':'.$urlParam.':'.$limit, 'wec_map_api');
		}
	}

	/**
	 * Makes the marker info bubble show on load if there is only one marker on the map
	 *
	 * @return void
	 **/
	function showInfoOnLoad() {

		$this->showInfoOnLoad = true;

		if(TYPO3_DLOG) {
			\TYPO3\CMS\Core\Utility\GeneralUtility::devLog($this->mapName.': Showing info bubble on load', 'wec_map_api');
		}
	}

	/**
	 * Sets the maximum zoom level that autozoom will use
	 *
	 * @return void
	 **/
	function setMaxAutoZoom($newZoom = null) {
		if($newZoom != null) {
			$this->maxAutoZoom = intval($newZoom);
		}
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/map_service/google/class.tx_wecmap_map_google.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/map_service/google/class.tx_wecmap_map_google.php']);
}


?>
