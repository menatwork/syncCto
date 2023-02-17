<?php

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2014
 * @package    syncCto
 * @license    GNU/LGPL
 * @filesource
 */

class SyncCtoDatabaseUpdater extends \Database\Installer
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
        $this->arrAllowedAction = deserialize($GLOBALS['TL_CONFIG']['syncCto_auto_db_updater'], true);
    }

    /**
     * Run auto update
     *
     * @throws Exception
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
            if (!in_array($strAction, $this->arrAllowedAction))
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
                    \Database::getInstance()->query($strOperation);
                }
                catch (Exception $exc)
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
                if (is_int($key)) {
                    $strError .= sprintf("%i. %s. | ", ((int)$key) + 1, $value['error']);
                } else {
                    $strError .= sprintf("%s. %s. | ", $key, $value['error']);
                }
            }

            throw new Exception('There was an error on updating the database: ' . $strError);
        }
    }

}
