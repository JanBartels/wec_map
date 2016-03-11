<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

return array(
	'ctrl' => Array (
		'title' => 'LLL:EXT:wec_map/Resources/Private/Languages/locallang_db.xlf:tx_wecmap_external',
	 	'label' => 'title',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'delete' => 'deleted',
		'iconfile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('wec_map').'Resources/Public/Images/icon_tx_wecmap_external_resource.gif',
	),
	'feInterface' => Array (
		'fe_admin_fieldList' => 'title, url',
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
				'wizards' => Array(
				        '_PADDING' => 2,
				        'link' => Array(
				                'type' => 'popup',
				                'title' => 'Link',
				                'icon' => 'link_popup.gif',
				                'script' => 'browse_links.php?mode=wizard',
				                'JSopenParams' => 'height=300,width=500,status=0,menubar=0,scrollbars=1',
								'params' => Array(
									'allowedExtensions' => 'kml, xml, kmz',
									'blindLinkOptions' => 'mail, page, spec'
								)
				        ),

				)
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