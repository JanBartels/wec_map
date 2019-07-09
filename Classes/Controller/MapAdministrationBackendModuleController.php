<?php
namespace JBartels\WecMap\Controller;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2019 Jan Bartels
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

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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
		// fetch all cached addresses
		$addresses = \JBartels\WecMap\Utility\Cache::getAllAddresses();
		$this->view->assign( 'addresses', $addresses );
	}

	/**
	 * action batch
	 *
	 * @return void
	 */
	public function batchAction() {

		$batchGeocode = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\JBartels\WecMap\Utility\BatchGeocode::class);
		$batchGeocode->addAllTables();
		$totalAddresses = 0;

		$tableNames = $batchGeocode->getTableNames();
		$tables = [];
		foreach( $tableNames as $tableName ) {
			$recordCount = $batchGeocode->getTableRecordCount( $tableName );
			$totalAddresses += $recordCount;

			$tables[] = [
				'table' => $tableName,
				'title' => $this->getLanguageService()->sL( $GLOBALS['TCA'][$tableName]['ctrl']['title'] ),
				'recordCount' => $recordCount
			];
		}

		$this->view->assign( 'totalAddresses', $totalAddresses );
		$this->view->assign( 'tables', $tables );
	}

	/**
	 * action download
	 *
	 * @return void
	 */
	public function downloadAction() {
		// form submitted
        if($this->request->hasArgument('submit')) {
			$extConf = $this->getExtConf();
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
		// nothing to do. Just render the template
    }

    /**
	 * action editCacheEntry
	 *
	 * @return void
     */
	public function editCacheEntryAction()
    {
    }

    /**
	 * action saveCacheEntry
	 *
	 * @return void
     */
	public function saveCacheEntryAction()
    {
    }

    /**
	 * action closeCacheEntry
	 *
	 * @return void
     */
	public function closeCacheEntryAction()
    {
    }

    /**
	 * action deleteCacheEntry
	 *
	 * @return void
     */
	public function deleteCacheEntryAction()
    {
		$hash = $this->request->getArgument('hash');
		\JBartels\WecMap\Utility\Cache::deleteByUID( $hash );
		$this->forward( 'geocode' );
    }

    /**
	 * action deleteCache
	 *
	 * @return void
     */
	public function deleteCacheAction()
    {
		\JBartels\WecMap\Utility\Cache::deleteAll();
		$this->forward( 'geocode' );
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

    /**
     * @return array extConf
     */
	protected function getExtConf()
    {
		if ( \TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) >= 9000000)
        	return GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('wec_map');
        else
        	return unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['wec_map']);
	}

}
