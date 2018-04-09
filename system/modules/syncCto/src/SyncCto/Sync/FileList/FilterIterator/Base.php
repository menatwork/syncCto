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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2014-2018 MEN AT WORK.
 * @license    https://github.com/menatwork/syncCto/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace SyncCto\Sync\FileList\FilterIterator;

use FilterIterator;
use Iterator;

class Base extends FilterIterator
{
    /**
     * The states for the filter.
     *
     * @var array|null The values.
     */
    protected $states;

    /**
     * The transmissions for the filter.
     *
     * @var array|null The values.
     */
    protected $transmissions;

    /**
     * Construct.
     *
     * @param Iterator $iterator      The iterator.
     *
     * @param array    $states        The state to be filtered for.
     *
     * @param array    $transmissions The transmission to be filtered for.
     */
    public function __construct(Iterator $iterator, $states, $transmissions)
    {
        // Init via parent.
        parent::__construct($iterator);

        // Set the filter values.
        $this->states        = $states;
        $this->transmissions = $transmissions;
    }

    /**
     * Get the state flag. Each file which have not this state
     * will be removed from the list.
     *
     * @return array|null The values.
     */
    public function getStates()
    {
        return $this->states;
    }

    /**
     * Get the transmission flag. Each file which have not this state
     * will be removed from the list.
     *
     * @return array|null The values.
     */
    public function getTransmissions()
    {
        return $this->transmissions;
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Check whether the current element of the iterator is acceptable
     *
     * @link http://php.net/manual/en/filteriterator.accept.php
     * @return bool true if the current element is acceptable, otherwise false.
     */
    public function accept()
    {
        // Get some data.
        $file                = $this->getInnerIterator()->current();
        $filterTransmissions = $this->getTransmissions();
        $filterStates        = $this->getStates();

        // Check transmission if not null.
        if ($filterTransmissions !== null && !\in_array($file['transmission'], $this->getTransmissions())) {
            return false;
        }

        // Check the state if not null.
        if ($filterStates !== null && !\in_array($file['state'], $this->getStates())) {
            return false;
        }

        // Return true.
        return true;
    }
}
