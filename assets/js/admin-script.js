document.addEventListener('DOMContentLoaded', function() {

    const list = document.getElementById('ojc-sortable-list');

    const addButton = document.getElementById('ojc-add-new-field');

    if (!list || !addButton) return;



    new Sortable(list, { animation: 150, handle: '.ojc-field-header' });



    function createFieldRow(data = {}) {

        const id = data.id || 'f' + Date.now();

        const row = document.createElement('li');

        row.className = 'ojc-field-row';

        row.innerHTML = `

            <div class="ojc-field-header">

                <strong>☰ <span class="title-prev">${data.label || 'שדה חדש'}</span></strong>

                <button type="button" class="ojc-remove-field">✖ הסר</button>

            </div>

            <div class="ojc-field-body">

                <div><label>כותרת:</label><input type="text" name="ojc_fields[${id}][label]" value="${data.label || ''}" class="widefat field-label"></div>

                <div><label>סוג:</label>

                    <select name="ojc_fields[${id}][type]" class="widefat type-sel">

                        <option value="text" ${data.type === 'text' ? 'selected' : ''}>טקסט</option>

                        <option value="radio" ${data.type === 'radio' ? 'selected' : ''}>בחירת פונט / רדיו</option>

                        <option value="file" ${data.type === 'file' ? 'selected' : ''}>תמונה</option>

                    </select>

                </div>

                <div><label>מחיר בסיס:</label><input type="number" name="ojc_fields[${id}][price]" value="${data.price || 0}" class="widefat"></div>

                <div style="margin-top:20px;"><label><input type="checkbox" name="ojc_fields[${id}][required]" ${data.required ? 'checked' : ''}> חובה</label></div>

            </div>

            <div class="ojc-options-wrapper" style="display: ${data.type === 'radio' ? 'block' : 'none'};">

                <div class="opts-list"></div>

                <button type="button" class="button add-opt-btn">+ הוסף אופציה</button>

            </div>

        `;



        row.querySelector('.field-label').oninput = function() { row.querySelector('.title-prev').textContent = this.value; };

        row.querySelector('.type-sel').onchange = function() { row.querySelector('.ojc-options-wrapper').style.display = this.value === 'radio' ? 'block' : 'none'; };

        row.querySelector('.ojc-remove-field').onclick = () => row.remove();

        row.querySelector('.add-opt-btn').onclick = () => addOpt(row.querySelector('.opts-list'), id);



        list.appendChild(row);

        if (data.options) data.options.forEach(o => addOpt(row.querySelector('.opts-list'), id, o));

    }



    function addOpt(cont, fId, oData = {}) {

        const oId = 'o' + Math.random().toString(36).substr(2, 5);

        const div = document.createElement('div');

        div.className = 'ojc-option-item';

        div.innerHTML = `<input type="text" name="ojc_fields[${fId}][options][${oId}][label]" value="${oData.label || ''}" placeholder="שם" style="flex:2;">

                         <input type="number" name="ojc_fields[${fId}][options][${fId}][price]" value="${oData.price || 0}" placeholder="₪" style="flex:1;">

                         <span class="ojc-remove-field rm-opt" style="cursor:pointer">✖</span>`;

        div.querySelector('.rm-opt').onclick = () => div.remove();

        cont.appendChild(div);

    }



    addButton.onclick = () => createFieldRow();

    if (window.ojc_vars && ojc_vars.existing_fields) ojc_vars.existing_fields.forEach(f => createFieldRow(f));

});
