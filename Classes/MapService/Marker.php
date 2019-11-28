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
 * frontend plugins and address lookup service to render map data.
 *
 * @author Web-Empowered Church Team <map@webempoweredchurch.org>
 * @package TYPO3
 * @subpackage tx_wecmap
 */
class Marker {
	public $index;

	public $latitude;
	public $longitude;
	public $minzoom = 0;
	public $maxzoom;
	public $title;
	public $description;
	public $color;
	public $strokeColor;
	public $mapName;
	public $map = null;
	public $iconID;
	public $groupId = -1;
	public $directionsMenuConf;
	public $prefillAddress;
	public $tabLabels;

	public  $lang;
	public  $LOCAL_LANG;
	/** @var $langService \TYPO3\CMS\Lang\LanguageService */
	public  $langService;

	/**
	 * Constructor stub. See map_service classes for more details on the marker
	 * constructor.
	 *
	 * @return void
	 **/
	protected function __construct($index, $latitude, $longitude, $title, $description, $prefillAddress = false, $tabLabels=null, $color='0xFF0000', $strokeColor='0xFFFFFF', $iconID='') {
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

		$this->index = $index;
		$infoTabTitle = $this->getLL('info');
		if ( $infoTabTitle == '' )
			$infoTabTitle = 'info';
		$this->tabLabels = array($infoTabTitle);
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

	protected function getLL($index)
	{
		return $this->langService->getLLL( $index, $this->LOCAL_LANG );
	}

	/**
	 * Getter for internal index for this marker.
	 *
	 * @return integer index of the marker
	 **/
	public function getIndex() {
		return $this->index;
	}

	/**
	 * Getter for the marker title.
	 *
	 * @return string title of the marker
	 **/
	public function getTitle() {
		return $this->title;
	}

	/**
	 * Getter for marker description
	 *
	 * @return string description of the marker
	 **/
	public function getDescription() {
		return $this->description;
	}

	/**
	 * Setter for the marker title.
	 *
	 * @return void
	 **/
	public function setTitle($newTitle) {
		$this->title = $newTitle;
	}

	/**
	 * Setter for marker description
	 *
	 * @return void
	 **/
	public function setDescription($newDesc) {
		$this->description = $newDesc;
	}

	/**
	 * Getter for marker color
	 *
	 * @return string marker color
	 **/
	public function getColor() {
		return $this->color;
	}

	/**
	 * Getter for the marker stroke color
	 *
	 * @return string marker stroke color
	 **/
	public function getStrokeColor() {
		return $this->strokeColor;
	}

	/**
	 * Getter for the latitude
	 *
	 * @return float latitude
	 **/
	public function getLatitude() {
		return $this->latitude;
	}

	/**
	 * Getter for the longitude
	 *
	 * @return float longitude
	 **/
	public function getLongitude() {
		return $this->longitude;
	}

	/**
	 * Setter for map name this marker is a part of
	 *
	 * @return void
	 **/
	public function setMapName($mapName) {
		$this->mapName = $mapName;
	}

	/**
	 * set the id of the group this marker belongs to
	 *
	 * @return void
	 **/
	public function setGroupId($id) {
		$this->groupId = $id;
	}

	/**
	 * sets the minimum zoom level this marker is displayed on
	 *
	 * @return void
	 **/
	public function setMinzoom($zoom) {
		$this->minzoom = $zoom;
	}

	/**
	 * sets the maximum zoom level this marker is displayed on
	 *
	 * @return void
	 **/
	public function setMaxzoom($zoom) {
		$this->maxzoom = $zoom;
	}

	/**
	 * Setter for map name this marker is a part of
	 *
	 * @return void
	 **/
	public function setDirectionsMenuConf( $conf ) {
		$this->directionsMenuConf = $conf;
	}

	/**
	 * undocumented function
	 *
	 * @return void
	 **/
	public function setDraggable( $flag ) {
		$this->isDraggable = flag;
	}

	/**
	 * Creates the Javascript to add a marker to the page.
	 *
	 * @access public
	 * @return	string	The Javascript to add a marker to the page.
	 */
	public function writeJS() {
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
	public function writeJSwithDirections() {
		$this->directions = true;
		return $this->writeJS();
	}

	/**
	 * undocumented function
	 *
	 * @return void
	 **/
	public function writeCreateMarkerJS() {
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
	 * adds a new tab to the marker
	 *
	 * @return void
	 **/
	public function addTab($tabLabel, $title, $description) {
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
		\TYPO3\CMS\Core\Utility\GeneralUtility::devLog($this->mapName.': manually adding tab to marker '.$this->index.' with title '. $title, 'wec_map_api');
	}

	/**
	 * Gets the address of the user who is currently logged in
	 *
	 * @return string
	 **/
	public function getUserAddress() {
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
	public function getClickJS() {
		\TYPO3\CMS\Core\Utility\GeneralUtility::devLog($this->mapName.': adding marker '.$this->index.'('.strip_tags($this->title[0]).strip_tags($this->description[0]).') to sidebar', 'wec_map_api');
		return 'WecMap.jumpTo(\'' . $this->mapName . '\', ' . $this->groupId . ', ' . $this->index . ', ' . $this->calculateClickZoom() . ');';
	}

	public function getOpenInfoWindowJS() {
		return 'WecMap.openInfoWindow("' . $this->mapName . '", ' . $this->groupId . ', ' . $this->index . ');';
	}

	public function getInitialOpenInfoWindowJS() {
		return 'WecMap.openInitialInfoWindow("' . $this->mapName . '", ' . $this->groupId . ', ' . $this->index . ');';
	}

	/**
	 * calculates the optimal zoom level for the click
	 * we want to keep the zoom level around $zoom, but will
	 * choose the max if the marker is only visible under $zoom,
	 * or the min if it's only shown over $zoom.
	 * @return integer
	 **/
	public function calculateClickZoom() {
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
	protected function filterNL2BR($input) {
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
	protected function stripNL($input) {
		$order  = array("\r\n", "\n", "\r");
		$replace = '<br />';
		return str_replace($order, $replace, $input);
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/class.tx_wecmap_marker.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/class.tx_wecmap_marker.php']);
}
?>