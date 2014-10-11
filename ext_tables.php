<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

#require_once(PATH_tslib . 'class.tslib_pibase.php');
#require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('wec_map').'class.tx_wecmap_backend.php');

if (TYPO3_MODE=='BE')    {
	/* Add the backend modules */
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModule('tools','txwecmapM1','',\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY).'mod1/');
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModule('tools','txwecmapM2','',\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY).'mod2/');

	/* Add the plugin to the New Content Element wizard */
	$TBE_MODULES_EXT['xMOD_db_new_content_el']['addElClasses']['tx_wecmap_pi1_wizicon'] = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY).'pi1/class.tx_wecmap_pi1_wizicon.php';
	$TBE_MODULES_EXT['xMOD_db_new_content_el']['addElClasses']['tx_wecmap_pi2_wizicon'] = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY).'pi2/class.tx_wecmap_pi2_wizicon.php';
	$TBE_MODULES_EXT['xMOD_db_new_content_el']['addElClasses']['tx_wecmap_pi3_wizicon'] = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY).'pi3/class.tx_wecmap_pi3_wizicon.php';
}

/* Set up the tt_content fields for the two frontend plugins */
\TYPO3\CMS\Core\Utility\GeneralUtility::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key,pages,recursive';
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi2']='layout,select_key,pages,recursive';
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi3']='layout,select_key,pages,recursive';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1']='pi_flexform';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi2']='pi_flexform';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi3']='pi_flexform';

/* Adds the plugins and flexforms to the TCA */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPlugin(Array('LLL:EXT:wec_map/locallang_db.xml:tt_content.list_type_pi1', $_EXTKEY.'_pi1'),'list_type');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPlugin(Array('LLL:EXT:wec_map/locallang_db.xml:tt_content.list_type_pi2', $_EXTKEY.'_pi2'),'list_type');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPlugin(Array('LLL:EXT:wec_map/locallang_db.xml:tt_content.list_type_pi3', $_EXTKEY.'_pi3'),'list_type');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($_EXTKEY.'_pi1', 'FILE:EXT:wec_map/pi1/flexform_ds.xml');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($_EXTKEY.'_pi2', 'FILE:EXT:wec_map/pi2/flexform_ds.xml');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($_EXTKEY.'_pi3', 'FILE:EXT:wec_map/pi3/flexform_ds.xml');

// register Ajax scripts
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::registerAjaxHandler('txwecmapM1::deleteAll',        'tx_wecmap_module1_ajax->ajaxDeleteAll');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::registerAjaxHandler('txwecmapM1::deleteSingle',     'tx_wecmap_module1_ajax->ajaxDeleteSingle');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::registerAjaxHandler('txwecmapM1::updatePagination', 'tx_wecmap_module1_ajax->ajaxUpdatePagination');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::registerAjaxHandler('txwecmapM1::saveRecord',       'tx_wecmap_module1_ajax->ajaxSaveRecord');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::registerAjaxHandler('txwecmapM1::batchGeocode',     'tx_wecmap_module1_ajax->ajaxBatchGeocode');

/* Add static TS template for plugins */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY,'static/','WEC Map API');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY,'pi3/static/','WEC Table Map');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY,'pi2/static/','WEC Frontend User Map');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY,'pi1/static/','WEC Simple Map');

$TCA['tx_wecmap_external'] = Array (
	'ctrl' => Array (
		'title' => 'LLL:EXT:wec_map/locallang_db.xml:tx_wecmap_external',
	 	'label' => 'title',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'delete' => 'deleted',
		'dynamicConfigFile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY).'tca.php',
		'iconfile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($_EXTKEY).'res/icon_tx_wecmap_external_resource.gif',
	),
	'feInterface' => Array (
		'fe_admin_fieldList' => 'title, url',
	)
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_wecmap_external');


/* Define the address related fields for a frontend user */
\TYPO3\CMS\Core\Utility\GeneralUtility::loadTCA('fe_users');
if(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('sr_feuser_register')) {
	$TCA['fe_users']['ctrl']['EXT']['wec_map'] = array (
		'isMappable' => 1,
		'addressFields' => array (
			'street' => 'address',
			'city' => 'city',
			'state' => 'zone',
			'zip' => 'zip',
			'country' => 'static_info_country',
		),
	);
} else {
	$TCA['fe_users']['ctrl']['EXT']['wec_map'] = array (
		'isMappable' => 1,
		'addressFields' => array (
			'street' => 'address',
			'city' => 'city',
			'state' => '',
			'zip' => 'zip',
			'country' => 'country',
		),
	);
}

if(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('tt_address')) {
	\TYPO3\CMS\Core\Utility\GeneralUtility::loadTCA('tt_address');
	$TCA['tt_address']['ctrl']['EXT']['wec_map'] = array (
		'isMappable' => 1,
		'addressFields' => array (
			'street' => 'address',
			'city' => 'city',
			'state' => 'region',
			'zip' => 'zip',
			'country' => 'country',
		),
	);
}

if(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('nn_address')) {
	\TYPO3\CMS\Core\Utility\GeneralUtility::loadTCA('nn_address');
	$TCA['tx_nnaddress_domain_model_address']['ctrl']['EXT']['wec_map'] = array (
		'isMappable' => 1,
		'addressFields' => array (
			'street' => 'street',
			'city' => 'city',
			'state' => 'region',
			'zip' => 'zip',
			'country' => 'country',
		),
	);
}

/* If we want to show a map in frontend user records, add it to the TCA */
if(tx_wecmap_backend::getExtConf('feUserRecordMap')) {
	$mapTCA = array (
		'tx_wecmap_map' => array (
			'exclude' => 1,
			'label' => 'LLL:EXT:wec_map/locallang_db.xml:berecord_maplabel',
			'config' => array (
				'type' => 'passthrough',
				'form_type' => 'user',
				'userFunc' => 'tx_wecmap_backend->drawMap',
			),
		),
	);

	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('fe_users', $mapTCA, 1);
	$TCA['fe_users']['interface']['showRecordFieldList'] .= ',tx_wecmap_map';
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('fe_users', '--div--;LLL:EXT:wec_map/locallang_db.xml:berecord_maplabel,tx_wecmap_map');
}


/* If we want to show the geocoding status in frontend user records, add it to the TCA */
if(tx_wecmap_backend::getExtConf('geocodingStatus')) {
	$geocodeTCA = array (
		'tx_wecmap_geocode' => array (
			'exclude' => 1,
			'label' => 'LLL:EXT:wec_map/locallang_db.xml:berecord_geocodelabel',
			'config' => array(
				'type' => 'passthrough',
				'form_type' => 'user',
				'userFunc' => 'tx_wecmap_backend->checkGeocodeStatus',
			),
		),
	);

	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('fe_users', $geocodeTCA, 1);
	$TCA['fe_users']['interface']['showRecordFieldList'] .= ',tx_wecmap_geocode';
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('fe_users', 'tx_wecmap_geocode');
}

?>