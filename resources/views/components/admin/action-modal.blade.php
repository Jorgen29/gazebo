<!-- resources/views/components/admin/action-modal.blade.php -->
<div x-show="actionModalOpen" x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs"
    x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
    <div class="bg-white border border-[#E5DDD0] rounded-2xl max-w-sm w-full overflow-hidden shadow-2xl space-y-4 p-6" @click.outside="actionModalOpen = false">
        <div class="flex items-center justify-between border-b border-[#E5DDD0] pb-3">
            <div>
                <h3 class="font-bold text-gray-900 text-sm">Manage Inquiry Actions</h3>
                <p class="text-xs text-[#B89A62] font-medium" x-text="selectedCustomer"></p>
            </div>
            <button @click="actionModalOpen = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>

        <div class="space-y-2 py-2">
            <button type="button" @click="showInquiryDetails(); actionModalOpen = false;"
                class="w-full px-4 py-2.5 rounded-xl text-xs font-semibold text-[#1C3627] bg-[#F7F4EE] hover:bg-[#E5DDD0]/60 flex items-center gap-3 transition-colors">
                <svg class="w-4 h-4 text-[#B89A62]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                View Details
            </button>

            <div class="rounded-xl border border-[#E5DDD0] bg-[#F7F4EE] p-3 text-xs space-y-1">
                <div class="flex justify-between gap-2"><span class="text-[#6B7E73]">Payment</span><span class="font-semibold" x-text="selectedPaymentStatus"></span></div>
                <div class="flex justify-between gap-2"><span class="text-[#6B7E73]">Amount</span><span class="font-semibold" x-text="selectedPaymentAmount"></span></div>
                <div class="flex justify-between gap-2"><span class="text-[#6B7E73]">Expires</span><span class="font-semibold" x-text="selectedPaymentExpiry"></span></div>
                <template x-if="selectedInvoiceUrl"><a :href="selectedInvoiceUrl" target="_blank" rel="noopener" class="block pt-1 font-semibold text-[#1C3627] underline break-all">Open invoice link</a></template>
            </div>

            <div class="border-t border-[#E5DDD0] my-2"></div>

            <form :action="`{{ route('admin.inquiries.send-payment', ':id') }}`.replace(':id', selectedInquiryId)" method="POST">
                @csrf
                <button type="submit" :disabled="!canSendPayment"
                    :class="canSendPayment ? 'hover:bg-[#E5DDD0]/60' : 'opacity-40 cursor-not-allowed'"
                    class="w-full px-4 py-2.5 rounded-xl text-xs font-semibold text-[#1C3627] bg-[#F7F4EE] flex items-center gap-3 transition-colors">
                    <svg class="w-4 h-4 text-[#B89A62]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg>
                    <span x-text="sendLabel"></span>
                </button>
            </form>

            <div class="border-t border-[#E5DDD0] my-2"></div>

            <form :action="`{{ route('admin.inquiries.update-status', ':id') }}`.replace(':id', selectedInquiryId)" method="POST">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="approved">
                <button type="submit" :disabled="!canApprove || selectedInquiryStatus === 'approved'"
                    :class="(canApprove && selectedInquiryStatus !== 'approved') ? 'hover:bg-emerald-100/80' : 'opacity-40 cursor-not-allowed'"
                    class="w-full px-4 py-2.5 rounded-xl text-xs font-semibold text-emerald-700 bg-emerald-50 flex items-center gap-3 transition-colors">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    Approve Reservation
                </button>
                <template x-if="!canApprove && selectedInquiryStatus !== 'approved'">
                    <p class="text-[10px] text-rose-500 font-medium mt-1 px-1">* Requires the latest current payment to be paid</p>
                </template>
            </form>

            <form :action="`{{ route('admin.inquiries.update-status', ':id') }}`.replace(':id', selectedInquiryId)" method="POST">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="declined">
                <button type="submit" :disabled="selectedInquiryStatus === 'approved' || selectedInquiryStatus === 'declined'"
                    :class="(selectedInquiryStatus === 'approved' || selectedInquiryStatus === 'declined') ? 'opacity-40 cursor-not-allowed' : 'hover:bg-rose-100/80'"
                    class="w-full px-4 py-2.5 rounded-xl text-xs font-semibold text-rose-700 bg-rose-50 flex items-center gap-3 transition-colors">
                    <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    Decline Reservation
                </button>
            </form>

            <form :action="`{{ route('admin.inquiries.update-status', ':id') }}`.replace(':id', selectedInquiryId)" method="POST">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="cancelled">
                <button type="submit" x-show="selectedInquiryStatus === 'approved'"
                    class="w-full px-4 py-2.5 rounded-xl text-xs font-semibold text-[#52463B] bg-[#F3EFEA] hover:bg-[#E9E1D7] flex items-center gap-3 transition-colors">
                    <svg class="w-4 h-4 text-[#7A6557]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    Cancel Reservation
                </button>
            </form>
        </div>

        <div class="pt-2 border-t border-[#E5DDD0] flex justify-end">
            <button @click="actionModalOpen = false" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-semibold rounded-xl transition-all">Cancel</button>
        </div>
    </div>
</div>
