<?php
/**
 Admin Page Framework v3.7.6b02 by Michael Uno
 Generated by PHP Class Files Script Generator <https://github.com/michaeluno/PHP-Class-Files-Script-Generator>
 <http://en.michaeluno.jp/admin-page-framework>
 Copyright (c) 2013-2015, Michael Uno; Licensed under MIT <http://opensource.org/licenses/MIT>
 */
abstract class AdminPageFramework_Widget_Model extends AdminPageFramework_Widget_Router {
    function __construct($oProp) {
        parent::__construct($oProp);
        if (did_action('widgets_init')) {
            add_action("set_up_{$this->oProp->sClassName}", array($this, '_replyToRegisterWidget'), 20);
        } else {
            add_action('widgets_init', array($this, '_replyToRegisterWidget'), 20);
        }
        if ($this->oProp->bIsAdmin) {
            add_filter('validation_' . $this->oProp->sClassName, array($this, '_replyToSortInputs'), 1, 3);
        }
    }
    public function _replyToSortInputs($aSubmittedFormData, $aStoredFormData, $oFactory) {
        return $this->oForm->getSortedInputs($aSubmittedFormData);
    }
    public function _replyToHandleSubmittedFormData($aSavedData, $aArguments, $aSectionsets, $aFieldsets) {
        if (empty($aSectionsets) || empty($aFieldsets)) {
            return;
        }
        $this->oResource;
    }
    public function _replyToRegisterWidget() {
        global $wp_widget_factory;
        if (!is_object($wp_widget_factory)) {
            return;
        }
        $wp_widget_factory->widgets[$this->oProp->sClassName] = new AdminPageFramework_Widget_Factory($this, $this->oProp->sWidgetTitle, $this->oUtil->getAsArray($this->oProp->aWidgetArguments));
        $this->oProp->oWidget = $wp_widget_factory->widgets[$this->oProp->sClassName];
    }
}