<script>
    document.querySelectorAll('[data-password-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const input = document.getElementById(button.dataset.passwordToggle);
            const showing = input.type === 'text';
            input.type = showing ? 'password' : 'text';
            button.setAttribute('aria-label', showing ? 'Show password' : 'Hide password');
            button.querySelector('i').className = showing ? 'bi bi-eye' : 'bi bi-eye-slash';
        });
    });

    document.querySelectorAll('[data-auth-form]').forEach((form) => {
        form.addEventListener('submit', () => {
            if (!form.checkValidity()) return;
            const button = form.querySelector('[type="submit"]');
            button.disabled = true;
            button.querySelector('[data-submit-label]').textContent = button.dataset.loadingText || 'Please wait…';
        });
    });
</script>
