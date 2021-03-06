<?
/*  check paths of files below  */
require_once('../settings.php');
header( 'Content-Type: text/html; charset='.DOCUMENT_CHARSET );
require_once('db.classes.php');

$database = new DB();
$database->log_errors = false;

$query="CREATE TABLE IF NOT EXISTS `".DB_PREFIX."components` (
	`id` int(3) NOT NULL,
	`component` varchar(255) NOT NULL,
	`name` varchar(255) NOT NULL,
	`description` text NOT NULL,
	`available` tinyint(1) NOT NULL,
	`version` int(3) NOT NULL,
	`edition` int(3) NOT NULL,
	`license` varchar(255) NOT NULL,
	`last_update` timestamp NOT NULL,
	INDEX (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=".DB_CHARSET;

$database->Go($query);

$query="CREATE TABLE IF NOT EXISTS `".DB_PREFIX."sections` (
	`id` int(3) NOT NULL,
	`section` varchar(255) NOT NULL,
	`name` varchar(255) NOT NULL,
	`component` int(3) NOT NULL,
	`area` int(3) NOT NULL,
	`available` tinyint(1) NOT NULL,
	INDEX (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=".DB_CHARSET;

$database->Go($query);

$query="CREATE TABLE IF NOT EXISTS `".DB_PREFIX."settings` (
	`name` varchar(255) NOT NULL,
	`value` varchar(255) NOT NULL,
	INDEX (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=".DB_CHARSET;

$database->Go($query);

$query="CREATE TABLE IF NOT EXISTS `".DB_PREFIX."aliases` (
	`alias` varchar(255) NOT NULL,
	`url` varchar(255) NOT NULL,
	INDEX (`alias`)
) ENGINE=MyISAM DEFAULT CHARSET=".DB_CHARSET;

$database->Go($query);

$query="CREATE TABLE IF NOT EXISTS `".DB_PREFIX."users` (
	`id` int(20) NOT NULL,
	`login` varchar(255) NOT NULL,
	`name` varchar(255) NOT NULL,
	`pass` varchar(255) NOT NULL,
	`key` varchar(255) NOT NULL,
	`group` int(10) NOT NULL,
	`email` varchar(255) NOT NULL,
	`approved` tinyint(1) NOT NULL,
	`active` tinyint(1) NOT NULL,
	INDEX (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=".DB_CHARSET;

$database->Go($query);

$query="CREATE TABLE `".DB_PREFIX."users_groups` (
	`id` int(3) NOT NULL ,
	`name` varchar(255) NOT NULL ,
	`identifier` varchar(255) NOT NULL ,
	`description` text NOT NULL,
	`areas` varchar(255) NOT NULL ,
	`active` tinyint(1) NOT NULL,
	INDEX (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=".DB_CHARSET;

$database->Go($query);

$query="CREATE TABLE `".DB_PREFIX."users_groups_access` (
	`group` int(3) NOT NULL ,
	`area` int(3) NOT NULL ,
	`section` varchar(255) NOT NULL ,
	`access` int(1) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=".DB_CHARSET;

$database->Go($query);

$query="CREATE TABLE `".DB_PREFIX."sessions` (
	`sid` varchar(255) NOT NULL ,
	`user` int(20) NOT NULL ,
	`data` text NOT NULL,
	`started` int(20) NOT NULL ,
	`expire` int(20) NOT NULL
	INDEX (`sid`)
) ENGINE=MyISAM DEFAULT CHARSET=".DB_CHARSET;

$database->Go($query);

echo '<span style="color:green;font-size:16px;">All tables were created successfully</span>';

?>
