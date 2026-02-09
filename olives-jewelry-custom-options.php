// 1. שמירת הנתונים והמחיר הנוסף לתוך ה-Cart Item
add_filter('woocommerce_add_cart_item_data', function($cart_item_data, $product_id, $variation_id) {
    if (isset($_POST['ojc_data']) || !empty($_FILES)) {
        $extra_price = 0;
        $sid = get_post_meta($product_id, '_ojc_set', true);
        $sets = get_option('ojc_sets', []);
        
        if ($sid && isset($sets[$sid])) {
            foreach ($sets[$sid]['fields'] as $fid => $f) {
                // הוספת מחיר בסיס של השדה
                if (!empty($_POST['ojc_data'][$fid]['v']) || !empty($_FILES['ojc_file_'.$fid])) {
                     $extra_price += (float)($f['price'] ?? 0);
                }
                
                // הוספת מחיר של האופציה שנבחרה (רדיו/סלקט)
                if (isset($_POST['ojc_data'][$fid]['v']) && isset($f['options'])) {
                    $selected_val = $_POST['ojc_data'][$fid]['v'];
                    foreach ($f['options'] as $opt) {
                        if ($opt['val'] == $selected_val) {
                            $extra_price += (float)($opt['price'] ?? 0);
                        }
                    }
                }
            }
        }
        $cart_item_data['ojc_extra_price'] = $extra_price;
        $cart_item_data['ojc_fields'] = $_POST['ojc_data'] ?? [];
    }
    return $cart_item_data;
}, 10, 3);

// 2. עדכון המחיר בפועל בתוך הסל (שקלול המחיר החדש)
add_action('woocommerce_before_calculate_totals', function($cart) {
    if (is_admin() && !defined('DOING_AJAX')) return;
    foreach ($cart->get_cart() as $cart_item) {
        if (isset($cart_item['ojc_extra_price'])) {
            $new_price = $cart_item['data']->get_price() + $cart_item['ojc_extra_price'];
            $cart_item['data']->set_price($new_price);
        }
    }
}, 10);

// 3. הצגת פרטי החריטה בסל הקניות כדי שהלקוח יראה מה הוא בחר
add_filter('woocommerce_get_item_data', function($item_data, $cart_item) {
    if (isset($cart_item['ojc_fields'])) {
        foreach ($cart_item['ojc_fields'] as $field) {
            if (!empty($field['v'])) {
                $item_data[] = array('name' => 'בחירה', 'value' => $field['v']);
            }
        }
    }
    return $item_data;
}, 10, 2);
