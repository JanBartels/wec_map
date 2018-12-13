<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

return array(
	'txwecmapM1::deleteAll' => array(
		'path' => '/txwecmapM1/deleteAll',
		'target' => \JBartels\WecMap\Ajax\MapAdministrationBackendModule::class . '::ajaxDeleteAll'
	),
	'txwecmapM1::deleteSingle' => array(
		'path' => '/txwecmapM1/ajaxDeleteSingle',
		'target' => \JBartels\WecMap\Ajax\MapAdministrationBackendModule::class . '::ajaxDeleteSingle'
	),
	'txwecmapM1::saveRecord' => array(
		'path' => '/txwecmapM1/ajaxSaveRecord',
		'target' => \JBartels\WecMap\Ajax\MapAdministrationBackendModule::class . '::ajaxSaveRecord'
	),
	'txwecmapM1::batchGeocode' => array(
		'path' => '/txwecmapM1/ajaxBatchGeocode',
		'target' => \JBartels\WecMap\Ajax\MapAdministrationBackendModule::class . '::ajaxBatchGeocode'
	),
	'txwecmapM1::listRecords' => array(
		'path' => '/txwecmapM1/ajaxListRecord',
		'target' => \JBartels\WecMap\Ajax\MapAdministrationBackendModule::class . '::ajaxListRecord'
	)
);

?>
