<?php

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2014
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

    public function generate()
    {
        return parent::generate();
    }

    protected function compile()
    {
        $this->import('BackendUser', 'User');
        $this->Template->script = $this->Environment->script;

        $this->Template->checkPhpConfiguration    = $this->checkPhpConfiguration($this->getPhpConfigurations());
        $this->Template->checkPhpFunctions        = $this->checkPhpFunctions($this->getPhpFunctions());
        $this->Template->checkProFunctions        = $this->checkProFunctions($this->getMySqlFunctions());
        $this->Template->extendedInformation      = $this->checkExtendedInformation($this->getExtendedInformation());
        $this->Template->syc_version              = $GLOBALS['SYC_VERSION'];
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
            'suhosin'                => $this->checkSuhosin()
        );
    }

    public function checkSuhosin()
    {
        $blnIsActive = false;

        // Check php ini
        if (ini_get('suhosin.session.max_id_length'))
        {
            $blnIsActive = true;
        }

        // Check patch
        if (defined("SUHOSIN_PATCH") && @constant("SUHOSIN_PATCH"))
        {
            $blnIsActive = true;
        }

        return $blnIsActive;
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
            'zip_archive' => class_exists('ZipArchive'),
            'gmp'         => extension_loaded('gmp'),
            'bcmath'      => extension_loaded('bcmath'),
            'xmlwriter'   => class_exists('XMLWriter'),
            'xmlreader'   => class_exists('XMLReader'),
            'mcrypt'      => extension_loaded('mcrypt'),
        );
    }

    /**
     * Run a little test for MySQL Trigger
     *
     * @return array
     */
    public function getMySqlFunctions()
    {
        $blnCreate = false;
        $blnDelete = false;

        $strErrorCreate = '';
        $strErrorDelete = '';

        try
        {
            // Create
            $strQuery = "
            CREATE TRIGGER `tl_synccto_clients_AfterUpdateTest` AFTER UPDATE ON tl_synccto_clients FOR EACH ROW
            BEGIN

            END
            ";
            $this->Database->query($strQuery);

            $blnCreate = true;

            try
            {
                // Drop
                $strQuery = "DROP TRIGGER IF EXISTS `tl_synccto_clients_AfterUpdateTest`";
                $this->Database->query($strQuery);

                $blnDelete = true;
            }
            catch (Exception $exc)
            {

                $strErrorDelete = $exc->getMessage();
            }
        }
        catch (Exception $exc)
        {

            $strErrorCreate = $exc->getMessage();
        }

        return array(
            'create'       => $blnCreate,
            'delete'       => $blnDelete,
            'error_create' => $strErrorCreate,
            'error_delete' => $strErrorDelete,
        );
    }

    public function getExtendedInformation($strTimeFormate = null)
    {
        // Set the time formate.
        if(empty($strTimeFormate))
        {
            $strTimeFormate = $GLOBALS['TL_CONFIG']['datimFormat'];
        }

        $arrReturn = array();

        // date_default_timezone_get
        if (date_default_timezone_get())
        {
            $arrReturn['date_default_timezone'] =  date_default_timezone_get();
        }
        else
        {
            $arrReturn['date_default_timezone'] = '';
        }

        // date.timezone
        if (ini_get('date.timezone'))
        {
            $arrReturn['date_ini_timezone'] =  ini_get('date.timezone');
        }
        else
        {
            $arrReturn['date_ini_timezone'] = '';
        }

        // $_SERVER[$value]
        $arrServerInfo = array(
            'server_software' => 'SERVER_SOFTWARE',
        );

        foreach ($arrServerInfo as $strKey => $strValue)
        {
            $arrReturn[$strKey] = $_SERVER[$strValue];
        }

        // Time
        $intCurrentTime                       = time();
        $arrReturn['current_time']['time']    = $intCurrentTime;
        $arrReturn['current_time']['formate'] = date($strTimeFormate, $intCurrentTime);
        $arrReturn['current_time']['day']     = date('d', $intCurrentTime);
        $arrReturn['current_time']['month']   = date('m', $intCurrentTime);
        $arrReturn['current_time']['year']    = date('Y', $intCurrentTime);
        $arrReturn['current_time']['houre']   = date('H', $intCurrentTime);
        $arrReturn['current_time']['minute']  = date('i', $intCurrentTime);
        $arrReturn['current_time']['second']  = date('s', $intCurrentTime);

        // PHP
        $arrReturn['php_version'] = phpversion();

        return $arrReturn;
    }

    /**
     * Replace the m/g/ etc. from php ini
     *
     * @param mix $strValue
     * @return int
     */
    private function getSize($strValue)
    {
        return SyncCtoModuleClient::parseSize($strValue);
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
        $return .= '<col width="215" />';
        $return .= '<col width="35" />';
        $return .= '<col width="90" />';
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
        $return .= '<td>' . $GLOBALS['TL_LANG']['tl_syncCto_check']['safemode'] . '</td>';
        $return .= '<td class="dot">' . ($ok ? '&nbsp;' : '&#149;') . '</td>';
        $return .= '<td class="value">' . ($safe_mode ? $GLOBALS['TL_LANG']['tl_syncCto_check']['on'] : $GLOBALS['TL_LANG']['tl_syncCto_check']['off']) . '</td>';
        $return .= '<td>' . $GLOBALS['TL_LANG']['tl_syncCto_check']['setting_off'] . '</td>';
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
        $ok           = (intval($memory_limit) >= 134217728);
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
        $return .= '<td>' . $GLOBALS['TL_LANG']['tl_syncCto_check']['register_globals'] . '</td>';
        $return .= '<td class="dot">' . ($ok ? '&nbsp;' : '&#149;') . '</td>';
        $return .= '<td class="value">' . ($register_globals ? $GLOBALS['TL_LANG']['tl_syncCto_check']['on'] : $GLOBALS['TL_LANG']['tl_syncCto_check']['off']) . '</td>';
        $return .= '<td>' . $GLOBALS['TL_LANG']['tl_syncCto_check']['setting_off'] . '</td>';
        $return .= '</tr>';

        // File uploads
        $file_uploads = $arrConfigurations['file_uploads'];
        $ok           = ($file_uploads == 1 || $file_uploads == 'On');
        $return .= '<tr class="' . ($ok ? 'ok' : 'warning') . '">';
        $return .= '<td>' . $GLOBALS['TL_LANG']['tl_syncCto_check']['file_uploads'] . '</td>';
        $return .= '<td class="dot">' . ($ok ? '&nbsp;' : '&#149;') . '</td>';
        $return .= '<td class="value">' . ($file_uploads ? $GLOBALS['TL_LANG']['tl_syncCto_check']['on'] : $GLOBALS['TL_LANG']['tl_syncCto_check']['off']) . '</td>';
        $return .= '<td>' . $GLOBALS['TL_LANG']['tl_syncCto_check']['setting_on'] . '</td>';
        $return .= '</tr>';

        // Upload maximum filesize
        $upload_max_filesize = $arrConfigurations['upload_max_filesize'];
        $ok                  = (intval($upload_max_filesize) >= 8388608);
        $return .= '<tr class="' . ($ok ? 'ok' : 'warning') . '">';
        $return .= '<td>' . $GLOBALS['TL_LANG']['tl_syncCto_check']['umf'][0] . '</td>';
        $return .= '<td class="dot">' . ($ok ? '&nbsp;' : '&#149;') . '</td>';
        $return .= '<td class="value">' . $this->getReadableSize($upload_max_filesize) . '</td>';
        $return .= '<td>' . $GLOBALS['TL_LANG']['tl_syncCto_check']['umf'][1] . '</td>';
        $return .= '</tr>';

        // Post maximum size
        $post_max_size = $arrConfigurations['post_max_size'];
        $ok            = (intval($post_max_size) >= 8388608);
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
        $suhosin = $arrConfigurations['suhosin'];
        $ok      = ($suhosin == false);
        $return .= '<tr class="' . ($ok ? 'ok' : 'warning') . '">';
        $return .= '<td>' . $GLOBALS['TL_LANG']['tl_syncCto_check']['suhosin'] . '</td>';
        $return .= '<td class="dot">' . ($ok ? '&nbsp;' : '&#149;') . '</td>';
        $return .= '<td class="value">' . ($suhosin ? $GLOBALS['TL_LANG']['tl_syncCto_check']['on'] : $GLOBALS['TL_LANG']['tl_syncCto_check']['off']) . '</td>';
        $return .= '<td>' . $GLOBALS['TL_LANG']['tl_syncCto_check']['setting_off'] . '</td>';
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
        $return .= '<col width="215" />';
        $return .= '<col width="35" />';
        $return .= '<col width="90" />';
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
        $return .= '<td>' . $GLOBALS['TL_LANG']['tl_syncCto_check']['fsocket'] . '</td>';
        $return .= '<td class="dot">' . ($ok ? '&nbsp;' : '&#149;') . '</td>';
        $return .= '<td class="value">' . ($fsockopen ? $GLOBALS['TL_LANG']['tl_syncCto_check']['on'] : $GLOBALS['TL_LANG']['tl_syncCto_check']['off']) . '</td>';
        $return .= '<td>' . $GLOBALS['TL_LANG']['tl_syncCto_check']['setting_on'] . '</td>';
        $return .= '</tr>';

        // ZipArchive
        $zip_archive = $arrFunctions['zip_archive'];
        $ok          = ($zip_archive == true);
        $return .= '<tr class="' . ($ok ? 'ok' : 'warning') . '">';
        $return .= '<td>' . $GLOBALS['TL_LANG']['tl_syncCto_check']['zip_archive'] . '</td>';
        $return .= '<td class="dot">' . ($ok ? '&nbsp;' : '&#149;') . '</td>';
        $return .= '<td class="value">' . ($zip_archive ? $GLOBALS['TL_LANG']['tl_syncCto_check']['on'] : $GLOBALS['TL_LANG']['tl_syncCto_check']['off']) . '</td>';
        $return .= '<td>' . $GLOBALS['TL_LANG']['tl_syncCto_check']['setting_on'] . '</td>';
        $return .= '</tr>';

        // mcrypt
        $mcrypt = $arrFunctions['mcrypt'];
        $ok     = ($mcrypt == true);
        $return .= '<tr class="' . ($ok ? 'ok' : 'warning') . '">';
        $return .= '<td>' . $GLOBALS['TL_LANG']['tl_syncCto_check']['mcrypt'] . '</td>';
        $return .= '<td class="dot">' . ($ok ? '&nbsp;' : '&#149;') . '</td>';
        $return .= '<td class="value">' . ($mcrypt ? $GLOBALS['TL_LANG']['tl_syncCto_check']['on'] : $GLOBALS['TL_LANG']['tl_syncCto_check']['off']) . '</td>';
        $return .= '<td>' . $GLOBALS['TL_LANG']['tl_syncCto_check']['setting_on'] . '</td>';
        $return .= '</tr>';

        // XMLWriter
        $xmlwriter = $arrFunctions['xmlwriter'];
        $ok        = ($xmlwriter == true);
        $return .= '<tr class="' . ($ok ? 'ok' : 'warning') . '">';
        $return .= '<td>' . $GLOBALS['TL_LANG']['tl_syncCto_check']['xmlwriter'] . '</td>';
        $return .= '<td class="dot">' . ($ok ? '&nbsp;' : '&#149;') . '</td>';
        $return .= '<td class="value">' . ($xmlwriter ? $GLOBALS['TL_LANG']['tl_syncCto_check']['on'] : $GLOBALS['TL_LANG']['tl_syncCto_check']['off']) . '</td>';
        $return .= '<td>' . $GLOBALS['TL_LANG']['tl_syncCto_check']['setting_on'] . '</td>';
        $return .= '</tr>';

        // XMLReader
        $xmlreader = $arrFunctions['xmlreader'];
        $ok        = ($xmlreader == true);
        $return .= '<tr class="' . ($ok ? 'ok' : 'warning') . '">';
        $return .= '<td>' . $GLOBALS['TL_LANG']['tl_syncCto_check']['xmlreader'] . '</td>';
        $return .= '<td class="dot">' . ($ok ? '&nbsp;' : '&#149;') . '</td>';
        $return .= '<td class="value">' . ($xmlreader ? $GLOBALS['TL_LANG']['tl_syncCto_check']['on'] : $GLOBALS['TL_LANG']['tl_syncCto_check']['off']) . '</td>';
        $return .= '<td>' . $GLOBALS['TL_LANG']['tl_syncCto_check']['setting_on'] . '</td>';
        $return .= '</tr>';

        $gmp    = $arrFunctions['gmp'];
        $bcmath = $arrFunctions['bcmath'];

        // bcmath
        if ($bcmath == true || ($bcmath == false && $gmp == false))
        {
            $ok = ($bcmath == true);
            $return .= '<tr class="' . ($ok ? 'ok' : 'warning') . '">';
            $return .= '<td>' . $GLOBALS['TL_LANG']['tl_syncCto_check']['bcmath'] . '</td>';
            $return .= '<td class="dot">' . ($ok ? '&nbsp;' : '&#149;') . '</td>';
            $return .= '<td class="value">' . ($bcmath ? $GLOBALS['TL_LANG']['tl_syncCto_check']['on'] : $GLOBALS['TL_LANG']['tl_syncCto_check']['off']) . '</td>';
            $return .= '<td>' . $GLOBALS['TL_LANG']['tl_syncCto_check']['setting_on'] . '</td>';
            $return .= '</tr>';
        }

        // gmp
        if ($gmp == true || ($bcmath == false && $gmp == false))
        {
            $ok = ($gmp == true);
            $return .= '<tr class="' . ($ok ? 'ok' : 'warning') . '">';
            $return .= '<td>' . $GLOBALS['TL_LANG']['tl_syncCto_check']['gmp'] . '</td>';
            $return .= '<td class="dot">' . ($ok ? '&nbsp;' : '&#149;') . '</td>';
            $return .= '<td class="value">' . ($gmp ? $GLOBALS['TL_LANG']['tl_syncCto_check']['on'] : $GLOBALS['TL_LANG']['tl_syncCto_check']['off']) . '</td>';
            $return .= '<td>' . $GLOBALS['TL_LANG']['tl_syncCto_check']['setting_on'] . '</td>';
            $return .= '</tr>';
        }

        $return .= '</table>';

        return $return;
    }

    public function checkProFunctions($arrFunctions)
    {
        $return .= '</table>';

        $return .= '<table width="100%" cellspacing="0" cellpadding="0" class="extensions" summary="">';
        $return .= '<colgroup>';
        $return .= '<col width="215" />';
        $return .= '<col width="35" />';
        $return .= '<col width="90" />';
        $return .= '<col width="*" />';
        $return .= '</colgroup>';
        $return .= '<tr>';
        $return .= '<th>' . $GLOBALS['TL_LANG']['tl_syncCto_check']['parameter'] . '</th>';
        $return .= '<th class="dot" style="width:1%;">&#149;</th>';
        $return .= '<th>' . $GLOBALS['TL_LANG']['tl_syncCto_check']['value'] . '</th>';
        $return .= '<th>' . $GLOBALS['TL_LANG']['tl_syncCto_check']['description'] . '</th>';
        $return .= '</tr>';

        $ok = $arrFunctions['create'] && $arrFunctions['delete'];

        $return .= '<tr class="' . ($ok ? 'ok' : 'warning') . '">';
        $return .= '<td>' . $GLOBALS['TL_LANG']['tl_syncCto_check']['trigger'] . '</td>';
        $return .= '<td class="dot">' . ($ok ? '&nbsp;' : '&#149;') . '</td>';
        $return .= '<td class="value">' . ($ok ? $GLOBALS['TL_LANG']['tl_syncCto_check']['on'] : $GLOBALS['TL_LANG']['tl_syncCto_check']['off']) . '</td>';
        $return .= '<td>' . $GLOBALS['TL_LANG']['tl_syncCto_check']['setting_on'] . '</td>';
        $return .= '</tr>';

        if (!$ok)
        {
            if (!empty($arrFunctions['error_create']))
            {
                $this->log('Error by checking trigger functions for syncCto Pro. ' . $arrFunctions['error_create'], __CLASS__ . '|' . __FUNCTION__, TL_ERROR);
            }
            else if (!empty($arrFunctions['error_delete']))
            {
                $this->log('Error by checking trigger functions for syncCto Pro. ' . $arrFunctions['error_create'], __CLASS__ . '|' . __FUNCTION__, TL_ERROR);
            }

            $return .= '<tr class="' . ($ok ? 'ok' : 'warning') . '">';
            $return .= '<td colspan="4">' . $GLOBALS['TL_LANG']['tl_syncCto_check']['trigger_information'] . '</td>';
            $return .= '</tr>';
        }

        $return .= '</table>';

        return $return;
    }

    public function checkExtendedInformation($arrExtendedFunctions)
    {
        $return .= '<table width="100%" cellspacing="0" cellpadding="0" class="extensions" summary="">';
        $return .= '<colgroup>';
        $return .= '<col width="331" />';
        $return .= '<col width="*" />';
        $return .= '</colgroup>';
        $return .= '<tr>';
        $return .= '<th>' . $GLOBALS['TL_LANG']['tl_syncCto_check']['parameter'] . '</th>';
        $return .= '<th>' . $GLOBALS['TL_LANG']['tl_syncCto_check']['value'] . '</th>';
        $return .= '</tr>';

        foreach ($arrExtendedFunctions as $key => $value)
        {
            if(isset($GLOBALS['TL_LANG']['tl_syncCto_check']['extendedInformation_desc'][$key]))
            {
                $strTitle = $GLOBALS['TL_LANG']['tl_syncCto_check']['extendedInformation_desc'][$key];
            }
            else
            {
                $strTitle = $key;
            }

            if ($key == 'current_time')
            {
                $return .= '<tr class="ok">';
                $return .= '<td>' . $strTitle . '</td>';
                $return .= '<td>' . $value['formate'] . '</td>';
                $return .= '</tr>';
            }
            else
            {
                $return .= '<tr class="ok">';
                $return .= '<td>' . $strTitle . '</td>';
                $return .= '<td>' . $value . '</td>';
                $return .= '</tr>';
            }
        }

        $return .= '</table>';

        return $return;
    }

    public function compareExtendedInformation($arrServerExtendedFunctions, $arrClientExtendedFunctions)
    {
        $return .= '<table width="100%" cellspacing="0" cellpadding="0" class="extensions" summary="">';
        $return .= '<colgroup>';
        $return .= '<col width="215" />';
        $return .= '<col width="35" />';
        $return .= '<col width="*" />';
        $return .= '<col width="*" />';
        $return .= '</colgroup>';
        $return .= '<tr>';
        $return .= '<th>' . $GLOBALS['TL_LANG']['tl_syncCto_check']['parameter'] . '</th>';
        $return .= '<th class="dot" style="width:1%;">&#149;</th>';
        $return .= '<th>' . $GLOBALS['TL_LANG']['tl_syncCto_check']['value_server'] . '</th>';
        $return .= '<th>' . $GLOBALS['TL_LANG']['tl_syncCto_check']['value_client'] . '</th>';
        $return .= '</tr>';

        foreach ($arrServerExtendedFunctions as $key => $value)
        {
            if (isset($GLOBALS['TL_LANG']['tl_syncCto_check']['extendedInformation_desc'][$key]))
            {
                $strTitle = $GLOBALS['TL_LANG']['tl_syncCto_check']['extendedInformation_desc'][$key];
            }
            else
            {
                $strTitle = $key;
            }

            if ($key == 'current_time')
            {
                $blnSame = true;

                // Check if same.
                foreach ($value as $strTimeKey => $mixTimeValue)
                {
                    if (in_array($strTimeKey, array('time', 'formate', 'second')))
                    {
                        continue;
                    }

                    if ($mixTimeValue != $arrClientExtendedFunctions[$key][$strTimeKey])
                    {
                        $blnSame = false;
                    }
                }

                // Build html.
                $return .= '<tr class="' . (($blnSame) ? 'ok' : 'warning') . '">';
                $return .= '<td>' . $strTitle . '</td>';
                $return .= '<td class="dot">' . ($blnSame ? '&nbsp;' : '&#149;') . '</td>';
                $return .= '<td>' . $value['formate'] . '</td>';
                $return .= '<td>' . $arrClientExtendedFunctions[$key]['formate'] . '</td>';
                $return .= '</tr>';
            }
            else
            {
                // Check if same.
                $blnSame = ($value == $arrClientExtendedFunctions[$key]);

                // Build html.
                $return .= '<tr class="' . (($blnSame) ? 'ok' : 'warning') . '">';
                $return .= '<td>' . $strTitle . '</td>';
                $return .= '<td class="dot">' . ($blnSame ? '&nbsp;' : '&#149;') . '</td>';
                $return .= '<td>' . $value . '</td>';
                $return .= '<td>' . $arrClientExtendedFunctions[$key] . '</td>';
                $return .= '</tr>';
            }
        }

        $return .= '</table>';

        return $return;
    }

}
