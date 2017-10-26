<?php

// must be run within Dokuwiki
if (!defined('DOKU_INC')) {
    die();
}

class helper_plugin_sitemapnavi extends DokuWiki_Plugin {

    protected $baseNS;

    public function getSiteMap($baseNS)
    {
        global $conf, $INFO;
        $this->baseNS = $baseNS;

        $base = $conf['datadir'] . '/' . str_replace(':', '/', $baseNS);
        dbglog($base, __FILE__ . ': ' . __LINE__);

        $pages = array();
        $currentNS = utf8_encodeFN(str_replace(':', '/', $INFO['namespace']));
        search($pages, $base, 'search_index', array('ns' => $currentNS));

        return html_buildlist($pages, 'idx', [$this, 'listItemCallback'], [$this, 'liCallback']);
    }


    public function listItemCallback($item)
    {
        $fullId = $this->baseNS . ':' . $item['id'];

        $ret = '';
        $base = ':' . $fullId;
        $base = substr($base, strrpos($base, ':') + 1);

        if ($item['type'] === 'd') {
            // FS#2766, no need for search bots to follow namespace links in the index
            $ret .= '<button title="' . $fullId . '" class="plugin__sitemapnavi__dir" ><strong>';
            $ret .= $base;
            $ret .= '</strong></button>';
        } else {
            // default is noNSorNS($id), but we want noNS($id) when useheading is off FS#2605
            $ret .= html_wikilink($fullId, useHeading('navigation') ? null : noNS($fullId));
        }
        return $ret;
    }

    public function liCallback($item)
    {
        global $INFO;
        $currentClass = '';
        $adjustedItemID = str_replace('::', ':', $this->baseNS . ':' . $item['id']);
        if (strpos(':' . $INFO['id'], $adjustedItemID) === 0) {
            $currentClass = 'current';
        }
        dbglog($INFO, __FILE__ . ': ' . __LINE__);

        if ($item['type'] === 'f') {
            return '<li class="level' . $item['level'] . ' ' . $currentClass . '">';
        }
        if ($item['open']) {
            return '<li class="open ' . $currentClass . '">';
        }

        return '<li class="closed ' . $currentClass . '" data-ns="'.$adjustedItemID.'">';

    }
}
