<button type="button" wire:click="toggle"
        aria-pressed="{{ $isFavorited ? 'true' : 'false' }}"
        aria-label="{{ $isFavorited ? 'Remove from wishlist' : 'Add to wishlist' }}"
        class="w-10 h-10 rounded-full border border-gray-200 bg-white flex items-center justify-center hover:border-red-300 transition shrink-0">
    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 transition-colors {{ $isFavorited ? 'text-red-500' : 'text-gray-300' }}"
         viewBox="0 0 24 24" fill="{{ $isFavorited ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2">
        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78Z"/>
    </svg>
</button>
