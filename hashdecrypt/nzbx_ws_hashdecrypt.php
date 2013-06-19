<?php
define("ENABLE_ECHO", TRUE);
require_once(dirname(__FILE__)."/../bin/config.php");
require_once(WWW_DIR ."/lib/framework/db.php");
require_once(WWW_DIR ."/lib/releases.php");
require_once(WWW_DIR ."/lib/category.php");


function getReleaseName($md5)
{
    return file_get_contents("http://nzbx.ws/decrypt/x0/?hash=" . $md5);
}

function isPreDBActive()
{
    return getReleaseName("0f4e871131b1d05c0010093021b521ca");
}

if (strlen(isPreDBActive()) < 10 && !strstr(isPreDBActive(), '_') == TRUE) {
    die("PreDB Maintenance");
}

function isInnerActive()
{
    $db = new DB();
    return $db->query(sprintf("SELECT * FROM `groups` WHERE `name` = 'alt.binaries.inner-sanctum'"));
}

$y = isInnerActive();
foreach ($y as $z) {
    if ($z["active"] == 0) {
        die("Please activate group alt.binaries.inner-sanctum");
    }
}

function getReleasez()
{
    $db = new DB();
    return $db->query(sprintf("SELECT * FROM `releases` WHERE `fromname` = 'HaShTaG@nzb.file' AND `name` REGEXP '^[a-fA-F0-9]{32}$' ORDER BY ID DESC LIMIT 0 , 30"));
}

function updaterelease($foundName, $id, $groupname)
{
    $db  = new DB();
    $rel = new Releases();
    $cat = new Category();
    
    $cleanRelName = $rel->cleanReleaseName($foundName);
    $catid        = $cat->determineCategory($groupname, $foundName);
    
    $db->query(sprintf("UPDATE releases SET name = %s,  searchname = %s, categoryID = %d WHERE ID = %d", $db->escapeString($cleanRelName), $db->escapeString($cleanRelName), $catid, $id));
    
}
$results = getReleasez();
foreach ($results as $result) {
    $x = $result['name'];
    if (!strstr($x, '.') == TRUE) {
        if (!strstr($x, ' ') == TRUE) {
            if (!strstr($x, '_') == TRUE) {
                if (!strstr($x, '(') == TRUE) {
                    if (!strstr($x, '-') == TRUE) {
                        $r = getReleaseName($result['name']);
                        if (strlen($r) > 5) {
                            if (!strstr($r, 'cloudflare') == TRUE) {
                                if (strstr($r, '-') == TRUE) {
                                    if (ENABLE_ECHO == TRUE) {
                                        echo "Release found " . $r . "\n";
                                    }
                                    updaterelease($r, $result['ID'], $result['name']);
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
?>