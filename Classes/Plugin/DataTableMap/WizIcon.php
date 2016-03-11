<?php
/***************************************************************
* Copyright notice
*
* (c) 2005-2009 Christian Technology Ministries International Inc.
* (c) 2010-2015 J. Bartels
* All rights reserved
*
* This file is part of the Web-Empowered Church (WEC)
* (http://WebEmpoweredChurch.org) ministry of Christian Technology Ministries
* International (http://CTMIinc.org). The WEC is developing TYPO3-based
* (http://typo3.org) free software for churches around the world. Our desire
* is to use the Internet to help offer new life through Jesus Christ. Please
* see http://WebEmpoweredChurch.org/Jesus.
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
/**
 * Class that adds the wizard icon.
 */

namespace JBartels\WecMap\Plugin\DataTableMap;

class WizIcon {
    public function proc($wizardItems)    {
        $wizardItems['plugins_tx_wecmap_pi3'] = array(
            'icon'=>\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('wec_map').'Resources/Public/Images/DataTableMapWizIcon.gif',
            'title'=>$GLOBALS['LANG']->sL('LLL:EXT:wec_map/Resources/Private/Languages/Plugin/DataTableMap/locallang.xlf:pi3_title'),
            'description'=>$GLOBALS['LANG']->sL('LLL:EXT:wec_map/Resources/Private/Languages/Plugin/DataTableMap/locallang.xlf:pi3_plus_wiz_description'),
            'params'=>'&defVals[tt_content][CType]=list&defVals[tt_content][list_type]=wec_map_pi3'
        );
        return $wizardItems;
    }
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/pi3/class.tx_wecmap_pi3_wizicon.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/pi3/class.tx_wecmap_pi3_wizicon.php']);
}

?>