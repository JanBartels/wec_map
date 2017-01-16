<?php
/***************************************************************
* Copyright notice
*
* (c) 2005-2009 Christian Technology Ministries International Inc.
* (c) 2015-2017 Jan Bartels, j.bartels@arcor.de
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

namespace JBartels\WecMap\Module\FEUserMap;

	// DEFAULT initialization of a module [BEGIN]
$GLOBALS['LANG']->includeLLFile('EXT:wec_map/Resources/Private/Languages/Module/FEUserMap/locallang.xlf');
$GLOBALS['BE_USER']->modAccess($MCONF, 1);	// This checks permissions and exits if the users has no permission for entry.
	// DEFAULT initialization of a module [END]

/**
 * Module 'Map FE Users' for the 'wec_map' extension.
 *
 * @author	Web-Empowered Church Team <map@webempoweredchurch.org>
 * @package	TYPO3
 * @subpackage	tx_wecmap
 */
class Module extends \TYPO3\CMS\Backend\Module\BaseScriptClass  {
	public $pageinfo;

	/**
	 * Initializes the Module
	 * @return	void
	 */
	function init()	{
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

		parent::init();
	}

	/**
	 * Adds items to the ->MOD_MENU array. Used for the function menu selector.
	 *
	 * @return	void
	 */
	function menuConfig()	{
		global $LANG;
		$this->MOD_MENU = Array (
			'function' => Array (
				'1' => $LANG->getLL('function1'),
				'2' => $LANG->getLL('function2'),
			)
		);
		parent::menuConfig();
	}

	/**
	 * Main function of the module. Write the content to $this->content
	 * If you chose "web" as main module, you will need to consider the $this->id parameter which will contain the uid-number of the page clicked in the page tree
	 *
	 * @return	[type]		...
	 */
	function main()	{
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

		// Access check!
		// The page will show only if there is a valid page and if this page may be viewed by the user
		$this->pageinfo = \TYPO3\CMS\Backend\Utility\BackendUtility::readPageAccess($this->id,$this->perms_clause);
		$access = is_array($this->pageinfo) ? 1 : 0;

		if (($this->id && $access) || ($BE_USER->user['admin'] && !$this->id) || ($BE_USER->user['uid'] && !$this->id)) {

				// Draw the header.
			$this->doc = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Backend\Template\DocumentTemplate::class);
			$this->doc->docType = 'xhtml_trans';
			$this->doc->backPath = $BACK_PATH;
			$this->doc->form='<form action="" method="post">';

				// JavaScript
			$this->doc->JScode = '
				<script language="javascript" type="text/javascript">
					script_ended = 0;
					function jumpToUrl(URL)	{
						document.location = URL;
					}
				</script>
			';
			$this->doc->postCode='
				<script language="javascript" type="text/javascript">
					script_ended = 1;
					if (top.fsMod) top.fsMod.recentIds["web"] = 0;
				</script>';

			$this->doc->inDocStylesArray[] = '
					.dirmenu a:link, .dirmenu a:visited {
						text-decoration: underline;
					}
					.description {
						margin-top: 8px;
					}';

			$headerSection = $this->doc->getHeader('pages',$this->pageinfo,$this->pageinfo['_thePath']).'<br />'.$LANG->sL('LLL:EXT:lang/locallang_core.xml:labels.path').': '.\TYPO3\CMS\Core\Utility\GeneralUtility::fixed_lgd_cs($this->pageinfo['_thePath'],-50);

			$this->content.=$this->doc->startPage($LANG->getLL('title'));
			$this->content.=$this->doc->header($LANG->getLL('title'));
			$this->content.=$this->doc->spacer(5);
			$this->content.=$this->doc->section('',$this->doc->funcMenu($headerSection,\TYPO3\CMS\Backend\Utility\BackendUtility::getFuncMenu($this->id,'SET[function]',$this->MOD_SETTINGS['function'],$this->MOD_MENU['function'])));
			$this->content.=$this->doc->divider(5);


			// Render content:
			$this->moduleContent();


			// ShortCut
			if ($BE_USER->mayMakeShortcut())	{
				$this->content.=$this->doc->spacer(20).$this->doc->section('',$this->doc->makeShortcutIcon('id',implode(',',array_keys($this->MOD_MENU)),$this->MCONF['name']));
			}

			$this->content.=$this->doc->spacer(10);
		} else {
				// If no access or if ID == zero

			$this->doc = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Backend\Template\DocumentTemplate::class);
			$this->doc->backPath = $BACK_PATH;

			$this->content.=$this->doc->startPage($LANG->getLL('title'));
			$this->content.=$this->doc->header($LANG->getLL('title'));
			$this->content.=$this->doc->spacer(5);
			$this->content.=$this->doc->spacer(10);
		}
	}

	/**
	 * Prints out the module HTML
	 *
	 * @return	void
	 */
	function printContent()	{
		$this->content.=$this->doc->endPage();
		echo $this->content;
	}

	/**
	 * Generates the module content
	 *
	 * @return	void
	 */
	function moduleContent()	{

		switch((string)$this->MOD_SETTINGS['function'])	{
			case 1:
				$this->content.=$this->showMap();
			break;

			case 2:
				$this->content .= $this->mapSettings();
			break;

		}
	}

	function linkSelf($addParams)	{
		return htmlspecialchars('index.php?id='.$this->pObj->id.'&showLanguage='.rawurlencode(\TYPO3\CMS\Core\Utility\GeneralUtility::_GP('showLanguage')).$addParams);
	}

	/**
	 * Show map settings
	 *
	 * @return String
	 **/
	function mapSettings() {

		if(\TYPO3\CMS\Core\Utility\GeneralUtility::_GP('tx-wecmap-mod1-submit')) {

			$scale = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('tx-wecmap-mod1-scale');
			if($scale == 'on') {
				$scale = 1;
			} else {
				$scale = 0;
			}

			$maptype = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('tx-wecmap-mod1-maptype');
			if($maptype == 'on') {
				$maptype = 1;
			} else {
				$maptype = 0;
			}

			$mapcontrolsize = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('tx-wecmap-mod1-mapcontrolzoom');
			if($controlzoom == 'on') {
				$controlzoom = 1;
			} else {
				$controlzoom = 0;
			}

			// build data array
			$data = array('scale' => $scale, 'maptype' => $maptype, 'controlzoom' => $controlzoom);

			// save to user config
			$GLOBALS['BE_USER']->pushModuleData('tools_txwecmapM2', $data);
		}

		// get module config
		$conf = $GLOBALS['BE_USER']->getModuleData('tools_txwecmapM2');


		// get config options
		$scale = $conf['scale'];
		$maptype = $conf['maptype'];
		$mapcontrolsize = $conf['mapcontrolsize'];	// deprecated;
		$controlzoom = $conf['controlzoom']
		             || ( mapcontrolsize != 'none' && !empty( $mapcontrolsize ) );	// deprecated

		$form = array();
		$form[] = '<table>';

		// scale option
		$form[] = '<tr>';
		$form[] = '<td><label for="tx-wecmap-mod1-scale">Show Scale:</label></td>';
		if($scale) {
			$form[] = '<td><input type="checkbox" name="tx-wecmap-mod1-scale" id="tx-wecmap-mod1-scale" checked="checked"/></td>';
		} else {
			$form[] = '<td><input type="checkbox" name="tx-wecmap-mod1-scale" id="tx-wecmap-mod1-scale" /></td>';
		}
		$form[] = '</tr>';

		// maptype option
		$form[] = '<tr>';
		$form[] = '<td><label for="tx-wecmap-mod1-maptype">Show Maptype:</label></td>';
		if($maptype) {
			$form[] = '<td><input type="checkbox" name="tx-wecmap-mod1-maptype" id="tx-wecmap-mod1-maptype" checked="checked"/></td>';
		} else {
			$form[] = '<td><input type="checkbox" name="tx-wecmap-mod1-maptype" id="tx-wecmap-mod1-maptype" /></td>';
		}
		$form[] = '</tr>';

		$form[] = '<tr>';
		$form[] = '<td><label for="tx-wecmap-mod1-controlzoom">Map Zoom Control:</label></td>';
		if($controlzoom) {
			$form[] = '<td><input type="checkbox" name="tx-wecmap-mod1-controlzoom" id="tx-wecmap-mod1-controlzoom" checked="checked"/></td>';
		} else {
			$form[] = '<td><input type="checkbox" name="tx-wecmap-mod1-controlzoom" id="tx-wecmap-mod1-controlzoom" /></td>';
		}
		$form[] = '</tr>';


		$form[] = '</table>';
		$form[] = '<input type="submit" name="tx-wecmap-mod1-submit" id="tx-wecmap-mod1-submit" value="Save" />';


		return implode(chr(10), $form);
	}

	/**
	 * Shows map
	 *
	 * @return String
	 **/
	function showMap() {
		global $LANG;
		/* Create the Map object */
		$width = 500;
		$height = 500;
		$conf = $GLOBALS['BE_USER']->getModuleData('tools_txwecmapM2');


		// get options
		$scale = $conf['scale'];
		$maptype = $conf['maptype'];
		$mapcontrolsize = $conf['mapcontrolsize'];	// deprecated;
		$controlzoom = $conf['controlzoom']
		             || ( mapcontrolsize != 'none' && !empty( $mapcontrolsize ) );	// deprecated

		$streetField  = \JBartels\WecMap\Utility\Shared::getAddressField('fe_users', 'street');
		$cityField    = \JBartels\WecMap\Utility\Shared::getAddressField('fe_users', 'city');
		$stateField   = \JBartels\WecMap\Utility\Shared::getAddressField('fe_users', 'state');
		$zipField     = \JBartels\WecMap\Utility\Shared::getAddressField('fe_users', 'zip');
		$countryField = \JBartels\WecMap\Utility\Shared::getAddressField('fe_users', 'country');

		#include_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('wec_map').'map_service/google/class.tx_wecmap_map_google.php');
		$map = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\JBartels\WecMap\MapService\Google\Map::class, $apiKey, $width, $height);

		// evaluate map controls based on configuration
		if($controlzoom) $map->addControl('zoom');
		if($scale) $map->addControl('scale');
		if($maptype) $map->addControl('mapType');
		$map->enableDirections(false, 'directions');

		/* Select all frontend users */
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'fe_users', '');

		// create country and zip code array to keep track of which country and state we already added to the map.
		// the point is to create only one marker per country on a higher zoom level to not
		// overload the map with all the markers and do the same with zip codes.
		$countries = array();
		$cities = array();
		while (($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result))) {

			// add check for country and use different field if empty
			// @TODO: make this smarter with TCA or something
			if(empty($row[$countryField]) && $countryField == 'static_info_country') {
				$countryField = 'country';
			} else if(empty($row[$countryField]) && $countryField == 'country') {
				$countryField = 'static_info_country';
			}

			/* Only try to add marker if there's a city */
			if($row[$cityField] != '') {

				// if we haven't added a marker for this country yet, do so.
				if(!in_array($row[$countryField], $countries) && !empty($row[$countryField])) {

					// add this country to the array
					$countries[] = $row[$countryField];

					// add a little info so users know what to do
					$title = '';
					$description = '<div class="description">'.sprintf($LANG->getLL('country_zoominfo_desc'), $row[$countryField]).'</div>';

					// add a marker for this country and only show it between zoom levels 0 and 2.
					$map->addMarkerByAddress(null, $row[$cityField], $row[$stateField], $row[$zipField], $row[$countryField], $title, $description, 0,2);
				}


				// if we haven't added a marker for this zip code yet, do so.
				if(!in_array($row[$cityField], $cities) && !empty($cityField)) {

					// add this country to the array
					$cities[] = $row[$cityField];

					// add a little info so users know what to do
					$title = '';
					$description = '<div class="description">'.$LANG->getLL('area_zoominfo_desc').'</div>';

					// add a marker for this country and only show it between zoom levels 0 and 2.
					$map->addMarkerByAddress(null, $row[$cityField], $row[$stateField], $row[$zipField], $row[$countryField], $title, $description, 3,7);
				}

				// make title and description
				$title = '<div style="font-size: 110%; font-weight: bold;">'.$row['name'].'</div>';
				$content = '<div>'.$row[$streetField].'<br />'.$row[$cityField].', '.$row[$stateField].' '.$row[$zipField].'<br />'. $row[$countryField].'</div>';


				// add all the markers starting at zoom level 3 so we don't crowd the map right away.
				// if private was checked, don't use address to geocode
				if($private) {
					$map->addMarkerByAddress(null, $row[$cityField], $row[$stateField], $row[$zipField], $row[$countryField], $title, $content, 8);
				} else {
					$map->addMarkerByAddress($row[$streetField], $row[$cityField], $row[$stateField], $row[$zipField], $row[$countryField], $title, $content, 8);
				}
			}
		}

		$content = $map->drawMap();
		$content .= '<div id="directions"></div>';
		return $content;
	}

	function returnEditLink($uid,$title) {
		$tablename = 'fe_users';
		$params = '&edit['.$tablename.']['.$uid.']=edit';
		$out .=    '<a href="#" onclick="'.
		\TYPO3\CMS\Backend\Utility\BackendUtility::editOnClick($params,$GLOBALS['BACK_PATH']).
		'">';
		$out .= $title;
		$out .= '<img'.\TYPO3\CMS\Backend\Utility\IconUtility::skinImg($GLOBALS['BACK_PATH'],'gfx/edit2.gif','width="11" height="12"').' title="Edit me" border="0" alt="" />';
		$out .= '</a>';
		return $out;
	}

}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/mod2/index.php'])	{
include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/mod2/index.php']);
}




// Make instance:
$SOBE = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\JBartels\WecMap\Module\FEUserMap\Module::class);
$SOBE->init();
$SOBE->main();
$SOBE->printContent();

?>
