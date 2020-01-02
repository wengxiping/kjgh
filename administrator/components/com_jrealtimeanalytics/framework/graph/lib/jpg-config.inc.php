<?php
/**
 * @author Joomla! Extensions Store
 * @package JREALTIMEANALYTICS::FRAMEWORK::administrator::components::com_jrealtimeanalytics
 * @subpackage framework 
 * @subpackage graph
 * @copyright (C) 2013 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
define('CSIMCACHE_DIR','csimcache/');
define('CSIMCACHE_HTTP_DIR','csimcache/');

//------------------------------------------------------------------------
// Various JpGraph Settings. Adjust accordingly to your
// preferences. Note that cache functionality is turned off by
// default (Enable by setting USE_CACHE to true)
//------------------------------------------------------------------------

// Deafult locale for error messages.
// This defaults to English = 'en'
define('DEFAULT_ERR_LOCALE','en');

// Deafult graphic format set to 'auto' which will automatically
// choose the best available format in the order png,gif,jpeg
// (The supported format depends on what your PHP installation supports)
define('DEFAULT_GFORMAT','auto');

// Should the cache be used at all? By setting this to false no
// files will be generated in the cache directory.
// The difference from READ_CACHE being that setting READ_CACHE to
// false will still create the image in the cache directory
// just not use it. By setting USE_CACHE=false no files will even
// be generated in the cache directory.
define('USE_CACHE',false);

// Should we try to find an image in the cache before generating it?
// Set this define to false to bypass the reading of the cache and always
// regenerate the image. Note that even if reading the cache is
// disabled the cached will still be updated with the newly generated
// image. Set also 'USE_CACHE' below.
define('READ_CACHE',true);

// Determine if the error handler should be image based or purely
// text based. Image based makes it easier since the script will
// always return an image even in case of errors.
define('USE_IMAGE_ERROR_HANDLER',true);

// Should the library examine the global php_errmsg string and convert
// any error in it to a graphical representation. This is handy for the
// occasions when, for example, header files cannot be found and this results
// in the graph not being created and just a 'red-cross' image would be seen.
// This should be turned off for a production site.
define('CATCH_PHPERRMSG',true);

// Determine if the library should also setup the default PHP
// error handler to generate a graphic error mesage. This is useful
// during development to be able to see the error message as an image
// instead as a 'red-cross' in a page where an image is expected.
define('INSTALL_PHP_ERR_HANDLER',false);

// Should usage of deprecated functions and parameters give a fatal error?
// (Useful to check if code is future proof.)
define('ERR_DEPRECATED',true);

// The builtin GD function imagettfbbox() fuction which calculates the bounding box for
// text using TTF fonts is buggy. By setting this define to true the library
// uses its own compensation for this bug. However this will give a
// slightly different visual apparance than not using this compensation.
// Enabling this compensation will in general give text a bit more space to more
// truly reflect the actual bounding box which is a bit larger than what the
// GD function thinks.
define('USE_LIBRARY_IMAGETTFBBOX',true);

//------------------------------------------------------------------------
// The following constants should rarely have to be changed !
//------------------------------------------------------------------------

// What group should the cached file belong to
// (Set to '' will give the default group for the 'PHP-user')
// Please note that the Apache user must be a member of the
// specified group since otherwise it is impossible for Apache
// to set the specified group.
define('CACHE_FILE_GROUP','www');

// What permissions should the cached file have
// (Set to '' will give the default persmissions for the 'PHP-user')
define('CACHE_FILE_MOD',0664);

// Default theme class name
define('DEFAULT_THEME_CLASS', 'UniversalTheme');

define('SUPERSAMPLING', true);
define('SUPERSAMPLING_SCALE', 1);

?>
