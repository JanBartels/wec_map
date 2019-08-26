<?php
/***************************************************************
* Copyright notice
*
* (c) 2005-2009 Christian Technology Ministries International Inc.
* All rights reserved
* (c) 2011-2019 Jan Bartels, j.bartels@arcor.de, Google API V3
*
* parts from static_info_tables:
*  (c) 2013 Stanislas Rolland <typo3(arobas)sjbr.ca>
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
 * Service 'Google Maps V3 Address Lookup' for the 'wec_map' extension.
 *
 * @author	j.bartels
 */

namespace JBartels\WecMap\GeocodeService;

/**
 * Service providing lat/long lookup via the Google Maps web service.
 *
 * @author Web-Empowered Church Team <map@webempoweredchurch.org>
 * @package TYPO3
 * @subpackage tx_wecmap
 */
class Google extends \TYPO3\CMS\Core\Service\AbstractService {
	var $prefixId = 'WecMapGeocodeGoogle';		// for temporary files

	/**
	 * Returns the type of an iso code: nr, 2, 3
	 * code copied from static_info_tables
	 *
	 * @param	string		iso code
	 * @return	string		iso code type
	 */
	protected static function isoCodeType ($isoCode) {
		$type = '';
		$isoCodeAsInteger = \TYPO3\CMS\Core\Utility\MathUtility::canBeInterpretedAsInteger($isoCode);
		if ($isoCodeAsInteger) {
			$type = 'nr';
		} else if (strlen($isoCode) == 2) {
			$type = '2';
		} else if (strlen($isoCode) == 3) {
			$type = '3';
		}
		return $type;
	}

	/**
	 * Get a list of countries by specific parameters or parts of names of countries
	 * in different languages. Parameters might be left empty.
	 * code copied from static_info_tables
	 *
	 * @param	string		a name of the country or a part of it in any language
	 * @param	string		ISO alpha-2 code of the country
	 * @param	string		ISO alpha-3 code of the country
	 * @param	array		Database row.
	 * @return	array		Array of rows of country records
	 */
	protected static function fetchCountries ($country, $iso2='', $iso3='', $isonr='') {
		$queryBuilder = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance( \TYPO3\CMS\Core\Database\ConnectionPool::class )
            ->getQueryBuilderForTable('static_countries');
        $queryBuilder->getRestrictions()
            ->removeAll();
        $statement = $queryBuilder
            ->select('*')
            ->from('static_countries');

		if ($country != '') {
			$statement = $statement->where( $queryBuilder->expr()->logicalOr(
				expr()->like(
					'cn_official_name_local', 
					$queryBuilder->createNamedParameter( '%' . $queryBuilder->escapeLikeWildcards( trim( $country ) ) . '%' )
				),
				expr()->like(
					'cn_official_name_en', 
					$queryBuilder->createNamedParameter( '%' . $queryBuilder->escapeLikeWildcards( trim( $country ) ) . '%' )
				),
				expr()->like(
					'cn_short_local', 
					$queryBuilder->createNamedParameter( '%' . $queryBuilder->escapeLikeWildcards( trim( $country ) ) . '%' )
				)
			) );
		} else if ($isonr != '') {
			$statement = $statement->where( $queryBuilder->expr()->eq(
				'cn_iso_nr', 
				$queryBuilder->createNamedParameter( trim( $isonr ) )
			) );
		} else if ($iso2 != '') {
			$statement = $statement->where( $queryBuilder->expr()->eq(
				'cn_iso_2', 
				$queryBuilder->createNamedParameter( trim( $isonr ) )
			) );
		} else if ($iso3 !='') {
			$statement = $statement->where( $queryBuilder->expr()->eq(
				'cn_iso_3', 
				$queryBuilder->createNamedParameter( trim( $isonr ) )
			) );
		} else {
			$statement = $statement->where( $queryBuilder->expr()->eq( '1', '0' ) );
		}

		$rcArray = $statement->execute()->fetchAll();

		return $rcArray;
	}

	/**
	 * Performs an address lookup using the google web service.
	 *
	 * @param	string	The street address.
	 * @param	string	The city name.
	 * @param	string	The state name.
	 * @param	string	The ZIP code.
	 * @return	array		Array containing latitude and longitude.  If lookup failed, empty array is returned.
	 */
	function lookup($street, $city, $state, $zip, $country)	{

		$addressString = '';
		$region = '';

		if ( \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('static_info_tables') )
		{
			// format address for Google search based on local address-format for given $country

			// convert $country to ISO3
			$countryCodeType = self::isoCodeType($country);
			if       ($countryCodeType == 'nr') {
				$countryArray = self::fetchCountries('', '', '', $country);
			} elseif ($countryCodeType == '2') {
				$countryArray = self::fetchCountries('', $country, '', '');
			} elseif ($countryCodeType == '3') {
				$countryArray = self::fetchCountries('', '', $country, '');
			} else {
				if ($country != '')	{
					$queryBuilder = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance( \TYPO3\CMS\Core\Database\ConnectionPool::class )
					->getQueryBuilderForTable('static_countries');
					$queryBuilder->getRestrictions()
						->removeAll();
					$statement = $queryBuilder
						->select('*')
						->from('static_countries')
						->where( $queryBuilder->expr()->logicalOr(
							$queryBuilder->expr()->eq(
								'cn_official_name_local', 
								$queryBuilder->createNamedParameter( trim( $country ) )
							),
							$queryBuilder->expr()->eq(
								'cn_official_name_en', 
								$queryBuilder->createNamedParameter( trim( $country ) )
							),
							$queryBuilder->expr()->eq(
								'cn_short_local', 
								$queryBuilder->createNamedParameter( trim( $country ) )
							)
						) )
						->execute();
					$countryArray = $statement->fetchAll();
				}

				if ( !is_array( $countryArray ) ) {
					$countryArray = self::fetchCountries($country, '', '', '');
				}
			}

			\TYPO3\CMS\Core\Utility\GeneralUtility::devLog('Google V3: countryArray for '.$country, 'wec_map_geocode', -1, $countryArray);

			if ( is_array( $countryArray ) && count( $countryArray ) == 1 )
			{
				$country = $countryArray[0]['cn_short_local'];
				$region = $countryArray[0]['cn_tldomain'];
			}

			// format address accordingly
			$addressString = $this->formatAddress(',', $street, $city, $zip, $state, $country);  // $country: local country name
			\TYPO3\CMS\Core\Utility\GeneralUtility::devLog('Google V3 addressString', 'wec_map_geocode', -1, array( street => $street, city => $city, zip => $zip, state => $state, country => $country, addressString => $addressString ) );
		}

		if ( !$addressString )
		{
			$addressString = $street.' '.$city.', '.$state.' '.$zip.', '.$country;	// default: US-format
			// $addressString = $street.','.$zip.' '.$city.','.$country;  			// Alternative German format for better search results
		}

		// build URL
		$lookupstr = trim( $addressString );

		$url = 'https://maps.googleapis.com/maps/api/geocode/json?address=' . urlencode( $lookupstr );
		if ( $region )
			$url .= '&region=' . urlencode( $region );

		$domainmgr = \JBartels\WecMap\Utility\DomainMgr::getInstance();
		$url = $domainmgr->addKeyToUrl( $url, $domainmgr->getServerKey() );

		// request Google-service and parse JSON-response
		\TYPO3\CMS\Core\Utility\GeneralUtility::devLog('Google V3: URL '.$url, 'wec_map_geocode', -1 );

		$attempt = 1;
		do {
			$jsonstr = \TYPO3\CMS\Core\Utility\GeneralUtility::getURL($url);

			$response_obj = json_decode( $jsonstr, true );
			\TYPO3\CMS\Core\Utility\GeneralUtility::devLog('Google V3: '.$jsonstr, 'wec_map_geocode', -1, $response_obj);
			if ($response_obj['status'] == 'OVER_QUERY_LIMIT')
				sleep(2);

			$attempt++;
		} while ($attempt <= 3 && $response_obj['status'] == 'OVER_QUERY_LIMIT');

		$latlong = array();
		$addressArray = array(
			'street' => $street,
			'city' => $city,
			'state' => $state,
			'zip' => $zip,
			'country' => $country,
			'region' => $region
		);
		\TYPO3\CMS\Core\Utility\GeneralUtility::devLog('Google V3: '.$addressString, 'wec_map_geocode', -1, array(
			'street' => $street,
			'city' => $city,
			'state' => $state,
			'zip' => $zip,
			'country' => $country,
			'region' => $region
		) );

		if ( $response_obj['status'] == 'OK' )
		{
			/*
			 * Geocoding worked!
			 */
			$latlong['lat'] = floatval( $response_obj['results'][0]['geometry']['location']['lat'] );
			$latlong['long'] = floatval( $response_obj['results'][0]['geometry']['location']['lng'] );
			\TYPO3\CMS\Core\Utility\GeneralUtility::devLog('Google V3 Answer successful', 'wec_map_geocode', -1, $latlong);
		}
		else if (  $response_obj['status'] == 'REQUEST_DENIED'
		        || $response_obj['status'] == 'INVALID_REQUEST'
		        )
		{
			/*
			 * Geocoder can't run at all, so disable this service and
			 * try the other geocoders instead.
			 */
			\TYPO3\CMS\Core\Utility\GeneralUtility::devLog('Google V3: '.$response_obj['status'].': '.$addressString.'. Disabling.', 'wec_map_geocode', 3 );
			$this->deactivateService();
			$latlong = null;
		}
		else
		{
			/*
			 * Something is wrong with this address. Might work for other
			 * addresses though.
			 */
			\TYPO3\CMS\Core\Utility\GeneralUtility::devLog('Google V3: '.$response_obj['status'].': '.$addressString.'. Disabling.', 'wec_map_geocode', 2 );
			$latlong = null;
		}

		return $latlong;
	}


	/**
	 * Formatting an address in the format specified
	 *
	 * @param	string		A delimiter for the fields of the returned address
	 * @param	string		A street address
	 * @param	string		A city
	 * @param	string		A country subdivision code (zn_code)
	 * @param	string		A ISO alpha-3 country code (cn_iso_3)
	 * @param	string		A zip code
	 * @return	string		The formated address using the country address format (cn_address_format)
	 */
	function formatAddress ($delim, $streetAddress, $city, $zip, $subdivisionCode='', $countryCode='')	{
		/** @var \SJBR\StaticInfoTables\PiBaseApi $staticInfoObj */
		$staticInfoObj = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\SJBR\StaticInfoTables\PiBaseApi::class);
		if ($staticInfoObj->needsInit()) {
			if(TYPO3_MODE == 'FE')
			{
				$staticInfoObj->init();
			}
			else
			{
				$conf = $this->loadTypoScriptForBEModule('tx_staticinfotables_pi1');
				$staticInfoObj->init( $conf );
				$staticInfoObj->conf = $conf;
			}
		}
		return $staticInfoObj->formatAddress($delim, $streetAddress, $city, $zip, $subdivisionCode, $countryCode);
	}


	/**
	 * Loads the TypoScript for the given extension prefix, e.g. tx_cspuppyfunctions_pi1, for use in a backend module.
	 *
	 * @param string $extKey
	 * @return array
	 */
	protected function loadTypoScriptForBEModule($extKey) {

		$queryBuilder = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance( \TYPO3\CMS\Core\Database\ConnectionPool::class )
			->getQueryBuilderForTable('pages');
		$statement = $queryBuilder
			->select('uid')
			->from('pages')
			->where($queryBuilder->expr()->eq('pid', 0))
			->execute();
		$page = $statement->fetch();
		$pageUid = intval($page['uid']);
		/** @var \TYPO3\CMS\Core\Utility\RootlineUtility $rootlineUtility */
		$rootlineUtility = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Utility\RootlineUtility::class, $pageUid);
		$rootline = $rootlineUtility->get();
		/** @var \TYPO3\CMS\Core\TypoScript\ExtendedTemplateService $TSObj */
		$TSObj = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\TypoScript\ExtendedTemplateService::class);
		$TSObj->tt_track = 0;
		$TSObj->init();
		$TSObj->runThroughTemplates($rootLine);
		$TSObj->generateConfig();
		return $TSObj->setup['plugin.'][$extKey . '.'];
	}

}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/geocode_service/class.tx_wecmap_geocode_google.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/geocode_service/class.tx_wecmap_geocode_google.php']);
}

?>
