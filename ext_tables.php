<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

if (TYPO3_MODE=='BE')    {
	/* Add the backend modules */
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModule('tools','txwecmapM1','',\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY).'Classes/Module/MapAdministration/');
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModule('tools','txwecmapM2','',\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY).'Classes/Module/FEUserMap/');

	/* Add the plugin to the New Content Element wizard */
	$TBE_MODULES_EXT['xMOD_db_new_content_el']['addElClasses']['JBartels\\WecMap\\Plugin\\SimpleMap\\WizIcon']    = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY).'Classes/Plugin/SimpleMap/WizIcon.php';
	$TBE_MODULES_EXT['xMOD_db_new_content_el']['addElClasses']['JBartels\\WecMap\\Plugin\\FEUserMap\\WizIcon']    = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY).'Classes/Plugin/FEUserMap/WizIcon.php';
	$TBE_MODULES_EXT['xMOD_db_new_content_el']['addElClasses']['JBartels\\WecMap\\Plugin\\DataTableMap\\WizIcon'] = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY).'Classes/Plugin/DataTableMap/WizIcon.php';
}

/* Set up the tt_content fields for the two frontend plugins */
/* DO NOT MOVE TO Configuration/TCA/Overrides/tt_content.php! */
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key,pages,recursive';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi2']='layout,select_key,pages,recursive';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi3']='layout,select_key,pages,recursive';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1']='pi_flexform';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi2']='pi_flexform';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi3']='pi_flexform';

// register Ajax scripts
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::registerAjaxHandler('txwecmapM1::deleteAll',        'JBartels\\WecMap\\Module\\MapAdministration\\Ajax->ajaxDeleteAll');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::registerAjaxHandler('txwecmapM1::deleteSingle',     'JBartels\\WecMap\\Module\\MapAdministration\\Ajax->ajaxDeleteSingle');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::registerAjaxHandler('txwecmapM1::saveRecord',       'JBartels\\WecMap\\Module\\MapAdministration\\Ajax->ajaxSaveRecord');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::registerAjaxHandler('txwecmapM1::batchGeocode',     'JBartels\\WecMap\\Module\\MapAdministration\\Ajax->ajaxBatchGeocode');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::registerAjaxHandler('txwecmapM1::listRecords',      'JBartels\\WecMap\\Module\\MapAdministration\\Ajax->ajaxListRecord');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_wecmap_external');


if( \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('tt_address')
  && \TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger(TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getExtensionVersion('tt_address')) <= TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger('2.3.5')
  ) {
    include_once(TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY)."Configuration/TCA/Overrides/tt_address.php");
}
?>