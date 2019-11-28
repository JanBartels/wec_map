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
	const FOLDER_ContentUploads = '_migrated/wecmap_resources';

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
     * WHERE-clause for zoom in PI-Flexform
     *
     * @var string
     */
    protected $mapControlSizeWhere = '%<field index="mapControlSize">%<value index="vDEF">%</value>%</field>%';

    /**
     * WHERE-clause for kml in PI-Flexform
     *
     * @var string
     */
    protected $mapKmlWhere = '%<field index="kml">%<value index="vDEF">%</value>%</field>%';

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
        return $this->accessValueInFlexForms( $this->mapControlSizeWhere )
            || $this->accessValueInFlexForms( $this->mapKmlWhere )
            ;
    }

    /**
     * Check if certain flexform-values are set in plugin
     *
     * @param string 
     * @return bool
     */
    protected function accessValueInFlexForms( $flexformWhere )
    {
        // Check for changed options in FlexForms
		$queryBuilder = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance( \TYPO3\CMS\Core\Database\ConnectionPool::class )
            ->getQueryBuilderForTable( 'tt_content' );
        $queryBuilder
            ->getRestrictions()->removeAll();
        $count = $queryBuilder
            ->count('*')
			->from( 'tt_content' )
			->where(
                $queryBuilder->expr()->like(
                    'pi_flexform',
                    $queryBuilder->createNamedParameter( $flexformWhere )
                ),
                $queryBuilder->expr()->like(
                    'list_type',
                    $queryBuilder->createNamedParameter( 'wec_map_pi%%' )
                )
            )
            ->execute()
            ->fetchColumn(0);


$queryBuilder = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance( \TYPO3\CMS\Core\Database\ConnectionPool::class )
    ->getQueryBuilderForTable( 'tt_content' );
$queryBuilder
    ->getRestrictions()->removeAll();
$sql = $queryBuilder
    ->count('*')
    ->from( 'tt_content' )
    ->where(
        $queryBuilder->expr()->like(
            'pi_flexform',
            $queryBuilder->createNamedParameter( $flexformWhere )
        ),
        $queryBuilder->expr()->like(
            'list_type',
            $queryBuilder->createNamedParameter( 'wec_map_pi%%' )
        )
    )
    ->getSQL();

\TYPO3\CMS\Core\Utility\DebugUtility::debug( $flexformWhere );
\TYPO3\CMS\Core\Utility\DebugUtility::debug( $sql );
\TYPO3\CMS\Core\Utility\DebugUtility::debug( $count );
        return $count > 0;
    }

    /**
     * The actual update function. Add your update task in here.
     *
     * @return void
     */
    protected function processUpdates()
    {
        $this->migrateToNewZoomControlInFlexForms();
        $this->migrateKMLToFALInFlexForms();
    }

    /**
     * Migrate old FlexForm values for Zoom Control to the new one
     *
     * @return void
     */
    protected function migrateToNewZoomControlInFlexForms()
    {
		$queryBuilder = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance( \TYPO3\CMS\Core\Database\ConnectionPool::class )
    		->getQueryBuilderForTable( 'tt_content' );
        $queryBuilder
            ->getRestrictions()->removeAll();
        $statement = $queryBuilder
            ->select('*')
			->from( 'tt_content' )
			->where(
                $queryBuilder->expr()->like(
                    'pi_flexform',
                    $queryBuilder->createNamedParameter( $this->mapControlSizeWhere )
                ),
                $queryBuilder->expr()->like(
                    'list_type',
                    $queryBuilder->createNamedParameter( 'wec_map_pi%%' )
                )
            )
            ->execute();
        $count = 0;
        while( $row = $statement->fetch() ) {            
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
            
            $queryBuilder = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance( \TYPO3\CMS\Core\Database\ConnectionPool::class )
                ->getQueryBuilderForTable( 'tt_content' );
            $queryBuilder
                ->getRestrictions()->removeAll();
            $queryBuilder
                ->update('tt_content')
                ->where(
                    $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter( $row['uid'], \PDO::PARAM_INT ) )
                )
                ->set('pi_flexform', $flexformData )
                ->execute();
			$count++;
        }
        $this->messageArray[] = array(
            \TYPO3\CMS\Core\Messaging\FlashMessage::OK,
            'Migrating mapControls successful',
            'We have updated '.$count.' related tt_content records'
        );
    }

        
    /**
     * Migrate old FlexForm values for kml-overlays to FAL
     *
     * @return void
     */
    protected function migrateKMLToFALInFlexForms()
    {
        # Set up FAL
		$fileadminDirectory = rtrim($GLOBALS['TYPO3_CONF_VARS']['BE']['fileadminDir'], '/') . '/';
		/** @var $storageRepository \TYPO3\CMS\Core\Resource\StorageRepository */
		$storageRepository = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Resource\\StorageRepository');
		$storages = $storageRepository->findAll();
		foreach ($storages as $storage) {
			$storageRecord = $storage->getStorageRecord();
			$configuration = $storage->getConfiguration();
			$isLocalDriver = $storageRecord['driver'] === 'Local';
			$isOnFileadmin = !empty($configuration['basePath']) && \TYPO3\CMS\Core\Utility\GeneralUtility::isFirstPartOfStr($configuration['basePath'], $fileadminDirectory);
			if ($isLocalDriver && $isOnFileadmin) {
				$storage = $storage;
				break;
			}
		}
		if (!isset($storage)) {
			throw new \RuntimeException('Local default storage could not be initialized - might be due to missing sys_file* tables.');
        }
        # Create folder if neccessary
		if (!$storage->hasFolder(self::FOLDER_ContentUploads)) {
			$storage->createFolder(self::FOLDER_ContentUploads, $storage->getRootLevelFolder());
		}

        $fileFactory = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Resource\\ResourceFactory');
		$fileIndexRepository = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Resource\\Index\\FileIndexRepository');
		$targetDirectory = PATH_site . $fileadminDirectory . self::FOLDER_ContentUploads . '/';


		$queryBuilder = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance( \TYPO3\CMS\Core\Database\ConnectionPool::class )
    		->getQueryBuilderForTable( 'tt_content' );
        $queryBuilder
            ->getRestrictions()->removeAll();
        $statement = $queryBuilder
            ->select('*')
			->from( 'tt_content' )
			->where(
                $queryBuilder->expr()->like(
                    'pi_flexform',
                    $queryBuilder->createNamedParameter( $this->mapKmlWhere )
                ),
                $queryBuilder->expr()->like(
                    'list_type',
                    $queryBuilder->createNamedParameter( 'wec_map_pi%%' )
                )
            )
            ->execute();
        $count = 0;
return;        
        while( $row = $statement->fetch() ) {            
        	$flexformData = \TYPO3\CMS\Core\Utility\GeneralUtility::xml2array($row['pi_flexform']);

            $uidExternalResource = $flexformData['data']['default']['lDEF']['kml']['vDEF'];
			unset( $flexformData['data']['default']['lDEF']['kml']);
			$flexformData['data']['default']['lDEF']['kmlfal']['vDEF']='4711';
            $flexformData = $this->getFlexFormTools()->flexArray2Xml($flexformData, TRUE);

            $queryBuilder = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance( \TYPO3\CMS\Core\Database\ConnectionPool::class )
                ->getQueryBuilderForTable( 'tt_content' );
            $queryBuilder
                ->getRestrictions()->removeAll();
            $queryBuilder
                ->update('tt_content')
                ->where(
                    $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter( $row['uid'], \PDO::PARAM_INT ) )
                )
                ->set('pi_flexform', $flexformData )
                ->execute();
			$count++;
        }
        $this->messageArray[] = array(
            \TYPO3\CMS\Core\Messaging\FlashMessage::OK,
            'Migrating kml-resources successful',
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
