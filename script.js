jQuery(function () {
    'use strict';

    var $sitemapNavi = jQuery('#plugin__sitemapnavi');
    if ($sitemapNavi.length === 0) return;

    var $mediaToggle = jQuery(
        '<label><input type="checkbox">&nbsp;' +
        LANG.plugins.sitemapnavi.medialabel +
        '</label>'
    );

    $mediaToggle.find('input').prop('checked', !$sitemapNavi.hasClass('hide-media-links'));

    $mediaToggle.change(function (event) {
        $sitemapNavi.toggleClass('hide-media-links');
        DokuCookie.setValue('plugin_sitemapnavi_showmedia', event.target.checked);
    });

    // update initially rendered default state if it does not match the value stored in cookie
    if (
        typeof (DokuCookie.getValue('plugin_sitemapnavi_showmedia')) !== 'undefined' &&
        (DokuCookie.getValue('plugin_sitemapnavi_showmedia') === 'true') === $sitemapNavi.hasClass('hide-media-links')
    ) {
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
