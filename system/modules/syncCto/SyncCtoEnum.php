<?php

class SyncCtoEnum
{
    //-----------------------------------------
    // FileState
    const FILESTATE_FILE = 0;    
    const FILESTATE_MISSING = 1;
    const FILESTATE_SAME = 2;
    const FILESTATE_NEED = 3;
    const FILESTATE_DELETE = 4;    
    const FILESTATE_TOO_BIG = 10;
    const FILESTATE_TOO_BIG_MISSING = 11;
    const FILESTATE_TOO_BIG_SAME = 12;
    const FILESTATE_TOO_BIG_NEED = 13;
    const FILESTATE_TOO_BIG_DELETE = 14;    
    const FILESTATE_BOMBASTIC_BIG = 99;
    //-----------------------------------------
    // File Transmission
    const FILETRANS_SEND = 1;
    const FILETRANS_SKIPPED = 2;
    const FILETRANS_WAITING = 3;
    //-----------------------------------------
    // Upload Folder
    const UPLOAD_TEMP = 1;
    const UPLOAD_SQL_TEMP = 2;
    const UPLOAD_SYNC_TEMP = 3;
    const UPLOAD_SYNC_SPLIT = 4;
    //-----------------------------------------
    // Base Folder
    const FOLDER_TEMP = 1;
    const FOLDER_DB_BAKUP = 2;
    const FOLDER_FILE_BAKUP = 3;    
    //-----------------------------------------
    // Loaclconfig state
    const LOADCONFIG_KEYS_ONLY = 1;
    const LOADCONFIG_KEY_VALUE = 2;
    //-----------------------------------------
    // Page State
    const WORK_OK = 1;
    const WORK_ERROR = 2;
    const WORK_WORK = 3;
    const WORK_SKIPPED = 4;
    //-----------------------------------------
    // Pre installet Codifyengines
    const CODIFY_EMPTY = "Empty";
    const CODIFY_BLOW = "Blowfish";
    const CODIFY_MCRYPT = "Mcrypt";
}
?>
