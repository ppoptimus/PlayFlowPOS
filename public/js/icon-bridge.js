(function () {
    const iconMap = {
        'bi-activity': ['fa-solid', 'fa-wave-square'],
        'bi-arrow-left': ['fa-solid', 'fa-arrow-left'],
        'bi-arrow-right': ['fa-solid', 'fa-arrow-right'],
        'bi-arrows-angle-expand': ['fa-solid', 'fa-up-right-and-down-left-from-center'],
        'bi-bar-chart-line': ['fa-solid', 'fa-chart-line'],
        'bi-bar-chart-line-fill': ['fa-solid', 'fa-chart-line'],
        'bi-box-arrow-right': ['fa-solid', 'fa-right-from-bracket'],
        'bi-box-seam': ['fa-solid', 'fa-box'],
        'bi-box-seam-fill': ['fa-solid', 'fa-box'],
        'bi-box2-heart': ['fa-solid', 'fa-box-open'],
        'bi-building-fill': ['fa-solid', 'fa-building'],
        'bi-calendar-check': ['fa-solid', 'fa-calendar-check'],
        'bi-calendar-event-fill': ['fa-solid', 'fa-calendar-days'],
        'bi-calendar2-check': ['fa-solid', 'fa-calendar-check'],
        'bi-cart-fill': ['fa-solid', 'fa-cart-shopping'],
        'bi-cart4': ['fa-solid', 'fa-cart-shopping'],
        'bi-cash': ['fa-solid', 'fa-money-bill-wave'],
        'bi-cash-coin': ['fa-solid', 'fa-coins'],
        'bi-cash-stack': ['fa-solid', 'fa-coins'],
        'bi-check-circle-fill': ['fa-solid', 'fa-circle-check'],
        'bi-check2-circle': ['fa-solid', 'fa-circle-check'],
        'bi-chevron-down': ['fa-solid', 'fa-chevron-down'],
        'bi-chevron-right': ['fa-solid', 'fa-chevron-right'],
        'bi-clock-history': ['fa-solid', 'fa-clock-rotate-left'],
        'bi-cloud-arrow-up': ['fa-solid', 'fa-cloud-arrow-up'],
        'bi-credit-card': ['fa-solid', 'fa-credit-card'],
        'bi-dash-lg': ['fa-solid', 'fa-minus'],
        'bi-door-open': ['fa-solid', 'fa-door-open'],
        'bi-exclamation-octagon-fill': ['fa-solid', 'fa-circle-xmark'],
        'bi-exclamation-triangle-fill': ['fa-solid', 'fa-triangle-exclamation'],
        'bi-filter': ['fa-solid', 'fa-filter'],
        'bi-flower1': ['fa-solid', 'fa-spa'],
        'bi-funnel': ['fa-solid', 'fa-filter'],
        'bi-grid-fill': ['fa-solid', 'fa-table-cells-large'],
        'bi-info-circle': ['fa-solid', 'fa-circle-info'],
        'bi-info-circle-fill': ['fa-solid', 'fa-circle-info'],
        'bi-journal-plus': ['fa-solid', 'fa-square-plus'],
        'bi-line': ['fa-brands', 'fa-line'],
        'bi-list': ['fa-solid', 'fa-bars'],
        'bi-list-stars': ['fa-solid', 'fa-list-check'],
        'bi-megaphone': ['fa-solid', 'fa-bullhorn'],
        'bi-percent': ['fa-solid', 'fa-percent'],
        'bi-pencil-square': ['fa-solid', 'fa-pen-to-square'],
        'bi-people': ['fa-solid', 'fa-users'],
        'bi-people-fill': ['fa-solid', 'fa-users'],
        'bi-person': ['fa-solid', 'fa-user'],
        'bi-person-badge': ['fa-solid', 'fa-id-badge'],
        'bi-person-badge-fill': ['fa-solid', 'fa-id-badge'],
        'bi-person-plus-fill': ['fa-solid', 'fa-user-plus'],
        'bi-person-walking': ['fa-solid', 'fa-person-walking'],
        'bi-plus-lg': ['fa-solid', 'fa-plus'],
        'bi-printer': ['fa-solid', 'fa-print'],
        'bi-qr-code-scan': ['fa-solid', 'fa-qrcode'],
        'bi-receipt': ['fa-solid', 'fa-receipt'],
        'bi-receipt-cutoff': ['fa-solid', 'fa-receipt'],
        'bi-save': ['fa-solid', 'fa-floppy-disk'],
        'bi-save2': ['fa-solid', 'fa-floppy-disk'],
        'bi-search': ['fa-solid', 'fa-magnifying-glass'],
        'bi-shield-check': ['fa-solid', 'fa-shield-halved'],
        'bi-shield-lock-fill': ['fa-solid', 'fa-shield-halved'],
        'bi-shop-window': ['fa-solid', 'fa-store'],
        'bi-sliders': ['fa-solid', 'fa-sliders'],
        'bi-speedometer2': ['fa-solid', 'fa-gauge-high'],
        'bi-telephone': ['fa-solid', 'fa-phone'],
        'bi-tools': ['fa-solid', 'fa-screwdriver-wrench'],
        'bi-trash': ['fa-solid', 'fa-trash'],
        'bi-trash3': ['fa-solid', 'fa-trash'],
        'bi-wallet': ['fa-solid', 'fa-wallet'],
        'bi-wallet2': ['fa-solid', 'fa-wallet'],
        'bi-x-circle-fill': ['fa-solid', 'fa-circle-xmark'],
    };

    const iconKeys = Object.keys(iconMap);

    function applyBridge(element) {
        if (!(element instanceof Element)) {
            return;
        }

        const matchedIcon = iconKeys.find(function (iconKey) {
            return element.classList.contains(iconKey);
        });

        if (!matchedIcon) {
            return;
        }

        element.classList.remove('bi');
        iconKeys.forEach(function (iconKey) {
            if (element.classList.contains(iconKey) && iconKey !== matchedIcon) {
                element.classList.remove(iconKey);
            }
        });
        element.classList.remove(matchedIcon);
        element.classList.add('pf-fa-icon');
        element.classList.add.apply(element.classList, iconMap[matchedIcon]);
    }

    function scan(root) {
        if (!(root instanceof Element) && root !== document) {
            return;
        }

        if (root instanceof Element) {
            applyBridge(root);
        }

        const scope = root === document ? document : root;
        scope.querySelectorAll('[class*="bi-"], .bi').forEach(applyBridge);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            scan(document);
        });
    } else {
        scan(document);
    }

    const observer = new MutationObserver(function (mutations) {
        mutations.forEach(function (mutation) {
            mutation.addedNodes.forEach(function (node) {
                if (node instanceof Element) {
                    scan(node);
                }
            });
        });
    });

    observer.observe(document.documentElement, {
        childList: true,
        subtree: true,
    });
})();
