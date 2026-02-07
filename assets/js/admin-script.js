jQuery(document).ready(function($) {
    const wrapper = $('#ojc-fields-wrapper');
    const existingData = wrapper.data('existing');

    function createFieldRow(id, d = {}) {
        const row = $(`
            <div class="ojc-field-box">
                <span class="remove-row">✖</span>
                <div class="field-main-inputs">
                    <div class="input-group"><label>שם השדה</label><input type="text" name="ojc_fields[${id}][label]" value="${d.label||''}"></div>
                    <div class="input-group"><label>סוג</label><select name="ojc_fields[${id}][type]" class="type-sel">
                        <option value="text" ${d.type==='text'?'selected':''}>טקסט</option>
                        <option value="select" ${d.type==='select'?'selected':''}>בחירה (Select)</option>
                    </select></div>
                    <div class="input-group"><label>לוגיקה (הצג אם נבחר...)</label><input type="text" name="ojc_fields[${id}][logic]" value="${d.logic||''}" placeholder="למשל: 4 צדדים"></div>
                </div>
                <div class="field-extra-settings">
                    <div class="opt-config" style="${d.type==='select'?'':'display:none;'}">
                        <label>אפשרויות (פורמט: שם|מחיר , שם|מחיר)</label>
                        <input type="text" name="ojc_fields[${id}][opts]" value="${d.opts||'צד אחד|0, 4 צדדים|20'}" style="width:100%;">
                    </div>
                    <div class="price-config" style="${d.type==='text'?'':'display:none;'}">
                        <label>מחיר תוספת</label><input type="number" name="ojc_fields[${id}][price]" value="${d.price||0}">
                    </div>
                </div>
            </div>
        `);
        wrapper.append(row);
    }

    // טעינת נתונים קיימים
    if(existingData) {
        Object.keys(existingData).forEach(k => createFieldRow(k, existingData[k]));
    }

    $('#add-f-btn').on('click', function() {
        createFieldRow('f' + Date.now());
    });

    $(document).on('change', '.type-sel', function() {
        const val = $(this).val();
        $(this).closest('.ojc-field-box').find('.opt-config').toggle(val === 'select');
        $(this).closest('.ojc-field-box').find('.price-config').toggle(val === 'text');
    });

    $(document).on('click', '.remove-row', function() {
        $(this).closest('.ojc-field-box').remove();
    });

    if (typeof Sortable !== 'undefined') {
        new Sortable(document.getElementById('ojc-fields-wrapper'), { animation: 150 });
    }
});
