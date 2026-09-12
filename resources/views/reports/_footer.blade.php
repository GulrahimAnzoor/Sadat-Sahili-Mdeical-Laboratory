<footer class="report-footer">
    <span class="report-rule report-footer-rule" aria-hidden="true"></span>

    <div class="report-footer-body">
        <p class="report-footer-contact">
            {{ implode(' · ', config('lab.phones')) }}
        </p>
        <div class="report-footer-addresses">
            <p class="report-footer-address">{{ config('lab.address') }}</p>
            <p class="report-footer-ps" dir="rtl" lang="ps">{{ config('lab.address_ps') }}</p>
        </div>
    </div>
</footer>
