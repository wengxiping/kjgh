<?php
/**
 * @package dompdf
 * @link    http://dompdf.github.com/
 * @author  Benj Carson <benjcarson@digitaljunkies.ca>
 * @author  Fabien Ménager <fabien.menager@gmail.com>
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 */

// HMLT5 Parser
require_once __DIR__ . '/lib/html5lib/Parser.php';

// php-font-lib
require_once __DIR__ . '/lib/php-font-lib/src/FontLib/Autoloader.php';

/*
 * New PHP 5.3.0 namespaced autoloader
 */
require_once __DIR__ . '/src/Autoloader.php';

Dompdf\Autoloader::register();
