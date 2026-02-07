<?php
/**
 * Plugin Name: Olives Jewelry Custom Options
 * Version: 8.7.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// טעינת קבצים
add_action( 'admin_enqueue_scripts', function() {
    wp_enqueue_script( 'sortable-js', 'https://cdn.jsdelivr.net/npm/sortablejs@1.14.0/Sortable.min.js', array(), '1.14.0', true );
    wp_enqueue_style( 'select2-css', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css' );
    wp_enqueue_script( 'select2-js', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', array('jquery'), '4.1.0', true );
    // טעינת הסקריפט שלך מתוך התיקייה
    wp_enqueue_script( 'ojc-admin-script', plugin_dir_url( __FILE__ ) . 'assets/js/admin-script.js', array('jquery'), time(), true );
});

add_action( 'wp_enqueue_scripts', function() {
    wp_enqueue_style( 'ojc-style', plugin_dir_url( __FILE__ ) . 'assets/css/style.css', array(), time() );
    wp_enqueue_script( 'ojc-main-js', plugin_dir_url( __FILE__ ) . 'assets/js/main.js', array('jquery'), time(), true );
});

// תפריט ניהול
add_action('admin_menu', function() {
    add_menu_page('ניהול חריטות', 'ניהול חריטות', 'manage_options', 'ojc-global-builder', 'ojc_render_global_builder', 'dashicons-art', 30);
});

function ojc_render_global_builder() {
    $sets = get_option('ojc_global_sets', array());
    $edit_id = isset($_GET['edit']) ? sanitize_text_field($_GET['edit']) : '';
    $current_set = ($edit_id && isset($sets[$edit_id])) ? $sets[$edit_id] : null;

    if ( isset($_POST['ojc_save_nonce']) && wp_verify_nonce($_POST['ojc_save_nonce'], 'ojc_save_action') ) {
        $id = !empty($_POST['ojc_set_id']) ? sanitize_text_field($_POST['ojc_set_id']) : 'set_' . time();
        $sets[$id] = array(
            'name'     => sanitize_text_field($_POST['ojc_set_name']),
            'fields'   => $_POST['ojc_fields'],
            'products' => isset($_POST['ojc_assigned_products']) ? $_POST['ojc_assigned_products'] : array()
        );
        update_option('ojc_global_sets', $sets);
        echo '<script>window.location.href="admin.php?page=ojc-global-builder&edit='.$id.'&saved=1";</script>';
    }
    ?>
    <div class="wrap" style="direction: rtl;">
        <h1>ניהול חריטות</h1>
        <form method="post">
            <?php wp_nonce_field('ojc_save_action', 'ojc_save_nonce'); ?>
            <input type="hidden" name="ojc_set_id" value="<?php echo esc_attr($edit_id); ?>">
            
            <div class="ojc-admin-card">
                <div class="ojc-header-row">
                    <input type="text" name="ojc_set_name" placeholder="שם הסט" value="<?php echo $current_set ? esc_attr($current_set['name']) : ''; ?>" required>
                    <select name="ojc_assigned_products[]" id="ojc-prod-select" multiple="multiple">
                        <?php foreach(get_posts(array('post_type'=>'product','numberposts'=>-1)) as $p) {
                            $sel = ($current_set && in_array($p->ID, ($current_set['products'] ?? []))) ? 'selected' : '';
                            echo '<option value="'.$p->ID.'" '.$sel.'>'.$p->post_title.'</option>';
                        } ?>
                    </select>
                </div>

                <div id="ojc-fields-wrapper" data-existing='<?php echo $current_set ? json_encode($current_set['fields']) : "null"; ?>'>
                    </div>

                <button type="button" id="add-f-btn" class="button">+ הוסף שדה (טקסט/בחירה)</button>
                <button type="submit" class="button button-primary">שמור סט</button>
            </div>
        </form>
    </div>
    <?php
}

// ... כאן להוסיף את פונקציית ה-Frontend (הצגת השדות באתר) ששלחתי קודם ...
