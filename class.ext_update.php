<?php
namespace JBartels\WecMap;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * Update class for the extension manager.
 */
class ext_update
{
    /**
     * Array of flash messages (params) array[][status,title,message]
     *
     * @var array
     */
    protected $messageArray = array();

    /**
     * @var \TYPO3\CMS\Core\Configuration\FlexForm\FlexFormTools
     */
    protected $flexFormTools;

    /**
     * Mapping for switchable controller actions
     * in FlexForms
     *
     * @var array
     */
    protected $mapControlSizeWhere = '%<field index="mapControlSize">%<value index="vDEF">%</value>%</field>%';

    /**
     * Main update function called by the extension manager.
     *
     * @return string
     */
    public function main()
    {
        $this->processUpdates();
        return $this->generateOutput();
    }

    /**
     * Called by the extension manager to determine if the update menu entry
     * should by showed.
     *
     * @return bool
     */
    public function access()
    {
        // Check for changed options in FlexForms
		$where = sprintf(
			'pi_flexform LIKE %s AND list_type LIKE "wec_map_pi%%"',
			$this->getDatabaseConnection()->fullQuoteStr($this->mapControlSizeWhere, 'tt_content')
		);
		$amountOfRecords = $this->getDatabaseConnection()->exec_SELECTcountRows(
			'*',
			'tt_content',
			$where
		);
		if ($amountOfRecords > 0) {
			return true;
		}
        return false;
    }

    /**
     * The actual update function. Add your update task in here.
     *
     * @return void
     */
    protected function processUpdates()
    {
        $this->migrateToNewZoomControlInFlexForms();
    }

    /**
     * Migrate old FlexForm values for Zoom Control to the new one
     *
     * @return void
     */
    protected function migrateToNewZoomControlInFlexForms()
    {
    	$count = 0;
		$where = sprintf(
			'pi_flexform LIKE %s AND list_type LIKE "wec_map_pi%%"',
			$this->getDatabaseConnection()->fullQuoteStr($this->mapControlSizeWhere, 'tt_content')
		);
		$rows = $this->getDatabaseConnection()->exec_SELECTgetRows(
			'uid, pi_flexform',
			'tt_content',
			$where
		);

		foreach ($rows as $key => $row) {
			$flexformData = \TYPO3\CMS\Core\Utility\GeneralUtility::xml2array($row['pi_flexform']);

			$mapControlSize = $flexformData['data']['mapControls']['lDEF']['mapControlSize']['vDEF'];
			unset( $flexformData['data']['mapControls']['lDEF']['mapControlSize']);
			switch ( $mapControlSize ) {
			case 'large':
			case 'small':
			case 'zoomonly':
				$flexformData['data']['mapControls']['lDEF']['showZoom']['vDEF']='1';
				break;
			case 'none':
			case '':
				$flexformData['data']['mapControls']['lDEF']['showZoom']['vDEF']='0';
				break;
			}
			$flexformData = $this->getFlexFormTools()->flexArray2Xml($flexformData, TRUE);

			$this->getDatabaseConnection()->exec_UPDATEquery(
				'tt_content',
				'uid=' . (int)$row['uid'],
				array(
					'pi_flexform' => $flexformData
				)
			);
			$count++;
        }
        $this->messageArray[] = array(
            \TYPO3\CMS\Core\Messaging\FlashMessage::OK,
            'Migrating flexforms successful',
            'We have updated '.$count.' related tt_content records'
        );
    }

    /**
     * Generates output by using flash messages
     *
     * @return string
     */
    protected function generateOutput()
    {
        /** @var \TYPO3\CMS\Core\Messaging\FlashMessageService $flashMessageService */
        $flashMessageService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Messaging\FlashMessageService::class);
        $flashMessageQueue = $flashMessageService->getMessageQueueByIdentifier();

        /** @var \TYPO3\CMS\Fluid\View\StandaloneView $view */
        $view = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Fluid\View\StandaloneView::class);
        $view->setTemplatePathAndFilename(
            \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName('EXT:wec_map/Resources/Private/Templates/ExtUpdate.html')
        );
        foreach ($this->messageArray as $messageItem) {
            /** @var \TYPO3\CMS\Core\Messaging\FlashMessage $flashMessage */
            $flashMessage = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Messaging\FlashMessage::class,
                $messageItem[2],
                $messageItem[1],
                $messageItem[0]
            );

            $flashMessageQueue->enqueue($flashMessage);
        }
        return $view->render();
    }

    /**
     * Get TYPO3s Database Connection
     *
     * @return \TYPO3\CMS\Core\Database\DatabaseConnection
     */
    protected function getDatabaseConnection()
    {
        return $GLOBALS['TYPO3_DB'];
    }

    /**
     * Get TYPO3s FlexFormTools
     *
     * @return FlexFormTools
     */
    protected function getFlexFormTools()
    {
        if (!$this->flexFormTools instanceof FlexFormTools) {
            $this->flexFormTools = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Configuration\FlexForm\FlexFormTools::class);
        }
        return $this->flexFormTools;
    }
}
