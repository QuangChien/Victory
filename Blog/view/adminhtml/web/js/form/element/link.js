/**
 * @author QuangChien(Glorious Victory) <quangchien01.it@gmail.com>
 * @copyright Copyright © 2022 QuangChien(Glorious Victory) <https://www.facebook.com/quangchien01>. All rights reserved.
 */

define([
    'Magento_Ui/js/form/element/abstract'
], function (AbstractElement) {
    'use strict';

    return AbstractElement.extend({
        defaults: {
            elementTmpl: 'Victory_Blog/form/element/link'
        },

        initialize: function () {
            this._super();

            var value = this.value();
            this.url = value.url;
            this.title = value.title;
            this.text = value.text;
        },

    });
});
