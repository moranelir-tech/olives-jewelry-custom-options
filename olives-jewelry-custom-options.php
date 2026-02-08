<?php
/**
 * Plugin Name: Olives Jewelry Custom Options - PRO Final
 * Version:     22.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// טעינת נכסים
add_action( 'admin_enqueue_scripts', function() {
    wp_enqueue_style( 'select2-css', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css' );
    wp_enqueue_script( 'select2-js', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', array('jquery'), '4.1.0', true );
    wp_enqueue_script( 'ojc-admin-script', plugin_dir_url( __FILE__ ) . 'assets/js/admin-script.js', array('jquery'), time(), true );
    wp_enqueue_style( 'ojc-admin-style', plugin_dir_url( __FILE__ ) . 'assets/css/style.css', array(), time() );
});

add_action( 'wp_enqueue_scripts', function() {
    wp_enqueue_style( 'ojc-fe-style', plugin_dir_url( __FILE__ ) . 'assets/css/style.css', array(), time() );
    wp_enqueue_script( 'ojc-main-js', plugin_dir_url( __FILE__ ) . 'assets/js/main.js', array('jquery'), time(), true );
});

// פאנל ניהול (מקוצר למען הבהירות, הלוגיקה זהה)
add_action('admin_menu', function() {
    add_menu_page('ניהול חריטות', 'ניהול חריטות', 'manage_options', 'ojc-global-builder', 'ojc_render_builder', 'dashicons-art', 30);
});

function ojc_render_builder() {
    $sets = get_option('ojc_global_sets', array());
    if (isset($_GET['delete'])) {
        unset($sets[sanitize_text_field($_GET['delete'])]);
        update_option('ojc_global_sets', $sets);
        echo "<script>window.location.href='admin.php?page=ojc-global-builder';</script>"; exit;
    }
    $edit_id = $_GET['edit'] ?? '';
    $current_set = ($edit_id && isset($sets[$edit_id])) ? $sets[$edit_id] : null;

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ojc_save_nonce'])) {
        $id = $edit_id ?: 'set_' . time();
        $sets[$id] = [
            'name' => sanitize_text_field($_POST['ojc_set_name']),
            'fields' => $_POST['ojc_fields'] ?? [],
            'products' => $_POST['ojc_assigned_products'] ?? []
        ];
        update_option('ojc_global_sets', $sets);
        echo "<script>window.location.href='admin.php?page=ojc-global-builder&edit=$id';</script>";
    }
    ?>
    <div class="wrap" style="direction: rtl;">
        <h1>ניהול חריטות</h1>
        <div class="ojc-admin-card">
            <h3>סטים קיימים:</h3>
            <?php foreach($sets as $sid => $s): ?>
                <div style="margin-bottom:10px;">
                    <strong><?php echo esc_html($s['name']); ?></strong>
                    <a href="?page=ojc-global-builder&edit=<?php echo $sid; ?>" class="button button-small">עריכה</a>
                    <a href="?page=ojc-global-builder&delete=<?php echo $sid; ?>" style="color:red;" onclick="return confirm('למחוק?')">מחיקה</a>
                </div>
            <?php endforeach; ?>
        </div>
        <form method="post">
            <?php wp_nonce_field('ojc_save_action', 'ojc_save_nonce'); ?>
            <div class="ojc-admin-card">
                <input type="text" name="ojc_set_name" value="<?php echo $current_set['name'] ?? ''; ?>" placeholder="שם הסט" style="width:100%;" required>
                <br><br>
                <select name="ojc_assigned_products[]" id="ojc-prod-search" multiple="multiple" style="width:100%;">
                    <?php foreach(get_posts(['post_type'=>'product','numberposts'=>-1]) as $p): ?>
                        <option value="<?php echo $p->ID; ?>" <?php echo (isset($current_set['products']) && in_array($p->ID, $current_set['products'])) ? 'selected' : ''; ?>><?php echo $p->post_title; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div id="ojc-fields-wrapper" data-existing='<?php echo json_encode($current_set['fields'] ?? []); ?>'></div>
            <button type="button" id="add-f-btn" class="button">+ הוסף שדה</button>
            <button type="submit" class="button button-primary">שמור סט</button>
        </form>
    </div>
    <script>jQuery(document).ready(function($) { $('#ojc-prod-search').select2({dir: "rtl"}); });</script>
    <?php
}

// תצוגה באתר
add_action('woocommerce_before_add_to_cart_button', function() {
    global $product;
    $sid = get_post_meta($product->get_id(), '_ojc_assigned_set', true);
    $sets = get_option('ojc_global_sets', array());
    if (!$sid || !isset($sets[$sid])) return;

    $symbols = ['❤︎', '❣︎', '✡', '♬', '♕', '♛', '♔', '𝄞', '♾', '⚓', '☘', '✨', '⭐', '🐾', '🕊️', '🦋'];
    echo '<div class="ojc-fe-container" data-base-price="'.$product->get_price().'">';
    
    foreach ($sets[$sid]['fields'] as $fid => $f) {
        $logic = !empty($f['logic']) ? 'data-logic-req="'.esc_attr($f['logic']).'"' : '';
        $f_price = !empty($f['price']) ? (float)$f['price'] : 0;

        echo '<div class="ojc-field-row" '.$logic.' data-price="'.$f_price.'" style="'.(!empty($f['logic'])?'display:none;':'').'">';
        echo '<label class="ojc-label">'.esc_html($f['label']).($f_price > 0 ? ' <span class="ojc-row-price">(+'.wc_price($f_price).')</span>' : '').'</label>';

        if($f['type']==='text') {
            echo '<div class="ojc-input-wrapper">';
            echo '<input type="text" name="ojc_data['.$fid.'][v]" class="ojc-input" autocomplete="off">';
            if(!empty($f['show_emoji'])) {
                echo '<div class="ojc-emoji-wrapper">';
                echo '<small>הוסף סימן</small>';
                echo '<button type="button" class="ojc-symbol-toggle">❤️</button>';
                echo '<div class="ojc-symbol-picker">';
                foreach($symbols as $s) echo '<span class="ojc-sym-item">'.$s.'</span>';
                echo '</div></div>';
            }
            echo '</div>';
        } elseif($f['type']==='select') {
            echo '<select name="ojc_data['.$fid.'][v]" class="ojc-trig ojc-select">';
            echo '<option value="" data-p="0">בחר אפשרות...</option>';
            foreach(($f['options']??[]) as $o) {
                $opt_p = !empty($o['price']) ? (float)$o['price'] : 0;
                $p_label = ($opt_p > 0) ? ' (+'.strip_tags(wc_price($opt_p)).')' : '';
                echo '<option value="'.$o['val'].'" data-p="'.$opt_p.'">'.$o['label'].$p_label.'</option>';
            }
            echo '</select>';
        }
        echo '</div>';
    }
    echo '<div class="ojc-final-price-box">מחיר סופי: <span id="ojc-total-display">'.wc_price($product->get_price()).'</span></div>';
    echo '</div>';
});
