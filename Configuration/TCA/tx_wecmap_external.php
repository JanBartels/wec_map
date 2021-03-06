<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

return array(
	'ctrl' => Array (
		'title' => 'LLL:EXT:wec_map/Resources/Private/Languages/locallang_db.xlf:tx_wecmap_external',
	 	'label' => 'title',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'delete' => 'deleted',
		'iconfile' => 'EXT:wec_map/Resources/Public/Images/icon_tx_wecmap_external_resource.gif',
	),
	"interface" => Array (
		"showRecordFieldList" => "title,url"
	),
	"columns" => Array (
		"title" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:wec_map/Resources/Private/Languages/locallang_db.xlf:tx_wecmap_external.title",
			"config" => Array (
				"type" => "input",
				"size" => "32",
				"max" => "128",
			)
		),
		"url" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:wec_map/Resources/Private/Languages/locallang_db.xlf:tx_wecmap_external.url",
			"config" => Array (
				"type" => "input",
				"size" => "32",
				"max" => "128",

				'renderType' => 'inputLink',
				'fieldControl' => Array(
					'linkPopup' => Array(
						'options' => Array(
							'title' => 'Link',
//							'title' => 'LLL:EXT:wec_map/...',
							'allowedExtensions' => 'kml, xml, kmz',
							'blindLinkOptions' => 'mail, page, spec, url',
							'blindLinkFields' => 'class, params, target, title'
						),
					),
				), 
			),
		),
	),
	"types" => Array (
		"0" => Array("showitem" => "title, url")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "title, url"),
	),
);

?>