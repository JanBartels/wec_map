<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

/* Add the frontend plugins */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPItoST43($_EXTKEY,'pi1/class.tx_wecmap_pi1.php','_pi1','list_type',0);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPItoST43($_EXTKEY,'pi2/class.tx_wecmap_pi2.php','_pi2','list_type',0);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPItoST43($_EXTKEY,'pi3/class.tx_wecmap_pi3.php','_pi3','list_type',0);

$GLOBALS ['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = 'EXT:wec_map/class.tx_wecmap_backend.php:tx_wecmap_backend';

/* Add the Google geocoding service */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService($_EXTKEY,'geocode','tx_wecmap_geocode_google',
	array(

		'title' => 'Google Maps Address Lookup API V3',
		'description' => '',

		'subtype' => '',

		'available' => TRUE,
		'priority' => 100,
		'quality' => 100,

		'os' => '',
		'exec' => '',

		'classFile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY).'geocode_service/class.tx_wecmap_geocode_google.php',
		'className' => 'tx_wecmap_geocode_google',
	)
);

?>