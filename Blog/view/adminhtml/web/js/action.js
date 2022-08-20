/**
 * @author QuangChien(Glorious Victory) <quangchien01.it@gmail.com>
 * @copyright Copyright © 2022 QuangChien(Glorious Victory) <https://www.facebook.com/quangchien01>. All rights reserved.
 */

define([
    "jquery",
    'uiGridColumnsActions'
], function($, actions){
    'use strict';
    return actions.extend({
        defaults: {
            draggable: true
        }
    });
});
