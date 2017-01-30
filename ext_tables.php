<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

if (TYPO3_MODE=='BE')    {
	/* Add the backend modules */
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModule('tools','txwecmapM1','',\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('wec_map').'Classes/Module/MapAdministration/');
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModule('tools','txwecmapM2','',\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('wec_map').'Classes/Module/FEUserMap/');
}

/* Set up the tt_content fields for the two frontend plugins */
/* DO NOT MOVE TO Configuration/TCA/Overrides/tt_content.php! */
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['wec_map_pi1']='layout,select_key,pages,recursive';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['wec_map_pi2']='layout,select_key,pages,recursive';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['wec_map_pi3']='layout,select_key,pages,recursive';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['wec_map_pi1']='pi_flexform';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['wec_map_pi2']='pi_flexform';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['wec_map_pi3']='pi_flexform';

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_wecmap_external');
?>