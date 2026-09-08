<footer class="report-footer">
    <span class="report-footer-rule" aria-hidden="true"></span>

    <div class="report-footer-body">
        <p class="report-footer-contact">
            {{ implode(' · ', array_merge(config('lab.phones'), [config('lab.email')])) }}
        </p>
        <p class="report-footer-address">{{ config('lab.address') }}</p>
        <p class="report-footer-ps" dir="rtl" lang="ps">{{ config('lab.address_ps') }}</p>
    </div>
</footer>
