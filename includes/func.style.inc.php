<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * LICENSE:
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *
 * @categories	Games/Entertainment, Systems Administration
 * @package		Bright Game Panel
 * @author		warhawk3407 <warhawk3407@gmail.com> @NOSPAM
 * @copyleft	2013
 * @license		GNU General Public License version 3.0 (GPLv3)
 * @version		(Release 0) DEVELOPER BETA 5
 * @link		http://www.bgpanel.net/
 */



//Prevent direct access
if (!defined('LICENSE'))
{
	exit('Access Denied');
}



/**
 * Format Bootstrap Icons
 *
 * Cyborg & Slate templates are dark so in order to see icons, we have to mark them as "white"
 */
function formatIcon()
{
	switch (TEMPLATE)
	{
		case 'cyborg.css':
			return 'icon-white';

		case 'slate.css':
			return 'icon-white';

		default:
			return '';
	}
}



/**
 * TableSorter Stylesheet Chooser
 *
 * Dark templates have a specific tablesorter stylesheet
 */
function formatTableSorter()
{
	switch (TEMPLATE)
	{
		case 'cyborg.css':
			return 'sorter-dark.css';

		case 'slate.css':
			return 'sorter-dark.css';

		default:
			return 'sorter.css';
	}
}



/**
 * Format the status
 *
 * Online / Offline -- Active / Inactive / Suspended / Pending -- Started / Stopped
 */
function formatStatus($status)
{
	switch ($status)
	{
		case 'Active':
			return "<span class=\"label label-success\">Active</span>";

		case 'Inactive':
			return "<span class=\"label\">Inactive</span>";

		case 'Suspended':
			return "<span class=\"label label-warning\">Suspended</span>";

		case 'Pending':
			return "<span class=\"label label-warning\">Pending</span>";

		case 'Online':
			return "<span class=\"label label-success\">Online</span>";

		case 'Offline':
			return "<span class=\"label label-important\">Offline</span>";

		case 'Started':
			return "<span class=\"label label-success\">Started</span>";

		case 'Stopped':
			return "<span class=\"label label-warning\">Stopped</span>";

		default:
			return "<span class=\"label\">Default</span>";
	}
}

?>