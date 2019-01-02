<?php
/***************************************************************
* Copyright notice
*
* (c) 2018-2019 j.bartels
* All rights reserved
*
* You can redistribute this file and/or modify it under the terms of the
* GNU General Public License as published by the Free Software Foundation;
* either version 2 of the License, or (at your option) any later version.
*
* The GNU General Public License can be found at
* http://www.gnu.org/copyleft/gpl.html.
*
* This file is distributed in the hope that it will be useful for ministry,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* This copyright notice MUST APPEAR in all copies of the file!
***************************************************************/

namespace JBartels\WecMap\Domain\Repository;

class DomainRepository extends \TYPO3\CMS\Extbase\Persistence\Repository {

    /**
     * @return void
     */
    public function initializeObject()
    {
        /** @var \TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings $defaultQuerySettings */
        $defaultQuerySettings = $this->objectManager->get( \TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings::class );
        $defaultQuerySettings->setRespectStoragePage( false );
        $this->setDefaultQuerySettings( $defaultQuerySettings );
    }

    public function findAll()
    {
		$query = $this->createQuery();
        $query->getQuerySettings()->setIgnoreEnableFields( true )->setIncludeDeleted( false );
        $result = $query->execute();
        return $result;
    }

	/*
	** param string $domainName
	** @return JBartels\WecMap\Domain\Model\Domain
	**/
    public function findByDomain( $domainName )
    {
		$query = $this->createQuery();
		$query->matching( $query->equals( 'domainName', $domainName ) );
        $result = $query->execute();
        return $result;
    }
}