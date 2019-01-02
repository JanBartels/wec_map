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
