<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In - Gazebo Management System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body
    class="min-h-screen flex items-center justify-center p-4 relative font-sans antialiased bg-[#0d1410] overflow-hidden">

    <!-- Blurred Background Image Container -->
    <div class="absolute inset-0 z-0">
        <!-- Replace the Unsplash URL below with your local hero/venue image path if desired (e.g., asset('images/hero.jpg')) -->
        <img src="https://images.unsplash.com/photo-1519167758481-83f550bb49b3?auto=format&fit=crop&w=1920&q=80"
            alt="Background" class="w-full h-full object-cover filter blur-sm scale-105 brightness-50">
        <!-- Dark Overlay for High Text Contrast -->
        <div class="absolute inset-0 bg-[#0d1410]/70 backdrop-blur-md"></div>
    </div>

    <!-- Ambient Lighting Overlay -->
    <div class="absolute -top-32 -left-32 w-96 h-96 bg-[#2d3f31]/40 rounded-full blur-3xl pointer-events-none z-0">
    </div>
    <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-[#b89462]/20 rounded-full blur-3xl pointer-events-none z-0">
    </div>

    <!-- Main Form Container -->
    <div class="w-full max-w-md relative z-10">

        <!-- Glassmorphism Card -->
        <div class="bg-[#16221b]/85 backdrop-blur-xl border border-white/15 rounded-2xl p-8 shadow-2xl relative">

            <!-- Header Section -->
            <div class="text-center mb-8">
                <div
                    class="w-12 h-12 bg-[#233328] border border-[#b89462]/40 rounded-xl mx-auto flex items-center justify-center shadow-md mb-3">
                    <svg class="w-6 h-6 text-[#b89462]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <h2 class="text-lg font-bold text-white tracking-wider uppercase">Gazebo Management System</h2>
                <p class="text-[11px] text-[#8e9f93] mt-1 font-medium tracking-widest uppercase">Admin Portal</p>
            </div>

            <!-- Global Status Alert -->
            @if (session('status'))
                <div
                    class="mb-5 text-xs font-medium text-emerald-300 bg-emerald-950/70 border border-emerald-500/30 p-3 rounded-xl">
                    {{ session('status') }}
                </div>
            @endif

            <!-- Login Form -->
            <form id="adminLoginForm" action="{{ route('admin.login.submit') }}" method="POST" class="space-y-5">
                @csrf

                <!-- Username or Email Address Input -->
                <div>
                    <label for="login"
                        class="block text-[11px] font-bold uppercase tracking-wider text-[#a3b3a7] mb-1.5">
                        Username or Email
                    </label>
                    <input type="text" id="login" name="login" value="{{ old('login') }}" required autofocus
                        placeholder="admin or admin@gmail.com"
                        class="w-full bg-[#0f1712]/90 border @error('login') border-red-500 focus:border-red-500 focus:ring-red-500/30 @else border-white/20 focus:border-[#b89462] focus:ring-[#b89462]/30 @enderror rounded-xl px-4 py-3 text-sm text-white placeholder-gray-500 focus:outline-none focus:ring-2 transition-all">

                    <!-- Inline Error -->
                    @error('login')
                        <p class="mt-1.5 text-xs text-red-400 font-medium flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>{{ $message }}</span>
                        </p>
                    @enderror
                </div>

                <!-- Password Input -->
                <div>
                    <label for="password"
                        class="block text-[11px] font-bold uppercase tracking-wider text-[#a3b3a7] mb-1.5">
                        Password
                    </label>
                    <div class="relative">
                        <input type="password" id="password" name="password" required placeholder="••••••••"
                            class="w-full bg-[#0f1712]/90 border @error('password') border-red-500 focus:border-red-500 focus:ring-red-500/30 @else border-white/20 focus:border-[#b89462] focus:ring-[#b89462]/30 @enderror rounded-xl px-4 py-3 pr-11 text-sm text-white placeholder-gray-500 focus:outline-none focus:ring-2 transition-all">

                        <button type="button"
                            class="absolute inset-y-0 right-3 flex items-center text-[#8e9f93] hover:text-white transition-colors"
                            aria-label="Show password" aria-pressed="false" data-password-toggle-for="password">
                            <svg class="eye-open h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12 18 18.75 12 18.75 2.25 12 2.25 12z" />
                                <circle cx="12" cy="12" r="2.75" stroke-width="1.8" />
                            </svg>
                            <svg class="eye-closed hidden h-5 w-5" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M3 3l18 18M10.5 10.5A2.5 2.5 0 0013.5 13.5M9.09 9.09A7.5 7.5 0 0112 7.5c5.25 0 9.75 4.5 9.75 4.5a16.5 16.5 0 01-4.06 4.73M6.2 6.2A16.5 16.5 0 002.25 12S5.25 18.75 12 18.75c2.73 0 4.9-.9 6.8-2.2" />
                            </svg>
                        </button>
                    </div>

                    <!-- Inline Error -->
                    @error('password')
                        <p class="mt-1.5 text-xs text-red-400 font-medium flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>{{ $message }}</span>
                        </p>
                    @enderror
                </div>

                <!-- Remember Me Checkbox -->
                <div class="flex items-center justify-between text-xs text-[#a3b3a7] pt-0.5">
                    <label class="flex items-center space-x-2 cursor-pointer select-none">
                        <input type="checkbox" name="remember"
                            class="rounded bg-[#0f1712] border-white/20 text-[#b89462] focus:ring-0 focus:ring-offset-0">
                        <span>Remember me</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <button type="submit"
                    class="w-full bg-[#b89462] hover:bg-[#a38153] text-white font-semibold py-3 px-4 rounded-xl text-xs uppercase tracking-widest transition-all duration-300 shadow-lg shadow-[#b89462]/10 active:scale-[0.99]">
                    LOG IN
                </button>
            </form>

            <!-- Footer Section -->
            <div class="mt-8 text-center border-t border-white/10 pt-4">
                <a href="{{ url('/') }}" class="text-xs text-[#8e9f93] hover:text-white transition-colors">
                    &larr; Back to Public Website
                </a>
            </div>

        </div>
    </div>

    <!-- SweetAlert Script Integration -->
    @if (session('success'))
        <script>
            Swal.fire({
                title: 'Welcome Back!',
                text: "{{ session('success') }}",
                icon: 'success',
                timer: 2000,
                showConfirmButton: false,
                background: '#16221b',
                color: '#ffffff',
                customClass: {
                    popup: 'border border-white/15 rounded-2xl shadow-2xl backdrop-blur-xl'
                }
            });
        </script>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggleButtons = document.querySelectorAll('[data-password-toggle-for]');

            toggleButtons.forEach((button) => {
                const targetId = button.getAttribute('data-password-toggle-for');
                const passwordInput = document.getElementById(targetId);
                const eyeOpen = button.querySelector('.eye-open');
                const eyeClosed = button.querySelector('.eye-closed');

                if (!passwordInput || !eyeOpen || !eyeClosed) {
                    return;
                }

                button.addEventListener('click', function() {
                    const shouldShow = passwordInput.type === 'password';
                    passwordInput.type = shouldShow ? 'text' : 'password';
                    button.setAttribute('aria-label', shouldShow ? 'Hide password' :
                        'Show password');
                    button.setAttribute('aria-pressed', String(shouldShow));
                    eyeOpen.classList.toggle('hidden', shouldShow);
                    eyeClosed.classList.toggle('hidden', !shouldShow);
                });
            });
        });
    </script>

</body>

</html>
