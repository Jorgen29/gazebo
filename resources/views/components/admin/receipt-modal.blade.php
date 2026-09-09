<!-- resources/views/components/admin/receipt-modal.blade.php -->
<div x-show="modalOpen" x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs"
    x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

    <div class="bg-white border border-[#E5DDD0] rounded-2xl max-w-lg w-full overflow-hidden shadow-2xl space-y-4 p-6"
        @click.outside="modalOpen = false">

        <div class="flex items-center justify-between border-b border-[#E5DDD0] pb-3">
            <div>
                <h3 class="font-bold text-gray-900 text-sm">Receipt Proof</h3>
                <p class="text-xs text-[#B89A62] font-medium" x-text="modalCustomer"></p>
            </div>
            <button @click="modalOpen = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Receipt Image Display -->
        <div
            class="bg-[#F7F4EE] rounded-xl p-2 border border-[#E5DDD0] flex items-center justify-center max-h-96 overflow-y-auto">
            <img :src="modalReceiptUrl" alt="Payment Receipt" class="max-w-full h-auto rounded-lg shadow-xs">
        </div>

        <!-- Footer Controls -->
        <div class="flex items-center justify-between pt-2">
            <a :href="modalReceiptUrl" target="_blank" download
                class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#6F927D] hover:underline">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                Download Original
            </a>
            <button @click="modalOpen = false"
                class="px-4 py-2 bg-[#6F927D] hover:bg-[#5b7a67] text-white text-xs font-semibold rounded-xl transition-all">
                Close Window
            </button>
        </div>
    </div>
</div>
