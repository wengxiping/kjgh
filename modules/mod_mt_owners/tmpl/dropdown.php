<?php defined('_JEXEC') or die('Restricted access'); ?>
<select onchange="javascript:if(this.value){window.location=this.value;}" size="1" style="width:<?php echo $dropdown_width; ?>px">
	<option value="" selected><?php echo $dropdown_select_text; ?></option>
	<?php foreach( $owners AS $o ) { ?>
	<option value="<?php echo $o->url; ?>"><?php echo $o->name; ?></option>
	<?php } ?>
</select>