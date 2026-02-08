jQuery(document).ready(function($) {
    function refreshOjcLogic() {
        let selectedValues = [];
        
        // בודק מה המשתמש בחר ב-Select או ב-Radio
        $('.ojc-trig:checked, select.ojc-trig').each(function() {
            if ($(this).val()) {
                selectedValues.push($(this).val().trim());
            }
        });

        // רץ על כל השדות המוסתרים ובודק אם להציג אותם
        $('.ojc-field-row[data-logic-req]').each(function() {
            const rawLogic = $(this).attr('data-logic-req'); // למשל: "ONE, TWO"
            if (!rawLogic) return;

            // מפרק את רשימת הטריגרים שכתבת בניהול למערך נקי
            const requiredValues = rawLogic.split(',').map(v => v.trim());
            
            // בודק אם הבחירה של המשתמש קיימת ברשימה הזו
            const shouldShow = requiredValues.some(val => selectedValues.includes(val));
            
            if (shouldShow) {
                $(this).stop().slideDown(250);
            } else {
                $(this).stop().slideUp(250);
            }
        });
    }

    // מאזין לכל שינוי בבחירה
    $(document).on('change', '.ojc-trig', refreshOjcLogic);
    
    // מפעיל פעם אחת בטעינה למקרה שיש ערכי ברירת מחדל
    refreshOjcLogic();
});
