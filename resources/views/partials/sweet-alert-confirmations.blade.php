<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script>
    const showLegalBruzConfirm = ({ title, text, confirmButtonText, cancelButtonText, confirmButtonColor }) => {
        return new Promise((resolve) => {
            const overlay = document.createElement('div');
            overlay.style.cssText = 'position:fixed;inset:0;z-index:3000;display:flex;align-items:center;justify-content:center;background:rgba(15,23,42,.48);padding:18px;';
            overlay.innerHTML = `
                <div style="width:min(420px,100%);background:#fff;border-radius:12px;box-shadow:0 24px 70px rgba(15,23,42,.28);padding:26px;text-align:center;font-family:Inter,system-ui,sans-serif;">
                    <div style="width:54px;height:54px;border-radius:50%;display:grid;place-items:center;margin:0 auto 14px;border:2px solid #93c5fd;color:#2563eb;font-size:28px;">?</div>
                    <h2 style="margin:0 0 10px;color:#102a4c;font-size:1.35rem;font-weight:700;">${title}</h2>
                    <p style="margin:0;color:#536176;font-size:.95rem;line-height:1.5;">${text}</p>
                    <div style="display:flex;justify-content:center;gap:10px;margin-top:22px;">
                        <button type="button" data-confirm-cancel style="min-height:40px;border:0;border-radius:7px;background:#6b7280;color:#fff;padding:0 16px;font-weight:500;">${cancelButtonText}</button>
                        <button type="button" data-confirm-ok style="min-height:40px;border:0;border-radius:7px;background:${confirmButtonColor};color:#fff;padding:0 16px;font-weight:500;">${confirmButtonText}</button>
                    </div>
                </div>
            `;
            document.body.appendChild(overlay);
            const close = (value) => {
                overlay.remove();
                resolve(value);
            };
            overlay.querySelector('[data-confirm-cancel]')?.addEventListener('click', () => close(false));
            overlay.querySelector('[data-confirm-ok]')?.addEventListener('click', () => close(true));
            overlay.addEventListener('click', (event) => {
                if (event.target === overlay) {
                    close(false);
                }
            });
        });
    };

    document.addEventListener('submit', function(event) {
        const form = event.target.closest('form[data-swal-confirm]');

        if (!form || form.dataset.swalConfirmed === 'true') {
            return;
        }

        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        event.preventDefault();
        event.stopPropagation();

        const title = form.dataset.swalTitle || 'Confirm action';
        const text = form.dataset.swalText || 'Please confirm that you want to continue.';
        const icon = form.dataset.swalIcon || 'question';
        const confirmButtonText = form.dataset.swalConfirmText || 'Yes, continue';
        const cancelButtonText = form.dataset.swalCancelText || 'Cancel';
        const confirmButtonColor = form.dataset.swalConfirmColor || '#2A9D8F';

        if (!window.Swal) {
            showLegalBruzConfirm({ title, text, confirmButtonText, cancelButtonText, confirmButtonColor }).then((confirmed) => {
                if (!confirmed) {
                    return;
                }

                form.dataset.swalConfirmed = 'true';
                const loading = window.LegalBruzButtonLoading;
                loading?.set(loading.submitButtonFor(form, event.submitter), form.dataset.loadingText || 'Submitting...');
                HTMLFormElement.prototype.submit.call(form);
            });

            return;
        }

        Swal.fire({
            title,
            text,
            icon,
            showCancelButton: true,
            confirmButtonText,
            cancelButtonText,
            confirmButtonColor,
            cancelButtonColor: '#6c757d',
            reverseButtons: true,
            focusCancel: true,
        }).then((result) => {
            if (!result.isConfirmed) {
                return;
            }

            form.dataset.swalConfirmed = 'true';
            const loading = window.LegalBruzButtonLoading;
            loading?.set(loading.submitButtonFor(form, event.submitter), form.dataset.loadingText || 'Submitting...');
            HTMLFormElement.prototype.submit.call(form);
        });
    }, true);
</script>
