<?php
/**
 * Calls the class on the post edit screen.
 */
function call_someClass() {
    new someClass();
}

if ( is_admin() ) {
    add_action( 'load-post.php',     'call_someClass' );
    add_action( 'load-post-new.php', 'call_someClass' );
}

/**
 * The Class.
 */
class someClass {

    /**
     * Hook into the appropriate actions when the class is constructed.
     */
    public function __construct() {
        add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
        add_action( 'save_post',      array( $this, 'save'         ) );
    }

    /**
     * Adds the meta box container.
     */
    public function add_meta_box( $post_type ) {
        // Limit meta box to certain post types.
        $post_types = array( 'product' );

        if ( in_array( $post_type, $post_types ) ) {
            add_meta_box(
                'some_meta_box_name',
                __( 'Some Meta Box Headline', 'woocommerce-according-tabs' ),
                array( $this, 'render_meta_box_content' ),
                $post_type,
                'advanced',
                'high'
            );
        }
    }

    /**
     * Save the meta when the post is saved.
     *
     * @param int $post_id The ID of the post being saved.
     */
    public function save( $post_id ) {

        /*
         * We need to verify this came from the our screen and with proper authorization,
         * because save_post can be triggered at other times.
         */

        // Check if our nonce is set.
        if ( ! isset( $_POST['myplugin_inner_custom_box_nonce'] ) ) {
            return $post_id;
        }

        $nonce = $_POST['myplugin_inner_custom_box_nonce'];

        // Verify that the nonce is valid.
        if ( ! wp_verify_nonce( $nonce, 'myplugin_inner_custom_box' ) ) {
            return $post_id;
        }

        /*
         * If this is an autosave, our form has not been submitted,
         * so we don't want to do anything.
         */
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return $post_id;
        }

        // Check the user's permissions.
        if ( 'page' == $_POST['post_type'] ) {
            if ( ! current_user_can( 'edit_page', $post_id ) ) {
                return $post_id;
            }
        } else {
            if ( ! current_user_can( 'edit_post', $post_id ) ) {
                return $post_id;
            }
        }

        /* OK, it's safe for us to save the data now. */

        // Sanitize the user input.
        $textarea_1 = sanitize_text_field( $_POST['textarea_input_1'] );
        $textarea_2 = sanitize_text_field( $_POST['textarea_input_2'] );
        $textarea_3 = sanitize_text_field( $_POST['textarea_input_3'] );
        $textarea_4 = sanitize_text_field( $_POST['textarea_input_4'] );
        $textarea_5 = sanitize_text_field( $_POST['textarea_input_5'] );

        // Update the meta field.
        update_post_meta( $post_id, 'textarea_1', $textarea_1 );
        update_post_meta( $post_id, 'textarea_2', $textarea_2 );
        update_post_meta( $post_id, 'textarea_3', $textarea_3 );
        update_post_meta( $post_id, 'textarea_4', $textarea_4 );
        update_post_meta( $post_id, 'textarea_5', $textarea_5 );

    }


    /**
     * Render Meta Box content.
     *
     * @param WP_Post $post The post object.
     */
    public function render_meta_box_content( $post ) {

        // Add an nonce field so we can check for it later.
        wp_nonce_field( 'myplugin_inner_custom_box', 'myplugin_inner_custom_box_nonce' );

        // Use get_post_meta to retrieve an existing value from the database.
        $value_1 = get_post_meta( $post->ID, 'textarea_1', true );
        $value_2 = get_post_meta( $post->ID, 'textarea_2', true );
        $value_3 = get_post_meta( $post->ID, 'textarea_3', true );
        $value_4 = get_post_meta( $post->ID, 'textarea_4', true );
        $value_5 = get_post_meta( $post->ID, 'textarea_5', true );

        // Display the form, using the current value.
        //print_r( $value_1 );
        ?>
        <style>
          .inside {
            display: grid;
          }
        </style>

        <label for="textarea_input_1" >
            <?php _e( 'First According Textarea', 'woocommerce-according-tabs' ); ?>
        </label>
        <textarea rows="4" cols="50" type="textarea" id="textarea_input_1" name="textarea_input_1" value="<?php print esc_attr( $value_1 ); ?>" ><?php print esc_attr( $value_1 ); ?></textarea>

        <label for="textarea_input_2" >
            <?php _e( 'Second According Textarea', 'woocommerce-according-tabs' ); ?>
        </label>
        <textarea rows="4" cols="50" type="textarea" id="textarea_input_2" name="textarea_input_2" value="<?php echo esc_attr( $value_2 ); ?>" ><?php echo esc_attr( $value_2 ); ?></textarea>


        <label for="textarea_input_3" >
            <?php _e( 'Third According Textarea', 'woocommerce-according-tabs' ); ?>
        </label>
        <textarea rows="4" cols="50" id="textarea_input_3" name="textarea_input_3" value="<?php echo esc_attr( $value_3 ); ?>" ><?php echo esc_attr( $value_3 ); ?></textarea>


        <label for="textarea_input_4" >
            <?php _e( 'Fourth According Textarea', 'woocommerce-according-tabs' ); ?>
        </label>
        <textarea rows="4" cols="50" id="textarea_input_4" name="textarea_input_4" value="<?php echo esc_attr( $value_4 ); ?>" ><?php echo esc_attr( $value_4 ); ?></textarea>


        <label for="textarea_input_5" >
            <?php _e( 'Fifth According Textarea', 'woocommerce-according-tabs' ); ?>
        </label>
        <textarea rows="4" cols="50" id="textarea_input_5" name="textarea_input_5" value="<?php echo esc_attr( $value_5 ); ?>" ><?php echo esc_attr( $value_5 ); ?></textarea>
        <?php
    }
}
