<?php
/**
 * Plugin Name: Olives Jewelry Global Options
 * Description: ניהול וריאציות חריטה בסטים גלובליים ושיוך למוצרים.
 * Version:     2.0.0
 * Author:      Olives Jewelry
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// יצירת תפריט ניהול ראשי בסרגל הצידי של וורדפרס
add_action('admin_menu', function() {
    add_menu_page(
        'חוקי חריטה',
        'חוקי חריטה',
        'manage_options',
        'ojc-global-options',
        'ojc_global_settings_page',
        'dashicons-edit-page',
        30
    );
});

// עמוד הגדרות הסטים
function ojc_global_settings_page() {
    $global_sets = get_option('ojc_global_sets', []);
    ?>
    <div class="wrap">
        <h1>ניהול סטים גלובליים לחריטה</h1>
        <p>כאן יוצרים את הסטים שיופיעו במוצרים.</p>
        
        <form method="post" action="options.php">
            <?php settings_fields('ojc_global_group'); ?>
            <div id="global-sets-container">
                <p><strong>טיפ:</strong> כרגע נגדיר סט ברירת מחדל אחד חזק שמתאים לכולם.</p>
            </div>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// הוספת אפשרות בחירת סט בתוך עמוד המוצר
add_action( 'woocommerce_product_options_general_product_data', function() {
    echo '<div class="options_group">';
    
    // רשימה נפתחת לבחירת סט חוקים
    woocommerce_wp_select( array(
        'id'      => '_ojc_selected_set',
        'label'   => 'בחר סט חריטה גלובלי',
        'options' => array(
            ''          => 'ללא חריטה',
            'bracelets' => 'סט חריטה לצמידים (פונט + טקסט)',
            'rings'     => 'סט חריטה לטבעות (חריטה פנימית)',
        ),
    ));
    
    echo '</div>';
});

// שמירת הבחירה של המוצר
add_action( 'woocommerce_process_product_meta', function( $post_id ) {
    $selected_set = isset( $_POST['_ojc_selected_set'] ) ? $_POST['_ojc_selected_set'] : '';
    update_post_meta( $post_id, '_ojc_selected_set', $selected_set );
});
