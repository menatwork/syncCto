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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2014-2018 MEN AT WORK.
 * @license    https://github.com/menatwork/syncCto/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace SyncCto\Contao\Database;

use Contao\Database;
use Contao\Database\Installer;

class Updater extends Installer
{
    /**
     * List with allowed update actions.
     *
     * @var array
     */
    protected $arrAllowedAction;

    /**
     * List with errors
     *
     * @var array
     */
    protected $arrError = array();

    /**
     * __construct
     */
    public function __construct()
    {
        parent::__construct();

        // Load allowed actions from config file.
        $this->arrAllowedAction = \deserialize($GLOBALS['TL_CONFIG']['syncCto_auto_db_updater'], true);
    }

    /**
     * Run auto update
     *
     * @throws \Exception
     *
     * @return boolean Return true on success or when there are no data.
     */
    public function runAutoUpdate()
    {
        $sql_command = $this->compileCommands();

        if (empty($sql_command))
        {
            return true;
        }

        // Remove not allowed actions
        foreach ($sql_command as $strAction => $strOperation)
        {
            if (!\in_array($strAction, $this->arrAllowedAction))
            {
                unset($sql_command[$strAction]);
            }
        }

        if (empty($sql_command))
        {
            return true;
        }

        // Execute all
        foreach ($this->arrAllowedAction as $strAction)
        {
            if(!isset($sql_command[$strAction]))
            {
                continue;
            }

            foreach ($sql_command[$strAction] as $strOperation)
            {
                try
                {
                    Database::getInstance()->query($strOperation);
                }
                catch (\Exception $exc)
                {
                    $this->arrError[$strAction][] = array(
                        'operation' => $strOperation,
                        'error'     => $exc->getMessage(),
                        'trace'     => $exc->getTraceAsString()
                    );
                }
            }
        }

        if (empty($this->arrError))
        {
            return true;
        }
        else
        {
            $strError = '';

            foreach ($this->arrError as $key => $value)
            {
                $strError .= \sprintf("%i. %s. | ", $key + 1, $value['error']);
            }

            throw new \Exception('There was an error on updating the database: ' . $strError);
        }
    }
}
