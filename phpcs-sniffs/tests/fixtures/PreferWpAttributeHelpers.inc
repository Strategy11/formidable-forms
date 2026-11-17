<input <?php echo $value === 'yes' ? 'checked="checked"' : ''; ?> />
<option <?php echo $value == 1 ? 'selected' : ''; ?>>A</option>
<input <?php echo $value === 1 ? 'disabled="disabled"' : ''; ?> />
<input <?php echo $value === 1 ? 'readonly="readonly"' : ''; ?> />
<?php
echo $value ? 'checked' : '';
$x = $value ? 'selected' : '';
?><p><?php echo $value ? 'disabled' : ''; ?></p>
<input <?php echo $value ? 'readonly onclick="return false;"' : ''; ?> />
<input <?php echo $value ? 'checked' : 'unchecked'; ?> />
<input <?php echo $value = $other ? 'checked' : ''; ?> />
<input <?php echo print $value ? 'checked' : ''; ?> />
<input data-state="<?php echo $value ? 'checked' : ''; ?>" />
<option data-value='<?php echo $value ? 'selected' : ''; ?>'>A</option>
