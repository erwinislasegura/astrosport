const ctaForm = document.getElementById('ctaForm');
const ctaPreview = document.getElementById('ctaPreview');

function updateCtaPreview() {
    if (!ctaForm || !ctaPreview) return;
    const data = new FormData(ctaForm);
    ctaPreview.querySelectorAll('[data-cta-preview]').forEach((node) => {
        node.textContent = String(data.get(node.dataset.ctaPreview) || '');
    });
    ctaPreview.classList.toggle('is-disabled', !ctaForm.elements.active.checked);
}

ctaForm?.addEventListener('input', updateCtaPreview);
ctaForm?.addEventListener('change', updateCtaPreview);
updateCtaPreview();
