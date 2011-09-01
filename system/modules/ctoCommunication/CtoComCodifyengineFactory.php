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
        if ($strEngine == "" || $strEngine == null)
            $strEngine = "Blowfish";

        if (!key_exists($strEngine, $GLOBALS["CTOCOM_ENGINE"]))
        {
            throw new Exception(vsprintf($GLOBALS['TL_LANG']['ERR']['unknown_engine'], array($strEngine)));
        }

        $arrEngine = $GLOBALS["CTOCOM_ENGINE"][$strEngine];

        if (!file_exists(TL_ROOT . "/" . $arrEngine["folder"] . "/" . $arrEngine["classname"] . ".php"))
            throw new Exception(vsprintf($GLOBALS['TL_LANG']['ERR']['missing_engine'], array($arrEngine["classname"] . ".php")));

        $strClass = $arrEngine["classname"];        
        $objEnginge = new $strClass();

        if ($objEnginge instanceof CtoComCodifyengineAbstract)
        {
            return $objEnginge;
        }
        else
        {
            throw new Exception($GLOBALS['TL_LANG']['ERR']['not_a_engine']);
        }
    }

}

?>