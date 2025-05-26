<?php
/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2013
 * @package    ZipArchiveCto
 * @license    GNU/LGPL
 * @filesource
 */


/**
 * A file archive, compressed with Zip.
 * @link http://php.net/manual/en/class.ziparchive.php
 */
class ZipArchiveCto extends ZipArchive
{

    /**
     * (PHP 5 &gt;= 5.2.0, PECL zip &gt;= 1.1.0)<br/>
     * Get for an error id the fitting error message.
     * @param int $intError
     * @return string
     */
    public function getErrorDescription($intError)
    {
        switch ($intError)
        {
            case self::ER_OK:
                return "No error.";

            case self::ER_MULTIDISK :
                return "Multi-disk zip archives not supported.";

            case self::ER_RENAME :
                return "Renaming temporary file failed.";

            case self::ER_CLOSE :
                return "Closing zip archive failed";

            case self::ER_SEEK :
                return "Seek error";

            case self::ER_READ :
                return "Read error";

            case self::ER_WRITE :
                return "Write error";

            case self::ER_CRC :
                return "CRC error";

            case self::ER_ZIPCLOSED :
                return "Containing zip archive was closed";

            case self::ER_NOENT :
                return "No such file.";

            case self::ER_EXISTS :
                return "File already exists";

            case self::ER_OPEN :
                return "Can't open file";

            case self::ER_TMPOPEN :
                return "Failure to create temporary file.";

            case self::ER_ZLIB :
                return "Zlib error";

            case self::ER_MEMORY :
                return "Memory allocation failure";

            case self::ER_CHANGED :
                return "Entry has been changed";

            case self::ER_COMPNOTSUPP :
                return "Compression method not supported.";

            case self::ER_EOF :
                return "Premature EOF";

            case self::ER_INVAL :
                return "Invalid argument";

            case self::ER_NOZIP :
                return "Not a zip archive";

            case self::ER_INTERNAL :
                return "Internal error";

            case self::ER_INCONS :
                return "Zip archive inconsistent";

            case self::ER_REMOVE :
                return "Can't remove file";

            case self::ER_DELETED :
                return "Entry has been deleted";

            default:
                return "Unknown error.";
        }
    }

    /**
     * (PHP 5 &gt;= 5.2.0, PECL zip &gt;= 1.1.0)<br/>
     * Open a ZIP file archive
     * @link http://php.net/manual/en/function.ziparchive-open.php
     * @param string $filename <p>
     * The file name of the ZIP archive to open.<br/>
     * Use only paths inside of the Contao installation like "system/modules/".
     * </p>
     * @param int $flags [optional] <p>
     * The mode to use to open the archive.
     * <p>
     * <b>ZIPARCHIVE::OVERWRITE</b>
     * </p>
     * @return mixed <i>Error codes</i>
     * <p>
     * Returns true on success or the error code.
     * <p>
     * <b>ZIPARCHIVE::ER_EXISTS</b>
     * </p>
     * <p>
     * <b>ZIPARCHIVE::ER_INCONS</b>
     * </p>
     * <p>
     * <b>ZIPARCHIVE::ER_INVAL</b>
     * </p>
     * <p>
     * <b>ZIPARCHIVE::ER_MEMORY</b>
     * </p>
     * <p>
     * <b>ZIPARCHIVE::ER_NOENT</b>
     * </p>
     * <p>
     * <b>ZIPARCHIVE::ER_NOZIP</b>
     * </p>
     * <p>
     * <b>ZIPARCHIVE::ER_OPEN</b>
     * </p>
     * <p>
     * <b>ZIPARCHIVE::ER_READ</b>
     * </p>
     * <p>
     * <b>ZIPARCHIVE::ER_SEEK</b>
     * </p>
     * </p>
     */
    public function open($filename, $flags = null)
    {
        $filename = SyncCtoHelper::getInstance()->getContaoRoot() . "/" . $filename;

        return parent::open($filename, $flags);
    }

    /**
     * (PHP 5 &gt;= 5.2.0, PECL zip &gt;= 1.1.0)<br/>
     * Adds a file to a ZIP archive from the given path
     * @link http://php.net/manual/en/function.ziparchive-addfile.php
     * @param string $filename <p>
     * The path to the file to add.<br/>
     * Use only paths inside of the Contao installation like "system/modules/".
     * </p>
     * @param string $localname [optional] <p>
     * If supplied, this is the local name inside the ZIP archive that will override the <i>filename</i>.
     * </p>
     * @param int $start [optional] <p>
     * This parameter is not used but is required to extend <b>ZipArchive</b>.
     * </p>
     * @param int $length [optional] <p>
     * This parameter is not used but is required to extend <b>ZipArchive</b>.
     * </p>
     * @return bool true on success or false on failure.
     */
    public function addFile(string $filename, string $localname = null, int $start = 0, int $length = 0, int $flags = 0): int
    {
        $filename = SyncCtoHelper::getInstance()->getContaoRoot() . "/" . $filename;

        return parent::addFile($filename, $localname, $start, $length, $flags);
    }

    /**
     * (PHP 5 &gt;= 5.2.0, PECL zip &gt;= 1.1.0)<br/>
     * Extract the archive contents
     * @link http://php.net/manual/en/function.ziparchive-extractto.php
     * @param string $destination <p>
     * Location where to extract the files.<br/>
     * Use only paths inside of the Contao installation like "system/modules/".
     * </p>
     * @param mixed $entries [optional] <p>
     * The entries to extract. It accepts either a single entry name or
     * an array of names.
     * </p>
     * @return bool true on success or false on failure.
     */
    public function extractTo($destination, $entries = null)
    {
        $destination = SyncCtoHelper::getInstance()->getContaoRoot() . "/" . $destination;

        return parent::extractTo($destination, $entries);
    }

}
