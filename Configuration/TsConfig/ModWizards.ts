mod.wizards {
	newContentElement {
		wizardItems {
			plugins {
				elements {
					authagainsttypo3_login {
						icon = ../typo3conf/ext/authagainsttypo3/ext_icon.gif
						title = LLL:EXT:authagainsttypo3/Resources/Private/Language/locallang_db.xml:tt_content.authagainsttypo3_login
						description = LLL:EXT:authagainsttypo3/Resources/Private/Language/locallang_db.xml:tt_content.authagainsttypo3_login.wiz_description
						tt_content_defValues {
							CType = list
							list_type = authagainsttypo3_login
						}
					}
				}
			}
		}
	}
}