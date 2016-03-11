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
}

?>