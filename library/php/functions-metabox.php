<?php


/*-----------------------------------------------------------------------------------*/
/*	Custom Write Panels
/*-----------------------------------------------------------------------------------*/

/* http://webdesignfan.com/custom-write-panels-in-wordpress/ */

// custom meta boxes

// Add meta box to editor
function ufandshands_meta_add_box() {
    $metaBoxes = ufandshands_getMetaBoxes();
    
	foreach ($metaBoxes as $metaBox) {
	    add_meta_box($metaBox['id'], $metaBox['title'], 'display_html', $metaBox['page'], $metaBox['context'], $metaBox['priority'], $metaBox);
    }
}
add_action('admin_menu', 'ufandshands_meta_add_box');



// Callback function to show fields in meta box
function display_html($post, $metaBox) {
    //global $meta_box, $post; // get the variables from global $meta_box and $post
	
    // Use nonce for verification to check that the person has adequate priveleges
    //echo '<input type="hidden" name="ufclas_options_meta_box_nonce" value="', wp_create_nonce(basename(__FILE__)), '" />';
	
	// Add a nonce field so we can check for it later.
	wp_nonce_field( 'ufclas_save_meta_box_data', 'ufclas_meta_box_nonce' );

	// create the table which the options will be displayed in
    echo '<table class="form-table">';

    foreach ($metaBox['args']['fields'] as $field) { // do this for each array inside of the fields array
        // get current post meta data
        $meta = get_post_meta($post->ID, $field['id'], true);
		
		// If no value is set and there is a default value, use the default value ('std')
		$field_value = ( !$meta && isset($field['std']) )? $field['std']:$meta;
				
		echo '<tr>', // create a table row for each option
                '<th style="width:20%"><label for="', $field['id'], '">', $field['name'], '</label></th>',
                '<td>';
        switch ($field['type']) {

            case 'text': // the HTML to display for type=text options
                echo '<input type="text" name="', $field['id'], '" id="', $field['id'], '" value="', esc_attr($field_value), '" size="30" style="width:97%" />', '
', $field['desc'];
                break;     

            case 'textarea': // the HTML to display for type=textarea options
                echo '<textarea name="', $field['id'], '" id="', $field['id'], '" cols="60" rows="8" style="width:97%">', esc_html($field_value), '</textarea>', '
', $field['desc'];
                break;

            case 'select': // the HTML to display for type=select options
                echo '<select name="', $field['id'], '" id="', $field['id'], '">';
                foreach ($field['options'] as $option) {
                    echo '<option', $field_value == $option ? ' selected="selected"' : '', '>', $option, '</option>';
                }
                echo '</select>';
                break;

            case 'radio': // the HTML to display for type=radio options
                foreach ($field['options'] as $option) {
                    echo '<input type="radio" name="', $field['id'], '" value="', $option['value'], '"', $field_value == $option['value'] ? ' checked="checked"' : '', ' />', $option['name'];
                }
                break;

            case 'checkbox': // the HTML to display for type=checkbox options
                echo '<input type="checkbox" name="', $field['id'], '" id="', $field['id'], '"', $field_value ? ' checked="checked"' : '', ' />', '
', $field['desc'];
                break;
        }
        echo     '<td>',
            '</tr>';
    }

    echo '</table>';
}

// Save data from meta box
function ufandshands_meta_save_data($post_id) {
    // verify nonce -- checks that the user has access
    
	// Check if our nonce is set.
	if ( ! isset( $_POST['ufclas_meta_box_nonce'] ) ) {
		return;
	}

	// Verify that the nonce is valid.
	if ( ! wp_verify_nonce( $_POST['ufclas_meta_box_nonce'], 'ufclas_save_meta_box_data' ) ) {
		return;
	}

	// If this is an autosave, our form has not been submitted, so we don't want to do anything.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// Check the user's permissions.
	if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {

		if ( ! current_user_can( 'edit_page', $post_id ) ) {
			return;
		}

	} else {

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
	}

    // creates an array of all the metaboxes that we're saving
    $metaBoxes = ufandshands_getMetaBoxes();
    
    foreach ($metaBoxes as $metaBox) {
	foreach ($metaBox['fields'] as $field) { // save each option
			
		$old = get_post_meta($post_id, $field['id'], true);
		
		// Sanitize user input
		// @todo Look into switching to more specific sanitize filters
		$new = ( isset($_POST[$field['id']]) )? sanitize_text_field( $_POST[$field['id']] ):'';

		if ($new && $new != $old) { // compare changes to existing values
			update_post_meta($post_id, $field['id'], $new);
		} elseif ('' == $new && $old) {
			delete_post_meta($post_id, $field['id'], $old);
		}
	}
    }
}
add_action('save_post', 'ufandshands_meta_save_data'); // save the data

// define the metabox for misc options
function ufandshands_getMetaBoxes() {
    $prefix = 'custom_meta_';
    
    $metaBoxes = array();
    
	// slider options metabox, std is the default value
	$metaBoxes[] = array(
		'id' => 'ufandshands_slider_options', // the id of our meta box
		'title' => 'Featured Content Slider Options (optional) <a href="/wp-admin/themes.php?page=options-framework">Enable in Theme Options</a>', // the title of the meta box
		'page' => 'post', // display this meta box on post editing screens
		'context' => 'normal',
		'priority' => 'high', // keep it near the top
		'fields' => array( // all of the options inside of our meta box
			array(
				'name' => 'Button Text',
				'desc' => 'Enter the text that will appear as a button',
				'id' => $prefix . 'featured_content_button_text',
				'type' => 'text',
				'std' => '',
			),
			array(
				'name' => 'Disable Image Effects',
				'desc' => 'Remove the border and shadow effects applied to half-image images',
				'id' => $prefix . 'image_effect_disabled',
				'type' => 'checkbox',
				'std' => '0'
			),
			array(
				'name' => 'Full Width Image',
				'desc' => 'Image will use 100% of the allowed width. Recommended size is 930px x 325px',
				'id' => $prefix . 'image_type',
				'type' => 'checkbox',
				'std' => '0'
			),
			array(
				'name' => 'Disable Image Captions',
				'desc' => 'Disable the caption box from appearing on <em>full width images</em> (contains title, excerpt)',
				'id' => $prefix . 'featured_content_disable_captions',
				'type' => 'checkbox',
				'std' => '0'
			),
		)
	);

	// post options meta box
	$metaBoxes[] = array(
		'id' => 'ufandshands_post_options', // the id of our meta box
		'title' => 'Post Options (optional)', // the title of the meta box
		'page' => 'post', // display this meta box on post editing screens
		'context' => 'normal',
		'priority' => 'low', 
		'fields' => array( // all of the options inside of our meta box
			array(
				'name' => 'Subtitle text',
				'desc' => 'Enter the text that will appear as a secondary title',
				'id' => $prefix . 'post_subtitle',
				'type' => 'text',
				'std' => ''
			),
			array(
				'name' => 'Title Override Text',
				'desc' => 'Enter the text that will appear as the page title shown at the top of the content section of the page template',
				'id' => $prefix . 'post_title_override',
				'type' => 'text',
				'std' => ''
			),
			array(
				'name' => 'Hide Featured Image',
				'desc' => 'Hides the featured image from single post view',
				'id' => $prefix . 'post_remove_featured',
				'type' => 'checkbox',
				'std' => '0'
			),
			array(
				'name' => 'Visitor Authentication Level',
				'desc' => 'Choose authentication level for visitors of this page. GatorLink Users only works if Shibboleth is configured properly.',
				'id' => $prefix . 'visitor_auth_level',
				'type' => 'select',
				'options' => array('Public', 'WordPress Users', 'GatorLink Users'),
			),
		)
	);
		
	// page options meta box
	$metaBoxes[] = array(
		'id' => 'ufandshands_page_options', // the id of our meta box
		'title' => 'Page Options (optional)', // the title of the meta box
		'page' => 'page', // display this meta box on post editing screens
		'context' => 'normal',
		'priority' => 'low', 
		'fields' => array( // all of the options inside of our meta box
			array(
				'name' => 'Subtitle text',
				'desc' => 'Enter the text that will appear as a secondary title',
				'id' => $prefix . 'page_subtitle',
				'type' => 'text',
				'std' => ''
			),
			array(
				'name' => 'Title Override Text',
				'desc' => 'Enter the text that will appear as the page title shown at the top of the content section of the page template',
				'id' => $prefix . 'page_title_override',
				'type' => 'text',
				'std' => ''
			),
			array(
				'name' => 'Visitor Authentication Level',
				'desc' => 'Choose authentication level for visitors of this page. GatorLink Users only works if Shibboleth is configured properly.',
				'id' => $prefix . 'visitor_auth_level',
				'type' => 'select',
				'options' => array('Public', 'WordPress Users', 'GatorLink Users', 'UFAD Group' ),
			),
			array(
				'name' => 'UFAD Groups',
				'desc' => 'Enter the name(s) of the UFAD Groups allowed to access this page separated by commas.',
				'id' => $prefix . 'visitor_auth_groups',
				'type' => 'text',
				'std' => ''
			)
		)
	);
    
   return $metaBoxes;
}
?>
