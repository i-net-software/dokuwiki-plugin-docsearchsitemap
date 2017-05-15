<?php
/**
 * Script to put indexed documents into the sitemap Plugin
 * 
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     i-net software <tools@inetsoftware.de>
 * @author     Gerry Weissbach <gweissbach@inetsoftware.de>
 */

if(!defined('DOKU_INC')) die();
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
if(!defined('DOKU_DATA')) define('DOKU_DATA',DOKU_INC.'data/');

require_once(DOKU_PLUGIN.'action.php');
require_once(DOKU_INC . 'inc/fulltext.php');

class action_plugin_docsearchsitemap extends DokuWiki_Action_Plugin {

    private $data = array();

    /**
     * Register to the content display event to place the results under it.
     */
    function register(Doku_Event_Handler $controller) {
        $controller->register_hook('SITEMAP_GENERATE', 'BEFORE', $this, 'runSitemapper', array());
    }

    /**
     * Builds a Google Sitemap of all public documents known to the indexer
     *
     * The map is placed in the root directory named sitemap.xml.gz - This
     * file needs to be writable!
     *
     * @autohr Gerry Weissbach
     * @link   https://www.google.com/webmasters/sitemaps/docs/en/about.html
     */
    function runSitemapper(&$event, $param){
        global $conf;
        
        // backup the config array
        $cp = $conf;

        // change index/pages folder for DocSearch
        $conf['indexdir'] = init_path($conf['savedir'] . '/docsearch/index');
        $conf['datadir'] = init_path($conf['savedir'] . '/docsearch/pages');
        
        $pages = idx_get_indexer()->getPages();

        // build the sitemap
        foreach($pages as $id){
        
            //skip hidden, non existing and restricted files
            if(isHiddenPage($id)) continue;
            if(auth_aclcheck($id,'','') < AUTH_READ) continue;
    
            
            // $item = SitemapItem::createFromID($id);
            $id = trim($id);
            $date = @filemtime(mediaFN($id));
            if(!$date) continue;
            $item = new SitemapItem(ml($id, '', true, '', true), $date, $changefreq, $priority);

            if ($item !== null) {
                $event->data['items'][] = $item;
            }
        }

        $conf = $cp;
        return true;
    }
}

?>
