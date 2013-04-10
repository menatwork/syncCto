<?php

/**
 * Contao Open Source CMS
 *
 * @see        InterfaceGeneralData
 * @copyright  MEN AT WORK 2013 
 * @package    syncCto
 * @license    GNU/LGPL 
 * @filesource
 */

class GeneralDataSyncCto implements InterfaceGeneralData
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

    public function __construct() { }

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
    public function setBaseConfig(array $arrConfig) { }

    /**
     * Return empty config object
     *
     * @return InterfaceGeneralDataConfig
     */
    public function getEmptyConfig()
    {
        return GeneralDataConfigDefault::init();
    }

    /**
     * Fetch an empty single record (new item).
     *
     * @return InterfaceGeneralModel
     */
    public function getEmptyModel()
    {
        $objModel = new GeneralModelDefault();
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
        return new GeneralCollectionDefault();
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
    public function delete($item) { }

    /**
     * Fetch a single/first record by id/filter.
     *
     * @param GeneralDataConfigDefault $objConfig
     *
     * @return InterfaceGeneralModel
     */
    public function fetch(GeneralDataConfigDefault $objConfig)
    {
        return  $this->getEmptyModel();
    }

    /**
     * Fetch all records (optional limited).
     *
     * @param GeneralDataConfigDefault $objConfig
     *
     * @return InterfaceGeneralCollection
     */
    public function fetchAll(GeneralDataConfigDefault $objConfig)
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
    public function getCount(GeneralDataConfigDefault $objConfig)
    {
        return 0;
    }

    public function isUniqueValue($strField, $varNew, $intId = null)
    {       
        return false;
    }

    public function resetFallback($strField)
    {
        $this->objDatabase->query('UPDATE ' . $this->strSource . ' SET ' . $strField . ' = \'\'');
    }

    public function save(InterfaceGeneralModel $objItem, $recursive = false)
    {
        return $this->getEmptyModel();
    }

    public function saveEach(InterfaceGeneralCollection $objItems, $recursive = false) { }

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
        return $this->getEmptyCollection();
    }

    public function saveVersion(InterfaceGeneralModel $objModel, $strUsername) { }

    /**
     * Set a Version as active.
     *
     * @param mix $mixID The ID of record
     * @param mix $mixVersion The ID of the Version
     */
    public function setVersionActive($mixID, $mixVersion) { }

    /**
     * Return the active version from a record
     *
     * @param mix $mixID The ID of record
     *
     * @return mix Version ID
     */
    public function getActiveVersion($mixID) { }

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

    protected function insertUndo($strSourceSQL, $strSaveSQL, $strTable) { }

}