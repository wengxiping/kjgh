<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="es-cards__item t-lg-mb--xl" data-app-item data-id="<?php echo $app->id;?>">
    <div class="es-card <?php echo $app->featured ? 'es-card--featured' : '';?>">        
        <div class="es-card__bd">
        	<?php if ($app->getTypeLabel()) { ?>
        	<div class="es-app-type es-app-type--<?php echo $app->getTypeClass();?> es-card__app-type" data-es-provide="tooltip" data-original-title="<?php echo $app->getTypeLabel();?>">
        		<i class="es-app-type__icon"></i>
        	</div>
        	<?php } ?>

            <?php if ($app->featured) { ?>
            <div class="es-card__label-txt">
                <div class="o-label o-label--warning-o">Featured App</div>
            </div>
            <?php } ?>

			<div class="o-avatar o-avatar--lg t-lg-mb--lg">
	            <img src="<?php echo $app->logo;?>" />
	        </div>
            
            <div class="es-card__title">
                <a href="<?php echo $app->getPermalink();?>"><?php echo $app->title;?></a>    
            </div>
            
            <div class="es-card__meta">
            	<ul class="g-list-inline g-list-inline--dashed">
            		<li>
            			<b><?php echo $app->category;?></b>
            		</li>
            		<li>
            			<b>v<?php echo $app->version;?></b>
            		</li>
            	</ul>
            </div>
            <div class="es-card__meta">
            	<?php echo $app->getDescription();?>
            </div>
        </div>
        <div class="es-card__ft es-card--border">
            <div class="t-lg-pull-right">
                <?php if ($app->isInstalled()) { ?>
                <a href="javascript:void(0);" class="btn btn-es-default-o btn-sm disabled">
                    <b><?php echo JText::_('Installed');?></b>
                </a>
                <?php } else { ?>

	                <?php if ($app->isDownloadable() && $app->isDownloadableFromApi()) { ?>
	                <button type="button" class="btn btn-es-primary-o btn-sm" data-app-install>
	                    <b><?php echo JText::_('Install');?></b>
	                </button>
	                <?php } else { ?>

		                <?php if ($app->isFree()) { ?>
		                <button type="button" class="btn btn-es-primary-o btn-sm" data-app-install>
		                    <b><?php echo JText::_('Install (FREE)');?></b>
		                </button>
		                <?php } ?>

		                <?php if (!$app->isFree()) { ?>
		                <button type="button" class="btn btn-es-primary-o btn-sm" data-app-install>
		                    <b><?php echo JText::sprintf('Install ($%1$s)', $app->getPrice());?></b>
		                </button>
		                <?php } ?>
		            <?php } ?>
		        <?php } ?>
	        </div>

            
            <div class="es-card__ft-ratings" style="margin-left: 0; margin-top: 5px;">
            	<div class="stars" data-ratings data-score="<?php echo $app->getScore();?>" style="display: inline-block;"></div> 
            	<?php echo $app->votes;?> <?php echo JText::_('reviews');?>
            </div>
        </div>
    </div>
</div>