<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "wec_map".
 *
 * Auto generated 19-07-2014 13:27
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array(
	'title' => 'WEC Map',
	'description' => 'Mapping extension that connects to geocoding databases and Google Maps API.',
	'category' => 'plugin',
	'shy' => 0,
	'version' => '3.0.5',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => 'bottom',
	'loadOrder' => '',
	'module' => 'mod1,mod2',
	'state' => 'stable',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearcacheonload' => 1,
	'lockType' => '',
	'author' => 'Web-Empowered Church Team (V1.x, V2.x), Jan Bartels (V3.x)',
	'author_email' => 'j.bartels@arcor.de',
	'author_company' => 'Christian Technology Ministries International Inc. (V1.x, V2.x)',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' => array(
		'depends' => array(
			'php' => '3.0.0-0.0.0',
			'typo3' => '4.3.0-4.7.99',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:68:{s:9:"CHANGELOG";s:4:"24e2";s:27:"class.tx_wecmap_backend.php";s:4:"ae09";s:32:"class.tx_wecmap_batchgeocode.php";s:4:"3825";s:25:"class.tx_wecmap_cache.php";s:4:"89d4";s:29:"class.tx_wecmap_domainmgr.php";s:4:"8bf6";s:23:"class.tx_wecmap_map.php";s:4:"0ce2";s:26:"class.tx_wecmap_marker.php";s:4:"ef39";s:31:"class.tx_wecmap_markergroup.php";s:4:"f8e3";s:26:"class.tx_wecmap_shared.php";s:4:"3e6a";s:21:"ext_conf_template.txt";s:4:"b074";s:12:"ext_icon.gif";s:4:"91f0";s:17:"ext_localconf.php";s:4:"a1ac";s:14:"ext_tables.php";s:4:"8d23";s:14:"ext_tables.sql";s:4:"a3f7";s:16:"locallang_db.xml";s:4:"14c0";s:7:"tca.php";s:4:"77b4";s:29:"contrib/tablesort/fastinit.js";s:4:"afbd";s:30:"contrib/tablesort/tablesort.js";s:4:"c6e0";s:24:"csh/locallang_csh_ff.xml";s:4:"cf9e";s:14:"doc/manual.sxw";s:4:"def8";s:50:"geocode_service/class.tx_wecmap_geocode_google.php";s:4:"e868";s:14:"images/aai.gif";s:4:"03ce";s:20:"images/icon_home.gif";s:4:"6e80";s:27:"images/icon_home_shadow.png";s:4:"ce1c";s:20:"images/mm_20_red.png";s:4:"453d";s:23:"images/mm_20_shadow.png";s:4:"f77b";s:49:"map_service/google/class.tx_wecmap_map_google.php";s:4:"0aec";s:52:"map_service/google/class.tx_wecmap_marker_google.php";s:4:"cd85";s:32:"map_service/google/locallang.xml";s:4:"a6a2";s:13:"mod1/ajax.php";s:4:"ad82";s:38:"mod1/class.tx_wecmap_recordhandler.php";s:4:"13f7";s:14:"mod1/clear.gif";s:4:"cc11";s:13:"mod1/conf.php";s:4:"b547";s:14:"mod1/index.php";s:4:"632b";s:18:"mod1/locallang.xml";s:4:"6517";s:22:"mod1/locallang_mod.xml";s:4:"cd7f";s:19:"mod1/moduleicon.gif";s:4:"1af1";s:14:"mod2/clear.gif";s:4:"cc11";s:13:"mod2/conf.php";s:4:"5da6";s:14:"mod2/index.php";s:4:"98b7";s:18:"mod2/locallang.xml";s:4:"62bf";s:22:"mod2/locallang_mod.xml";s:4:"acf5";s:19:"mod2/moduleicon.gif";s:4:"4fd7";s:14:"pi1/ce_wiz.gif";s:4:"fa31";s:27:"pi1/class.tx_wecmap_pi1.php";s:4:"f13b";s:35:"pi1/class.tx_wecmap_pi1_wizicon.php";s:4:"baa6";s:19:"pi1/flexform_ds.xml";s:4:"0824";s:17:"pi1/locallang.xml";s:4:"c370";s:20:"pi1/static/setup.txt";s:4:"7f68";s:14:"pi2/ce_wiz.gif";s:4:"4083";s:27:"pi2/class.tx_wecmap_pi2.php";s:4:"b7fe";s:35:"pi2/class.tx_wecmap_pi2_wizicon.php";s:4:"7f5e";s:19:"pi2/flexform_ds.xml";s:4:"2bac";s:17:"pi2/locallang.xml";s:4:"3978";s:20:"pi2/static/setup.txt";s:4:"6e2c";s:14:"pi3/ce_wiz.gif";s:4:"e7bd";s:27:"pi3/class.tx_wecmap_pi3.php";s:4:"4aaf";s:35:"pi3/class.tx_wecmap_pi3_wizicon.php";s:4:"2a73";s:19:"pi3/flexform_ds.xml";s:4:"0463";s:17:"pi3/locallang.xml";s:4:"ed1a";s:20:"pi3/static/setup.txt";s:4:"1fa5";s:17:"res/copyrights.js";s:4:"09e8";s:40:"res/icon_tx_wecmap_external_resource.gif";s:4:"daf0";s:13:"res/wecmap.js";s:4:"3979";s:21:"res/wecmap_backend.js";s:4:"dec6";s:16:"static/setup.txt";s:4:"7121";s:27:"tests/autozoom_testcase.php";s:4:"25d1";s:36:"tests/get_address_field_testcase.php";s:4:"5255";}',
	'suggests' => array(
	),
);

?>