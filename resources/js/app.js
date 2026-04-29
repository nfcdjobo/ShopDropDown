import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

/* ------------------------------------------------------------------
 | Reveal on scroll — observe les éléments avec la classe .reveal
 * ------------------------------------------------------------------*/
document.addEventListener('DOMContentLoaded', () => {
    const io = new IntersectionObserver((entries) => {
        entries.forEach(e => {
            if (e.isIntersecting) {
                e.target.classList.add('is-visible');
                io.unobserve(e.target);
            }
        });
    }, { threshold: 0.12 });

    document.querySelectorAll('.reveal').forEach(el => io.observe(el));
});

/* ------------------------------------------------------------------
 | Helpers AJAX panier — exposés globalement via window.SD
 * ------------------------------------------------------------------*/
window.SD = {
    csrf: () => document.querySelector('meta[name="csrf-token"]').content,

    async addToCart(productId, quantity = 1, btn = null) {
        const labelBackup = btn?.innerHTML;
        if (btn) { btn.disabled = true; btn.innerHTML = 'Ajout…'; }

        try {
            const res = await fetch(window.SD.routes.cartStore, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': window.SD.csrf(),
                },
                body: JSON.stringify({ product_id: productId, quantity }),
            });

            const data = await res.json();
            if (!res.ok || !data.success) throw new Error(data.message || 'Erreur');

            // MAJ compteur et montant du header
            const label = document.getElementById('cart-summary-label');
            const badge = document.getElementById('cart-badge');
            if (label) label.textContent = data.formatted_subtotal;
            if (badge) {
                badge.textContent = data.items_count;
                badge.classList.toggle('hidden', !data.items_count);
                badge.classList.toggle('flex',   !!data.items_count);
            }

            window.SD.toast(data.message || 'Produit ajouté au panier', 'success');
        } catch (e) {
            window.SD.toast(e.message, 'error');
        } finally {
            if (btn) { btn.disabled = false; btn.innerHTML = labelBackup; }
        }
    },

    toast(msg, type = 'success') {
        const iconMap = {
            success: '<i class="fa-solid fa-circle-check mr-2"></i>',
            error:   '<i class="fa-solid fa-triangle-exclamation mr-2"></i>',
        };
        const colorMap = {
            success: 'bg-emerald-50 border-emerald-200 text-emerald-900',
            error:   'bg-red-50 border-red-200 text-red-900',
        };
        const el = document.createElement('div');
        el.className = `fixed top-24 right-5 z-[60] max-w-sm rounded-2xl shadow-lg px-5 py-4 text-sm font-medium border flex items-center transition-all duration-300 opacity-0 translate-y-2 ${colorMap[type] || ''}`;
        el.innerHTML = (iconMap[type] || '') + msg;
        document.body.appendChild(el);
        requestAnimationFrame(() => { el.classList.remove('opacity-0', 'translate-y-2'); });
        setTimeout(() => { el.classList.add('opacity-0'); setTimeout(() => el.remove(), 300); }, 3500);
    },
};
