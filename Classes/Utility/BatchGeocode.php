<?php
/***************************************************************
* Copyright notice
*
* (c) 2005-2009 Christian Technology Ministries International Inc.
* (c) 2010-2018 J. Bartels
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

namespace JBartels\WecMap\Utility;

/**
 * Performs autmated geocoding for any address information.
 *
 * @author Web-Empowered Church Team <map@webempoweredchurch.org>
 * @package TYPO3
 * @subpackage tx_wecmap
 */
class BatchGeocode {

	protected $tables;
	protected $geocodedAddresses;
	protected $geocodeLimit;
	protected $processedAddresses;


	/**
	 * Default constructor.
	 *
	 * @return		none
	 */
	public function __construct($limit=25) {
		$this->tables = array();
		$this->geocodedAddresses = 0;
		$this->processedAddresses = 0;
		$this->geocodeLimit = $limit;
	}

	/**
	 * Adds a specific tables to the list of tables that should be geocoded.
	 *
	 * @param		string		The name of the table.
	 * @return		none
	 */
	public function addTable($table) {
		$this->tables[] = $table;
	}


	/**
	 * Traverses the TCA and adds all mappable tables to the list of tables that
	 * should be geocoded.
	 *
	 * @return		none
	 */
	public function addAllTables() {

		foreach($GLOBALS['TCA'] as $tableName => $tableContents) {
			if($tableContents['ctrl']['EXT']['wec_map']['isMappable']) {
				$this->tables[] = $tableName;
			}
		}
	}

	/**
	 * Get names of registered tables
	 *
	 * @return		array
	 */
	public function getTableNames() {
		return $this->tables;
	}

	/**
	 * Main function to initiate geocoding of all address-related tables.
	 *
	 * @return		none
	 */
	public function geocode() {
		if ( is_array( $this->tables ) ) {
			foreach($this->tables as $table) {
				if($this->stopGeocoding()) {
					return;
				} else {
					$this->geocodeTable($table);
				}
			}
		}
	}

	/**
	 * Performs geocoding on an individual table.
	 *
	 * @param		string		Name of the table.
	 * @return		none
	 */
	public function geocodeTable($table) {
		$addressFields = array(
			'street'  => \JBartels\WecMap\Utility\Shared::getAddressField($table, 'street'),
			'city'    => \JBartels\WecMap\Utility\Shared::getAddressField($table, 'city'),
			'state'   => \JBartels\WecMap\Utility\Shared::getAddressField($table, 'state'),
			'zip'     => \JBartels\WecMap\Utility\Shared::getAddressField($table, 'zip'),
			'country' => \JBartels\WecMap\Utility\Shared::getAddressField($table, 'country'),
		);

		$where = "1=1".\TYPO3\CMS\Backend\Utility\BackendUtility::deleteClause($table);
		$result =  $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', $table, $where);
		while($row =  $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
			if($this->stopGeocoding()) {
				return;
			} else {
				$this->geocodeRecord($row, $addressFields);
			}
		}
	}


	/**
	 * Performs geocoding on an individual row.
	 *
	 * @param		array		The associative array of the record to be geocoded.
	 * @param		array		The array mapping address elements to individual fields in the record.
	 * @return		none
	 */
	public function geocodeRecord($row, $addressFields) {
		$street  = $addressFields['street']  > '' ? $row[$addressFields['street']]  : '';
		$city    = $addressFields['city']    > '' ? $row[$addressFields['city']]    : '';
		$state   = $addressFields['state']   > '' ? $row[$addressFields['state']]   : '';
		$zip     = $addressFields['zip']     > '' ? $row[$addressFields['zip']]     : '';
		$country = $addressFields['country'] > '' ? $row[$addressFields['country']] : '';

		// increment total count
		$this->processedAddresses++;
		\JBartels\WecMap\Utility\Cache::lookupWithCallback($street, $city, $state, $zip, $country, false, $this);
	}

	/**
	 * Callback function for tx_wecmap_cache::lookup().  Called when a lookup
	 * is not cached and must use external geocoding services. Increments an
	 * internal counter of how many external lookups we've made.
	 *
	 * @return		none
	 */
	public function callback_lookupThroughGeocodeService() {
		$this->geocodedAddresses++;
	}


	/**
	 * Utility function to determine whether batch geocoding should be stopped.
	 *
	 * @return		boolean		True/false whethr batch geocoding should be stopped.
	 */
	protected function stopGeocoding() {
		return ( $this->geocodedAddresses >= $this->geocodeLimit );
	}

	/**
	 * Getter function for the total number of addresses processed.
	 *
	 * @return		The total number of addresses processed.  This includes both
	 *				cached and non-cached.
	 */
	public function getProcessedAddresses() {
		return $this->processedAddresses;
	}


	/**
	 * Getter function for the total number of addresses geocoded.
	 *
	 * @return		The total number of addresses geocoded by external services.
	 *				This does not include cached addresses.
	 */
	public function getGeocodedAddresses() {
		return $this->geocodedAddresses;
	}

	/**
	 * Count of all records containing address-related data.
	 *
	 * @return		integer		The count of all records with addresses.
	 */
	public function getRecordCount() {
		$recordCount = 0;

		if ( is_array( $this->tables ) ) {
			foreach($this->tables as $table) {
				$recordCount += $this->getTableRecordCount( $table );
			}
		}

		return $recordCount;
	}

	/**
	 * Count of all records containing address-related data.
	 *
	 * @param		String		The name of the table
	 * @return		integer		The count of all records with addresses.
	 */
	public function getTableRecordCount( $table ) {
		$where = "1=1".\TYPO3\CMS\Backend\Utility\BackendUtility::deleteClause($table);
		$result =  $GLOBALS['TYPO3_DB']->exec_SELECTquery('COUNT(*)', $table, $where);
		$row =  $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result);
		$recordCount = $row['COUNT(*)'];

		return $recordCount;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/class.tx_wecmap_batchgeocode.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/class.tx_wecmap_batchgeocode.php']);
}

?>
