{{-- Choix du mode de transmission de l'accès (création et réinitialisation).
     S'appuie sur l'état Alpine : delivery ('link' | 'password'). --}}
<fieldset>
    <legend class="block text-xs uppercase tracking-widest text-ink-muted font-semibold mb-2">Transmission de l'accès</legend>

    <div class="space-y-2.5">
        <label class="flex gap-3 p-4 rounded-2xl ring-1 cursor-pointer transition"
               :class="delivery === 'link' ? 'ring-2 ring-terracotta-500 bg-terracotta-50' : 'ring-ink/10 hover:bg-ivory-50'">
            <input type="radio" name="delivery" value="link" x-model="delivery" class="mt-1 accent-[#C4573A]">
            <span>
                <span class="block text-sm font-semibold">Envoyer un lien par e-mail <span class="ml-1 text-[10px] font-bold uppercase tracking-wider text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded-full">Recommandé</span></span>
                <span class="block text-xs text-ink-muted mt-0.5">L'administrateur reçoit un lien sécurisé pour choisir lui-même son mot de passe. Aucun mot de passe ne circule.</span>
            </span>
        </label>

        <label class="flex gap-3 p-4 rounded-2xl ring-1 cursor-pointer transition"
               :class="delivery === 'password' ? 'ring-2 ring-terracotta-500 bg-terracotta-50' : 'ring-ink/10 hover:bg-ivory-50'">
            <input type="radio" name="delivery" value="password" x-model="delivery" class="mt-1 accent-[#C4573A]">
            <span>
                <span class="block text-sm font-semibold">Envoyer des identifiants provisoires</span>
                <span class="block text-xs text-ink-muted mt-0.5">Un mot de passe provisoire est envoyé par e-mail et vous est aussi affiché une seule fois, au cas où l'e-mail n'arrive pas.</span>
            </span>
        </label>
    </div>

    @error('delivery') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
</fieldset>
