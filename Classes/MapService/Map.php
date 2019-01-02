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

	public $mapOptions = array();

	/**
	 * Class constructor stub.  Override in the map_service classes. Look there for
	 * examples.
	 */
	function __construct() {}

	/**
	 * Stub for the drawMap function.  Individual map services should implement
	 * this method to output their own HTML and Javascript.
	 *
	 */
	function drawMap() {}

	/**
	 * Generic stub to set special map-options
	 */
	function addOption( $key, $value ) {
		$this->mapOptions[ $key ] = $value;
	}


	/**
	 * Stub for the autoCenterAndZoom function.  Individual map services should
	 * implement this method to perform their own centering and zooming based
	 * on map attributes.
	 */
	function autoCenterAndZoom(){}

	/**
	 * Calculates the center and lat/long spans from the current markers.
	 *
	 * @access	private
	 * @return	array		Array of lat/long center and spans.  Array keys
	 *						are lat, long, latSpan, and longSpan.
	 */
	function getLatLongData() {

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
	function getFarthestLatLongFromCenter() {

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
	function setCenter($lat, $long) {
		$this->lat  = $lat;
		$this->long = $long;
	}

	/**
	 * Sets the zoom value for the current map to specified values.
	 *
	 * @param	integer		The initial zoom level for the map.
	 * @return	none
	 */
	function setZoom($zoom) {
		$this->zoom = $zoom;
	}

	/**
	 * Sets the radius from the center that markers need to be within
	 *
	 * @param	integer		The radius from the center
	 * @param 	boolean		Whether it's kilometers or miles
	 * @return	none
	 */
	function setRadius($radius, $kilometers = false) {
		$this->kilometers = $kilometers;
		$this->radius = $radius;
		if(TYPO3_DLOG) {
			$kilometers ? $km = 'km':$km = 'miles';
			\TYPO3\CMS\Core\Utility\GeneralUtility::devLog($this->mapName.': setting radius '.$radius.' '.$km, 'wec_map_api');
		}
	}

	# haversine formula to calculate distance between two points
	function getDistance($lat1, $long1, $lat2, $long2)
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
	function getLatLongBounds() {
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
	function addMarkerByAddress($street, $city, $state, $zip, $country, $title='', $description='', $minzoom = 0, $maxzoom = 18, $iconID='') {

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
	function addMarkerByLatLong($lat, $long, $title='', $description='', $minzoom = 0, $maxzoom = 18, $iconID='') {

		if(!empty($this->radius)) {
			$distance = $this->getDistance($this->lat, $this->long, $lat, $long);

			if(TYPO3_DLOG) {
				\TYPO3\CMS\Core\Utility\GeneralUtility::devLog($this->mapName.': Distance: '.$distance.' - Radius: '.$this->radius, 'wec_map_api');
			}

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
	function addMarkerByString($string, $title='', $description='', $minzoom = 0, $maxzoom = 18, $iconID = '') {

		// first split the string into it's components. It doesn't need to be perfect, it's just
		// put together on the other end anyway
		$address = explode(',', $string);

		$street = $address[0];
		$city = $address[1];
		$state = $address[2];
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
	function addMarkerByTCA($table, $uid, $title='', $description='', $minzoom = 0, $maxzoom = 18, $iconID = '') {

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
	function addGroup($minzoom = 1, $maxzoom = '') {

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
	function getMarkerClassName() {
		return $this->markerClassName;
	}


	function markerCount() {
		return $this->markerCount;
	}


	/**
	 * Moves the marker-position if overlapping
	 */
	function handleOverlappingMarker( $marker, $latDev, $longDev )
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
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/class.tx_wecmap_map.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/class.tx_wecmap_map.php']);
}
?>