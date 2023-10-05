<?php
namespace SCBCW;

if (!defined('ABSPATH')) exit;

class Admin_Settings_Handler {
    
    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'settings_init']);
    }

    public function add_admin_menu() {
        add_options_page('Adjust Shipping Methods', 'Adjust Shipping Methods', 'manage_options', 'adjust_shipping_methods', [$this, 'options_page']);
    }

    public function settings_init() {
        register_setting('pluginPage', 'asm_settings');

        add_settings_section(
            'asm_pluginPage_section',
            __('Adjust the weight classes and corresponding delivery classes.', 'adjust-shipping-methods'),
            [$this, 'settings_section_callback'],
            'pluginPage'
        );
    }


    public function options_page() {
        // Check if form is submitted and nonce is valid
        if (isset($_POST['your_settings_nonce_name']) && wp_verify_nonce($_POST['your_settings_nonce_name'], 'save_your_settings')) {
            // Process and save settings
            $this->save_settings($_POST);
        }
        ?>
        <form action='' method='post'>
            <h2>Adjust Shipping Methods</h2>
            <?php
            // Nonce field
            wp_nonce_field('save_your_settings', 'your_settings_nonce_name');
            
            settings_fields('pluginPage');
            do_settings_sections('pluginPage');
            
            // Display shipping methods and classes directly.
            $this->display_shipping_methods();
            
            submit_button();
            ?>
        </form>
        <?php
    }
    
    

    public function settings_section_callback() {
        echo '<p>' . __('This section is for setting up the weight limits for different shipping methods.', 'adjust-shipping-methods') . '</p>';
    }

    public function display_shipping_methods() {
        $shipping_zones = \WC_Shipping_Zones::get_zones();
        // Get saved settings
        $saved_settings = get_option('your_option_name', []);
        echo '<h3>Available Shipping Methods</h3>';
        
        foreach( $shipping_zones as $zone ) {
            echo '<div class="shipping-zone">';
            echo '<strong>' . esc_html($zone['zone_name']) . '</strong></br>';
            echo '<ul>';
            
            foreach( $zone['shipping_methods'] as $method ) {
                $saved_min_weight = $saved_settings['min_weight'][$method->title] ?? '';
                $saved_max_weight = $saved_settings['max_weight'][$method->title] ?? '';

                echo '<li>';
                echo '<div class="shipping-method">';
                echo '<strong>' . esc_html($method->title) . '</strong> - ';
                echo 'Min Weight: <input type="text" name="min_weight[' . esc_attr($method->title) . ']" value="' . esc_attr($saved_min_weight) . '" /> ';
                echo 'Max Weight: <input type="text" name="max_weight[' . esc_attr($method->title) . ']" value="' . esc_attr($saved_max_weight) . '" />';
                echo '</div>';
                
                // Display available classes with checkboxes
                $this->display_classes_with_checkboxes($method->id);
                
                echo '</li>';
            }
            echo '</ul>';
            echo '</div>';
        }
    }

    public function display_classes_with_checkboxes($method_id) {
        $shipping_classes = \WC()->shipping->get_shipping_classes();
        $saved_settings = get_option('your_option_name', []);
        echo '<div class="shipping-classes">';
        echo '<strong>Available Classes:</strong> ';
        echo '<ul>';
        
        foreach ($shipping_classes as $class) {
             // Check if this class id is in the saved settings for this method
            $is_checked = in_array($class->term_id, $saved_settings['shipping_classes'][$method_title] ?? []) ? 'checked' : '';
            echo '<li>';
            echo '<input type="checkbox" id="class_' . esc_attr($class->term_id) . '_method_' . esc_attr($method_title) . '" name="shipping_classes[' . esc_attr($method_title) . '][]" value="' . esc_attr($class->term_id) . '" ' . $is_checked . ' />';
            echo '<label for="class_' . esc_attr($class->term_id) . '_method_' . esc_attr($method_id) . '">' . esc_html($class->name) . '</label>';
            echo '</li>';
        }
        echo '</ul>';
        echo '</div>';
    }

    private function save_settings($data) {
        
        // Save settings using update_option
        update_option('your_option_name', $data);
        error_log(print_r($data, true));
    }
    
}
