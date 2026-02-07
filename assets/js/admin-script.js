jQuery(document).ready(function($) {
    /**
     * 1. לוגיקת הצגה/הסתרה (Conditional Logic)
     * סורק את כל שדות ה-Radio וה-Select ובודק אם יש שדות שתלויים בהם
     */
    function refreshOjcLogic() {
        let selectedValues = [];
        
        // אוסף ערכים מ-Radio שנבחרו
        $('.ojc-trig:checked').each(function() {
            selectedValues.push($(this).val());
        });
        
        // אוסף ערכים מ-Select (לבחירת כמות שמות למשל)
        $('select.ojc-trig').each(function() {
            if ($(this).val()) {
                selectedValues.push($(this).val());
            }
        });

        // עובר על כל השדות שיש להם דרישת לוגיקה
        $('.ojc-field-row[data-logic-req]').each(function() {
            const requiredValue = $(this).attr('data-logic-req');
            
            // אם הערך הנדרש נמצא ברשימת הנבחרים - הצג, אחרת הסתר
            if (selectedValues.includes(requiredValue)) {
                $(this).slideDown(250);
            } else {
                $(this).slideUp(250);
            }
        });
    }

    // הפעלה בכל שינוי בשדה טריגר
    $(document).on('change', '.ojc-trig', refreshOjcLogic);
    
    // הרצה ראשונית בטעינת הדף
    refreshOjcLogic();

    /**
     * 2. מקלדת אימוג'ים (Emoji Picker)
     * משתמש בספריית EmojiButton שהוספנו ב-PHP
     */
    if (typeof EmojiButton !== 'undefined') {
        $('.ojc-emoji-btn').each(function() {
            const button = this;
            const targetInput = $(button).siblings('input.ojc-input');
            
            // הגדרת המקלדת
            const picker = new EmojiButton({
                position: 'bottom-start',
                rootElement: document.body,
                autoHide: true
            });

            // מה קורה כשבוחרים אימוג'י
            picker.on('emoji', selection => {
                const currentVal = targetInput.val();
                targetInput.val(currentVal + selection);
                targetInput.focus(); // החזרת הפוקוס לשדה
            });

            // פתיחת המקלדת בלחיצה
            $(button).on('click', function(e) {
                e.preventDefault();
                picker.togglePicker(button);
            });
        });
    }

    /**
     * 3. תמיכה בהעלאת קבצים
     * מוודא שהטופס של WooCommerce יודע לשלוח קבצים (enctype)
     */
    if ($('.ojc-file-input').length > 0) {
        $('form.cart').attr('enctype', 'multipart/form-data');
    }
});
