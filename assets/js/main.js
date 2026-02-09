jQuery(document).ready(function($) {
    function updateFinalPrice() {
        let container = $('.ojc-branded-container');
        if(!container.length) return;

        let basePrice = parseFloat(container.attr('data-base-price')) || 0;
        let extra = 0;

        $('.ojc-field-row:visible').each(function() {
            let rowPrice = parseFloat($(this).data('price')) || 0;
            let hasInput = false;

            // בדיקה אם הוזן טקסט
            if($(this).find('.ojc-input').val()) hasInput = true;
            // בדיקה אם נבחר רדיו/סלקט
            let optPrice = 0;
            let sel = $(this).find('select option:selected').data('p');
            if(sel) { optPrice = parseFloat(sel); hasInput = true; }
            
            let rad = $(this).find('input[type="radio"]:checked').data('p');
            if(rad) { optPrice = parseFloat(rad); hasInput = true; }

            if(hasInput) extra += (rowPrice + optPrice);
        });

        let total = basePrice + extra;
        $('#ojc-total-display').text('₪' + total.toLocaleString(undefined, {minimumFractionDigits: 2}));
    }

    $(document).on('show_variation', function(e, variation) {
        $('.ojc-branded-container').attr('data-base-price', variation.display_price);
        updateFinalPrice();
    });

    $(document).on('change input', '.ojc-input, .ojc-trig', function() {
        // לוגיקה מותנית
        let currentVals = [];
        $('.ojc-trig:checked, select.ojc-trig option:selected').each(function(){ currentVals.push($(this).val()); });

        $('.ojc-field-row[data-logic-req]').each(function() {
            let reqs = $(this).attr('data-logic-req').split(',');
            let show = reqs.some(r => currentVals.includes(r.trim()));
            $(this).toggle(show);
        });

        updateFinalPrice();
    });

    // אימוג'ים
    $(document).on('click', '.ojc-symbol-toggle', function(e){ e.preventDefault(); $(this).next().toggle(); });
    $(document).on('click', '.ojc-sym-item', function(){
        let inp = $(this).closest('.ojc-input-wrapper').find('.ojc-input');
        inp.val(inp.val() + $(this).text()).trigger('input');
        $(this).parent().hide();
    });

    updateFinalPrice();
});
