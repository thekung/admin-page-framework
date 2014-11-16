<?php
/**
 * Admin Page Framework
 * 
 * http://en.michaeluno.jp/admin-page-framework/
 * Copyright (c) 2013-2014 Michael Uno; Licensed MIT
 * 
 */
if ( ! class_exists( 'AdminPageFramework_FormTable' ) ) :
/**
 * Provides methods to render setting sections and fields.
 * 
 * @package     AdminPageFramework
 * @subpackage  Form
 * @since       3.0.0
 * @internal
 */
class AdminPageFramework_FormTable extends AdminPageFramework_FormTable_Row {
        
    /**
     * Returns a set of HTML table outputs consisting of form sections and fields.
     * 
     * Currently there are mainly two types of structures.
     * 1. Normal Sections - Vertically arranged sections. They can be repeatable.
     * <code>
     *  <div class="admin-page-framework-sectionset">
     *      <div class="admin-page-framework-sections">
     *          <div class="admin-page-framework-section">
     *              <table class="form-table">
     *                  <caption>       
     *                      <div class="admin-page-framework-section-title">...</div>
     *                      <div class="admin-page-framework-section-description">...</div>
     *                  </caption>
     *                  <tbody>
     *                      <tr>a field goes here.</tr>
     *                      <tr>a field goes here.</tr>
     *                      <tr>a field goes here.</tr>
     *                  </tbody>
     *              </table>
     *          </div>
     *          <div class="admin-page-framework-section">
     *              if repeatable sections, this container is repeated
     *          </div>
     *      </div>
     *  </div>
     * </code>
     * 2. Tabbed Sections - Horizontally arranged grouped sections. They can be repeatable.
     * <code>
     *  <div class="admin-page-framework-sectionset">
     *      <div class="admin-page-framework-sections">
     *          <ul class="admin-page-framework-section-tabs">
     *              <li> ... </li>
     *              <li> ... </li>
     *          </ul>
     *          <div class="admin-page-framework-section">
     *              <table class="form-table">
     *                  <caption>       
     *                      <div class="admin-page-framework-section-title">...</div>
     *                      <div class="admin-page-framework-section-description">...</div>
     *                  </caption>
     *                  <tbody>
     *                      <tr>a field goes here.</tr>
     *                      <tr>a field goes here.</tr>
     *                      <tr>a field goes here.</tr>
     *                  </tbody>
     *              </table>
     *          </div>
     *          <div class="admin-page-framework-section">
     *              if repeatable sections, this container is repeated
     *          </div>
     *      </div>
     *  </div>
     * </code>
     * @since 3.0.0
     */
    public function getFormTables( $aSections, $aFieldsInSections, $hfSectionCallback, $hfFieldCallback ) {
        
        $_aOutput = array();
        foreach( $this->_getSectionsBySectionTabs( $aSections ) as $_sSectionTabSlug => $_aSections ) {
            
            $_sSectionSet = $this->_getFormTablesBySectionTab( $_sSectionTabSlug, $_aSections, $aFieldsInSections, $hfSectionCallback, $hfFieldCallback );
            if ( $_sSectionSet ) {
                $_aOutput[] = "<div " . $this->generateAttributes(
                        array(
                            'class' => 'admin-page-framework-sectionset',
                            'id'    => "sectionset-{$_sSectionTabSlug}_" . md5( serialize( $_aSections ) ),
                        ) 
                    ) . ">" 
                        . $_sSectionSet
                    . "</div>";
            }
            
        }
    
        return implode( PHP_EOL, $_aOutput ) 
            . $this->_getSectionTabsEnablerScript()
            . ( defined( 'WP_DEBUG' ) && WP_DEBUG && in_array( $this->_getSectionsFieldsType( $aSections ), array( 'widget', 'post_meta_box', 'page_meta_box', ) )
                ? "<div class='admin-page-framework-info'>" 
                        . 'Debug Info: ' . AdminPageFramework_Registry::Name . ' '. AdminPageFramework_Registry::getVersion() 
                    . "</div>"
                : ''
            );
            
    }
    
        /**
         * Returns the fields type of the given sections.
         * 
         * @since   3.3.3
         */
        private function _getSectionsFieldsType( array $aSections=array() ) {
            // Only the first iteration item is needed
            foreach( $aSections as $_aSection ) {
                return $_aSection['_fields_type'];
            }
        }
                
        /**
         * Returns an output string of form tables.
         * 
         * @since       3.0.0
         */
        private function _getFormTablesBySectionTab( $sSectionTabSlug, $aSections, $aFieldsInSections, $hfSectionCallback, $hfFieldCallback ) {

            // if empty, return a blank string.
            if ( empty( $aSections ) ) { return ''; } 
        
            /* <ul>
                <li><a href="#tabs-1">Nunc tincidunt</a></li>
                <li><a href="#tabs-2">Proin dolor</a></li>
                <li><a href="#tabs-3">Aenean lacinia</a></li>
            </ul>  */     
            $_aSectionTabList   = array();
            $_abCollapsible     = null;
            $_aOutput           = array();
            
            foreach( $aFieldsInSections as $_sSectionID => $aSubSectionsOrFields ) {
                
                if ( ! isset( $aSections[ $_sSectionID ] ) ) { continue; }
                
                $_sSectionTabSlug   = $aSections[ $_sSectionID ]['section_tab_slug']; // will be referred outside the loop.
             
                // Update the collapsible argument.
                $_abCollapsible         = isset( $_abCollapsible )
                    ? $_abCollapsible
                    : $aSections[ $_sSectionID ]['collapsible'];

                // For repeatable sections
                $_aSubSections      = $aSubSectionsOrFields;
                $_aSubSections      = $this->getIntegerElements( $_aSubSections );
                $_iCountSubSections = count( $_aSubSections ); // Check sub-sections.
                if ( $_iCountSubSections ) {

                    // Add the repeatable sections enabler script.
                    if ( $aSections[ $_sSectionID ]['repeatable'] ) {
                        $_aOutput[] = $this->_getRepeatableSectionsEnablerScript( 'sections-' .  md5( serialize( $aSections ) ), $_iCountSubSections, $aSections[ $_sSectionID ]['repeatable'] );    
                    }
                    
                    // Get the section tables.
                    foreach( $this->numerizeElements( $_aSubSections ) as $_iIndex => $_aFields ) { // will include the main section as well.
                                  
                        // For tabbed sections,
                        if ( $aSections[ $_sSectionID ]['section_tab_slug'] ) {
                            $_aSectionTabList[] = $this->_getTabList( $_sSectionID, $_iIndex, $aSections[ $_sSectionID ], $_aFields, $hfFieldCallback );
                        }
                    
                        $_aOutput[] = $this->getFormTable( $_sSectionID, $_iIndex, $aSections[ $_sSectionID ], $_aFields, $hfSectionCallback, $hfFieldCallback );
                        
                    }
                    continue;
                } 
                // The normal section
                $_aFields       = $aSubSectionsOrFields;
                
                // For tabbed sections,
                if ( $aSections[ $_sSectionID ]['section_tab_slug'] ) {
                    $_aSectionTabList[] = $this->_getTabList( $_sSectionID, 0, $aSections[ $_sSectionID ], $_aFields, $hfFieldCallback );
                }
                
                $_aOutput[] = $this->getFormTable( $_sSectionID, 0, $aSections[ $_sSectionID ], $_aFields, $hfSectionCallback, $hfFieldCallback );
            
                    
            }
            
            if ( empty( $_aOutput ) ) {
                return '';
            }

            return $this->_getCollapsibleSectionTitleBlock( $_abCollapsible )
                . "<div " . $this->generateAttributes(
                        array(
                            'id'    => "sections-" . md5( serialize( $aSections ) ), 
                            'class' => $this->generateClassAttribute( 
                                'admin-page-framework-sections',
                                ! $_sSectionTabSlug || '_default' === $_sSectionTabSlug 
                                    ? null 
                                    : 'admin-page-framework-section-tabs-contents',
                                empty( $_abCollapsible  )
                                    ? null
                                    : 'admin-page-framework-collapsible-sections accordion-section-content'
                            ),
                        )
                    ) . ">"                 
                    . ( $_sSectionTabSlug // if the section tab slug yields true, insert the section tab list
                        ? "<ul class='admin-page-framework-section-tabs nav-tab-wrapper'>" . implode( PHP_EOL, $_aSectionTabList ) . "</ul>"
                        : ''
                    )    
                    . implode( PHP_EOL, $_aOutput )
                . "</div>";
            
        }
            
            /**
             * Returns the output of a title block of the given collapsible section.
             * 
             * @since       3.3.4
             */
            private function _getCollapsibleSectionTitleBlock( $abCollapsible ) {
                
                if ( empty( $abCollapsible ) ) {
                    return '';
                }
                
                return $this->_getCollapsibleSectionsEnablerScript()
                    . "<div " . $this->generateAttributes(
                        array(
                            'class' => $this->generateClassAttribute( 
                                'admin-page-framework-collapsible-sections-title admin-page-framework-section-title accordion-section-title',
                                $abCollapsible['is_collapsed'] ? 'collapsed' : ''
                            ),
                        ) 
                        + ( empty( $abCollapsible ) ? '' : $this->getDataAttributeArray( $abCollapsible ) )
                    ) . ">"  
                            . "<h3>" . $abCollapsible['title'] . "</h3>"
                        . "</div>";
                
            }
                    
            /**
             * Returns the output of a list tab element for tabbed sections.
             * 
             * @since       3.3.4
             */
            private function _getTabList( $sSectionID, $iIndex, array $aSection, array $aFields, $hfFieldCallback ) {
                
                $_sSectionTagID     = 'section-' . $sSectionID . '__' . $iIndex;
                $_aTabAttributes    = $aSection['attributes']['tab']
                    + array(
                        'class' => 'admin-page-framework-section-tab nav-tab',
                        'id'    => "section_tab-{$_sSectionTagID}",
                        'style' => null
                    );
                $_aTabAttributes['class'] = $this->generateClassAttribute( $_aTabAttributes['class'], $aSection['class']['tab'] );  // 3.3.1+
                $_aTabAttributes['style'] = $this->generateStyleAttribute( $_aTabAttributes['style'], $aSection['hidden'] ? 'display:none' : null );  // 3.3.1+
                return "<li " . $this->generateAttributes( $_aTabAttributes ) . ">"
                    . "<a href='#{$_sSectionTagID}'>"
                        . $this->_getSectionTitle( $aSection['title'], 'h4', $aFields, $hfFieldCallback )
                    ."</a>"
                . "</li>";
                
            }        
        /**
         * Returns the section title output.
         * 
         * @since 3.0.0
         */
        private function _getSectionTitle( $sTitle, $sTag, $aFields, $hfFieldCallback ) {
            
            $_aSectionTitleField = $this->_getSectionTitleField( $aFields );
            return $_aSectionTitleField
                ? call_user_func_array( $hfFieldCallback, array( $_aSectionTitleField ) )
                : "<{$sTag}>" . $sTitle . "</{$sTag}>";
            
        }
        
        /**
         * Returns the first found section_title field.
         * 
         * @since 3.0.0
         */
        private function _getSectionTitleField( $aFields ) {
            
            foreach( $aFields as $aField ) {
                if ( 'section_title' === $aField['type'] ) {
                    return $aField; // will return the first found one.
                }
            }
            
        }
        
        /**
         * Returns an array holding section definition arrays by section tab.
         * 
         * @since 3.0.0
         */
        private function _getSectionsBySectionTabs( array $aSections ) {

            $_aSectionsBySectionTab = array();
            $_iIndex                = 0;

            foreach( $aSections as $_aSection ) {
                
                if ( ! $_aSection['section_tab_slug'] ) {
                    $_aSectionsBySectionTab[ '_default_' . $_iIndex ][ $_aSection['section_id'] ] = $_aSection;
                    $_iIndex++;
                    continue;
                }
                    
                $_sSectionTaqbSlug = $_aSection['section_tab_slug'];
                $_aSectionsBySectionTab[ $_sSectionTaqbSlug ] = isset( $_aSectionsBySectionTab[ $_sSectionTaqbSlug ] ) && is_array( $_aSectionsBySectionTab[ $_sSectionTaqbSlug ] )
                    ? $_aSectionsBySectionTab[ $_sSectionTaqbSlug ]
                    : array();
                
                $_aSectionsBySectionTab[ $_sSectionTaqbSlug ][ $_aSection['section_id'] ] = $_aSection;
                
            }
            return $_aSectionsBySectionTab;
            
        }
                
    /**
     * Returns a single HTML table output of a set of fields generated from the given field definition arrays.
     * 
     * @since       3.0.0
     * @since       3.3.1       Now the first parameter is for the section ID not the tag ID.
     * @param       string      $sSectionID          The section ID specified by the user.
     * @param       integer     $iSectionIndex       The section index. Zero based.
     * @param       array       $aSection            The section definition array,
     * @param       array       $sFields             The array holding field definition arrays.
     * @param       callable    $hfSectionCallback   The callback for the section header output.
     * @param       callable    $hfFieldCallback     The callback for the field output.
     */
    public function getFormTable( $sSectionID, $iSectionIndex, $aSection, $aFields, $hfSectionCallback, $hfFieldCallback ) {

        if ( count( $aFields ) <= 0 ) { return ''; }
        
        $_sSectionTagID = 'section-' . $sSectionID . '__' . $iSectionIndex;
        $_aOutput       = array();
        $_aOutput[]     = "<table "
            . $this->generateAttributes(  
                    array( 
                        'id'    => 'section_table-' . $_sSectionTagID,
                        'class' => 'form-table', // temporarily deprecated: admin-page-framework-section-table
                    )
                )
            . ">"
                . $this->_getCaption( $aSection, $hfSectionCallback, $iSectionIndex, $aFields, $hfFieldCallback )
                . $this->getFieldRows( $aFields, $hfFieldCallback )
            . "</table>";
            
        $_aSectionAttributes    = $this->uniteArrays(
            $this->dropElementsByType( $aSection['attributes'] ),   // remove elements of an array.
            array( 
                'id'            => $_sSectionTagID, // section-{section id}__{index}
                'class'         => 'admin-page-framework-section'
                    . ( $aSection['section_tab_slug'] ? ' admin-page-framework-tab-content' : null ),
                // [3.3.1+] The repeatable script refers to this model value to generate new IDs.
                'data-id_model' => 'section-' . $sSectionID . '__' . '-si-',
            )     
        );
        $_aSectionAttributes['class']   = $this->generateClassAttribute( $_aSectionAttributes['class'], $this->dropElementsByType( $aSection['class'] ) );  // 3.3.1+
        $_aSectionAttributes['style']   = $this->generateStyleAttribute( $_aSectionAttributes['style'], $aSection['hidden'] ? 'display:none' : null );  // 3.3.1+        

        return "<div "
                . $this->generateAttributes( $_aSectionAttributes )
            . ">"
                . implode( PHP_EOL, $_aOutput )
            . "</div>";
        
    }
        /**
         * Returns the output of the table caption block.
         * 
         * @since       3.3.4
         */
        private function _getCaption( array $aSection, $hfSectionCallback, $iSectionIndex, $aFields, $hfFieldCallback ) {
            
            if ( ! $aSection['description'] && ! $aSection['title'] ) {
                return "<caption class='admin-page-framework-section-caption' style='display:none;'></caption>";
            }
            
            // For regular repeatable fields, the title should be omitted except the first item.
            $_sDisplayNone  = ( $aSection['repeatable'] && $iSectionIndex != 0 && ! $aSection['section_tab_slug'] ) || $aSection['collapsible']
                ? " style='display:none;'"
                : '';
            
            // @todo avoid calling the property but pass it from a parameter.
            $_sSectionError = isset( $this->aFieldErrors[ $aSection['section_id'] ] ) && is_string( $this->aFieldErrors[ $aSection['section_id'] ] )
                ? $this->aFieldErrors[ $aSection['section_id'] ]
                : '';
            
            return 
                "<caption " . $this->generateAttributes( 
                    array(
                        'class'             => 'admin-page-framework-section-caption',
                        // data-section_tab is referred by the repeater script to hide/show the title and the description
                        'data-section_tab'  => $aSection['section_tab_slug'],
                    ) 
                ) . ">"
                    . ( $aSection['title'] && ! $aSection['section_tab_slug']
                        ? "<div class='admin-page-framework-section-title' {$_sDisplayNone}>" 
                                .  $this->_getSectionTitle( $aSection['title'], 'h3', $aFields, $hfFieldCallback )    
                            . "</div>"
                        : ""
                    )     
                    . ( is_callable( $hfSectionCallback )
                        ? "<div class='admin-page-framework-section-description'>"     // admin-page-framework-section-description is referred by the repeatable section buttons
                                . call_user_func_array( $hfSectionCallback, array( $this->_getDescription( $aSection['description'] ) , $aSection ) )
                            . "</div>"
                        : ""
                    )
                    . ( $_sSectionError  
                        ? "<div class='admin-page-framework-error'><span class='section-error'>* " . $_sSectionError .  "</span></div>"
                        : ''
                    )
                . "</caption>";
          
            
        }
            /**
             * Returns the HTML formatted description blocks by the given description definition.
             * 
             * @since   3.3.0
             * @return  string      The description output.
             */
            private function _getDescription( $asDescription ) {
                
                if ( empty( $asDescription ) ) { return ''; }
                
                $_aOutput = array();
                foreach( $this->getAsArray( $asDescription ) as $_sDescription ) {
                    $_aOutput[] = "<p class='admin-page-framework-section-description'>"
                            . "<span class='description'>{$_sDescription}</span>"
                        . "</p>";
                }
                return implode( PHP_EOL, $_aOutput );
                
            }    
            
}
endif;