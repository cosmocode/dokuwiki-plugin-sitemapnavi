<?php
/**
 * DokuWiki Plugin sitemapnavi (Syntax Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Michael Große <grosse@cosmocode.de>
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) {
    die();
}

class syntax_plugin_sitemapnavi extends DokuWiki_Syntax_Plugin
{
    /**
     * @return string Syntax mode type
     */
    public function getType()
    {
        return 'substition';
    }

    /**
     * @return string Paragraph type
     */
    public function getPType()
    {
        return 'block';
    }

    /**
     * @return int Sort order - Low numbers go before high numbers
     */
    public function getSort()
    {
        return 99;
    }

    /**
     * Connect lookup pattern to lexer.
     *
     * @param string $mode Parser mode
     */
    public function connectTo($mode)
    {
        $this->Lexer->addSpecialPattern('{{sitemapnavi}}', $mode, 'plugin_sitemapnavi');
    }

    /**
     * Handle matches of the sitemapnavi syntax
     *
     * @param string $match The match of the syntax
     * @param int $state The state of the handler
     * @param int $pos The position in the document
     * @param Doku_Handler $handler The handler
     * @return array Data for the renderer
     */
    public function handle($match, $state, $pos, Doku_Handler $handler)
    {
        $data = array();

        return $data;
    }

    /**
     * Render xhtml output or metadata
     *
     * @param string $mode Renderer mode (supported modes: xhtml)
     * @param Doku_Renderer $renderer The renderer
     * @param array $data The data from the handler() function
     * @return bool If rendering was successful.
     */
    public function render($mode, Doku_Renderer $renderer, $data)
    {
        if ($mode !== 'xhtml') {
            return false;
        }

        global $conf, $INFO;
        $renderer->info['cache'] = false;

        /** @var helper_plugin_sitemapnavi $helper */
        $helper = $this->loadHelper('sitemapnavi');
        $listHtml = $helper->getSiteMap(':');

        $divClass = empty($this->getConf('showMedia')) ? 'hide-media-links' : '';
        $renderer->doc .= '<div id="plugin__sitemapnavi" class="' . $divClass . '">';
        $renderer->doc .= $listHtml; //html_buildlist($pages, 'idx', [$this, 'listItemCallback'], [$this, 'liCallback']);
        $renderer->doc .= '</div>';

        return true;
    }
}

// vim:ts=4:sw=4:et:
