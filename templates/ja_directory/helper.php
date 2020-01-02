<?php
/*
 * ------------------------------------------------------------------------
 * JA Directory Template
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2018 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - Copyrighted Commercial Software
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites:  http://www.joomlart.com -  http://www.joomlancers.com
 * This file may not be redistributed in whole or significant part.
 * ------------------------------------------------------------------------
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

class JATemplateHelper {

  public static function relTime($timespan, $granularity = 1) {
    static $units = array(
      'YEAR' => 31536000,
      'MONTH' => 2592000,
      'WEEK' => 604800,
      'DAY' => 86400,
      'HOUR' => 3600,
      'MIN' => 60,
      'SEC' => 1,
    );

    $output = '';
    if(!ctype_digit($timespan)){
      $timespan = strtotime($timespan);
    }

      $interval = time() - $timespan;

      $future = $interval < 0;
      if($future){
        $interval = abs($interval);
      }

    foreach ($units as $key => $value) {
      if ($interval >= $value) {
        $output .= ($output ? ' ' : '') . JText::sprintf('TPL_RT_' . $key . (floor($interval / $value) != 1 ? 'S' : ''), floor($interval / $value));
        $interval %= $value;
        $granularity--;
      }

      if ($granularity == 0) {
        break;
      }
    }

    return $output ? JText::sprintf($future ? 'TPL_RT_FUTURE' : 'TPL_RT_PAST', $output) : JText::_('TPL_RT_NOW');
  }
}