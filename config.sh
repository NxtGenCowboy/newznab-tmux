#!/usr/bin/env bash
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

#Copy this file to defaults.sh
#DO NOT EDIT THIS FILE

#########################EDIT THESE#########################
############################################################

#This is the shutdown, true/false
#on, it runs, off and no scripts will be RESTARTED, when all panes are DEAD, killall tmux
#if this is set to false, the script will run 1 loop and terminate
export RUNNING="true"

#these scripts set the 'nice'ness of each script, default is 19, the lowest, the highest is -20
#anything between -1 and -20 require root/sudo to run
export NICENESS="10"

#these scripts can add some serious load to your system, without proper monitoring it can be
#to much, you can set the max load that any pane will be started at
#for example, if you set load to 2, no pane will start when your system load exceeds 2
#this does not mean the the desired load will not be exceeded, just that no panes will be be restarted
#this one is for all panes except update_releases
export MAX_LOAD="2.0"

#this one is for update_releases
export MAX_LOAD_RELEASES="2.0"

############################################################

#Set paths
export NEWZPATH="/var/www/newznab"

#Should not need to change
export NEWZNAB_PATH=$NEWZPATH"/misc/update_scripts"
export TESTING_PATH=$NEWZPATH"/misc/testing"
export ADMIN_PATH=$NEWZPATH"/www/admin"

############################################################

#Post Processing Additional is the post processing that downloads rar files and attempts to get info for your site
#you are able to set the number of processes to be run from 1-32, remember that each process uses 1 of your nntp connections
#so, if you have 20, and you set this to 32, you will have errors, lots of errors, nfo lookup uses 1-3 connections each
#binaries and backfill threaded default up to 10 connections each and predb uses 1, so understand how many connections you are using when setting
#trial and error for this, set to 1 will run > 0, set to 2 will run > 100, 3 will run > 200 and so on.
#At some point, increasing this begins to slow things down. It will need to be adjusted for your system
#to get the desired performance, 0 will disable all post processing, but not category processing
#the first window has up to 16 postprocess and can use primary or alternate NNTP provider
export POST_TO_RUN_A="0"

#The second window also has 16 processes and can use promary or alternate NNTP provider
export POST_TO_RUN_B="0"

#by modifying www/config.php like http://pastebin.com/VgH9DCZw, you can use 1 provider to run update_binaries
#and backup and another provider to run post processing with. Or, 1 provider to run up to 16 postprocesses and another to run
#up to 16 more postprocesses, or the same provider for everything
#you can not switch providers without resetting all groups and truncating, I have included a script in scripts folders to reset and truncate
#sudo scripts/reset_truncate.php
#it is not necessary to run reset_truncate.php in order to second nntp provider for postprocessing only

#this one sets 1 provider for everything(false), or first provider for update_binaries and backfill and another for postprocessing(true)
#this can not be changed after starting scripts
export USE_TWO_NNTP="false"

#this allows you split the 32 postprocessing into 2 separate providers
#this can not be changed after starting scripts
export USE_TWO_PP="false"

############################################################

#post processing per category, setting the above to 0 does not disable these
#this now takes 0 for none, 1 for the first processor or 2 for both processors
#run processNfos
export NFOS="0"

#run processGames
export GAMES="0"

#run processMovies
export MOVIES="0"

#run processMusic
export MUSIC="0"

#run processEbook
export EBOOK="0"

#these are true/false
#run processTV
export TVRAGE="false"

#run processOther
export OTHERS="false"

#run processUnwanted
export UNWANTED="false"

############################################################

#Enter the session name to be used by tmux, no spaces allowed in the name, this can be changed after scripts start
#if you are running multiple servers, you could put your hostname here
export TMUX_SESSION="Newznab"

#Set, in seconds - how often the monitor.php (left top pane) script should update run the queries against the database
#the monitor script will update itself and each pane, once every 5 seconds plus the lagg time time on the loop the db is queried
#to press the point, the db is not queried any sooner than the time set below, but the script loops each iteration once every 5 seconds
#this makes it more responsive to stop/kill without slamming the db with needless queries, starts are still controlled by the queries
export MONITOR_UPDATE="30"

#you may want to kill the update_binaries, backfill and import if no releases have been add in x minutes, set the timer to anything other than 0 to enable
#this will only run every 5 loops of monitor
export KILL_UPDATES="0"

#do you want to just cause it to restart or keep off until a release is created, true for off until a release is created
#this will stop all downloads and imports
export KEEP_KILLED="false"

############################################################

#You can have backfill loop constantly and interject binaries every so often
#by setting this next to true, if true, the normal backfill pane will be dead
#this works by setting the 2 start timers and which is run at the start of the loop is determined like this
#if at the start of the loop, the BINARIES_SEQ_TIMER has expired, then update_binaries will run and the BINARIES_SEQ_TIMER timer is reset
#if BINARIES_SEQ_TIMER has not expired, then if BACKFILL_SEQ_TIMER has expired, the backfill will run and BACKFILL_SEQ_TIMER timer is reset
export SEQUENTIAL="false"

#time between loop start for update_binaries, in seconds, this is a countdown timer, not a sleep after it runs
#default is 30 minutes
#will run on first loop and then not again for at least 1800 seconds
export BINARIES_SEQ_TIMER="1800"

#this will not run on first loop, time between loop start for backfill, in seconds
#default is 10 seconds, this will run after time has expired, binaries will take precedence and run before this, if its time has expired
export BACKFILL_SEQ_TIMER="10"

############################################################

#Choose to run update_binaries true/false
export BINARIES="false"

#Choose to run the threaded or non-threaded newznab binaries scripts true/false
#update_binaries.php or update_binaries_threaded.php
export BINARIES_THREADS="false"

#Set, in seconds - how long the update_binaries should sleep between runs
#top right pane
#sleep timers are not used when using SEQ
export BINARIES_SLEEP="40"

#Set the max amount of unprocessed releases and still allow update_binaries to run
#set to 0 to disable
export BINARIES_MAX_RELEASES="0"

#Set the max amount of binaries in the binaries table and still allow update_binaries to run, this is unprocessed binaries, not the total count
#only usefull when used with ugo's automake_threaded.php
#set to 0 to disable
export BINARIES_MAX_BINS="0"

#Set the max amount of of rows in the parts table and still allow update_binaries to run
#set to 0 to disable
export BINARIES_MAX_ROWS="0"

############################################################

#Choose to run backfill script true/false
export BACKFILL="false"

#Choose to run the threaded or non-threaded newznab backfill scripts true/false
#backfill.php or backfill_threaded.php
export BACKFILL_THREADS="false"

#Set, in seconds - how long the backfill should sleep between runs
#in pane below update_binaries
#sleep timers are not used when using SEQ
export BACKFILL_SLEEP="40"

#Set the max amount of unprocessed releases and still allow backfill to run
#set to 0 to disable
export BACKFILL_MAX_RELEASES="0"

#Set the max amount of binaries in the binaries table and still allow backfill to run, this is unprocessed binaries, not the total count
#only usefull when used with ugo's automake_threaded.php
#set to 0 to disable
export BACKFILL_MAX_BINS="0"

#Set the max amount of of rows in the parts table and still allow backfill to run
#set to 0 to disable
export BACKFILL_MAX_ROWS="0"

#Set the maximum days to backfill, you set the Newznab+ admin/edit backfill to 1
#this will increment your database by 1 after each backfill loop
#once your backfill numbers reach $MAXDAYS, then it will no long increment the database
#backfill will continue to run, and do no work, at that point you should disable backfill, below
export MAXDAYS="210"

############################################################

#use kevin123's safer_backfill_parts.php instead of normal backfill or backfill threaded
#this is the script I use, it does 1 group at a time from z to a (wanted to start with tv groups first) 100k parts,
#then the script stops (once per loop), if your first_record_postdate on the group is 2012-06-24
#it will be skipped (target reached). When that group is done, it will do another ( again from z to a).
#this does not use increment, it works by the date set below
#you also need to enable kevin's compression mod, those files are needed and you still need to enable BACKFILL
export KEVIN_SAFER="false"

#use kevin123's backfill_parts.php instead of normal backfill
export KEVIN_BACKFILL_PARTS="false"

#use kevin123's backfill_parts_threaded.php instead of normal backfill_threaded
export KEVIN_THREADED="false"

#set the date to go back to, must be in the format of YYYY-MM-DD, like 2012-06-24, this is the date of the posted nzbs
export KEVIN_DATE="2012-06-24"

#set the number of articles/headers to download at one time
export KEVIN_PARTS="100000"

############################################################

#Set the path to the nzb dump you downloaded from torrents, this is the path to bulk files folder of nzbs
#this does not recurse through subfolders, unless you set NZB_THREADS to true
#this must be a valid path
export NZBS="/path/to/nzbs"

#Choose to run import nzb script true/false
export IMPORT="false"

#If, you have all of your nzbs in one folder select false
#If, you have all of you nzbs split into separate folders, with the root at $NZBS then select true
#and 10 nzbs will be imported from each subfolder per loop.
#Importing this way, allows all post processing scripts to run, such as book, music, movies
#Instead of doing all 1 type at once, spreads the work load
export NZB_THREADS="false"

#How many nzbs to import per loop, if using NZB_THREADS=true the per folder
export NZBCOUNT="10"

#Set, in seconds - how long the nzb-import should sleep between runs
#below backfill
export IMPORT_SLEEP="40"

#Set the max amount of unprocessed releases and still allow nzb-import to run
#set to 0 to disable
export IMPORT_MAX_RELEASES="0"

#Set the max amount of of rows in the parts table and still allow nzb-import to run
#set to 0 to disable
export IMPORT_MAX_ROWS="0"

#import nzbs using the filename as the release name true/false
export IMPORT_TRUE="false"

############################################################

#MAX_RELEASES for each can be calculated on the total post processing or just the Misc category
#to calculate on just the Misc, enable this
export MISC_ONLY="false"

############################################################

#Create releases, this is really only necessary to turn off when you only want to post process
export RELEASES="false"

#Set, in seconds - how long the update_release should sleep between runs
#bottom right
export RELEASES_SLEEP="40"

############################################################

#Choose to run optimize_innodb.php or optimize_mysiam.php script true/false
#set to false by default, you should test the optimize scripts in bin first
#optimize_myisam on small tables runs after every 5th loop of update_releases
export OPTIMIZE="false"

#optimize can wait, patiently while all other panes stop and then run
#or, forcefully terminate all panes while it runs, to kill all panes and run optimize, enable
export OPTIMIZE_KILL="false"

#How often to run optimize_myisam on small tables seconds, default is 10 min
export MYISAM_SMALL="600"

#How often to run optimize_myisam on large tables seconds, default is 1 hr
export MYISAM_LARGE="3600"

#How often to run optimize_innodb on small tables in seconds, default is 2 hr
export INNODB_SMALL="7200"

#How often to run optimize_innodb on large tables in seconds, default is 48 hrs
export INNODB_LARGE="172800"

############################################################

#Choose your database engine, comment the one true/false
#you should have already converted your database to InnoDB engine, if you select true here
export INNODB="false"

############################################################

#run del_crossposted.php every hours. Removes crossposted releases in last 6 hours by default.
#If you want to remove more, run it manually from test folder.
export REMOVECRAP="false"
#How often to run this script in seconds, default is 40 seconds
export REMOVECRAP_TIMER="3600"

############################################################

#choose to run fixReleaseNames script.Before you can use this scripts you need
#to import db.sql from test/db_updates/ and prehash.sql from test/initial_setup/ folder
#to your newznab database.
#Script may stop working after svn update.
#This is a modified script used by nZEDb and is work in progress
export FIXRELEASES="false"
#How often to run this script in seconds, default is 10 minutes
export FIXRELEASES_TIMER="600"

############################################################

#choose to use predb_hash_decrypt. Use this only if you have access to 
#nzpre/predb, otherwise it will not work
#Before you use this, import db.sql and/or updates from test/db_updates/ folder
export PREDBHASH="false"
#How often to run this script in seconds, default is 10 minutes
export PREDBHASH_TIMER="600"

############################################################

#Choose to run update_predb.php
export PREDB="false"

#How often to update the PreDB in seconds
export PREDB_TIMER="900"

############################################################

#Choose to run processSpotnab.php
export SPOTNAB="false"

#How often to update the SpotNab in seconds
export SPOTNAB_TIMER="900"

############################################################

#update the tv schedule and in theaters listings
export TV_SCHEDULE="false"

#How often to update the TV Schedule and the In Theaters in seconds
export TVRAGE_TIMER="43200"

############################################################

#Choose to run sphinx.php script true/false
#set to false by default, you should test the script first, php sphinx.php from the bin folder
export SPHINX="false"

#How often to run sphinx in seconds
export SPHINX_TIMER="3600"

############################################################

#mediainfo and ffmpeg can hang occasionally, set timer, in seconds, to anything other than 0 to enable
#it should not need to run longer that 120 seconds
export KILL_PROCESS="0"

#look at man killall - if you have the -q option, enable this, otherwise leave it disabled
export KILL_QUIET="false"

############################################################

#Delete parts and binaries older than retention days, which is set in edit - site
#this uses a script posted by cj https://github.com/NNScripts/nn-custom-scripts
export DELETE_PARTS="false"

#how often should this be run, default it 1 hr
export DELETE_TIMER="3600"

#Releases may be added/edited with an imdb-id that does not exists in the movieinfo table. This script, update_missing_movie_info,
#will fetch all the missing imdb id's from the releases table.
export FETCH_MOVIE="false"

#how often should this be run, default it 12 hr
export MOVIE_TIMER="43200"

############################################################

#Specify your SED binary, if you are using freebsd or mac, you need to install gnu sed (gsed) and adjust the path
export SED="/bin/sed"
#export SED="/usr/local/bin/gsed"

#freebsd, and maybe mac, does not contain SIGSTKFLT, SIGCLD, SIGPOLL, SIGPWR
#and powerprocess.php will error on one of those, but appears to work if they
#are commented out, enable to have the script comment them out while running update_svn
export FIX_POSIX="false"

############################################################

#Select some monitoring script, if they are not installed, it will not affect the running of the scripts
#these are set to false by default, enable if you want them
export USE_HTOP="false"
export USE_BWMNG="false"
export USE_MYTOP="false"
export USE_ATOP="false"
export USE_NMON="false"
export USE_IOTOP="false"

#define vnstat user settings to apply at runtime
export USE_VNSTAT="false"
export VNSTAT_ARGS=""

#define tcptrack user settings to apply at runtime
export USE_TCPTRACK="false"
export TRCPTRACK_ARGS="-i eth0 port 443"

#freebsd does not have iotop, but can run top -m io -o total
export USE_TOP="false"

export USE_IFTOP="false"
#select interface to listen, only 1 interface
export INTERFACE="eth0"

#an additional window can be created manually with Ctrl-a c or it can be created at start of script
export USE_CONSOLE="false"

############################################################

#Use powerline scripts to display the status bar
#To display properly, you need a modified font, download and install the font and then select that font in your terminal
#This is done on the terminal computer, not the newznab++ server
#download fonts from https://github.com/jonnyboy/powerline-fonts
#I recommend Consolas if you are using putty in Win7
export POWERLINE="false"

############################################################

#set your LANG to which ever you like, only effects these scripts
export LANG="en_US.UTF-8"

#to help IMDB return only English titles, enable this, you will need to run update_svn.php or fix_files.sh
export EN_IMDB="false"

############################################################

#newzdash is a web front end to show statistics of your Newznab+ install
#to use, you must first install from https://github.com/AlienXAXS/newzdash

#this is YOUR shared key and allows these scripts to communicate with newzdash
export NEWZDASH_SHARED_SECRET=""

#the url of your newzdash install, ensure it include HTTP:// or HTTPS:// or it will fail
#do not include the trailing /
#to disable leave blank ie. export NEWZDASH_URL=""
export NEWZDASH_URL=""

###########################################################

#Use tmpfs to run post processing on true/false
#to keep from running scripts as root, you can create your own ramdisk by adding the next line to /etc/fstab
#tmpfs /var/www/newznab/nzbfiles/tmpunrar1 tmpfs user,nodev,nodiratime,nosuid,size=256M,mode=777 0 0
#edit the path, the path MUST be the path in site edit with a "1" appended to the end, like above
#you still need to set this to true or mount it manually as your user, not as root
export RAMDISK="false"

#for freebsd, it is just a little different, you can either create the ramdisk and mount it by adding the next line to /etc/fstab
#tmpfs /var/www/newznab/nzbfiles/tmpunrar1 tmpfs rw,size=256M,mode=777 0 0
#or, give users the permission to mount it by running sudo sysctl vfs.usermount=1 and then add the next line to /etc/fstab
#tmpfs /var/www/newznab/nzbfiles/tmpunrar1 tmpfs rw,noauto,size=256M,mode=777 0 0

############################################################

#set svn password, for use with scripts/update_svn.sh
#update_svn.sh is destructive, it update your version to match the svn version
export SVN_PASSWORD="password"

#running update_svn as root will change file ownership of every file in the svn path
#to chown -R the path, enable and set user/group
#newznab/nzbfiles is not chown'd
export CHOWN_TRUE="false"

#set CHOWN_TRUE="true" and WWW_USER="{youruser}:www-data" and run update_svn.sh or fix_files.sh
#and you will will not need root to run these scripts
export WWW_USER="www-data:www-data"

###########################################################

#if you have a ramdisk and would like to monitor it's use, set path here
#this is not the same as RAMDISK above, I keep my parts table on a ramdisk
export RAMDISK_PATH=""

###########################################################

#logs can be written, per pane, to the logs folder
export WRITE_LOGS="false"

###########################################################

#user defined scripts, you can define 3 scripts to run at specific times
#you must have them in the user_scripts, no other location
#This one will run before tmux creates the ui, so if you want to run something before the scripts add here
#it is assumed that the script is a bash script and has been chmod +x
export USER_DEF_ONE=""

#this one will run before MyIsam Large
export USER_DEF_TWO=""

#this one will run after MyIsam Large, not same process as below
export USER_DEF_THREE=""

#this one will run before InnoDB Large and MyIsam Large runs in same process, so before MyIsam Large, not same process as above
export USER_DEF_FOUR=""

#this one will run after InnoDB Large
export USER_DEF_FIVE=""

###########################################################

#By using this script you understand that the programmer is not responsible for any loss of data, users, or sanity.
#You also agree that you were smart enough to make a backup of your database and files. Do you agree? yes/no
export AGREED="no"

############################################################

##END OF EDITS##

command -v mysql >/dev/null 2>&1 || { echo >&2 "I require mysql but it's not installed. Aborting."; exit 1; } && export MYSQL=`command -v mysql`
command -v php5 >/dev/null 2>&1 && export PHP=`command -v php5` || { export PHP=`command -v php`; }
command -v tmux >/dev/null 2>&1 || { echo >&2 "I require tmux but it's not installed. Aborting."; exit 1; } && export TMUXCMD=`command -v tmux`
command -v nice >/dev/null 2>&1 || { echo >&2 "I require nice but it's not installed. Aborting."; exit 1; } && export NICE=`command -v nice`
command -v tee >/dev/null 2>&1 || { echo >&2 "I require tee but it's not installed. Aborting."; exit 1; } && export TEE=`command -v tee`
command -v mysqladmin >/dev/null 2>&1 || { echo >&2 "I require mysqladmin but it's not installed. Aborting."; exit 1; } && export MYSQLADMIN=`command -v mysqladmin`

if [[ $USE_HTOP == "true" ]]; then
  command -v htop >/dev/null 2>&1|| { echo >&2 "I require htop but it's not installed. Aborting."; exit 1; } && export HTOP=`command -v htop`
fi
if [[ $USE_NMON == "true" ]]; then
  command -v nmon >/dev/null 2>&1 || { echo >&2 "I require nmon but it's not installed. Aborting."; exit 1; } && export NMON=`command -v nmon`
fi
if [[ $USE_BWMNG == "true" ]]; then
  command -v bwm-ng >/dev/null 2>&1|| { echo >&2 "I require bwm-ng but it's not installed. Aborting."; exit 1; } && export BWMNG=`command -v bwm-ng`
fi
if [[ $USE_IOTOP == "true" ]]; then
  command -v iotop >/dev/null 2>&1|| { echo >&2 "I require iotop but it's not installed. Aborting."; exit 1; } && export IOTOP=`command -v iotop`
fi
if [[ $USE_TOP == "true" ]]; then
  command -v top >/dev/null 2>&1|| { echo >&2 "I require top but it's not installed. Aborting."; exit 1; } && export TOP=`command -v top`
fi
if [[ $USE_MYTOP == "true" ]]; then
  command -v mytop >/dev/null 2>&1|| { echo >&2 "I require mytop but it's not installed. Aborting."; exit 1; } && export MYTOP=`command -v mytop`
fi
if [[ $USE_VNSTAT == "true" ]]; then
  command -v vnstat >/dev/null 2>&1|| { echo >&2 "I require vnstat but it's not installed. Aborting."; exit 1; } && export VNSTAT=`command -v vnstat`
fi
if [[ $USE_IFTOP == "true" ]]; then
  command -v iftop >/dev/null 2>&1|| { echo >&2 "I require iftop but it's not installed. Aborting."; exit 1; } && export IFTOP=`command -v iftop`
fi
if [[ $USE_ATOP == "true" ]]; then
  command -v atop >/dev/null 2>&1|| { echo >&2 "I require atop but it's not installed. Aborting."; exit 1; } && export ATOP=`command -v atop`
fi
if [[ $USE_TCPTRACK == "true" ]]; then
  command -v tcptrack >/dev/null 2>&1|| { echo >&2 "I require tcptrack but it's not installed. Aborting."; exit 1; } && export TCPTRACK=`command -v tcptrack`
fi
if [[ $POWERLINE == "true" ]]; then
  export TMUX_CONF="powerline/tmux.conf"
else
  export TMUX_CONF="conf/tmux.conf"
fi
