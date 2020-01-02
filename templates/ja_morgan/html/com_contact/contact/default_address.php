<?php
/**
 * ------------------------------------------------------------------------
 * JA Focus Template
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2011 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - Copyrighted Commercial Software
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites:  http://www.joomlart.com -  http://www.joomlancers.com
 * This file may not be redistributed in whole or significant part.
 * ------------------------------------------------------------------------
 */

defined('_JEXEC') or die;

/**
 * marker_class: Class based on the selection of text, none, or icons
 */
?>
<dl class="contact-address dl-horizontal row equal-height equal-height-child" itemprop="address" itemscope itemtype="http://schema.org/PostalAddress">
	<?php if (($this->params->get('address_check') > 0) &&
		($this->contact->address || $this->contact->suburb  || $this->contact->state || $this->contact->country || $this->contact->postcode)) : ?>
		<?php if ($this->params->get('address_check') > 0) : ?>
			<dt>
				<span class="<?php echo $this->params->get('marker_class'); ?>" >
					<?php echo $this->params->get('marker_address'); ?>
				</span>
			</dt>
		<?php endif; ?>

		<?php if ($this->contact->address && $this->params->get('show_street_address')) : ?>
			<dd class="address-detail col col-sm-6 col-md-3">
				<div class="item">
					<span class="contact-street" itemprop="streetAddress">
						<i class="fa fa-map-marker"> </i>
						<span class="title"><?php  echo JText::_('TPL_CONTACT_ADDRESS') ;?></span>
						<span class="content"><?php echo $this->contact->address; ?></span>
					</span>
				</div>
			</dd>
		<?php endif; ?>

		<?php if ($this->contact->suburb && $this->params->get('show_suburb')) : ?>
			<dd class="address-detail col col-sm-6 col-md-3">
				<div class="item">
					<span class="contact-suburb" itemprop="addressLocality">
						<i class="fa fa-location-arrow"></i>
						<span class="title"><?php  echo JText::_('TPL_CONTACT_ADDRESS') ;?></span>
						<span class="content"><?php echo $this->contact->suburb; ?></span>
					</span>
				</div>
			</dd>
		<?php endif; ?>
		<?php if ($this->contact->state && $this->params->get('show_state')) : ?>
			<dd class="address-detail col col-sm-6 col-md-3">
				<div class="item">
					<span class="contact-state" itemprop="addressRegion">
						<i class="fa fa-location-arrow"></i>
						<span class="title"><?php  echo JText::_('TPL_CONTACT_STATE') ;?></span>
						<span class="content"><?php echo $this->contact->state; ?></span>
					</span>
				</div>
			</dd>
		<?php endif; ?>
		<?php if ($this->contact->postcode && $this->params->get('show_postcode')) : ?>
			<dd class="address-detail col col-sm-6 col-md-3">
				<div class="item">
					<span class="contact-postcode" itemprop="postalCode">
						<i class="fa fa-magic"></i>
						<span class="title"><?php  echo JText::_('TPL_CONTACT_POSTCODE') ;?></span>
						<span class="content"><?php echo $this->contact->postcode; ?>
					</span>
				</div>
			</dd>
		<?php endif; ?>
		<?php if ($this->contact->country && $this->params->get('show_country')) : ?>
		<dd class="address-detail col col-sm-6 col-md-3">
			<div class="item">
				<span class="contact-country" itemprop="addressCountry">
					<i class="fa fa-building-o"></i>
					<span class="title"><?php  echo JText::_('TPL_CONTACT_COUNTRY') ;?></span>
					<span class="content"><?php echo $this->contact->country ; ?></span>
				</span>	
			</div>
		</dd>
		<?php endif; ?>
	<?php endif; ?>

<?php if ($this->contact->email_to && $this->params->get('show_email')) : ?>
	<dt>
		<span class="<?php echo $this->params->get('marker_class'); ?>" itemprop="email">
			<?php echo nl2br($this->params->get('marker_email')); ?>
		</span>
	</dt>
	<dd class="address-detail col col-sm-6 col-md-3">
		<div class="item">
			<span class="contact-emailto">
				<i class="fa fa-envelope-o"></i>
				<span class="title"><?php  echo JText::_('TPL_CONTACT_EMAIL') ;?></span>
				<span class="content"><?php echo $this->contact->email_to; ?></span>
			</span>		
		</div>
	</dd>
<?php endif; ?>

<?php if ($this->contact->telephone && $this->params->get('show_telephone')) : ?>
	<dt>
		<span class="<?php echo $this->params->get('marker_class'); ?>">
			<?php echo $this->params->get('marker_telephone'); ?>
		</span>
	</dt>
	<dd class="address-detail col col-sm-6 col-md-3">
		<div class="item">
			<span class="contact-telephone" itemprop="telephone">
				<i class="fa fa-phone"></i>
				<span class="title"><?php  echo JText::_('TPL_CONTACT_PHONE') ;?></span>
				<span class="content"><?php echo nl2br($this->contact->telephone); ?></span>
			</span>		
		</div>
	</dd>
<?php endif; ?>
<?php if ($this->contact->fax && $this->params->get('show_fax')) : ?>
	<dt>
		<span class="<?php echo $this->params->get('marker_class'); ?>">
			<i class="fa fa-print"></i>
			<span><?php  echo JText::_('TPL_CONTACT_FAX') ;?></span>
			<?php echo $this->params->get('marker_fax'); ?>
		</span>
	</dt>
	<dd class="address-detail col col-sm-6 col-md-3">
		<div class="item">
			<span class="contact-fax" itemprop="faxNumber">
				<i class="fa fa-print"></i>
				<span class="title"><?php  echo JText::_('TPL_CONTACT_FAX') ;?></span>
				<span class="content"><?php echo nl2br($this->contact->fax); ?></span>
			</span>		
		</div>
	</dd>
<?php endif; ?>
<?php if ($this->contact->mobile && $this->params->get('show_mobile')) :?>
	<dt>
		<span class="<?php echo $this->params->get('marker_class'); ?>" >
			<?php echo $this->params->get('marker_mobile'); ?>
		</span>
	</dt>
	<dd class="address-detail col col-sm-6 col-md-3">
		<div class="item">
			<span class="contact-mobile" itemprop="telephone">
				<i class="fa fa-phone-square"></i>
				<span class="title"><?php  echo JText::_('TPL_CONTACT_MOBILE') ;?></span>
				<span class="content"><?php echo nl2br($this->contact->mobile); ?></span>
			</span>		
		</div>
	</dd>
<?php endif; ?>
<?php if ($this->contact->webpage && $this->params->get('show_webpage')) : ?>
	<dt>
		<span class="<?php echo $this->params->get('marker_class'); ?>" >
		</span>
	</dt>
	<dd class="address-detail col col-sm-6 col-md-3">
		<div class="item">
			<span class="contact-webpage">
				<i class="fa fa-globe"></i>
				<span class="title"><?php  echo JText::_('TPL_CONTACT_WEBSITE') ;?></span>
				<a href="<?php echo $this->contact->webpage; ?>" target="_blank" itemprop="url"><?php echo $this->contact->webpage; ?></a>
			</span>		
		</div>
	</dd>
<?php endif; ?>
</dl>
