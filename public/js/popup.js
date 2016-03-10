(function (window, document) {
    "use strict";

    var popupOverlay = $('<div id="popupOverlay"></div>');

    var openPopup = null;
    function closeIfOpen() {
        openPopup && openPopup.hide();
    }
    popupOverlay.click(closeIfOpen);

    document.addEventListener('keydown', function (event) {
        event.keyCode === 27 && closeIfOpen();
    });

    $(document).ready(function () {
        $(document.body).append(popupOverlay);
    });

    $.fn.popup = function (options) {
        if (!this.length) {
            return null;
        }
        var elem = this[0];
        var popup = $('<div class="popup"></div>');
        popup.click(function (event) {
            event.stopPropagation();
        });
        popup.append(elem);
        popupOverlay.append(popup);
        var origTop = popup.css('top');
        return {
            'show': function () {
                if (openPopup !== null) {
                    throw "Cannot open more than one popup at a time";
                }
                popupOverlay.show();
                popup.show();
                popup.css({
                    'marginLeft': (popup.width() / -2) + 'px', // ensure centered
                    'top': origTop
                });
                openPopup = this;
                if (options.open) {
                    options.open.call(elem);
                }
            },
            'hide': function () {
                popup.css('top', '0px');
                popupOverlay.hide();
                popup.hide();
                openPopup = null;
                if (options.close) {
                    options.close.call(elem);
                }
            },
            'element': this
        };
    };

}) (window, document);
