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

        $items = array();
        $currentNS = utf8_encodeFN(str_replace(':', '/', $INFO['namespace']));
        search($items, $base, 'search_index', array('ns' => $currentNS));
        search($items, $conf['mediadir'], 'search_media', array('depth' => 1, 'showmsg'=>false), str_replace(':', '/', $baseNS));

        return html_buildlist($items, 'idx', [$this, 'listItemCallback'], [$this, 'liCallback']);
    }


    public function listItemCallback($item)
    {
        $fullId = cleanID($this->baseNS . ':' . $item['id']);

        $ret = '';
        $base = ':' . $fullId;
        $base = substr($base, strrpos($base, ':') + 1);

        if ($item['type'] === 'd') {
            // FS#2766, no need for search bots to follow namespace links in the index
            $ret .= '<button title="' . $fullId . '" class="plugin__sitemapnavi__dir" ><strong>';
            $ret .= $base;
            $ret .= '</strong></button>';
        } elseif ($item['type'] === 'f') {
            // default is noNSorNS($id), but we want noNS($id) when useheading is off FS#2605
            $ret .= html_wikilink($fullId, useHeading('navigation') ? null : noNS($fullId));
        } else {
            list($ext) = mimetype($item['file'],false);
            $class = "mf_$ext media mediafile";
            $ret .= '<a class="'.$class.'" href="'.ml($item['id']).'" target="_blank">' . $item['file'] . '</a>';
        }
        return $ret;
    }

    public function liCallback($item)
    {
        global $INFO;
        $currentClass = '';
        $adjustedItemID = str_replace('::', ':', $this->baseNS . ':' . $item['id']);
        if (strpos(':' . $INFO['id'] . ':', $adjustedItemID . ':') === 0) {
            $currentClass = 'current';
        }

        if (!isset($item['type'])) {
            return '<li class="media">';
        }
        if ($item['type'] === 'f') {
            return '<li class="level' . $item['level'] . ' ' . $currentClass . '">';
        }
        if ($item['open']) {
            return '<li class="open ' . $currentClass . '">';
        }

        return '<li class="closed ' . $currentClass . '" data-ns="'.$adjustedItemID.'">';

    }
}
