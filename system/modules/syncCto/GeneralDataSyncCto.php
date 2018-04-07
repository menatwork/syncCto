<?php

// Interfaces
use DcGeneral\Data\CollectionInterface as CollectionInterface;
use DcGeneral\Data\ConfigInterface as ConfigInterface;
use DcGeneral\Data\DefaultCollection as DefaultCollection;
use DcGeneral\Data\DefaultConfig as DefaultConfig;
use DcGeneral\Data\DefaultModel as DefaultModel;
use DcGeneral\Data\DriverInterface as DriverInterface;
use DcGeneral\Data\ModelInterface as ModelInterface;

// Classes

/**
 * Contao Open Source CMS
 *
 * @see        InterfaceGeneralData
 * @copyright  MEN AT WORK 2014
 * @package    DC_General Drvier
 * @license    GNU/LGPL
 * @filesource
 */
class GeneralDataSyncCto implements DriverInterface
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
     * @throws Exception
     */
    public function setBaseConfig(array $arrConfig)
    {

    }

    /**
     * Return empty config object
     *
     * @return InterfaceGeneralDataConfig
     */
    public function getEmptyConfig()
    {
        return DefaultConfig::init();
    }

    /**
     * Fetch an empty single record (new item).
     *
     * @return InterfaceGeneralModel
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
     * @return InterfaceGeneralModel
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
     * @param int|string|InterfaceGeneralModel Id or the object itself, to delete
     */
    public function delete($item)
    {

    }

    /**
     * Fetch a single/first record by id/filter.
     *
     * @param GeneralDataConfigDefault $objConfig
     *
     * @return InterfaceGeneralModel
     */
    public function fetch(ConfigInterface $objConfig)
    {
        return $this->getEmptyModel();
    }

    /**
     * Fetch all records (optional limited).
     *
     * @param GeneralDataConfigDefault $objConfig
     *
     * @return InterfaceGeneralCollection
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
     * @param InterfaceGeneralDataConfig $objConfig   The filter config options.
     *
     * @return InterfaceGeneralCollection
     */
    public function getFilterOptions(ConfigInterface $objConfig)
    {
        return $this->getEmptyCollection();
    }

    /**
     * Return the amount of total items.
     *
     * @param GeneralDataConfigDefault $objConfig
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
     * @return InterfaceGeneralCollection
     */
    public function getVersions($mixID, $blnOnlyActve = false)
    {
        return false;
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
     * @param InterfaceGeneralModel $objModel1
     * @param InterfaceGeneralModel $objModel2
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
