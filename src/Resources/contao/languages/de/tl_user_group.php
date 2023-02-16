<?php

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2014
 * @package    syncCto
 * @license    GNU/LGPL
 * @filesource
 */

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_user_group']['syncCto_legend']        = 'SyncCto - Client-Rechte';
$GLOBALS['TL_LANG']['tl_user_group']['syncCto_tables_legend'] = 'SyncCto - Erlaubte Tabellen';

/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_user_group']['syncCto_clients']               = [
    'Erlaubte Clients',
    'Hier können Sie den Zugriff auf einen oder mehrere Clients erlauben.'
];
$GLOBALS['TL_LANG']['tl_user_group']['syncCto_clients_p']             = [
    'Clientrechte',
    'Hier können Sie die Clientrechte festlegen.'
];
$GLOBALS['TL_LANG']['tl_user_group']['syncCto_sync_options']          = [
    'Erlaubte Dateioperationen',
    'Hier können Sie den Zugriff auf bestimmte Dateioperationen erlauben.'
];
$GLOBALS['TL_LANG']['tl_user_group']['syncCto_tables']                = [
    'Erlaubte Tabellen',
    'Hier können Sie den Zugriff auf die Datenbank-Tabellen erlauben.'
];
$GLOBALS['TL_LANG']['tl_user_group']['syncCto_useTranslatedNames']    = [
    'Verständliche Tabellennamen verwenden',
    'Hier können Sie auswählen, ob bei der Synchronisation anstelle der Tabellennamen (tl_content, ...) verständlichere Namen verwendet werden.'
];
$GLOBALS['TL_LANG']['tl_user_group']['syncCto_force_dbafs_overwrite'] = [
    'Überschreiben des DBAFS erzwingen',
    'Wenn aktiviert, wird die tl_filed Tabelle des DBAFS überschrieben.'
];
$GLOBALS['TL_LANG']['tl_user_group']['syncCto_hide_auto_sync']      = [
    'Verstecke den Auto-Sync-All Knopf',
    'Wenn aktiviert, wird der Knopf für den Auto-Sync-All in der Client Übersicht versteckt.'
];
