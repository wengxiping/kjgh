<?php
/**
 * ------------------------------------------------------------------------
 * JA Aiga Template
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2011 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - Copyrighted Commercial Software
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites:  http://www.joomlart.com -  http://www.joomlancers.com
 * This file may not be redistributed in whole or significant part.
 * ------------------------------------------------------------------------
 */

defined('_JEXEC') or die;

$cparams = JComponentHelper::getParams('com_media');
$tparams = $this->params;
jimport('joomla.html.html.bootstrap');
?>
<div class="contact <?php echo $this->pageclass_sfx?>" itemscope itemtype="http://schema.org/Person">
	<!-- Page heading -->
	<?php if ($this->params->get('show_page_heading')) : ?>
		<h1>
			<?php echo $this->escape($this->params->get('page_heading')); ?>
		</h1>
	<?php endif; ?>
	<!-- End page heading -->
	
	<!-- Show Contact name -->
	<?php if ($this->contact->name && $this->params->get('show_name')) : ?>
		<?php if($this->params->get('presentation_style') != 'plain') :?>
		<div class="page-header">
			<h2>
				<?php if ($this->item->published == 0) : ?>
					<span class="label label-warning"><?php echo JText::_('JUNPUBLISHED'); ?></span>
				<?php endif; ?>
				<span class="contact-name" itemprop="name"><?php echo $this->contact->name; ?></span>
			</h2>
		</div>
		<?php endif;  ?>
	<?php endif;  ?>
	<!-- End Show Contact name -->

	<?php $presentation_style = $tparams->get('presentation_style'); ?>
	<?php $accordionStarted = false; ?>
	<?php $tabSetStarted = false; ?>

	<!-- Slider type -->
	<?php if ($presentation_style === 'sliders') : ?>
    <div class="panel-group" id="slide-contact">

		<?php if ($this->params->get('show_info', 1)) : ?>
      <div class="panel panel-default">
        <div class="panel-heading">
          <h4 class="panel-title">
            <a class="accordion-toggle" data-toggle="collapse" data-parent="#slide-contact" href="#basic-details">
            <?php echo JText::_('COM_CONTACT_DETAILS');?>
            </a>
          </h4>
        </div>

        <div id="basic-details" class="panel-collapse collapse in">
          <div class="panel-body">
            <?php if ($this->contact->image && $tparams->get('show_image')) : ?>
              <div class="thumbnail pull-right">
                <?php echo JHtml::_('image', $this->contact->image, $this->contact->name, array('itemprop' => 'image')); ?>
              </div>
            <?php endif; ?>

            <?php if ($tparams->get('show_tags', 1) && !empty($this->item->tags->itemTags)) : ?>
							<?php $this->item->tagLayout = new JLayoutFile('joomla.content.tags'); ?>
							<?php echo $this->item->tagLayout->render($this->item->tags->itemTags); ?>
						<?php endif; ?>

            <?php if ($this->contact->con_position && $tparams->get('show_position')) : ?>
              <dl class="contact-position dl-horizontal">
                <dt><?php echo JText::_('COM_CONTACT_POSITION'); ?>:</dt>
                <dd itemprop="jobTitle">
                  <?php echo $this->contact->con_position; ?>
                </dd>
              </dl>
            <?php endif; ?>

            <?php echo $this->loadTemplate('address'); ?>

            <?php if ($tparams->get('allow_vcard')) : ?>
              <?php echo JText::_('COM_CONTACT_DOWNLOAD_INFORMATION_AS'); ?>
              <a href="<?php echo JRoute::_('index.php?option=com_contact&amp;view=contact&amp;id=' . $this->contact->id . '&amp;format=vcf'); ?>">
              <?php echo JText::_('COM_CONTACT_VCARD'); ?></a>
            <?php endif; ?>
          </div>
        </div>

      </div>

		<?php endif; ?> <!-- // Show info -->

		<?php if ($tparams->get('show_email_form') && ($this->contact->email_to || $this->contact->user_id)) : ?>
      <div class="panel panel-default">
        <div class="panel-heading">
          <h4 class="panel-title">
            <a class="accordion-toggle" data-toggle="collapse" data-parent="#slide-contact" href="#display-form">
            <?php echo JText::_('COM_CONTACT_EMAIL_FORM');?>
            </a>
          </h4>
        </div>

        <div id="display-form" class="panel-collapse collapse">
          <div class="panel-body">
            <?php echo $this->loadTemplate('form'); ?>
          </div>
        </div>
      </div>

		<?php endif; ?> <!-- // Show email form -->

		<?php if ($tparams->get('show_links')) : ?>
	    <?php echo $this->loadTemplate('links'); ?>
	  <?php endif; ?>

	  <?php if ($tparams->get('show_articles') && $this->contact->user_id && $this->contact->articles) : ?>
      <div class="panel panel-default">
        <div class="panel-heading">
          <h4 class="panel-title">
            <a class="accordion-toggle" data-toggle="collapse" data-parent="#slide-contact" href="#display-articles">
            <?php echo JText::_('JGLOBAL_ARTICLES');?>
            </a>
          </h4>
        </div>

        <div id="display-articles" class="panel-collapse collapse">
          <div class="panel-body">
            <?php echo $this->loadTemplate('articles'); ?>
          </div>
        </div>
      </div>
	  <?php endif; ?> <!-- // Show articles -->

	  <?php if ($tparams->get('show_profile') && $this->contact->user_id && JPluginHelper::isEnabled('user', 'profile')) : ?>
      <div class="panel panel-default">
        <div class="panel-heading">
          <h4 class="panel-title">
            <a class="accordion-toggle" data-toggle="collapse" data-parent="#slide-contact" href="#display-profile">
            <?php echo JText::_('COM_CONTACT_PROFILE');?>
            </a>
          </h4>
        </div>

        <div id="display-profile" class="panel-collapse collapse">
          <div class="panel-body">
            <?php echo $this->loadTemplate('profile'); ?>
          </div>
        </div>
      </div>
	  <?php endif; ?> <!-- // Show profile -->

	  <?php if ($tparams->get('show_user_custom_fields') && $this->contactUser) : ?>
	    <?php echo $this->loadTemplate('user_custom_fields'); ?>
	  <?php endif; ?>

	  <?php if ($this->contact->misc && $tparams->get('show_misc')) : ?>
      <div class="panel panel-default">
        <div class="panel-heading">
          <h4 class="panel-title">
            <a class="accordion-toggle" data-toggle="collapse" data-parent="#slide-contact" href="#display-misc">
            <?php echo JText::_('COM_CONTACT_OTHER_INFORMATION');?>
            </a>
          </h4>
        </div>

        <div id="display-misc" class="panel-collapse collapse">
          <div class="panel-body">
            <div class="contact-miscinfo">
              <dl class="dl-horizontal">
                <dt>
                  <span class="<?php echo $tparams->get('marker_class'); ?>">
                  <?php echo $tparams->get('marker_misc'); ?>
                  </span>
                </dt>
                <dd>
                  <span class="contact-misc">
                    <?php echo $this->contact->misc; ?>
                  </span>
                </dd>
              </dl>
            </div>
          </div>
        </div>
      </div>
	  <?php endif; ?>  <!-- // Contact misc -->
	  <?php echo $this->item->event->beforeDisplayContent; ?>
    </div>
	<?php endif; ?>
	<!-- //Sliders type -->


	<!-- Tabs type -->
	<?php if ($presentation_style === 'tabs') : ?>
	<div class="wrap-tabs">
		<?php if ($this->params->get('show_info', 1)) : ?>
      <?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'basic-details')); ?>
      <?php $tabSetStarted = true; ?>
      <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'basic-details', JText::_('COM_CONTACT_DETAILS')); ?>

	    <?php if ($this->contact->image && $tparams->get('show_image')) : ?>
	      <div class="thumbnail pull-right">
	        <?php echo JHtml::_('image', $this->contact->image, $this->contact->name, array('itemprop' => 'image')); ?>
	      </div>
	    <?php endif; ?>

	    <?php if ($tparams->get('show_tags', 1) && !empty($this->item->tags->itemTags)) : ?>
				<?php $this->item->tagLayout = new JLayoutFile('joomla.content.tags'); ?>
				<?php echo $this->item->tagLayout->render($this->item->tags->itemTags); ?>
			<?php endif; ?>

	    <?php if ($this->contact->con_position && $tparams->get('show_position')) : ?>
	      <dl class="contact-position dl-horizontal">
	        <dt><?php echo JText::_('COM_CONTACT_POSITION'); ?>:</dt>
	        <dd itemprop="jobTitle">
	          <?php echo $this->contact->con_position; ?>
	        </dd>
	      </dl>
	    <?php endif; ?>

	    <?php echo $this->loadTemplate('address'); ?>

	    <?php if ($tparams->get('allow_vcard')) : ?>
	      <?php echo JText::_('COM_CONTACT_DOWNLOAD_INFORMATION_AS'); ?>
	      <a href="<?php echo JRoute::_('index.php?option=com_contact&amp;view=contact&amp;id=' . $this->contact->id . '&amp;format=vcf'); ?>">
	      <?php echo JText::_('COM_CONTACT_VCARD'); ?></a>
	    <?php endif; ?>

	    <?php echo JHtml::_('bootstrap.endTab'); ?>

		<?php endif; ?><!-- // Show info -->

		<?php if ($tparams->get('show_email_form') && ($this->contact->email_to || $this->contact->user_id)) : ?>
      <?php if (!$tabSetStarted)
      {
        echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'display-form'));
        $tabSetStarted = true;
      }
      ?>
      <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'display-form', JText::_('COM_CONTACT_EMAIL_FORM')); ?>

      <?php echo $this->loadTemplate('form'); ?>

      <?php echo JHtml::_('bootstrap.endTab'); ?>
		<?php endif; ?> <!-- // Show email form -->

	  <?php if ($tparams->get('show_links')) : ?>
	  	<?php if (!$tabSetStarted) : ?>
				<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'display-links')); ?>
				<?php $tabSetStarted = true; ?>
			<?php endif; ?>
	    <?php echo $this->loadTemplate('links'); ?>
	  <?php endif; ?>

	  <?php if ($tparams->get('show_articles') && $this->contact->user_id && $this->contact->articles) : ?>
      <?php if (!$tabSetStarted)
      {
        echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'display-articles'));
        $tabSetStarted = true;
      }
      ?>
      <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'display-articles', JText::_('JGLOBAL_ARTICLES')); ?>

      <?php echo $this->loadTemplate('articles'); ?>

      <?php echo JHtml::_('bootstrap.endTab'); ?>
	  <?php endif; ?> <!-- // Show articles -->

	  <?php if ($tparams->get('show_profile') && $this->contact->user_id && JPluginHelper::isEnabled('user', 'profile')) : ?>
      <?php if (!$tabSetStarted)
      {
        echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'display-profile'));
        $tabSetStarted = true;
      }
      ?>
      <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'display-profile', JText::_('COM_CONTACT_PROFILE')); ?>

      <?php echo $this->loadTemplate('profile'); ?>
      <?php echo JHtml::_('bootstrap.endTab'); ?>
	  <?php endif; ?> <!-- // Show profile -->

	  <?php if ($tparams->get('show_user_custom_fields') && $this->contactUser) : ?>
	    <?php echo $this->loadTemplate('user_custom_fields'); ?>
	  <?php endif; ?>

	  <?php if ($this->contact->misc && $tparams->get('show_misc')) : ?>
      <?php if (!$tabSetStarted)
      {
        echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'display-misc'));
        $tabSetStarted = true;
      }
      ?>
      <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'display-misc', JText::_('COM_CONTACT_OTHER_INFORMATION')); ?>

	    <div class="contact-miscinfo">
	      <dl class="dl-horizontal">
	        <dt>
	          <span class="<?php echo $tparams->get('marker_class'); ?>">
	          <?php echo $tparams->get('marker_misc'); ?>
	          </span>
	        </dt>
	        <dd>
	          <span class="contact-misc">
	            <?php echo $this->contact->misc; ?>
	          </span>
	        </dd>
	      </dl>
	    </div>
	    <?php echo JHtml::_('bootstrap.endTab'); ?>
	  <?php endif; ?>  <!-- // Contact misc -->
	</div>
	<?php echo $this->item->event->beforeDisplayContent; ?>
	<?php endif; ?>
	<!-- //Tabs type -->
	
	<!-- JA Override Contact From for case "plain" -->
	<?php if($this->params->get('presentation_style') == 'plain') :?>
	<div class="<?php echo $this->params->get('presentation_style') ?>-style">
		<div class="box-info">

			<div class="container">
				<div class="wrap-content">
					<!-- Show Contact name -->
					<?php if ($this->contact->name && $this->params->get('show_name')) : ?>
						<div class="header-title">
							<span><?php  echo JText::_('TPL_CONTACT_US') ;?></span>
							<h2>
								<?php if ($this->item->published == 0) : ?>
									<span class="label label-warning"><?php echo JText::_('JUNPUBLISHED'); ?></span>
								<?php endif; ?>
								<?php echo $this->contact->name; ?>
							</h2>
						</div>
					<?php endif;  ?>
					<!-- End Show Contact name -->

					<?php if ($this->params->get('show_info', 1)) :?>			
						<div class="contact-info">
							<?php if ($this->contact->con_position && $this->params->get('show_position')) : ?>
								<dl class="contact-position dl-horizontal">
									<dd>
										<?php echo $this->contact->con_position; ?>
									</dd>
								</dl>
							<?php endif; ?>
							
							<?php echo $this->loadTemplate('address'); ?>
					
							<?php if ($this->params->get('allow_vcard')) :	?>
								<?php echo JText::_('COM_CONTACT_DOWNLOAD_INFORMATION_AS');?>
									<a href="<?php echo JRoute::_('index.php?option=com_contact&amp;view=contact&amp;id='.$this->contact->id . '&amp;format=vcf'); ?>">
									<?php echo JText::_('COM_CONTACT_VCARD');?></a>
							<?php endif; ?>
						</div>
					<?php endif; ?>

				</div>
			</div>
		</div>

		<div class="contact-inner">
			<!-- Show Other information -->
				<div class="contact-image">
					<?php if ($this->contact->image && $tparams->get('show_image')) : ?>
		              <div class="thumbnail">
		                <?php echo JHtml::_('image', $this->contact->image, $this->contact->name, array('itemprop' => 'image')); ?>
		              </div>
		            <?php endif; ?>
				</div>
				
				<!-- Show form contact -->
				<div class="contact-form-wrap">
					<?php if ($tparams->get('show_email_form') && ($this->contact->email_to || $this->contact->user_id)) : ?>
					<div class="contact-box">
						<div class="contact-form">
							<div class="contact-title">
								<?php echo '<h2>'. JText::_('COM_CONTACT_EMAIL_FORM').'</h2>';  ?>
							</div>
							<div class="contact-body">
								<?php echo $this->loadTemplate('form');  ?>
							</div>
						</div>
					</div>
					<?php endif ;?>
				</div>
				<!-- End Show form contact -->
		</div>

		<div class="wrapper-info">

			<?php if($this->contact->misc && $tparams->get('show_misc')) :?>
				<div class="contact-misc">
					<?php echo $this->contact->misc; ?>
				</div>
			<?php endif ;?>

			<?php if ($tparams->get('show_tags', 1) && !empty($this->item->tags->itemTags)) : ?>
				<?php $this->item->tagLayout = new JLayoutFile('joomla.content.tags'); ?>
				<?php echo $this->item->tagLayout->render($this->item->tags->itemTags); ?>
			<?php endif; ?>


			<!-- Contact -->
			<?php if ($tparams->get('show_links')) : ?>
				<div class="contact-link">
				
					<?php if ($tparams->get('show_links')) : ?>
						<?php echo $this->loadTemplate('links'); ?>
					<?php endif; ?>
				</div>
			<!-- End contact-->
			<?php endif; ?>

			<?php echo $this->item->event->afterDisplayTitle; ?>

			<?php $show_contact_category = $tparams->get('show_contact_category'); ?>

			<?php if ($show_contact_category === 'show_no_link') : ?>
				<h5>
					<span class="contact-category"><?php echo $this->contact->category_title; ?></span>
				</h5>
			<?php elseif ($show_contact_category === 'show_with_link') : ?>
				<?php $contactLink = ContactHelperRoute::getCategoryRoute($this->contact->catid); ?>
				<h5>
					<span class="contact-category"><a href="<?php echo $contactLink; ?>">
						<?php echo $this->escape($this->contact->category_title); ?></a>
					</span>
				</h5>
			<?php endif; ?>

			<?php if ($tparams->get('show_contact_list') && count($this->contacts) > 1) : ?>
				<form action="#" method="get" name="selectForm" id="selectForm">
					<label for="select_contact"><?php echo JText::_('COM_CONTACT_SELECT_CONTACT'); ?></label>
					<?php echo JHtml::_('select.genericlist', $this->contacts, 'select_contact', 'class="inputbox" onchange="document.location.href = this.value"', 'link', 'name', $this->contact->link); ?>
				</form>
			<?php endif; ?>


			<?php echo $this->item->event->beforeDisplayContent; ?>

			<?php if ($tparams->get('show_user_custom_fields') && $this->contactUser) : ?>
				<?php echo $this->loadTemplate('user_custom_fields'); ?>
			<?php endif; ?>

			<?php if ($this->params->get('show_profile') && $this->contact->user_id && JPluginHelper::isEnabled('user', 'profile')) : ?>
				<div class="contact-profile-user">
					<?php echo '<h2>'. JText::_('COM_CONTACT_PROFILE').'</h2>'; ?>
					<?php echo $this->loadTemplate('profile'); ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
	<?php endif;?>
	<!-- End Override -->

	<?php if ($accordionStarted) : ?>
    <?php echo JHtml::_('bootstrap.endAccordion'); ?>
  <?php elseif ($tabSetStarted) : ?>
    <?php echo JHtml::_('bootstrap.endTabSet'); ?>
  <?php endif; ?>

	<?php if (!empty($this->item->event)) echo $this->item->event->afterDisplayContent; ?>
</div>
