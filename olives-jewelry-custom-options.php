// תצוגה באתר (Frontend)
add_action( 'woocommerce_before_add_to_cart_button', function() {
    global $product;
    $sid = get_post_meta($product->get_id(), '_ojc_assigned_set', true);
    $sets = get_option('ojc_global_sets', array());
    if (!$sid || !isset($sets[$sid])) return;

    echo '<div class="ojc-fe-container"><div class="ojc-fe-body">';
    
    // רשימת הסמלים המיוחדים שלך
    $special_symbols = ['❤︎', '❣︎', '✡', '♬', '♕', '♛', '♔', '𝄞'];

    foreach ($sets[$sid]['fields'] as $fid => $f) {
        $logic = !empty($f['logic']) ? 'data-logic-req="'.esc_attr($f['logic']).'"' : '';
        echo '<div class="ojc-field-row" '.$logic.' style="'.(!empty($f['logic'])?'display:none;':'').'">';
        echo '<label class="ojc-label">'.esc_html($f['label']).'</label>';

        if($f['type']==='text') {
            echo '<div class="ojc-input-wrapper" style="position:relative;">';
            echo '<input type="text" name="ojc_data['.$fid.'][v]" class="ojc-input" placeholder="הקלד כאן...">';
            
            // כפתור סמלים מיוחדים
            echo '<button type="button" class="ojc-symbol-toggle">✨</button>';
            echo '<div class="ojc-symbol-picker" style="display:none;">';
            foreach($special_symbols as $sym) {
                echo '<span class="ojc-sym-item">'.$sym.'</span>';
            }
            echo '</div>';
            echo '</div>';
            echo '<input type="hidden" name="ojc_data['.$fid.'][p]" value="'.esc_attr($f['price']).'">';
        } 
        // ... (שאר סוגי השדות כמו Select ו-Radio נשארים אותו דבר)
        elseif($f['type']==='select') {
            $options = explode(',', ($f['opts'] ?? 'בחר|0'));
            echo '<select name="ojc_data['.$fid.'][v]" class="ojc-trig ojc-select-field">';
            echo '<option value="">בחר אפשרות...</option>';
            foreach($options as $opt_raw) {
                $parts = explode('|', $opt_raw);
                $label = trim($parts[0]); $price = isset($parts[1]) ? floatval($parts[1]) : 0;
                echo '<option value="'.esc_attr($label).'" data-price="'.$price.'">'.esc_html($label).($price>0?" (+₪$price)":"").'</option>';
            }
            echo '</select><input type="hidden" name="ojc_data['.$fid.'][p]" class="ojc-dynamic-price" value="0">';
        }

        echo '<input type="hidden" name="ojc_data['.$fid.'][l]" value="'.$f['label'].'">';
        echo '</div>';
    }
    echo '</div></div>';
});
