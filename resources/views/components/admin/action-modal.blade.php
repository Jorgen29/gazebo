<!-- resources/views/components/admin/action-modal.blade.php -->
<div x-show="actionModalOpen" x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs"
    x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

    <div class="bg-white border border-[#E5DDD0] rounded-2xl max-w-sm w-full overflow-hidden shadow-2xl space-y-4 p-6 max-h-[90vh] overflow-y-auto"
        @click.outside="actionModalOpen = false">

        <!-- Header -->
        <div class="flex items-center justify-between border-b border-[#E5DDD0] pb-3">
            <div>
                <h3 class="font-bold text-gray-900 text-sm">Manage Inquiry Actions</h3>
                <p class="text-xs text-[#B89A62] font-medium" x-text="selectedCustomer"></p>
            </div>
            <button @click="actionModalOpen = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Action Options -->
        <div class="space-y-2 py-2">
            <button type="button" @click="showInquiryDetails()"
                class="w-full px-4 py-2.5 rounded-xl text-xs font-semibold text-[#1C3627] bg-[#F7F4EE] hover:bg-[#E5DDD0]/60 flex items-center gap-3 transition-colors">
                <svg class="w-4 h-4 text-[#B89A62]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                View Details
            </button>

            <!-- Payment Session Summary -->
            <div class="rounded-xl bg-[#F7F4EE] border border-[#E5DDD0] p-3 space-y-1.5">
                <div class="text-[10px] font-bold uppercase tracking-[0.18em] text-[#6B7E73]">Payment Session</div>
                <div class="flex items-center justify-between gap-2 text-xs">
                    <span class="text-[#6B7E73]">Status</span>
                    <span class="font-bold text-[#1C3627]"
                        x-text="selectedPaymentStatus ? selectedPaymentStatus.toUpperCase() : 'No payment link'"></span>
                </div>
                <template x-if="selectedPaymentAmount">
                    <div class="flex items-center justify-between gap-2 text-xs">
                        <span class="text-[#6B7E73]">Amount</span>
                        <span class="font-bold text-[#1C3627]" x-text="selectedPaymentAmount"></span>
                    </div>
                </template>
                <template x-if="selectedPaymentExpiry">
                    <div class="flex items-center justify-between gap-2 text-xs">
                        <span class="text-[#6B7E73]">Expires</span>
                        <span class="font-semibold text-[#1C3627] text-right" x-text="selectedPaymentExpiry"></span>
                    </div>
                </template>
                <template x-if="selectedPaymentLinkUrl">
                    <a :href="selectedPaymentLinkUrl" target="_blank" rel="noopener"
                        class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#1C3627] underline decoration-[#E5DDD0] underline-offset-2 hover:text-[#2E4A3B]">
                        Open payment link
                    </a>
                </template>
            </div>

            <!-- Refund -->
            <div x-show="selectedPaymentStatus" class="rounded-xl bg-[#F7F4EE] border border-[#E5DDD0] p-3 space-y-1.5">
                <div class="text-[10px] font-bold uppercase tracking-[0.18em] text-[#6B7E73]">Refund</div>
                <div class="flex items-center justify-between gap-2 text-xs">
                    <span class="text-[#6B7E73]">Status</span>
                    <span class="font-bold text-[#1C3627]"
                        x-text="selectedRefundStatus ? selectedRefundStatus.toUpperCase() : 'Not requested'"></span>
                </div>
                <template x-if="selectedRefundAmount">
                    <div class="flex items-center justify-between gap-2 text-xs">
                        <span class="text-[#6B7E73]">Full amount</span>
                        <span class="font-bold text-[#1C3627]" x-text="selectedRefundAmount"></span>
                    </div>
                </template>
                <template x-if="selectedRefundFailureCode">
                    <div class="flex items-center justify-between gap-2 text-xs">
                        <span class="text-[#6B7E73]">Failure code</span>
                        <span class="font-semibold text-rose-600 text-right" x-text="selectedRefundFailureCode"></span>
                    </div>
                </template>
                <template x-if="selectedRefundNote">
                    <div class="text-[11px] text-[#6B7E73]">
                        <span class="font-semibold">Note:</span> <span x-text="selectedRefundNote"></span>
                    </div>
                </template>

                <template x-if="!canRefund && selectedPaymentStatus === 'paid'">
                    <p class="text-[10px] text-[#9B6C2D] font-medium pt-1">
                        A refund is already in progress or this payment has already been refunded.
                    </p>
                </template>

                <form x-show="canRefund"
                    :action="`{{ route('admin.inquiries.refund', ':id') }}`.replace(':id', selectedInquiryId)"
                    method="POST" class="space-y-2 pt-1">
                    @csrf

                    <template x-if="selectedRefundStatus === 'failed' || selectedRefundStatus === 'cancelled'">
                        <p class="text-[10px] text-rose-600 font-medium">
                            The previous attempt did not succeed. Retrying creates a new refund attempt.
                        </p>
                    </template>

                    <label class="block text-[10px] font-bold uppercase tracking-[0.16em] text-[#6B7E73]"
                        for="refund-reason">Reason</label>
                    <select id="refund-reason" name="reason" x-model="selectedRefundReason"
                        class="w-full bg-white border border-[#E5DDD0] text-[#1C3627] text-xs rounded-lg px-2.5 py-2 font-semibold focus:outline-none focus:ring-2 focus:ring-[#6F927D]/35">
                        <option value="FRAUDULENT">Fraudulent</option>
                        <option value="DUPLICATE">Duplicate</option>
                        <option value="REQUESTED_BY_CUSTOMER">Requested by customer</option>
                        <option value="CANCELLATION">Customer cancellation</option>
                        <option value="OTHERS">Others</option>
                    </select>

                    <label class="block text-[10px] font-bold uppercase tracking-[0.16em] text-[#6B7E73]"
                        for="refund-note">Internal note (optional)</label>
                    <textarea id="refund-note" name="note" x-model="refundNote" rows="2" maxlength="500"
                        placeholder="Never sent to Xendit or the customer"
                        class="w-full bg-white border border-[#E5DDD0] text-[#1C3627] text-xs rounded-lg px-2.5 py-2 focus:outline-none focus:ring-2 focus:ring-[#6F927D]/35"></textarea>

                    <label class="flex items-start gap-2 text-[10px] text-[#6B7E73] font-medium">
                        <input type="checkbox" x-model="refundConfirmed" class="mt-0.5 accent-[#1C3627]">
                        <span>I confirm a full refund of <strong x-text="selectedRefundAmount"></strong> to the original
                            payment method.</span>
                    </label>

                    <button type="submit" :disabled="!refundConfirmed"
                        class="w-full px-4 py-2.5 rounded-xl text-xs font-semibold text-[#1C3627] bg-[#F3EFEA] disabled:opacity-40 disabled:cursor-not-allowed hover:bg-[#E9E1D7] flex items-center gap-3 transition-colors">
                        <svg class="w-4 h-4 text-[#B89A62]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                        </svg>
                        <span
                            x-text="(selectedRefundStatus === 'failed' || selectedRefundStatus === 'cancelled') ? 'Retry full refund' : 'Refund full amount'"></span>
                    </button>
                </form>
            </div>

            <div class="border-t border-[#E5DDD0] my-2"></div>

            <!-- Send / Resend Payment Link -->
            <form :action="`{{ route('admin.inquiries.send-payment', ':id') }}`.replace(':id', selectedInquiryId)"
                method="POST">
                @csrf
                <button type="submit" x-bind:disabled="selectedInquiryStatus !== 'pending'"
                    x-bind:class="selectedInquiryStatus !== 'pending' ? 'opacity-40 cursor-not-allowed' : 'hover:bg-[#E5DDD0]/60'"
                    class="w-full px-4 py-2.5 rounded-xl text-xs font-semibold text-[#6F927D] bg-[#F7F4EE] flex items-center gap-3 transition-colors">
                    <svg class="w-4 h-4 text-[#B89A62]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>
                    <span x-text="selectedPaymentStatus ? 'Resend Payment Link' : 'Send Payment Link'"></span>
                </button>
            </form>

            <div class="border-t border-[#E5DDD0] my-2"></div>

            <!-- Approve Inquiry -->
            <form :action="`{{ route('admin.inquiries.update-status', ':id') }}`.replace(':id', selectedInquiryId)"
                method="POST">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="approved">
                <button type="submit"
                    :disabled="selectedPaymentStatus !== 'paid' || selectedInquiryStatus === 'approved'"
                    x-bind:class="(selectedPaymentStatus !== 'paid' || selectedInquiryStatus === 'approved') ?
                    'opacity-40 cursor-not-allowed' : 'hover:bg-emerald-100/80'"
                    class="w-full px-4 py-2.5 rounded-xl text-xs font-semibold text-emerald-700 bg-emerald-50 disabled:opacity-40 disabled:hover:bg-emerald-50 disabled:cursor-not-allowed flex items-center gap-3 transition-colors">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Approve Reservation
                </button>
                <template x-if="selectedPaymentStatus !== 'paid'">
                    <p class="text-[10px] text-rose-500 font-medium mt-1 px-1">* Requires the latest payment to be
                        paid</p>
                </template>
            </form>

            <!-- Decline Inquiry -->
            <form :action="`{{ route('admin.inquiries.update-status', ':id') }}`.replace(':id', selectedInquiryId)"
                method="POST">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="declined">
                <button type="submit"
                    :disabled="selectedInquiryStatus === 'approved' || selectedPaymentStatus === 'paid'"
                    x-bind:class="(selectedInquiryStatus === 'approved' || selectedPaymentStatus === 'paid') ?
                    'opacity-40 cursor-not-allowed' : 'hover:bg-rose-100/80'"
                    class="w-full px-4 py-2.5 rounded-xl text-xs font-semibold text-rose-700 bg-rose-50 flex items-center gap-3 transition-colors">
                    <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Decline Reservation
                </button>
                <template x-if="selectedPaymentStatus === 'paid'">
                    <p class="text-[10px] text-[#9B6C2D] font-medium mt-1 px-1">* A paid reservation must be refunded
                        before it can be declined or cancelled</p>
                </template>
            </form>

            <!-- Cancel Reservation -->
            <form :action="`{{ route('admin.inquiries.update-status', ':id') }}`.replace(':id', selectedInquiryId)"
                method="POST">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="cancelled">
                <button type="submit" x-show="selectedInquiryStatus === 'approved'"
                    :disabled="selectedPaymentStatus === 'paid'"
                    x-bind:class="selectedPaymentStatus === 'paid' ? 'opacity-40 cursor-not-allowed' : 'hover:bg-[#E9E1D7]'"
                    class="w-full px-4 py-2.5 rounded-xl text-xs font-semibold text-[#52463B] bg-[#F3EFEA] flex items-center gap-3 transition-colors">
                    <svg class="w-4 h-4 text-[#7A6557]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Cancel Reservation
                </button>
            </form>
        </div>

        <!-- Footer -->
        <div class="pt-2 border-t border-[#E5DDD0] flex justify-end">
            <button @click="actionModalOpen = false"
                class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-semibold rounded-xl transition-all">
                Cancel
            </button>
        </div>
    </div>
</div>
