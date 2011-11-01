<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');
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
 * @copyright  MEN AT WORK 2011
 * @package    syncCto
 * @license    GNU/LGPL
 * @filesource
 */
/**
 * Initialize the system
 */
define('TL_MODE', 'BE');
require_once('../../initialize.php');

/**
 * Class PurgeLog
 */
class CronDeleteFileBackups extends Backend
{

    /**
     * Initialize the controller
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Implement the commands to run by this batch program
     */
    public function run()
    {
        $this->import('Files');
		
        $files = scan(TL_ROOT . $GLOBALS['TL_CONFIG']['uploadPath'] . '/syncCto_backups/files');
        foreach ($files as $file)
        {
            $f = new File($GLOBALS['TL_CONFIG']['uploadPath'] . '/syncCto_backups/files/' . $file);

            if (strtolower($f->__get('extension')) == "zip")
            {
                $f->delete();
            }
        }
    }

}

/**
 * Instantiate log purger
 */
$objDeleteFileBackups = new SyncCtoDeleteFileBackups();
$objDeleteFileBackups->run();

?>