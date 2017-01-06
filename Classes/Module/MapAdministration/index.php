<?php
/***************************************************************
* Copyright notice
*
* (c) 2005-2009 Christian Technology Ministries International Inc.
* (c) 2011-2017 J. Bartels
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

namespace JBartels\WecMap\Module\MapAdministration;

	// DEFAULT initialization of a module [BEGIN]
$GLOBALS['LANG']->includeLLFile('EXT:wec_map/Resources/Private/Languages/Module/MapAdministration/locallang.xlf');
$GLOBALS['BE_USER']->modAccess($MCONF, 1);	// This checks permissions and exits if the users has no permission for entry.
	// DEFAULT initialization of a module [END]

/**
 * Module 'WEC Map Admin' for the 'wec_map' extension.
 *
 * @author	Web-Empowered Church Team <map@webempoweredchurch.org>
 * @package	TYPO3
 * @subpackage	tx_wecmap
 */
class Module extends \TYPO3\CMS\Backend\Module\BaseScriptClass {
	public $pageinfo;
	var $extKey = 'wec_map';

	/**
	 * Initializes the Module
	 * @return	void
	 */
	function init()	{
		parent::init();
		$javaScriptDir = '../' . \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::siteRelPath('wec_map') . 'Resources/Public/JavaScript/';

$pageRenderer = $GLOBALS['TBE_TEMPLATE']->getPageRenderer();
		$pageRenderer->loadJquery();
		$pageRenderer->addJsFile( $javaScriptDir . 'MapAdministration.js' );
	}

	/**
	 * Adds items to the ->MOD_MENU array. Used for the function menu selector.
	 *
	 * @return	void
	 */
	function menuConfig()	{
		global $LANG;
		$this->MOD_MENU = array (
			'function' => array (
				'1' => $LANG->getLL('function1'),
				'2' => $LANG->getLL('function3'),
				'3' => $LANG->getLL('function4'),
				'4' => $LANG->getLL('function2'),
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
			$this->doc->backPath = $BACK_PATH;
			$this->doc->form='<form action="" method="POST">';

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
				</script>
			';

			$headerSection = $this->doc->getHeader('pages',$this->pageinfo,$this->pageinfo['_thePath']).'<br />'.$LANG->sL('LLL:EXT:lang/locallang_core.xml:labels.path').': '.\TYPO3\CMS\Core\Utility\GeneralUtility::fixed_lgd_cs($this->pageinfo['_thePath'],-50);

			$this->content.=$this->doc->startPage($LANG->getLL('title'));
			$this->content.=$this->doc->header($LANG->getLL('title'));
			$this->content.=$this->doc->spacer(5);
			$this->content.=$this->doc->section('',$this->doc->funcMenu($headerSection,\TYPO3\CMS\Backend\Utility\BackendUtility::getFuncMenu($this->id,'SET[function]',$this->MOD_SETTINGS['function'],$this->MOD_MENU['function'])));
			$this->content.=$this->doc->divider(5);

			// Render content:
			$this->content.=$this->moduleContent();

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
				$this->content.=$this->geocodeAdmin();
			break;
			case 2:
				$this->content.=$this->batchGeocode();
			break;
			case 3:
				$this->content.=$this->downloadJSFiles();
			break;
			case 4:
				$this->content.=$this->apiKeyAdmin();
			break;
		}
	}

	function linkSelf($addParams)	{
		return htmlspecialchars('index.php?id='.$this->pObj->id.'&showLanguage='.rawurlencode(strip_tags(\TYPO3\CMS\Core\Utility\GeneralUtility::_GP('showLanguage'))).$addParams);
	}

	/**
	 * Rendering the encode-cache content
	 *
	 * @param	array		The Page tree data
	 * @return	string		HTML for the information table.
	 */
	function geocodeAdmin()	{

		$recordHandler = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\JBartels\WecMap\Module\MapAdministration\RecordHandler::class );

		global $LANG;

		$output   = $recordHandler->displaySearch();
		$output  .= $recordHandler->displayTable();

		return $output;
	}

	/*
	 * Admin module for setting Google Maps API Key.
	 * @return		string		HTML output of the module.
	 */
	function apiKeyAdmin() {
		global $TYPO3_CONF_VARS, $LANG;

		$domainmgr = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\JBartels\WecMap\Utility\DomainMgr::class);

		$blankDomainValue = 'Enter domain....';

		$cmd = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('cmd');

		switch($cmd) {
			case 'setkey' :

				// transform the POST array to our needs.
				// we then get a simple array in the form:
				// array('domain1', 'domain2', 'key1', 'key2'), etc.
				$post = $_POST;
				unset($post['cmd']);
				unset($post['SET']);
				unset($post['x']);
				unset($post['y']);

				$allDomains = $domainmgr->processPost($post);

				break;

			default :
				$allDomains = $domainmgr->getAllDomains();
				break;
		}

		$content = array();
		$content[] = '<style type="text/css" media="screen">input[type=image] {border: none; background: none;}</style>';
		$content[] = '<p style="margin-bottom:15px;">';
		$content[] = $LANG->getLL('apiInstructions');
		$content[] = '</p>';

		$content[] = '<form action="" method="POST">';
		$content[] = '<input name="cmd" type="hidden" value="setkey" />';

		$index = 0;

		// get number of entries that have a key
		$tempDomains = $allDomains;
		foreach( $tempDomains as $key => $value) {
			if(empty($value)) unset($tempDomains[$key]);
		}
		$number = count($tempDomains);

		foreach( $allDomains as $key => $value ) {

			// show the first summary text above all the already saved domains
			if($number != 0 && $index == 0) {
				$content[] = '<h1>Existing Domains</h1>';
				$content[] = '<p style="margin-bottom:15px;">';
				$content[] = $LANG->getLL('alreadySavedDomains');
				$content[] = '</p>';
			} else if ($number == $index) {
				$content[] = '<h1>Suggested Domains</h1>';
				$content[] = '<p style="margin-bottom:15px;">';
				$content[] = $LANG->getLL('suggestedDomains');
				$content[] = '</p>';
			}

			if($index < $number) {
				$deleteButton = '<input type="image" '.\TYPO3\CMS\Backend\Utility\IconUtility::skinImg($GLOBALS['BACK_PATH'],'gfx/garbage.gif','width="11" height="12"').' onclick="document.getElementById(\'key_'. $index .'\').value = \'\';" />';
			} else {
				$deleteButton = null;
			}

			$values = explode( '&', $value );
			$valuebrowser = $values[ 0 ];
			$valueserver = $values[ 1 ];
			$content[] = '<div class="domain-item" style="margin-bottom: 15px;">';
			$content[] = '<div style="width: 25em;"><strong>'. $key .'</strong> '. $deleteButton .'</div>';

			$content[] = '<input type="hidden" name="domain_'.$index.'" value="'. $key .'">';

			$content[] = '<div><label for="key_'. $index .'">'.$LANG->getLL('googleMapsBrowserApiKey').': </label>';
			$content[] = '<input style="width: 29em;" id="browserkey_'. $index .'" name="browserkey_'. $index .'" value="'.$valuebrowser.'" />';

			$content[] = '<label for="serverkey_'. $index .'">'.$LANG->getLL('googleMapsServerApiKey').': </label>';
			$content[] = '<input style="width: 29em;" id="serverkey_'. $index .'" name="serverkey_'. $index .'" value="'.$valueserver.'" /></div>';

			$content[] = '</div>';
			$index++;
		}

		$content[] = '<div id="adddomainbutton" style="margin-bottom: 15px;"><a href="#" onclick="document.getElementById(\'blank-domain\').style.display = \'block\'; document.getElementById(\'adddomainbutton\').style.display = \'none\'; document.getElementById(\'domain_'.$index.'\').value=\''. $blankDomainValue .'\';">Manually add a new API key for domain</a></div>';
		$content[] = '<div class="domain-item" id="blank-domain" style="margin-bottom: 15px; display: none;">';
		$content[] = '<div style="width: 35em;"><label style="display: none;" for="domain_'. $index .'">Domain: </label><input style="width: 12em;" id="domain_'. $index .'" name="domain_'. $index .'" value="" onfocus="this.value=\'\';"/> <input type="image" '.\TYPO3\CMS\Backend\Utility\IconUtility::skinImg($GLOBALS['BACK_PATH'],'gfx/garbage.gif','width="11" height="12"').' onclick="document.getElementById(\'key_'. $index .'\').value = \'\'; document.getElementById(\'blank-domain\').style.display =\'none\'; document.getElementById(\'adddomainbutton\').style.display = \'block\'; return false;" /></div>';

		$content[] = '<div><label for="browserkey_'. $index .'">'.$LANG->getLL('googleMapsBrowserApiKey').': </label>';
		$content[] = '<input style="width: 29em;" id="browserkey_'. $index .'" name="browserkey_'. $index .'" value="" />';
		$content[] = '<label for="serverkey_'. $index .'">'.$LANG->getLL('googleMapsServerApiKey').': </label>';
		$content[] = '<input style="width: 29em;" id="serverkey_'. $index .'" name="serverkey_'. $index .'" value="" /></div>';

		$content[] = '</div>';

		$content[] = '<input type="submit" value="'.$LANG->getLL('submit').'"/>';
		$content[] = '</form>';

		return implode(chr(10), $content);
	}

	/**
	 * Submodule for the batch geocoder.
	 *
	 * @return		string		HTML output.
	 */
	function batchGeocode() {
		global $LANG;
		$content = array();

		$batchGeocode = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\JBartels\WecMap\Module\MapAdministration\BatchGeocode::class, 1);
		$batchGeocode->addAllTables();
		$totalAddresses = $batchGeocode->getRecordCount();

		$content[] = '<h3>'.$LANG->getLL('batchGeocode').'</h3>';
		$content[] = '<p>'.$LANG->getLL('batchInstructions').'</p>';

		$content[] = '<p style="margin-top:1em;">'.$LANG->getLL('batchTables').'</p>';
		$content[] = '<ul>';
		foreach($GLOBALS['TCA'] as $tableName => $tableContents) {
			if($tableContents['ctrl']['EXT']['wec_map']['isMappable']) {
				$title = $LANG->sL($tableContents['ctrl']['title']);
				$content[] = '<li>'.$title.'</li>';
			}
		}
		$content[] = '</ul>';

		$content[] = '<div id="status" style="margin-bottom: 5px; display:none;">';
		$content[] =   '<div id="bar" style="width:300px; height:20px; border:1px solid black">';
		$content[] =     '<div id="progress" style="width:0%; height:20px; background-color:red"></div>';
		$content[] =   '</div>';
		$content[] =   '<p>'.$LANG->getLL('processedStart').' <span id="processed">0</span> '.$LANG->getLL('processedMid').' '.$totalAddresses.'.</p>';
		$content[] = '</div>';

		$content[] = '<input id="startGeocoding" type="submit" value="'.$LANG->getLL('startGeocoding').'">';

		return implode(chr(10), $content);
	}
	/**
	 * Rendering the encode-cache content
	 *
	 * @param	array		The Page tree data
	 * @return	string		HTML for the information table.
	 */
	function downloadJSFiles()	{
		global $LANG;

		$cmd = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('cmd');

		$content = array();

		switch($cmd) {
			case 'downloadJS' :
				$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['wec_map']);
				$content[] = $this->download( 'https://' . $extConf['mmURL'], 'markermanager.js' );
				$content[] = $this->download( 'https://' . $extConf['ibURL'], 'infobubble.js' );
				$content[] = $this->download( 'https://' . $extConf['omURL'], 'oms.min.js' );
				$content[] = '<br />';
				break;

			default :
				break;
		}

		$content[] = '<style type="text/css" media="screen">input[type=image] {border: none; background: none;}</style>';
		$content[] = '<p style="margin-bottom:15px;">';
		$content[] = $LANG->getLL('downloadInstructions');
		$content[] = '</p>';

		$content[] = '<form action="" method="POST">';
		$content[] = '<input name="cmd" type="hidden" value="downloadJS" />';
		$content[] = '<input type="submit" value="'.$LANG->getLL('download').'"/>';
		$content[] = '</form>';

		return implode(chr(10), $content);
	}

	/**
	 * @param string $sourceUrl
	 * @param string $destFile
     * @return string HTML
	 */
    protected function download($sourceUrl, $destFile)    {
		global $LANG;

		$destDir = \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName('EXT:wec_map/Resources/Public/JavaScript/ContribJS/');

			// Get file and cancel if not existing/accessible
		$remoteFileContent = \TYPO3\CMS\Core\Utility\GeneralUtility::getURL($sourceUrl);
		if ($remoteFileContent === FALSE) {
			return $LANG->getLL('downloadError') . $sourceUrl . '<br />';
		}

			// Create dir if not existing
		if (!file_exists($destDir)) {
			mkdir($destDir);
		}

			// Write content to disk
		$handle = fopen($destDir . $destFile, 'wb');
		fwrite($handle, $remoteFileContent);
		fclose($handle);

		return $LANG->getLL('downloadSuccess') . $destFile . '<br />';
    }

}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/mod1/index.php'])	{
include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/mod1/index.php']);
}


// Make instance:
$SOBE = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\JBartels\WecMap\Module\MapAdministration\Module::class);
$SOBE->init();
$SOBE->main();
$SOBE->printContent();

?>