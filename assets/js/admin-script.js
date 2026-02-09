jQuery(document).ready(function($) {
    const wrap = $('#ojc-fields-wrapper');

    function addF(id, d = {}) {
        const card = $(`
            <div class="ojc-field-card" data-id="${id}">
                <div class="ojc-card-header">
                    <strong>הגדרות שדה</strong>
                    <button type="button" class="rem-f" style="color:red;border:1px solid red;background:none;cursor:pointer;">הסר שדה X</button>
                </div>
                <div class="ojc-grid-3">
                    <div><label>תווית:</label><input type="text" name="ojc_fields[${id}][label]" value="${d.label||''}" style="width:100%"></div>
                    <div><label>סוג:</label><select name="ojc_fields[${id}][type]" class="t-sel" style="width:100%"><option value="text" ${d.type==='text'?'selected':''}>טקסט</option><option value="select" ${d.type==='select'?'selected':''}>בחירה</option></select></div>
                    <div><label>מחיר תוספת:</label><input type="number" name="ojc_fields[${id}][price]" value="${d.price||0}" style="width:100%"></div>
                    <div><label>לוגיקה (ערך טריגר):</label><input type="text" name="ojc_fields[${id}][logic]" value="${d.logic||''}" style="width:100%"></div>
                    <div style="margin-top:25px;"><label><input type="checkbox" name="ojc_fields[${id}][required]" value="1" ${d.required?'checked':''}> שדה חובה</label></div>
                    <div style="margin-top:25px;" class="e-opt ${d.type==='select'?'hidden':''}"><label><input type="checkbox" name="ojc_fields[${id}][show_emoji]" value="1" ${d.show_emoji?'checked':''}> אפשר אימוג'ים ❤️</label></div>
                </div>
                <div class="opt-section" style="${d.type==='select'?'':'display:none'}">
                    <div class="o-list"></div>
                    <button type="button" class="add-o-btn button">+ הוסף אופציה</button>
                </div>
            </div>
        `);
        wrap.append(card);
        if(d.options) Object.values(d.options).forEach(o => addO(card, id, o));
    }

    function addO(card, fId, o = {}) {
        const oId = 'o' + Math.random().toString(36).substr(2,5);
        card.find('.o-list').append(`
            <div style="display:flex;gap:5px;margin-bottom:5px;align-items:center;">
                <input type="text" name="ojc_fields[${fId}][options][${oId}][label]" value="${o.label||''}" placeholder="שם">
                <input type="text" name="ojc_fields[${fId}][options][${oId}][val]" value="${o.val||''}" placeholder="טריגר">
                <input type="number" name="ojc_fields[${fId}][options][${oId}][price]" value="${o.price||0}" placeholder="מחיר">
                <span class="rem-o" style="cursor:pointer;color:red;">✖</span>
            </div>
        `);
    }

    $('#add-f-btn').click(() => addF('f' + Date.now()));
    $(document).on('click', '.add-o-btn', function(){ 
        const c = $(this).closest('.ojc-field-card'); 
        addO(c, c.data('id')); 
    });
    $(document).on('change', '.t-sel', function(){ 
        const c = $(this).closest('.ojc-field-card'); 
        c.find('.opt-section').toggle($(this).val()==='select'); 
        c.find('.e-opt').toggle($(this).val()==='text'); 
    });
    $(document).on('click', '.rem-f', function(){ $(this).closest('.ojc-field-card').remove(); });
    $(document).on('click', '.rem-o', function(){ $(this).closest('div').remove(); });

    const ex = wrap.data('existing'); 
    if(ex) Object.keys(ex).forEach(k => addF(k, ex[k]));
});
