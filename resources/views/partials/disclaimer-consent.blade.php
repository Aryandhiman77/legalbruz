@unless (request()->is('admin*'))
    @php($disclaimerPage = \App\Models\CmsPage::findByKey(\App\Models\CmsPage::DISCLAIMER))
    <div class="lb-disclaimer-modal" id="lb-disclaimer-modal" role="dialog" aria-modal="true"
        aria-labelledby="lb-disclaimer-title" hidden>
        <div class="lb-disclaimer-dialog">
            <header class="lb-disclaimer-header">
                <span>Legal Bruz Disclaimer</span>
                <h2 id="lb-disclaimer-title">{{ $disclaimerPage['title'] ?? 'Website Disclaimer' }}</h2>
            </header>

            <div class="lb-disclaimer-body">
                <div class="lb-disclaimer-text">{!! $disclaimerPage['content'] ?? '' !!}</div>
            </div>

            <footer class="lb-disclaimer-footer">
                <label class="lb-disclaimer-check">
                    <input type="checkbox" id="lb-disclaimer-checkbox">
                    <span>I have read and agree to the website disclaimer.</span>
                </label>
                <div class="lb-disclaimer-actions">
                    <button type="button" class="lb-disclaimer-btn lb-disclaimer-btn-light" id="lb-disclaimer-disagree">Disagree</button>
                    <button type="button" class="lb-disclaimer-btn lb-disclaimer-btn-primary" id="lb-disclaimer-agree" disabled>Agree</button>
                </div>
            </footer>
        </div>
    </div>

    <style>
        body.lb-disclaimer-locked { overflow: hidden; }
        .lb-disclaimer-modal { position: fixed; inset: 0; z-index: 2147483000; display: grid; place-items: center; padding: 18px; background: rgba(5, 21, 38, .72); backdrop-filter: blur(7px); }
        .lb-disclaimer-modal[hidden] { display: none; }
        .lb-disclaimer-dialog { width: min(940px, 100%); max-height: min(90vh, 860px); display: flex; flex-direction: column; overflow: hidden; border-radius: 22px; background: #fff; box-shadow: 0 34px 90px rgba(0, 0, 0, .35); }
        .lb-disclaimer-header { padding: 24px 28px 18px; border-bottom: 1px solid #e5edf4; background: linear-gradient(135deg, #f8fbff, #f3fffc); }
        .lb-disclaimer-header span { color: #0f8f82; font-size: .72rem; font-weight: 950; letter-spacing: .12em; text-transform: uppercase; }
        .lb-disclaimer-header h2 { margin: 7px 0 0; color: #1D3557; font-size: clamp(1.35rem, 3vw, 2rem); font-weight: 950; }
        .lb-disclaimer-body { padding: 24px 28px; overflow: auto; }
        .lb-disclaimer-text { white-space: pre-wrap; color: #26364f; font-size: .96rem; line-height: 1.75; }
        .lb-disclaimer-footer { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 18px 28px; border-top: 1px solid #e5edf4; background: #fbfdff; }
        .lb-disclaimer-check { display: flex; align-items: flex-start; gap: 10px; margin: 0; color: #26364f; font-weight: 800; line-height: 1.45; }
        .lb-disclaimer-check input { width: 18px; height: 18px; margin-top: 2px; accent-color: #159485; flex: 0 0 auto; }
        .lb-disclaimer-actions { display: flex; gap: 10px; flex: 0 0 auto; }
        .lb-disclaimer-btn { min-height: 44px; border: 0; border-radius: 10px; padding: 0 20px; font-weight: 950; }
        .lb-disclaimer-btn-light { background: #e9eef5; color: #1D3557; }
        .lb-disclaimer-btn-primary { background: linear-gradient(135deg, #2A9D8F, #0b8f7e); color: #fff; box-shadow: 0 13px 28px rgba(42, 157, 143, .22); }
        .lb-disclaimer-btn-primary:disabled { cursor: not-allowed; opacity: .45; box-shadow: none; }
        @media (max-width: 720px) { .lb-disclaimer-footer { align-items: stretch; flex-direction: column; } .lb-disclaimer-actions { display: grid; grid-template-columns: 1fr 1fr; } .lb-disclaimer-btn { width: 100%; } .lb-disclaimer-header, .lb-disclaimer-body, .lb-disclaimer-footer { padding-inline: 18px; } }
    </style>

    <script>
        (() => {
            const consentKey = 'legalbruz_disclaimer_consent_v1';
            const modal = document.getElementById('lb-disclaimer-modal');
            const checkbox = document.getElementById('lb-disclaimer-checkbox');
            const agreeButton = document.getElementById('lb-disclaimer-agree');
            const disagreeButton = document.getElementById('lb-disclaimer-disagree');

            const getConsent = () => {
                try {
                    return localStorage.getItem(consentKey);
                } catch (error) {
                    return null;
                }
            };
            const setConsent = () => {
                try {
                    localStorage.setItem(consentKey, 'accepted');
                } catch (error) {
                    //
                }
            };

            if (!modal || getConsent() === 'accepted') return;

            const leaveWebsite = () => {
                if (window.history.length > 1) {
                    window.history.back();
                    window.setTimeout(() => window.location.replace('about:blank'), 700);
                    return;
                }

                window.location.replace('about:blank');
            };

            document.body.classList.add('lb-disclaimer-locked');
            modal.hidden = false;

            checkbox?.addEventListener('change', () => {
                agreeButton.disabled = !checkbox.checked;
            });

            agreeButton?.addEventListener('click', () => {
                if (!checkbox?.checked) return;
                setConsent();
                modal.hidden = true;
                document.body.classList.remove('lb-disclaimer-locked');
            });

            disagreeButton?.addEventListener('click', leaveWebsite);
        })();
    </script>
@endunless
