<?php
/**
 * @var array $section
 * @var array $saved_data
 * @var \Art\Settings\Renderers\PageRenderer $renderer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$title       = $section['title'] ?? '';
$description = $section['description'] ?? '';
$fields      = $section['fields'] ?? [];
?>

<div class="art-settings-section">
	<?php if ( ! empty( $title ) ) : ?>
		<h2><?php echo esc_html( $title ); ?></h2>
	<?php endif; ?>

	<?php if ( ! empty( $description ) ) : ?>
		<p class="description"><?php echo esc_html( $description ); ?></p>
	<?php endif; ?>

	<table class="form-table" role="presentation">
		<tbody>
			<?php foreach ( $fields as $field_id => $field_object ) : ?>
				<?php
				if ( ! is_object( $field_object ) || ! method_exists( $field_object, 'render' ) ) {
					continue;
				}
				$current_value = $saved_data['fields'][ $field_id ] ?? $field_object->get_default();
				?>
				<tr>
					<th scope="row">
						<label for="<?php echo esc_attr( $field_id ); ?>">
							<?php echo esc_html( $field_object->get_label() ); ?>
						</label>
					</th>
					<td>
						<?php echo $field_object->render( $current_value ); ?>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>