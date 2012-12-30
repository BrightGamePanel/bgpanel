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


?>
			<hr>
			<footer>
				<div class="pull-left">
					Copyleft - 2012. Released Under <a href="http://www.gnu.org/licenses/gpl.html" target="_blank">GPLv3</a>.<br />
					All Images Are Copyrighted By Their Respective Owners.
				</div>
				<div class="pull-right" style="text-align: right;">
					<a href="http://www.bgpanel.net/" target="_blank">Bright Game Panel</a> @Client<br />
					Built with <a href="http://twitter.github.com/bootstrap/index.html" target="_blank">Bootstrap</a>.
				</div>
			</footer>
		</div><!--/container-->

		<!--Powered By Bright Game Panel-->

<?php

if (isClientLoggedIn() == TRUE)
{
?>
		<script type="text/javascript">
		$(document).ready(function() {
			<!-- Header Tooltips -->
			$('#gototop').tooltip({placement: 'bottom'});
			$('#clock').tooltip({placement: 'bottom'});
		});
		</script>

<?php
}

?>
	</body>
</html>
