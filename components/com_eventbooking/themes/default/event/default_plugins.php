<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2019 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

if (count($this->plugins) > 1)
{
?>
	<div id="eb-plugins-output" class="clearfix">
        <?php
            echo JHtml::_('bootstrap.startTabSet', 'eb-event-plugins-output', array('active' => 'eb-plugin-page-0'));

            $count = 0;

            foreach ($this->plugins as $plugin)
            {
                if (is_array($plugin) && array_key_exists('title', $plugin) && array_key_exists('form', $plugin))
                {
	                echo JHtml::_('bootstrap.addTab', 'eb-event-plugins-output', 'eb-plugin-page-' . $count, $plugin['title']);
	                echo $plugin['form'];
	                echo JHtml::_('bootstrap.endTab');

	                $count++;
                }
            }

            echo JHtml::_('bootstrap.endTabSet');
        ?>
	</div>	
<?php
}
else
{
	$plugin = $this->plugins[0];
?>
	<div id="eb-plugins">
		<h3><?php echo $plugin['title']; ?></h3>
		<div class="eb-plugin-output">
			<?php echo $plugin['form']; ?>
		</div>
		<div class="clearfix"></div>
	</div>
<?php
}
