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

    const FILESTATE_DBAFS_CONFLICT  = 200;

    /**
     * File Transmission
     */

    const FILETRANS_MOVED   = 0; // The file is moved to the destination.
    const FILETRANS_SEND    = 1; // The file is send to the client.
    const FILETRANS_SKIPPED = 2; // The file is skipped, because of error or other things.
    const FILETRANS_WAITING = 3; // The file is waiting.

    /**
     * DBAFS state
     */

    const DBAFS_CREATE        = 'create';
    const DBAFS_SAME          = 'same';
    const DBAFS_CONFLICT      = 'conflict';
    const DBAFS_TAIL_CONFLICT = 'tail_conflict';
    const DBAFS_DATA_CONFLICT = 'data_conflict';

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