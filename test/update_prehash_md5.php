<?php

require_once(dirname(__FILE__)."/../bin/config.php");
require_once(WWW_DIR. "lib/framework/db.php");
require_once(WWW_DIR."lib/category.php");
require_once("ColorCLI.php");
require_once("consoletools.php");

$db = new DB();
$consoletools = new ConsoleTools();
$c = new ColorCLI();

if (!isset($argv[1]) || $argv[1] != 'true') {
	exit($c->error("\nThis script will recalculate and update the MD5 column for each pre.\n\n"
					. "php $argv[0] true      ...: To reset every predb MD5.\n"));
}

// Drop the unique index
$has_index = $db->queryDirect("SHOW INDEXES IN prehash WHERE Key_name = 'ix_prehash_md5'");
if ($has_index->rowCount() > 0) {
	echo $c->info("Dropping index ix_prehash_md5.");
	$db->queryDirect("DROP index ix_prehash_md5 ON prehash");
}

$res = $db->queryDirect("SELECT ID, title FROM prehash");
$total = $res->rowCount();
$deleted = $count = 0;
echo $c->header("Updating MD5's on $total preDB's.");
foreach ($res as $row) {
	$name = trim($row['title']);
	$md5 = $db->escapeString(md5($name));
	$title = $db->escapeString($name);

	$db->queryDirect(sprintf("UPDATE prehash SET title = %s, hash = %s WHERE ID = %d", $title, $md5, $row['ID']));
	$consoletools->overWriteHeader("Reset MD5s: " . $consoletools->percentString(++$count, $total));
}

//Re-create the unique index, dropping dupes
echo $c->info("\nCreating index ix_prehash_md5.");
$db->queryDirect("ALTER IGNORE TABLE prehash ADD CONSTRAINT ix_prehash_md5 UNIQUE (hash)");
echo $c->header("\nDone.");