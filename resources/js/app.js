function samePage(url) {
    return url.pathname === window.location.pathname
        && url.search === window.location.search
        && url.hash === window.location.hash;
}

function parseUrl(href) {
    try {
        return new URL(href, window.location.href);
    } catch {
        return null;
    }
}

function isInternalGet(anchor) {
    if (!(anchor instanceof HTMLAnchorElement) || !anchor.href) {
        return false;
    }

    if (anchor.hasAttribute('download') || anchor.target === '_blank') {
        return false;
    }

    const url = parseUrl(anchor.href);

    if (!url || url.origin !== window.location.origin || url.protocol === 'javascript:') {
        return false;
    }

    if (url.pathname === window.location.pathname && url.search === window.location.search && url.hash !== '') {
        return false;
    }

    return !samePage(url);
}

function progressRoot() {
    return document.getElementById('lab-progress');
}

function startProgress() {
    const bar = progressRoot();

    if (!bar) {
        return;
    }

    document.documentElement.classList.add('lab-navigating');
    bar.hidden = false;
}

function stopProgress() {
    const bar = progressRoot();

    document.documentElement.classList.remove('lab-navigating');

    if (bar) {
        bar.hidden = true;
    }
}

function closestAnchor(target) {
    if (!(target instanceof Element)) {
        return null;
    }

    return target.closest('a[href]');
}

document.addEventListener('click', (event) => {
    if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
        return;
    }

    const anchor = closestAnchor(event.target);

    if (!anchor || !isInternalGet(anchor)) {
        return;
    }

    startProgress();
}, { capture: true });

document.addEventListener('submit', (event) => {
    const form = event.target;

    if (!(form instanceof HTMLFormElement) || form.target === '_blank') {
        return;
    }

    startProgress();
}, { capture: true });

window.addEventListener('pageshow', () => {
    stopProgress();
});

document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') {
        return;
    }

    const toggle = document.getElementById('lab-nav-toggle');

    if (toggle && toggle.checked) {
        toggle.checked = false;
    }

    document.querySelectorAll('details.lab-dropdown[open]').forEach((dropdown) => {
        dropdown.removeAttribute('open');
    });
});

document.addEventListener('toggle', (event) => {
    const dropdown = event.target;

    if (!(dropdown instanceof HTMLDetailsElement) || !dropdown.classList.contains('lab-dropdown') || !dropdown.open) {
        return;
    }

    document.querySelectorAll('details.lab-dropdown[open]').forEach((other) => {
        if (other !== dropdown) {
            other.removeAttribute('open');
        }
    });
}, true);

document.addEventListener('click', (event) => {
    if (event.target instanceof Element && event.target.closest('details.lab-dropdown')) {
        return;
    }

    document.querySelectorAll('details.lab-dropdown[open]').forEach((dropdown) => {
        dropdown.removeAttribute('open');
    });
});
