<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Access Forbidden</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-[#F7F4EE] min-h-screen flex items-center justify-center p-4 antialiased text-gray-800">

    <div class="max-w-md w-full text-center space-y-6 bg-white p-8 rounded-2xl border border-[#E5DDD0] shadow-xl">
        <!-- Lock Graphic Icon -->
        <div class="mx-auto w-20 h-20 bg-amber-50 rounded-full flex items-center justify-center border border-amber-200">
            <svg class="w-10 h-10 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
        </div>

        <!-- Error Text -->
        <div class="space-y-2">
            <span class="text-xs font-bold tracking-widest text-[#B89A62] uppercase">Error 403</span>
            <h1 class="text-2xl font-bold text-gray-900">Access Restricted</h1>
            <p class="text-sm text-gray-500 leading-relaxed">
                {{ $exception->getMessage() ?: 'You do not have active authorization to access this page. Please log in first.' }}
            </p>
        </div>

        <!-- Return Button -->
        <div class="pt-2">
            <a href="{{ url('/') }}"
                class="inline-flex items-center justify-center gap-2 w-full px-5 py-2.5 text-sm font-semibold text-white bg-[#6F927D] hover:bg-[#5b7a67] transition-colors rounded-xl shadow-xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Return to Home</span>
            </a>
        </div>
    </div>

</body>

</html>
