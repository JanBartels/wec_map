<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

/* Set up the tt_content fields for the frontend plugins */
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['wec_map_pi1']='layout,select_key,pages,recursive';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['wec_map_pi2']='layout,select_key,pages,recursive';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['wec_map_pi3']='layout,select_key,pages,recursive';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['wec_map_pi1']='pi_flexform';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['wec_map_pi2']='pi_flexform';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['wec_map_pi3']='pi_flexform';

/* Add the plugins */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPlugin(Array('LLL:EXT:wec_map/Resources/Private/Languages/locallang_db.xlf:tt_content.list_type_pi1', 'wec_map_pi1'),'list_type', 'wec_map');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPlugin(Array('LLL:EXT:wec_map/Resources/Private/Languages/locallang_db.xlf:tt_content.list_type_pi2', 'wec_map_pi2'),'list_type', 'wec_map');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPlugin(Array('LLL:EXT:wec_map/Resources/Private/Languages/locallang_db.xlf:tt_content.list_type_pi3', 'wec_map_pi3'),'list_type', 'wec_map');

/* Add the flexforms to the TCA */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue('wec_map_pi1', 'FILE:EXT:wec_map/Configuration/FlexForms/SimpleMap.xml');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue('wec_map_pi2', 'FILE:EXT:wec_map/Configuration/FlexForms/FEUserMap.xml');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue('wec_map_pi3', 'FILE:EXT:wec_map/Configuration/FlexForms/DataTableMap.xml');

?>