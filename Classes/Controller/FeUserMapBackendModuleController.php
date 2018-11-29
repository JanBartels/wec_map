<?php
namespace JBartels\WecMap\Controller;


/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2018 Jan Bartels
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
	}

	/**
	 * action settings
	 *
	 * @return void
	 */
	public function settingsAction() {
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