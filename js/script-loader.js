(function () {
    function getJson(u) {
        return fetch(u, {credentials: 'same-origin'}).then(function (r) {
            return r.json()
        })
    }

    function mapKey(cat) {
        var m = {
            'Functional': 'functional',
            'Analytics & Performance': 'analytics',
            'Advertising & Targeting': 'marketing'
        };
        return m[cat] || cat
    }

    function allowed(consent, cat) {
        if (!cat || cat === 'Strictly Necessary' || cat === 'essential') return true;
        var k = mapKey(cat);
        return !!consent && !!consent[k]
    }

    var REGISTRY = [], INJECTED = new Map();

    function inject(src, attrs) {
        var s = document.createElement('script');
        s.src = src;
        s.async = true;
        s.defer = true;
        s.dataset.mwSrc = src;
        if (attrs) {
            Object.keys(attrs).forEach(function (k) {
                s.setAttribute(k, attrs[k])
            })
        }
        document.head.appendChild(s);
        INJECTED.set(src, s)
    }

    function remove(src) {
        var s = INJECTED.get(src);
        if (s && s.parentNode) {
            s.parentNode.removeChild(s)
        }
        INJECTED.delete(src)
    }

    function setConsent(c) {
        window.MW_CONSENT = {
            essential: true,
            functional: !!(c && c.functional),
            analytics: !!(c && c.analytics),
            marketing: !!(c && c.marketing),
            performance: !!(c && c.performance)
        }
    }

    function loadRegistry(consent) {
        getJson('/cookie/scripts').then(function (res) {
            REGISTRY = (res && res.scripts) || [];
            REGISTRY.forEach(function (item) {
                if (item.is_active && allowed(consent, item.category_required)) {
                    inject(item.script_src)
                }
            });
            loadKnown(consent)
        }).catch(function () {
            loadKnown(consent)
        })
    }

    function cfg() {
        var c = window.MW_AD_CONFIG || window.__mwConsentCfg || {};
        return {
            fb: c.facebookPixelId || c.fbPixelId || window.__FB_PIXEL_ID || null,
            ads: c.googleAdsId || window.__GOOGLE_ADS_ID || null,
            ga: c.gtagId || window.__GA4_ID || null,
            li: c.linkedinPartnerId || c.linkedinPid || window.__LINKEDIN_PID || null
        }
    }

    var MWMarketing = {
        loadFacebookPixel: function (id) {
            if (!id || !window.MW_CONSENT.marketing) return;
            !function (f, b, e, v, n, t, s) {
                if (f.fbq) return;
                n = f.fbq = function () {
                    n.callMethod ? n.callMethod.apply(n, arguments) : n.queue.push(arguments)
                };
                if (!f._fbq) f._fbq = n;
                n.push = n;
                n.loaded = !0;
                n.version = '2.0';
                n.queue = [];
                t = b.createElement(e);
                t.async = !0;
                t.src = v;
                s = b.getElementsByTagName(e)[0];
                s.parentNode.insertBefore(t, s)
            }(window, document, 'script', 'https://connect.facebook.net/en_US/fbevents.js');
            window.fbq('init', id);
            window.fbq('track', 'PageView')
        },
        loadGoogleAds: function (id) {
            if (!id || !window.MW_CONSENT.marketing) return;
            inject('https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(id));
            window.dataLayer = window.dataLayer || [];
            window.gtag = function () {
                window.dataLayer.push(arguments)
            };
            window.gtag('js', new Date());
            window.gtag('config', id)
        },
        loadLinkedIn: function (pid) {
            if (!pid || !window.MW_CONSENT.marketing) return;
            inject('https://snap.licdn.com/li.lms-analytics/insight.min.js');
            window._linkedin_partner_id = pid;
            window.lintrk = (window.lintrk || function (a, b) {
                window.lintrk.q.push([a, b])
            });
            window.lintrk.q = []
        },
        disable: function () {
            if (window.fbq) {
                window.fbq = function () {
                }
            }
            if (window.gtag) {
                window.gtag = function () {
                }
            }
            if (window.lintrk) {
                window.lintrk = function () {
                }
            }
            ['https://connect.facebook.net/en_US/fbevents.js', 'https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(cfg().ads || ''), 'https://snap.licdn.com/li.lms-analytics/insight.min.js'].forEach(remove)
        },
        trackJobView: function () {
            if (!window.MW_CONSENT.marketing) return;
            if (window.fbq) window.fbq('track', 'ViewContent')
        },
        trackApply: function () {
            if (!window.MW_CONSENT.marketing) return;
            if (window.fbq) window.fbq('track', 'Lead')
        },
        trackSignup: function () {
            if (!window.MW_CONSENT.marketing) return;
            if (window.fbq) window.fbq('track', 'CompleteRegistration')
        }
    };
    window.MWMarketing = MWMarketing;

    function loadKnown(consent) {
        var c = cfg();
        if (consent && consent.marketing) {
            MWMarketing.loadFacebookPixel(c.fb);
            MWMarketing.loadLinkedIn(c.li);
            MWMarketing.loadGoogleAds(c.ads)
        }
        if (consent && consent.analytics && c.ga) {
            inject('https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(c.ga));
            window.dataLayer = window.dataLayer || [];
            window.gtag = function () {
                window.dataLayer.push(arguments)
            };
            window.gtag('js', new Date());
            window.gtag('config', c.ga, {anonymize_ip: true})
        }
        var nodes = document.querySelectorAll('script[data-consent-category][src]');
        nodes.forEach(function (n) {
            var cat = n.getAttribute('data-consent-category');
            if (allowed(consent, cat)) {
                var src = n.getAttribute('src');
                inject(src)
            }
        
        })
    }

    function applyConsent(consent, settings) {
        setConsent(consent);
        var block = (settings && settings.block_scripts_until_consent);
        if (block && (!consent)) {
            return
        }
        loadRegistry(consent)
    }

    document.addEventListener('mw:consent-updated', function (e) {
        var c = e.detail && e.detail.consent;
        var s = e.detail && e.detail.settings;
        applyConsent(c, s);
        if (!(c && c.marketing)) {
            MWMarketing.disable()
        }
    });
    document.addEventListener('DOMContentLoaded', function () {
        getJson('/cookie/status').then(function (s) {
            applyConsent(s.consent, s.settings)
        })
    });
})();
