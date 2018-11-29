<?php
namespace JBartels\WecMap\Domain\Model;

/**
 * Domain model
 */
class Domain extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{
    /**
     * domainName
     *
     * @var string
     */
    protected $domainName = '';

    /**
     * Returns the domain
     *
     * @return string
     */
    public function getDomainName()
    {
        return $this->domainName;
    }

    /**
     * Sets the domain
     *
     * @param string $domainName
     * @return void
     */
    public function setDomainName($domainName)
    {
        $this->domainName = $domainName;
    }


    /**
     * browserKey
     *
     * @var string
     */
    protected $browserKey = '';

    /**
     * Returns the browserKey
     *
     * @return string $browserKey
     */
    public function getBrowserKey()
    {
        return $this->browserKey;
    }

    /**
     * Sets the browserKey
     *
     * @param string $browserKey
     * @return void
     */
    public function setBrowserKey($browserKey)
    {
        $this->browserKey = $browserKey;
    }


    /**
     * staticKey
     *
     * @var string
     */
    protected $staticKey = '';

    /**
     * Returns the staticKey
     *
     * @return string $staticKey
     */
    public function getStaticKey()
    {
        return $this->staticKey;
    }

    /**
     * Sets the staticKey
     *
     * @param string $staticKey
     * @return void
     */
    public function setStaticKey($staticKey)
    {
        $this->staticKey = $staticKey;
    }


    /**
     * serverKey
     *
     * @var string
     */
    protected $serverKey = '';

    /**
     * Returns the serverKey
     *
     * @return string $serverKey
     */
    public function getServerKey()
    {
        return $this->serverKey;
    }

    /**
     * Sets the serverKey
     *
     * @param string $serverKey
     * @return void
     */
    public function setServerKey($serverKey)
    {
        $this->serverKey = $serverKey;
    }

}
