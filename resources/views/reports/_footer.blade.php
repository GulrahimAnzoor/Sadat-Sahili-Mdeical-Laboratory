<footer class="report-footer">
    <span class="report-rule report-rule-footer" aria-hidden="true"></span>

    <div class="report-footer-meta">
        <p>{{ implode(' · ', config('lab.phones')) }}</p>
        <span class="report-footer-dot" aria-hidden="true"></span>
        <p>{{ config('lab.email') }}</p>
        <span class="report-footer-dot" aria-hidden="true"></span>
        <p>{{ config('lab.address') }}</p>
    </div>
    <p class="report-footer-ps" dir="rtl" lang="ps">{{ config('lab.address_ps') }}</p>
</footer>
