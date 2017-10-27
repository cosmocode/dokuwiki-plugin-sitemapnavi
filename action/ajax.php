<?php

// must be run within Dokuwiki
if (!defined('DOKU_INC')) {
    die();
}

class action_plugin_sitemapnavi_ajax extends DokuWiki_Action_Plugin
{
    /**
     * Registers a callback function for a given event
     *
     * @param Doku_Event_Handler $controller DokuWiki's event controller object
     * @return void
     */
    public function register(Doku_Event_Handler $controller)
    {
        $controller->register_hook('AJAX_CALL_UNKNOWN', 'BEFORE', $this, 'handle_ajax');
        $controller->register_hook('DOKUWIKI_STARTED', 'BEFORE', $this, 'markAsAvailableInJSINFO');
    }

    /**
     * Let js know that this plugin exists
     */
    public function markAsAvailableInJSINFO() {
        global $JSINFO;
        if (empty($JSINFO['plugins'])) {
            $JSINFO['plugins'] = [];
        }
        $JSINFO['plugins']['sitemapnavi'] = true;
    }

    /**
     * Pass Ajax call to a type
     *
     * @param Doku_Event $event event object by reference
     * @param mixed $param [the parameters passed as fifth argument to register_hook() when this
     *                           handler was registered]
     */
    public function handle_ajax(Doku_Event $event, $param)
    {
        if ($event->data !== 'plugin__sitemapnavi') {
            return;
        }
        $event->preventDefault();
        $event->stopPropagation();

        // get namespace
        global $INPUT, $INFO;

        if (empty($INFO)) {
            $INFO = [
                'id' => getID(),
                'namespace' => getNS(getID())
            ];
        }
        $ns = $INPUT->str('namespace');

        /** @var helper_plugin_sitemapnavi $helper */
        $helper = $this->loadHelper('sitemapnavi');
        echo $helper->getSiteMap($ns);
    }
}
