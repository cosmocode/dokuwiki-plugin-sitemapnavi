<?php

// must be run within Dokuwiki
if (!defined('DOKU_INC')) {
    die();
}

class helper_plugin_sitemapnavi extends DokuWiki_Plugin {

    public function getSiteMap($baseNS)
    {
        global $conf, $INFO;

        $subdir = trim(str_replace(':', '/', $baseNS),'/');
        $level = $this->getNumberOfSubnamespaces($baseNS) + 1;

        $pages = array();
        $currentNS = utf8_encodeFN(str_replace(':', '/', $INFO['namespace']));
        search($pages, $conf['datadir'], 'search_index', array('ns' => $currentNS), $subdir, $level);
        $media = array();
        search($media, $conf['mediadir'], [$this, 'searchMediaIndex'], array('ns' => $currentNS, 'depth' => 1, 'showmsg'=>false), str_replace(':', '/', $baseNS));
        $media = array_map(function($mediaFile) {
            $cleanedNamespace = trim(getNS($mediaFile['id']), ':');
            if ($cleanedNamespace === '') {
                $mediaFile['level'] = 1;
            } else {
                $mediaFile['level'] = count(explode(':', $cleanedNamespace)) + 1;
            }
            return $mediaFile;
        }, $media);
        $items = $this->mergePagesAndMedia($pages, $media);
        $items = $this->sortMediaAfterPages($items);

        $html =  html_buildlist($items, 'idx', [$this, 'listItemCallback'], [$this, 'liCallback'], true);
        return $html;
    }

    /**
     * Calculate the number of subnamespaces, the given namespace is consisting of
     *
     * @param string $namespace
     * @return int
     */
    protected function getNumberOfSubnamespaces($namespace) {
        $cleanedNamespace = trim($namespace, ':');
        if ($cleanedNamespace === '') {
            return 0;
        }
        return substr_count($cleanedNamespace, ':') + 1;
    }

    /**
     * A stable sort, that moves media entries after the pages in the same namespace
     *
     * @param array $items list of items to be sorted, consisting both of directories, pages and media
     * @return array
     */
    protected function sortMediaAfterPages(array $items) {
        $numberOfItems = count($items);

        if (empty($items)) {
            return $items;
        }
        $count = 0;
        $hasChanged = false;
        $isUnsorted = true;
        while($isUnsorted) {
            $item1 = $items[$count];
            $item2 = $items[$count + 1];
            if ($this->compareMediaPages($item1, $item2) === 1) {
                $temp = $item1;
                $items[$count] = $item2;
                $items[$count + 1] = $temp;
                $hasChanged = true;
            }
            $count++;
            if ($count === $numberOfItems) {
                if ($hasChanged) {
                    $count = 0;
                    $hasChanged = false;
                    continue;
                }
                $isUnsorted = false;
            }
        }

        return $items;
    }

    /**
     * "compare" media items to pages and directories
     *
     * Considers media items to be "larger" than pages and directories if those are in the same namespace or a subnamespace
     * Considers media items to be "larger" than other media items if those are in a subnamespace
     *
     * @param $item1
     * @param $item2
     * @return int
     */
    protected function compareMediaPages($item1, $item2) {
        $item1IsMedia = !isset($item1['type']);
        $item2IsMedia = !isset($item2['type']);
        if ($item1IsMedia) {
            $nameSpaceDifference = $this->namespaceDifference($item1['id'], $item2['id']);
            if ($nameSpaceDifference > 0) {
                return 1;
            }
            if ($nameSpaceDifference === 0 && !$item2IsMedia) {
                return 1;
            }
        }
        return -1;
    }

    /**
     * Calculate how far $id2 is in the namespace of $id1
     *
     * If $id2 is not in the same namespace or a subnamespace of $id1 return false
     * If they are in the same namespace return 0
     * If $id2 is in a subnamespace to the namespace of $id1, return the relative number of subnamespaces
     *
     * @param $id1
     * @param $id2
     * @return bool|int
     */
    protected function namespaceDifference($id1, $id2) {
        $nslist1 = explode(':', getNS($id1));
        $nslist2 = explode(':', getNS($id2));
        if (empty($nslist1)) {
            return count($nslist2);
        }
        $NS1depth = count($nslist1);
        for ($i = 0; $i < $NS1depth; $i += 1) {
            if (empty($nslist2[$i]) || $nslist1[$i] !== $nslist2[$i]) {
                // not in our namespace
                return false;
            }
        }
        return (count($nslist2) - count($nslist1));
    }

    /**
     * Merge media items into an flat ordered list of index items, after their respecitve directories
     *
     * @param array $pages
     * @param array $mediaFiles
     * @return array
     */
    protected function mergePagesAndMedia(array $pages, array $mediaFiles) {
        $items = [];
        $unhandledMediaFiles = $mediaFiles;
        foreach ($pages as $page) {
            if ($page['type'] === 'f') {
                $items[] = $page;
                continue;
            }
            $items[] = $page;
            $currentMediaFiles = $unhandledMediaFiles;
            $unhandledMediaFiles = [];
            foreach ($currentMediaFiles as $mediaFile) {
                $mediafileNamespace = getNs($mediaFile['id']);
                if ($page['id'] === $mediafileNamespace) {
                    $items[] = $mediaFile;
                    continue;
                }
                $unhandledMediaFiles[] = $mediaFile;
            }
        }
        $items = array_merge($items, $unhandledMediaFiles);
        return $items;
    }

    /**
     * Wrapper for search_media, that descends only towards the current directory
     *
     * @see search_media
     *
     * @param $data
     * @param $base
     * @param $file
     * @param $type
     * @param $lvl
     * @param $opts
     * @return bool
     */
    public function searchMediaIndex(&$data,$base,$file,$type,$lvl,$opts) {
        if($type === 'd') {
            if (strpos($opts['ns'] . '/', trim($file,'/') . '/') === 0) {
                return true;
            }
        }
        return search_media($data,$base,$file,$type,$lvl,$opts);
    }


    public function listItemCallback($item)
    {
        $fullId = cleanID($item['id']);

        $ret = '';
        $fullId = ':' . $fullId;
        $base = substr($fullId, strrpos($fullId, ':') + 1);

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
        $adjustedItemID = str_replace('::', ':', ':' . $item['id']);
        if (strpos(':' . $INFO['id'] . ':', $adjustedItemID . ':') === 0) {
            $currentClass = 'current';
        }

        if (!isset($item['type'])) {
            return '<li class="level' . $item['level'] . ' media">';
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
