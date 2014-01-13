<?php
/***************************************************************
* Copyright notice
*
* (c) 2005-2009 Christian Technology Ministries International Inc.
* (c) 2013 J. Bartels
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

require_once(t3lib_extMgm::extPath('wec_map').'class.tx_wecmap_map.php');
require_once(t3lib_extMgm::extPath('wec_map').'class.tx_wecmap_cache.php');
require_once(t3lib_extMgm::extPath('wec_map').'map_service/google/class.tx_wecmap_map_google.php');
require_once(t3lib_extMgm::extPath('wec_map').'class.tx_wecmap_shared.php');

/**
 * General purpose backend class for the WEC Map extension.  This class
 * provides user functions for displaying geocode status and maps within
 * TCEForms.
 *
 * @author Web-Empowered Church Team <map@webempoweredchurch.org>
 * @package TYPO3
 * @subpackage tx_wecmap
 */
class tx_wecmap_backend {

	function processDatamap_postProcessFieldArray($status, $table, $id, &$fieldArray, &$reference) {
		global $TCA;
		t3lib_div::loadTCA($table);

		$tca = &$TCA[$table]['ctrl']['EXT']['wec_map'];
		$isMappable = $tca['isMappable'];

		if($isMappable) {
			if ( $tca['addressFields'] )
			{
				/* Get the names of the fields from the TCA */
				$streetField  = tx_wecmap_shared::getAddressField($table, 'street');
				$cityField    = tx_wecmap_shared::getAddressField($table, 'city');
				$stateField   = tx_wecmap_shared::getAddressField($table, 'state');
				$zipField     = tx_wecmap_shared::getAddressField($table, 'zip');
				$countryField = tx_wecmap_shared::getAddressField($table, 'country');

				/* Get the row that we're saving */
				$row = t3lib_befunc::getRecord($table, $id);

				/* @todo	Eliminate double save */
				tx_wecmap_backend::drawGeocodeStatus($row[$streetField], $row[$cityField], $row[$stateField], $row[$zipField], $row[$countryField]);
			}
			else if ( $tca['latlongFields'] )
			{
				/* Get the names of the fields from the TCA */
				$latField  = tx_wecmap_shared::getLatLongField($table, 'lat');
				$longField    = tx_wecmap_shared::getLatLongField($table, 'long');

				/* Get the row that we're saving */
				$row = t3lib_befunc::getRecord($table, $id);

				/* @todo	Eliminate double save */
				tx_wecmap_backend::drawLatlongStatus($row[$latField], $row[$longField]);
			}
		}
	}

	function processDatamap_preProcessFieldArray(array &$incomingFieldArray, $table, $id, t3lib_TCEmain &$reference) {
		global $TCA;
		t3lib_div::loadTCA($table);

		$tca = $TCA[$table]['ctrl']['EXT']['wec_map'];
		$isMappable = $tca['isMappable'];

		if($isMappable) {
			if ( $tca['latlongFields'] )
			{
				/* Grab the lat and long that were posted */
				$newlat = t3lib_div::_GP('wec_map_lat');
				$newlong = t3lib_div::_GP('wec_map_long');

				$origlat = t3lib_div::_GP('wec_map_original_lat');
				$origlong = t3lib_div::_GP('wec_map_original_long');

				/* If the lat/long changed, then insert a new entry into the cache or update it. */
				if((($newlat != $origlat) or ($newlong != $origlong)) and (!empty($newlat) && !empty($newlong)) and (is_numeric($newlat) && is_numeric($newlong))) {
					/* Get the names of the fields from the TCA */
					$latField  = tx_wecmap_shared::getLatLongField($table, 'lat');
					$longField = tx_wecmap_shared::getLatLongField($table, 'long');

					$incomingFieldArray[$latField] = $newlat;
					$incomingFieldArray[$longField] = $newlong;
				}
			}
		}
	}

	/**
	 * Checks the geocoding status for the current record.  This function is
	 * mainly responsible for taking backend record data and handing it to
	 * drawGeocodeStatus().
	 *
	 * @param	array	Array with information about the current field.
	 * @param	object	Parent object.  Instance of t3lib_tceforms.
	 * @return	string	HTML output of current geocoding status and editing form.
	 */
	function checkGeocodeStatus($PA, &$fobj) {
		// if geocoding status is disabled, return
		if(!tx_wecmap_backend::getExtConf('geocodingStatus')) return;

		$street  = tx_wecmap_backend::getFieldValue('street', $PA);
		$city    = tx_wecmap_backend::getFieldValue('city', $PA);
		$state   = tx_wecmap_backend::getFieldValue('state', $PA);
		$zip     = tx_wecmap_backend::getFieldValue('zip', $PA);
		$country = tx_wecmap_backend::getFieldValue('country', $PA);

		return tx_wecmap_backend::drawGeocodeStatus($street, $city, $state, $zip, $country);
	}

	/**
	 * Checks the geocoding status for the current record.  This function is
	 * mainly responsible for taking backend record data and handing it to
	 * drawLatlongStatus().
	 *
	 * @param	array	Array with information about the current field.
	 * @param	object	Parent object.  Instance of t3lib_tceforms.
	 * @return	string	HTML output of current geocoding status and editing form.
	 */
	function checkLatLongStatus($PA, &$fobj) {
		// if geocoding status is disabled, return
		if(!tx_wecmap_backend::getExtConf('geocodingStatus')) return;

		$lat  = tx_wecmap_backend::getFieldValue('lat', $PA);
		$long = tx_wecmap_backend::getFieldValue('long', $PA);

		return tx_wecmap_backend::drawLatlongStatus($lat, $long);
	}

	/**
	 * Checks the goecoding status for the current FlexForm.  This function is
	 * mainly responsible for taking FlexForm data and handing it to
	 * drawGeocodeStatus().
	 *
	 * @param	array	Array with information about the current FlexForm.
	 * @param	object	Parent object.  Instance of t3lib_tceforms.
	 * @return	string	HTML output of current geocoding status and editing form.
	 * @todo	Does our method of digging into FlexForms mess up localization?
	 */
	function checkGeocodeStatusFF($PA, &$fobj) {

		// if geocoding status is disabled, return
		if(!tx_wecmap_backend::getExtConf('geocodingStatus')) return;

		$street  = tx_wecmap_backend::getFieldValueFromFF('street', $PA);
        $city    = tx_wecmap_backend::getFieldValueFromFF('city', $PA);
        $state   = tx_wecmap_backend::getFieldValueFromFF('state', $PA);
        $zip     = tx_wecmap_backend::getFieldValueFromFF('zip', $PA);
        $country = tx_wecmap_backend::getFieldValueFromFF('country', $PA);


		return tx_wecmap_backend::drawGeocodeStatus($street, $city, $state, $zip, $country);
	}

	/**
	 * Checks the geocoding status of the address and displays an editing form.
	 *
	 * @param	string	Street portion of the address.
	 * @param	string	City portion of the address.
	 * @param	string	State portion of the address.
	 * @param	string	ZIP code portion of the address.
	 * @param	string	Country portion of the address.
	 * @return	string	HTML output of current geocoding status and editing form.
	 */
	function drawGeocodeStatus($street, $city, $state, $zip, $country) {
		global $LANG;
		$LANG->includeLLFile('EXT:wec_map/locallang_db.xml');

		/* Normalize the address before we try to insert it or anything like that */
		tx_wecmap_cache::normalizeAddress($street, $city, $state, $zip, $country);

		// if there is no info about the user, return different status
		if(!$city) {
			return $LANG->getLL('geocodeNoAddress');
		}

		/* Grab the lat and long that were posted */
		$newlat = t3lib_div::_GP('wec_map_lat');
		$newlong = t3lib_div::_GP('wec_map_long');

		$origlat = t3lib_div::_GP('wec_map_original_lat');
		$origlong = t3lib_div::_GP('wec_map_original_long');

		/* If the new lat/long are empty, delete our cached entry */
		if (empty($newlat) && empty($newlong) && !empty($origlat) && !empty($origlong)) {
			tx_wecmap_cache::delete($street, $city, $state, $zip, $country);
		}

		/* If the lat/long changed, then insert a new entry into the cache or update it. */
		if((($newlat != $origlat) or ($newlong != $origlong)) and (!empty($newlat) && !empty($newlong)) and (is_numeric($newlat) && is_numeric($newlong))) {
			tx_wecmap_cache::insert($street, $city, $state, $zip, $country, $newlat, $newlong);
		}

		/* Get the lat/long and status from the geocoder */
		$latlong = tx_wecmap_cache::lookup($street, $city, $state, $zip, $country);
		$status = tx_wecmap_cache::status($street, $city, $state, $zip, $country);

		switch($status) {
			case -1:
				$status = $LANG->getLL('geocodeFailed');
				break;
			case 0:
				$status = $LANG->getLL('geocodeNotPerformed');
				break;
			case 1:
				$status = $LANG->getLL('geocodeSuccessful');
				break;
		}

		$form = '<label for="wec_map_lat">'.$LANG->getLL('latitude').'</label> <input id="wec_map_lat" name="wec_map_lat" value="'.htmlspecialchars($latlong['lat']).'" />
				 <label for="wec_map_long">'.$LANG->getLL('longitude').'</label>  <input id="wec_map_long" name="wec_map_long" value="'.htmlspecialchars($latlong['long']).'" />
				 <input type="hidden" name="wec_map_original_lat" value="'.htmlspecialchars($latlong['lat']).'" />
				 <input type="hidden" name="wec_map_original_long" value="'.htmlspecialchars($latlong['long']).'" />';

		return '<p>'.$status.'</p><p>'.$form.'</p>';
	}

	/**
	 * displays an editing form.
	 *
	 * @param	string	Latitude portion of the address.
	 * @param	string	Longitude portion of the address.
	 * @return	string	HTML output of current geocoding status and editing form.
	 */
	function drawLatlongStatus($lat, $long) {
		global $LANG;
		$LANG->includeLLFile('EXT:wec_map/locallang_db.xml');

		/* Grab the lat and long that were posted */
		$newlat = t3lib_div::_GP('wec_map_lat');
		$newlong = t3lib_div::_GP('wec_map_long');

		$origlat = t3lib_div::_GP('wec_map_original_lat');
		$origlong = t3lib_div::_GP('wec_map_original_long');

		$form = '<input type="hidden" id="wec_map_lat" name="wec_map_lat" value="'.htmlspecialchars($lat).'" />
				 <input type="hidden" id="wec_map_long" name="wec_map_long" value="'.htmlspecialchars($long).'" />
				 <input type="hidden" name="wec_map_original_lat" value="'.htmlspecialchars($lat).'" />
				 <input type="hidden" name="wec_map_original_long" value="'.htmlspecialchars($long).'" />';

		return '<p>'.$form.'</p>';
	}

	/**
	 * Draws a backend map.
	 * @param		array		Array with information about the current field.
	 * @param		object		Parent object.  Instance of t3lib_tceforms.
	 * @return		string		HTML to display the map within a backend record.
	 */
	function drawMap($PA, $fobj) {
		$width = '400';
		$height = '400';

		$street  = tx_wecmap_backend::getFieldValue('street', $PA);
		$city    = tx_wecmap_backend::getFieldValue('city', $PA);
		$state   = tx_wecmap_backend::getFieldValue('state', $PA);
		$zip     = tx_wecmap_backend::getFieldValue('zip', $PA);
		$country = tx_wecmap_backend::getFieldValue('country', $PA);

		$description = $street.'<br />'.$city.', '.$state.' '.$zip.'<br />'.$country;

		$map = t3lib_div::makeInstance( 'tx_wecmap_map_google', $apiKey, $width, $height);
		$marker = $map->addMarkerByAddress($street, $city, $state, $zip, $country, '<h1>Address</h1>', $description);
		// enable dragging to correct lat/long interactively
		if ( $marker )
			$marker->setDraggable(true);

		// add some default controls to the map
		$map->addControl('largeMap');
		$map->addControl('scale');
		$map->addControl('mapType');
		$map->enableDirections(true);

		$content = $map->drawMap();

		return $content;
	}

	/**
	 * Draws a backend map.
	 * @param		array		Array with information about the current field.
	 * @param		object		Parent object.  Instance of t3lib_tceforms.
	 * @return		string		HTML to display the map within a backend record.
	 */
	function drawLatLongMap($PA, $fobj) {
		$width = '400';
		$height = '400';

		$lat  = tx_wecmap_backend::getFieldValue('lat', $PA);
		$long = tx_wecmap_backend::getFieldValue('long', $PA);

		$description = $lat.','.$long;

		$map = t3lib_div::makeInstance( 'tx_wecmap_map_google', $apiKey, $width, $height);
		$marker = $map->addMarkerByLatLong($lat, $long, $description );
		// enable dragging to correct lat/long interactively
		if ( $marker )
			$marker->setDraggable(true);
		// add some default controls to the map
		$map->addControl('largeMap');
		$map->addControl('scale');
		$map->addControl('mapType');
		$map->enableDirections(true);

		$content = $map->drawMap();

		return $content;
	}

	/**
	 * Checks the TCA for address mapping rules and returns the address value.
	 * If a mapping rule is defined, this tells us what field contains address
	 * related information.  If no rules are defined, we pick default fields
	 * to use.
	 *
	 * @param	string	The portion of the address we're trying to map.
	 * @param	array	Array of field related data.
	 * @return	string	The specified portion of the address.
	 * @todo			Refactor this to use getFieldNameForTable().
	 */
	function getFieldValue($key, $PA) {
		global $TCA;
		$table = $PA['table'];

        $row = $PA['row'];

		/* If the address mapping array has a mapping for this address, use it */
        $addressFields = $PA['fieldConf']['config']['params']['addressFields'];
        if(isset($addressFields[$key])) {
            $fieldName = $addressFields[$key];
        } else {
			/* If the address mapping array has a mapping for this lat/long, use it */
			$latlongFields = $PA['fieldConf']['config']['params']['latlongFields'];
			if(isset($latlongFields[$key])) {
				$fieldName = $latlongFields[$key];
			} else {
				/* If the ctrl section of the TCA has a name, use it */
				if(isset($ctrlAddressFields[$key])) {
					$fieldName = tx_wecmap_shared::getAddressField($table, $key);
				} else {
					/* Otherwise, use the default name */
					$fieldName = $key;
				}
			}
        }

		/* If the source data has a value for the address field, grab it */
        if (isset($row[$fieldName])) {
            $value = $row[$fieldName];
        } else {
			/* Otherwise, use an empty string */
            $value = '';
        }

        return $value;
    }

	/**
	 * Checks the FlexForm for address mapping rules and returns the address value.
	 * If a mapping rule is defined, this tells us what field contains address
	 * related information.  If no rules are defined, we pick default fields
	 * to use.
	 *
	 * @param	string	The portion of the address we're trying to map.
	 * @param	array	Array of field related data.
	 * @return	string	The specified portion of the address.
	 */
	function getFieldValueFromFF($key, $PA) {
		$flexForm = t3lib_div::xml2array($PA['row']['pi_flexform']);
		if(is_array($flexForm)) {
			$flexForm = $flexForm['data']['default']['lDEF'];

			/* If the address mapping array has a map for this address, use it */
			$addressFields = $PA['fieldConf']['config']['params']['addressFields'];
			if(isset($addressFields[$key])) {
				$fieldName = $addressFields[$key];
			} else {
				$latlongFields = $PA['fieldConf']['config']['params']['latlongFields'];
				if(isset($latlongFields[$key])) {
					$fieldName = $latlongFields[$key];
				} else {
					$fieldName = $key;
				}
			}


			/* If the source data has a value for the addres field, grab it */
			if (isset($flexForm[$fieldName]['vDEF'])) {
				$value = $flexForm[$fieldName]['vDEF'];
			} else {
				$value = '';
			}
		} else {
			$value = '';
		}

        return $value;
	}

	/**
	 * Gets extConf from TYPO3_CONF_VARS and returns the specified key.
	 *
	 * @param	string	The key to look up in extConf.
	 * @return	mixed	The value of the specified key.
	 */
	function getExtConf($key) {
		/* Make an instance of the Typoscript parser */
		require_once(PATH_t3lib.'class.t3lib_tsparser.php');
		$tsParser = t3lib_div::makeInstance('t3lib_TSparser');

		/* Unserialize the TYPO3_CONF_VARS and extract the value using the parser */
		$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['wec_map']);
		$valueArray = $tsParser->getVal($key, $extConf);

		if (is_array($valueArray)) {
			$returnValue = $valueArray[0];
		} else {
			$returnValue = '';
		}

		return $returnValue;
	}

	/**
	 * Returns a list of all mappable tables
	 *
	 * @return void
	 **/
	function getMappableTables($config=null) {
		if(!isset($config)) {
			$config = array();
		}
		global $LANG;

		foreach( $GLOBALS['TCA'] as $table => $conf ) {
			t3lib_div::loadTCA($table);
			$isMappable = $conf['ctrl']['EXT']['wec_map']['isMappable'];
			if($isMappable) {
				$title = $LANG->sL($conf['ctrl']['title']);
				$config['items'][] = Array($title . ' ('.$table.')', $table);
			}
		}
		return $config;
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/class.tx_wecmap_backend.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/class.tx_wecmap_backend.php']);
}

?>