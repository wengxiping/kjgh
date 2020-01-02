<?php
	$items = $displayData['items'];
?>
Hello,<br /><br />

This is your user submitted data:<br /><br />

<?php foreach ($items as $item) : ?>
<strong><?php echo $item['title'] ?></strong>
<pre><?php echo $item['value'] ?></pre>
<?php endforeach ?>

<br />
Thank you,