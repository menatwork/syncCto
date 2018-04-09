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

namespace SyncCto\Data;

use Contao\Database;
use ContaoCommunityAlliance\DcGeneral\Data\CollectionInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ConfigInterface;
use ContaoCommunityAlliance\DcGeneral\Data\DataProviderInterface;
use ContaoCommunityAlliance\DcGeneral\Data\DefaultCollection;
use ContaoCommunityAlliance\DcGeneral\Data\DefaultConfig;
use ContaoCommunityAlliance\DcGeneral\Data\DefaultModel;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;

class GeneralDataSyncCto implements DataProviderInterface
{
    /* /////////////////////////////////////////////////////////////////////
     * ---------------------------------------------------------------------
     * Vars
     * ---------------------------------------------------------------------
     * ////////////////////////////////////////////////////////////////// */

    /**
     * Name of current source
     * @var string
     */
    protected $strSource = null;

    /**
     * Database
     * @var Database
     */
    protected $objDatabase = null;

    /* /////////////////////////////////////////////////////////////////////
     * ---------------------------------------------------------------------
     * Constructor and co
     * ---------------------------------------------------------------------
     * ////////////////////////////////////////////////////////////////// */

    public function __construct()
    {

    }

    /* /////////////////////////////////////////////////////////////////////
     * ---------------------------------------------------------------------
     * Getter | Setter
     * ---------------------------------------------------------------------
     * ////////////////////////////////////////////////////////////////// */

    /**
     * Set base config with source and other neccesary prameter
     *
     * @param array $arrConfig
     * @throws \Exception
     */
    public function setBaseConfig(array $arrConfig)
    {

    }

    /**
     * Return empty config object
     *
     * @return ConfigInterface
     */
    public function getEmptyConfig()
    {
        return DefaultConfig::init();
    }

    /**
     * Fetch an empty single record (new item).
     *
     * @return ModelInterface
     */
    public function getEmptyModel()
    {
        $objModel = new DefaultModel();
        $objModel->setProviderName($this->strSource);
        return $objModel;
    }

    /**
     * Fetch an empty single collection (new item).
     *
     * @return CollectionInterface
     */
    public function getEmptyCollection()
    {
        return new DefaultCollection();
    }

    /* /////////////////////////////////////////////////////////////////////
     * ---------------------------------------------------------------------
     * Functions
     * ---------------------------------------------------------------------
     * ////////////////////////////////////////////////////////////////// */

    /**
     * Delete an item.
     *
     * @param ModelInterface Id or the object itself, to delete
     */
    public function delete($item)
    {

    }

    /**
     * Fetch a single/first record by id/filter.
     *
     * @param ConfigInterface $objConfig
     *
     * @return ModelInterface
     */
    public function fetch(ConfigInterface $objConfig)
    {
        return $this->getEmptyModel();
    }

    /**
     * Fetch all records (optional limited).
     *
     * @param ConfigInterface $objConfig
     *
     * @return CollectionInterface
     */
    public function fetchAll(ConfigInterface $objConfig)
    {
        return $this->getEmptyCollection();
    }

    /**
     * Retrieve all unique values for the given property.
     *
     * The result set will be an array containing all unique values contained in the data provider.
     * Note: this only re-ensembles really used values for at least one data set.
     *
     * The only information being interpreted from the passed config object is the first property to fetch and the
     * filter definition.
     *
     * @param ConfigInterface $objConfig   The filter config options.
     *
     * @return CollectionInterface
     */
    public function getFilterOptions(ConfigInterface $objConfig)
    {
        return $this->getEmptyCollection();
    }

    /**
     * Return the amount of total items.
     *
     * @param ConfigInterface $objConfig
     *
     * @return int
     */
    public function getCount(ConfigInterface $objConfig)
    {
        return 0;
    }

    public function isUniqueValue($strField, $varNew, $intId = null)
    {
        return false;
    }

    public function resetFallback($strField)
    {
        return;
    }

    public function save(ModelInterface $objItem, $recursive = false)
    {
        return $this->getEmptyModel();
    }

    public function saveEach(CollectionInterface $objItems, $recursive = false)
    {

    }

    /**
     * Check if the value exists in the table
     *
     * @return boolean
     */
    public function fieldExists($strField)
    {
        return false;
    }

    /* /////////////////////////////////////////////////////////////////////
     * ---------------------------------------------------------------------
     * Version Functions
     * ---------------------------------------------------------------------
     * ////////////////////////////////////////////////////////////////// */

    public function getVersion($mixID, $mixVersion)
    {
        return $this->getEmptyModel();
    }

    /**
     * Return a list with all versions for this row
     *
     * @param mixed $mixID The ID of record
     *
     * @return CollectionInterface
     */
    public function getVersions($mixID, $blnOnlyActve = false)
    {
        return null;
    }

    public function saveVersion(ModelInterface $objModel, $strUsername)
    {

    }

    /**
     * Set a Version as active.
     *
     * @param mix $mixID The ID of record
     * @param mix $mixVersion The ID of the Version
     */
    public function setVersionActive($mixID, $mixVersion)
    {

    }

    /**
     * Return the active version from a record
     *
     * @param mix $mixID The ID of record
     *
     * @return mix Version ID
     */
    public function getActiveVersion($mixID)
    {

    }

    /**
     * Check if two models have the same properties
     *
     * @param ModelInterface $objModel1
     * @param ModelInterface $objModel2
     *
     * return boolean True - If both models are same, false if not
     */
    public function sameModels($objModel1, $objModel2)
    {
        return false;
    }

    /* /////////////////////////////////////////////////////////////////////
     * ---------------------------------------------------------------------
     * Undo
     * ---------------------------------------------------------------------
     * ////////////////////////////////////////////////////////////////// */

    protected function insertUndo($strSourceSQL, $strSaveSQL, $strTable)
    {

    }
}
