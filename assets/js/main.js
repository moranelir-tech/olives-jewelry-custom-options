jQuery(document).ready(function($) {

    function forceClosePickers() {
        $('.ojc-symbol-picker').hide();
    }

    // עדכון מחיר
    function updatePrice() {
        const container = $('.ojc-fe-container');
        if(!container.length) return;

        let total = parseFloat(container.attr('data-base-price')) || 0;

        $('.ojc-field-row:visible').each(function() {
            total += parseFloat($(this).data('price')) || 0;
            const selP = $(this).find('select option:selected').data('p');
            if(selP) total += parseFloat(selP);
        });

        $('#ojc-total-display').text('₪' + total.toFixed(2));
    }

    // טאבים
    function refreshLogic() {
        let vals = [];
        $('.ojc-trig').each(function() {
            if($(this).is('select')) {
                if($(this).val()) vals.push($(this).val().trim());
            } else if($(this).is(':checked')) {
                vals.push($(this).val().trim());
            }
        });

        $('.ojc-field-row[data-logic-req]').each(function() {
            const reqs = $(this).attr('data-logic-req').split(',').map(v=>v.trim());
            const show = reqs.some(v => vals.includes(v));
            if(show) $(this).show(); else $(this).hide();
        });
        updatePrice();
    }

    // אירועי אימוג'ים - מניעת קפיצות
    $(document).off('click', '.ojc-symbol-toggle').on('click', '.ojc-symbol-toggle', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const picker = $(this).siblings('.ojc-symbol-picker');
        const isVisible = picker.is(':visible');
        forceClosePickers();
        if(!isVisible) picker.show();
    });

    $(document).off('click', '.ojc-sym-item').on('click', '.ojc-sym-item', function(e) {
        e.stopPropagation();
        const s = $(this).text();
        const input = $(this).closest('.ojc-input-wrapper').find('.ojc-input');
        input.val(input.val() + s).trigger('change');
        forceClosePickers();
    });

    $(document).on('click', function(e) {
        if(!$(e.target).closest('.ojc-input-wrapper').length) forceClosePickers();
    });

    $(document).on('change', '.ojc-trig', refreshLogic);
    
    refreshLogic();
});
