<?php
// This script is adapted FROM nZEDb
/*
 * This script deletes releases that match certain criteria, type php removeCrapReleases.php false for details.
 */

require_once(dirname(__FILE__)."/../bin/config.php");
require_once(WWW_DIR."lib/framework/db.php");
require_once(WWW_DIR."lib/releases.php");
require_once(WWW_DIR."lib/site.php");
require_once("functions.php");
require_once ("ColorCLI.php");

$c = new ColorCLI();

if (!isset($argv[1]) && !isset($argv[2])) {
	exit($c->error("Run fixReleaseNames.php first to attempt to fix release names. This will miss some releases if you have not set fixReleaseNames to set the release as checked.\n"
					. "php $argv[0] false               ...:To see an explanation of what this script does.\n"
					. "php $argv[0] true full           ...:If you are sure you want to run this script.\n"
					. "\nThe second mandatory argument is the time in hours(ex: 12) to go back, or you can type full.\n"
					. "You can pass 1 optional third argument:\n"
					. "blacklist | executable | gibberish | hashed | installbin | passworded | passwordurl | sample | scr | short | size\n"));
} else if (isset($argv[1]) && $argv[1] == 'false' && !isset($argv[2])) {
	exit($c->primary("blacklist:   deletes releases after applying the configured blacklist regexes.\n"
					. "executable:  deletes releases not in other misc or the apps sections and contain an .exe file\n"
					. "gibberish:   deletes releases where the name is only letters or numbers and is 15 characters or more.\n"
					. "hashed:      deletes releases where the name contains a string of 25 or more numbers or letters.\n"
					. "installbin:  deletes releases which contain an install.bin file\n"
					. "passworded:  deletes releases which contain password or passworded in the search name\n"
					. "passwordurl: deletes releases which contain a password.url file\n"
					. "sample:      deletes releases smaller than 40MB and has more than 1 file and has sample in the name\n"
					. "scr:         deletes releases where .scr extension is found in the files or subject\n"
					. "short:       deletes releases where the name is only numbers or letters and is 5 characters or less\n"
					. "size:        deletes releases smaller than 1MB and has only 1 file not in mp3/books\n\n"
					. "php $argv[0] true full             ...: To run all the above\n"
					. "php $argv[0] true full gibberish   ...: To run only this type\n"));
}

if (isset($argv[1]) && !is_numeric($argv[1]) && isset($argv[2]) && $argv[2] == 'full') {
	echo $c->header("Removing crap releases - no time limit.");
	$and = '';
} else if (isset($argv[1]) && isset($argv[2]) && is_numeric($argv[2])) {
	echo $c->header('Removing crap releases from the past ' . $argv[2] . " hour(s).");
	$db = new DB();
		$and = ' AND adddate > (NOW() - INTERVAL ' . $argv[2] . ' HOUR) ORDER BY ID ASC';
} else if (!isset($argv[2]) || $argv[2] !== 'full' || !is_numeric($argv[2])) {
	exit($c->error("\nERROR: Wrong second argument.\n"));
}
$delete = 0;
if (isset($argv[1]) && $argv[1] == 'true') {
	$delete = 1;

	function deleteReleases($sql, $type)
	{
	        global $delete;
		$releases = new Releases();
        $functions = new Functions ();
		$s = new Sites();
		$site = $s->get();
        $c = new ColorCLI();
		$delcount = 0;
		foreach ($sql as $rel)
		{
		  if ($delete == 1)
		        {
        			echo $c->primary('Deleting: ' . $type . ': ' . $rel['searchname']);
			        $functions->fastDelete($rel['ID'], $rel['guid'], $site);
			}
			else
			        echo $c->primary('Would be deleting: ' . $type . ': ' . $rel['searchname']);
			$delcount++;
		}
		return $delcount;
	}

	// 15 or more letters or numbers, nothing else.
	function deleteGibberish($and)
	{
		$type = "Gibberish";
		$db = new DB();
		$sql = $db->query("SELECT ID, guid, searchname FROM releases WHERE searchname REGEXP '^[a-zA-Z0-9]{15,}$' AND releasenfoID IN (0,-1) AND (proc_files = 1 OR proc_par2 = 1 OR proc_nfo = 1) AND rarinnerfilecount >= 0".$and);
		$delcount = deleteReleases($sql, $type);
		return $delcount;
	}

	// 25 or more letters/numbers, probably hashed.
	function deleteHashed($and)
	{
		$type = "Hashed";
		$db = new DB();
		$sql = $db->query("SELECT ID, guid, searchname FROM releases WHERE searchname REGEXP '[a-zA-Z0-9]{25,}' AND releasenfoID IN (0,-1) AND ishashed = 1 AND (proc_files = 1 OR proc_par2 = 1 OR proc_nfo = 1) AND rarinnerfilecount >= 0".$and);
		$delcount = deleteReleases($sql, $type);
		return $delcount;
	}
	
	// 5 or less letters/numbers.
	function deleteShort($and)
	{
		$type = "Short";
		$db = new DB();
		$sql = $db->query("SELECT ID, guid, searchname FROM releases WHERE searchname REGEXP '^[a-zA-Z0-9]{0,5}$' AND releasenfoID = 0 AND iscategorized = 1 AND rarinnerfilecount >= 0 AND (proc_files = 1 OR proc_par2 = 1 OR proc_nfo = 1)".$and);
		$delcount = deleteReleases($sql, $type);
		return $delcount;
	}
	
	// Anything with an exe not in other misc or pc apps/games.
	function deleteExecutable($and)
	{
		$type = "Executable";
		$db = new DB();
		$sql = $db->query('SELECT r.ID, r.guid, r.searchname FROM releases r INNER JOIN releasefiles rf ON rf.releaseID = r.ID WHERE r.searchname NOT LIKE "%.exes%" AND rf.name LIKE "%.exe%" AND r.categoryID NOT IN (4000, 4010, 4020, 4050)'.$and);
		$delcount = deleteReleases($sql, $type);
		return $delcount;
	}

	// Anything with an install.bin file.
	function deleteInstallBin($and)
	{
		$type = "install.bin";
		$db = new DB();
		$sql = $db->query('SELECT r.ID, r.guid, r.searchname FROM releases r INNER JOIN releasefiles rf ON rf.releaseID = r.ID WHERE rf.name LIKE "%install.bin%"'.$and);
		$delcount = deleteReleases($sql, $type);
		return $delcount;
	}
	
	// Anything with a password.url file.
	function deletePasswordURL($and)
	{
		$type = "PasswordURL";
		$db = new DB();
		$sql = $db->query('SELECT r.ID, r.guid, r.searchname FROM releases r INNER JOIN releasefiles rf ON rf.releaseID = r.ID WHERE rf.name LIKE "%password.url%"'.$and);
		$delcount = deleteReleases($sql, $type);
		return $delcount;
	}
	
	// Password in the searchname
	function deletePassworded($and)
	{
		$type = "Passworded";
		$db = new DB();
		$sql = $db->query("SELECT ID, guid, searchname FROM releases WHERE ( searchname LIKE '%passworded%' OR searchname LIKE '%password protect%' OR searchname LIKE '%password%' OR searchname LIKE '%passwort%' OR searchname LIKE '%[pw]%' ) AND searchname NOT LIKE '%no password%' AND searchname NOT LIKE '%not passworded%' AND searchname NOT LIKE '%unlocker%' AND searchname NOT LIKE '%reset%' AND searchname NOT LIKE '%recovery%' AND searchname NOT LIKE '%keygen%' AND searchname NOT LIKE '%advanced%' AND categoryID not in (4000, 4010, 4020, 4030, 4040, 4050, 4060, 4070)".$and);
		$delcount = deleteReleases($sql, $type);
		return $delcount;
	}
	
	// Anything that is 1 part and smaller than 1MB and not in MP3/books.
	function deleteSize($and)
	{
		$type = "Size";
		$db = new DB();
		$sql = $db->query("SELECT ID, guid, searchname FROM releases WHERE totalPart = 1 AND size < 1000000 AND categoryID not in (7000, 7010, 7020, 7030, 3010)".$and);
		$delcount = deleteReleases($sql, $type);
		return $delcount;
	}

	// More than 1 part, less than 40MB, sample in name. TV/Movie sections.
	function deleteSample($and)
	{
		$type = "Sample";
		$db = new DB();
		$sql = $db->query('SELECT ID, guid, searchname FROM releases WHERE totalPart > 1 AND name LIKE "%sample%" AND size < 40000000 AND categoryID IN (5020, 5030, 5040, 5050, 5060, 5070, 5080, 2020, 2030, 2040, 2050, 2060)'.$and);
		$delcount = deleteReleases($sql, $type);
		return $delcount;
	}
	
	// Anything with a scr file in the filename/subject.
	function deleteScr($and)
	{
		$type = ".scr";
		$db = new DB();
		$sql = $db->query("SELECT r.ID, r.guid, r.searchname FROM releases r LEFT JOIN releasefiles rf ON rf.releaseID = r.ID WHERE (rf.name REGEXP '[.]scr$' OR r.name REGEXP '[.]scr[$ \"]')".$and);
		$delcount = deleteReleases($sql, $type);
		return $delcount;
	}

	// Use the site blacklists to delete releases.
	function deleteBlacklist($and)
	{
		$type = "Blacklist";
        $groups = new Groups();
        $functions = new Functions();
		$db = new DB();
		$regexes = $db->queryDirect('SELECT regex, ID, groupname FROM binaryblacklist WHERE status = 1 AND optype = 1');
		$delcount = 0;
        $count = count($regexes);
		if($count > 0)
		{
			foreach ($regexes as $regex)
			{
				$regexsql = "(rf.name REGEXP " . $db->escapeString($regex['regex']) . " OR r.name REGEXP " . $db->escapeString($regex['regex']) . ")";

			// Get the group ID if the regex is set to work against a group.
			$groupID = '';
			if (strtolower($regex['groupname']) !== 'alt.binaries.*') {
				$groupID = $functions->getIDByName($regex['groupname']);
				$groupID = ($groupID === '' ? '' : ' AND r.groupID = ' . $groupID . ' ');
			}

			$sql = $db->prepare("SELECT r.ID, r.guid, r.searchname FROM releases r LEFT JOIN releasefiles rf ON rf.releaseID = r.ID WHERE {$regexsql} " . $groupID . $and);
			$sql->execute();
			$delcount += deleteReleases($sql, 'Blacklist ' . $regex['ID']);
		}
	}
	return $delcount;
}

	$totalDeleted = $gibberishDeleted = $hashedDeleted = $shortDeleted = $executableDeleted = $installBinDeleted = $PURLDeleted = $PassDeleted = $sizeDeleted = $sampleDeleted = $scrDeleted = $blacklistDeleted = 0;
	
   if (isset($argv[3])) {
	switch ($argv[3]) {
		case 'gibberish':
			$gibberishDeleted = deleteGibberish($and);
			break;
		case 'hashed':
			$hashedDeleted = deleteHashed($and);
			break;
		case 'short':
			$shortDeleted = deleteShort($and);
			break;
		case 'executable':
			$executableDeleted = deleteExecutable($and);
			break;
		case 'installbin':
			$installBinDeleted = deleteInstallBin($and);
			break;
		case 'passwordurl':
			$PURLDeleted = deletePasswordURL($and);
			break;
		case 'passworded':
			$PURLDeleted = deletePassworded($and);
			break;
		case 'size':
			$sizeDeleted = deleteSize($and);
			break;
		case 'sample':
			$sampleDeleted = deleteSample($and);
			break;
		case 'scr':
			$scrDeleted = deleteScr($and);
			break;
		case 'blacklist':
			$blacklistDeleted = deleteBlacklist($and);
			break;
		default:
			exit("Wrong third argument.\n");
	}
} else {
	$gibberishDeleted = deleteGibberish($and);
	$hashedDeleted = deleteHashed($and);
	$shortDeleted = deleteShort($and);
	$executableDeleted = deleteExecutable($and);
	$installBinDeleted = deleteInstallBin($and);
	$PURLDeleted = deletePasswordURL($and);
	$PassDeleted = deletePassworded($and);
	$sizeDeleted = deleteSize($and);
	$sampleDeleted = deleteSample($and);
	$scrDeleted = deleteScr($and);
	$blacklistDeleted = deleteBlacklist($and);
}

	$totalDeleted = $totalDeleted+$gibberishDeleted+$hashedDeleted+$shortDeleted+$executableDeleted+$installBinDeleted+$PURLDeleted+$PassDeleted+$sizeDeleted+$sampleDeleted+$scrDeleted+$blacklistDeleted;

	if ($totalDeleted > 0)
	{
	   echo $c->header("Total Removed: " . $totalDeleted);
		if ($gibberishDeleted > 0) {
			echo $c->primary("Gibberish    : " . $gibberishDeleted);
		}
		if ($hashedDeleted > 0) {
			echo $c->primary("Hashed       : " . $hashedDeleted);
		}
		if ($shortDeleted > 0) {
			echo $c->primary("Short        : " . $shortDeleted);
		}
		if ($executableDeleted > 0) {
			echo $c->primary("Executable   : " . $executableDeleted);
		}
		if ($installBinDeleted > 0) {
			echo $c->primary("install.bin  : " . $installBinDeleted);
		}
		if ($PURLDeleted > 0) {
			echo $c->primary("PURL         : " . $PURLDeleted);
		}
		if ($PassDeleted > 0) {
			echo $c->primary("Passworded : " . $PassDeleted);
		}
		if ($sizeDeleted > 0) {
			echo $c->primary("Size         : " . $sizeDeleted);
		}
		if ($sampleDeleted > 0) {
			echo $c->primary("Sample       : " . $sampleDeleted);
		}
		if ($scrDeleted > 0) {
			echo $c->primary(".scr         : " . $scrDeleted);
		}
		if ($blacklistDeleted > 0) {
			echo $c->primary("Blacklist    : " . $blacklistDeleted);
		}
	}  else {
	exit($c->info("Nothing was found to delete."));
}
}