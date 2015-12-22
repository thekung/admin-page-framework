<?php
/**
 Admin Page Framework v3.7.6b02 by Michael Uno
 Generated by PHP Class Files Script Generator <https://github.com/michaeluno/PHP-Class-Files-Script-Generator>
 <http://en.michaeluno.jp/admin-page-framework>
 Copyright (c) 2013-2015, Michael Uno; Licensed under MIT <http://opensource.org/licenses/MIT>
 */
class AdminPageFramework_View__PageRenderer extends AdminPageFramework_FrameworkUtility {
    public $oFactory;
    public $sPageSlug;
    public $sTabSlug;
    public $aCSSRules = array();
    public $aScripts = array();
    public function __construct($oFactory, $sPageSlug, $sTabSlug) {
        $this->oFactory = $oFactory;
        $this->sPageSlug = $sPageSlug;
        $this->sTabSlug = $sTabSlug;
    }
    public function render() {
        $_sPageSlug = $this->sPageSlug;
        $_sTabSlug = $this->sTabSlug;
        $this->addAndDoActions($this->oFactory, $this->getFilterArrayByPrefix('do_before_', $this->oFactory->oProp->sClassName, $_sPageSlug, $_sTabSlug, true), $this->oFactory); ?>
        <div class="<?php echo esc_attr($this->oFactory->oProp->sWrapperClassAttribute); ?>">
            <?php echo $this->_getContentTop(); ?>
            <div class="admin-page-framework-container">    
                <?php
        $this->addAndDoActions($this->oFactory, $this->getFilterArrayByPrefix('do_form_', $this->oFactory->oProp->sClassName, $_sPageSlug, $_sTabSlug, true), $this->oFactory);
        $this->_printFormOpeningTag($this->oFactory->oProp->bEnableForm); ?>
                <div id="poststuff">
                    <div id="post-body" class="metabox-holder columns-<?php echo $this->_getNumberOfColumns(); ?>">
                    <?php
        $this->_printMainPageContent($_sPageSlug, $_sTabSlug);
        $this->_printPageMetaBoxes(); ?>     
                    </div><!-- #post-body -->    
                </div><!-- #poststuff -->
                
            <?php echo $this->_printFormClosingTag($_sPageSlug, $_sTabSlug, $this->oFactory->oProp->bEnableForm); ?>
            </div><!-- .admin-page-framework-container -->
                
            <?php echo $this->addAndApplyFilters($this->oFactory, $this->getFilterArrayByPrefix('content_bottom_', $this->oFactory->oProp->sClassName, $_sPageSlug, $_sTabSlug, false), ''); ?>
        </div><!-- .wrap -->
        <?php
        $this->addAndDoActions($this->oFactory, $this->getFilterArrayByPrefix('do_after_', $this->oFactory->oProp->sClassName, $_sPageSlug, $_sTabSlug, true), $this->oFactory);
    }
    private function _getNumberOfColumns() {
        if (!$this->doesMetaBoxExist('side')) {
            return 1;
        }
        $_iColumns = $this->getNumberOfScreenColumns();
        return $_iColumns ? $_iColumns : 1;
    }
    private function _getContentTop() {
        $_oScreenIcon = new AdminPageFramework_View__PageRenderer__ScreenIcon($this->oFactory, $this->sPageSlug, $this->sTabSlug);
        $_sContentTop = $_oScreenIcon->get();
        $_oPageHeadingTabs = new AdminPageFramework_View__PageRenderer__PageHeadingTabs($this->oFactory, $this->sPageSlug);
        $_sContentTop.= $_oPageHeadingTabs->get();
        $_oInPageTabs = new AdminPageFramework_View__PageRenderer__InPageTabs($this->oFactory, $this->sPageSlug);
        $_sContentTop.= $_oInPageTabs->get();
        return $this->addAndApplyFilters($this->oFactory, $this->getFilterArrayByPrefix('content_top_', $this->oFactory->oProp->sClassName, $this->sPageSlug, $this->sTabSlug, false), $_sContentTop);
    }
    private function _printMainPageContent($sPageSlug, $sTabSlug) {
        $_bSideMetaboxExists = $this->doesMetaBoxExist('side');
        echo "<!-- main admin page content -->";
        echo "<div class='admin-page-framework-content'>";
        if ($_bSideMetaboxExists) {
            echo "<div id='post-body-content'>";
        }
        echo $this->addAndApplyFilters($this->oFactory, $this->getFilterArrayByPrefix('content_', $this->oFactory->oProp->sClassName, $sPageSlug, $sTabSlug, false), $this->oFactory->content($this->_getFormOutput($sPageSlug)));
        $this->addAndDoActions($this->oFactory, $this->getFilterArrayByPrefix('do_', $this->oFactory->oProp->sClassName, $sPageSlug, $sTabSlug, true), $this->oFactory);
        if ($_bSideMetaboxExists) {
            echo "</div><!-- #post-body-content -->";
        }
        echo "</div><!-- .admin-page-framework-content -->";
    }
    private function _getFormOutput($sPageSlug) {
        if (!$this->oFactory->oProp->bEnableForm) {
            return '';
        }
        return $this->oFactory->oForm->get();
    }
    private function _printPageMetaBoxes() {
        $_oPageMetaBoxRenderer = new AdminPageFramework_View__PageMataBoxRenderer();
        $_oPageMetaBoxRenderer->render('side');
        $_oPageMetaBoxRenderer->render('normal');
        $_oPageMetaBoxRenderer->render('advanced');
    }
    private function _printFormOpeningTag($fEnableForm = true) {
        if (!$fEnableForm) {
            return;
        }
        echo "<form " . $this->getAttributes(array('method' => 'post', 'enctype' => $this->oFactory->oProp->sFormEncType, 'id' => 'admin-page-framework-form', 'action' => wp_unslash(remove_query_arg('settings-updated', $this->oFactory->oProp->sTargetFormPage)),)) . " >" . PHP_EOL;
        echo "<input type='hidden' name='admin_page_framework_start' value='1' />" . PHP_EOL;
        settings_fields($this->oFactory->oProp->sOptionKey);
    }
    private function _printFormClosingTag($sPageSlug, $sTabSlug, $fEnableForm = true) {
        if (!$fEnableForm) {
            return;
        }
        $_sNonceTransientKey = 'form_' . md5($this->oFactory->oProp->sClassName . get_current_user_id());
        $_sNonce = $this->getTransient($_sNonceTransientKey, '_admin_page_framework_form_nonce_' . uniqid());
        $this->setTransient($_sNonceTransientKey, $_sNonce, 60 * 60);
        echo "<input type='hidden' name='page_slug' value='{$sPageSlug}' />" . PHP_EOL . "<input type='hidden' name='tab_slug' value='{$sTabSlug}' />" . PHP_EOL . "<input type='hidden' name='_is_admin_page_framework' value='{$_sNonce}' />" . PHP_EOL . "</form><!-- End Form -->" . PHP_EOL;
    }
}