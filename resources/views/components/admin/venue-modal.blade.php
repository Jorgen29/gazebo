<!-- resources/views/components/admin/venue-modal.blade.php -->
<div x-show="venueModalOpen" x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs"
    x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

    <div class="bg-white border border-[#E5DDD0] rounded-2xl max-w-2xl w-full overflow-hidden shadow-2xl space-y-4 p-6 my-8 max-h-[90vh] flex flex-col"
        @click.outside="venueModalOpen = false">

        <!-- Modal Header -->
        <div class="flex items-center justify-between border-b border-[#E5DDD0] pb-3 flex-shrink-0">
            <div>
                <h3 class="font-bold text-gray-900 text-sm" x-text="isEdit ? 'Edit Venue' : 'Add New Venue'"></h3>
                <p class="text-xs text-[#B89A62] font-medium"
                    x-text="isEdit ? 'Update venue information and feature highlights' : 'Fill in details to list a new venue'">
                </p>
            </div>
            <button @click="venueModalOpen = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Venue Form -->
        <form :action="isEdit ? `/admin/venues/${formData.id}` : '{{ route('admin.venues.store') }}'" method="POST"
            enctype="multipart/form-data" class="space-y-4 overflow-y-auto pr-1 flex-1"
            @submit.prevent="submitVenueForm($event)">
            @csrf
            <template x-if="isEdit">
                <input type="hidden" name="_method" value="PUT">
            </template>
            <input type="hidden" name="removed_showcase_images" x-bind:value="removedShowcaseImages.join(',')">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Title -->
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Venue Title</label>
                    <input type="text" name="title" x-model="formData.title" required
                        placeholder="e.g. Grand Ballroom"
                        class="w-full bg-[#F7F4EE] text-gray-800 text-xs font-medium rounded-xl border border-[#E5DDD0] px-3.5 py-2 focus:outline-none focus:ring-2 focus:ring-[#6F927D]/40 focus:bg-white transition-all">
                </div>

                <!-- Price -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Price per Hour (₱)</label>
                    <input type="number" step="0.01" name="price_per_hour" x-model="formData.price_per_hour"
                        required placeholder="0.00"
                        class="w-full bg-[#F7F4EE] text-gray-800 text-xs font-medium rounded-xl border border-[#E5DDD0] px-3.5 py-2 focus:outline-none focus:ring-2 focus:ring-[#6F927D]/40 focus:bg-white transition-all">
                </div>

                <!-- Capacity -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Capacity (Guests)</label>
                    <input type="number" name="capacity" x-model="formData.capacity" required placeholder="e.g. 150"
                        class="w-full bg-[#F7F4EE] text-gray-800 text-xs font-medium rounded-xl border border-[#E5DDD0] px-3.5 py-2 focus:outline-none focus:ring-2 focus:ring-[#6F927D]/40 focus:bg-white transition-all">
                </div>

                <!-- Main Cover Image -->
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Cover Image</label>
                    <input type="file" name="image" accept="image/*" @change="previewCoverImage($event)"
                        class="w-full text-xs text-gray-400 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-[#F7F4EE] file:text-gray-700 hover:file:bg-[#E5DDD0] cursor-pointer">

                    <div x-show="coverPreviewUrl" x-cloak
                        class="mt-3 rounded-2xl border border-[#E5DDD0] bg-[#F7F4EE] p-2">
                        <img :src="coverPreviewUrl" alt="Cover preview" class="w-full h-40 object-cover rounded-xl">
                    </div>
                </div>

                <!-- Status -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Status</label>
                    <select name="is_active" x-model="formData.is_active"
                        class="w-full bg-[#F7F4EE] text-gray-800 text-xs font-semibold rounded-xl border border-[#E5DDD0] px-3.5 py-2 focus:outline-none focus:ring-2 focus:ring-[#6F927D]/40 focus:bg-white transition-all">
                        <option value="1">Active / Available</option>
                        <option value="0">Inactive / Maintenance</option>
                    </select>
                </div>

                <!-- Showcase Images Upload -->
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Showcase Images (Multiple)</label>
                    <input type="file" name="showcase_images[]" accept="image/*" multiple
                        @change="previewShowcaseImages($event)"
                        class="w-full text-xs text-gray-400 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-[#F7F4EE] file:text-gray-700 hover:file:bg-[#E5DDD0] cursor-pointer">
                    <p class="text-[10px] text-gray-400 mt-1">Upload multiple photos to feature in the venue gallery
                        display.</p>

                    <div x-show="showcasePreviewUrls.length > 0" x-cloak class="mt-3 grid grid-cols-3 gap-2">
                        <template x-for="(preview, index) in showcasePreviewUrls" :key="index">
                            <div class="relative overflow-hidden rounded-xl border border-[#E5DDD0] bg-[#F7F4EE]">
                                <img :src="preview" alt="Showcase preview" class="w-full h-20 object-cover">
                                <button type="button" @click="removeShowcasePreview(index)"
                                    class="absolute top-1.5 right-1.5 w-6 h-6 rounded-full bg-[#1C3627]/85 text-white flex items-center justify-center hover:bg-[#1C3627] transition-colors cursor-pointer shadow-sm"
                                    title="Remove image">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Dynamic Features Section -->
                <div class="md:col-span-2 space-y-2 border-t border-[#E5DDD0] pt-3">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-semibold text-gray-700">Venue Features / Amenities</label>
                        <button type="button" @click="addFeature()"
                            class="inline-flex items-center gap-1 text-[11px] font-bold text-[#6F927D] hover:text-[#5b7a67] cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4" />
                            </svg>
                            Add Feature
                        </button>
                    </div>

                    <div class="space-y-2">
                        <template x-for="(feature, index) in formData.features" :key="index">
                            <div class="flex items-center gap-2">
                                <input type="text" name="features[]" x-model="formData.features[index]"
                                    placeholder="e.g. Free High-Speed Wi-Fi, Air Conditioned, Sound System"
                                    class="flex-1 bg-[#F7F4EE] text-gray-800 text-xs font-medium rounded-xl border border-[#E5DDD0] px-3.5 py-2 focus:outline-none focus:ring-2 focus:ring-[#6F927D]/40 focus:bg-white transition-all">
                                <button type="button" @click="removeFeature(index)"
                                    class="p-2 text-rose-500 hover:bg-rose-50 rounded-xl transition-colors"
                                    x-show="formData.features.length > 1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Description -->
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Description</label>
                    <textarea name="description" x-model="formData.description" rows="3"
                        placeholder="Briefly describe venue amenities and specifications..."
                        class="w-full bg-[#F7F4EE] text-gray-800 text-xs font-medium rounded-xl border border-[#E5DDD0] p-3 focus:outline-none focus:ring-2 focus:ring-[#6F927D]/40 focus:bg-white transition-all"></textarea>
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="pt-3 border-t border-[#E5DDD0] flex items-center justify-end gap-2 flex-shrink-0">
                <button type="button" @click="venueModalOpen = false"
                    class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-semibold rounded-xl transition-all">
                    Cancel
                </button>
                <button type="submit"
                    class="px-4 py-2 bg-[#6F927D] hover:bg-[#5b7a67] text-white text-xs font-semibold rounded-xl transition-all shadow-xs"
                    x-text="isEdit ? 'Update Venue' : 'Save Venue'">
                </button>
            </div>
        </form>
    </div>
</div>
