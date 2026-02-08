jQuery(document).ready(function($) {
    /**
     * לוגיקת הצגת שדות לפי בחירה (Tabs)
     */
    function refreshOjcLogic() {
        let selectedValues = [];
        
        // אוסף את מה שהמשתמש בחר
        $('.ojc-trig:checked, select.ojc-trig').each(function() {
            if ($(this).val()) {
                selectedValues.push($(this).val().trim());
            }
        });

        // בודק איזה שדה צריך להופיע
        $('.ojc-field-row[data-logic-req]').each(function() {
            const rawLogic = $(this).attr('data-logic-req');
            if (!rawLogic) return;

            const requiredValues = rawLogic.split(',').map(v => v.trim());
            const shouldShow = requiredValues.some(val => selectedValues.includes(val));
            
            if (shouldShow) {
                $(this).stop().slideDown(250);
            } else {
                $(this).stop().slideUp(250);
            }
        });
    }

    // הפעלה בשינוי בחירה
    $(document).on('change', '.ojc-trig', refreshOjcLogic);

    /**
     * לוגיקת מקלדת אימוג'ים
     */
    $(document).on('click', '.ojc-symbol-toggle', function(e) {
        e.preventDefault();
        $(this).siblings('.ojc-symbol-picker').fadeToggle(150);
    });

    $(document).on('click', '.ojc-sym-item', function() {
        const symbol = $(this).text();
        const $input = $(this).closest('.ojc-input-wrapper').find('.ojc-input');
        $input.val($input.val() + symbol).focus();
        $(this).parent().fadeOut(100);
    });

    // הרצה ראשונית
    refreshOjcLogic();
});
