jQuery(document).ready(function($) {
    console.log("OJC Builder Loaded!"); // בדיקה בקונסול שהסקריפט עובד

    const builderModal = $('#ojc-builder-modal');
    const fieldList = $('#ojc-sortable-list');

    // פתיחת הממשק
    $('#ojc-create-new-set').on('click', function(e) {
        e.preventDefault();
        console.log("Button Clicked");
        builderModal.show();
        $(this).hide();
    });

    // הוספת שדה
    $('#ojc-add-field-btn').on('click', function() {
        const id = 'f' + Math.floor(Math.random() * 1000000);
        const html = `
            <li class="ojc-field-row" style="border:1px solid #ccc; background:#f9f9f9; padding:15px; margin-bottom:10px; list-style:none;">
                <div class="ojc-field-header"><strong>☰ שדה חדש</strong></div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                    <input type="text" placeholder="שם השדה (למשל: חריטה בצד ב')" class="f-label" style="width:100%">
                    <select class="f-type" style="width:100%">
                        <option value="text">טקסט</option>
                        <option value="radio">כן/לא (Radio)</option>
                    </select>
                    <input type="number" placeholder="תוספת מחיר" class="f-price" style="width:100%">
                    <input type="text" placeholder="לוגיקה (הצג אם שדה קודם שווה ל...)" class="f-logic" style="width:100%">
                </div>
                <button type="button" class="remove-f" style="color:red; margin-top:10px; cursor:pointer;">✖ הסר שדה</button>
            </li>`;
        fieldList.append(html);
    });

    // הסרת שדה
    $(document).on('click', '.remove-f', function() {
        $(this).closest('li').remove();
    });
});
