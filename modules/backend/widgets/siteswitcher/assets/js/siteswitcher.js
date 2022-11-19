/*
 * Site Switcher logic
 */
'use strict';

oc.Module.register('backend.widget.siteswitcher', function() {
    class SiteSwitcherWidget extends oc.FoundationBase
    {
        constructor(element, config) {
            super(element, config);

            oc.Events.on(this.element, 'click', '[data-handler]', this.proxy(this.onClickHandler));
            this.markDisposable();
        }

        dispose() {
            oc.Events.off(this.element, 'click', '[data-handler]', this.proxy(this.onClickHandler));
            super.dispose();
        }

        static get DATANAME() {
            return 'ocSiteSwitcherWidget';
        }

        static get DEFAULTS() {
            return {
            }
        }

        onClickHandler(ev) {
            ev.preventDefault();

            var $anchor = ev.target.closest('a');
            oc.request($anchor, $anchor.dataset.handler).done(function(data) {
                oc.Events.dispatch('backend:hidemenus');

                if (data.confirm) {
                    $.oc.confirm(data.confirm, function() {
                        oc.visit($anchor.href);
                    });
                }
                else {
                    oc.visit($anchor.href);
                }
            });
        }
    }

    addEventListener('render', function() {
        document.querySelectorAll('[data-control=siteswitcher]').forEach(function(el) {
            SiteSwitcherWidget.getOrCreateInstance(el);
        });
    });
});
