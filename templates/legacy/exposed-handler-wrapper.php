<?php
/**
 * @var array $item
 * @var array $values
 * @var string $exposed_form
 */
?>
<div class="query-exposed-<?php print $item['name']; ?>">
	<?php if ( ! empty( $item['values']['exposed_label'] ) ) { ?>
		<label
			class="query-exposed-label query-exposed-label-<?php print $item['name']; ?>">
			<?php print $item['values']['exposed_label']; ?>
		</label>
	<?php } ?>
	<!-- exposed-<?php print $item['name']; ?> -->
	<?php $item['exposed_form']( $item, $values ); ?>

	<?php if ( ! empty( $item['values']['exposed_desc'] ) ) { ?>
		<div
			class="query-exposed-description query-exposed-description-<?php print $item['name']; ?>">
			<?php print $item['values']['exposed_desc']; ?>
		</div>
	<?php } ?>
</div>
