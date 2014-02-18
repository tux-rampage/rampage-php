/**
 * This is part of rampage.php
 * Copyright (c) 2014 Axel Helmert
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author    Axel Helmert
 * @copyright Copyright (c) 2014 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

(function($){
    var toasts = [];
    var currentToast = null;

    /* Private */
    function _nextToast() {
        if (currentToast) {
            return;
        }

        currentToast = toasts.shift();

        if (currentToast) {
            currentToast.show();
        }
    }

    // Update toast position on resize
    $(window).resize(function() {
        if (currentToast) {
            currentToast.updateSize();
        }
    });

    function Toast(content, options)
    {
        this.dismissed = false;
        this.content = content;

        this.options = $.extend({
            displayTime: $.toast.defaults.options.displayTime,
            contentElement: null,
            additionalClass: null
        }, options || {});

        if (!this.options.displayTime || isNaN(this.options.displayTime) || (this.options.displayTime < 0)) {
            this.options.displayTime = 5;
        }
    };

    Toast.prototype = {
        show: function() {
            this.element = document.createElement('div');
            $(this.element).addClass('toast').css('display', 'none');
            $(this.element).append(this.content);

            if (this.options.additionalClass) {
                $(this.element).addClass(this.options.additionalClass);
            }

            var self = this;
            $(this.element).click(function(event) {
                self.dismiss(event, true);
            });

            $('body').append(this.element);

            this.updateSize();
            $(this.element).css({ 'position': 'fixed' })
                .fadeIn(400);

            this.timeout = window.setTimeout(function() { self.dismiss(null, false); }, this.options.displayTime * 1000);
        },

        updateSize: function()
        {
            var element = $(this.element);
            var viewport = $(window);

            var vpH = viewport.height();
            var elH = element.height();
            var top = 0;
            var margin = ($.type($.toast.margin) == 'function')? $.toast.margin() : $.toast.margin;

            switch ($.toast.position) {
                case 'top':
                    top = margin;
                    if ((elH + top) > vpH) {
                        top = $.toast.minTopMargin;
                    }

                    break;

                case 'bottom': // break intentionally omitted
                default:
                    top = (vpH - (elH + margin));
                    break;
            }

            if (top < $.toast.minTopMargin) {
                top = $.toast.minTopMargin;
            }

            var style = {
                'left': ((viewport.width() - element.width()) / 2) + 'px',
                'top': top + 'px'
            };

            console.info(style);
            element.css(style);
        },

        dismiss: function(event, clearTimeout) {
            if (this.dismissed) {
                return;
            }

            this.dismissed = true;
            if (clearTimeout) {
                window.clearTimeout(this.timeout);
            }

            $(this.element).fadeOut(200, function() {
                this.remove();
                currentToast = null;
                _nextToast();
            });

            this.element = null;
        }
    };

    $.toast = function(message, options) {
        var toast = new Toast(message, options);

        toasts.push(toast);
        _nextToast();
    };

    $.toast.margin = function() {
        var vpH = $(window).height();
        console.log('Viewport: ' + vpH);

        if (vpH < 500) {
            return 40;
        } else if (vpH < 800) {
            return 100;
        }

        return 200;
    };

    $.toast.minTopMargin = 10;
    $.toast.position = 'bottom';
    $.toast.defaults = { options: {
        displayTime: 5
    }};

    $.fn.extend({
        toast: function(options) {
            $(this).detach();
            $.toast(this, options);
        }
    });
})(jQuery);
