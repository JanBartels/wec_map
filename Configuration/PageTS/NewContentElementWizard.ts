mod.wizards {
    newContentElement.wizardItems {
        plugins {
            elements {
                plugins_wecmap_pi1 {
                    icon = EXT:wec_map/Resources/Public/Images/SimpleMapWizIcon.gif
                    title = LLL:EXT:wec_map/Resources/Private/Languages/Plugin/SimpleMap/locallang.xlf:pi1_title
                    description = LLL:EXT:wec_map/Resources/Private/Languages/Plugin/SimpleMap/locallang.xlf:pi1_plus_wiz_description
                    tt_content_defValues {
                        CType = list
                        list_type = wec_map_pi1
                    }
                },
                plugins_wecmap_pi2 {
                    icon = EXT:wec_map/Resources/Public/Images/FEUserMapWizIcon.gif
                    title = LLL:EXT:wec_map/Resources/Private/Languages/Plugin/FEUserMap/locallang.xlf:pi2_title
                    description = LLL:EXT:wec_map/Resources/Private/Languages/Plugin/FEUserMap/locallang.xlf:pi2_plus_wiz_description
                    tt_content_defValues {
                        CType = list
                        list_type = wec_map_pi2
                    }
                },
                plugins_tx_wecmap_pi3 {
                    icon = EXT:wec_map/Resources/Public/Images/DataTableMapWizIcon.gif
                    title = LLL:EXT:wec_map/Resources/Private/Languages/Plugin/DataTableMap/locallang.xlf:pi3_title
                    description = LLL:EXT:wec_map/Resources/Private/Languages/Plugin/DataTableMap/locallang.xlf:pi3_plus_wiz_description
                    tt_content_defValues {
                        CType = list
                        list_type = wec_map_pi3
                    }
                }
            }
        }
    }
}
