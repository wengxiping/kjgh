<?php 
/**
 * ------------------------------------------------------------------------
 * JA Conf Template
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2018 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - Copyrighted Commercial Software
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites:  http://www.joomlart.com -  http://www.joomlancers.com
 * This file may not be redistributed in whole or significant part.
 * ------------------------------------------------------------------------
*/

defined('_JEXEC') or die;
$count      = $helper->getRows('data.event-name');
?>

<div class="acm-events">
	<div class="style-1 event-items">
		<?php
      for ($i=0; $i < $count; $i++) :
    ?>
		<div class="event-item">
      <div class="item-inner">
        <div class="row">
          <div class="col-xs-12 col-sm-2">
            <p class="event-time"><?php echo $helper->get('data.event-time', $i); ?></p>
          </div>

          <div class="col-xs-10 col-sm-8">
            <h3 class="event-name"><?php echo $helper->get('data.event-name', $i); ?></h3>
            <p class="event-description"><?php echo $helper->get('data.event-description', $i); ?></p>
          </div>

          <div class="col-xs-2 col-sm-2">
            <?php if($helper->get('data.event-link', $i)) :?>
              <div class="event-link text-right">
                <a href="<?php echo $helper->get('data.event-link', $i); ?>" title="Event Link">
                  <span class="fas fa-arrow-right"></span>
                  <span class="element-invisible hidden">empty</span>
                </a>
              </div>
            <?php endif ;?>
          </div>
        </div>  
      </div>
		</div>
		<?php endfor; ?>
	</div>
</div>