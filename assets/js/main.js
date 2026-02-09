jQuery(document).ready(function($) {
    function forceClose() { $('.ojc-symbol-picker').hide(); }

    // עדכון מחיר חי
    function updateP() {
        const c = $('.ojc-branded-container'); 
        if(!c.length) return;
        let base = parseFloat(c.attr('data-base-price')) || 0;
        let add = 0;
        
        $('.ojc-field-row:visible').each(function() {
            add += parseFloat($(this).data('price')) || 0;
            const sp = $(this).find('select option:selected').data('p');
            if(sp) add += parseFloat(sp);
        });
        
        const total = base + add;
        $('#ojc-total-display').text('₪' + total.toLocaleString(undefined,{minimumFractionDigits:2}));
    }

    // רענון לוגיקה מותנית
    function refreshL() {
        let vs = []; 
        $('.ojc-trig, .variations select').each(function(){ 
            if($(this).val()) vs.push($(this).val().trim()); 
        });

        $('.ojc-field-row[data-logic-req]').each(function(){
            const reqs = $(this).attr('data-logic-req').split(',').map(v=>v.trim());
            const show = reqs.some(v => vs.includes(v));
            if(show){ 
                $(this).show(); 
                $(this).find('input,select').prop('disabled',false); 
            } else { 
                $(this).hide(); 
                $(this).find('input,select').prop('disabled',true); 
            }
        });
        updateP();
    }

    // תמיכה בווריאציות ווקומרס
    $(document).on('show_variation', function(e, v) { 
        $('.ojc-branded-container').attr('data-base-price', v.display_price); 
        updateP(); 
    });

    // אימוג'ים
    $(document).on('click', '.ojc-symbol-toggle', function(e) { 
        e.preventDefault(); e.stopPropagation(); 
        const p = $(this).siblings('.ojc-symbol-picker'); 
        const vis = p.is(':visible'); 
        forceClose(); 
        if(!vis) p.show(); 
    });

    $(document).on('click', '.ojc-sym-item', function() { 
        const i = $(this).closest('.ojc-input-wrapper').find('.ojc-input'); 
        i.val(i.val() + $(this).text()).trigger('change'); 
        forceClose(); 
    });

    $(document).on('click', function(e){ 
        if(!$(e.target).closest('.ojc-input-wrapper').length) forceClose(); 
    });

    $(document).on('change', '.ojc-trig, .variations select', refreshL);
    
    refreshL();
});
