<?php
return array(
	// Flexforms (Backend): neccessary for cal
	'tx_wecmap_backend' => 'JBartels\\WecMap\\Utility\\Backend',

	// Google map service: neccessary for extensions using non-namespaced
	'tx_wecmap_map_google' => 'JBartels\\WecMap\\MapService\\Google\\Map',
	'tx_wecmap_marker_google' => 'JBartels\\WecMap\\MapService\\Google\\Marker',
);
