<?php if (!defined('TL_ROOT')) die('You cannot access this file directly!');

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2013 
 * @package    syncCto
 * @license    GNU/LGPL 
 * @filesource
 */
 
/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_syncCto_backup_file']['backup_legend']                  = "Backup settings";
$GLOBALS['TL_LANG']['tl_syncCto_backup_file']['filelist_legend']                = "Files and folders";
$GLOBALS['TL_LANG']['tl_syncCto_backup_file']['edit']                           = 'Create a backup of the files';

/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_syncCto_backup_file']['core_files']                     = array('Contao-Installation', 'Wählen Sie bitte aus, ob die Contao-Installation gesichert werden soll.');
$GLOBALS['TL_LANG']['tl_syncCto_backup_file']['user_files']                     = array('Persönliche Dateien (tl_files)', 'Wählen Sie bitte aus, ob die persönlichen Dateien gesichert werden sollen.');
$GLOBALS['TL_LANG']['tl_syncCto_backup_file']['backup_name']                    = array('Filename', 'Here you can enter an optional name for identification.');
$GLOBALS['TL_LANG']['tl_syncCto_backup_file']['filelist']                       = array('Source files', 'Please select a file or folder from the files directory.');

/**
 * List
 */
$GLOBALS['TL_LANG']['tl_syncCto_backup_file']['step1']                          = 'Save Contao Installation / tl_files.';
$GLOBALS['TL_LANG']['tl_syncCto_backup_file']['complete']                       = 'Create file backup under';

?>