{{-- Champs d'identité partagés par les modales « Nouvel administrateur » et « Modifier ».
     S'appuie sur l'état Alpine de la page : form, showErrors. --}}
@php
    $inputClass = 'w-full h-11 px-4 rounded-xl bg-ivory-100 ring-1 ring-ink/5 focus:ring-2 focus:ring-terracotta-500 focus:bg-white outline-none transition text-sm';
    $labelClass = 'block text-xs uppercase tracking-widest text-ink-muted font-semibold mb-1.5';
@endphp

<div class="grid sm:grid-cols-2 gap-4">
    <div>
        <label class="{{ $labelClass }}" for="{{ $mode }}-first_name">Prénom</label>
        <input id="{{ $mode }}-first_name" type="text" name="first_name" x-model="form.first_name"
               required maxlength="100" autocomplete="off" class="{{ $inputClass }}">
        @error('first_name') <p x-show="showErrors" class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="{{ $labelClass }}" for="{{ $mode }}-last_name">Nom</label>
        <input id="{{ $mode }}-last_name" type="text" name="last_name" x-model="form.last_name"
               required maxlength="100" autocomplete="off" class="{{ $inputClass }}">
        @error('last_name') <p x-show="showErrors" class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>
</div>

<div>
    <label class="{{ $labelClass }}" for="{{ $mode }}-email">Adresse e-mail</label>
    <input id="{{ $mode }}-email" type="email" name="email" x-model="form.email"
           required maxlength="150" autocomplete="off" inputmode="email" class="{{ $inputClass }}">
    @error('email') <p x-show="showErrors" class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
</div>

<div>
    <label class="{{ $labelClass }}" for="{{ $mode }}-phone">Téléphone <span class="normal-case tracking-normal font-normal">(facultatif)</span></label>
    <input id="{{ $mode }}-phone" type="tel" name="phone" x-model="form.phone"
           maxlength="30" autocomplete="off" class="{{ $inputClass }}">
    @error('phone') <p x-show="showErrors" class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
</div>
