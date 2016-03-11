<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

if(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('nn_address')) {
	$GLOBALS['TCA']['tx_nnaddress_domain_model_address']['ctrl']['EXT']['wec_map'] = array (
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

?>