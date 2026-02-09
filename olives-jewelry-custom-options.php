<?php
/**
 * Plugin Name: Olives Jewelry Custom Options
 * Description: תוסף לחריטה ועיצוב אישי עם מחיר דינמי וכותרת ממותגת.
 * Version: 35.0
 * Author: Gemini AI
 */

if (!defined('ABSPATH')) exit;

// טעינת נכסים
add_action('wp_enqueue_scripts', function() {
    wp_enqueue_style('ojc-style', plugin_dir_url(__FILE__) . 'assets/css/style.css', array(), time());
    wp_enqueue_script('ojc-script', plugin_dir_url(__FILE__) . 'assets/js/main.js', array('jquery'), time(), true);
});

add_action('admin_enqueue_scripts', function() {
    wp_enqueue_style('select2-css', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css');
    wp_enqueue_script('select2-js', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', array('jquery'), '4.1.0', true);
    wp_enqueue_style('ojc-admin-css', plugin_dir_url(__FILE__) . 'assets/css/style.css', array(), time());
    wp_enqueue_script('ojc-admin-js', plugin_dir_url(__FILE__) . 'assets/js/admin-script.js', array('jquery'), time(), true);
});

// תפריט ניהול
add_action('admin_menu', function() {
    add_menu_page('ניהול חריטות', 'ניהול חריטות', 'manage_options', 'ojc-builder', 'ojc_render_page', 'dashicons-art', 30);
});

function ojc_render_page() {
    $sets = get_option('ojc_sets', []);
    if (isset($_GET['del'])) { unset($sets[$_GET['del']]); update_option('ojc_sets', $sets); }

    $edit_id = $_GET['edit'] ?? '';
    $curr = ($edit_id && isset($sets[$edit_id])) ? $sets[$edit_id] : null;

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ojc_nonce'])) {
        $id = $edit_id ?: 's_' . time();
        $sets[$id] = [
            'name' => sanitize_text_field($_POST['ojc_name']),
            'fields' => $_POST['ojc_fields'] ?? [],
            'prods' => $_POST['ojc_prods'] ?? []
        ];
        update_option('ojc_sets', $sets);
        // עדכון מטא דאטה למוצרים
        foreach(get_posts(['post_type'=>'product','numberposts'=>-1]) as $p) {
            if(get_post_meta($p->ID, '_ojc_set', true) == $id) delete_post_meta($p->ID, '_ojc_set');
        }
        foreach(($sets[$id]['prods'] ?? []) as $pid) update_post_meta($pid, '_ojc_set', $id);
        echo "<script>window.location.href='admin.php?page=ojc-builder&edit=$id';</script>";
    }
    ?>
    <div class="wrap" style="direction:rtl;">
        <h1>ניהול חריטות - אוליבס ג'ולרי</h1>
        <div class="ojc-admin-card">
            <h3>סטים פעילים:</h3>
            <?php foreach($sets as $id => $s): ?>
                <a href="?page=ojc-builder&edit=<?php echo $id; ?>" class="button"><?php echo $s['name']; ?> (ערוך)</a>
                <a href="?page=ojc-builder&del=<?php echo $id; ?>" style="color:red; margin-left:15px;">מחק</a>
            <?php endforeach; ?>
        </div>
        <form method="post">
            <input type="hidden" name="ojc_nonce" value="1">
            <div class="ojc-admin-card">
                <input type="text" name="ojc_name" value="<?php echo $curr['name'] ?? ''; ?>" placeholder="שם הסט" style="width:100%;" required>
                <br><br>
                <label>בחר מוצרים לסט זה:</label>
                <select name="ojc_prods[]" id="ojc-prods" multiple style="width:100%;">
                    <?php foreach(get_posts(['post_type'=>'product','numberposts'=>-1]) as $p): ?>
                        <option value="<?php echo $p->ID; ?>" <?php echo (isset($curr['prods']) && in_array($p->ID, $curr['prods'])) ? 'selected' : ''; ?>><?php echo $p->post_title; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div id="ojc-fields-wrapper" data-existing='<?php echo json_encode($curr['fields'] ?? []); ?>'></div>
            <button type="button" id="add-f-btn" class="button">+ הוסף שדה</button>
            <button type="submit" class="button button-primary">שמור הכל</button>
        </form>
    </div>
    <script>jQuery(document).ready(function($){ $('#ojc-prods').select2({dir:'rtl'}); });</script>
    <?php
}

// הצגה באתר
add_action('woocommerce_before_add_to_cart_button', function() {
    global $product;
    $sid = get_post_meta($product->get_id(), '_ojc_set', true);
    $sets = get_option('ojc_sets', []);
    if (!$sid || !isset($sets[$sid])) return;

    $symbols = ['❤︎', '❣︎', '✡', '♬', '♕', '♛', '♔', '𝄞', '♾', '⚓', '☘', '✨', '⭐', '🐾', '🕊️', '🦋'];
    echo '<div class="ojc-branded-container" data-base-price="'.$product->get_price().'">';
    echo '<div class="ojc-section-header"><span class="ojc-step-badge">1</span><h3 class="ojc-section-title">עיצוב אישי של התכשיט</h3></div>';
    
    foreach ($sets[$sid]['fields'] as $fid => $f) {
        $logic = !empty($f['logic']) ? 'data-logic-req="'.esc_attr($f['logic']).'"' : '';
        $req = !empty($f['required']) ? 'required' : '';
        $f_p = (float)($f['price'] ?? 0);
        $p_html = ($f_p > 0) ? ' <span class="ojc-row-price">('.strip_tags(wc_price($f_p)).')</span>' : '';

        echo '<div class="ojc-field-row" '.$logic.' data-price="'.$f_p.'" style="'.(!empty($f['logic'])?'display:none;':'').'">';
        echo '<label class="ojc-label">'.esc_html($f['label']).$p_html.($req?' <span style="color:red;">*</span>':'').'</label>';

        if ($f['type'] === 'text') {
            echo '<div class="ojc-input-wrapper"><input type="text" name="ojc_data['.$fid.'][v]" class="ojc-input" placeholder="הקלד כאן..." '.$req.'>';
            if(!empty($f['show_emoji'])) {
                echo '<div class="ojc-emoji-wrapper"><small>הוסף סימן</small><button type="button" class="ojc-symbol-toggle">❤️</button><div class="ojc-symbol-picker" style="display:none !important;">';
                foreach($symbols as $s) echo '<span class="ojc-sym-item">'.$s.'</span>';
                echo '</div></div>';
            }
            echo '</div>';
        } else {
            echo '<select name="ojc_data['.$fid.'][v]" class="ojc-trig" '.$req.'><option value="" data-p="0">בחר אפשרות...</option>';
            foreach(($f['options']??[]) as $o) {
                $op = (float)($o['price'] ?? 0);
                $oph = ($op > 0) ? ' ('.strip_tags(wc_price($op)).')' : '';
                echo '<option value="'.$o['val'].'" data-p="'.$op.'">'.$o['label'].$oph.'</option>';
            }
            echo '</select>';
        }
        echo '</div>';
    }
    echo '<div class="ojc-final-price-box">מחיר סופי: <span id="ojc-total-display">'.wc_price($product->get_price()).'</span></div>';
    echo '</div>';
});
