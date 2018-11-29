<?php
namespace JBartels\WecMap\Controller;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2018 Jan Bartels
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Backend Controller
 */
class MapAdministrationBackendModuleController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

	/**
	 * action geocode
	 *
	 * @return void
	 */
	public function geocodeAction() {

		$addresses = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*','tx_wecmap_cache','', 'address', 'address', $limit);
		$this->view->assign( 'addresses', $addresses );
	}

	/**
	 * action batch
	 *
	 * @return void
	 */
	public function batchAction() {

/*
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

		// form submitted
        if($this->request->hasArgument('submit')) {
			$this->view->assign('Mode', 'Importing');
			if ( $this->doImport() ) {
				$this->view->assign('Result', '<strong style="color: red">DER IMPORT KONNTE NICHT DURCHGEFÜHRT WERDEN</strong>' );
			} else {
				$this->view->assign('Result', '<strong style="color: green">IMPORT DURCHGEFÜHRT</strong>' );
			}
        } else {
			$this->view->assign('Mode', 'Displaying');
        }
*/
	}

	/**
	 * action download
	 *
	 * @return void
	 */
	public function downloadAction() {
		// form submitted
        if($this->request->hasArgument('submit')) {
			$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['wec_map']);
			$results = [];
			$results[] = $this->download( 'https://' . $extConf['mmURL'], 'markermanager.js' );
			$results[] = $this->download( 'https://' . $extConf['ibURL'], 'infobubble.js' );
			$results[] = $this->download( 'https://' . $extConf['omURL'], 'oms.min.js' );
			$this->view->assign( 'results', $results );
        }
	}

    /**
	 * action apikey
	 *
	 * @return void
     */
    public function apikeyAction()
    {
    }

	/**
	 * @param string $sourceUrl
	 * @param string $destFile
     * @return array
	 */
    protected function download($sourceUrl, $destFile)    {
		$destDir = \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName('EXT:wec_map/Resources/Public/JavaScript/ContribJS/');
		// Get file and cancel if not existing/accessible
		$remoteFileContent = \TYPO3\CMS\Core\Utility\GeneralUtility::getURL($sourceUrl);
		if ($remoteFileContent === FALSE) {
			return array(
				'Status' => FALSE,
				'URL' => $sourceUrl
			);
		}
		// Create dir if not existing
		if (!file_exists($destDir)) {
			mkdir($destDir);
		}
		// Write content to disk
		$handle = fopen($destDir . $destFile, 'wb');
		fwrite($handle, $remoteFileContent);
		fclose($handle);
		return array(
			'Status' => TRUE,
			'URL' => $sourceUrl
		);
    }

    protected function getPageTitle( $pid )
    {
		$pageSelect = $this->objectManager->get('TYPO3\CMS\Frontend\Page\PageRepository');
		$pageSelect->init(false);
		$row = $pageSelect->getPage( $pid );
 		if (!empty($row)) {
			return $row['title'];
		}
		return '';
    }


    /**
     * Creates te URI for a backend action
     *
     * @param string $controller
     * @param string $action
     * @param array $parameters
     * @return string
     */
    protected function getHref($controller, $action, $parameters = [])
    {
        $uriBuilder = $this->objectManager->get(\TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder::class);
        $uriBuilder->setRequest($this->request);
        return $uriBuilder->reset()->uriFor($action, $parameters, $controller);
    }

    /**
     * Returns the Backend User
     * @return BackendUserAuthentication
     */
    protected function getBackendUser()
    {
        return $GLOBALS['BE_USER'];
    }

    /**
     * @return LanguageService
     */
    protected function getLanguageService()
    {
        return $GLOBALS['LANG'];
    }

}