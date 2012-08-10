<?php if (!defined('TL_ROOT')) die('You cannot access this file directly!');

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
 * @copyright  MEN AT WORK 2012
 * @package    syncCto
 * @license    GNU/LGPL
 * @filesource
 */
// Workaround for missing posix_getpwuid function
if (!function_exists('posix_getpwuid'))
{

    function posix_getpwuid($int)
    {
        return array('name' => $int);
    }

}

/**
 * Class for systemcheck
 */
class SyncCtoModuleCheck extends BackendModule
{

    /**
     * Template variables
     */
    protected $strTemplate = 'be_syncCto_check';

    /**
     * Initialize variables
     */
    protected $soap              = false;
    protected $safeModeHack      = false;
    protected $isWindows         = false;
    protected $folderPermissions = array();
    protected $filePermissions = array();

    /**
     * Constructor
     * 
     * @param DataContainer $objDc 
     */
    public function __construct(DataContainer $objDCA = null)
    {
        parent::__construct($objDCA);
        $this->loadLanguageFile('tl_syncCto_check');
    }

    protected function compile()
    {
        $this->import('BackendUser', 'User');
        $this->Template->script = $this->Environment->script;

        $this->Template->checkPhpConfiguration = $this->checkPhpConfiguration($this->getPhpConfigurations());
        $this->Template->checkPhpFunctions     = $this->checkPhpFunctions($this->getPhpFunctions());
        $this->Template->syc_version = $GLOBALS['SYC_VERSION'];
    }

    /**
     * Get a list with informations about some php vars
     * 
     * @return array
     */
    public function getPhpConfigurations()
    {
        return array(
            'safe_mode'              => ini_get('safe_mode'),
            'max_execution_time'     => ini_get('max_execution_time'),
            'memory_limit'           => $this->getSize(ini_get('memory_limit')),
            'register_globals'       => ini_get('register_globals'),
            'file_uploads'           => ini_get('file_uploads'),
            'upload_max_filesize'    => $this->getSize(ini_get('upload_max_filesize')),
            'post_max_size'          => $this->getSize(ini_get('post_max_size')),
            'max_input_time'         => ini_get('max_input_time'),
            'default_socket_timeout' => ini_get('default_socket_timeout'),
            'suhosin'                => ini_get('suhosin.session.max_id_length')
        );
    }

    /**
     * Get a list with informations about the required functions
     * 
     * @return array
     */
    public function getPhpFunctions()
    {
        return array(
            'fsockopen'   => function_exists("fsockopen"),
            'zip_archive' => @class_exists('ZipArchive'),
            'bcmath'      => function_exists('bcadd'),
            'xmlwriter'   => @class_exists('XMLWriter'),
            'xmlreader'   => @class_exists('XMLReader')
        );
    }
    
    private function getSize($strValue)
    {
        return (int) str_replace(array("M", "G"), array("000000", "000000000"), $strValue);
    }

    /**
     * Return true if the Safe Mode Hack is required
     * 
     * @return boolean
     */
    public function requiresSafeModeHack()
    {
        return $this->safeModeHack;
    }

    /**
     * Check all PHP extensions and return the result as string
     * 
     * @return string
     */
    public function checkPhpConfiguration($arrConfigurations)
    {
        $return = '<table width="100%" cellspacing="0" cellpadding="0" class="extensions" summary="">';
        $return .= '<colgroup>';
        $return .= '<col width="25%" />';
        $return .= '<col width="5%" />';
        $return .= '<col width="15%" />';
        $return .= '<col width="*" />';
        $return .= '</colgroup>';
        $return .= '<tr>';
        $return .= '<th>' . $GLOBALS['TL_LANG']['tl_syncCto_check']['parameter'] . '</th>';
        $return .= '<th class="dot" style="width:1%;">&#149;</th>';
        $return .= '<th>' . $GLOBALS['TL_LANG']['tl_syncCto_check']['value'] . '</th>';
        $return .= '<th>' . $GLOBALS['TL_LANG']['tl_syncCto_check']['description'] . '</th>';
        $return .= '</tr>';

        // Safe mode
        $safe_mode = $arrConfigurations['safe_mode'];
        $ok        = ($safe_mode == '' || $safe_mode == 0 || $safe_mode == 'Off');
        $return .= '<tr class="' . ($ok ? 'ok' : 'warning') . '">';
        $return .= '<td>' . $GLOBALS['TL_LANG']['tl_syncCto_check']['safemode'][0] . '</td>';
        $return .= '<td class="dot">' . ($ok ? '&nbsp;' : '&#149;') . '</td>';
        $return .= '<td class="value">' . ($safe_mode ? $GLOBALS['TL_LANG']['tl_syncCto_check']['on'] : $GLOBALS['TL_LANG']['tl_syncCto_check']['off']) . '</td>';
        $return .= '<td>' . $GLOBALS['TL_LANG']['tl_syncCto_check']['safemode'][1] . '</td>';
        $return .= '</tr>';

        if ($safe_mode)
        {
            $this->safeModeHack = true;
        }

        // Maximum execution time
        $max_execution_time = $arrConfigurations['max_execution_time'];
        $ok                 = ($max_execution_time >= 30 || $max_execution_time == 0);
        $return .= '<tr class="' . ($ok ? 'ok' : 'warning') . '">';
        $return .= '<td>' . $GLOBALS['TL_LANG']['tl_syncCto_check']['met'][0] . '</td>';
        $return .= '<td class="dot">' . ($ok ? '&nbsp;' : '&#149;') . '</td>';
        $return .= '<td class="value">' . $max_execution_time . '</td>';
        $return .= '<td>' . $GLOBALS['TL_LANG']['tl_syncCto_check']['met'][1] . '</td>';
        $return .= '</tr>';

        // Memory limit
        $memory_limit = $arrConfigurations['memory_limit'];
        $ok           = (intval($memory_limit) >= 128000000);
        $return .= '<tr class="' . ($ok ? 'ok' : 'warning') . '">';
        $return .= '<td>' . $GLOBALS['TL_LANG']['tl_syncCto_check']['memory_limit'][0] . '</td>';
        $return .= '<td class="dot">' . ($ok ? '&nbsp;' : '&#149;') . '</td>';
        $return .= '<td class="value">' . $this->getReadableSize($memory_limit) . '</td>';
        $return .= '<td>' . $GLOBALS['TL_LANG']['tl_syncCto_check']['memory_limit'][1] . '</td>';
        $return .= '</tr>';

        // Register globals
        $register_globals = $arrConfigurations['register_globals'];
        $ok               = ($register_globals == '' || $register_globals == 0 || $register_globals == 'Off');
        $return .= '<tr class="' . ($ok ? 'ok' : 'warning') . '">';
        $return .= '<td>' . $GLOBALS['TL_LANG']['tl_syncCto_check']['register_globals'][0] . '</td>';
        $return .= '<td class="dot">' . ($ok ? '&nbsp;' : '&#149;') . '</td>';
        $return .= '<td class="value">' . ($register_globals ? $GLOBALS['TL_LANG']['tl_syncCto_check']['on'] : $GLOBALS['TL_LANG']['tl_syncCto_check']['off']) . '</td>';
        $return .= '<td>' . $GLOBALS['TL_LANG']['tl_syncCto_check']['register_globals'][1] . '</td>';
        $return .= '</tr>';

        // File uploads
        $file_uploads = $arrConfigurations['file_uploads'];
        $ok           = ($file_uploads == 1 || $file_uploads == 'On');
        $return .= '<tr class="' . ($ok ? 'ok' : 'warning') . '">';
        $return .= '<td>' . $GLOBALS['TL_LANG']['tl_syncCto_check']['file_uploads'][0] . '</td>';
        $return .= '<td class="dot">' . ($ok ? '&nbsp;' : '&#149;') . '</td>';
        $return .= '<td class="value">' . ($file_uploads ? $GLOBALS['TL_LANG']['tl_syncCto_check']['on'] : $GLOBALS['TL_LANG']['tl_syncCto_check']['off']) . '</td>';
        $return .= '<td>' . $GLOBALS['TL_LANG']['tl_syncCto_check']['file_uploads'][1] . '</td>';
        $return .= '</tr>';

        // Upload maximum filesize
        $upload_max_filesize = $arrConfigurations['upload_max_filesize'];
        $ok                  = (intval($upload_max_filesize) >= 8000000);
        $return .= '<tr class="' . ($ok ? 'ok' : 'warning') . '">';
        $return .= '<td>' . $GLOBALS['TL_LANG']['tl_syncCto_check']['umf'][0] . '</td>';
        $return .= '<td class="dot">' . ($ok ? '&nbsp;' : '&#149;') . '</td>';
        $return .= '<td class="value">' . $this->getReadableSize($upload_max_filesize) . '</td>';
        $return .= '<td>' . $GLOBALS['TL_LANG']['tl_syncCto_check']['umf'][1] . '</td>';
        $return .= '</tr>';

        // Post maximum size
        $post_max_size = $arrConfigurations['post_max_size'];
        $ok            = (intval($post_max_size) >= 8000000);
        $return .= '<tr class="' . ($ok ? 'ok' : 'warning') . '">';
        $return .= '<td>' . $GLOBALS['TL_LANG']['tl_syncCto_check']['pms'][0] . '</td>';
        $return .= '<td class="dot">' . ($ok ? '&nbsp;' : '&#149;') . '</td>';
        $return .= '<td class="value">' . $this->getReadableSize($post_max_size) . '</td>';
        $return .= '<td>' . $GLOBALS['TL_LANG']['tl_syncCto_check']['pms'][1] . '</td>';
        $return .= '</tr>';

        // Maximum input time
        $max_input_time = $arrConfigurations['max_input_time'];
        $ok             = ($max_input_time == '-1' || (intval($max_input_time) >= 60));
        $return .= '<tr class="' . ($ok ? 'ok' : 'warning') . '">';
        $return .= '<td>' . $GLOBALS['TL_LANG']['tl_syncCto_check']['mit'][0] . '</td>';
        $return .= '<td class="dot">' . ($ok ? '&nbsp;' : '&#149;') . '</td>';
        $return .= '<td class="value">' . $max_input_time . '</td>';
        $return .= '<td>' . $GLOBALS['TL_LANG']['tl_syncCto_check']['mit'][1] . '</td>';
        $return .= '</tr>';

        // Default socket timeout
        $default_socket_timeout = $arrConfigurations['default_socket_timeout'];
        $ok                     = (intval($default_socket_timeout) >= 32);
        $return .= '<tr class="' . ($ok ? 'ok' : 'warning') . '">';
        $return .= '<td>' . $GLOBALS['TL_LANG']['tl_syncCto_check']['dst'][0] . '</td>';
        $return .= '<td class="dot">' . ($ok ? '&nbsp;' : '&#149;') . '</td>';
        $return .= '<td class="value">' . $default_socket_timeout . '</td>';
        $return .= '<td>' . $GLOBALS['TL_LANG']['tl_syncCto_check']['dst'][1] . '</td>';
        $return .= '</tr>';

        // suhosin
        $suhosin = $arrConfigurations['$suhosin'];
        $ok      = ($suhosin == false);
        $return .= '<tr class="' . ($ok ? 'ok' : 'warning') . '">';
        $return .= '<td>' . $GLOBALS['TL_LANG']['tl_syncCto_check']['suhosin'][0] . '</td>';
        $return .= '<td class="dot">' . ($ok ? '&nbsp;' : '&#149;') . '</td>';
        $return .= '<td class="value">' . ($suhosin ? $GLOBALS['TL_LANG']['tl_syncCto_check']['on'] : $GLOBALS['TL_LANG']['tl_syncCto_check']['off']) . '</td>';
        $return .= '<td>' . $GLOBALS['TL_LANG']['tl_syncCto_check']['suhosin'][1] . '</td>';
        $return .= '</tr>';

        $return .= '</table>';

        return $return;
    }

    /**
     * Check all PHP function/class and return the result as string
     * 
     * @return string
     */
    public function checkPhpFunctions($arrFunctions)
    {        
        $return = '<table width="100%" cellspacing="0" cellpadding="0" class="extensions" summary="">';
        $return .= '<colgroup>';
        $return .= '<col width="25%" />';
        $return .= '<col width="5%" />';
        $return .= '<col width="15%" />';
        $return .= '<col width="*" />';
        $return .= '</colgroup>';
        $return .= '<tr>';
        $return .= '<th>' . $GLOBALS['TL_LANG']['tl_syncCto_check']['parameter'] . '</th>';
        $return .= '<th class="dot" style="width:1%;">&#149;</th>';
        $return .= '<th>' . $GLOBALS['TL_LANG']['tl_syncCto_check']['value'] . '</th>';
        $return .= '<th>' . $GLOBALS['TL_LANG']['tl_syncCto_check']['description'] . '</th>';
        $return .= '</tr>';

        // fsockopen
        $fsockopen = $arrFunctions['fsockopen'];
        $ok        = ($fsockopen == true);
        $return .= '<tr class="' . ($ok ? 'ok' : 'warning') . '">';
        $return .= '<td>' . $GLOBALS['TL_LANG']['tl_syncCto_check']['fsocket'][0] . '</td>';
        $return .= '<td class="dot">' . ($ok ? '&nbsp;' : '&#149;') . '</td>';
        $return .= '<td class="value">' . ($fsockopen ? $GLOBALS['TL_LANG']['tl_syncCto_check']['on'] : $GLOBALS['TL_LANG']['tl_syncCto_check']['off']) . '</td>';
        $return .= '<td>' . $GLOBALS['TL_LANG']['tl_syncCto_check']['fsocket'][1] . '</td>';
        $return .= '</tr>';

        // ZipArchive
        $zip_archive = $arrFunctions['zip_archive'];
        $ok          = ($zip_archive == true);
        $return .= '<tr class="' . ($ok ? 'ok' : 'warning') . '">';
        $return .= '<td>' . $GLOBALS['TL_LANG']['tl_syncCto_check']['zip_archive'][0] . '</td>';
        $return .= '<td class="dot">' . ($ok ? '&nbsp;' : '&#149;') . '</td>';
        $return .= '<td class="value">' . ($zip_archive ? $GLOBALS['TL_LANG']['tl_syncCto_check']['on'] : $GLOBALS['TL_LANG']['tl_syncCto_check']['off']) . '</td>';
        $return .= '<td>' . $GLOBALS['TL_LANG']['tl_syncCto_check']['zip_archive'][1] . '</td>';
        $return .= '</tr>';

        // bcmath
        $bcmath = $arrFunctions['bcmath'];
        $ok     = ($bcmath == true);
        $return .= '<tr class="' . ($ok ? 'ok' : 'warning') . '">';
        $return .= '<td>' . $GLOBALS['TL_LANG']['tl_syncCto_check']['bcmath'][0] . '</td>';
        $return .= '<td class="dot">' . ($ok ? '&nbsp;' : '&#149;') . '</td>';
        $return .= '<td class="value">' . ($bcmath ? $GLOBALS['TL_LANG']['tl_syncCto_check']['on'] : $GLOBALS['TL_LANG']['tl_syncCto_check']['off']) . '</td>';
        $return .= '<td>' . $GLOBALS['TL_LANG']['tl_syncCto_check']['bcmath'][1] . '</td>';
        $return .= '</tr>';

        // XMLWriter
        $xmlwriter = $arrFunctions['xmlwriter'];
        $ok        = ($xmlwriter == true);
        $return .= '<tr class="' . ($ok ? 'ok' : 'warning') . '">';
        $return .= '<td>' . $GLOBALS['TL_LANG']['tl_syncCto_check']['xmlwriter'][0] . '</td>';
        $return .= '<td class="dot">' . ($ok ? '&nbsp;' : '&#149;') . '</td>';
        $return .= '<td class="value">' . ($xmlwriter ? $GLOBALS['TL_LANG']['tl_syncCto_check']['on'] : $GLOBALS['TL_LANG']['tl_syncCto_check']['off']) . '</td>';
        $return .= '<td>' . $GLOBALS['TL_LANG']['tl_syncCto_check']['xmlwriter'][1] . '</td>';
        $return .= '</tr>';

        // XMLReader
        $xmlreader = $arrFunctions['xmlreader'];
        $ok        = ($xmlreader == true);
        $return .= '<tr class="' . ($ok ? 'ok' : 'warning') . '">';
        $return .= '<td>' . $GLOBALS['TL_LANG']['tl_syncCto_check']['xmlreader'][0] . '</td>';
        $return .= '<td class="dot">' . ($ok ? '&nbsp;' : '&#149;') . '</td>';
        $return .= '<td class="value">' . ($xmlreader ? $GLOBALS['TL_LANG']['tl_syncCto_check']['on'] : $GLOBALS['TL_LANG']['tl_syncCto_check']['off']) . '</td>';
        $return .= '<td>' . $GLOBALS['TL_LANG']['tl_syncCto_check']['xmlreader'][1] . '</td>';
        $return .= '</tr>';

        $return .= '</table>';

        return $return;
    }

}

?>