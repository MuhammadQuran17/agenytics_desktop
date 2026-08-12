<template>
    <div>
        <a
            :href="route('sso.redirect', { provider: 'google' })"
            class="flex items-center justify-center gap-2 w-full px-4 py-2 text-sm font-medium text-black bg-white border border-gray-300 rounded-md shadow hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
        >
            <img
                src="/images/7123025_logo_google_g_icon.svg"
                alt="Google Logo"
                class="w-6 h-6"
            />
            <span>Continue with Google</span>
        </a>
    </div>
</template>
