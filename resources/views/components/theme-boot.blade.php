<script>
    (() => {
        const root = document.documentElement;
        const preference = root.dataset.theme || 'system';
        const media = window.matchMedia('(prefers-color-scheme: dark)');

        const apply = () => {
            const dark = preference === 'dark' || (preference === 'system' && media.matches);
            root.classList.toggle('dark', dark);
            const meta = document.querySelector('meta[name="theme-color"]');
            if (meta) {
                meta.setAttribute('content', dark ? '#020617' : '#f1f5f9');
            }
        };

        apply();
        media.addEventListener('change', apply);
    })();
</script>
