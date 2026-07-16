<script>
    (function() {
        const loadingClass = 'is-action-loading';

        const actionLabelFrom = (text, fallback) => {
            const normalized = (text || '').replace(/\s+/g, ' ').trim().toLowerCase();

            if (normalized.includes('upload')) return 'Uploading...';
            if (normalized.includes('submit')) return 'Submitting...';
            if (normalized.includes('approve')) return 'Approving...';
            if (normalized.includes('reject')) return 'Rejecting...';
            if (normalized.includes('request')) return 'Sending...';
            if (normalized.includes('send')) return 'Sending...';
            if (normalized.includes('resend')) return 'Sending...';
            if (normalized.includes('verify')) return 'Verifying...';
            if (normalized.includes('pay') || normalized.includes('payment')) return 'Processing...';
            if (normalized.includes('login') || normalized.includes('log in')) return 'Logging in...';
            if (normalized.includes('logout') || normalized.includes('log out')) return 'Logging out...';
            if (normalized.includes('save')) return 'Saving...';
            if (normalized.includes('mark') || normalized.includes('complete')) return 'Updating...';

            return fallback;
        };

        const labelFor = (button, fallback = 'Saving...') => {
            if (!button) {
                return fallback;
            }

            return button.dataset.loadingText ||
                button.closest('form')?.dataset.loadingText ||
                actionLabelFrom(button.textContent || button.value, fallback);
        };

        const set = (button, label = null) => {
            if (!button || button.dataset.loading === 'true') {
                return;
            }

            const isInput = button instanceof HTMLInputElement;
            button.dataset.originalHtml = isInput ? button.value : button.innerHTML;
            button.dataset.loading = 'true';
            button.disabled = true;
            button.setAttribute('aria-disabled', 'true');
            if (button instanceof HTMLAnchorElement) {
                button.dataset.originalPointerEvents = button.style.pointerEvents || '';
                button.style.pointerEvents = 'none';
            }
            button.classList.add(loadingClass);
            if (isInput) {
                button.value = label || labelFor(button);
            } else {
                button.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>' + (label || labelFor(button));
            }
        };

        const reset = (button) => {
            if (!button || button.dataset.loading !== 'true') {
                return;
            }

            if (button instanceof HTMLInputElement) {
                button.value = button.dataset.originalHtml || button.value;
            } else {
                button.innerHTML = button.dataset.originalHtml || button.innerHTML;
            }
            button.disabled = false;
            button.removeAttribute('aria-disabled');
            if (button instanceof HTMLAnchorElement) {
                button.style.pointerEvents = button.dataset.originalPointerEvents || '';
                delete button.dataset.originalPointerEvents;
            }
            button.classList.remove(loadingClass);
            delete button.dataset.loading;
            delete button.dataset.originalHtml;
        };

        const submitButtonFor = (form, submitter = null) => {
            if (submitter instanceof HTMLButtonElement || submitter instanceof HTMLInputElement) {
                return submitter;
            }

            return form?.querySelector('button[type="submit"], input[type="submit"]');
        };

        window.LegalBruzButtonLoading = {
            set,
            reset,
            submitButtonFor,
        };

        document.addEventListener('submit', function(event) {
            if (event.defaultPrevented) {
                return;
            }

            const form = event.target.closest('form');
            if (!form || form.dataset.disableLoadingState === 'true' || form.dataset.swalConfirm) {
                return;
            }

            set(submitButtonFor(form, event.submitter), form.dataset.loadingText || 'Submitting...');
        });
    })();
</script>
