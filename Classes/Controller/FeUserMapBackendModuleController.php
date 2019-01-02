<?php
namespace JBartels\WecMap\Controller;


/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2019 Jan Bartels
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Backend Controller
 */
class FeUserMapBackendModuleController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

    /** @var int */
    protected $id = 0;

    /**
     * @var array
     */
    protected $pageinfo;

    /**
     * @var string
     */
    protected $perms_clause;

    /**
     *
     */
    public function initializeAction()
    {
        parent::initializeAction();

        $this->id = (int)\TYPO3\CMS\Core\Utility\GeneralUtility::_GET('id');
        $this->perms_clause = $this->getBackendUser()->getPagePermsClause( 1 );
        $this->pageinfo = \TYPO3\CMS\Backend\Utility\BackendUtility::readPageAccess( $this->id, $this->perms_clause );

        // check access and redirect accordingly
        $access = is_array($this->pageinfo) ? 1 : 0;

        if ( $this->id > 0 && ( $access || $this->getBackendUser()->user['admin'] ) ) {
            //proceed normally
        } else {
            if ($this->request->getControllerActionName() !== 'alert') {
                $this->redirect('alert', $this->request->getControllerName());
            }
        }
    }

	/**
	 * action show
	 *
	 * @return void
	 */
	public function showAction() {
        $languageService = $this->getLanguageService();

		$streetField  = \JBartels\WecMap\Utility\Shared::getAddressField('fe_users', 'street');
		$cityField    = \JBartels\WecMap\Utility\Shared::getAddressField('fe_users', 'city');
		$stateField   = \JBartels\WecMap\Utility\Shared::getAddressField('fe_users', 'state');
		$zipField     = \JBartels\WecMap\Utility\Shared::getAddressField('fe_users', 'zip');
		$countryField = \JBartels\WecMap\Utility\Shared::getAddressField('fe_users', 'country');

		// create country and zip code array to keep track of which country and state we already added to the map.
		// the point is to create only one marker per country on a higher zoom level to not
		// overload the map with all the markers and do the same with zip codes.
		$countries = array();
        $cities = array();
        $markers = array();

		/* Select all frontend users */
		$queryBuilder = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance( \TYPO3\CMS\Core\Database\ConnectionPool::class )
            ->getQueryBuilderForTable('fe_users');
        $statement = $queryBuilder
            ->select('*')
            ->from('fe_users')
            ->where(
                $queryBuilder->expr()->eq( 'pid', $queryBuilder->createNamedParameter( $this->id ) )
            )
            ->execute();
        while ( $row = $statement->fetch() ) {
			// add check for country and use different field if empty
			// @TODO: make this smarter with TCA or something
			if(empty($row[$countryField]) && $countryField == 'static_info_country') {
				$countryField = 'country';
			} else if(empty($row[$countryField]) && $countryField == 'country') {
				$countryField = 'static_info_country';
			}

			/* Only try to add marker if there's a city */
			if($row[$cityField] != '') {

				// if we haven't added a marker for this country yet, do so.
				if(!in_array($row[$countryField], $countries) && !empty($row[$countryField])) {

					// add this country to the array
					$countries[] = $row[$countryField];

					// add a little info so users know what to do
					$title = '<div class="title">' . $languageService->sL('LLL:EXT:wec_map/Resources/Private/Languages/Backend/FEUserMap/locallang.xlf:country_zoominfo_title') . '</div>';
					$description = '<div class="description">'.sprintf( $languageService->sL('LLL:EXT:wec_map/Resources/Private/Languages/Backend/FEUserMap/locallang.xlf:country_zoominfo_desc'), $row[$countryField]).'</div>';

					// add a marker for this country and only show it between zoom levels 0 and 2.
                    $markers[] = array( 
                        city => $row[$cityField],
                        state => $row[$stateField],
                        zip => $row[$zipField],
                        country => $row[$countryField], 
                        title => $title, 
                        description => $description, 
                        minzoom => 0, 
                        maxzoom => 2
                    );
				}


				// if we haven't added a marker for this zip code yet, do so.
				if(!in_array($row[$cityField], $cities) && !empty($cityField)) {

					// add this country to the array
					$cities[] = $row[$cityField];

					// add a little info so users know what to do
					$title = '<div class="title">' . $languageService->sL('LLL:EXT:wec_map/Resources/Private/Languages/Backend/FEUserMap/locallang.xlf:area_zoominfo_title') . '</div>';
					$description = '<div class="description">'. $languageService->sL('LLL:EXT:wec_map/Resources/Private/Languages/Backend/FEUserMap/locallang.xlf:area_zoominfo_desc').'</div>';

					// add a marker for this country and only show it between zoom levels 3 and 7.
                    $markers[] = array(
                        city => $row[$cityField],
                        state => $row[$stateField],
                        zip => $row[$zipField],
                        country => $row[$countryField], 
                        title => $title, 
                        description => $description, 
                        minzoom => 3, 
                        maxzoom => 7
                    );
				}

				// make title and description
				$title = '<div style="font-size: 110%; font-weight: bold;">'.$row['name'].'</div>';
				$content = '<div>'.$row[$streetField].'<br />'.$row[$cityField].', '.$row[$stateField].' '.$row[$zipField].'<br />'. $row[$countryField].'</div>';


				// add all the markers starting at zoom level 3 so we don't crowd the map right away.
				// if private was checked, don't use address to geocode
				if($private) {
                    $markers[] = array(
                        city => $row[$cityField],
                        state => $row[$stateField],
                        zip => $row[$zipField],
                        country => $row[$countryField], 
                        title => $title, 
                        description => $content, 
                        minzoom => 8 
                    );
				} else {
                    $markers[] = array(
                        street => $row[$streetField],
                        city => $row[$cityField],
                        state => $row[$stateField],
                        zip => $row[$zipField],
                        country => $row[$countryField], 
                        title => $title, 
                        description => $content, 
                        minzoom => 8 
                    );
				}
			}
        }
        $this->view->assign( 'markersByAddress', $markers );
	}

	/**
	 * action alert
	 *
	 * @return void
	 */
	public function alertAction() {
	}


    protected function getPageTitle( $pid )
    {
		$pageSelect = $this->objectManager->get('TYPO3\CMS\Frontend\Page\PageRepository');
		$pageSelect->init(false);
		$row = $pageSelect->getPage( $pid );
 		if (!empty($row)) {
			return $row['title'];
		}
		return '';
    }


    /**
     * Creates te URI for a backend action
     *
     * @param string $controller
     * @param string $action
     * @param array $parameters
     * @return string
     */
    protected function getHref($controller, $action, $parameters = [])
    {
        $uriBuilder = $this->objectManager->get(\TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder::class);
        $uriBuilder->setRequest($this->request);
        return $uriBuilder->reset()->uriFor($action, $parameters, $controller);
    }

    /**
     * Returns the Backend User
     * @return BackendUserAuthentication
     */
    protected function getBackendUser()
    {
        return $GLOBALS['BE_USER'];
    }

    /**
     * @return LanguageService
     */
    protected function getLanguageService()
    {
        return $GLOBALS['LANG'];
    }

}