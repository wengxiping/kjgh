<?php

/**
 * @package         EngageBox
 * @version         3.5.2 Pro
 * 
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            http://www.tassos.gr
 * @copyright       Copyright © 2019 Tassos Marinos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

defined('_JEXEC') or die('Restricted access');

?>

<table class="adminlist table table-striped">
    <thead>
        <tr>
            <th width="20%">Syntax</th>
            <th width="40%">Description</th>
            <th width="40%">Output example</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($this->tags as $tag => $tagvalue) { ?>
            <tr>
                <td style="font-family:consolas"><?php echo $tag ?></td>
                <td><?php echo JText::_('NR_TAG_' . strtoupper(str_replace(array("{", "}", "."), "", $tag))) ?></td>
                <td><?php echo $tagvalue; ?></td>
            </tr>
        <?php } ?>
    </tbody>
</table>
