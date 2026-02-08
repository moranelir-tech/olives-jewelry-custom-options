<?php
/**
 * Plugin Name: Olives Jewelry Custom Options - Ultimate
 * Version:     16.0.0
 * Description: מערכת חריטות עם חיפוש מוצרים, אימוג'ים ולוגיקה מורכבת
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// 1. טעינת קבצי JS ו-CSS (חשוב שיהיו בתיקיית assets)
add_action( 'admin_enqueue_scripts', function() {
    wp_enqueue_style( 'select2-css', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css' );
    wp_enqueue_script( 'select2-js', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', array('jquery'), '4.1.0', true );
    
    // קישור לקובץ ה-JS של הניהול
    wp_enqueue_script( 'ojc-admin-script', plugin_dir_url( __FILE__ ) . 'assets/js/admin-script.js', array('jquery'), time(), true );
    // קישור לקובץ ה-CSS
    wp_enqueue_style( 'ojc-admin-style', plugin_dir_url( __FILE__ ) . 'assets/css/style.css', array(), time() );
});

add_action( 'wp_enqueue_scripts', function() {
    wp_enqueue_style( 'ojc-fe-style', plugin_dir_url( __FILE__ ) . 'assets/css/style.css', array(), time() );
    // קישור לקובץ ה-JS של האתר (זה שיוצר את הטאבים והאימוג'ים)
    wp_enqueue_script( 'ojc-main-js', plugin_dir_url( __FILE__ ) . 'assets/js/main.js', array('jquery'), time(), true );
});

// 2. יצירת תפריט הניהול
add_action('admin_menu', function() {
    add_menu_page('ניהול חריטות', 'ניהול חריטות', 'manage_options', 'ojc-global-builder', 'ojc_render_builder', 'dashicons-art', 30);
});

function ojc_render_builder() {
    $sets = get_option('ojc_global_sets', array());
    $edit_id = isset($_GET['edit']) ? sanitize_text_field($_GET['edit']) : '';
    $current_set = ($edit_id && isset($sets[$edit_id])) ? $sets[$edit_id] : null;

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ojc_save_nonce'])) {
        $id = $edit_id ?: 'set_' . time();
        $sets[$id] = [
            'name' => sanitize_text_field($_POST['ojc_set_name']),
            'fields' => $_POST['ojc_fields'] ?? [],
            'products' => $_POST['ojc_assigned_products'] ?? []
        ];
        update_option('ojc_global_sets', $sets);
        
        // עדכון שיוך מוצרים במטא דאטה
        $all_p = get_posts(['post_type'=>'product','numberposts'=>-1]);
        foreach($all_p as $p) { 
            if(get_post_meta($p->ID, '_ojc_assigned_set', true) == $id) delete_post_meta($p->ID, '_ojc_assigned_set'); 
        }
        if(!empty($sets[$id]['products'])) {
            foreach($sets[$id]['products'] as $pid) { update_post_meta($pid, '_ojc_assigned_set', $id); }
        }
        echo "<script>window.location.href='admin.php?page=ojc-global-builder&edit=$id&saved=1';</script>";
    }
    ?>
    <div class="wrap" style="direction: rtl;">
        <h1>ניהול סטים של חריטות</h1>
        
        <form method="post" id="ojc-builder-form">
            <?php wp_nonce_field('ojc_save_action', 'ojc_save_nonce'); ?>
            
            <div class="ojc-admin-card" style="background:#fff; padding:20px; border:1px solid #ccd0d4; margin-bottom:20px;">
                <div style="display:flex; gap:20px;">
                    <div style="flex:1;">
                        <label>שם הסט (לשימוש פנימי):</label>
                        <input type="text" name="ojc_set_name" value="<?php echo $current_set['name'] ?? ''; ?>" style="width:100%;" required>
                    </div>
                    <div style="flex:2;">
                        <label>חפש ובחר תכשיטים לשיוך הסט:</label>
                        <select name="ojc_assigned_products[]" id="ojc-prod-search" multiple="multiple" style="width:100%;">
                            <?php foreach(get_posts(['post_type'=>'product','numberposts'=>-1]) as $p): 
                                $sel = ($current_set && in_array($p->ID, ($current_set['products'] ?? []))) ? 'selected' : '';
                            ?>
                                <option value="<?php echo $p->ID; ?>" <?php echo $sel; ?>><?php echo $p->post_title; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div id="ojc-fields-wrapper" data-existing='<?php echo json_encode($current_set['fields'] ?? []); ?>'></div>
            
            <div style="margin-top:20px;">
                <button type="button" id="add-f-btn" class="button">+ הוסף שדה חדש</button>
                <button type="submit" class="button button-primary">שמור סט חריטות</button>
            </div>
        </form>
    </div>
    <script>jQuery(document).ready(function($) { $('#ojc-prod-search').select2({ placeholder: "הקלד שם תכשיט...", dir: "rtl" }); });</script>
    <?php
}

// 3. תצוגה באתר (Frontend)
add_action('woocommerce_before_add_to_cart_button', function() {
    global $product;
    $sid = get_post_meta($product->get_id(), '_ojc_assigned_set', true);
    $sets = get_option('ojc_global_sets', array());
    if (!$sid || !isset($sets[$sid])) return;

    $symbols = ['❤︎', '❣︎', '✡', '♬', '♕', '♛', '♔', '𝄞', '✝', '☪', '☯', '☘'];

    echo '<div class="ojc-fe-container"><div class="ojc-fe-body">';
    foreach ($sets[$sid]['fields'] as $fid => $f) {
        $logic = !empty($f['logic']) ? 'data-logic-req="'.esc_attr($f['logic']).'"' : '';
        echo '<div class="ojc-field-row" '.$logic.' style="'.(!empty($f['logic'])?'display:none;':'').' margin-bottom:15px;">';
        
        if($f['type'] !== 'html') {
            echo '<label class="ojc-label" style="display:block; font-weight:bold; margin-bottom:5px;">'.esc_html($f['label']).'</label>';
        }

        if($f['type']==='text') {
            echo '<div class="ojc-input-wrapper" style="display:flex; gap:5px;">';
            echo '<input type="text" name="ojc_data['.$fid.'][v]" class="ojc-input" style="flex:1;">';
            echo '<button type="button" class="ojc-symbol-toggle" style="padding:0 10px;">✨</button>';
            echo '<div class="ojc-symbol-picker" style="display:none; position:absolute; bottom:100%; right:0; background:#fff; border:1px solid #ccc; padding:10px; z-index:100; display:grid; grid-template-columns:repeat(4,1fr); gap:5px;">';
            foreach($symbols as $s) echo '<span class="ojc-sym-item" style="cursor:pointer; font-size:20px;">'.$s.'</span>';
            echo '</div></div>';
        } elseif($f['type']==='select') {
            echo '<select name="ojc_data['.$fid.'][v]" class="ojc-trig" style="width:100%;">';
            echo '<option value="">בחר...</option>';
            foreach(($f['options']??[]) as $o) echo '<option value="'.$o['val'].'">'.$o['label'].'</option>';
            echo '</select>';
        } elseif($f['type']==='radio') {
            echo '<div class="ojc-radio-group">';
            foreach(($f['options']??[]) as $o) {
                echo '<label style="display:block;"><input type="radio" name="ojc_data['.$fid.'][v]" value="'.$o['val'].'" class="ojc-trig"> '.esc_html($o['label']).'</label>';
            }
            echo '</div>';
        } elseif($f['type']==='html') {
            echo '<div class="ojc-html-content">'.do_shortcode($f['label']).'</div>';
        }
        echo '</div>';
    }
    echo '</div></div>';
});
