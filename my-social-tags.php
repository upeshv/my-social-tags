<?php
/**
 * Plugin Name: My Social Tags
 * Plugin URI: https://github.com/upeshv/my-social-tags
 * Description: This plugin add's Social Open Graph tags to our single posts. It enables any web page to become a rich object in a social graph. For instance, this is used on Facebook, twitter, Linkedin etc to allow any web page to have the same functionality as any other object on this social sites.
 * Tags : Social Open Graph, Open Graph protocol, og:title, og:type, og:url , og:image
 * Version: 2.0.0
 * Author: Upesh Vishwakarma
 * Author URI: https://github.com/upeshv/
 * License: GPL2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

function mft_add_additional_fields_meta_box()
{
	add_meta_box(
		'mft_additional_fields_meta_box', // $id
		'Optional Metadata', // $title
		'mft_show_additional_fields_meta_box', // $callback
		'post', // $screen
		'normal', // $context
		'high' // $priority
	);
}
add_action('add_meta_boxes', 'mft_add_additional_fields_meta_box');

function mft_show_additional_fields_meta_box()
{
	global $post;
	$meta = get_post_meta($post->ID, 'additional_fields', true); ?>

	<input type="hidden" name="your_meta_box_nonce" value="<?php echo wp_create_nonce(basename(__FILE__)); ?>">

	<!-- All fields will go here -->
	<p>
		<label for="additional_fields[1]"><b>OG:Type</b> (Type of your object.)</label>
		<br>
		<input type="text" name="additional_fields[1]" id="additional_fields[1]" class="regular-text" value="<?php echo $meta['1']; ?>">
	</p>
	<p>
		<label for="additional_fields[2]"><b>OG:Description</b> (A one to two sentence description of your object.)</label>
		<br>
		<input type="text" name="additional_fields[2]" id="additional_fields[2]" class="regular-text" value="<?php echo $meta['2']; ?>">
	</p>
	<p>
		<label for="additional_fields[3]"><b>OG:Audio</b> (A URL to an audio file to accompany this object)</label>
		<br>
		<input type="text" name="additional_fields[3]" id="additional_fields[3]" class="regular-text" value="<?php echo $meta['3']; ?>">
	</p>
	<p>
		<label for="additional_fields[4]"><b>OG:Video</b> (A URL to a video file that complements this object.)</label>
		<br>
		<input type="text" name="additional_fields[4]" id="additional_fields[4]" class="regular-text" value="<?php echo $meta['4']; ?>">
	</p>
	<p>
		<label for="additional_fields[5]"><b>OG:Determiner</b> (The word that appears before this object's title in a sentence. An enum of (a, an, the, "", auto). If auto is chosen, the consumer of your data should chose between "a" or "an". Default is "" (blank).)</label>
		<br>
		<input type="text" name="additional_fields[5]" id="additional_fields[5]" class="regular-text" value="<?php echo $meta['5']; ?>">
	</p>
	<p>
		<label for="additional_fields[6]"><b>OG:Locale</b> (The locale these tags are marked up in. Of the format language_TERRITORY. Default is en_US.)</label>
		<br>
		<input type="text" name="additional_fields[6]" id="additional_fields[6]" class="regular-text" value="<?php echo $meta['6']; ?>">
	</p>
	<p>
		<label for="additional_fields[7]"><b>OG:Locale Alternate</b> (An array of other locales this page is available in.)</label>
		<br>
		<input type="text" name="additional_fields[7]" id="additional_fields[7]" class="regular-text" value="<?php echo $meta['7']; ?>">
	</p>


<?php
}


function mft_save_additional_fields_meta($post_id)
{
	// verify nonce
	if (!wp_verify_nonce($_POST['your_meta_box_nonce'], basename(__FILE__))) {
		return $post_id;
	}
	// check autosave
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return $post_id;
	}
	// check permissions
	if ('page' === $_POST['post_type']) {
		if (!current_user_can('edit_page', $post_id)) {
			return $post_id;
		} elseif (!current_user_can('edit_post', $post_id)) {
			return $post_id;
		}
	}

	$old = get_post_meta($post_id, 'additional_fields', true);
	$new = $_POST['additional_fields'];

	// Sanitzing list of array
	function sanitize_text_or_array_field($new)
	{
		if (is_string($new)) {
			$new = sanitize_text_field($new);
		} elseif (is_array($new)) {
			foreach ($new as $key => &$value) {
				if (is_array($value)) {
					$value = sanitize_text_or_array_field($value);
				} else {
					$value = sanitize_text_field($value);
				}
			}
		}
		return $new;
	}

	if ($new && $new !== $old) {
		update_post_meta($post_id, 'additional_fields', $new);
	} elseif ('' === $new && $old) {
		delete_post_meta($post_id, 'additional_fields', $old);
	}
}
add_action('save_post', 'mft_save_additional_fields_meta', 10, 2);


function mft_my_social_tags()
{
	if (is_single()) {
		?>

		<meta property="og:title" content="<?php esc_attr(the_title()); ?>" />
		<meta property="og:site_name" content="<?php esc_attr(bloginfo('name')); ?>" />
		<meta property="og:url" content="<?php esc_url(the_permalink()); ?>" />
		<?php
		if (has_post_thumbnail()) :
			$image = wp_get_attachment_image_src(get_post_thumbnail_id(), 'large');
			?>
			<meta property="og:image" content="<?php echo $image[0]; ?>" /> <?php endif; ?>
		<?php
		global $post;
		while (have_posts()) : the_post();
			$meta = get_post_meta($post->ID, 'additional_fields', true); ?>

			<?php if ($meta['1'] != '') { ?>
				<meta property="og:type" content="<?php echo esc_attr($meta['1']); ?>" /> <?php } ?>
			<?php if ($meta['2'] != '') { ?>
				<meta property="og:description" content="<?php echo esc_attr($meta['2']); ?>" /> <?php } ?>
			<?php if ($meta['3'] != '') { ?>
				<meta property="og:audio" content="<?php echo esc_url($meta['3']); ?>" /> <?php } ?>
			<?php if ($meta['4'] != '') { ?>
				<meta property="og:video" content="<?php echo esc_url($meta['4']); ?>" /> <?php } ?>
			<?php if ($meta['5'] != '') { ?>
				<meta property="og:determiner" content="<?php echo esc_attr($meta['5']); ?>" /> <?php } ?>
			<?php if ($meta['6'] != '') { ?>
				<meta property="og:locale" content="<?php echo esc_attr($meta['6']); ?>" /> <?php } ?>
			<?php if ($meta['7'] != '') { ?>
				<meta property="og:locale:alternate" content="<?php echo esc_attr($meta['7']); ?>" /> <?php } ?>

		<?php endwhile; ?>

	<?php
}
}
add_action('wp_head', 'mft_my_social_tags');

?>