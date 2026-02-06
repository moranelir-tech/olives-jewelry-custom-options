<?php
/**
 * Plugin Name: Olives Jewelry Custom Options
 * Description: מערכת חריטה מקצועית עם ממשק Drag & Drop ותמיכה במחירים דינמיים.
 * Version:     1.2.0
 * Author:      Olives Jewelry
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'OJC_URL', plugin_dir_url( __FILE__ ) );

// 1. טעינת סקריפטים לניהול
add_action( 'admin_enqueue_scripts', function( $hook ) {
    global $post_type;
    if ( 'product' !== $post_type ) return;

    wp_enqueue_script( 'sortable-js', 'https://cdn.jsdelivr.net/npm/sortablejs@1.14.0/Sortable.min.js', array(), '1.14.0', true );
    wp_enqueue_script( 'ojc-admin-script', OJC_URL . 'assets/js/admin-script.js', array( 'jquery', 'sortable-js' ), time(), true );
    
    wp_localize_script( 'ojc-admin-script', 'ojc_vars', array(
        'existing_fields' => get_post_meta( get_the_ID(), '_ojc_fields_data', true ) ?: []
    ));
});

// 2. הוספת לשונית בעריכת מוצר
add_filter( 'woocommerce_product_data_tabs', function( $tabs ) {
    $tabs['ojc_custom_options'] = array(
        'label'    => 'וריאציות חריטה',
        'target'   => 'ojc_custom_options_data',
        'class'    => array( 'show_if_simple', 'show_if_variable' ),
        'priority' => 21,
    );
    return $tabs;
});

// 3. ממשק הניהול בלשונית
add_action( 'woocommerce_product_data_panels', function() {
    ?>
    <div id="ojc_custom_options_data" class="panel woocommerce_options_panel hidden" style="padding: 20px;">
        <h3 style="margin-top:0;">ניהול שדות חריטה (Drag & Drop)</h3>
        <ul id="ojc-sortable-list" style="margin-bottom:20px; list-style:none; padding:0;"></ul>
        <button type="button" class="button button-primary" id="ojc-add-new-field">+ הוסף שדה חדש</button>

        <style>
            .ojc-field-row { background: #fff; border: 1px solid #ccd0d4; margin-bottom: 15px; border-radius: 4px; box-shadow: 0 1px 1px rgba(0,0,0,.04); }
            .ojc-field-header { cursor: move; background: #f7f7f7; padding: 10px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; }
            .ojc-field-body { padding: 15px; display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
            .ojc-options-wrapper { background: #f9f9f9; padding: 15px; border-top: 1px solid #eee; }
            .ojc-option-item { display: flex; gap: 10px; margin-bottom: 8px; }
            .ojc-remove-field { color: #a00; cursor: pointer; border: none; background: none; font-weight: bold; }
        </style>
    </div>
    <?php
});

// 4. שמירת הנתונים
add_action( 'woocommerce_process_product_meta', function( $post_id ) {
    if ( isset( $_POST['ojc_fields'] ) ) {
        update_post_meta( $post_id, '_ojc_fields_data', $_POST['ojc_fields'] );
    } else {
        delete_post_meta( $post_id, '_ojc_fields_data' );
    }
});
