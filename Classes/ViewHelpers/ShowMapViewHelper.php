<?php
namespace JBartels\WecMap\ViewHelpers;

class ShowMapViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
    * Don't escape output!
    *
    * @var bool
	*/
	protected $escapeOutput = false;
	protected $escapeChildren = false;

	protected function getUid( $mapName ) {
		if (TYPO3_MODE == 'FE') {
			/** @var $TypoScriptFrontendController TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController */
			$TypoScriptFrontendController = $GLOBALS['TSFE'];
			return $TypoScriptFrontendController->getUniqueId( $mapName );
		} else {
			return $mapName;
		}
	}

	/**
	* Arguments Initialization
	*/
	public function initializeArguments() {
		$this->registerArgument('width', 'int', 'width of the map', FALSE, 250);
		$this->registerArgument('height', 'int', 'height of the map', FALSE, 250);
		$this->registerArgument('zoom', 'int', 'zoom level', FALSE, 14);
		$this->registerArgument('mapName', 'string', 'name of the map', FALSE, NULL );
		$this->registerArgument('type', 'string', 'type of the map', FALSE, 'G_NORMAL_MAP' );
		$this->registerArgument('kmlFiles', 'array', 'array of KML-files', FALSE, NULL );
		$this->registerArgument('markersByAddress', 'array', 'array of markers', FALSE, NULL );
		$this->registerArgument('controls', 'array', 'array of control-options', FALSE, NULL );
	}

	/**
	* Zeigt eine Karte an
	*
	* @return string Rendered map
	*/
	public function render() {
		$mapName = 'map_' . $this->getUid( $this->arguments['mapName'] );

		$map = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('JBartels\\WecMap\\MapService\\Google\\Map',
			'', $this->arguments['width'], $this->arguments['height'], '', '', $this->arguments['zoom'], $mapName);

		$controls = $this->arguments['controls'];
		if ( is_array( $controls ) ) {
			if ( $controls['scale'] )
				$map->addControl('scale');
			if ( $controls['mapType'] )
				$map->addControl('mapType');
			if ( $controls['zoom'] )
				$map->addControl('zoom');
		}
		$map->setType( $this->arguments['type'] );

		$kmlFiles = $this->arguments['kmlFiles'];
		if ( is_array( $kmlFiles ) ) {
			foreach( $kmlFiles as $kmlFile ) {
				$map->addKML( $kmlFile );
			}
		}

		$markers = $this->arguments['markersByAddress'];
		if ( is_array( $markers ) ) {
			foreach( $markers as $marker ) {
				$map->addMarkerByAddress( $marker['street'] ?: '', $marker['city'] ?: '', $marker['state'] ?: '', 
				                          $marker['zip'] ?: '', $marker['country'] ?: '', 
										  $marker['title'] ?: '', $marker['description'] ?: '', 
										  $marker['minzoom'] ?: 0, $marker['maxzoom'] ?: 18, $marker['iconID'] ?: ''
										);
			}
		}

		return $map->drawMap();
	}
}
