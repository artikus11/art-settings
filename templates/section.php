<?php
/**
 * @var array                                $section
 * @var array                                $saved_data
 * @var \Art\Settings\Renderers\PageRenderer $renderer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$title       = $section['title'] ?? '';
$description = $section['description'] ?? '';
$fields      = $section['fields'] ?? [];
?>

<div class="ast__section">
	<?php if ( ! empty( $title ) ) : ?>
		<h2 class="ast__section-title"><?php echo esc_html( $title ); ?></h2>
	<?php endif; ?>
	
	<?php if ( ! empty( $description ) ) : ?>
		<p class="description ast__section-description"><?php echo esc_html( $description ); ?></p>
	<?php endif; ?>
	
	<table class="form-table ast__table"
	       role="presentation">
		<tbody>
			<?php foreach ( $fields as $field_id => $field_object ) : ?>
				<?php
				if ( ! is_object( $field_object ) || ! method_exists( $field_object, 'render' ) ) :
					continue;
				endif;
				
				$current_value = $saved_data[ $field_id ] ?? $field_object->get_default();
				
				?>
				<tr class="ast__row ast__row--<?php echo esc_attr( $field_id ); ?>">
					<th scope="row"
					    class="ast__label-cell">
						<label for="<?php echo esc_attr( $field_id ); ?>"
						       class="ast__label">
							<?php echo esc_html( $field_object->get_label() ); ?>
						</label>
					</th>
					<td class="ast__field-cell">
						<?php echo $field_object->render( $current_value, $renderer ); ?>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>