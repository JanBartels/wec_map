<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

return array(
	'txwecmapM1::deleteAll' => array(
		'path' => '/txwecmapM1/deleteAll',
		'target' => \JBartels\WecMap\Module\MapAdministration\Ajax::class . '::ajaxDeleteAll'
	),
	'txwecmapM1::deleteSingle' => array(
		'path' => '/txwecmapM1/ajaxDeleteSingle',
		'target' => \JBartels\WecMap\Module\MapAdministration\Ajax::class . '::ajaxDeleteSingle'
	),
	'txwecmapM1::saveRecord' => array(
		'path' => '/txwecmapM1/ajaxSaveRecord',
		'target' => \JBartels\WecMap\Module\MapAdministration\Ajax::class . '::ajaxSaveRecord'
	),
	'txwecmapM1::batchGeocode' => array(
		'path' => '/txwecmapM1/ajaxBatchGeocode',
		'target' => \JBartels\WecMap\Module\MapAdministration\Ajax::class . '::ajaxBatchGeocode'
	),
	'txwecmapM1::listRecords' => array(
		'path' => '/txwecmapM1/ajaxListRecord',
		'target' => \JBartels\WecMap\Module\MapAdministration\Ajax::class . '::ajaxListRecord'
	)
);

?>
