jQuery(document).ready(function($) {
    const wrapper = $('#ojc-fields-wrapper');
    function createField(id, d = {}) {
        const card = $(`
            <div class="ojc-field-card" data-id="${id}" style="border:1px solid #ccc; background:#fff; margin-bottom:15px; border-radius:8px; overflow:hidden;">
                <div style="background:#f0f0f1; padding:10px; display:flex; justify-content:space-between; align-items:center;">
                    <strong>שדה חדש</strong>
                    <button type="button" class="remove-f" style="color:red; cursor:pointer; background:none; border:none;">הסר X</button>
                </div>
                <div style="padding:15px; display:grid; grid-template-columns:1fr 1fr 1fr; gap:10px;">
                    <div><label>תווית:</label><input type="text" name="ojc_fields[${id}][label]" value="${d.label||''}" style="width:100%"></div>
                    <div><label>סוג:</label><select name="ojc_fields[${id}][type]" class="type-sel" style="width:100%"><option value="text" ${d.type==='text'?'selected':''}>טקסט</option><option value="select" ${d.type==='select'?'selected':''}>בחירה</option></select></div>
                    <div><label>טריגרים:</label><input type="text" name="ojc_fields[${id}][logic]" value="${d.logic||''}" style="width:100%"></div>
                    <div><label>מחיר תוספת:</label><input type="number" name="ojc_fields[${id}][price]" value="${d.price||0}" style="width:100%"></div>
                    <div class="emoji-opt" style="${d.type==='select'?'display:none':''}">
                        <label><input type="checkbox" name="ojc_fields[${id}][show_emoji]" value="1" ${d.show_emoji?'checked':''}> אפשר אימוג'ים ❤️</label>
                    </div>
                </div>
                <div class="opt-section" style="${d.type==='select'?'':'display:none'}; padding:15px; background:#f9f9f9;">
                    <div class="opt-list"></div>
                    <button type="button" class="add-o-btn button">+ הוסף אופציה</button>
                </div>
            </div>
        `);
        wrapper.append(card);
        if(d.options) Object.values(d.options).forEach(o => addOpt(card, id, o));
    }
    function addOpt(card, fId, o = {}) {
        const oId = 'o' + Math.random().toString(36).substr(2, 5);
        card.find('.opt-list').append(`<div style="display:flex; gap:5px; margin-bottom:5px;"><input type="text" name="ojc_fields[${fId}][options][${oId}][label]" value="${o.label||''}" placeholder="שם"><input type="text" name="ojc_fields[${fId}][options][${oId}][val]" value="${o.val||''}" placeholder="טריגר"><input type="number" name="ojc_fields[${fId}][options][${oId}][price]" value="${o.price||0}" placeholder="₪"><span class="remove-o" style="cursor:pointer">✖</span></div>`);
    }
    $('#add-f-btn').click(() => createField('f' + Date.now()));
    $(document).on('click', '.add-o-btn', function() { const c = $(this).closest('.ojc-field-card'); addOpt(c, c.data('id')); });
    $(document).on('change', '.type-sel', function() { $(this).closest('.ojc-field-card').find('.opt-section').toggle($(this).val()==='select'); $(this).closest('.ojc-field-card').find('.emoji-opt').toggle($(this).val()==='text'); });
    $(document).on('click', '.remove-f', function() { $(this).closest('.ojc-field-card').remove(); });
    $(document).on('click', '.remove-o', function() { $(this).closest('div').remove(); });
    const existing = wrapper.data('existing');
    if(existing) Object.keys(existing).forEach(k => createField(k, existing[k]));
});
