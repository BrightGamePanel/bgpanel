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
 * @copyleft	2012
 * @license		GNU General Public License version 3.0 (GPLv3)
 * @version		(Release 0) DEVELOPER BETA 4
 * @link		http://sourceforge.net/projects/brightgamepanel/
 */



$title = 'Cron Job';
$page = 'cron';


chdir(realpath(dirname(__FILE__)));
set_include_path(realpath(dirname(__FILE__)));


require('../configuration.php');
require('../includes/functions.php');
require('../includes/mysql.php');
require_once('../libs/lgsl/lgsl_class.php');
require_once('../libs/phpseclib/SSH2.php');
require_once("../libs/phpseclib/Crypt/AES.php");


if (is_dir("../install")) //Checks if the install directory has been removed
{
	die();
}

$perms = substr(sprintf('%o', fileperms('../.ssh/passphrase')), -4); //Check PASSPHRASE file CHMOD
###
if ($perms != '0644')
{
	die();
}
unset($perms);

$panelVersion = query_fetch_assoc( "SELECT `value` FROM `".DBPREFIX."config` WHERE `setting` = 'panelversion' LIMIT 1" );
###
if ($panelVersion['value'] != '0.3.5')
{
	die();
}

$maintenance = query_fetch_assoc( "SELECT `value` FROM `".DBPREFIX."config` WHERE `setting` = 'maintenance' LIMIT 1" );
###
if ($maintenance['value']  == '1')
{
	die();
}


error_reporting(E_ALL);
set_time_limit(3600);


//------------------------------------------------------------------------------------------------------------+

query_basic( "UPDATE `".DBPREFIX."config` SET `value` = '".date('Y-m-d H:i:s')."' WHERE `setting` = 'lastcronrun'" );

//------------------------------------------------------------------------------------------------------------+
//------------------------------------------------------------------------------------------------------------+

  lgsl_database();

//------------------------------------------------------------------------------------------------------------+
// CRON SETTINGS:

  $lgsl_config['cache_time'] = 60; // HOW OLD CACHE MUST BE BEFORE IT NEEDS REFRESHING
  $request = "sep";                // WHAT TO PRE-CACHE: [s] = BASIC INFO [e] = SETTINGS [p] = PLAYERS

//------------------------------------------------------------------------------------------------------------+

  $mysql_query  = "SELECT `type`,`ip`,`c_port`,`q_port`,`s_port` FROM `{$lgsl_config['db']['prefix']}{$lgsl_config['db']['table']}` WHERE `disabled`=0 ORDER BY `cache_time` ASC";
  $mysql_result = mysql_query($mysql_query) or die(mysql_error());

  while($mysql_row = mysql_fetch_array($mysql_result, MYSQL_ASSOC))
  {
    echo str_pad(lgsl_timer("taken"),  8,  " ").":".
         str_pad($mysql_row['type'],   15, " ").":".
         str_pad($mysql_row['ip'],     30, " ").":".
         str_pad($mysql_row['c_port'], 6,  " ").":".
         str_pad($mysql_row['q_port'], 6,  " ").":".
         str_pad($mysql_row['s_port'], 12, " ")."\r\n";

    lgsl_query_cached($mysql_row['type'], $mysql_row['ip'], $mysql_row['c_port'], $mysql_row['q_port'], $mysql_row['s_port'], $request);

    //flush();
    //ob_flush();
  }

//------------------------------------------------------------------------------------------------------------+
//------------------------------------------------------------------------------------------------------------+

$data['boxids'] = NULL;
$data['boxnetstat'] = NULL;
$data['players'] = NULL;
$data['cpu'] = NULL;
$data['ram'] = NULL;
$data['loadavg'] = NULL;
$data['hdd'] = NULL;


if (query_numrows( "SELECT `boxid` FROM `".DBPREFIX."box` ORDER BY `boxid`" ) != 0)
{
	$boxes = mysql_query( "SELECT `boxid`, `ip`, `login`, `password`, `sshport` FROM `".DBPREFIX."box`" );

	while ($rowsBoxes = mysql_fetch_assoc($boxes))
	{
		$ssh = new Net_SSH2($rowsBoxes['ip'].':'.$rowsBoxes['sshport']);
		$aes = new Crypt_AES();
		$aes->setKeyLength(256);
		$aes->setKey(CRYPT_KEY);
		if (!$ssh->login($rowsBoxes['login'], $aes->decrypt($rowsBoxes['password'])))
		{
			//Connection Error!

			$data['boxids'] .= $rowsBoxes['boxid'].';';
			$data['boxnetstat'] .= '0;';
			$data['players'] .= '0;';
			$data['cpu'] .= ';';
			$data['ram'] .= ';';
			$data['loadavg'] .= ';';
			$data['hdd'] .= ';';

			query_basic( "UPDATE `".DBPREFIX."box` SET
				`cpu` = 'NULL;0;0',
				`ram` = '0;0;0;0',
				`loadavg` = '0',
				`hostname` = 'NULL',
				`os` = 'NULL',
				`date` = 'NULL',
				`kernel` = 'NULL',
				`arch` = 'NULL',
				`uptime` = 'NULL',
				`swap` = '0;0;0;0',
				`hdd` = '0;0;0;0' WHERE `boxid` = '".$rowsBoxes['boxid']."'" );

			goto end;
		}

		//------------------------------------------------------------------------------------------------------------+
		//We have to clean screenlog.0 files

		$servers = mysql_query( "SELECT `homedir` FROM `".DBPREFIX."server` WHERE `boxid` = '".$rowsBoxes['boxid']."' " );
		###
		while ($rowsServers = mysql_fetch_assoc($servers))
		{
			$ssh->exec('cd '.$rowsServers['homedir'].'; tail -n500 screenlog.0 > screenlog.1; cat screenlog.1 > screenlog.0; rm screenlog.1'."\n");
		}
		unset($servers);

		//------------------------------------------------------------------------------------------------------------+
		//Retrieves information from box

		$cpuOutput = $ssh->exec('top -b -n 1 | grep Cpu'."\n");
		$procModelOutput = $ssh->exec('cat /proc/cpuinfo | grep \'model name\''."\n");
		$procCoresOutput = $ssh->exec('cat /proc/cpuinfo | grep \'cpu cores\''."\n");

		$memOutput = $ssh->exec('free -m | grep \'buffers/cache\''."\n");

		$loadavgOutput = $ssh->exec('top -b -n 1 | grep \'load average\''."\n");

		$hostname = trim($ssh->exec('hostname'."\n"));
		$os = trim($ssh->exec('uname -o'."\n"));
		$date = trim($ssh->exec('date'."\n"));
		$kernel = trim($ssh->exec('uname -r'."\n"));
		$arch = trim($ssh->exec('uname -m'."\n"));

		$uptimeOutput = $ssh->exec('cat /proc/uptime'."\n");

		$swapOutput = $ssh->exec('free -m | grep Swap'."\n");

		$hddOutput = $ssh->exec('df -h | grep \'^/\''."\n");

		//Processing
			$cpuTable = explode(',', $cpuOutput);
			$cpuTable[0] = str_replace('Cpu(s):', '', $cpuTable[0]); //Remove useless chars
			$cpuTable[0] = str_replace('%us', '', $cpuTable[0]);
			###
			$procModelTable = explode("\n", $procModelOutput);
			$procModelTable[0] = str_replace("model name\t: ", '', $procModelTable[0]);
			###
			$procCoresTable = explode("\n", $procCoresOutput);
			$procCoresTable[0] = str_replace("cpu cores\t: ", '', $procCoresTable[0]);
			###
			$cpu = trim($procModelTable[0]).';'.trim($procCoresTable[0]).';'.trim($cpuTable[0]); // Proc Model ; Proc Num Cores ; CPU Usage in percentage
			unset($cpuOutput, $procModelOutput, $procModelTable, $procCoresOutput, $procCoresTable);
		###
			$memTable = explode(' ', $memOutput);
			$n = 0;
			$nMax = count($memTable);
			while ($n < $nMax)
			{
				if (is_numeric(trim($memTable[$n])))
				{
					$memTable2[] = trim($memTable[$n]);
				}
				++$n;
			}
			$mem = ( $memTable2[0] + $memTable2[1] ).';'.$memTable2[0].';'.$memTable2[1].';'.round( (($memTable2[0] * 100) / ($memTable2[0] + $memTable2[1])), 2); // Total Mem Mo ; used Mo ; free Mo ; percentage of used ram
			unset($memOutput, $memTable, $n, $nMax);
		###
			$loadavgTable = explode(',', $loadavgOutput);
			$loadavgTable[4] = trim($loadavgTable[4]);
			$loadavg = $loadavgTable[4];
			unset($loadavgOutput, $loadavgTable);
		###
			$uptimeTable = explode(' ', $uptimeOutput);
			$uptimeMin = $uptimeTable[0] / 60;
			if ($uptimeMin > 59)
			{
				$uptimeH = $uptimeMin / 60;
				if ($uptimeH > 23)
				{
					$uptimeD = $uptimeH / 24;
				}
				else
				{
					$uptimeD = 0;
				}
			}
			else
			{
				$uptimeH = 0;
				$uptimeD = 0;
			}
			$uptime = floor($uptimeD).' days '.($uptimeH % 24).' hours '.($uptimeMin % 60).' minutes ';
			unset($uptimeOutput, $uptimeTable, $uptimeMin, $uptimeH, $uptimeD);
		###
			$swapTable = explode(' ', $swapOutput);
			$n = 0;
			$nMax = count($swapTable);
			while ($n < $nMax)
			{
				if (is_numeric(trim($swapTable[$n])))
				{
					$swapTable2[] = trim($swapTable[$n]);
				}
				++$n;
			}
			$swap = $swapTable2[0].';'.$swapTable2[1].';'.$swapTable2[2].';'.round(( $swapTable2[1] * 100) / ( $swapTable2[0] ), 2); // Total Swap Mo ; used Mo ; free Mo ; percentage of used Swap
			unset($swapOutput, $swapTable, $swapTable2, $n, $nMax);
		###
			$hddTable = explode(' ', $hddOutput);
			$n = 0;
			$nMax = count($hddTable);
			while ($n < $nMax)
			{
				if ( (preg_match("#^[0-9]#", $hddTable[$n]) && (preg_match("#M$#", $hddTable[$n]) || preg_match("#G$#", $hddTable[$n]) || preg_match("#T$#", $hddTable[$n]) || preg_match("#%$#", $hddTable[$n]))) )
				{
					$hddTable2[] = trim($hddTable[$n]);
				}
				++$n;
			}
			$hdd = $hddTable2[0].';'.$hddTable2[1].';'.$hddTable2[2].';'.substr($hddTable2[3], 0, (strlen($hddTable2[3]) - 1)); // Total HDD Mem ; used ; free ; percentage of used HDD
			unset($hddOutput, $hddTable, $n, $nMax);

		//------------------------------------------------------------------------------------------------------------+
		//Retrieves num players of the box

		$p = 0;

		$servers = mysql_query( "SELECT * FROM `".DBPREFIX."server` WHERE `boxid` = '".$rowsBoxes['boxid']."'" );

		while ($rowsServers = mysql_fetch_assoc($servers))
		{
			$type = query_fetch_assoc( "SELECT `querytype` FROM `".DBPREFIX."game` WHERE `gameid` = '".$rowsServers['gameid']."' LIMIT 1");
			$game = query_fetch_assoc( "SELECT `game` FROM `".DBPREFIX."game` WHERE `gameid` = '".$rowsServers['gameid']."' LIMIT 1" );

			if ($rowsServers['status'] == 'Active' && $rowsServers['panelstatus'] == 'Started')
			{
				//---------------------------------------------------------+
				//Querying the server
				include_once("../libs/lgsl/lgsl_class.php");

				$cache = lgsl_query_live($type['querytype'], $rowsBoxes['ip'], NULL, $rowsServers['queryport'], NULL, 'sc');

				$p = $p + $cache['s']['players'];

				unset($cache);
				//---------------------------------------------------------+
			}

			unset($game);
			unset($type);
		}
		unset($servers);

		//------------------------------------------------------------------------------------------------------------+
		//Data

		$data['boxids'] .= $rowsBoxes['boxid'].';';
		$data['boxnetstat'] .= '1;';
		$data['players'] .= $p.';';
		$data['cpu'] .= trim($cpuTable[0]).';';
		$data['ram'] .= round( (($memTable2[0] * 100) / ($memTable2[0] + $memTable2[1])), 2).';';
		$data['loadavg'] .= $loadavg.';';
		$data['hdd'] .= substr($hddTable2[3], 0, (strlen($hddTable2[3]) - 1)).';';

		unset($p, $cpuTable, $memTable2, $hddTable2);

		//------------------------------------------------------------------------------------------------------------+
		//Update DB for the current box

		query_basic( "UPDATE `".DBPREFIX."box` SET
			`cpu` = '".$cpu."',
			`ram` = '".$mem."',
			`loadavg` = '".$loadavg."',
			`hostname` = '".$hostname."',
			`os` = '".$os."',
			`date` = '".$date."',
			`kernel` = '".$kernel."',
			`arch` = '".$arch."',
			`uptime` = '".$uptime."',
			`swap` = '".$swap."',
			`hdd` = '".$hdd."' WHERE `boxid` = '".$rowsBoxes['boxid']."'" );

		unset($cpu, $mem, $loadavg, $hostname, $os, $date, $kernel, $arch, $uptime, $swap, $hdd);

		end:

		sleep(1);
	}
	unset($boxes);

	//------------------------------------------------------------------------------------------------------------+
	//Update dataBox table

	$data['timestamp'] = time();

	query_basic( "INSERT INTO `".DBPREFIX."boxData` SET
		`timestamp` = '".$data['timestamp']."',
		`boxids` = '".$data['boxids']."',
		`boxnetstat` = '".$data['boxnetstat']."',
		`players` = '".$data['players']."',
		`cpu` = '".$data['cpu']."',
		`ram` = '".$data['ram']."',
		`loadavg` = '".$data['loadavg']."',
		`hdd` = '".$data['hdd']."'" );

}

//------------------------------------------------------------------------------------------------------------+
//------------------------------------------------------------------------------------------------------------+

/**
 * 'pChart' table operations
 */

	//---------------------------------------------------------+
	// Remove old data

	$time = time() - (60 * 60 * 24 * 7 + 3600);
	$numOldData = mysql_num_rows(mysql_query( "SELECT `id` FROM `".DBPREFIX."boxData` WHERE `timestamp` < '".$time."'" ));

	if ($numOldData > 0)
	{
		$oldData = mysql_query( "SELECT `id` FROM `".DBPREFIX."boxData` WHERE `timestamp` < '".$time."'" );
		while ($rowsData = mysql_fetch_assoc($oldData))
		{
			query_basic( "DELETE FROM `".DBPREFIX."boxData` WHERE `id` = '".$rowsData['id']."'" );
		}
		unset($oldData);
	}

	//---------------------------------------------------------+
	// Optimize table

	$sql = "OPTIMIZE TABLE `".DBPREFIX."boxData`";

	query_basic( $sql );

	unset($sql);

//------------------------------------------------------------------------------------------------------------+
//------------------------------------------------------------------------------------------------------------+

/**
 * 'log' table operations
 */

	//---------------------------------------------------------+
	// Optimize table

	$sql = "OPTIMIZE TABLE `".DBPREFIX."log`";

	query_basic( $sql );

	unset($sql);

//------------------------------------------------------------------------------------------------------------+
?>