<?php
//This inserts the patches into MySQL and PostgreSQL.

require_once(dirname(__FILE__)."/../../bin/config.php");
require_once (WWW_DIR.'/lib/site.php');
require_once("../ColorCLI.php");
require_once(WWW_DIR.'/lib/Tmux.php');
require_once(WWW_DIR.'/lib/smarty/Smarty.class.php'); 

// Function inspired by : http://stackoverflow.com/questions/1883079/best-practice-import-mysql-file-in-php-split-queries/2011454#2011454
function SplitSQL($file, $delimiter = ';')
{
	set_time_limit(0);

	if (is_file($file) === true) {
		$file = fopen($file, 'r');

		if (is_resource($file) === true) {
			$query = array();
			$db = new DB();
			$dbsys = DB_TYPE;
			$c = new ColorCLI();

			while (feof($file) === false) {
				$query[] = fgets($file);
				if (preg_match('~' . preg_quote($delimiter, '~') . '\s*$~iS', end($query)) === 1) {
					$query = trim(implode('', $query));

					if ($dbsys == "pgsql") {
						$query = str_replace(array("`", chr(96)), '', $query);
					}
					try {
						$qry = $db->prepare($query);
						$qry->execute();
						echo $c->alternateOver('SUCCESS: ') . $c->primary($query);
					} catch (PDOException $e) {
						if ($e->errorInfo[1] == 1091 || $e->errorInfo[1] == 1060 || $e->errorInfo[1] == 1054 || $e->errorInfo[1] == 1061 || $e->errorInfo[1] == 1062 || $e->errorInfo[1] == 1071 || $e->errorInfo[1] == 1072 || $e->errorInfo[1] == 1146 || $e->errorInfo[0] == 23505 || $e->errorInfo[0] == 42701 || $e->errorInfo[0] == 42703 || $e->errorInfo[0] == '42P07' || $e->errorInfo[0] == '42P16') {
							if ($e->errorInfo[1] == 1060) {
								echo $c->error($query . " The column already exists - Not Fatal {" . $e->errorInfo[1] . "}.\n");
							} else {
								echo $c->error($query . " Skipped - Not Fatal {" . $e->errorInfo[1] . "}.\n");
							}
						} else {
							if (preg_match('/ALTER IGNORE/i', $query)) {
								$db->queryExec("SET SESSION old_alter_table = 1");
								try {
									$qry = $db->prepare($query);
									$qry->execute();
									echo $c->alternateOver('SUCCESS: ') . $c->primary($query);
								} catch (PDOException $e) {
									exit($c->error($query . " Failed {" . $e->errorInfo[1] . "}\n\t" . $e->errorInfo[2]));
								}
							} else {
								exit($c->error($query . " Failed {" . $e->errorInfo[1] . "}\n\t" . $e->errorInfo[2]));
							}
						}
					}

					while (ob_get_level() > 0) {
						ob_end_flush();
					}
					flush();
				}

				if (is_string($query) === true) {
					$query = array();
				}
			}
			return fclose($file);
		} else {
			return false;
		}
	} else {
		return false;
	}
}

function BackupDatabase()
{
	$db = new DB();
	$c = new ColorCLI();
	$DIR = dirname (__FILE__);

	if (Util::hasCommand("php5")) {
		$PHP = "php5";
	} else {
		$PHP = "php";
	}

	//Backup based on database system
	if ($db->dbSystem() == "mysql") {
		system("$PHP ${DIR}mysqldump_tables.php db dump ../../");
	} else if ($db->dbSystem() == "pgsql") {
		exit($c->error("Currently not supported on this platform."));
	}
}

$os = (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') ? "windows" : "unix";

if (isset($argv[1]) && $argv[1] == "safe") {
	$safeupgrade = true;
} else {
	$safeupgrade = false;
}

if (isset($os) && $os == "unix") {
	$t = new Tmux();
	$tmux = $t->get();
	$currentversion = $tmux->sqlpatch;
	$patched = 0;
	$patches = array();
	$db = new DB();
	$backedup = false;
	$c = new ColorCLI();
    $DIR = dirname (__FILE__);
    $path = $DIR.'/patches/';


	// Open the patch folder.
	if ($handle = @opendir($path)) {
		while (false !== ($patch = readdir($handle))) {
			$patches[] = $patch;
		}
		closedir($handle);
	} else {
		exit($c->error("\nHave you changed the path to the patches folder, or do you have the right permissions?\n"));
	}

	/* 	if ($db->dbSystem() == "mysql")
	  $patchpath = preg_replace('/\/misc\/testing\/DB/i', '/db/patches/mysql/',
	nZEDb_ROOT);
	  else if ($db->dbSystem() == "pgsql")
	  $patchpath = preg_replace('/\/misc\/testing\/DB/i', '/db/patches/pgsql/', nZEDb_ROOT);
	 */ sort($patches);

	foreach ($patches as $patch) {
		if (preg_match('/\.sql$/i', $patch)) {
			$filepath = $path . $patch;
			$file = fopen($filepath, "r");
			$patch = fread($file, filesize($filepath));
			if (preg_match('/UPDATE `?tmux`? SET `?value`? = \'?(\d{1,})\'? WHERE `?setting`? = \'sqlpatch\'/i', $patch, $patchnumber)) {
				if ($patchnumber['1'] > $currentversion) {
					if ($safeupgrade == true && $backedup == false) {
						BackupDatabase();
						$backedup = true;
					}
					SplitSQL($filepath);
					$patched++;
				}
			}
		}
	}
} else if (isset($os) && $os == "windows") {
	$t = new Tmux();
	$tmux = $t->get();
	$currentversion = $tmux->sqlpatch;
	$patched = 0;
	$patches = array();

	// Open the patch folder.
	if (!isset($argv[1])) {
		exit($c->error("\nYou must supply the directory to the patches.\n"));
	}
	if ($handle = @opendir($argv[1])) {
		while (false !== ($patch = readdir($handle))) {
			$patches[] = $patch;
		}
		closedir($handle);
	} else {
		exit($c->error("\nHave you changed the path to the patches folder, or do you have the right permissions?\n"));
	}

	sort($patches);
	foreach ($patches as $patch) {
		if (preg_match('/\.sql$/i', $patch)) {
			$filepath = $argv[1] . $patch;
			$file = fopen($filepath, "r");
			$patch = fread($file, filesize($filepath));
			if (preg_match('/UPDATE `?tmux`? SET `?value`? = \'?(\d{1,})\'? WHERE `?setting`? = \'sqlpatch\'/i', $patch, $patchnumber)) {
				if ($patchnumber['1'] > $currentversion) {
					if ($safeupgrade == true && $backedup == false) {
						BackupDatabase();
						$backedup = true;
					}
					SplitSQL($filepath);
					$patched++;
				}
			}
		}
	}
} else {
	exit($c->error("\nUnable to determine OS.\n"));
}

if ($patched == 0) {
	exit($c->info("Nothing to patch, you are already on patch version " . $currentversion));
}
if ($patched > 0) {
	echo $c->header($patched . " patch(es) applied.");
	$smarty = new Smarty;
	$cleared = $smarty->clearCompiledTemplate();
	if ($cleared) {
		echo $c->header("The smarty template cache has been cleaned for you");
	} else {
		echo $c->header("You should clear your smarty template cache at: " . SMARTY_DIR . "templates_c");
	}
}