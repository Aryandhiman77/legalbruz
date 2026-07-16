@once
    <script>
        (() => {
            const systems = [
                {
                    add: '[data-add-draft-document]',
                    list: '[data-draft-document-list]',
                    template: '[data-draft-document-template]',
                    row: '[data-draft-document-row]',
                    remove: '[data-remove-draft-document]',
                    empty: 'data-draft-document-empty',
                },
                {
                    add: '[data-add-evidence-request-document]',
                    list: '[data-evidence-request-document-list]',
                    template: '[data-evidence-request-document-template]',
                    row: '[data-evidence-request-document-row]',
                    remove: '[data-remove-evidence-request-document]',
                    empty: 'data-evidence-request-document-empty',
                },
                {
                    add: '[data-add-oppose-draft-document]',
                    list: '[data-oppose-draft-document-list]',
                    template: '[data-oppose-draft-document-template]',
                    row: '[data-oppose-draft-document-row]',
                    remove: '[data-remove-oppose-draft-document]',
                    empty: 'data-oppose-draft-document-empty',
                },
                {
                    add: '[data-add-filing-document]',
                    list: '[data-filing-document-list]',
                    template: '[data-filing-document-template]',
                    row: '[data-filing-document-row]',
                    remove: '[data-remove-filing-document]',
                    empty: 'data-filing-document-empty',
                },
                {
                    add: '[data-add-oppose-filing-document]',
                    list: '[data-oppose-filing-document-list]',
                    template: '[data-oppose-filing-document-template]',
                    row: '[data-oppose-filing-document-row]',
                    remove: '[data-remove-oppose-filing-document]',
                    empty: 'data-oppose-filing-document-empty',
                },
                {
                    add: '[data-add-oppose-tracking-document]',
                    list: '[data-oppose-tracking-document-list]',
                    template: '[data-oppose-tracking-document-template]',
                    row: '[data-oppose-tracking-document-row]',
                    remove: '[data-remove-oppose-tracking-document]',
                    empty: 'data-oppose-tracking-document-empty',
                },
                {
                    add: '[data-add-tracking-document]',
                    list: '[data-tracking-document-list]',
                    template: '[data-tracking-document-template]',
                    row: '[data-tracking-document-row]',
                    remove: '[data-remove-tracking-document]',
                    empty: 'data-tracking-document-empty',
                },
                {
                    add: '[data-add-exam-objection-document]',
                    list: '[data-exam-objection-document-list]',
                    template: '[data-exam-objection-document-template]',
                    row: '[data-exam-objection-document-row]',
                    remove: '[data-remove-exam-objection-document]',
                    empty: 'data-exam-objection-document-empty',
                },
                {
                    add: '[data-add-requested-document]',
                    list: '[data-requested-document-list]',
                    template: '[data-requested-document-template]',
                    row: '[data-requested-document-row]',
                    remove: '[data-remove-requested-document]',
                    empty: 'data-requested-document-empty',
                },
                {
                    add: '[data-add-decision-document]',
                    list: '[data-decision-document-list]',
                    template: '[data-decision-document-template]',
                    row: '[data-decision-document-row]',
                    remove: '[data-remove-decision-document]',
                    empty: 'data-decision-document-empty',
                },
            ];

            systems.forEach((system) => {
                const addButton = document.querySelector(system.add);
                const list = document.querySelector(system.list);
                const template = document.querySelector(system.template);
                if (!addButton || !list || !template) return;

                const emptySelector = `[${system.empty}]`;
                const refreshEmptyState = () => {
                    const hasRows = Boolean(list.querySelector(system.row));
                    let empty = list.querySelector(emptySelector);
                    const emptyDisabled = list.hasAttribute('data-disable-empty-state');

                    if (hasRows) {
                        list.classList.remove('is-empty', 'admin-additional-documents-list-empty');
                        empty?.remove();
                        return;
                    }

                    list.classList.add('is-empty');
                    if (emptyDisabled) {
                        empty?.remove();
                        return;
                    }

                    if (!empty) {
                        empty = document.createElement('p');
                        empty.className = 'admin-document-empty';
                        empty.setAttribute(system.empty, '');
                        empty.textContent = list.dataset.emptyText || 'No additional documents attached. You can add up to 10 files of 15 MB each.';
                        list.appendChild(empty);
                    }
                };

                addButton.addEventListener('click', () => {
                    if (list.querySelectorAll(system.row).length >= 10) return;
                    list.classList.remove('is-empty', 'admin-additional-documents-list-empty');
                    list.querySelector(emptySelector)?.remove();
                    list.appendChild(template.content.cloneNode(true));
                    refreshEmptyState();
                });

                list.addEventListener('click', (event) => {
                    const removeButton = event.target.closest(system.remove);
                    if (!removeButton) return;
                    removeButton.closest(system.row)?.remove();
                    refreshEmptyState();
                });

                refreshEmptyState();
            });

            document.addEventListener('click', (event) => {
                const saveNoteButton = event.target.closest('[data-additional-document-save-note]');
                if (saveNoteButton) {
                    saveNoteButton.textContent = 'Note Saved';
                    window.setTimeout(() => {
                        saveNoteButton.textContent = 'Save Note';
                    }, 1400);
                    return;
                }

                const editButton = event.target.closest('[data-additional-documents-edit]');
                if (editButton) {
                    const section = editButton.closest('[data-additional-documents-section]');
                    section?.querySelector('[data-additional-documents-fields]')?.removeAttribute('style');
                    editButton.style.display = 'none';
                    const cancelButton = section?.querySelector('[data-additional-documents-cancel]');
                    if (cancelButton) cancelButton.style.display = '';
                    return;
                }

                const cancelButton = event.target.closest('[data-additional-documents-cancel]');
                if (cancelButton) {
                    const section = cancelButton.closest('[data-additional-documents-section]');
                    if (section?.dataset.hasSubmittedAdditionalDocuments === '1') {
                        const fields = section.querySelector('[data-additional-documents-fields]');
                        if (fields) fields.style.display = 'none';
                        const button = section.querySelector('[data-additional-documents-edit]');
                        if (button) button.style.display = '';
                        cancelButton.style.display = 'none';
                    }
                    return;
                }

                const deleteButton = event.target.closest('[data-additional-document-delete]');
                if (!deleteButton) return;
                if (!window.confirm('Remove this additional document?')) return;

                const form = document.createElement('form');
                form.method = 'POST';
                form.action = deleteButton.dataset.deleteAction;
                form.style.display = 'none';
                form.innerHTML = `
                    <input type="hidden" name="_token" value="${deleteButton.dataset.deleteToken}">
                    <input type="hidden" name="_method" value="DELETE">
                `;
                document.body.appendChild(form);
                form.submit();
            });
        })();
    </script>
@endonce
