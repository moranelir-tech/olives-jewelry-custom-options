<?php
/**
 * Plugin Name: Olives Jewelry Custom Options Pro
 * Description: מנוע לבניית חוקי חריטה גלובליים ושיוך למוצרים.
 * Version:     3.0.0
 * Author:      Olives Jewelry
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'OJC_URL', plugin_dir_url( __FILE__ ) );

// 1. יצירת תפריט ניהול ראשי
add_action('admin_menu', function() {
    add_menu_page(
        'ניהול חריטות',     // שם העמוד
        'ניהול חריטות',     // שם בתפריט
        'manage_options',   // הרשאות
        'ojc-global-builder', // Slug
        'ojc_render_global_builder', // פונקציית התצוגה
        'dashicons-art',    // אייקון
        30                  // מיקום
    );
});

// 2. פונקציית התצוגה של הפאנל המרכזי
function ojc_render_global_builder() {
    // שליפת כל הסטים ששמרנו
    $global_sets = get_option('ojc_global_sets', array());
    ?>
    <div class="wrap" id="ojc-global-app">
        <h1>בונה סטים גלובליים לחריטה</h1>
        <p>כאן אתה בונה את ה"חוקים". אחרי שתשמור סט, תוכל לשייך אותו לכל מוצר בעמוד עריכת המוצר.</p>
        
        <div id="ojc-sets-list">
            </div>

        <button type="button" class="button button-primary" id="ojc-create-new-set">
            + צור סט חוקים חדש
        </button>

        <div id="ojc-builder-modal" style="display:none; margin-top:20px; background:#fff; border:1px solid #ccc; padding:20px;">
            <h2 id="set-title">עריכת סט</h2>
            <input type="text" id="ojc-set-name" placeholder="שם הסט (למשל: חריטה דו-צדדית)" style="width:100%; font-size:1.5em; margin-bottom:20px;">
            
            <ul id="ojc-sortable-list" style="list-style:none; padding:0;"></ul>
            
            <button type="button" class="button" id="ojc-add-field-btn">+ הוסף שדה לסט</button>
            <hr>
            <button type="button" class="button button-primary" id="ojc-save-global-set">שמור סט חוקים</button>
        </div>
    </div>
    <?php
}

// 3. הוספת בחירת סט בתוך עמוד המוצר (WooCommerce)
add_action( 'woocommerce_product_options_general_product_data', function() {
    $global_sets = get_option('ojc_global_sets', array());
    $options = array('' => '--- ללא חריטה ---');
    
    foreach ( $global_sets as $id => $set ) {
        $options[$id] = $set['name'];
    }

    echo '<div class="options_group">';
    woocommerce_wp_select( array(
        'id'      => '_ojc_assigned_set',
        'label'   => 'שייך סט חריטה למוצר זה',
        'options' => $options,
    ));
    echo '</div>';
});

// 4. שמירת השיוך במוצר
add_action( 'woocommerce_process_product_meta', function( $post_id ) {
    if ( isset( $_POST['_ojc_assigned_set'] ) ) {
        update_post_meta( $post_id, '_ojc_assigned_set', $_POST['_ojc_assigned_set'] );
    }
});
