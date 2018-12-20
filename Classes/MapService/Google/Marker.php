<?php
/***************************************************************
* Copyright notice
*
* (c) 2005-2009 Christian Technology Ministries International Inc.
* All rights reserved
* (c) 2011-2018 Jan Bartels, j.bartels@arcor.de, Google API V3
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
 * Marker implementation for the Google Maps mapping service.
 *
 * @author Web-Empowered Church Team <map@webempoweredchurch.org>
 * @package TYPO3
 * @subpackage tx_wecmap
 */
class Marker extends \JBartels\WecMap\MapService\Marker {
	public $index;

	public $latitude;
	public $longitude;

	public $title;
	public $description;
	public $color;
	public $strokeColor;
	public $prefillAddress;
	public $tabLabels;
	public $iconID;

	public  $lang;
	public  $LOCAL_LANG;
	/** @var $langService \TYPO3\CMS\Lang\LanguageService */
	public  $langService;

	/**
	 * Constructor for the Google Maps marker class.
	 *
	 * @access	public
	 * @param	integer		Index within the overall array of markers.
	 * @param	float		Latitude of the marker location.
	 * @param	float		Longitude of the marker location.
	 * @param	string		Title of the marker.
	 * @param	string		Description of the marker.
	 * @param 	boolean		Sets whether the directions address should be prefilled with logged in user's address
	 * @param	array 		Labels used on tabs. Optional.
	 * @param	string		Unused for Google Maps.
	 * @param	string		Unused for Google Maps.
	 * @return	none
	 */
	public function __construct($index, $latitude, $longitude, $title, $description, $prefillAddress = false, $tabLabels=null, $color='0xFF0000', $strokeColor='0xFFFFFF', $iconID='') {

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

		$this->index = $index;
		$this->tabLabels = array($this->getLL('info'));
		if(is_array($tabLabels)) {
			$this->tabLabels = array_merge($this->tabLabels, $tabLabels);
		}

		$this->prefillAddress = $prefillAddress;

		$this->title = array();
		$this->description = array();

		if(is_array($title)) {
			foreach( $title as $value ) {
				$this->title[] = $value;
			}
		} else {
			$this->title[] = $title;
		}

		if(is_array($description)) {
			foreach($description as $value ) {
				$this->description[] = $this->filterNL2BR($value);
			}
		} else {
			$this->description[] = $this->filterNL2BR($description);
		}

		$this->color = $color;
		$this->strokeColor = $strokeColor;

		$this->latitude = $latitude;
		$this->longitude = $longitude;

		$this->iconID = $iconID;

		$this->isDraggable = false;
	}

	function getLL($index)
	{
		return $this->langService->getLLL( $index, $this->LOCAL_LANG );
	}

	/**
	 * Creates the Javascript to add a marker to the page.
	 *
	 * @access public
	 * @return	string	The Javascript to add a marker to the page.
	 */
	function writeJS() {
		$markerContent = array();
		foreach ($this->tabLabels as $index => $label) {
			$markerContent[] = json_encode( $this->title[$index] ) . '+' . json_encode( $this->description[$index], JSON_HEX_APOS );
		}
		$tabLabels = array();
		foreach ($this->tabLabels as $index => $label) {
			$tabLabels[] = json_encode( $label, JSON_HEX_APOS );
		}

		if ($this->directions) {
			$data = array( 'map_id' => $this->mapName,
						   'groupId' => $this->groupId,
						   'index' => $this->index,
						   'address' => $this->getUserAddress(),
						   'latitude' => $this->latitude,
						   'longitude' => $this->longitude,
						   'dirTitle' => htmlspecialchars(strip_tags($this->title[0]))
						 );

			if ( is_array( $this->directionsMenuConf ) )
				$markerContent[0] .= '+' . json_encode( \JBartels\WecMap\Utility\Shared::render( $data, $this->directionsMenuConf ), JSON_HEX_APOS );
			else
			{
				// Workaround for EXT:cal
				// get default directionsMenu
				$directionsMenuConf = $GLOBALS['TSFE']->tmpl->setup['tx_wecmap_api.']['directionsMenu.'];
				if ( is_array( $directionsMenuConf ) )
					$markerContent[0] .= '+' . json_encode( \JBartels\WecMap\Utility\Shared::render( $data, $directionsMenuConf ), JSON_HEX_APOS );
			}
		}

		return '
WecMap.addBubble( "' . $this->mapName . '", ' . $this->groupId . ', ' . $this->index . ', ['  . implode(',', $tabLabels) . '], [' . implode(',', $markerContent) . ']);';
	}

	/**
	 * Wrapper method that makes sure directions are properly displayed
	 *
	 * @return string 	the javascript to add the marker
	 **/
	function writeJSwithDirections() {
		$this->directions = true;
		return $this->writeJS();
	}

	/**
	 * undocumented function
	 *
	 * @return void
	 **/
	function writeCreateMarkerJS() {
		if (empty($this->title[0]) && $this->directions) {
			$this->title[0] = 'Address';
		}
		$js = "WecMap.addMarker( "
		                       . "'". $this->mapName . "', " . $this->index . ", "
		                       . "[" . $this->latitude . "," . $this->longitude . "], "
		                       . "'" . $this->iconID . "', "
		                       . json_encode(htmlspecialchars(strip_tags($this->title[0])), JSON_HEX_APOS ) .", "
		                       . $this->groupId . ", "
		                       . json_encode($this->getUserAddress(), JSON_HEX_APOS )
		                     .");";
		if ( $this->isDraggable )
			$js .= "WecMap.setDraggable('". $this->mapName . "', " . $this->groupId . ", " . $this->index . ", true);";
		return $js;
	}

	/**
	 * undocumented function
	 *
	 * @return void
	 **/
	function setDraggable( $flag ) {
		$this->isDraggable = flag;
	}

	/**
	 * adds a new tab to the marker
	 *
	 * @return void
	 **/
	function addTab($tabLabel, $title, $description) {
		if(!is_array($this->title)) {
			$temp = $this->title;
			$this->title = array($temp);
		}

		if(!is_array($this->description)) {
			$temp = $this->description;
			$this->description = array($temp);
		}

		if(!is_array($this->tabLabels)) {
			$this->tabLabels = array($this->getLL('info'));
		}

		$this->tabLabels[] = $tabLabel;
		$this->title[] = $title;
		$this->description[] = $description;
		// TODO: devlog start
		if(TYPO3_DLOG) {
			\TYPO3\CMS\Core\Utility\GeneralUtility::devLog($this->mapName.': manually adding tab to marker '.$this->index.' with title '. $title, 'wec_map_api');
		}
		// devlog end
	}

	/**
	 * Gets the address of the user who is currently logged in
	 *
	 * @return string
	 **/
	function getUserAddress() {
		if($this->prefillAddress) {

			if(TYPO3_MODE == 'FE') {
				$feuser_id = $GLOBALS['TSFE']->fe_user->user['uid'];

				if(!empty($feuser_id)) {
					$table = 'fe_users';
					$streetField  = \JBartels\WecMap\Utility\Shared::getAddressField($table, 'street');
					$cityField    = \JBartels\WecMap\Utility\Shared::getAddressField($table, 'city');
					$stateField   = \JBartels\WecMap\Utility\Shared::getAddressField($table, 'state');
					$zipField     = \JBartels\WecMap\Utility\Shared::getAddressField($table, 'zip');
					$countryField = \JBartels\WecMap\Utility\Shared::getAddressField($table, 'country');

					$queryBuilder = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance( \TYPO3\CMS\Core\Database\ConnectionPool::class )
						->getQueryBuilderForTable( 'fe_users' );
					$statement = $queryBuilder
						->select('*')
						->from( 'fe_users' )
						->where(
							$queryBuilder->expr()->eq( 'uid', $queryBuilder->createNamedParameter( $feuser_id, \PDO::PARAM_INT ) )
						)
						->execute();
					$row = $statement->fetch();
		
					return $row[$streetField].', '.$row[$cityField].', '.$row[$stateField].' '.$row[$zipField].', '.$row[$countryField];
				}
			} else {

			}
		}
		return '';
	}

	/**
	 * Returns the javascript function call to center on this marker
	 *
	 * @return String
	 **/
	function getClickJS() {
		if(TYPO3_DLOG) {
			\TYPO3\CMS\Core\Utility\GeneralUtility::devLog($this->mapName.': adding marker '.$this->index.'('.strip_tags($this->title[0]).strip_tags($this->description[0]).') to sidebar', 'wec_map_api');
		}
		return 'WecMap.jumpTo(\'' . $this->mapName . '\', ' . $this->groupId . ', ' . $this->index . ', ' . $this->calculateClickZoom() . ');';
	}

	function getOpenInfoWindowJS() {
		return 'WecMap.openInfoWindow("' . $this->mapName . '", ' . $this->groupId . ', ' . $this->index . ');';
	}

	function getInitialOpenInfoWindowJS() {
		return 'WecMap.openInitialInfoWindow("' . $this->mapName . '", ' . $this->groupId . ', ' . $this->index . ');';
	}

	/**
	 * calculates the optimal zoom level for the click
	 * we want to keep the zoom level around $zoom, but will
	 * choose the max if the marker is only visible under $zoom,
	 * or the min if it's only shown over $zoom.
	 * @return integer
	 **/
	function calculateClickZoom() {
		$zoom = 14;
		if ($zoom < $this->minzoom) {
			$zoom = $this->minzoom;
		} else if ($zoom > $this->maxzoom) {
			$zoom = $this->maxzoom;
		}
		return $zoom;
	}

	/**
	 * Converts newlines to <br/> tags.
	 *
	 * @access	private
	 * @param	string		The input string to filtered.
	 * @return	string		The converted string.
	 */
	function filterNL2BR($input) {
		$order  = array("\r\n", "\n", "\r");
		$replace = '<br />';
		return str_replace($order, $replace, $input);
	}

	/**
	 * strip newlines
	 *
	 * @access	private
	 * @param	string		The input string to filtered.
	 * @return	string		The converted string.
	 */
	function stripNL($input) {
		$order  = array("\r\n", "\n", "\r");
		$replace = '<br />';
		return str_replace($order, $replace, $input);
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/map_service/google/class.tx_wecmap_marker_google.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/map_service/google/class.tx_wecmap_marker_google.php']);
}


?>