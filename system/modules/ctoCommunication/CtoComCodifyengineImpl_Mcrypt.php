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
 * SyncCtoCodifyengineImpl_Mcrypt
 */
class CtoComCodifyengineImpl_Mcrypt implements CtoComCodifyengineAbstract
{

    protected static $instance = null;
    protected $strKey = "";
    protected $strName = "Mcypt";

    /**
     * Constructor
     */
    public function __construct()
    {
        
    }

    /**
     * Singelton Pattern
     * 
     * @return CtoComCodifyengineImpl_Mcrypt 
     */
    public static function getInstance()
    {
        if (self::$instance == null)
            self::$instance = new CtoComCodifyengineImpl_Mcrypt();

        return self::$instance;
    }

    /* -------------------------------------------------------------------------
     * getter / setter / clear
     */

    public function setKey($strKey)
    {
        $this->strKey = $strKey;
    }

    /* -------------------------------------------------------------------------
     * Functions
     */

    // Verschluesseln
    public function Encrypt($text)
    {
        /* Open the cipher */
        $td = mcrypt_module_open('rijndael-256', '', 'ofb', '');

        /* Create the IV and determine the keysize length, use MCRYPT_RAND
         * on Windows instead */
        $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_DEV_RANDOM);
        $ks = mcrypt_enc_get_key_size($td);

        /* Create key */
        $key = substr(md5($this->strKey), 0, $ks);

        /* Intialize encryption */
        mcrypt_generic_init($td, $key, $iv);

        /* Encrypt data */
        $encrypted = mcrypt_generic($td, $text);

        /* Terminate encryption handler */
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);

        /* Return string */
        return trim($encrypted);
    }

    // Decrypt
    public function Decrypt($text)
    {
        /* Open the cipher */
        $td = mcrypt_module_open('rijndael-256', '', 'ofb', '');

        /* Create the IV and determine the keysize length, use MCRYPT_RAND
         * on Windows instead */
        $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_DEV_RANDOM);
        $ks = mcrypt_enc_get_key_size($td);

        /* Create key */
        $key = substr(md5($this->strKey), 0, $ks);

        /* Initialize encryption module for decryption */
        mcrypt_generic_init($td, $key, $iv);

        /* Decrypt encrypted string */
        $decrypted = mdecrypt_generic($td, $text);

        /* Terminate decryption handle and close module */
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);

        /* Return string */
        return trim($decrypted);
    }

}

?>