<?php

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
 * Ping the system
 */
$openurl = $_GET['hostIP'];
$timeout = 5;
$old = ini_set('default_socket_timeout', $timeout);
$handle = @fopen($openurl, "r");
if ($handle) {
	echo 'true';
	return;
}
$openurl = str_replace('GPL.txt', 'index.php', openurl);
$handle = @fopen($openurl, "r");
echo ($handle) ? 'true' : 'false';
@fclose($handle);

?>