<?php
$extensionClassesPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('wec_map');

$default = array(
        'tx_wecmap_shared'  => $extensionClassesPath . 'class.tx_wecmap_shared.php',
        'tx_wecmap_backend'  => $extensionClassesPath . 'class.tx_wecmap_backend.php',
        'tx_wecmap_batchgeocode'  => $extensionClassesPath . 'class.tx_wecmap_batchgeocode.php',
        'tx_wecmap_cache'  => $extensionClassesPath . 'class.tx_wecmap_cache.php',
        'tx_wecmap_domainmgr'  => $extensionClassesPath . 'class.tx_wecmap_domainmgr.php',
        'tx_wecmap_map'  => $extensionClassesPath . 'class.tx_wecmap_map.php',
        'tx_wecmap_marker'  => $extensionClassesPath . 'class.tx_wecmap_marker.php',
        'tx_wecmap_markergroup'  => $extensionClassesPath . 'class.tx_wecmap_markergroup.php',

        'tx_wecmap_recordhandler'  => $extensionClassesPath . 'mod1/class.tx_wecmap_recordhandler.php',

        'tx_wecmap_map_google'  => $extensionClassesPath . 'map_service/google/class.tx_wecmap_map_google.php',
        'tx_wecmap_marker_google'  => $extensionClassesPath . 'map_service/google/class.tx_wecmap_marker_google.php',

        'tx_wecmap_module1' => $extensionClassesPath . 'mod1/index.php',
        'tx_wecmap_module1_ajax' => $extensionClassesPath . 'mod1/ajax.php',
);
return $default;
?>