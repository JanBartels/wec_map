<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

if(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('tt_address')) {
	$GLOBALS['TCA']['tt_address']['ctrl']['EXT']['wec_map'] = array (
		'isMappable' => 1,
		'addressFields' => array (
			'street' => 'address',
			'city' => 'city',
			'state' => 'region',
			'zip' => 'zip',
			'country' => 'country',
		),
	);

	/* If we want to show a map in address records, add it to the TCA */
	$mapTCA = array (
		'tx_wecmap_map' => array (
			'exclude' => 1,
			'label' => 'LLL:EXT:wec_map/Resources/Private/Languages/locallang_db.xlf:berecord_maplabel',
			'config' => array (
				'type' => 'user',
				'userFunc' => 'JBartels\\WecMap\\Utility\\Backend->drawMap',
			),
		),
	);

	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('tt_address', $mapTCA, 1);
	$GLOBALS['TCA']['tt_address']['interface']['showRecordFieldList'] .= ',tx_wecmap_map';
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('tt_address', '--div--;LLL:EXT:wec_map/Resources/Private/Languages/locallang_db.xlf:berecord_maplabel,tx_wecmap_map');


	/* If we want to show the geocoding status in address records, add it to the TCA */
	if(\JBartels\WecMap\Utility\Backend::getExtConf('geocodingStatus')) {
		$geocodeTCA = array (
			'tx_wecmap_geocode' => array (
				'exclude' => 1,
				'label' => 'LLL:EXT:wec_map/Resources/Private/Languages/locallang_db.xlf:berecord_geocodelabel',
				'config' => array(
					'type' => 'user',
					'userFunc' => 'JBartels\\WecMap\\Utility\\Backend->checkGeocodeStatus',
				),
			),
		);

		\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('tt_address', $geocodeTCA, 1);
		$GLOBALS['TCA']['tt_address']['interface']['showRecordFieldList'] .= ',tx_wecmap_geocode';
		\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('tt_address', 'tx_wecmap_geocode');
	}
}

?>