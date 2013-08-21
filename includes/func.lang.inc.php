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
 * @version		(Release 0) DEVELOPER BETA 8
 * @link		http://www.bgpanel.net/
 */



//Prevent direct access
if (!defined('LICENSE'))
{
	exit('Access Denied');
}



/**
 * Available Languages
 */
$languages = array(
	'English'	=>	'en_EN',
	'Spanish'	=>	'es_ES',
	'French'	=>	'fr_FR',
	'Dutch'		=>	'nl_NL',
	'Polish'	=>	'pl_PL',
	'Russian'	=>	'ru_RU'
	);

/**
 * Define language for get-text translator
 *
 * Directory structure for traduction must be:
 *		./locale/Lang/LC_MESSAGES/messages.mo
 * Example (French):
 *		./locale/fr_FR/LC_MESSAGES/messages.mo
 */
function defineLanguage($lang)
{
	$encoding = 'UTF-8';

	if (isset($lang)) {
		$locale = $lang;
	} else {
		$locale = DEFAULT_LOCALE;
	}

	// gettext setup
	T_setlocale(LC_MESSAGES, $locale);
	// Set the text domain as 'messages'
	$domain = 'messages';
	T_bindtextdomain($domain, LOCALE_DIR);
	T_bind_textdomain_codeset($domain, $encoding);
	T_textdomain($domain);
}

?>