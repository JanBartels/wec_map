<?php
/***************************************************************
* Copyright notice
*
* (c) 2005-2009 Christian Technology Ministries International Inc.
* All rights reserved
* (c) 2011-2019 Jan Bartels, j.bartels@arcor.de, Google API V3
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

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;

/**
 * Map implementation for the Google Maps mapping service.
 *
 * @author Web-Empowered Church Team <map@webempoweredchurch.org>
 * @package TYPO3
 * @subpackage tx_wecmap
 */
class Map extends \JBartels\WecMap\MapService\Map {

	public  $markerClassName = 'JBartels\\WecMap\\MapService\\Google\\Marker';
	public  $LLFile = 'EXT:wec_map/Resources/Private/Languages/MapService/Google/locallang.xlf';
	public  $prefixId = 'tx_wecmap_map_google';

	/**
	 * Class constructor for Google Map
	 */
	public function __construct($key, $width=250, $height=250, $lat='', $long='', $zoom='', $mapName='') {
		parent::__construct($key, $width, $height, $lat, $long, $zoom, $mapName);
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
	public function addControl($name) {
		\TYPO3\CMS\Core\Utility\GeneralUtility::devLog($this->mapName.': adding control '.$name, 'wec_map_api');
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
				\TYPO3\CMS\Core\Utility\GeneralUtility::devLog($this->mapName.': ' . $name . '  not supported for addControl()', 'wec_map_api');
				break;
		}
	}


	/**
	 * Main function to draw the map.  Outputs all the necessary HTML and
	 * Javascript to draw the map in the frontend or backend.
	 *
	 * @access	public
	 * @return	string	HTML and Javascript markup to draw the map.
	 */
	public function drawMap() {

		\TYPO3\CMS\Core\Utility\GeneralUtility::devLog($this->mapName.': starting map drawing', 'wec_map_api', array(
			'domain' => \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('HTTP_HOST'),
			'maptype' => $this->type
		) );

		$hasThingsToDisplay = $this->hasThingsToDisplay();
		$hasHeightWidth = $this->hasHeightWidth();

		// make sure we have markers to display and an API key
		$domainmgr = \JBartels\WecMap\Utility\DomainMgr::getInstance();
		$browserKey = $domainmgr->getBrowserKey();

		if ($hasThingsToDisplay && $hasHeightWidth && $browserKey ) {

			// auto center and zoom if necessary
			$this->autoCenterAndZoom();

			$htmlContent = $this->mapDiv();

			$get = \TYPO3\CMS\Core\Utility\GeneralUtility::_GPmerged('tx_wecmap_api');

			// if we're forcing static display, skip the js
			if($this->static && ($this->staticMode == 'force' || ($this->staticUrlParam && intval($get['static']) == 1))) {
				return $htmlContent;
			}

			$scheme = (\TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('TYPO3_SSL') ? 'https://' : 'http://');
			// get the correct API URL
			$apiURL = $scheme . 'maps.googleapis.com/maps/api/js?language=' . $this->lang . '&libraries=places';
			$apiURL = $domainmgr->addKeyToUrl( $apiURL, $browserKey );

			$siteRelPath = PathUtility::stripPathSitePrefix(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('wec_map'));

			if(\JBartels\WecMap\Utility\Backend::getExtConf('useOwnJS'))
			{
				$mmURL  = $siteRelPath . 'Resources/Public/JavaScript/ContribJS/markermanager.js';
				$ibURL  = $siteRelPath . 'Resources/Public/JavaScript/ContribJS/infobubble.js';
				$omURL  = $siteRelPath . 'Resources/Public/JavaScript/ContribJS/oms.min.js';
			}
			else
			{
				$extConf = $this->getExtConf();
				$mmURL  = $scheme . $extConf['mmURL'];
				$ibURL  = $scheme . $extConf['ibURL'];
				$omURL  = $scheme . $extConf['omURL'];
			}

			\TYPO3\CMS\Core\Utility\GeneralUtility::devLog($this->mapName.': loading API from URL: '.$apiURL, 'wec_map_api');

			/* If we're in the frontend, use TSFE.  Otherwise, include JS manually. */
			$jsDir = \JBartels\WecMap\Utility\Backend::getExtConf('jsDir');
			if ( empty( $jsDir ) )
				$jsDir = $siteRelPath . 'Resources/Public/JavaScript/';
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
					\TYPO3\CMS\Core\Utility\GeneralUtility::devLog($this->mapName.': adding '. $group->getMarkerCount() .' markers from group '.$group->id, 'wec_map_api');
					$jsContent = array_merge($jsContent, $group->drawMarkerJS());
					$jsContent[] = '';
				}
			}

			$jsContent[] = $this->js_initialOpenInfoWindow();
			$jsContent[] = $this->js_addKMLOverlay();
			$jsContent[] = $this->js_loadCalls();
			$jsContent[] = $this->js_drawMapEnd();

			\TYPO3\CMS\Core\Utility\GeneralUtility::devLog($this->mapName.': finished map drawing', 'wec_map_api');

			// get our content out of the array into a string
			$jsContentString = implode(chr(10), $jsContent);

			// then return it
			return $htmlContent.\TYPO3\CMS\Core\Utility\GeneralUtility::wrapJS($jsContentString);

		} else if (!$hasThingsToDisplay) {
			$error = '<p>'.$this->getLL( 'error_nothingToDisplay' ).'</p>';
		} else if (!$hasHeightWidth) {
			$error = '<p>'.$this->getLL('error_noHeightWidth' ).'</p>';
		} else if (!$browserKey) {
			$error = '<p>'.$this->getLL( 'error_noBrowserKey' ).'</p>';
		}
		\TYPO3\CMS\Core\Utility\GeneralUtility::devLog($this->mapName.': finished map drawing with errors', 'wec_map_api', 2);
		return $error;
	}


	/**
	 * Draws the static map if desired
	 *
	 * @return String content
	 **/
	public function drawStaticMap() {
		if(!$this->static) return null;


		$index = 0;
		if($this->staticExtent == 'all') {
			$markerString = 'size:small';
			if($this->staticLimit > 50) $this->staticLimit = 50;
			foreach( $this->groups as $key => $group ) {
				foreach( $group->markers as $marker ) {
					if($index >= $this->staticLimit) break 2;
					$index++;
					$markerString .= '|' . floatval($marker->latitude).','.floatval($marker->longitude);
				}
			}
			$img = $this->generateStaticMap($markerString);
			return $img;
		} elseif($this->staticExtent == 'each') {
			foreach( $this->groups as $key => $group ) {
				foreach( $group->markers as $marker ) {
					if($index >= $this->staticLimit) break 2;
					$markerString = 'size:small|' . floatval($marker->latitude).','.floatval($marker->longitude);
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
	protected function generateStaticMap($markers, $center = true, $alt = '') {
		$scheme = (\TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('TYPO3_SSL') ? 'https://' : 'http://');

		if($center) {
			$url = $scheme . 'maps.googleapis.com/maps/api/staticmap?center='.floatval($this->lat) .','.floatval($this->long) .'&zoom='.intval($this->zoom).'&size='.intval($this->width).'x'.intval($this->height).'&maptype='.urlencode($this->type).'&markers='.urlencode($markers);
		} else {
			$url = $scheme . 'maps.googleapis.com/maps/api/staticmap?size='.intval($this->width).'x'.intval($this->height).'&maptype='.urlencode($this->type).'&markers='.urlencode($markers);
		}
		$domainmgr = \JBartels\WecMap\Utility\DomainMgr::getInstance();
		$url = $domainmgr->addKeyToUrl( $url, $domainmgr->getBrowserKey(), $domainmgr->getStaticKey() );
		return '<img class="tx-wecmap-api-staticmap" alt="'.$alt.'" src="' . $url .'" />';
	}

	/**
	 * Adds some language specific markers to the global WecMap JS object.
	 *
	 * @access	private
	 * @return	string		The Javascript code for the labels.
	 */
	protected function js_createLabels() {
		$content = '
function InitWecMapGoogleV3Labels() {
	WecMap.labels.startaddress = ' . json_encode( $this->getLL('startaddress') ) .';
	WecMap.labels.endaddress = '   . json_encode( $this->getLL('endaddress') )   .';
	WecMap.labels.OSM = '          . json_encode( $this->getLL('OSM') )          .';
	WecMap.labels.OSM_alt = '      . json_encode( $this->getLL('OSM-alt') )      .';
	WecMap.labels.OSM_bike = '     . json_encode( $this->getLL('OSM-bike') )     .';
	WecMap.labels.OSM_bike_alt = ' . json_encode( $this->getLL('OSM-bike-alt') ) .';
	WecMap.labels.locale =  '      . json_encode( $this->lang ) . ';
	/* error messages */
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
	protected function js_drawMapStart() {
		$js =  'google.maps.event.addDomListener(window,"load", function () {
if ( !window["WecMap"] )
	WecMap = createWecMap();
WecMap.initGoogle();
InitWecMapGoogleV3Labels();
WecMap.createMap("'. $this->mapName . '", "google" );';

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
	protected function js_drawMapEnd() {
		return '	WecMap.drawMap( "'. $this->mapName . '" );	} );';
	}

	/**
	 * Creates the Google Directions Javascript object.
	 *
	 * @access	private
	 * @param	string		Name of the map object that the direction overlay will be shown on.
	 * @return	string		Javascript for the Google Directions object.
	 */
	protected function js_newGDirections() {
		if($this->directionsDivID == null)
			return '    WecMap.createDirections( "' . $this->mapName . '" );';
		else
			return '    WecMap.createDirections( "' . $this->mapName . '", "' . $this->directionsDivID . '" );';
	}




	/**
	 * Returns the Javascript that is responsible for loading and unloading
	 * the maps.
	 *
	 * @return string The javascript output
	 **/
	protected function js_loadCalls() {
		$loadCalls  = 'if(document.getElementById("'.$this->mapName.'_radiusform") != null) document.getElementById("'.$this->mapName.'_radiusform").style.display = "";';
		$loadCalls .= 'if(document.getElementById("'.$this->mapName.'_sidebar") != null) document.getElementById("'.$this->mapName.'_sidebar").style.display = "";';
		$loadCalls .= 'document.getElementById("'.$this->mapName.'").style.height="'.$this->height.'px";';
		return $loadCalls;
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/map_service/google/class.tx_wecmap_map_google.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/map_service/google/class.tx_wecmap_map_google.php']);
}


?>
