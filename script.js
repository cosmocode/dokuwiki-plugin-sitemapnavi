jQuery(function () {
    'use strict';

    var $sitemapNavi = jQuery('#plugin__sitemapnavi');
    if ($sitemapNavi.length === 0) return;
    var confShowMediaLinks = !$sitemapNavi.hasClass('hide-media-links');

    var cookieShowMedia = function () {
        DokuCookie.setValue('plugin_sitemapnavi_showmedia', 'checked');
    };
    var cookieHideMedia = function () {
        DokuCookie.setValue('plugin_sitemapnavi_showmedia', '');
    };

    var setDefaultCookieShowMedia = function () {
        if (confShowMediaLinks) {
            cookieShowMedia();
        } else {
            cookieHideMedia();
        }
    };
    var getCookieShowMedia = function () {
        return DokuCookie.getValue('plugin_sitemapnavi_showmedia');
    };
    var flipCookieShowMedia = function () {
        if (getCookieShowMedia().length === 0) {
           cookieShowMedia();
        } else {
            cookieHideMedia();
        }
    };

    var checkedAttr = confShowMediaLinks ? 'checked="checked"' : '';
    var $mediaToggle = jQuery(
        '<label><input type="checkbox" ' + checkedAttr + '>&nbsp;' +
        LANG.plugins.sitemapnavi.medialabel +
        '</label>'
    );

    $mediaToggle.change(function () {
        $sitemapNavi.toggleClass('hide-media-links');
        flipCookieShowMedia();
    });

    // if there is no state in cookie, set default state
    if ((typeof(getCookieShowMedia()) === 'undefined')) {
        setDefaultCookieShowMedia();
    }

    // update initially rendered  default state if it does not match the value stored in cookie
    if ((getCookieShowMedia().length === 0) !== $sitemapNavi.hasClass('hide-media-links')) {
        $mediaToggle.find('input').prop('checked', !$mediaToggle.find('input').prop('checked'));
        $sitemapNavi.toggleClass('hide-media-links');
    }

    $sitemapNavi.prepend($mediaToggle);

    jQuery(document).on('click', '#plugin__sitemapnavi button', function () {
        var $li = jQuery(this).closest('li');
        var liState = $li.hasClass('closed') ? 'closed' : 'open';
        var $sublist = $li.find('> ul');

        if (liState === 'open' || $sublist.length) {
            $li.toggleClass('closed open');
            $sublist.find('li.media').toggle($mediaToggle.checked);
            $sublist.slideToggle(150);
            return;
        }

        jQuery.get(DOKU_BASE + 'lib/exe/ajax.php', {
            'call': 'plugin__sitemapnavi',
            'namespace': $li.data('ns')
        }).done(function (sublistHTML) {
            $li.toggleClass('closed open');
            jQuery(sublistHTML).hide().appendTo($li).slideDown(150);
        });
    });


});
