<?php
/***************************************************************
* Copyright notice
*
* (c) 2005-2009 Christian Technology Ministries International Inc.
* (c) 2013-2019 Jan Bartels
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

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;

/**
 * Main class for the wec_map extension.  This class sits between the various
 * frontend plugins and address lookup service to render map data.  All map
 * services implement this abstract class.
 *
 * @author Web-Empowered Church Team <map@webempoweredchurch.org>
 * @package TYPO3
 * @subpackage tx_wecmap
 */
class Map {
	public $lat;
	public $long;
	public $zoom;
	public $radius;
	public $kilometers;
	public $markers;
	public $width;
	public $height;
	public $mapName;
	public $groupCount = 0;
	public $groups;
	public $js;

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

	public  $markerClassName = '';
	public  $LLFile = '';
	public  $prefixId = '';


	public $mapOptions = array();

	/**
	 * Class constructor stub.  Override in the map_service classes. Look there for
	 * examples.
	 */
	protected function __construct($key, $width=250, $height=250, $lat='', $long='', $zoom='', $mapName='') {
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
		$this->LOCAL_LANG = $this->langService->includeLLFile( $this->LLFile, false );
	}

	protected function getLL($index)
	{
		return $this->langService->getLLL( $index, $this->LOCAL_LANG );
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
	public function setType($type) {
		$this->type = $type;
	}

	/**
	 * Stub for the drawMap function.  Individual map services should implement
	 * this method to output their own HTML and Javascript.
	 *
	 */
	public function drawMap() {}

	/**
	 * Generic stub to set special map-options
	 */
	public function addOption( $key, $value ) {
		$this->mapOptions[ $key ] = $value;
	}


	/**
	 * Calculates the center and lat/long spans from the current markers.
	 *
	 * @access	private
	 * @return	array		Array of lat/long center and spans.  Array keys
	 *						are lat, long, latSpan, and longSpan.
	 */
	protected function getLatLongData() {

		// if only center is given, do a different calculation
		if(isset($this->lat) && isset($this->long) && !isset($this->zoom)) {
			$latlong = $this->getFarthestLatLongFromCenter();

			return array(
				'lat' => $this->lat,
				'long' => $this->long,
				'latSpan' => abs(($latlong[0]-$this->lat) * 2),
				'longSpan' => abs(($latlong[1]-$this->long) * 2)
			);

		} else {

			$latlong = $this->getLatLongBounds();

			$minLat = $latlong['minLat'];
			$maxLat = $latlong['maxLat'];
			$minLong = $latlong['minLong'];
			$maxLong = $latlong['maxLong'];

			/* Calculate the span of the lat/long boundaries */
			$latSpan = $maxLat-$minLat;
			$longSpan = $maxLong-$minLong;

			/* Calculate center lat/long based on boundary markers */
			$lat = ($minLat + $maxLat) / 2;
			$long = ($minLong + $maxLong) / 2;

			return array(
				'lat' => $lat,
				'long' => $long,
				'latSpan' => $latSpan,
				'longSpan' => $longSpan,
			);
		}
	}

	/**
	 * Goes through all the markers and calculates the max distance from the center
	 * to any one marker.
	 *
	 * @return array with lat long bounds
	 **/
	protected function getFarthestLatLongFromCenter() {

		$max_long_distance = -360;
		$max_lat_distance = -360;

		// find farthest away point
		foreach($this->groups as $key => $group) {
			foreach( $group->markers as $marker ) {
				if(($marker->getLatitude() - $this->lat) >= $max_lat_distance) {
					$max_lat_distance = $marker->getLatitude() - $this->lat;
					$max_lat = $marker->getLatitude();
				}

				if (($marker->getLongitude() - $this->long) >= $max_long_distance) {
					$max_long_distance = $marker->getLongitude() - $this->long;
					$max_long = $marker->getLongitude();
				}
 			}
		}

		return array($max_lat, $max_long);
	}

	/*
	 * Sets the center value for the current map to specified values.
	 *
	 * @param	float		The latitude for the center point on the map.
	 * @param	float		The longitude for the center point on the map.
	 * @return	none
	 */
	public function setCenter($lat, $long) {
		$this->lat  = $lat;
		$this->long = $long;
	}

	/**
	 * Sets the zoom value for the current map to specified values.
	 *
	 * @param	integer		The initial zoom level for the map.
	 * @return	none
	 */
	public function setZoom($zoom) {
		$this->zoom = $zoom;
	}

	/**
	 * Sets the radius from the center that markers need to be within
	 *
	 * @param	integer		The radius from the center
	 * @param 	boolean		Whether it's kilometers or miles
	 * @return	none
	 */
	public function setRadius($radius, $kilometers = false) {
		$this->kilometers = $kilometers;
		$this->radius = $radius;
		$kilometers ? $km = 'km':$km = 'miles';
		\TYPO3\CMS\Core\Utility\GeneralUtility::devLog($this->mapName.': setting radius '.$radius.' '.$km, 'wec_map_api');
	}

	# haversine formula to calculate distance between two points
	protected function getDistance($lat1, $long1, $lat2, $long2)
	{
	    $l1 = deg2rad ($lat1);
	    $l2 = deg2rad ($lat2);
	    $o1 = deg2rad ($long1);
	    $o2 = deg2rad ($long2);

		$this->kilometers ? $radius = 6372.795 : $radius = 3959.8712 ;


		return 2 * $radius * asin(min(1, sqrt( pow(sin(($l2-$l1)/2), 2) + cos($l1)*cos($l2)* pow(sin(($o2-$o1)/2), 2) )));
	}


	/**
	 * Calculates the bounds for the latitude and longitude based on the
	 * defined markers.
	 *
	 * @return	array	Array of minLat, minLong, maxLat, and maxLong.
	 */
	public function getLatLongBounds() {
		$minLat = 360;
		$maxLat = -360;
		$minLong = 360;
		$maxLong = -360;

		/* Find min and max zoom lat and long */
		if ( is_array( $this->groups ) )
		{
			foreach($this->groups as $key => $group) {
				foreach( $group->markers as $marker ) {
					if ($marker->getLatitude() < $minLat)
						$minLat = $marker->getLatitude();
					if ($marker->getLatitude() > $maxLat)
						$maxLat = $marker->getLatitude();

					if ($marker->getLongitude() < $minLong)
						$minLong = $marker->getLongitude();
					if ($marker->getLongitude() > $maxLong)
						$maxLong = $marker->getLongitude();
				}
			}
		}

		/* If we only have one point, expand the boundaries slightly to avoid
		   inifite zoom value */
		if ($maxLat == $minLat) {
			$maxLat = $maxLat + 0.001;
			$minLat = $minLat - 0.001;
		}
		if ($maxLong == $minLong) {
			$maxLong = $maxLong + 0.001;
			$minLat = $minLat - 0.001;
		}

		return array('maxLat' => $maxLat, 'maxLong' => $maxLong, 'minLat' => $minLat, 'minLong' => $minLong);
	}

	/**
	 * Adds an address to the currently list of markers rendered on the map.
	 *
	 * @param	string		The street address.
	 * @param	string		The city name.
	 * @param	string		The state or province.
	 * @param	string		The ZIP code.
	 * @param	string		The country name.
	 * @param	string		The title for the marker popup.
	 * @param	string		The description to be displayed in the marker popup.
	 * @param	integer		Minimum zoom level for marker to appear.
	 * @param	integer		Maximum zoom level for marker to appear.
	 * @return	added marker object
	 */
	public function addMarkerByAddress($street, $city, $state, $zip, $country, $title='', $description='', $minzoom = 0, $maxzoom = 18, $iconID='') {

		/* Geocode the address */
		$latlong = \JBartels\WecMap\Utility\Cache::lookup($street, $city, $state, $zip, $country);

		/* Create a marker at the specified latitude and longitdue */
		return $this->addMarkerByLatLong($latlong['lat'], $latlong['long'], $title, $description, $minzoom, $maxzoom, $iconID);
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
	 */
	public function addMarkerByLatLong($lat, $long, $title='', $description='', $minzoom = 0, $maxzoom = 18, $iconID='') {

		if(!empty($this->radius)) {
			$distance = $this->getDistance($this->lat, $this->long, $lat, $long);

			\TYPO3\CMS\Core\Utility\GeneralUtility::devLog($this->mapName.': Distance: '.$distance.' - Radius: '.$this->radius, 'wec_map_api');

			if(!empty($this->lat) && !empty($this->long) &&  $distance > $this->radius) {
				return null;
			}
		}

		if($lat != '' && $long != '') {
			$group =& $this->addGroup($minzoom, $maxzoom);
			$marker =  \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
			                          $this->getMarkerClassName(),
			                          $group->getMarkerCount(),
									  $lat,
									  $long,
									  $title,
									  $description,
									  $this->prefillAddress,
	  								  null,
									  '0xFF0000',
									  '0xFFFFFF',
									  $iconID);
			$group->addMarker($marker);
			$group->setDirections($this->directions);

			return $marker;
		}
		return null;
	}

	/**
	 * Adds an address string to the current list of markers rendered on the map.
	 *
	 * @param	string		The full address string.
	 * @param	string		The title for the marker popup.
	 * @param	string		The description to be displayed in the marker popup.
	 * @param	integer		Minimum zoom level for marker to appear.
	 * @param	integer		Maximum zoom level for marker to appear.
	 * @return	marker object
	 **/
	public function addMarkerByString($string, $title='', $description='', $minzoom = 0, $maxzoom = 18, $iconID = '') {

		// first split the string into it's components. It doesn't need to be perfect, it's just
		// put together on the other end anyway
		$address = explode(',', $string);

		$street = $address[0];
		$city = $address[1];
		$state = $address[2];
		$zip = '';
		$country = $address[3];

		/* Geocode the address */
		$latlong = \JBartels\WecMap\Utility\Cache::lookup($street, $city, $state, $zip, $country);

		/* Create a marker at the specified latitude and longitdue */
		return $this->addMarkerByLatLong($latlong['lat'], $latlong['long'], $title, $description, $minzoom, $maxzoom, $iconID);
	}

	/**
	 * Adds a marker by getting the address info from the TCA
	 *
	 * @param	string		The db table that contains the mappable records
	 * @param	integer		The uid of the record to be mapped
	 * @param	string		The title for the marker popup.
	 * @param	string		The description to be displayed in the marker popup.
	 * @param	integer		Minimum zoom level for marker to appear.
	 * @param	integer		Maximum zoom level for marker to appear.
	 * @return	marker object
	 **/
	public function addMarkerByTCA($table, $uid, $title='', $description='', $minzoom = 0, $maxzoom = 18, $iconID = '') {

		$uid = intval($uid);

		// first get the mappable info from the TCA
		$tca = $GLOBALS['TCA'][$table]['ctrl']['EXT']['wec_map'];

		if(!$tca) return false;
		if(!$tca['isMappable']) return false;

		// get address from db for this record
		$queryBuilder = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance( \TYPO3\CMS\Core\Database\ConnectionPool::class )
			->getQueryBuilderForTable( $table );
		$statement = $queryBuilder
			->select('*')
			->from( $table )
			->where(
				$queryBuilder->expr()->eq( 'uid', $queryBuilder->createNamedParameter( $uid, \PDO::PARAM_INT ) )
			)
			->execute();
		$record = $statement->fetch();

		if ( $tca['addressFields'] )
		{
			$streetfield  = \JBartels\WecMap\Utility\Shared::getAddressField($table, 'street');
			$cityfield    = \JBartels\WecMap\Utility\Shared::getAddressField($table, 'city');
			$statefield   = \JBartels\WecMap\Utility\Shared::getAddressField($table, 'state');
			$zipfield     = \JBartels\WecMap\Utility\Shared::getAddressField($table, 'zip');
			$countryfield = \JBartels\WecMap\Utility\Shared::getAddressField($table, 'country');

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

			/* Create a marker at the specified latitude and longitude */
			return $this->addMarkerByLatLong($latlong['lat'], $latlong['long'], $title, $description, $minzoom, $maxzoom, $iconID);
		}
		else if ( $tca['latlongFields'] )
		{
			$latfield  = \JBartels\WecMap\Utility\Shared::getLatLongField($table, 'lat');
			$longfield = \JBartels\WecMap\Utility\Shared::getLatLongField($table, 'long');

			$lat  = $record[$latfield];
			$long = $record[$longfield];

			/* Create a marker at the specified latitude and longitude */
			return $this->addMarkerByLatLong($lat, $long, $title, $description, $minzoom, $maxzoom, $iconID);
		}
		else
			return false;
	}

	/**
	 * adds a group to this map
	 *
	 * @return int id of this group
	 **/
	public function addGroup($minzoom = 1, $maxzoom = '') {

		if(!is_object($this->groups[$minzoom.':'.$maxzoom])) {
			$group =  \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\JBartels\WecMap\MapService\MarkerGroup::class, $this->groupCount, $minzoom, $maxzoom);
			$this->groupCount++;
			$group->setMapName($this->mapName);
			$this->groups[$minzoom.':'.$maxzoom] =& $group;
		}

		return $this->groups[$minzoom.':'.$maxzoom];
	}


	/**
	 * Returns the classname of the marker class.
	 * @return	string	The name of the marker class.
	 */
	protected function getMarkerClassName() {
		return $this->markerClassName;
	}


	public function markerCount() {
		return $this->markerCount;
	}


	/**
	 * Moves the marker-position if overlapping
	 */
	public function handleOverlappingMarker( $marker, $latDev, $longDev )
	{
		// Store coord pairs
		$cords = number_format ( $marker->latitude, 8 , '.' , '' ) . '-' . number_format ( $marker->longitude, 8 , '.' , '' );
		if( !isset( $this->devcache[ $cords] ) )
			$this->devcache[ $cords ] = 0;
		else
			$this->devcache[ $cords ]++;
		// Include linear deviation for markers in exact same location
		$marker->latitude = $marker->latitude + ( $this->devcache[$cords] * $latDev );
		$marker->longitude = $marker->longitude + ( $this->devcache[$cords] * $longDev );
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
	public function addMarkerByAddressWithTabs($street, $city, $state, $zip, $country, $tabLabels = null, $title=null, $description=null, $minzoom = 0, $maxzoom = 18, $iconID = '') {
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
	public function addMarkerByStringWithTabs($string, $tabLabels, $title=null, $description=null, $minzoom = 0, $maxzoom = 18, $iconID = '') {

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
	public function addMarkerByTCAWithTabs($table, $uid, $tabLabels, $title=null, $description=null, $minzoom = 0, $maxzoom = 18, $iconID = '') {
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
		$queryBuilder = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance( \TYPO3\CMS\Core\Database\ConnectionPool::class )
            ->getQueryBuilderForTable( $table );
        $statement = $queryBuilder
            ->select('*')
            ->from( $table )
            ->where(
                $queryBuilder->expr()->eq( 'uid', $queryBuilder->createNamedParameter( $uid, \PDO::PARAM_INT ) )
            )
            ->execute();
        $record = $statement->fetch();

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
	public function addMarkerByLatLongWithTabs($lat, $long, $tabLabels = null, $title=null, $description=null, $minzoom = 0, $maxzoom = 18, $iconID = '') {

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
	public function addMarkerIcon($dataArray, &$cObj=null ) {
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

		  	$this->icons[] = 'WecMap.addIcon("'. $this->mapName . '", "' . $dataArray['iconID'] . '", "' . $dataArray['imagepath'] . '", "' . $dataArray['shadowpath'] . '", [' . $dataArray['width'] . ', ' . $dataArray['height'] . '], [' . $dataArray['shadowWidth'] . ', ' . $dataArray['shadowHeight'] . '], [' . $dataArray['anchorX'] . ', ' . $dataArray['anchorY'] . '], [' . $dataArray['infoAnchorX'] . ', ' . $dataArray['infoAnchorY'] . ']);';
			return true;
		}

	}

	/**
	 * Adds a KML overlay to the map.
	 *
	 * @return void
	 **/
	public function addKML($url) {
		$this->kml[] = $url;
	}

	/**
	 * Sets the map center to a given address' coordinates.
	 *
	 * @return void
	 **/
	public function setCenterByAddress($street, $city, $state, $zip, $country = null) {

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
	protected function mapDiv() {
		$staticContent = $this->drawStaticMap();
		if ($this->static) {
			$height = '100%';
		} else {
			$height = $this->height . 'px';
		}
		return '<div id="'.$this->mapName.'" class="tx-wecmap-map" style="width:'.$this->width.'px; height:' . $height . ';">'.$staticContent.'</div>';
	}

	protected function js_setMapType($type) {
		return 'WecMap.setMapType("'. $this->mapName . '", ' . $type . ');';
	}

	protected function js_addMapType($type) {
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
	protected function js_setCenter($lat, $long, $zoom, $type) {
		if ($type) {
			return 'WecMap.setCenter("'. $this->mapName . '", ['.$lat.', '.$long.'], '.$zoom.', '.$type.');';
		} else {
			return 'WecMap.setCenter("'. $this->mapName . '", ['.$lat.', '.$long.'], '.$zoom.');';
		}
	}


	/**
	 * Creates Javascript to add map controls.
	 *
	 * @access	private
	 * @param	string		Javascript to add a control to the map.
	 */
	protected function js_addControl($control) {
		return 'WecMap.addControl("'. $this->mapName . '", '.$control.');';
	}

	/**
	 * generate the js for kml overlays
	 *
	 * @return string
	 **/
	protected function js_addKMLOverlay() {
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
	protected function js_icons() {
		/* If we're in the backend, get an absolute path.  Frontend can use a relative path. */
		$siteRelPath = PathUtility::stripPathSitePrefix(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('wec_map'));
		if (TYPO3_MODE=='BE')	{
			$path = \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('TYPO3_SITE_URL').$siteRelPath;
		} else {
			$path = $siteRelPath;
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
	protected function js_initialOpenInfoWindow() {
		if ( !is_array($this->markers) )
			return '';
		$markers = reset($this->markers);
		if (count(array($markers)) == 1 && $this->showInfoOnLoad) {
			foreach($this->groups as $key => $group) {
				foreach( $group->markers as $marker ) {
					return $marker->getInitialOpenInfoWindowJS();  // return 1st marker
				}
			}
		}
		return '';
	}

	/**
	 * Sets the center and zoom values for the current map dynamically, based
	 * on the markers to be displayed on the map.
	 *
	 * @access	private
	 * @return	none
 	 */
	public function autoCenterAndZoom() {

		/* Get center and lat/long spans from parent object */
		$latLongData = $this->getLatLongData();

		$lat = $latLongData['lat']; /* Center latitude */
		$long = $latLongData['long']; /* Center longitude */
		$latSpan = $latLongData['latSpan']; /* Total latitude the map covers */
		$longSpan = $latLongData['longSpan']; /* Total longitude the map covers */

		// process center
		if(!isset($this->lat) or !isset($this->long)) {
			\TYPO3\CMS\Core\Utility\GeneralUtility::devLog($this->mapName.': setting center to '.$lat.', '.$long, 'wec_map_api');
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
				\TYPO3\CMS\Core\Utility\GeneralUtility::devLog($this->mapName.': Called hook. Potentially new lat/long/zoom', 'wec_map_api', 2, array(
					Lat => $this->lat,
					Long => $this->long,
					Zoom => $this->zoom
				) );
			}
		}
	}

	/**
	 * Calculates the auto zoom
	 *
	 * @return int 	zoom level
	 **/
	public function getAutoZoom($latSpan, $longSpan) {

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
		\TYPO3\CMS\Core\Utility\GeneralUtility::devLog($this->mapName.': set zoom '.$zoom, 'wec_map_api');
		return $zoom;
	}

	/**
     * Checks if a map has markers or a
     * specific center.Otherwise, we have nothing
     * to draw.
     * @return        boolean        True/false whether the map is valid or not.
     */
    public function hasThingsToDisplay() {
        $valid = false;

        if(is_array($this->groups) and sizeof($this->groups) > 0) {
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
	public function hasHeightWidth() {
		if(!empty($this->width) && !empty($this->height)) {
			\TYPO3\CMS\Core\Utility\GeneralUtility::devLog($this->mapName.': height: '.$this->height.', width: '.$this->width, 'wec_map_api');
			return true;
		} else {
			\TYPO3\CMS\Core\Utility\GeneralUtility::devLog($this->mapName.': width or height missing', 'wec_map_api', 3);
			return false;
		}
	}

	/**
	 * Makes the marker info bubble show on load if there is only one marker on the map
	 *
	 * @return void
	 **/
	public function showInfoOnLoad() {

		$this->showInfoOnLoad = true;

		\TYPO3\CMS\Core\Utility\GeneralUtility::devLog($this->mapName.': Showing info bubble on load', 'wec_map_api');
	}

	/**
	 * Sets the maximum zoom level that autozoom will use
	 *
	 * @return void
	 **/
	public function setMaxAutoZoom($newZoom = null) {
		if($newZoom != null) {
			$this->maxAutoZoom = intval($newZoom);
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
	public function enableDirections($prefillAddress = false, $divID = null) {
		$this->prefillAddress = $prefillAddress;
		if($prefillAddress && $divID) {
			\TYPO3\CMS\Core\Utility\GeneralUtility::devLog($this->mapName.': enabling directions with prefill and written dirs', 'wec_map_api');
		} else if($prefillAddress && !$divID) {
			\TYPO3\CMS\Core\Utility\GeneralUtility::devLog($this->mapName.': enabling directions with prefill and without written dirs', 'wec_map_api');
		} else if(!$prefillAddress && $divID) {
			\TYPO3\CMS\Core\Utility\GeneralUtility::devLog($this->mapName.': enabling directions without prefill but with written dirs', 'wec_map_api');
		} else if(!$prefillAddress && !$divID) {
			\TYPO3\CMS\Core\Utility\GeneralUtility::devLog($this->mapName.': enabling directions without prefill and written dirs', 'wec_map_api');
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
	public function enableStatic($mode='automatic', $extent='all', $urlParam=false, $limit=50) {
		$this->static = true;
		if(empty($mode)) $mode = 'automatic';
		$this->staticMode = $mode;
		if(empty($extent)) $extent = 'all';
		$this->staticExtent = $extent;
		if(empty($urlParam)) $urlParam = false;
		$this->staticUrlParam = $urlParam;
		if(empty($limit)) $limit = 50;
		$this->staticLimit = $limit;

		\TYPO3\CMS\Core\Utility\GeneralUtility::devLog($this->mapName.': Enabling static maps: '.$mode.':'.$extent.':'.$urlParam.':'.$limit, 'wec_map_api');
	}

    /**
     * @return array extConf
     */
	protected function getExtConf()
    {
		if ( \TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) >= 9000000)
        	return GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('wec_map');
        else
        	return unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['wec_map']);
	}

}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/class.tx_wecmap_map.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/class.tx_wecmap_map.php']);
}
?>