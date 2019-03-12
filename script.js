jQuery(function () {
    'use strict';

    var $sitemapNavi = jQuery('#plugin__sitemapnavi');
    if ($sitemapNavi.length === 0) return;

    var $mediaToggle = jQuery(
        '<label><input type="checkbox" checked="checked">&nbsp;' +
        LANG.plugins.sitemapnavi.medialabel +
        '</label>'
    );
    $mediaToggle.change(function () {
        $sitemapNavi.find('li.media').toggle($mediaToggle.checked);
    });
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
