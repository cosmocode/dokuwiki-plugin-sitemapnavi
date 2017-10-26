jQuery(function () {
    'use strict';

    jQuery(document).on('click', '#plugin__sitemapnavi button', function () {
        var $li = jQuery(this).closest('li');
        var liState = $li.hasClass('closed') ? 'closed' : 'open';
        var $sublist = $li.find('> ul');

        if (liState === 'open' || $sublist.length) {
            $sublist.slideToggle();
            $li.toggleClass('closed open');
            return;
        }

        jQuery.get(DOKU_BASE + 'lib/exe/ajax.php', {
            'call': 'plugin__sitemapnavi',
            'namespace': $li.data('ns')
        }).done(function (sublistHTML) {
            jQuery(sublistHTML).hide().appendTo($li).slideDown();
            $li.toggleClass('closed open');
        });
    });
});
