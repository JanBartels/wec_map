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

require_once(t3lib_extMgm::extPath('wec_map').'class.tx_wecmap_backend.php');

/**
 * Main address lookup class for the wec_map extension.  Looks up existing
 * values in cache tables or initiates service chain to perform a lookup.
 * Also provides basic administrative functions for managing entries in the
 * cache.
 *
 * @author Web-Empowered Church Team <map@webempoweredchurch.org>
 * @package TYPO3
 * @subpackage tx_wecmap
 */
class tx_wecmap_cache {

	function lookup($street, $city, $state, $zip, $country, $key='', $forceLookup=false) {
		$fakeObject = null;
		return tx_wecmap_cache::lookupWithCallback($street, $city, $state, $zip, $country, $key, $forceLookup, $fakeObject);
	}

	/**
	 * Looks up the latitude and longitude of a specified address. Cache tables
	 * are searched first, followed by external service lookups.
	 *
	 * @param	string		The street address.
	 * @param	string		The city name.
	 * @param	string		The state name.
	 * @param	string		This ZIP code.
	 * @param	string		The country name.
	 * @param	string		The optional API key to use in the lookup.
	 * @param	boolean		Force a new lookup for address.
	 * @return	array		Lat/long array for specified address.  Null if lookup fails.
	 */
	function lookupWithCallback($street, $city, $state, $zip, $country, $key='', $forceLookup=false, &$pObj) {

		/* Do some basic normalization on the address */
		tx_wecmap_cache::normalizeAddress($street, $city, $state, $zip, $country);

		/* If we have enough address information, try to geocode. If not, return null. */
		if(tx_wecmap_cache::isEmptyAddress($street, $city, $state, $zip, $country)) {
			$latlong = null;
		} else {
			/* Look up the address in the cache table. */
			$latlong = tx_wecmap_cache::find($street, $city, $state, $zip, $country);

			/* Didn't find a cached match */
			if (is_null($latlong)) {
				/* Intiate service chain to find lat/long */
				$serviceChain='';

				while (is_object($lookupObj = t3lib_div::makeInstanceService('geocode', '', $serviceChain))) {
					$serviceChain.=','.$lookupObj->getServiceKey();
					$latlong = $lookupObj->lookup($street, $city, $state, $zip, $country, $key);

					if(method_exists($pObj, 'callback_lookupThroughGeocodeService')) {
						$pObj->callback_lookupThroughGeocodeService();
					}

					/* If we found a match, quit. Otherwise proceed to next best service */
					if($latlong) {
						break;
					}
				}

				/* Insert the lat/long into the cache.  */
				tx_wecmap_cache::insert($street, $city, $state, $zip, $country, $latlong['lat'], $latlong['long']);
				$latlong['lat'] = trim($latlong['lat'],'0');
				$latlong['long'] = trim($latlong['long'],'0');
			}

			/* Return the lat/long, either from cache table for from fresh lookup */
			if ($latlong['lat'] == 0 and $latlong['long'] == 0){
				$latlong = null;
			}
		}

		return $latlong;

	}


	/**
	 * Performs basic normalize on the address compontents.  Should be called
	 * before any function searches cached data by address name or inserts
	 * values into the cache. All parameters are passed by reference and
	 * normalized.
	 *
	 * @param	string		The street address.
	 * @param	string		The city name.
	 * @param	string		The state name.
	 * @param	string		This ZIP code.
	 * @param	string		The country name.
	 * @param	string		The optional API key to use in the lookup.
	 * @return	none
	 */
	function normalizeAddress(&$street, &$city, &$state, &$zip, &$country) {

		// pseudo normalize data: first letter uppercase.
		// @todo: get rid of this once we implement normalization properly
		$street = ucwords($street);
		$city 	= ucwords($city);
		$state 	= ucwords($state);

		// some zip codes contain letters, so just upper case them all
		$zip 	= strtoupper($zip);

		// if length of country string is 3 or less, it's probably an abbreviation;
		// make it all upper case then
		if(strlen($country) < 4) {
			$country = strtoupper($country);
		} else {
			$country= ucwords($country);
		}

		// to somehow normalize the data we get, we will check for country codes like DEU that the geocoder
		// doesn't understand and look up a real country name from static_info_countries
		// 1. check if static_info_tables is available
		if (t3lib_extmgm::isLoaded('static_info_tables')) {

			// 2. check the length of the country and do lookup only if it's 2 or 3 characters
			$length = strlen($country);
			if($length == 2) {

				// try to find a country with that two character code
				$rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('cn_short_en', 'static_countries', 'cn_iso_2='.$GLOBALS['TYPO3_DB']->fullQuoteStr($country,static_countries));
				$newCountry = $rows[0]['cn_short_en'];
				if(!empty($newCountry)) $country = $newCountry;
			} else if ($length == 3) {
				// try to find a country with that two character code
				$rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('cn_short_en', 'static_countries', 'cn_iso_3='.$GLOBALS['TYPO3_DB']->fullQuoteStr($country,static_countries));
				$newCountry = $rows[0]['cn_short_en'];
				if(!empty($newCountry)) $country = $newCountry;
			}
		}

		// if we still have no country, use the default one
		if(empty($country)) {
			$country = tx_wecmap_backend::getExtConf('defaultCountry');
			// TODO: devlog start
			if(TYPO3_DLOG) {
				t3lib_div::devLog('Using default country: '.$country, 'wec_map_geocode');
			}
			// devlog end
		}
	}


	/**
	 * Returns the current geocoding status.  Geocoding may be successfull,
	 * failed, or may not have been attempted.
	 *
	 * @param	string		The street address.
	 * @param	string		The city name.
	 * @param	string		The state name.
	 * @param	string		This ZIP code.
	 * @param	string		The country name.
	 * @return	integer		Status code. -1=Failed, 0=Not Completed, 1=Successfull.
	 */
	function status($street, $city, $state, $zip, $country) {
		/* Look up the address in the cache table */
		$latlong = tx_wecmap_cache::find($street, $city, $state, $zip, $country);

		/* Found a cached match */
		if ($latlong) {
			if($latlong['lat']==0 and $latlong['long']==0) {
				$statusCode = -1; /* Previous lookup failed */
			} else {
				$statusCode = 1; /* Previous lookup succeeded */
			}
		} else {
			$statusCode = 0; /* Lookup has not been performed */
		}

		return $statusCode;
	}



	/**
	 * Looks up the latitude and longitude of a specified address in the cache
	 * table only.
	 *
	 * @param	string		The street address.
	 * @param	string		The city name.
	 * @param	string		The state name.
	 * @param	string		This ZIP code.
	 * @param	string		The country name.
	 * @return	array		Lat/long array for specified address.  Null if lookup fails.
	 */
	function find($street, $city, $state, $zip, $country) {
		$hash = tx_wecmap_cache::hash($street, $city, $state, $zip, $country);
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_wecmap_cache', ' address_hash="'.$hash.'"');
		if ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
			$latlong = array('lat' => $row['latitude'], 'long' => $row['longitude']);
			return $latlong;
		} else {
			return null;
		}
	}


	/**
	 * Inserts an address with a specified latitude and longitdue into the cache table.
	 *
	 * @param	string		The street address.
	 * @param	string		The city name.
	 * @param	string		The state name.
	 * @param	string		This ZIP code.
	 * @param	string		The country name.
	 * @param	string		Latidude.
	 * @param	string		Longitude.
	 * @return	none
	 */
	function insert($street, $city, $state, $zip, $country, $lat, $long) {
		/* Check if value is already in DB */
		if (tx_wecmap_cache::find($street,$city,$state,$zip,$country)) {
			/* Update existing entry */
			$latlong = array('latitude' => $lat, 'longitude' => $long);
			$result = $GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_wecmap_cache', "address_hash='".tx_wecmap_cache::hash($street, $city, $state, $zip, $country)."'", $latlong);
		} else {
			/* Insert new entry */
			$insertArray = array();
			$insertArray['address_hash'] = tx_wecmap_cache::hash($street, $city, $state, $zip, $country);
			$insertArray['address'] = $street.' '.$city.' '.$state.' '.$zip.' '.$country;
			$insertArray['latitude'] = $lat;
			$insertArray['longitude'] = $long;

			/* Write address to cache table */
			$result = $GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_wecmap_cache', $insertArray);
		}
	}

	/**
	 * Update a cached entry based on its address hash.
	 *
	 * @param	string		Address hash.
	 * @param	float		New latitude.
	 * @param	float		New longitude.
	 * @return	none
	 */
	function updateByUID($uid, $lat, $long) {
		$latlong = array('latitude' => $lat, 'longitude' => $long);
		$result = $GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_wecmap_cache', "address_hash='".$uid."'", $latlong);
	}

	/**
	 * Deletes a cached entry based on its address hash.
	 *
	 * @return	none
	 */
	function deleteByUID($uid) {
		$result = $GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_wecmap_cache', "address_hash='".$uid."'");
	}

	/**
	 * Deletes all cached entries.
	 *
	 * @return	none
	 */
	function deleteAll() {
		$result = $GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_wecmap_cache','');
	}

	/**
	 * Deletes a specified address from the cache table.
	 *
	 * @param	string		The street address.
	 * @param	string		The city name.
	 * @param	string		The state name.
	 * @param	string		This ZIP code.
	 * @param	string		The country name.
	 * @return	none
	 */
	function delete($street, $city, $state, $zip, $country) {
		$result = $GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_wecmap_cache', "address_hash='".tx_wecmap_cache::hash($street, $city, $state, $zip, $country)."'");
	}

	/**
	 * Creates the address hash, which acts as a unique identifier for the cache table.
	 *
	 * @param	string		The street address.
	 * @param	string		The city name.
	 * @param	string		The state name.
	 * @param	string		This ZIP code.
	 * @param	string		The country name.
	 * @return	string		MD5 hash of the address.
	 */
	function hash($street, $city, $state, $zip, $country) {
		$address_string = $street.' '.$city.' '.$state.' '.$zip.' '.$country;
		return md5($address_string);
	}

	/**
	 *  Checks if the minimum amount of address data is available before
	 *  geocoding.
	 *
	 * @param	string		The street address.
	 * @param	string		The city name.
	 * @param	string		The state name.
	 * @param	string		This ZIP code.
	 * @param	string		The country name.
	 * @return	string		True if an address is empty. False otherwise.
	 */
	function isEmptyAddress($street, $city, $state, $zip, $country) {
		if($street == '' and $city == '' and $state == '' and $zip == '' and $country == '') {
			$isEmptyAddress = true;
		} else {
			$isEmptyAddress = false;
		}

		return $isEmptyAddress;
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/class.tx_wecmap_cache.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/class.tx_wecmap_cache.php']);
}

?>