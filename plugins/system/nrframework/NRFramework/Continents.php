<?php
/**
 *  @author          Tassos.gr <info@tassos.gr>
 *  @link            http://www.tassos.gr
 *  @copyright       Copyright © 2018 Tassos Marinos All Rights Reserved
 *  @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
 */

namespace NRFramework;

defined('_JEXEC') or die('Restricted access');

/**
 *  Helper class to work with continent names/codes
 */
class Continents
{
    /**
     *  Return a continent code from it's name
     *
     *  @param  string $cont
     *  @return string|void
     */
    public static function getCode($cont)
    {
        $cont = \ucwords(strtolower($cont));
        foreach (self::$map as $key => $value)
        {
            if (strpos($value, $cont) !== false)
            {
                return $key;
            }
        }
        return null;
    }

    /**
	 *  Continents List
	 *
	 *  @var  array
	 */
    public static $map = [
		'AF' => 'Africa',
		'AS' => 'Asia',
		'EU' => 'Europe',
		'NA' => 'North America',
		'SA' => 'South America',
		'OC' => 'Oceania',
		'AN' => 'Antarctica',
    ];
}
