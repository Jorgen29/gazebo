<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Gazebo Management System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="min-h-screen bg-white text-gray-800 font-sans antialiased flex">

    <!-- Admin Sidebar Component -->
    <x-admin.sidebar />

    <!-- Main Workspace -->
    <div class="flex-1 flex flex-col min-w-0 min-h-screen bg-gray-50/50">

        <!-- Admin Header Component -->
        <x-admin.header />

        <!-- Main Content Area -->
        <main class="p-6 lg:p-8 space-y-8 flex-1 overflow-y-auto">

            <!-- Metrics Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white border border-gray-200 p-6 rounded-2xl shadow-sm flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-gray-500">Total Inquiries</p>
                        <h3 class="text-3xl font-extrabold text-gray-900 mt-1">24</h3>
                    </div>
                    <div
                        class="w-12 h-12 bg-blue-50 border border-blue-100 rounded-xl flex items-center justify-center text-blue-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>

                <div
                    class="bg-white border border-gray-200 p-6 rounded-2xl shadow-sm flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-gray-500">Pending Payments</p>
                        <h3 class="text-3xl font-extrabold text-amber-600 mt-1">5</h3>
                    </div>
                    <div
                        class="w-12 h-12 bg-amber-50 border border-amber-100 rounded-xl flex items-center justify-center text-amber-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>

                <div
                    class="bg-white border border-gray-200 p-6 rounded-2xl shadow-sm flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-gray-500">Verified Payments</p>
                        <h3 class="text-3xl font-extrabold text-emerald-600 mt-1">12</h3>
                    </div>
                    <div
                        class="w-12 h-12 bg-emerald-50 border border-emerald-100 rounded-xl flex items-center justify-center text-emerald-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>

                <div
                    class="bg-white border border-gray-200 p-6 rounded-2xl shadow-sm flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-gray-500">Active Venues</p>
                        <h3 class="text-3xl font-extrabold text-[#b89462] mt-1">4</h3>
                    </div>
                    <div
                        class="w-12 h-12 bg-[#b89462]/10 border border-[#b89462]/20 rounded-xl flex items-center justify-center text-[#b89462]">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Inquiries Data Table -->
            <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm">
                <div class="mb-6">
                    <h3 class="text-base font-bold text-gray-900 tracking-wide uppercase">Recent Inquiries</h3>
                    <p class="text-sm text-gray-500">Venue booking requests & payment verification logs</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm border-collapse">
                        <thead>
                            <tr
                                class="border-b border-gray-200 text-gray-500 uppercase font-bold text-xs tracking-wider">
                                <th class="py-4 px-4">Ref ID</th>
                                <th class="py-4 px-4">Client Name</th>
                                <th class="py-4 px-4">Venue</th>
                                <th class="py-4 px-4">Booking Date</th>
                                <th class="py-4 px-4">Status</th>
                                <th class="py-4 px-4 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-gray-700">
                            <tr class="hover:bg-gray-50/80 transition-colors">
                                <td class="py-4 px-4 font-mono text-[#b89462] font-bold text-sm">R1</td>
                                <td class="py-4 px-4 font-semibold text-gray-900">John Doe</td>
                                <td class="py-4 px-4 font-medium">The Courtyard & Grounds</td>
                                <td class="py-4 px-4">Sep 15, 2026</td>
                                <td class="py-4 px-4">
                                    <span
                                        class="bg-emerald-50 text-emerald-700 border border-emerald-200 px-3 py-1 rounded-full text-xs font-semibold uppercase">Payment
                                        Received</span>
                                </td>
                                <td class="py-4 px-4 text-right">
                                    <button
                                        class="bg-gray-900 hover:bg-gray-800 text-white px-4 py-2 rounded-xl text-xs font-medium transition-all">
                                        View Details
                                    </button>
                                </td>
                            </tr>
                            <tr class="hover:bg-gray-50/80 transition-colors">
                                <td class="py-4 px-4 font-mono text-[#b89462] font-bold text-sm">R2</td>
                                <td class="py-4 px-4 font-semibold text-gray-900">Jane Smith</td>
                                <td class="py-4 px-4 font-medium">Grand Gazebo Hall</td>
                                <td class="py-4 px-4">Sep 20, 2026</td>
                                <td class="py-4 px-4">
                                    <span
                                        class="bg-amber-50 text-amber-700 border border-amber-200 px-3 py-1 rounded-full text-xs font-semibold uppercase">Awaiting
                                        Proof</span>
                                </td>
                                <td class="py-4 px-4 text-right">
                                    <button
                                        class="bg-gray-900 hover:bg-gray-800 text-white px-4 py-2 rounded-xl text-xs font-medium transition-all">
                                        View Details
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>

    <!-- Session Alerts -->
    @if (session('success'))
        <script>
            Swal.fire({
                title: 'Welcome Back!',
                text: "{{ session('success') }}",
                icon: 'success',
                timer: 2000,
                showConfirmButton: false,
                background: '#ffffff',
                color: '#111827',
                customClass: {
                    popup: 'border border-gray-200 rounded-2xl shadow-xl'
                }
            });
        </script>
    @endif

</body>

</html>
