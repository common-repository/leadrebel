<?php


include_once('Leadrebel_LifeCycle.php');

class Leadrebel_Plugin extends Leadrebel_LifeCycle {

    /**
     * @return array of option meta data.
     */
    public function getOptionMetaData() {
        return array(
            'ATextInput' => array(__('Enter in some text')),
            'AmAwesome' => array(__('I like this awesome plugin'), 'false', 'true'),
            'CanDoSomething' => array(__('Which user role can do something'),
                                        'Administrator', 'Editor', 'Author', 'Contributor', 'Subscriber', 'Anyone')
        );
    }



    protected function initOptions() {
    }

    public function getPluginDisplayName() {
        return 'LeadRebel';
    }

    protected function getMainPluginFileName() {
        return 'leadrebel.php';
    }



    public function addActionsAndFilters() {
        // Add options administration page
        add_action('admin_menu', array(&$this, 'addSettingsSubMenuPage'));

        // Example adding a script & style just for the options administration page
                if (strpos($_SERVER['REQUEST_URI'], $this->getSettingsSlug()) !== false) {
                    wp_enqueue_script('leadrebel-script', plugins_url('/js/leadrebel.js', __FILE__), array('jquery'), true, true);
                    wp_enqueue_style('leadrebel-style', plugins_url('/css/leadrebel.css', __FILE__), true, true);
                }



    }


}
