<?php

return [
    'ad'				        => 'Активна директория',
    'ad_domain'				    => 'Домейн на активна директория',
    'ad_domain_help'			=> 'Това е понякога еднакво с вашия email домейн, но не винаги.',
    'ad_append_domain_label'    => 'Добави името на домейна',
    'ad_append_domain'          => 'Добави името на домейна към потребителското име',
    'ad_append_domain_help'     => 'От потребителя не се изисква да въвежда "username@domain.local", достатъчно е да напише само "username".',
    'admin_cc_email'            => 'CC електронна поща',
    'admin_cc_email_help'       => 'Въведете допълнителни електронни адреси, ако желаете да се изпраща копие на електронните пощи при вписване и изписване на активи.',
    'admin_settings'            => 'Админ настройки',
    'is_ad'				        => 'Това е активна директория на сървър',
    'alerts'                	=> 'Известия',
    'alert_title'               => 'Обнови настройките за известие',
    'alert_email'				=> 'Изпращане на нотификации към',
    'alert_email_help'    => 'Е-майл адреси или групов е-маил за известяване, разделен със запетайка',
    'alerts_enabled'			=> 'Включване на известията',
    'alert_interval'			=> 'Изтичаш праг на известия (в дни)',
    'alert_inv_threshold'		=> 'Праг на известия за запаси',
    'allow_user_skin'           => 'Позволи потребителска тема',
    'allow_user_skin_help_text' => 'Поставянето на отметка тук, ще позволи на потребителя да ползва различна UI тема от основната.',
    'asset_ids'					=> 'ID на активи',
    'audit_interval'            => 'Одитен интервал',
    'audit_interval_help'       => 'Ако искадате да правите периодична инвентаризация на вашите активи, въведете интервала в месеци за инвентаризация. Ако въведете този интервал, всички активи ще им се смени датата за следваща инвентаризация.',
    'audit_warning_days'        => 'Праг за предупреждение за одит',
    'audit_warning_days_help'   => 'Колко дни предварително трябва да ви предупреждаваме, когато активите са дължими за одит?',
    'auto_increment_assets'		=> 'Автоматично генериране на инвентарни номера на активите',
    'auto_increment_prefix'		=> 'Префикс (незадължително)',
    'auto_incrementing_help'    => 'Първо включете автоматично генериране на инвентарни номера, за да включите тази опция',
    'backups'					=> 'Архивиране',
    'backups_help'              => 'Създаване, сваляне и възстановяване на архиви ',
    'backups_restoring'         => 'Възстановяване от архив',
    'backups_upload'            => 'Качване на архив',
    'backups_path'              => 'Архивите на сървъра са записани в <code>:path</code>',
    'backups_restore_warning'   => 'Използвайте бутона за възстановяване <small><span class="btn btn-xs btn-warning"><i class="text-white fas fa-retweet" aria-hidden="true"></i></span></small> ,за да възстановите архивно копие. (Това не работи с S3 файлова система или Docker.)<br><br>Вашата <strong>цяла :app_name датабаза и всички качени файлове ще бъдат заменени</strong> от съдържанието на архива.  ',
    'backups_logged_out'         => 'Всички потребители, включително и вие, ще бъдат отписани след възстановяването.',
    'backups_large'             => 'Много големите архиви може да не могат да се възстановят поради изтичане на времето на сесията и ще трябва да се възстановят ръчно през команден ред. ',
    'barcode_settings'			=> 'Настройки на баркод',
    'confirm_purge'			    => 'Потвърдете пречистване ',
    'confirm_purge_help'		=> 'Моля да потвърдите изтриването като въведете думата "DELETE" в полето. Изтриването не може да се прекрати и всички записи който са маркирани за истриване, ще бъдат безвъзвратно изтрити. (Добре е да направите архив преди това.)',
    'custom_css'				=> 'Потребителски CSS',
    'custom_css_help'			=> 'Включете вашите CSS правила тук. Не използвайте &lt;style&gt;&lt;/style&gt; тагове.',
    'custom_forgot_pass_url'	=> 'Персонализиран адрес за възстановяване на паролата',
    'custom_forgot_pass_url_help'	=> 'Това URL ще замени вградения механизъм за възстановяване на паролата на входния екран, което е полезно за потребителите, използващи външни оторизации като LDAP. Това ефективно ще спре възможността за възстановяване на паролата за потребителите, управлявани през Sinpe-it.',
    'dashboard_message'			=> 'Съобщение на таблото',
    'dashboard_message_help'	=> 'Този текст ще се появи на таблото на всички потребители с права за достъп до таблото.',
    'default_currency'  		=> 'Валута по подразбиране',
    'default_eula_text'			=> 'EULA по подразбиране',
    'default_language'			=> 'Език по подразбиране',
    'default_eula_help_text'	=> 'Можете да асоциирате специфична EULA към всяка избрана категория.',
    'acceptance_note'           => 'Добавете бележка за вашето решение (По желание)',
    'display_asset_name'        => 'Визуализиране на актив',
    'display_checkout_date'     => 'Визуализиране на дата на изписване',
    'display_eol'               => 'Визуализиране на EOL в таблиците',
    'display_qr'                => 'Показване на Square кодове',
    'display_alt_barcode'		=> 'Показване на 1D баркод',
    'email_logo'                => 'Е-майл лого',
    'barcode_type'				=> '2D тип на баркод',
    'alt_barcode_type'			=> '1D тип на баркод',
    'email_logo_size'       => 'Квадратно лого в е-майлът изглежда най-добре. ',
    'enabled'                   => 'Активно',
    'eula_settings'				=> 'Настройки на EULA',
    'eula_markdown'				=> 'Съдържанието на EULA може да бъде форматирано с <a href="https://help.github.com/articles/github-flavored-markdown/">Github flavored markdown</a>.',
    'favicon'                   => 'Favicon',
    'favicon_format'            => 'Приетите файлови формати са ico, png, и gif. Другите формати на снимки може да не работят в всъчки браузъри.',
    'favicon_size'          => 'Favicons трябва да бъдат квадратна снимка с размери, 16х16 пиксела.',
    'footer_text'               => 'Допълнителен текст във футъра',
    'footer_text_help'          => 'Този текст ще се визуализира в дясната част на футъра. Връзки могат да бъдат добавяни с използването на <a href="https://help.github.com/articles/github-flavored-markdown/">Github тип markdown</a>. Нови редове, хедър тагове, изображения и т.н. могат да доведат до непредвидими резултати.',
    'general_settings'			=> 'Общи настройки',
    'general_settings_keywords' => 'поддръжка, подписи, получаване, формат на е-майл, формат на потребителско име, снимки, страници, иконки, EULA, Gravatar, tos, панели, поверителност',
    'general_settings_help'     => 'Общи условия и други',
    'generate_backup'			=> 'Създаване на архив',
    'google_workspaces'         => 'Google Workspaces',
    'header_color'              => 'Цвят на хедъра',
    'info'                      => 'Тези настройки позволяват да конфигурирате различни аспекти на Вашата инсталация.',
    'label_logo'                => 'Лого за етикет',
    'label_logo_size'           => 'Квадратните логота изглеждат най-добре - ще бъдат показани в горния десен ъгъл на всеки артикулен етикет. ',
    'laravel'                   => 'Версия на Laravel',
    'ldap'                      => 'LDAP',
    'ldap_default_group'        => 'Група с права на достъп по подразбиране',
    'ldap_default_group_info'   => 'Изберете група на потребителите, които са синхронизирани. Потребителите ще получат правата на достъп от групата към която са присвоени.',
    'no_default_group'          => 'Без група по потразбиране',
    'ldap_help'                 => 'LDAP/Активна директория',
    'ldap_client_tls_key'       => 'LDAP клиент с TLS ключ',
    'ldap_client_tls_cert'      => 'LDAP клиент TLS сертификат',
    'ldap_enabled'              => 'LDAP включен',
    'ldap_integration'          => 'LDAP интеграция',
    'ldap_settings'             => 'LDAP настройки',
    'ldap_client_tls_cert_help' => 'Клиетски TLS сертификат и ключ за LDAP връзка се използват обикновенно в Google Workspace конфигурациите със "Secure LDAP." Сертификата + ключ са задъжителни.',
    'ldap_location'             => 'LDAP локация',
'ldap_location_help'             => 'LDAP полето с локация трябва да се използва ако <strong> OU не е оказан при свързването към DN.</strong> Оставете това поле празно ако ползвате търсене по OU.',
    'ldap_login_test_help'      => 'Въведете валидни LDAP потребител и парола от базовия DN, който указахте по-горе, за да тествате коректната конфигурация. НЕОБХОДИМО Е ДА ЗАПИШЕТЕ LDAP НАСТРОЙКИТЕ ПРЕДИ ТОВА.',
    'ldap_login_sync_help'      => 'Това единствено проверява дали LDAP може да се синхронизира успешно. Ако вашата LDAP заявка за оторизация не е коректна е възможно потребителите да не могат да влязат. НЕОБХОДИМО Е ДА ЗАПИШЕТЕ LDAP НАСТРОЙКИТЕ ПРЕДИ ТОВА.',
    'ldap_manager'              => 'LDAP мениджър',
    'ldap_server'               => 'LDAP сървър',
    'ldap_server_help'          => 'Това трябва да започва с Idap:// (for unencrypted or TLS) или Idaps:// (for SSL)',
    'ldap_server_cert'			=> 'Валидация на LDAP SSL сертификата',
    'ldap_server_cert_ignore'	=> 'Допускане на невалиден SSL сертификат',
    'ldap_server_cert_help'		=> 'Изберете тази опция ако използвате самоподписан SSL сертификат.',
    'ldap_tls'                  => 'Използвайте TLS',
    'ldap_tls_help'             => 'Това трябва да се маркира само ако изпълнявате STARTTLS на вашия LDAP сървър. ',
    'ldap_uname'                => 'LDAP потребител за връзка',
    'ldap_dept'                 => 'LDAP Отдел',
    'ldap_phone'                => 'LDAP Телефонен номер',
    'ldap_jobtitle'             => 'LDAP Длъжност',
    'ldap_country'              => 'LDAP Държава',
    'ldap_pword'                => 'LDAP парола на потребител за връзка',
    'ldap_basedn'               => 'Базов DN',
    'ldap_filter'               => 'LDAP филтър',
    'ldap_pw_sync'              => 'LADP Password SYNC',
    'ldap_pw_sync_help'         => 'Премахнете отметката в тази клетка ако не желаете да запазите LDAP паролите синхронизирани с локални пароли. Деактивиране на това означава, че вашите потребители може да не успеят да влязат използвайки LDAP сървъри ако са недостижими по някаква причина.',
    'ldap_username_field'       => 'Поле за потребителско име',
    'ldap_lname_field'          => 'Фамилия',
    'ldap_fname_field'          => 'LDAP собствено име',
    'ldap_auth_filter_query'    => 'LDAP оторизационна заявка',
    'ldap_version'              => 'LDAP версия',
    'ldap_active_flag'          => 'LDAP флаг за активност',
    'ldap_activated_flag_help'  => 'Тази стойност определя дали синхронизирания потребител може да се логва в Snipe-IT. <strong>Не се премахва възможността да се изписват активи към потребителя</strong> и полето трябва да бъде <strong>attribute name</strong> от вашата AD/LDAP, а не <strong>неговата стройност</strong>. <br><br>Ако това поле не съществува във вашата AD/LDAP или стойността е <code>0</code> или <code>false</code> <strong>достъпа на потребителя ще бъде забранен</strong>. Ако стойността в AD/LDAP полето е <code>1</code> или <code>true</code> означава че потребителя може да се логва. Когато това поле е празно във вашата AD се приема <code>userAccountControl</code> атрибута, който обикновенно позволява не блокираните потребители да се логват.',
    'ldap_emp_num'              => 'LDAP номер на служител',
    'ldap_email'                => 'LDAP електронна поща',
    'ldap_test'                 => 'Тест LDAP',
    'ldap_test_sync'            => 'Тест LDAP Синхронизация',
    'license'                   => 'Софтуерен лиценз',
    'load_remote'               => 'Зареждане на отдалечени аватари',
    'load_remote_help_text'		=> 'Премахнете отметката от това квадратче, ако вашата инсталация не може да зареди скриптове от външен интернет. Това ще попречи на Snipe-IT да се опита да зареди аватари от Gravatar или други външни източници.',
    'login'                     => 'Опити за вход',
    'login_attempt'             => 'Опит за вход',
    'login_ip'                  => 'IP Адрес',
    'login_success'             => 'Успешно?',
    'login_user_agent'          => 'Потребителски агент',
    'login_help'                => 'Списък на опитите за достъп',
    'login_note'                => 'Вход забележка',
    'login_note_help'           => 'По избор включете няколко изречения на екрана за вход, например, за да помогнете на хора, които са намерили изгубено или откраднато устройство. Това поле приема <a href="https://help.github.com/articles/github-flavored-markdown/">Github flavored markdown</a>',
    'login_remote_user_text'    => 'Опции за вход с Remote User',
    'login_remote_user_enabled_text' => 'Включване на вход с HTTP хедър Remote User',
    'login_remote_user_enabled_help' => 'Тази опция включва автентификация с HTTP хедър REMOTE_USER в съответствие с "Common Gateway Interface (rfc3875)"',
    'login_common_disabled_text' => 'Изключване на други оторизационни механизми',
    'login_common_disabled_help' => 'Тази опция изключва останалите оторизационни механизми. Преди да включите тази настройка моля проверете дали REMOTE_USER механизмът работи',
    'login_remote_user_custom_logout_url_text' => 'Персонализиран адрес за изход',
    'login_remote_user_custom_logout_url_help' => 'Ако уеб адреса е вписън тук, потребителите ще бъдат препратени към този URL след като излязат от Snipe-IT. Това е препоръчително за да се затвори сесията от доставчика на удостоверения правилно.',
    'login_remote_user_header_name_text' => 'Поле по избор за потребителско име',
    'login_remote_user_header_name_help' => 'Използвайте поле по избор вместо REMOTE_USER',
    'logo'                    	=> 'Лого',
    'logo_print_assets'         => 'Използвай при печат',
    'logo_print_assets_help'    => 'Показвай логото при печат на листа с артикули ',
    'full_multiple_companies_support_help_text' => 'Ограничаване на потребителите (включително административните) до активите на собствената им компания.',
    'full_multiple_companies_support_text' => 'Поддръжка на множество компании',
    'show_in_model_list'   => 'Показване в падащите менюта на моделите',
    'optional'					=> 'незадължително',
    'per_page'                  => 'Резултати на страница',
    'php'                       => 'PHP версия',
    'php_info'                  => 'PHP инфо',
    'php_overview'              => 'PHP',
    'php_overview_keywords'     => 'phpinfo, система, информация',
    'php_overview_help'         => 'PHP Системна информация',
    'php_gd_info'               => 'Необходимо е да инсталирате php-gd, за да визуализирате QR кодове. Моля прегледайте инструкцията за инсталация.',
    'php_gd_warning'            => 'php-gd НЕ е инсталиран.',
    'pwd_secure_complexity'     => 'Сложност на паролата',
    'pwd_secure_complexity_help' => 'Изберете правилата за сложност на паролата, които искате да приложите.',
    'pwd_secure_complexity_disallow_same_pwd_as_user_fields' => 'Паролата не може да бъде същата както името, фамилията, е-майл адреса или потребителското име',
    'pwd_secure_complexity_letters' => 'Изисква се поне една буква',
    'pwd_secure_complexity_numbers' => 'Изисква се поне едно число',
    'pwd_secure_complexity_symbols' => 'Изисква се поне един символ',
    'pwd_secure_complexity_case_diff' => 'Изисква се поне една главна и една малка буква',
    'pwd_secure_min'            => 'Минимални знаци за паролата',
    'pwd_secure_min_help'       => 'Минималният брой символи е 8',
    'pwd_secure_uncommon'       => 'Предотвратяване на общи пароли',
    'pwd_secure_uncommon_help'  => 'Това ще забрани на потребителите да използват общи пароли от най-добрите 10 000 пароли, за които се съобщава, че са нарушени.',
    'qr_help'                   => 'Първо включете QR кодовете, за да извършите тези настройки.',
    'qr_text'                   => 'Съдържание на QR код',
    'saml'                      => 'SAML',
    'saml_title'                => 'Обноваване на SAML настройките',
    'saml_help'                 => 'SAML настройки',
    'saml_enabled'              => 'SAML включен',
    'saml_integration'          => 'SAML интеграция',
    'saml_sp_entityid'          => 'Иден. Номер',
    'saml_sp_acs_url'           => 'Assertion Consumer Service (ACS) URL адрес',
    'saml_sp_sls_url'           => 'Single Logout Service (SLS) URL адрес',
    'saml_sp_x509cert'          => 'Публичен сертификат',
    'saml_sp_metadata_url'      => 'Метаданни URL',
    'saml_idp_metadata'         => 'SAML IdP Metadata',
    'saml_idp_metadata_help'    => 'Може да изберете IdP метадата използвайки URL или XML файл.',
    'saml_attr_mapping_username' => 'Асоцииране на поле - Username',
    'saml_attr_mapping_username_help' => 'NameID ще се използва, ако мапването на атрибут е невалидно.',
    'saml_forcelogin_label'     => 'SAML задължителен вход',
    'saml_forcelogin'           => 'Направете SAML основен метод за вход',
    'saml_forcelogin_help'      => 'Може да използвате \'/login?nosaml\' за да се логнете през нормалната логин страница.',
    'saml_slo_label'            => 'SAML единичен Log Out',
    'saml_slo'                  => 'Изпраща LogoutRequest до IdP при Logout',
    'saml_slo_help'             => 'Това ще направи потребителя да бъде препратен към IdP при logout. Оставете това поле не маркирано ако IdP не поддържа SP-initiated SAML SLO.',
    'saml_custom_settings'      => 'SAML настройки по избор',
    'saml_custom_settings_help' => 'Може да направите допълнителни настройки на onelogin/php-saml библиотеката. На ваша отговорност.',
    'saml_download'             => 'Изтегляне на Метадата',
    'setting'                   => 'Настройка',
    'settings'                  => 'Настройки',
    'show_alerts_in_menu'       => 'Показва съобщения в главното меню',
    'show_archived_in_list'     => 'Архивирани активи',
    'show_archived_in_list_text'     => 'Показва архивираните активи в списъка "Всички активи"',
    'show_assigned_assets'      => 'Показва активите зачислени към други активи',
    'show_assigned_assets_help' => 'Показва активите, които са зачислени към други активи от Потребители -> Активи, Потребители -> Информация -> Разпечатай всички заведени, или  Акаунт -> Покажи заведени активи.',
    'show_images_in_email'     => 'Показване на изображения в електронните съобщения',
    'show_images_in_email_help'   => 'Премахнете отметката, ако Вашата инсталация е достъпна единствено във вътрешната мрежа или през VPN.',
    'site_name'                 => 'Име на системата',
    'integrations'               => 'Интеграции',
    'slack'                     => 'Slack',
    'general_webhook'           => 'Основни Webhook',
    'ms_teams'                  => 'Microsoft Teams',
    'webhook'                   => ':app',
    'webhook_presave'           => 'Тест и запис',
    'webhook_title'               => 'Обнови Webhook настроки',
    'webhook_help'                => 'Настройки на интеграцията',
    'webhook_botname'             => ':app Botname',
    'webhook_channel'             => ':app Канал',
    'webhook_endpoint'            => ':app Крайна точка',
    'webhook_integration'         => ':app Настройки',
    'webhook_test'                 =>'Тест :app интеграция',
    'webhook_integration_help'    => ':app интеграцията е по избор, въпреки че крайната цел и канала са задължителни, ако искате да я ползате. За да се конфигурира :app интеграцията трябва първо да <a href=":webhook_link" target="_new" rel="noopener"> създадете входяща webhook</a> във вашият :app акаунт. Кликнете на <strong>Тест :app интеграция</strong> бутона за да потвърдите, че всичко работи преди да запишете настройките. ',
    'webhook_integration_help_button'    => 'След като запишите вашата информация за :app, ще се пояави тест бутон.',
    'webhook_test_help'           => 'Тест за коректна конфигурация на :app интеграцията. НЕОБХОДИМО Е ПЪРВО ДА ЗАПИШЕТЕ :app НАСТРОЙКИТЕ.',
    'snipe_version'  			=> 'Snipe-IT версия',
    'support_footer'            => 'Връзки към Snipe-it поддръжката във футъра',
    'support_footer_help'       => 'Указва визуализацията на връзки към поддръжката на Snipe-IT и потребителската документация',
    'version_footer'            => 'Версия в долен колонтитул ',
    'version_footer_help'       => 'Определя кой ще вижда Snipe-IT версията и номера на компилация.',
    'system'                    => 'Информация за системата',
    'update'                    => 'Обновяване на настройките',
    'value'                     => 'Стойност',
    'brand'                     => 'Брандиране',
    'brand_keywords'            => 'долен колонтитул, лого, печат, тема, скин, горен колонтитул, цветове, css',
    'brand_help'                => 'Лого, Име на сайт',
    'web_brand'                 => 'Тип уеб брандиране',
    'about_settings_title'      => 'Относно настройките',
    'about_settings_text'       => 'Тези настройки позволяват да конфигурирате различни аспекти на Вашата инсталация.',
    'labels_per_page'           => 'Етикети на страница',
    'label_dimensions'          => 'Измерения на етикети (инчове)',
    'next_auto_tag_base'        => 'Следващото автоматично увеличение',
    'page_padding'              => 'Марж на страница (инчове)',
    'privacy_policy_link'       => 'Връзка към декларация за поверителност',
    'privacy_policy'            => 'Декларация за поверителност',
    'privacy_policy_link_help'  => 'Ако впишете адрес ще бъде добавена връзка към декларация за поверителност във футъра и във всички e-mail съобщения, в съответствие с GDPR.',
    'purge'                     => 'Пречисти изтрити записи',
    'purge_deleted'             => 'Изчистване на изтритите ',
    'labels_display_bgutter'    => 'Обозначаване на долен канал',
    'labels_display_sgutter'    => 'Обозначаване на страничен канал',
    'labels_fontsize'           => 'Обозначаване на размер на шрифта',
    'labels_pagewidth'          => 'Обозначаване на ширина на листа',
    'labels_pageheight'         => 'Обозначаване на височина на листа',
    'label_gutters'        => 'Обозначаване на разстояние (инчове)',
    'page_dimensions'        => 'Размери на страницата (инчове)',
    'label_fields'          => 'Обозначаване на видими полета',
    'inches'        => 'инчове',
    'width_w'        => 'w',
    'height_h'        => 'h',
    'show_url_in_emails'                => 'Връзка към Snipe-IT в имейли',
    'show_url_in_emails_help_text'      => 'Премахнете отметката от тази кутийка, ако не желаете да свържете обратно към вашата Snipe-IT инсталация в досиетата ви за електронна поща. Полезно, ако повечето от вашите потребители никога не влизат в системата.',
    'text_pt'        => 'pt',
    'thumbnail_max_h'   => 'Максимална височина на миниатюрите',
    'thumbnail_max_h_help'   => 'Максималната височина в пиксели, която миниатюрите могат да се показват в изгледа на малката обява. Мин. 25, макс. 500.',
    'two_factor'        => 'Двуфакторно удостоверяване',
    'two_factor_secret'        => 'Двуфакторен код',
    'two_factor_enrollment'        => 'Двуфакторово записване',
    'two_factor_enabled_text'        => 'Разреши два фактор',
    'two_factor_reset'        => 'Нулиране на двуфакторова тайна',
    'two_factor_reset_help'        => 'Това ще принуди потребителя да запише своето устройство с Authenticator отново. Това може да бъде полезно, ако записаните понастоящем устройства са изгубени или откраднати. ',
    'two_factor_reset_success'          => 'Двуфакторово устройство нулирано успешно',
    'two_factor_reset_error'          => 'Нулирането на двуфакторово устройство беше неуспешно',
    'two_factor_enabled_warning'        => 'Разрешаване на два-фактора ако не са разрешени в момента, ще ви принуди незабавно да се удостоверите с устройство записано в Google Auth. Ще имате възможността да запишете устройството си ако нямате такова.',
    'two_factor_enabled_help'        => 'Това ще включи двуфакторова заверка която използва Google Authenticator.',
    'two_factor_optional'        => 'Селективни (Потребителите могат да включват или изключват ако им е позволено)',
    'two_factor_required'        => 'Задължително за всички потребители',
    'two_factor_disabled'        => 'Деактивирано',
    'two_factor_enter_code'	=> 'Въведете двуфакторен код',
    'two_factor_config_complete'	=> 'Подаване на код',
    'two_factor_enabled_edit_not_allowed' => 'Вашият администратор не разрешава да редактирате тази настройка.',
    'two_factor_enrollment_text'	=> "Двуфакторово удостоверяване се изисква, но вашето устройство не е било регистрирано още. Отворете Google Authenticator и сканирайте QR кода по-долу за да регистрирате вашето устройство. След като сте записани вашето устройство, въведете кода по-долу",
    'require_accept_signature'      => 'Изисква подпис',
    'require_accept_signature_help_text'      => 'Разрешаването на тази функция ще изисква от потребителите да се подпишат физически за приемане на даден актив.',
    'left'        => 'ляво',
    'right'        => 'дясно',
    'top'        => 'Горе',
    'bottom'        => 'Долу',
    'vertical'        => 'Вертикално',
    'horizontal'        => 'Хоризонтално',
    'unique_serial'                => 'Уникален сериен номер',
    'unique_serial_help_text'                => 'Отмятането на този чек, ще задължи ползването на уникални сериини номера на артикулите',
    'zerofill_count'        => 'Дължина на етикети на актив, включително zerofill',
    'username_format_help'   => 'Тази настройка се изпозлва само при импортиране, ако потребителя не е въведен и ние трябва да му генерираме потребителско име.',
    'oauth_title' => 'OAuth API Настройки',
    'oauth_clients' => 'OAuth Клиенти',
    'oauth' => 'OAuth',
    'oauth_help' => 'Oauth Endpoint настройки',
    'oauth_no_clients' => 'Все още не сте създали OAuth клиенти.',
    'oauth_secret' => 'Тайна',
    'oauth_authorized_apps' => 'Оторизирани приложения',
    'oauth_redirect_url' => 'URL адрес за пренасочване',
    'oauth_name_help' => ' Нещо, на което вашите потребители ще разпознаят и ще се доверят.',
    'oauth_scopes' => 'Обхват',
    'oauth_callback_url' => 'Вашият URL адрес за обратно извикване за оторизация.',
    'create_client' => 'Създайте клиент',
    'no_scopes' => 'Без обхват',
    'asset_tag_title' => 'Обнови настроките на етикета на актива',
    'barcode_title' => 'Обнови настройките на баркод',
    'barcodes' => 'Баркоди',
    'barcodes_help_overview' => 'Баркод &amp; QR настройки',
    'barcodes_help' => 'Това ще изтрие кеша на баркодовете. Това обикновено се използва при промяна на баркод настройките или при промяна на Snipe-IT URL адреса. Баркодовете ще бъдат генерирани отново.',
    'barcodes_spinner' => 'Опит за изтриване на файлове...',
    'barcode_delete_cache' => 'Изтрий баркод кеша',
    'branding_title' => 'Обноваване на настройките за брандиране',
    'general_title' => 'Обнови общите настройки',
    'mail_test' => 'Изпрати Тест',
    'mail_test_help' => 'Изпрати тестов е-майл до :replyto.',
    'filter_by_keyword' => 'Филтър по ключова дума',
    'security' => 'Сигурност',
    'security_title' => 'Обнови настройките за сигурност',
    'security_keywords' => 'парола, парили, изисквания, двустепенна идентификация, двустепенна-идентификация, общи пароли, отдалечен вход, изход, идентификация',
    'security_help' => 'Двустепенна идентификация, ограничения на пароли',
    'groups_keywords' => 'права за достъп, групи за достъп, упълномощаване',
    'groups_help' => 'Групи с разрешения за акаунт',
    'localization' => 'Локализация',
    'localization_title' => 'Обнови настройките за локализация',
    'localization_keywords' => 'локализация, валута, местен, място, часова зона, международен, интернационализация, език, езици, превод',
    'localization_help' => 'Език, дата формат',
    'notifications' => 'Известия',
    'notifications_help' => 'Настройки на е-майл известия',
    'asset_tags_help' => 'Автоматично номериране и префикси',
    'labels' => 'Етикети',
    'labels_title' => 'Обнови настройките на етикета',
    'labels_help' => 'Размер на етикета &amp; настройки',
    'purge_keywords' => 'изтриване за постоянно',
    'purge_help' => 'Пречисти изтрити записи',
    'ldap_extension_warning' => 'Изглежда, че нямате инсталирани LDAP разширения или не са пуснати на сървъра. Вие можете все пак да запишите настройките, но ще трябва да включите LDAP разширенията за PHP преди да синхронизирате с LDAP, в противен случай няма да можете да се логнете.',
    'ldap_ad' => 'LDAP/AD',
    'employee_number' => 'Номер на служител',
    'create_admin_user' => 'Нов потребител ::',
    'create_admin_success' => 'Готово! Вашият админ потребител беше добавен!',
    'create_admin_redirect' => 'Щракнете тук за да отидете на логин екрана на вашата програма!',
    'setup_migrations' => 'Миграция на датабаза ::',
    'setup_no_migrations' => 'Няма нищо за миграция. Таблиците от вашата датабаза са вече обновени!',
    'setup_successful_migrations' => 'Таблиците в базаданите бяха създадени',
    'setup_migration_output' => 'Резултат от миграцията:',
    'setup_migration_create_user' => 'Следва: Създаване на потребител',
    'ldap_settings_link' => 'LDAP настройки',
    'slack_test' => 'Тест <i class="fab fa-slack"></i> интеграция',
    'label2_enable'           => 'Нов генератор за етикети',
    'label2_enable_help'      => 'Използвайте новия генератор за етикети. <b>Забележка: Трябва да изберете тази настройка и да запишете преди да променята другите настройки.</b>',
    'label2_template'         => 'Шаблон',
    'label2_template_help'    => 'Изберете кой шаблон да се използва за генериране на етикет',
    'label2_title'            => 'Титла',
    'label2_title_help'       => 'Това заглаве да се показва на етикета, ако се поддържа от него',
    'label2_title_help_phold' => 'Полето <code>{COMPANY}</code> ще бъде заменено с името на фирмата',
    'label2_asset_logo'       => 'Използвай логото на актива',
    'label2_asset_logo_help'  => 'Използвай логото на фирмата, вместо стойността от <code>:setting_name</code>',
    'label2_1d_type'          => '1D тип на баркод',
    'label2_1d_type_help'     => 'Формат на 1D баркод',
    'label2_2d_type'          => '2D тип на баркод',
    'label2_2d_type_help'     => 'Формат на 2D баркод',
    'label2_2d_target'        => '2D баркод адрес',
    'label2_2d_target_help'   => 'Къде да сочи URL адреса на 2D баркода, когато се сканира',
    'label2_fields'           => 'Настройки на полета',
    'label2_fields_help'      => 'Полетата могат да бъдат добавяни, премахване и подреждани от лявата колона. За всяко едно поле, множество настройки могат да бъдат добавяни премахвани и подреждани в дясната колона.',
    'help_asterisk_bold'    => 'Текста въведен като <code>**text**</code> ,ще бъде показан удебелено',
    'help_blank_to_use'     => 'Оставете празно за да се ползва стойност от <code>:setting_name</code>',
    'help_default_will_use' => '<code>:default</code> ще използва стойност от <code>:setting_name</code>. <br>Стойността на баркода трябва да отговаря на изискванията за баркод или той няма да се генерира. Моля вижте следното<a href="https://snipe-it.readme.io/docs/barcodes">упътване <i class="fa fa-external-link"></i></a> за повече информация. ',
    'default'               => 'Подразбиране',
    'none'                  => 'Няма',
    'google_callback_help' => 'Това трябва да се въведе като callback URL във вашият Google OAuth настройки за вашата организация &apos;s <strong><a href="https://console.cloud.google.com/" target="_blank">Google developer console <i class="fa fa-external-link" aria-hidden="true"></i></a></strong>.',
    'google_login'      => 'Настройки за вход към Google Workspace',
    'enable_google_login'  => 'Позволи потребителя да влиза с Google Workspace',
    'enable_google_login_help'  => 'Потребителите ще бъдат автоматично създадени. Те трябва да имат наличен акаунт тук И във Google Workspace, като тяхното потребителско име трябва да съвшада с е-майл адреса от Google Workspace. ',
    'mail_reply_to' => 'Е-майл адрес за отговор',
    'mail_from' => 'Е-маил от адрес',
    'database_driver' => 'Драйвер на датабаза',
    'bs_table_storage' => 'Table Storage',
    'timezone' => 'Часова зона',
    'profile_edit'          => 'Редактиране на профил',
    'profile_edit_help'          => 'Позволете на потребителите сами да могат да редактират собствените си профили.',
    'default_avatar' => 'Качване на аватар по подразбиране',

];
