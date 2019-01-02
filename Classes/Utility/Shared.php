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

namespace JBartels\WecMap\Utility;

/**
 * General purpose class for the WEC Map extension.  This class
 * provides shared methods used by other classes
 *
 * @author Web-Empowered Church Team <map@webempoweredchurch.org>
 * @package TYPO3
 * @subpackage tx_wecmap
 */
class Shared {

	static function render($data, $conf, $table = '') {
		$local_cObj =  \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer::class); // Local cObj.
		$local_cObj->start($data, $table );
	    $output = $local_cObj->cObjGet($conf);
		return $output;
	}

	static function cObjGet($setup, &$cObj, $addKey='')	{
		if (is_array($setup))	{

			$sKeyArray = $setup;
			$content ='';

			foreach($sKeyArray as $theKey => $theValue)	{

				if (!strstr($theKey,'.'))	{
					$conf=$setup[$theKey.'.'];
					$content.=$cObj->cObjGetSingle($theValue,$conf,$addKey.$theKey);	// Get the contentObject
				}
			}
			return $content;
		}
	}

	public static function getListQueryFromCSV($field, $values, $queryBuilder, $mode = 'AND') {
		$where = [];
		$csv = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $values);
		foreach ( $csv as $value ) {
			$where[] = $queryBuilder->expr()->inSet( $field, $queryBuilder->expr()->literal( (string) $value ) );
		}
		if ( $mode == 'OR')
			return $queryBuilder->expr()->orX( ...$where );
		return $queryBuilder->expr()->andX( ...$where );
	}

	public static function setOrderBy($queryBuilder,string $input) {
		// QueryHelper is marked "internal only" and may be deprecated in the future!
		foreach (\TYPO3\CMS\Core\Database\Query\QueryHelper::parseOrderBy((string)$orderBy) as $orderPair) {
			list($fieldName, $order) = $orderPair;
			$queryBuilder->addOrderBy($fieldName, $order);
		}
	}			

	public static function setGroupBy($queryBuilder,string $input) {
		// QueryHelper is marked "internal only" and may be deprecated in the future!
		$queryBuilder->groupBy(...\TYPO3\CMS\Core\Database\Query\QueryHelper::parseGroupBy($groupBy));
	}			

	public static function setLimit($queryBuilder,string $input)
    {
		if ( $limitValues ) {
			if ( $limitValues->limit )
				$queryBuilder->setMaxResult($limit);
			if ( $limitValues->offset )
				$queryBuilder->setFirstResult($limit);
		}

		$limitValues = [];
		$matchCount = preg_match('/^(?:LIMIT[[:space:]]+)?(\d+)(?:[[:space:]]*,[[:space:]]*(\d+))?$/i', trim($input), $limitValues );
		if ( $matchCount == 1 ) {
			$queryBuilder->setMaxResult( $input[1] );
		} else if ( $matchCount == 2 ) {
			$queryBuilder->setMaxResult( $input[2] );
			$queryBuilder->setFirstResult( $input[1] );
		} else {
			$matchCount = preg_match('/^(?:LIMIT[[:space:]]+)?(\d+)[[:space:]]+OFFSET[[:space:]]+(\d+))$/i', trim($input), $limitValues );
			if ( $matchCount == 2 ) {
				$queryBuilder->setMaxResult( $input[1] );
				$queryBuilder->setFirstResult( $input[2] );
			}
		}
    }

	static function getAddressField($table, $field) {
 		return $GLOBALS['TCA'][$table]['ctrl']['EXT']['wec_map']['addressFields'][$field];
	}

	static function getLatLongField($table, $field) {
 		return $GLOBALS['TCA'][$table]['ctrl']['EXT']['wec_map']['latlongFields'][$field];
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/class.tx_wecmap_shared.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/class.tx_wecmap_shared.php']);
}
?>