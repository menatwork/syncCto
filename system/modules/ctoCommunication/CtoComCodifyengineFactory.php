<?php

if (!defined('TL_ROOT'))
    die('You cannot access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  MEN AT WORK 2011
 * @package    syncCto
 * @license    GNU/LGPL
 * @filesource
 */

/**
 * Factory for create the codifyengine
 */
class CtoComCodifyengineFactory extends Backend
{

    /**
     * Create the codifyengine.
     * 
     * @return CtoComCodifyengineAbstract 
     */
    public static function getEngine($strEngine = null)
    {
        if ($strEngine == null || $strEngine == "Blowfish")
        {
            $enginge = CtoComCodifyengineImpl_Blowfish::getInstance();
        }
        else
        {
            if (!file_exists(TL_ROOT . "/system/modules/ctoCommunication/CtoComCodifyengineImpl_" . $strEngine . ".php"))
                throw new Exception("Unknown codifyengine: " . $strEngine);

            $strClass = "CtoComCodifyengineImpl_" . $strEngine;
            $enginge = $strClass::getInstance();
        }

        if ($enginge instanceof CtoComCodifyengineAbstract)
        {
            return $enginge;
        }
        else
        {
            throw new Exception("Codifyenginge is not instance of CtoComCodifyengineAbstract.");
        }
    }

}

?>