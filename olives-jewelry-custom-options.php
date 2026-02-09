<?php
/**
 * Plugin Name: Olives Jewelry Custom - Full Final Version
 * Version: 110.0
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
    wp_enqueue_script('ojc-admin-js', plugin_dir_url(__FILE__) . 'assets/js/admin-script.js', array('jquery'), time(), true);
});

// תפריט ניהול
add_action('admin_menu', function() {
    add_menu_page('ניהול חריטות', 'ניהול חריטות', 'manage_options', 'ojc-builder', 'ojc_render_page', 'dashicons-art', 30);
});

function ojc_render_page() {
    $sets = get_option('ojc_sets', []);
    if (isset($_GET['del'])) { unset($sets[$_GET['del']]); update_option('ojc_sets', $sets); echo "<script>window.location.href='admin.php?page=ojc-builder';</script>"; exit; }
    $edit_id = $_GET['edit'] ?? '';
    $curr = ($edit_id && isset($sets[$edit_id])) ? $sets[$edit_id] : null;

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ojc_nonce'])) {
        $id = $edit_id ?: 's_' . time();
        $sets[$id] = ['name' => sanitize_text_field($_POST['ojc_name']), 'fields' => $_POST['ojc_fields'] ?? [], 'prods' => $_POST['ojc_prods'] ?? []];
        update_option('ojc_sets', $sets);
        foreach(get_posts(['post_type'=>'product','numberposts'=>-1]) as $p) { if(get_post_meta($p->ID, '_ojc_set', true) == $id) delete_post_meta($p->ID, '_ojc_set'); }
        if(!empty($sets[$id]['prods'])) { foreach($sets[$id]['prods'] as $pid) update_post_meta($pid, '_ojc_set', $id); }
        echo "<script>window.location.href='admin.php?page=ojc-builder&edit=$id';</script>"; exit;
    }
    ?>
    <div class="wrap" style="direction:rtl; text-align:right;">
        <h1>ניהול חריטות - אוליבס ג'ולרי</h1>
        <div style="background:#fff; padding:15px; border:1px solid #ccc; margin-bottom:20px;">
            <h3>סטים קיימים:</h3>
            <?php foreach($sets as $sid => $s): ?>
                <div style="display:inline-block; background:#f0f0f0; padding:10px; margin:5px; border-radius:5px;">
                    <strong><?php echo $s['name']; ?></strong> 
                    <a href="?page=ojc-builder&edit=<?php echo $sid; ?>" class="button button-small">ערוך</a>
                    <a href="?page=ojc-builder&del=<?php echo $sid; ?>" style="color:red; margin-right:10px;" onclick="return confirm('למחוק?')">מחק X</a>
                </div>
            <?php endforeach; ?>
            <a href="admin.php?page=ojc-builder" class="button button-primary">+ צור סט חדש</a>
        </div>
        <form method="post">
            <input type="hidden" name="ojc_nonce" value="1">
            <div style="background:#fff; padding:20px; border:1px solid #ccc;">
                <label>שם הסט:</label>
                <input type="text" name="ojc_name" value="<?php echo esc_attr($curr['name'] ?? ''); ?>" style="width:100%;" required>
                <br><br>
                <label>מוצרים משויכים:</label>
                <select name="ojc_prods[]" id="ojc-prods" multiple style="width:100%;">
                    <?php foreach(get_posts(['post_type'=>'product','numberposts'=>-1]) as $p): ?>
                        <option value="<?php echo $p->ID; ?>" <?php echo (isset($curr['prods']) && in_array($p->ID, $curr['prods'])) ? 'selected' : ''; ?>><?php echo $p->post_title; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div id="ojc-fields-wrapper" data-existing='<?php echo json_encode($curr['fields'] ?? []); ?>' style="margin-top:20px;"></div>
            <button type="button" id="add-f-btn" class="button button-large">+ הוסף שדה חדש</button>
            <button type="submit" class="button button-primary button-large" style="float:left;">שמור הכל</button>
        </form>
    </div>
    <script>jQuery(document).ready(function($){ $('#ojc-prods').select2({dir:'rtl'}); });</script>
    <?php
}

// תצוגה בדף המוצר
add_action('woocommerce_before_add_to_cart_button', function() {
    global $product;
    $sid = get_post_meta($product->get_id(), '_ojc_set', true);
    $sets = get_option('ojc_sets', []);
    if (!$sid || !isset($sets[$sid])) return;

    $symbols = ['❤︎', '❣︎', '✡', '♬', '♕', '♛', '♔', '𝄞', '♾', '🐾'];
    $base_price = $product->get_price();

    echo '<div class="ojc-branded-container" data-base-price="'.$base_price.'">';
    echo '<div class="ojc-section-header"><span class="ojc-step-badge">1</span><h3 class="ojc-section-title">בחירת חריטה על המוצר</h3></div>';
    
    foreach ($sets[$sid]['fields'] as $fid => $f) {
        $logic = !empty($f['logic']) ? 'data-logic-req="'.esc_attr($f['logic']).'"' : '';
        $placeholder = !empty($f['placeholder']) ? esc_attr($f['placeholder']) : '';
        $f_p = (float)($f['price'] ?? 0);

        echo '<div class="ojc-field-row" '.$logic.' data-price="'.$f_p.'" style="'.(!empty($f['logic'])?'display:none;':'').'">';
        echo '<label class="ojc-label">'.esc_html($f['label']).'</label>';

        if ($f['type'] === 'text') {
            echo '<div class="ojc-input-wrapper">';
            echo '<input type="text" name="ojc_data['.$fid.'][v]" class="ojc-input" placeholder="'.$placeholder.'">';
            if(!empty($f['show_emoji'])) {
                echo '<div class="ojc-emoji-wrapper"><button type="button" class="ojc-symbol-toggle">❤️</button><div class="ojc-symbol-picker">';
                foreach($symbols as $s) echo '<span class="ojc-sym-item">'.$s.'</span>';
                echo '</div></div>';
            }
            echo '</div>';
        } elseif ($f['type'] === 'radio') {
            echo '<div class="ojc-radio-group" style="display:grid; grid-template-columns: repeat(2, 1fr); gap:10px;">';
            foreach(($f['options']??[]) as $o) {
                $op = (float)($o['price'] ?? 0);
                echo '<label class="ojc-radio-item" style="border:1px solid #ddd; padding:8px; border-radius:8px; text-align:center; cursor:pointer;">';
                if(!empty($o['img'])) echo '<img src="'.$o['img'].'" style="max-width:100%; height:auto; display:block; margin:0 auto 5px;">';
                echo '<input type="radio" name="ojc_data['.$fid.'][v]" value="'.$o['val'].'" data-p="'.$op.'" class="ojc-trig"> ';
                echo esc_html($o['label']).($op>0?' (+₪'.$op.')':'');
                echo '</label>';
            }
            echo '</div>';
        } elseif ($f['type'] === 'select') {
            echo '<select name="ojc_data['.$fid.'][v]" class="ojc-trig"><option value="" data-p="0">בחר אפשרות...</option>';
            foreach(($f['options']??[]) as $o) echo '<option value="'.$o['val'].'" data-p="'.($o['price']??0).'">'.$o['label'].'</option>';
            echo '</select>';
        } elseif ($f['type'] === 'file') {
            echo '<input type="file" name="ojc_file_'.$fid.'" accept="image/*">';
        }
        echo '</div>';
    }
    echo '<div class="ojc-final-price-box">מחיר סופי: <span id="ojc-total-display">₪'.number_format($base_price, 2).'</span></div>';
    echo '</div>';
});

// לוגיקה של הוספה לסל ועדכון מחיר
add_filter('woocommerce_add_cart_item_data', function($cart_item_data, $product_id) {
    if (isset($_POST['ojc_data'])) {
        $extra = 0;
        $sets = get_option('ojc_sets', []);
        $sid = get_post_meta($product_id, '_ojc_set', true);
        if($sid && isset($sets[$sid])) {
            foreach($sets[$sid]['fields'] as $fid => $f) {
                if(!empty($_POST['ojc_data'][$fid]['v'])) {
                    $extra += (float)($f['price'] ?? 0);
                    if(isset($f['options'])) {
                        foreach($f['options'] as $o) { if($o['val'] == $_POST['ojc_data'][$fid]['v']) $extra += (float)($o['price'] ?? 0); }
                    }
                }
            }
        }
        $cart_item_data['ojc_extra'] = $extra;
        $cart_item_data['ojc_summary'] = $_POST['ojc_data'];
    }
    return $cart_item_data;
}, 10, 2);

add_action('woocommerce_before_calculate_totals', function($cart) {
    foreach ($cart->get_cart() as $item) {
        if (isset($item['ojc_extra'])) {
            $item['data']->set_price($item['data']->get_price() + $item['ojc_extra']);
        }
    }
}, 10);
