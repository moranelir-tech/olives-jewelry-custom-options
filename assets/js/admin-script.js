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
            </div>
        `;
        row.querySelector('.field-label').oninput = function() { row.querySelector('.title-prev').textContent = this.value; };
        row.querySelector('.ojc-remove-field').onclick = () => row.remove();
        list.appendChild(row);
    }

    addButton.onclick = () => createFieldRow();
    if (window.ojc_vars && ojc_vars.existing_fields) {
        Object.values(ojc_vars.existing_fields).forEach(f => createFieldRow(f));
    }
});
