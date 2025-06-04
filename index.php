<?php
// Version
define('VERSION', '3.0.3.7');
define('VERSION_CORE', 'ocStore');
define('VERSION_BUILD', '0002');
define('VERSION_LANGPACK', 'PL-EN');


define('ROOT_RATH', __DIR__.'/'); //echo ROOT_RATH;
if (is_file(ROOT_RATH.'config.php')) {
	require_once(ROOT_RATH.'config.php');
}

// Startup
require_once(DIR_SYSTEM . 'startup.php');

start('catalog');