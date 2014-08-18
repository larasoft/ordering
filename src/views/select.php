<select onchange="location = this.value;" class="form-control">
	<option value="<?php echo $disable_action; ?>">Default</option>
	<?php foreach($orders as $order): ?>
	<option value="<?php echo $order['asc_action']; ?>" <?php echo ($order['active'] == 'ASC') ? 'selected' : ''; ?> >
		<?php echo $order['asc_title']; ?>
	</option>
	<option value="<?php echo $order['desc_action']; ?>" <?php echo ($order['active'] == 'DESC') ? 'selected' : ''; ?> >
		<?php echo $order['desc_title']; ?>
	</option>
	<?php endforeach; ?>
</select>