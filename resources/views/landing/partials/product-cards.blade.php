{{-- Cartes produit compactes — $products (collection/paginator), $animate (bool : effet reveal) --}}
@foreach($products as $i => $product)
    @php
        $soldOut = $product->is_sold_out;
        $rating  = (float) ($product->rating_value ?? 0);
    @endphp

    <article class="group {{ $animate ? 'reveal' : '' }}"
             @if($animate) style="transition-delay: {{ ($i % 5) * .04 }}s" @endif>
        <a href="{{ route('products.show', $product) }}"
           class="block h-full bg-white rounded-2xl ring-1 ring-ink/5 overflow-hidden hover:shadow-lift hover:-translate-y-0.5 transition-all duration-300">

            <div class="relative aspect-square bg-sand-100 overflow-hidden">
                @if($product->featured_image_url)
                    <img src="{{ $product->featured_image_url }}"
                         alt="{{ $product->name }}"
                         loading="lazy"
                         class="w-full h-full object-cover group-hover:scale-[1.05] transition-transform duration-700 ease-out {{ $soldOut ? 'grayscale opacity-70' : '' }}">
                @else
                    <div class="w-full h-full flex items-center justify-center text-ink-muted">
                        <i class="fa-regular fa-image text-3xl"></i>
                    </div>
                @endif

                @if($soldOut)
                    <span class="absolute top-2 left-2 bg-ink text-ivory-50 text-[10px] font-bold uppercase tracking-wider px-2 py-1 rounded-full">
                        Épuisé
                    </span>
                @else
                    @if($product->on_sale)
                        <span class="absolute top-2 left-2 bg-terracotta-500 text-white text-[10px] font-bold px-2 py-1 rounded-full">
                            -{{ $product->discount_percent }}%
                        </span>
                    @endif

                    {{-- Ajout rapide : toujours visible sur mobile, au survol sur grand écran --}}
                    <button type="button" onclick="event.preventDefault(); event.stopPropagation(); SD.addToCart('{{ $product->id }}', 1, this);"
                            class="absolute bottom-2 right-2 w-10 h-10 rounded-full bg-ink text-ivory-50 shadow-lg flex items-center justify-center transition-all duration-300 hover:bg-terracotta-500
                                   opacity-100 translate-y-0 lg:opacity-0 lg:translate-y-2 lg:group-hover:opacity-100 lg:group-hover:translate-y-0"
                            aria-label="Ajouter {{ $product->name }} au panier">
                        <i class="fa-solid fa-plus text-sm"></i>
                    </button>
                @endif
            </div>

            <div class="p-3">
                <h3 class="text-sm font-medium leading-snug line-clamp-2 min-h-[2.5rem] group-hover:text-terracotta-500 transition-colors">
                    {{ $product->name }}
                </h3>

                <div class="mt-1.5 flex items-baseline flex-wrap gap-x-2">
                    <p class="font-bold text-base {{ $soldOut ? 'text-ink-muted' : 'text-terracotta-600' }}">{{ $product->formatted_price }}</p>
                    @if($product->on_sale && !$soldOut)
                        <p class="text-xs text-ink-muted line-through">{{ number_format($product->compare_price, 0, ',', ' ') }} FCFA</p>
                    @endif
                </div>

                <div class="mt-1.5 flex items-center gap-2 text-[11px] text-ink-muted min-h-[1rem]">
                    @if($rating > 0 && (int) $product->rating_count > 0)
                        <span class="inline-flex items-center gap-1">
                            <i class="fa-solid fa-star text-amber-500 text-[10px]"></i>
                            {{ number_format($rating, 1, ',', '') }}
                            <span>({{ (int) $product->rating_count }})</span>
                        </span>
                    @endif
                    @if(!$soldOut && $product->is_low_stock)
                        <span class="inline-flex items-center gap-1 text-terracotta-600 font-semibold">
                            <i class="fa-solid fa-fire text-[10px]"></i> Plus que {{ $product->stock }}
                        </span>
                    @endif
                </div>
            </div>
        </a>
    </article>
@endforeach
