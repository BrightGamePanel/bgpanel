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
 * @version		(Release 0) DEVELOPER BETA 9
 * @link		http://www.bgpanel.net/
 */



//Prevent direct access
if (!defined('LICENSE'))
{
	exit('Access Denied');
}


?>
			<hr>
			<a href="#" class="go-top"><i class="icon-arrow-up icon-white"></i>&nbsp;Go Top</a>
			<footer>
				<div class="pull-left">
					Copyleft - 2013. Released Under <a href="http://www.gnu.org/licenses/gpl.html" target="_blank">GPLv3</a>.<br />
					All images are copyrighted by their respective owners.
				</div>
				<div class="pull-right" style="text-align: right;">
					<a href="http://www.bgpanel.net/" target="_blank">Bright Game Panel</a> @Admin<br />
					Built with <a href="http://getbootstrap.com/2.3.2/" target="_blank">Bootstrap</a>.
				</div>
			</footer>
		</div><!--/container-->

		<!--Powered By Bright Game Panel-->

<?php

if (isAdminLoggedIn() == TRUE)
{
?>
		<script>
		$(document).ready(function() {
			<!-- Header Tooltips -->
			$('#clock').tooltip({placement: 'bottom'});
			$('#notificationsPopover').popover({placement: 'bottom', trigger: 'hover'});
			$('#me').tooltip({placement: 'bottom'});
			$('#logout').tooltip({placement: 'bottom'});
		});
		<!-- nav-scripts -->
		function doScript(id, name, action)
		{
			if (confirm("Are you sure you want to "+action+" script: "+name+"?"))
			{
				if (action == 'launch') { action = 'start'; }
				window.location="scriptprocess.php?task=script"+action+"&scriptid="+id;
			}
		}
		</script>

<?php
}

?>
	</body>
</html>
