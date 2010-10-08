/*
---

name: Jx.Section

description: This class creates a basic folding section.

license: MIT-style license.

requires:
 - jxlib/Jx.Widget
 - jxlib/Jx.Slide

provides: [Jx.Section]

...
 */

Jx.Section = new Class({

    Family: 'Jx.Section',
    Extends: Jx.Widget,

    options: {
        template: '<div class="jxSection"><div class="jxSectionHeader"></div><div class="jxSectionBody"></div></div>',

        parent: null,
        heading: null,
        body: null
    },

    classes: $H({
        domObj: 'jxSection',
        header: 'jxSectionHeader',
        body: 'jxSectionBody'
    }),

    render: function() {
        this.parent();
        opts = this.options;

        if ($defined(opts.heading) && $defined(this.header)) {
            var h = document.id(opts.heading);
            if (h) {
                h.inject(this.header);
            } else {
                this.header.set('html','<p>' + opts.heading + '</p>');
            }
        }

        if ($defined(opts.body) && $defined(this.body)) {
            document.id(opts.body).inject(this.body);

        }

        this.slide = new Jx.Slide({
            target: this.body,
            trigger: this.header
        });

        if ($defined(opts.parent)) {
            this.domObj.inject(document.id(opts.parent));
        }

    }
});
