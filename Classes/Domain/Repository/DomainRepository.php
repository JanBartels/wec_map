<?php
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