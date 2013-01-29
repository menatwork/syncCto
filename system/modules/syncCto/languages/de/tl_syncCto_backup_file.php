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
$GLOBALS['TL_LANG']['tl_syncCto_backup_file']['backup_legend']                  = 'Backup-Einstellungen';
$GLOBALS['TL_LANG']['tl_syncCto_backup_file']['filelist_legend']                = 'Dateien und Ordner';
$GLOBALS['TL_LANG']['tl_syncCto_backup_file']['edit']                           = 'Ein Backup der Dateien erstellen';

/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_syncCto_backup_file']['core_files']                     = array('Contao-Installation', 'Wählen Sie bitte aus, ob die Contao-Installation gesichert werden soll.');
$GLOBALS['TL_LANG']['tl_syncCto_backup_file']['user_files']                     = array('Persönliche Dateien (tl_files)', 'Wählen Sie bitte aus, ob die persönlichen Dateien gesichert werden sollen.');
$GLOBALS['TL_LANG']['tl_syncCto_backup_file']['backup_name']                    = array('Dateiname', 'Hier können Sie einen optionalen Namen zur eindeutigen Identifikation eingeben.');
$GLOBALS['TL_LANG']['tl_syncCto_backup_file']['filelist']                       = array('Quelldateien', 'Bitte wählen Sie eine Datei oder einen Ordner aus der Dateiübersicht.');

/**
 * List
 */
$GLOBALS['TL_LANG']['tl_syncCto_backup_file']['step1']                          = 'Contao-Installation / tl_files sichern.';
$GLOBALS['TL_LANG']['tl_syncCto_backup_file']['complete']                       = 'Backup erstellt unter';

?>