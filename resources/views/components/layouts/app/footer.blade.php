<!-- Footer -->
<footer class="bg-[#f7f3ee] dark:bg-navy-950 border-t border-[#e4ddd3] dark:border-gold-400/20 mt-auto">
    <div class="px-6 py-4">
        <div class="flex flex-col md:flex-row justify-between items-center text-sm text-navy-800/55 dark:text-cream-100/70">
            <div class="mb-2 md:mb-0">
                &copy; {{ date('Y') }}
                <span class="font-semibold text-navy-800 dark:text-gold-400">PERGABI</span>
                <span class="mx-2">•</span>
                <span>v{{ config('app.version', '1.0.0') }}</span>
            </div>
            <div>
                Sistem Informasi Keanggotaan
            </div>
        </div>
    </div>
</footer>
