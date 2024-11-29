<?php
/*
Plugin Name: GWP Custom Product Tabs
Plugin URI:
Description: A plugin to add Custom product tabs for WooCommerce
Version: 1.2
Author: Ohad Raz
Author URI: http://generatewp.com
*/
/**
* GWP_Custom_Product_Tabs
*/
class GWP_Custom_Product_Tabs{
    /**
     * $post_type
     * holds custo post type name
     * @var string
     */
    public $post_type = 'product';
    /**
     * $id
     * holds settings tab id
     * @var string
     */
    public $id = 'gwp_custom_tabs';

    /**
    * __construct
    * class constructor will set the needed filter and action hooks
    */
    function __construct(){
        if (is_admin()){
            //add settings tab
            add_filter( 'woocommerce_settings_tabs_array', array($this,'woocommerce_settings_tabs_array'), 50 );
            //show settings tab
            add_action( 'woocommerce_settings_tabs_'.$this->id, array($this,'show_settings_tab' ));
            //save settings tab
            add_action( 'woocommerce_update_options_'.$this->id, array($this,'update_settings_tab' ));

            //add tabs select field
            add_action('woocommerce_admin_field_'.$this->post_type,array($this,'show_'.$this->post_type.'_field' ),10);
            //save tabs select field
            add_action( 'woocommerce_update_option_'.$this->post_type,array($this,'save_'.$this->post_type.'_field' ),10);

            //add product tab link in admin
            add_action( 'woocommerce_product_write_panel_tabs', array($this,'woocommerce_product_write_panel_tabs' ));
            //add product tab content in admin
            add_action('woocommerce_product_data_panels', array($this,'woocommerce_product_write_panels'));
            //save product selected tabs
            add_action('woocommerce_process_product_meta', array($this,'woocommerce_process_product_meta'), 10, 2);
        }else{
            //add tabs to product page
            //add_filter( 'woocommerce_product_tabs', array($this,'woocommerce_product_tabs') );

            add_filter( 'woocommerce_product_tabs', array( $this, 'wpb_new_product_tab' ) );

            add_filter( 'woocommerce_product_tabs', array( $this, 'wpb_remove_product_tabs' ) , 98 );
        }
        //ajax search handler
        add_action('wp_ajax_woocommerce_json_custom_tabs', array($this,'woocommerce_json_custom_tabs'));
        //register_post_type

    }


    function wpb_remove_product_tabs( $tabs ) {
        //unset( $tabs['description'] );             // Remove the description tab
        unset( $tabs['reviews'] );                 // Remove the reviews tab
        //unset( $tabs['additional_information'] );  // Remove the additional information tab
        //unset( $tabs['test_tab'] );                // Remove the discount tab
        return $tabs;
    }

    /**
     * woocommerce_settings_tabs_array
     * Used to add a WooCommerce settings tab
     * @param  array $settings_tabs
     * @return array
     */
    function woocommerce_settings_tabs_array( $settings_tabs ) {
        $settings_tabs[$this->id] = __('GWP Custom Tabs','GWP');
        return $settings_tabs;
    }

    /**
     * show_settings_tab
     * Used to display the WooCommerce settings tab content
     * @return void
     */
    function show_settings_tab(){
        woocommerce_admin_fields($this->get_settings());
    }

    /**
     * update_settings_tab
     * Used to save the WooCommerce settings tab values
     * @return void
     */
    function update_settings_tab(){
        woocommerce_update_options($this->get_settings());
    }

    /**
     * get_settings
     * Used to define the WooCommerce settings tab fields
     * @return void
     */
    function get_settings(){
        $settings = array(
            'section_title' => array(
                'name'     => __('GWP Custom Tabs','GWP'),
                'type'     => 'title',
                'desc'     => '',
                'id'       => 'wc_'.$this->id.'_section_title'
            ),
            'title' => array(
                'name'     => __( 'Global Custom Tabs', 'GWP' ),
                'type'     => $this->post_type,
                'desc'     => __( 'Start typing the Custom Tab name, Used for including custom tabs on all products.', 'GWP' ),
                'desc_tip' => true,
                'default'  => '',
                'id'       => 'wc_'.$this->id.'_globals'
            ),
            'section_end' => array(
                'type' => 'sectionend',
                'id'   => 'wc_'.$this->id.'_section_end'
            )
        );
        return apply_filters( 'wc_'.$this->id.'_settings', $settings );
    }



    /**
     * save_c_p_tab_field
     * Used to save the settings field of the custom type c_p_tab
     * @param  array $field
     * @return void
     */
    function save_c_p_tab_field($field){
        if (isset($_POST[$field['id']])){
            $option_value =   $_POST[$field['id']];
            update_option($field['id'],$option_value);
        }else{
            delete_option($field['id']);
        }
    }

    /**
     * ajax_footer_js
     * Used to add needed javascript to product edit screen and custom settings tab
     * @return void
     */
    function ajax_footer_js(){
        ?>
        <script type="text/javascript">
        jQuery(document).ready(function($){
            // Ajax Chosen Product Selectors
            jQuery("select.ajax_chosen_select_tabs").select2({});
        });
        </script>
        <?php
    }

    /**
     * woocommerce_product_write_panel_tabs
     * Used to add a product custom tab to product edit screen
     * @return void
     */
    function woocommerce_product_write_panel_tabs(){
        ?>
        <li class="custom_tab">
            <a href="#custom_tab_data_ctabs">
                <?php _e('Custom Tabs', 'GWP'); ?>
            </a>
        </li>
        <?php
    }

    /**
     * woocommerce_product_write_panels
     * Used to display a product custom tab content (fields) to product edit screen
     * @return void
     */
    function woocommerce_product_write_panels() {
        global $post,$woocommerce;
        $fields = array(
            array(
                'key'   => 'custom_tabs_ids',
                'label' => __( 'Select Custom Tabs', 'GWP' ),
                'desc'  => __( 'Start typing the Custom Tab name, Used for including custom tabs.', 'GWP' )
            ),
            array(
                'key'   => 'exclude_custom_tabs_ids',
                'label' => __( 'Select Global Tabs to exclude', 'GWP' ),
                'desc'  => __( 'Start typing the Custom Tab name. used for excluding global tabs.', 'GWP' )
            )
        );
        ?>
        <div id="custom_tab_data_ctabs" class="panel woocommerce_options_panel">

                <div class="options_group">
                    <p class="form-field custom_product_tabs">
                        <label for="custom_product_tabs">Custom Tabs</label>
                        <input style="width: 50%;" id="<?php echo $post->ID; ?>" name="accordin-product-id" value="<?php echo $post->ID; ?>"  class="ajax_chosen_select_tabs" multiple="multiple" />

                    </p>
                </div>

        </div>
        <?php
        add_action('admin_footer',array($this,'ajax_footer_js'));
    }

    /**
     * woocommerce_process_product_meta
     * used to save product custom tabs meta
     * @param  int $post_id
     * @return void
     */
    function woocommerce_process_product_meta( $post_id ) {
        foreach (array('exclude_custom_tabs_ids','custom_tabs_ids') as $key) {
            if (isset($_POST[$key]))
                update_post_meta( $post_id, $key, $_POST[$key]);
            else
                delete_post_meta( $post_id, $key);
        }
    }

    /**
     * woocommerce_json_custom_tabs
     * An AJAX handler to list tabs for tabs field
     * prints out json of {tab_id: tab_name}
     * @return void
     */
    function woocommerce_json_custom_tabs(){
        check_ajax_referer( 'search-products-tabs', 'security' );
        header( 'Content-Type: application/json; charset=utf-8' );
        $term = (string) urldecode(stripslashes(strip_tags($_GET['term'])));
        if (empty($term)) die();
        $post_types = array('products');
        if ( is_numeric( $term ) ) {
            //by tab id
            $args = array(
                'post_type'      => $post_types,
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'post__in'       => array(0, $term),
                'fields'         => 'ids'
            );

            $args2 = array(
                'post_type'      => $post_types,
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'post_parent'    => $term,
                'fields'         => 'ids'
            );

            $posts = array_unique(array_merge( get_posts( $args ), get_posts( $args2 )));

        } else {
            //by name
            $args = array(
                'post_type'      => $post_types,
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                's'              => $term,
                'fields'         => 'ids'
            );
            $posts = array_unique( get_posts( $args ) );
        }

        $found_tabs = array();

        if ( $posts ) foreach ( $posts as $post_id ) {

            $found_tabs[ $post_id ] = get_the_title($post_id);
        }

        $found_tabs = apply_filters( 'woocommerce_json_search_found_tabs', $found_tabs );
        echo json_encode( $found_tabs );

        die();
    }



    function wpb_new_product_tab( $tabs ) {
        $value_1 = get_post_meta( get_the_ID() , 'textarea_1', true );

        if ( ! empty( $value_1 ) ) {
          $tabs['test_tab'] = array(
              'title'       => __( 'Description ' . get_the_title(), 'text-domain' ),
              'priority'    => 50,
              'callback' => array($this,'render_tab'),
              'content'  => $value_1 //this allows shortcodes in custom tabs
          );
        }

        $value_2 = get_post_meta( get_the_ID() , 'textarea_2', true );

        if ( ! empty( $value_2 ) ) {
          $tabs['test_tab1'] = array(
              'title'       => __( 'Ingredients', 'text-domain' ),
              'priority'    => 50,
              'callback' => array($this,'render_tab'),
              'content'  => $value_2 //this allows shortcodes in custom tabs
          );
        }


        $value_3 = get_post_meta( get_the_ID() , 'textarea_3', true );

        if ( ! empty( $value_3 ) ) {
          $tabs['test_tab2'] = array(
              'title'       => __( 'Directions for use', 'text-domain' ),
              'priority'    => 50,
              'callback' => array($this,'render_tab'),
              'content'  => $value_3 //this allows shortcodes in custom tabs
          );
        }


        $value_4 = get_post_meta( get_the_ID() , 'textarea_4', true );

        if ( ! empty( $value_4 ) ) {
          $tabs['test_tab2'] = array(
              'title'       => __( 'Composition', 'text-domain' ),
              'priority'    => 50,
              'callback' => array( $this,'render_tab' ),
              'content'  => $value_4 //this allows shortcodes in custom tabs
          );
        }


        $value_5 = get_post_meta( get_the_ID() , 'textarea_5', true );

        if ( ! empty( $value_4 ) ) {
          $tabs['test_tab2'] = array(
              'title'       => __( 'Composition', 'text-domain' ),
              'priority'    => 50,
              'callback' => array( $this,'render_tab' ),
              'content'  => $value_5 //this allows shortcodes in custom tabs
          );
        }

        return $tabs;
    }

    function wpb_new_product_tab_content() {
        // The new tab content
        echo 'Discount';
        echo 'Here\'s your new discount product tab.';
    }



    /**
     * render_tab
     * Used to render tabs on product view page
     * @param  string $key
     * @param  array  $tab
     * @return void
     */
    function render_tab($key,$tab){
        global $post;
        echo '<h2>'.apply_filters('GWP_custom_tab_title',$tab['title'],$tab,$key).'</h2>';
        echo apply_filters('GWP_custom_tab_content',$tab['content'],$tab,$key);
    }


    function post_exists($post_id){
    	return is_string(get_post_status( $post_id ) );
    }



    function get_custom_tabs_list(){
        $args = array(
            'post_type'      => array($this->post_type),
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'fields'         => 'ids'
        );
        $found_tabs = array();
        $posts = get_posts($args);
        if ( $posts ) foreach ( $posts as $post_id ) {

            $found_tabs[ $post_id ] = get_the_title($post_id);
        }
        return $found_tabs;
    }




}//end GWP_Custom_Product_Tabs class.
new GWP_Custom_Product_Tabs();
