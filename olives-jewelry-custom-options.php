<?php
/**
 * Plugin Name: Olives Jewelry Custom Options - Ultimate Pro
 * Description: מערכת חריטות הכוללת אימוג'ים, העלאת תמונות, HTML ולוגיקה מתקדמת.
 * Version:     7.5.0
 * Author:      Olives Jewelry
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// 1. טעינת ספריות לניהול ולאתר
add_action( 'admin_enqueue_scripts', function() {
    wp_enqueue_script( 'sortable-js', 'https://cdn.jsdelivr.net/npm/sortablejs@1.14.0/Sortable.min.js', array(), '1.14.0', true );
    wp_enqueue_style( 'select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css' );
    wp_enqueue_script( 'select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', array('jquery'), '4.1.0', true );
});

add_action( 'wp_enqueue_scripts', function() {
    // טעינת ספריית אימוג'ים קלה ונקייה
    wp_enqueue_script( 'emoji-button', 'https://cdn.jsdelivr.net/npm/@joeattardi/emoji-button@4.6.4/dist/index.min.js', array(), '4.6.4', true );
});

// 2. תפריט ניהול
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
        <h1>הגדרות חריטה - Olives Jewelry</h1>
        <form method="post" style="background:#fff; padding:20px; border:1px solid #ccc; max-width:1100px; border-radius:8px;">
            <?php wp_nonce_field('ojc_save_action', 'ojc_save_nonce'); ?>
            <input type="hidden" name="ojc_set_id" value="<?php echo esc_attr($edit_id); ?>">
            
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px; margin-bottom:20px;">
                <div><label><strong>שם הסט:</strong></label><input type="text" name="ojc_set_name" value="<?php echo $current_set ? esc_attr($current_set['name']) : ''; ?>" style="width:100%;" required></div>
                <div><label><strong>מוצרים:</strong></label><select name="ojc_assigned_products[]" id="ojc-prod-select" multiple="multiple" style="width:100%;"><?php foreach(get_posts(array('post_type'=>'product','numberposts'=>-1)) as $p) { $sel = ($current_set && in_array($p->ID, ($current_set['products'] ?? []))) ? 'selected' : ''; echo '<option value="'.$p->ID.'" '.$sel.'>'.$p->post_title.'</option>'; } ?></select></div>
            </div>

            <ul id="ojc-fields-sortable" style="padding:0;"></ul>
            <button type="button" id="add-f-btn" class="button">+ הוסף שדה</button>
            <hr><button type="submit" class="button button-primary button-large">שמור סט</button>
        </form>
    </div>
    <script>
    jQuery(document).ready(function($) {
        $('#ojc-prod-select').select2();
        const list = $('#ojc-fields-sortable');
        const data = <?php echo $current_set ? json_encode($current_set['fields']) : 'null'; ?>;
        function addR(id, d = {}) {
            list.append(`<li style="background:#fcfcfc; border:1px solid #ddd; padding:15px; margin-bottom:10px; list-style:none; border-right:4px solid #0073aa;">
                <div style="display:grid; grid-template-columns:1.5fr 1fr 0.5fr 1fr; gap:10px;">
                    <input type="text" name="ojc_fields[${id}][label]" value="${d.label||''}" placeholder="כותרת שדה">
                    <select name="ojc_fields[${id}][type]" class="ojc-type-select">
                        <option value="text" ${d.type==='text'?'selected':''}>טקסט</option>
                        <option value="radio" ${d.type==='radio'?'selected':''}>כן/לא</option>
                        <option value="file" ${d.type==='file'?'selected':''}>העלאת תמונה</option>
                        <option value="html" ${d.type==='html'?'selected':''}>קוד HTML/הסבר</option>
                    </select>
                    <input type="number" name="ojc_fields[${id}][price]" value="${d.price||0}" placeholder="₪">
                    <input type="text" name="ojc_fields[${id}][logic]" value="${d.logic||''}" placeholder="לוגיקה (כן/לא)">
                </div>
                <div style="margin-top:10px;">
                    <div class="ojc-html-extra" style="${d.type==='html'?'':'display:none;'}"><textarea name="ojc_fields[${id}][html]" style="width:100%; height:60px;" placeholder="הכנס קוד HTML כאן">${d.html||''}</textarea></div>
                    <div class="ojc-emoji-extra" style="${d.type==='text'?'':'display:none;'}"><label><input type="checkbox" name="ojc_fields[${id}][emoji]" value="1" ${d.emoji?'checked':''}> אפשר אימוג'ים</label></div>
                </div>
                <button type="button" class="rem-f" style="color:red; background:none; border:none; cursor:pointer; margin-top:10px;">✖ הסר</button>
            </li>`);
        }
        if(data) Object.keys(data).forEach(k => addR(k, data[k]));
        $('#add-f-btn').click(() => addR('f'+Date.now()));
        $(document).on('change', '.ojc-type-select', function(){
            let r = $(this).closest('li');
            r.find('.ojc-html-extra').toggle($(this).val()==='html');
            r.find('.ojc-emoji-extra').toggle($(this).val()==='text');
        });
        $(document).on('click', '.rem-f', function(){ $(this).closest('li').remove(); });
        new Sortable(document.getElementById('ojc-fields-sortable'), {animation: 150});
    });
    </script>
    <?php
}

// --- Frontend ---
add_action( 'woocommerce_before_add_to_cart_button', function() {
    global $product;
    $sid = get_post_meta($product->get_id(), '_ojc_assigned_set', true);
    $sets = get_option('ojc_global_sets', array());
    if (!$sid || !isset($sets[$sid])) return;

    echo '<div class="ojc-fe-container" style="border:1px solid #ddd; border-radius:10px; margin-bottom:20px; overflow:hidden;">';
    echo '<div style="background:#f8f8f8; padding:12px; border-bottom:1px solid #ddd; font-weight:bold;">✨ התאמה אישית של התכשיט</div>';
    echo '<div style="padding:15px; background:#fff;">';

    foreach ($sets[$sid]['fields'] as $fid => $f) {
        $logic = !empty($f['logic']) ? 'data-logic-req="'.esc_attr($f['logic']).'"' : '';
        $style = !empty($f['logic']) ? 'display:none;' : 'display:block;';
        
        echo '<div class="ojc-field-row" '.$logic.' style="'.$style.' margin-bottom:15px; position:relative;">';
        
        if($f['type'] === 'html') {
            echo '<div>'.do_shortcode($f['html']).'</div>';
        } else {
            echo '<label style="display:block; font-weight:bold; margin-bottom:5px;">'.esc_html($f['label']).($f['price']>0?' (+₪'.$f['price'].')':'').'</label>';
            if($f['type']==='text') {
                echo '<input type="text" name="ojc_data['.$fid.'][v]" class="ojc-input" style="width:100%; padding:10px; border:1px solid #ccc; padding-left:35px;">';
                if(!empty($f['emoji'])) echo '<button type="button" class="ojc-emoji-btn" style="position:absolute; left:8px; bottom:8px; border:none; background:none; cursor:pointer; font-size:18px;">😀</button>';
            } elseif($f['type']==='radio') {
                echo '<label><input type="radio" name="ojc_data['.$fid.'][v]" value="כן" class="ojc-trig"> כן</label> &nbsp; <label><input type="radio" name="ojc_data['.$fid.'][v]" value="לא" class="ojc-trig" checked> לא</label>';
            } elseif($f['type']==='file') {
                echo '<input type="file" name="ojc_file_'.$fid.'" accept="image/*">';
            }
        }
        echo '<input type="hidden" name="ojc_data['.$fid.'][l]" value="'.$f['label'].'">';
        echo '<input type="hidden" name="ojc_data['.$fid.'][p]" value="'.$f['price'].'">';
        echo '</div>';
    }
    echo '</div></div>';
    ?>
    <script>
    jQuery(document).ready(function($){
        // לוגיקה
        function refresh() {
            let vals = []; $('.ojc-trig:checked').each(function(){ vals.push($(this).val()); });
            $('.ojc-field-row[data-logic-req]').each(function(){
                (vals.includes($(this).data('logic-req'))) ? $(this).slideDown(200) : $(this).slideUp(200);
            });
        }
        $('.ojc-trig').on('change', refresh); refresh();

        // אימוג'ים
        if(typeof EmojiButton !== 'undefined') {
            $('.ojc-emoji-btn').each(function(){
                const btn = this;
                const input = $(btn).siblings('input');
                const picker = new EmojiButton({ position: 'bottom-start' });
                picker.on('emoji', emoji => { input.val(input.val() + emoji); });
                $(btn).click(() => picker.togglePicker(btn));
            });
        }
    });
    </script>
    <?php
});

// --- חיבור לעגלה ---
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
                    if(isset($up['url'])) $items[] = ['l'=>'תמונה','v'=>$up['url'],'img'=>true];
                }
            }
        }
        if(!empty($items)){ $data['ojc_cost'] = $cost; $data['ojc_items'] = $items; }
    }
    return $data;
}, 10, 2);

add_action('woocommerce_before_calculate_totals', function($cart){
    foreach($cart->get_cart() as $item) { if(isset($item['ojc_cost'])) $item['data']->set_price($item['data']->get_price() + $item['ojc_cost']); }
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
