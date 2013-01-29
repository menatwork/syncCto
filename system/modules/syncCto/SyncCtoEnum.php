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

/**
 * Enum class with constants
 */
class SyncCtoEnum
{
    /**
     * File/Folder state
     */

    const FILESTATE_FILE            = 0;
    const FILESTATE_MISSING         = 1;
    const FILESTATE_SAME            = 2;
    const FILESTATE_NEED            = 3;
    const FILESTATE_DELETE          = 4;
    const FILESTATE_TOO_BIG         = 10;
    const FILESTATE_TOO_BIG_MISSING = 11;
    const FILESTATE_TOO_BIG_SAME    = 12;
    const FILESTATE_TOO_BIG_NEED    = 13;
    const FILESTATE_TOO_BIG_DELETE  = 14;
    const FILESTATE_BOMBASTIC_BIG   = 99;
    
    const FILESTATE_FOLDER          = 100;
    const FILESTATE_FOLDER_DELETE   = 104;

    /**
     * File Transmission
     */
    
    const FILETRANS_SEND    = 1;
    const FILETRANS_SKIPPED = 2;
    const FILETRANS_WAITING = 3;

    /**
     * Upload Folder
     */
    
    const UPLOAD_TEMP       = 1;
    const UPLOAD_SQL_TEMP   = 2;
    const UPLOAD_SYNC_TEMP  = 3;
    const UPLOAD_SYNC_SPLIT = 4;

    /**
     * Base Folder
     */
    
    const FOLDER_TEMP        = 1;
    const FOLDER_DB_BACKUP   = 2;
    const FOLDER_FILE_BACKUP = 3;

    /**
     * Localconfig state
     */
    
    const LOADCONFIG_KEYS_ONLY = 1;
    const LOADCONFIG_KEY_VALUE = 2;

    /**
     * Page State
     */
    
    const WORK_OK      = 'Ok';
    const WORK_ERROR   = 'Error';
    const WORK_WORK    = 'Work';
    const WORK_SKIPPED = 'Skipped';

    /**
     * Pre installed Codifyengines
     */
    
    const CODIFY_EMPTY = "empty";

    /**
     * Fileinformation size
     */
    
    const FILEINFORMATION_SMALL = 1;
    const FILEINFORMATION_BIG   = 2;

}