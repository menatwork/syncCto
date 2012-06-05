<?php if (!defined('TL_ROOT')) die('You cannot access this file directly!');

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
 * @copyright  MEN AT WORK 2012
 * @package    syncCto
 * @license    GNU/LGPL
 * @filesource
 */

/**
 * Class SyncCtoCheckBox
 *
 * Provide methods to handle check boxes.
 */
class SyncCtoCheckBox extends CheckBox
{

    /**
     * Generate the widget and return it as string
     * @return string
     */
    public function generate()
    {
        if (!$this->multiple && count($this->arrOptions) > 1)
        {
            $this->arrOptions = array($this->arrOptions[0]);
        }
        
        $state = $this->Session->get('checkbox_groups');

        if ($this->Input->get('cbc'))
        {
            $state[$this->Input->get('cbc')] = (isset($state[$this->Input->get('cbc')]) && $state[$this->Input->get('cbc')] == 1) ? 0 : 1;
        }

        $arrOptionsKeys = array_keys($this->arrOptions);
        
        if (is_array($this->state))
        {
            foreach ($this->state AS $key => $value)
            {
                if (isset($arrOptionsKeys[$key]))
                {
                    $id = 'cbc_' . $this->strId . '_' . standardize($arrOptionsKeys[$key]);
                    $state[$id] = $value;
                }
            }
        }
        $this->Session->set('checkbox_groups', $state);

        return parent::generate();
    }

}

?>