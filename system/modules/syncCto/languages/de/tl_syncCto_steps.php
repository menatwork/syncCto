<?php

/**
 * This file is part of menatwork/synccto.
 *
 * (c) 2014-2018 MEN AT WORK.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    menatwork/synccto
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Andreas Isaak <andy.jared@googlemail.com>
 * @author     Patrick Kahl <kahl.patrick@googlemail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2014-2018 MEN AT WORK.
 * @license    https://github.com/menatwork/syncCto/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

/**
 * Headline
 */
$GLOBALS['TL_LANG']['tl_syncCto_sync']['edit']                              = 'Synchronisation: Server ';

/**
 * List
 */
$GLOBALS['TL_LANG']['tl_syncCto_sync']['step_1_show']['description_1']      = 'Systemcheck des Clients verarbeiten.';

$GLOBALS['TL_LANG']['tl_syncCto_sync']['step_1']['description_1']           = 'Vorbereitung des Clients.';
$GLOBALS['TL_LANG']['tl_syncCto_sync']['step_1']['description_2']           = 'Temporäre Ordner leeren.';
$GLOBALS['TL_LANG']['tl_syncCto_sync']['step_1']['description_3']           = 'Aktualisierung der entfernten Synchronisationssoftware.';

$GLOBALS['TL_LANG']['tl_syncCto_sync']['step_2']['description_1']           = 'Abgleich und Versand der Vergleichslisten.';
$GLOBALS['TL_LANG']['tl_syncCto_sync']['step_2']['description_2']           = 'Nach löschbaren Dateien suchen.';
$GLOBALS['TL_LANG']['tl_syncCto_sync']['step_2']['description_3']           = 'Vergleich der Dateien.';
$GLOBALS['TL_LANG']['tl_syncCto_sync']['step_2']['description_4']           = 'Es wurden %s neue, %s veränderte, %s gelöschte und %s nicht zustellbare Datei(en) gefunden.<br />Das ergibt eine Größe von %s neuen, %s veränderten und %s gelöschten Dateien.';
$GLOBALS['TL_LANG']['tl_syncCto_sync']['step_2']['description_5']           = 'Es wurden %s zu große Dateien gefunden.';

$GLOBALS['TL_LANG']['tl_syncCto_sync']['step_3']['description_1']           = 'Dateien verarbeiten.';
$GLOBALS['TL_LANG']['tl_syncCto_sync']['step_3']['description_2']           = '%s von %s Datei(en) verarbeitet.';
$GLOBALS['TL_LANG']['tl_syncCto_sync']['step_3']['description_3']           = 'Große Dateien aufteilen.';
$GLOBALS['TL_LANG']['tl_syncCto_sync']['step_3']['description_4']           = 'Große Dateien transferieren.';
$GLOBALS['TL_LANG']['tl_syncCto_sync']['step_3']['description_5']           = 'Große Dateien zusammenbauen.';
$GLOBALS['TL_LANG']['tl_syncCto_sync']['step_3']['description_6']           = '%s von %s großen Datei(en) aufgeteilt.';
$GLOBALS['TL_LANG']['tl_syncCto_sync']['step_3']['description_7']           = '%s von %s großen Datei(en) übertragen.';
$GLOBALS['TL_LANG']['tl_syncCto_sync']['step_3']['description_8']           = '%s von %s großen Datei(en) zusammengebaut.';

$GLOBALS['TL_LANG']['tl_syncCto_sync']['step_4']['description_1']           = 'Abgleich der Datenbank.';
$GLOBALS['TL_LANG']['tl_syncCto_sync']['step_4']['description_2']           = 'SQL-Scripte erstellen.';
$GLOBALS['TL_LANG']['tl_syncCto_sync']['step_4']['description_3']           = 'SQL-Scripte transferieren.';
$GLOBALS['TL_LANG']['tl_syncCto_sync']['step_4']['description_4']           = 'SQL-Scripte wurden erfolgreich importiert.';

$GLOBALS['TL_LANG']['tl_syncCto_sync']['step_5']['description_1']           = 'Daten importieren.';
$GLOBALS['TL_LANG']['tl_syncCto_sync']['step_5']['description_2']           = 'Konfigurationsdateien importieren.';
$GLOBALS['TL_LANG']['tl_syncCto_sync']['step_5']['description_3']           = ' Datei(en) übersprungen.';
$GLOBALS['TL_LANG']['tl_syncCto_sync']['step_5']['description_4']           = ' Datei(en) gesendet.';
$GLOBALS['TL_LANG']['tl_syncCto_sync']['step_5']['description_5']           = ' Datei(en) wartend.';
$GLOBALS['TL_LANG']['tl_syncCto_sync']['step_5']['description_6']           = 'Übertragende Dateien:';
$GLOBALS['TL_LANG']['tl_syncCto_sync']['step_5']['description_7']           = 'Gelöschte Dateien:';
$GLOBALS['TL_LANG']['tl_syncCto_sync']['step_5']['description_8']           = 'Fehlerhafte Dateien:';
$GLOBALS['TL_LANG']['tl_syncCto_sync']['step_5']['description_9']           = 'Gelöschte Ordner:';

$GLOBALS['TL_LANG']['tl_syncCto_sync']['abort']                             = 'Abbruch der Synchronisation und Säuberung des Clients.';
$GLOBALS['TL_LANG']['tl_syncCto_sync']['complete_client']                   = 'Die Synchronisation des %sClients%s wurde erfolgreich abgeschlossen.';
$GLOBALS['TL_LANG']['tl_syncCto_sync']['complete_server']                   = 'Die Synchronisation des %sServers%s wurde erfolgreich abgeschlossen.';
$GLOBALS['TL_LANG']['tl_syncCto_sync']['popup']                             = 'Synchronisationsübersicht';
