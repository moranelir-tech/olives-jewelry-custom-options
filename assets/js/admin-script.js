jQuery(document).ready(function($) {
    const wrap = $('#ojc-fields-wrapper');

    function addF(id, d = {}) {
        const card = $(`
            <div class="ojc-field-card" data-id="${id}" style="border:1px solid #ccc; background:#f9f9f9; padding:15px; margin-bottom:20px; border-radius:8px; direction:rtl;">
                <div style="display:flex; justify-content:space-between; border-bottom:1px solid #ddd; padding-bottom:10px; margin-bottom:10px;">
                    <strong>שדה חדש</strong>
                    <button type="button" class="rem-f" style="color:red;">הסר שדה X</button>
                </div>
                <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:15px;">
                    <div><label>תווית:</label><input type="text" name="ojc_fields[${id}][label]" value="${d.label||''}" style="width:100%"></div>
                    <div><label>סוג:</label><select name="ojc_fields[${id}][type]" class="t-sel" style="width:100%">
                        <option value="text" ${d.type==='text'?'selected':''}>טקסט</option>
                        <option value="radio" ${d.type==='radio'?'selected':''}>רדיו (פונטים עם תמונות)</option>
                        <option value="select" ${d.type==='select'?'selected':''}>בחירה (Select)</option>
                        <option value="file" ${d.type==='file'?'selected':''}>העלאת תמונה</option>
                    </select></div>
                    <div><label>מחיר שדה:</label><input type="number" name="ojc_fields[${id}][price]" value="${d.price||0}" style="width:100%"></div>
                    
                    <div class="txt-extra" style="${d.type==='text'?'':'display:none'}">
                        <label>טקסט רמז (Placeholder):</label>
                        <input type="text" name="ojc_fields[${id}][placeholder]" value="${d.placeholder||''}" style="width:100%">
                    </div>
                    <div><label>לוגיקה (טריגר):</label><input type="text" name="ojc_fields[${id}][logic]" value="${d.logic||''}" style="width:100%"></div>
                    <div>
                        <label><input type="checkbox" name="ojc_fields[${id}][show_emoji]" value="1" ${d.show_emoji?'checked':''}> אימוג'ים ❤️</label>
                    </div>
                </div>

                <div class="opt-section" style="margin-top:15px; ${(d.type==='radio'||d.type==='select')?'':'display:none'}">
                    <div class="o-list"></div>
                    <button type="button" class="add-o-btn button">+ הוסף אופציה / פונט</button>
                </div>
            </div>
        `);
        wrap.append(card);
        if(d.options) Object.values(d.options).forEach(o => addO(card, id, o));
    }

    function addO(card, fId, o = {}) {
        const oId = 'o' + Math.random().toString(36).substr(2,5);
        card.find('.o-list').append(`
            <div style="display:flex; gap:10px; margin-bottom:10px; background:#fff; padding:10px; border:1px solid #eee;">
                <input type="text" name="ojc_fields[${fId}][options][${oId}][label]" value="${o.label||''}" placeholder="שם הפונט" style="flex:1">
                <input type="number" name="ojc_fields[${fId}][options][${oId}][price]" value="${o.price||0}" placeholder="מחיר" style="width:70px">
                <input type="text" name="ojc_fields[${fId}][options][${oId}][img]" value="${o.img||''}" placeholder="קישור לתמונה" style="flex:2">
                <input type="hidden" name="ojc_fields[${fId}][options][${oId}][val]" value="${o.val||oId}">
                <span class="rem-o" style="color:red; cursor:pointer;">✖</span>
            </div>
        `);
    }

    $('#add-f-btn').click(() => addF('f' + Date.now()));
    $(document).on('click', '.add-o-btn', function(){ const c = $(this).closest('.ojc-field-card'); addO(c, c.data('id')); });
    $(document).on('change', '.t-sel', function(){ 
        const v = $(this).val(); const c = $(this).closest('.ojc-field-card');
        c.find('.opt-section').toggle(v==='radio'||v==='select');
        c.find('.txt-extra').toggle(v==='text');
    });
    $(document).on('click', '.rem-f', function(){ $(this).closest('.ojc-field-card').remove(); });
    $(document).on('click', '.rem-o', function(){ $(this).closest('div').remove(); });
    const ex = wrap.data('existing'); if(ex) Object.keys(ex).forEach(k => addF(k, ex[k]));
});
