<?php
/**
 * @package    Mosets Tree
 * @copyright    (C) 2019 Mosets Consulting. All rights reserved.
 * @license    GNU General Public License
 * @author    Lee Cher Yeong <mtree@mosets.com>
 * @url        http://www.mosets.com/tree/
 */

defined('_JEXEC') or die('Restricted access');

class mUpgrade_3_10_9 extends mUpgrade
{
    public function upgrade()
    {
        $database =& JFactory::getDBO();

        $database->setQuery('DELETE FROM `#__mt_config` WHERE `varname` IN (\'show_share_with_googleplus\')');
        $database->execute();

        $database->setQuery('UPDATE `#__mt_config` SET `ordering` = \'4110\' WHERE `varname` = \'cluster_map_max_zoom\'');
        $database->execute();

        $database->setQuery('UPDATE `#__mt_config` SET `ordering` = \'4113\' WHERE `varname` = \'google_maps_api_key\'');
        $database->execute();

        $database->setQuery('UPDATE `#__mt_config` SET `configcode` = \'textarea\' WHERE `varname` = \'google_maps_styled_map_style_array\'');
        $database->execute();

        $insertSql = 'INSERT INTO `#__mt_config` (`varname`, `groupname`, `value`, `default`, `configcode`, `ordering`, `displayed`, `overridable_by_category`) VALUES';
        $insertSql .= '(\'note_map_provider\', \'map\', \'\', \'\', \'note\', \'3948\', \'1\', \'1\'), ';
        $insertSql .= '(\'note_google_maps\', \'map\', \'\', \'\', \'note\', \'4111\', \'1\', \'1\'), ';
        
        // Default map provider is custom. However, for upgrade, we will prefill the value with 'google' because Google Maps is the only map provider prior to this update.
        $insertSql .= '(\'map_provider\', \'map\', \'google\', \'\custom\', \'map_providers\', \'3949\', \'1\', \'1\'), ';
        
        $insertSql .= '(\'note_mapbox\', \'map\', \'\', \'\', \'note\', 4130, 1, 1), ';
        $insertSql .= '(\'mapbox_access_token\', \'map\', \'\', \'\', \'text\', \'4135\', \'1\', \'0\'), ';
        $insertSql .= '(\'note_here\', \'map\', \'\', \'\', \'note\', \'4140\', \'1\', \'1\'), ';
        $insertSql .= '(\'here_app_id\', \'map\', \'\', \'\', \'text\', \'4145\', \'1\', \'0\'), ';
        $insertSql .= '(\'here_app_code\', \'map\', \'\', \'\', \'text\', \'4150\', \'1\', \'0\'), ';
        $insertSql .= '(\'note_custom_tile_server\', \'map\', \'\', \'\', \'note\', 4160, 1, 0), ';
        $insertSql .= '(\'custom_tile_server\', \'map\', \'{\"url\": \"https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png\", \"attribution\": \"&copy; <a href=\\\"https://www.openstreetmap.org/copyright\\\">OpenStreetMap</a> contributors &copy; <a href=\\\"https://carto.com/attributions\\\">CARTO</a>\"}\', \'{\"url\": \"https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png\", \"attribution\": \"&copy; <a href=\\\"https://www.openstreetmap.org/copyright\\\">OpenStreetMap</a> contributors &copy; <a href=\\\"https://carto.com/attributions\\\">CARTO</a>\"}\', \'tile_servers\', 4165, 1, 0), ';
        
        $insertSql .= '(\'custom_tile_url\', \'map\', \'\', \'\', \'text\', 4170, 1, 0), ';
        $insertSql .= '(\'custom_tile_attribution\', \'map\', \'\', \'\', \'text\', 4180, 1, 0); ';
        $database->setQuery($insertSql);
        $database->execute();

        updateVersion(3, 10, 9);
        $this->updated = true;
        return true;
    }
}


