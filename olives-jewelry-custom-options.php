<?php
/**
 * Plugin Name: Olives Jewelry Custom Options - Ultimate Pro
 * Description: מערכת חריטות מלאה עם אימוג'ים, העלאת תמונות, HTML ולוגיקה מבוססת GitHub.
 * Version:     7.6.0
 * Author:      Olives Jewelry
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// 1. טעינת ספריות ניהול (Admin) וקבצי Assets (Frontend)
add_action( 'admin_enqueue_scripts', function() {
    wp_enqueue_script( 'sortable-js', 'https://cdn.jsdelivr.net/npm/sortablejs@1.14.0/Sortable.min.js', array(), '1.14.0', true );
    wp_enqueue_style( 'select2-css', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css' );
    wp_enqueue_script( 'select2-js', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', array('jquery'), '4.1.0', true );
});

add_action( 'wp_enqueue_scripts', function() {
    // טעינת ה-CSS מהתיקייה ב-GitHub
    wp_enqueue_style( 'ojc-style', plugin_dir_url( __FILE__ ) . 'assets/css/style.css', array(), '1.0.0' );
    
    // טעינת ספריית האימוג'ים
    wp_enqueue_script( 'emoji-picker-lib', 'https://cdn.jsdelivr.net/npm/@joeattardi/emoji-button@4.6.4/dist/index.min.js', array(), '4.6.4', true );

    // טעינת ה-JS מהתיקייה ב-GitHub (מכיל לוגיקה ואימוג'ים)
    wp_enqueue_script( 'ojc-main-js', plugin_dir_url( __FILE__ ) . 'assets/js/main.js', array('jquery', 'emoji-picker-lib'), '1.0.0', true );
});

// 2. תפריט ניהול הסטים
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
        
        $all_p = get_posts(array('post_type'=>'product','numberposts'=>-1));
        foreach($all_p as $p) { if(get_post_meta($p->ID, '_ojc_assigned_set', true) == $id) delete_post_meta($p->ID, '_ojc_assigned_set'); }
        if(!empty($sets[$id]['products'])) {
            foreach($sets[$id]['products'] as $pid) { update_post_meta($pid, '_ojc_assigned_set', $id); }
        }
        echo '<script>window.location.href="admin.php?page=ojc-global-builder&edit='.$id.'&saved=1";</script>';
    }
    ?>
    <div class="wrap">
        <h1>הגדרות חריטה מתקדמות - Olives Jewelry</h1>
        <form method="post" style="background:#fff; padding:20px; border:1px solid #ccc; max-width:1100px; border-radius:8px;">
            <?php wp_nonce_field('ojc_save_action', 'ojc_save_nonce'); ?>
            <input type="hidden" name="ojc_set_id" value="<?php echo esc_attr($edit_id); ?>">
            
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px; margin-bottom:20px;">
                <div><label><strong>שם הסט (לניהול פנימי):</strong></label>
                <input type="text" name="ojc_set_name" value="<?php echo $current_set ? esc_attr($current_set['name']) : ''; ?>" style="width:100%;" required></div>
                <div><label><strong>שייך למוצרים:</strong></label>
                <select name="ojc_assigned_products[]" id="ojc-prod-select" multiple="multiple" style="width:100%;">
                    <?php foreach(get_posts(array('post_type'=>'product','numberposts'=>-1)) as $p) {
                        $sel = ($current_set && in_array($p->ID, ($current_set['products'] ?? []))) ? 'selected' : '';
                        echo '<option value="'.$p->ID.'" '.$sel.'>'.$p->post_title.'</option>';
                    } ?>
                </select></div>
            </div>

            <ul id="ojc-fields-sortable" style="padding:0;"></ul>
            <button type="button" id="add-f-btn" class="button">+ הוסף שדה חדש</button>
            <hr><button type="submit" class="button button-primary button-large">שמור שינויים בסט</button>
        </form>
    </div>
    <script>
    jQuery(document).ready(function($) {
        $('#ojc-prod-select').select2();
        const list = $('#ojc-fields-sortable');
        const data = <?php echo $current_set ? json_encode($current_set['fields']) : 'null'; ?>;
        
        function addRow(id, d = {}) {
            list.append(`<li style="background:#fcfcfc; border:1px solid #ddd; padding:15px; margin-bottom:10px; list-style:none; border-right:4px solid #0073aa;">
                <div style="display:grid; grid-template-columns:1.5fr 1fr 0.5fr 1fr; gap:10px;">
                    <input type="text" name="ojc_fields[${id}][label]" value="${d.label||''}" placeholder="כותרת השדה">
                    <select name="ojc_fields[${id}][type]" class="ojc-type-trigger">
                        <option value="text" ${d.type==='text'?'selected':''}>שדה טקסט</option>
                        <option value="radio" ${d.type==='radio'?'selected':''}>כן/לא (Radio)</option>
                        <option value="file" ${d.type==='file'?'selected':''}>העלאת תמונה</option>
                        <option value="html" ${d.type==='html'?'selected':''}>קוד HTML/הסבר</option>
                    </select>
                    <input type="number" name="ojc_fields[${id}][price]" value="${d.price||0}" placeholder="מחיר">
                    <input type="text" name="ojc_fields[${id}][logic]" value="${d.logic||''}" placeholder="הצג אם ערך הוא...">
                </div>
                <div style="margin-top:10px;">
                    <div class="html-extra" style="${d.type==='html'?'':'display:none;'}"><textarea name="ojc_fields[${id}][html]" style="width:100%; height:60px;" placeholder="קוד HTML">${d.html||''}</textarea></div>
                    <div class="emoji-extra" style="${d.type==='text'?'':'display:none;'}"><label><input type="checkbox" name="ojc_fields[${id}][emoji]" value="1" ${d.emoji?'checked':''}> אפשר בחירת אימוג'ים</label></div>
                </div>
                <button type="button" class="rem-f" style="color:red; background:none; border:none; cursor:pointer; margin-top:10px;">✖ הסר שדה</button>
            </li>`);
        }

        if(data) Object.keys(data).forEach(k => addRow(k, data[k]));
        $('#add-f-btn').click(() => addRow('f'+Date.now()));
        $(document).on('change', '.ojc-type-trigger', function(){
            let r = $(this).closest('li');
            r.find('.html-extra').toggle($(this).val()==='html');
            r.find('.emoji-extra').toggle($(this).val()==='text');
        });
        $(document).on('click', '.rem-f', function(){ $(this).closest('li').remove(); });
        new Sortable(document.getElementById('ojc-fields-sortable'), {animation: 150});
    });
    </script>
    <?php
}

// 3. תצוגה באתר (Frontend)
add_action( 'woocommerce_before_add_to_cart_button', function() {
    global $product;
    $sid = get_post_meta($product->get_id(), '_ojc_assigned_set', true);
    $sets = get_option('ojc_global_sets', array());
    if (!$sid || !isset($sets[$sid])) return;

    echo '<div class="ojc-fe-container">';
    echo '<div style="background:#f8f8f8; padding:12px; border-bottom:1px solid #ddd; font-weight:bold;">✨ התאמה אישית של התכשיט</div>';
    echo '<div style="padding:15px; background:#fff;">';

    foreach ($sets[$sid]['fields'] as $fid => $f) {
        $logic = !empty($f['logic']) ? 'data-logic-req="'.esc_attr($f['logic']).'"' : '';
        $style = !empty($f['logic']) ? 'display:none;' : 'display:block;';
        
        echo '<div class="ojc-field-row" '.$logic.' style="'.$style.'">';
        
        if($f['type'] === 'html') {
            echo '<div class="ojc-html-content">'.do_shortcode($f['html']).'</div>';
        } else {
            echo '<label>'.esc_html($f['label']).($f['price']>0?' (+₪'.$f['price'].')':'').'</label>';
            if($f['type']==='text') {
                echo '<input type="text" name="ojc_data['.$fid.'][v]" class="ojc-input">';
                if(!empty($f['emoji'])) echo '<button type="button" class="ojc-emoji-btn">😀</button>';
            } elseif($f['type']==='radio') {
                echo '<div class="ojc-radio-opts"><label><input type="radio" name="ojc_data['.$fid.'][v]" value="כן" class="ojc-trig"> כן</label> &nbsp; <label><input type="radio" name="ojc_data['.$fid.'][v]" value="לא" class="ojc-trig" checked> לא</label></div>';
            } elseif($f['type']==='file') {
                echo '<input type="file" name="ojc_file_'.$fid.'" class="ojc-file-input" accept="image/*">';
            }
        }
        echo '<input type="hidden" name="ojc_data['.$fid.'][l]" value="'.$f['label'].'">';
        echo '<input type="hidden" name="ojc_data['.$fid.'][p]" value="'.$f['price'].'">';
        echo '</div>';
    }
    echo '</div></div>';
});

// 4. חיבור לעגלה וחישוב מחיר
add_filter( 'woocommerce_add_cart_item_data', function($data, $pid) {
    if(isset($_POST['ojc_data'])) {
        $cost = 0; $items = [];
        foreach($_POST['ojc_data'] as $fid => $f) {
            if(!empty($f['v']) && $f['v'] !== 'לא') {
                $cost += floatval($f['p']);
                $items[] = ['l' => $f['l'], 'v' => $f['v']];
            }
        }
        if(!empty($_FILES)) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            foreach($_FILES as $k => $file) {
                if(strpos($k,'ojc_file_') !== false && !empty($file['name'])) {
                    $up = wp_handle_upload($file, ['test_form'=>false]);
                    if(isset($up['url'])) $items[] = ['l'=>'תמונה חריטה','v'=>$up['url'],'img'=>true];
                }
            }
        }
        if(!empty($items)){ $data['ojc_cost'] = $cost; $data['ojc_items'] = $items; }
    }
    return $data;
}, 10, 2);

add_action('woocommerce_before_calculate_totals', function($cart){
    if (is_admin() && !defined('DOING_AJAX')) return;
    foreach($cart->get_cart() as $item) {
        if(isset($item['ojc_cost'])) {
            $item['data']->set_price($item['data']->get_price() + $item['ojc_cost']);
        }
    }
});

add_filter('woocommerce_get_item_data', function($data, $item){
    if(isset($item['ojc_items'])) {
        foreach($item['ojc_items'] as $i) {
            $val = isset($i['img']) ? '<a href="'.$i['v'].'" target="_blank">צפה בתמונה</a>' : $i['v'];
            $data[] = ['name'=>$i['l'], 'value'=>$val];
        }
    }
    return $data;
}, 10, 2);
