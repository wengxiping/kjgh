<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_contact
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * marker_class: Class based on the selection of text, none, or icons
 */
?>
<dl class="contact-address dl-horizontal" itemprop="address" itemscope itemtype="http://schema.org/PostalAddress">
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
			<dd>
				<span class="contact-street" itemprop="streetAddress">
					<span class="fa fa-map-marker"> </span><?php echo $this->contact->address .'<br/>'; ?>
				</span>
			</dd>
		<?php endif; ?>

		<?php if ($this->contact->suburb && $this->params->get('show_suburb')) : ?>
			<dd>
				<span class="contact-suburb" itemprop="addressLocality">
					<span class="fa fa-location-arrow"></span><?php echo $this->contact->suburb .'<br/>'; ?>
				</span>
			</dd>
		<?php endif; ?>
		<?php if ($this->contact->state && $this->params->get('show_state')) : ?>
			<dd>
				<span class="contact-state" itemprop="addressRegion">
					<span class="fa fa-location-arrow"></span><?php echo $this->contact->state . '<br/>'; ?>
				</span>
			</dd>
		<?php endif; ?>
		<?php if ($this->contact->postcode && $this->params->get('show_postcode')) : ?>
			<dd>
				<span class="contact-postcode" itemprop="postalCode">
					<span class="fa fa-magic"></span><?php echo $this->contact->postcode .'<br/>'; ?>
				</span>
			</dd>
		<?php endif; ?>
		<?php if ($this->contact->country && $this->params->get('show_country')) : ?>
		<dd>
			<span class="contact-country" itemprop="addressCountry">
				<span class="fa fa-building-o"></span><?php echo $this->contact->country .'<br/>'; ?>
			</span>
		</dd>
		<?php endif; ?>
	<?php endif; ?>

<?php if ($this->contact->email_to && $this->params->get('show_email')) : ?>
	<dt>
		<span class="<?php echo $this->params->get('marker_class'); ?>" itemprop="email">
			<?php echo nl2br($this->params->get('marker_email')); ?>
		</span>
	</dt>
	<dd>
		<span class="contact-emailto">
			<span class="fa fa-envelope-o"></span><?php echo $this->contact->email_to; ?>
		</span>
	</dd>
<?php endif; ?>

<?php if ($this->contact->telephone && $this->params->get('show_telephone')) : ?>
	<dt>
		<span class="<?php echo $this->params->get('marker_class'); ?>">
			<?php echo $this->params->get('marker_telephone'); ?>
		</span>
	</dt>
	<dd>
		<span class="contact-telephone" itemprop="telephone">
			<span class="fa fa-phone"></span><?php echo nl2br($this->contact->telephone); ?>
		</span>
	</dd>
<?php endif; ?>
<?php if ($this->contact->fax && $this->params->get('show_fax')) : ?>
	<dt>
		<span class="<?php echo $this->params->get('marker_class'); ?>">
			<span class="fa fa-print"></span><?php echo $this->params->get('marker_fax'); ?>
		</span>
	</dt>
	<dd>
		<span class="contact-fax" itemprop="faxNumber">
			<span class="fa fa-print"></span><?php echo nl2br($this->contact->fax); ?>
		</span>
	</dd>
<?php endif; ?>
<?php if ($this->contact->mobile && $this->params->get('show_mobile')) :?>
	<dt>
		<span class="<?php echo $this->params->get('marker_class'); ?>" >
			<?php echo $this->params->get('marker_mobile'); ?>
		</span>
	</dt>
	<dd>
		<span class="contact-mobile" itemprop="telephone">
			<span class="fa fa-phone-square"></span><?php echo nl2br($this->contact->mobile); ?>
		</span>
	</dd>
<?php endif; ?>
<?php if ($this->contact->webpage && $this->params->get('show_webpage')) : ?>
	<dt>
		<span class="<?php echo $this->params->get('marker_class'); ?>" >
		</span>
	</dt>
	<dd>
		<span class="contact-webpage">
			<span class="fa fa-globe"></span><a href="<?php echo $this->contact->webpage; ?>" target="_blank" itemprop="url">
			<?php echo $this->contact->webpage; ?></a>
		</span>
	</dd>
<?php endif; ?>
</dl>
