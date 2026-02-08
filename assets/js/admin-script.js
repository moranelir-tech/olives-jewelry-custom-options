jQuery(document).ready(function($) {
    const wrapper = $('#ojc-fields-wrapper');
    const existing = wrapper.data('existing');

    function createField(id, d = {}) {
        const card = $(`
            <div class="ojc-field-card" data-id="${id}">
                <div class="ojc-card-header">
                    <strong>שדה: <span class="label-preview">${d.label || 'חדש'}</span></strong>
                    <button type="button" class="remove-f">הסר שדה X</button>
                </div>
                <div class="ojc-card-body">
                    <div class="ojc-grid-3">
                        <div><label>שם השדה:</label><input type="text" name="ojc_fields[${id}][label]" value="${d.label||''}" class="label-input"></div>
                        <div><label>סוג שדה:</label>
                            <select name="ojc_fields[${id}][type]" class="type-sel">
                                <option value="text" ${d.type==='text'?'selected':''}>טקסט</option>
                                <option value="select" ${d.type==='select'?'selected':''}>בחירה (Select)</option>
                                <option value="radio" ${d.type==='radio'?'selected':''}>רדיו (Radio)</option>
                                <option value="file" ${d.type==='file'?'selected':''}>תמונה</option>
                                <option value="html" ${d.type==='html'?'selected':''}>HTML</option>
                            </select>
                        </div>
                        <div><label>טריגרים להצגה (מופרד בפסיק):</label><input type="text" name="ojc_fields[${id}][logic]" value="${d.logic||''}" placeholder="למשל: S1, S2"></div>
                    </div>

                    <div class="opt-section" style="${(d.type==='select'||d.type==='radio')?'':'display:none;'}">
                        <h5>אפשרויות:</h5>
                        <div class="opt-list"></div>
                        <button type="button" class="add-o-btn button">+ הוסף אופציה</button>
                    </div>
                </div>
            </div>
        `);
        wrapper.append(card);
        if(d.options) Object.values(d.options).forEach(o => addOpt(card, id, o));
    }

    function addOpt(card, fId, o = {}) {
        const oId = 'o' + Date.now() + Math.random().toString(36).substr(2, 5);
        card.find('.opt-list').append(`
            <div class="opt-row">
                <input type="text" name="ojc_fields[${fId}][options][${oId}][label]" value="${o.label||''}" placeholder="שם">
                <input type="text" name="ojc_fields[${fId}][options][${oId}][val]" value="${o.val||''}" placeholder="קוד טריגר">
                <input type="number" name="ojc_fields[${fId}][options][${oId}][price]" value="${o.price||0}" placeholder="₪">
                <span class="remove-o">✖</span>
            </div>
        `);
    }

    if(existing) Object.keys(existing).forEach(k => createField(k, existing[k]));
    $('#add-f-btn').click(() => createField('f' + Date.now()));
    $(document).on('change', '.type-sel', function() { 
        $(this).closest('.ojc-field-card').find('.opt-section').toggle($(this).val()==='select'||$(this).val()==='radio'); 
    });
    $(document).on('click', '.add-o-btn', function() { const c = $(this).closest('.ojc-field-card'); addOpt(c, c.data('id')); });
    $(document).on('click', '.remove-f', function() { if(confirm('להסיר?')) $(this).closest('.ojc-field-card').remove(); });
    $(document).on('click', '.remove-o', function() { $(this).closest('.opt-row').remove(); });
});
