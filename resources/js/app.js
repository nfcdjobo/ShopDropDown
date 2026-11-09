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

/* ---- SDK ---------------------------------------------------------
 | Kit panier global : window.SD
 |
 | • Le layout (layouts/app.blade.php) ne fournit que la CONFIGURATION :
 |     window.SD.routes = { cartStore, cartSummary }
 |   et une file d'attente qui mémorise les clics arrivés avant le chargement de ce fichier.
 | • TOUTE la logique vit ici.
 | • window.SD n'est JAMAIS réaffecté (window.SD = {...}) : on le complète uniquement,
 |   sinon `routes` serait effacé (erreur « reading 'cartStore' »).
 * ------------------------------------------------------------------*/
const SD = (window.SD = window.SD || {});

SD.csrf = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

/** Met à jour le badge et le montant du panier dans l'en-tête. */
SD.updateCartUI = (count, formattedSubtotal) => {
    const badge = document.getElementById('cart-badge');
    const label = document.getElementById('cart-summary-label');
    const has   = Number(count) > 0;

    if (badge) {
        badge.textContent = has ? count : '';
        badge.classList.toggle('hidden', !has);
        badge.classList.toggle('flex', has);
    }
    if (label && formattedSubtotal) {
        label.textContent = formattedSubtotal;
    }
};

/** Notification éphémère (styles en ligne : indépendante de Tailwind). Message affiché en texte brut. */
SD.toast = (message, type = 'success') => {
    document.getElementById('sd-toast')?.remove();

    const ok = type === 'success';

    const wrap = document.createElement('div');
    wrap.id = 'sd-toast';
    wrap.setAttribute('role', 'status');
    wrap.style.cssText = 'position:fixed;top:5.5rem;right:1.25rem;z-index:9999;max-width:22rem;transition:opacity .3s ease';

    const box = document.createElement('div');
    box.style.cssText = [
        'display:flex', 'align-items:flex-start', 'gap:.75rem',
        'padding:1rem 1.25rem', 'border-radius:1rem',
        `background:${ok ? '#ecfdf5' : '#fef2f2'}`,
        `border:1px solid ${ok ? '#a7f3d0' : '#fecaca'}`,
        `color:${ok ? '#065f46' : '#991b1b'}`,
        'box-shadow:0 4px 24px rgba(0,0,0,.08)',
        'font-family:inherit', 'font-size:.875rem',
    ].join(';');

    const icon = document.createElement('span');
    icon.style.cssText = 'font-size:1.1rem;margin-top:.05rem';
    icon.textContent = ok ? '✅' : '❌';

    const text = document.createElement('span');
    text.style.fontWeight = '500';
    text.textContent = message;          // textContent : jamais d'HTML injecté

    box.append(icon, text);
    wrap.append(box);
    document.body.appendChild(wrap);

    setTimeout(() => {
        wrap.style.opacity = '0';
        setTimeout(() => wrap.remove(), 350);
    }, 3500);
};

/**
 * Ajoute un produit au panier.
 * @param {string}           productId
 * @param {number}           quantity
 * @param {HTMLElement|null} triggerEl  bouton cliqué (désactivé + spinner pendant l'appel)
 */
SD.addToCart = async (productId, quantity = 1, triggerEl = null) => {
    if (triggerEl?.disabled) return;                  // évite le double clic

    const url = SD.routes?.cartStore;
    if (!url) {
        console.error('[SD] window.SD.routes.cartStore est introuvable : vérifiez le script du layout.');
        SD.toast("Le panier est momentanément indisponible. Rechargez la page.", 'error');
        return;
    }

    const original = triggerEl?.innerHTML;
    if (triggerEl) {
        const hasText = triggerEl.textContent.trim().length > 0;
        triggerEl.disabled = true;
        triggerEl.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>' + (hasText ? ' Ajout…' : '');
    }

    try {
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': SD.csrf(),
            },
            body: JSON.stringify({ product_id: productId, quantity }),
        });

        if (res.status === 419) {
            throw new Error('Votre session a expiré. Rechargez la page puis réessayez.');
        }

        const json = await res.json().catch(() => ({}));

        if (!res.ok || !json.success) {
            throw new Error(json.message || "Impossible d'ajouter l'article.");
        }

        SD.updateCartUI(json.items_count || 0, json.formatted_subtotal || '');
        SD.toast(json.message || 'Article ajouté au panier !', 'success');

        // Meta Pixel : fbq('track') est envoyé à TOUS les pixels initialisés
        if (typeof fbq !== 'undefined') {
            fbq('track', 'AddToCart', {
                content_ids: [productId],
                content_type: 'product',
                contents: [{ id: productId, quantity }],
                currency: 'XOF',
            });
        }
    } catch (err) {
        console.error('[SD] addToCart :', err);
        const offline = err instanceof TypeError;       // fetch rejette en TypeError si le réseau est coupé
        SD.toast(offline ? 'Erreur réseau. Veuillez réessayer.' : err.message, 'error');
    } finally {
        if (triggerEl) {
            triggerEl.disabled = false;
            triggerEl.innerHTML = original;
        }
    }
};

// Rejoue les clics arrivés avant le chargement de ce fichier (file créée par le layout)
if (Array.isArray(SD._queue)) {
    SD._queue.splice(0).forEach((args) => SD.addToCart(...args));
    delete SD._queue;
}
