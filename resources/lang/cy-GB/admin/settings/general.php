<?php

return [
    'ad'				        => 'Active Directory',
    'ad_domain'				    => 'Parth Active Directory',
    'ad_domain_help'			=> 'Ar adegau yn debyg i parth eich cyfeiriad ebost, ond dim pob tro.',
    'ad_append_domain_label'    => 'Append domain name',
    'ad_append_domain'          => 'Append domain name to username field',
    'ad_append_domain_help'     => 'User isn\'t required to write "username@domain.local", they can just type "username".',
    'admin_cc_email'            => 'CC Ebost',
    'admin_cc_email_help'       => 'Os ydych am i cyfrif ebost derbyn copi o negeseuon i ddefnyddwyr wrth nodi asdedau allan i defnyddwyr ac yn ol i fewn rhowch o yma. Fel arall, gadewch yn wag.',
    'admin_settings'            => 'Admin Settings',
    'is_ad'				        => 'Mae hwn yn Server Active Directory',
    'alerts'                	=> 'Alerts',
    'alert_title'               => 'Update Notification Settings',
    'alert_email'				=> 'Gyrru rhybuddion i',
    'alert_email_help'    => 'Email addresses or distribution lists you want alerts to be sent to, comma separated',
    'alerts_enabled'			=> 'Rhybuddion ebost wedi alluogi',
    'alert_interval'			=> 'Trothwy Rhybuddion sy\'n Dod i Ben (mewn dyddiau)',
    'alert_inv_threshold'		=> 'Trothwy Rhybudd Rhestr',
    'allow_user_skin'           => 'Allow User Skin',
    'allow_user_skin_help_text' => 'Checking this box will allow a user to override the UI skin with a different one.',
    'asset_ids'					=> 'Rhifau Unigryw Asedau',
    'audit_interval'            => 'Cyfnod Awdit',
    'audit_interval_help'       => 'If you are required to regularly physically audit your assets, enter the interval in months that you use. If you update this value, all of the "next audit dates" for assets with an upcoming audit date will be updated.',
    'audit_warning_days'        => 'Trothwy Rhybuddio Awdit',
    'audit_warning_days_help'   => 'Sawl diwrnod o flaen llaw ddylswn rhybuddio chi o asedau sydd angen awdit?',
    'auto_increment_assets'		=> 'Generate auto-incrementing asset tags',
    'auto_increment_prefix'		=> 'Rhagddodiad (dewisol)',
    'auto_incrementing_help'    => 'Enable auto-incrementing asset tags first to set this',
    'backups'					=> 'Copi wrth gefn',
    'backups_help'              => 'Create, download, and restore backups ',
    'backups_restoring'         => 'Restoring from Backup',
    'backups_upload'            => 'Upload Backup',
    'backups_path'              => 'Backups on the server are stored in <code>:path</code>',
    'backups_restore_warning'   => 'Use the restore button <small><span class="btn btn-xs btn-warning"><i class="text-white fas fa-retweet" aria-hidden="true"></i></span></small> to restore from a previous backup. (This does not currently work with S3 file storage or Docker.)<br><br>Your <strong>entire :app_name database and any uploaded files will be completely replaced</strong> by what\'s in the backup file.  ',
    'backups_logged_out'         => 'All existing users, including you, will be logged out once your restore is complete.',
    'backups_large'             => 'Very large backups may time out on the restore attempt and may still need to be run via command line. ',
    'barcode_settings'			=> 'Gosodiadau Barcode',
    'confirm_purge'			    => 'Cadarnhau Clirio',
    'confirm_purge_help'		=> 'Enter the text "DELETE" in the box below to purge your deleted records. This action cannot be undone and will PERMANENTLY delete all soft-deleted items and users. (You should make a backup first, just to be safe.)',
    'custom_css'				=> 'Addasu CSS',
    'custom_css_help'			=> 'Cewch nodi unrhyw CSS personol yma. Peidiwch a cynnwys &lt;style&gt;&lt;/style&gt;.',
    'custom_forgot_pass_url'	=> 'Cyfeiriad gwasanaeth newid cyfrinair',
    'custom_forgot_pass_url_help'	=> 'Mae hyn yn cymeryd lle y system menwol i newid cyfrineiriau ar y wefan mewngofnodi, o defnydd i gyfeirio pobol at eich datrysiad newid cyfrineiriau LDAP. Nid ywn\'n bosib i defnyddwyr lleol o SNipe-IT newid cyfrineair os yw\'r opsiwn wedi alluogi.',
    'dashboard_message'			=> 'Neges Dashfwrdd',
    'dashboard_message_help'	=> 'Fydd y neges yma yn ymddangos ar y dashfwrdd i unrhywun hefo hawl i weld y dashfwrdd.',
    'default_currency'  		=> 'Arian Diofyn',
    'default_eula_text'			=> 'CGTG Diofyn',
    'default_language'			=> 'Iaith Diofyn',
    'default_eula_help_text'	=> 'Yn ogystal, fedrwch perthnasu CTDT yn erbyn asedau penodol.',
    'acceptance_note'           => 'Add a note for your decision (Optional)',
    'display_asset_name'        => 'Dangos Enw Ased',
    'display_checkout_date'     => 'Dangos Dyddiad Allan',
    'display_eol'               => 'Dangos DB yn y tabl',
    'display_qr'                => 'Arddangos Codau Sgwâr',
    'display_alt_barcode'		=> 'Arddangos barcode 1D',
    'email_logo'                => 'Logo ebyst',
    'barcode_type'				=> 'Math Barcode 2D',
    'alt_barcode_type'			=> 'Math Barcode 1D',
    'email_logo_size'       => 'Logo sgwar sydd edrych orau mewn ebost. ',
    'enabled'                   => 'Enabled',
    'eula_settings'				=> 'Gosodiadau CTDT',
    'eula_markdown'				=> 'Mae\'r CTDT yma yn caniatau <a href="https://help.github.com/articles/github-flavored-markdown/">markdown GitHub</a>.',
    'favicon'                   => 'Favicon',
    'favicon_format'            => 'Mathau o ffeiliau a dderbynnir yw ico, png, a gif. Mae\'n bosib cewch trafferthion hefo rhai gwahanol mewn rhai porrwyr.',
    'favicon_size'          => 'Dylith favicons bod yn delweddau sgwar 16x16 pixels.',
    'footer_text'               => 'Testun Troedyn Ychwanegol ',
    'footer_text_help'          => 'Dangosir y text yma ir ochor dde yn y troedyn. Mae lincs yn dderbyniol gan defnyddio <a href="https://help.github.com/articles/github-flavored-markdown/">Github flavored markdown</a>. Line breaks, headers, images, etc may result in unpredictable results.',
    'general_settings'			=> 'Gosodiadau Cyffredinol',
    'general_settings_keywords' => 'company support, signature, acceptance, email format, username format, images, per page, thumbnail, eula, gravatar, tos, dashboard, privacy',
    'general_settings_help'     => 'Default EULA and more',
    'generate_backup'			=> 'Creu copi-wrth-gefn',
    'google_workspaces'         => 'Google Workspaces',
    'header_color'              => 'Lliw penawd',
    'info'                      => 'Mae\'r gosodiadau yma yn caniatau i chi addasu elfennau o\'r system.',
    'label_logo'                => 'Logo Label',
    'label_logo_size'           => 'Logos sgwar sydd orau - dangosir ar y dde ar top label ased. ',
    'laravel'                   => 'Fersiwn Laravel',
    'ldap'                      => 'LDAP',
    'ldap_default_group'        => 'Default Permissions Group',
    'ldap_default_group_info'   => 'Select a group to assign to newly synced users. Remember that a user takes on the permissions of the group they are assigned.',
    'no_default_group'          => 'No Default Group',
    'ldap_help'                 => 'LDAP/Active Directory',
    'ldap_client_tls_key'       => 'LDAP Client TLS Key',
    'ldap_client_tls_cert'      => 'LDAP Client-Side TLS Certificate',
    'ldap_enabled'              => 'LDAP wedi alluogi',
    'ldap_integration'          => 'Integreiddio LDAP',
    'ldap_settings'             => 'Gosodiadau LDAP',
    'ldap_client_tls_cert_help' => 'Client-Side TLS Certificate and Key for LDAP connections are usually only useful in Google Workspace configurations with "Secure LDAP." Both are required.',
    'ldap_location'             => 'LDAP Location',
'ldap_location_help'             => 'The Ldap Location field should be used if <strong>an OU is not being used in the Base Bind DN.</strong> Leave this blank if an OU search is being used.',
    'ldap_login_test_help'      => 'Gosodwch cyfrif a chyfrinair LDAP dilys o\'r base DN i profi cysyllted a gweithrediad LDAP. RHAID ARBED Y GOSODIADAU LDAP CYNTAF.',
    'ldap_login_sync_help'      => 'Mae\'r prawf yma yn profi\'r gallu i LDAP gwneud sync. Os ydi\'r gosodiadau LDAP yn anghywir mae\'n bosib ni ellith defnyddwyr mewngofnodi. RHAID ARBED GOSODIADAU LDAP CYNTAF.',
    'ldap_manager'              => 'LDAP Manager',
    'ldap_server'               => 'Server LDAP',
    'ldap_server_help'          => 'Dylith hwn ddechra hefo ldap://(Ar gyfer cysylltiadau TLS neu heb eu hamcryptio) neu ldaps://(ar gyfer SSL)',
    'ldap_server_cert'			=> 'Profi tystysgrif LDAP SSL dilys',
    'ldap_server_cert_ignore'	=> 'Caniatau Tystyrgrif SSL annilys',
    'ldap_server_cert_help'		=> 'Dewisiwch y blwch yma os ydych yn defnyddio tystysgrif wedi hunan-arwyddo ac os ydych am dderbyn tystysgrif SSL annilys.',
    'ldap_tls'                  => 'Defnyddio TLS',
    'ldap_tls_help'             => 'Dewisiwch os ydych yn rhedeg STARTTLS ar eich server LDAP. ',
    'ldap_uname'                => 'Enw defnyddiwr i cysylltu trwy LDAP',
    'ldap_dept'                 => 'LDAP Department',
    'ldap_phone'                => 'LDAP Telephone Number',
    'ldap_jobtitle'             => 'Teitl Swydd LDAP',
    'ldap_country'              => 'Gwlad LDAP',
    'ldap_pword'                => 'Cyfrinair i cysylltu trwy LDAP',
    'ldap_basedn'               => 'DN Cyswllt Sylfaenol',
    'ldap_filter'               => 'Hidlydd LDAP',
    'ldap_pw_sync'              => 'Sync cyfrinair LDAP',
    'ldap_pw_sync_help'         => 'Tynnwch y tic o\'r focs yma os nad ydych am cadw cyfrineiriau LDAP mewn sync a cyfrineiriau lleol. Mae an-alluogi hyn yn feddwl ni ellith defnyddywr mewngofnodi os oes problem hefo\'r server LDAP.',
    'ldap_username_field'       => 'Maes Enw Defnyddiwr',
    'ldap_lname_field'          => 'Enw Olaf',
    'ldap_fname_field'          => 'Enw Cyntaf LDAP',
    'ldap_auth_filter_query'    => 'Ymholiad dilysu LDAP',
    'ldap_version'              => 'Fersiwn LDAP',
    'ldap_active_flag'          => 'Nodi bod LDAP yn weithredol',
    'ldap_activated_flag_help'  => 'This value is used to determine whether a synced user can login to Snipe-IT. <strong>It does not affect the ability to check items in or out to them</strong>, and should be the <strong>attribute name</strong> within your AD/LDAP, <strong>not the value</strong>. <br><br>If this field is set to a field name that does not exist in your AD/LDAP, or the value in the AD/LDAP field is set to <code>0</code> or <code>false</code>, <strong>user login will be disabled</strong>. If the value in the AD/LDAP field is set to <code>1</code> or <code>true</code> or <em>any other text</em> means the user can log in. When the field is blank in your AD, we respect the <code>userAccountControl</code> attribute, which usually allows non-suspended users to log in.',
    'ldap_emp_num'              => 'LDAP Rhif Cyflogai',
    'ldap_email'                => 'Ebost LDAP',
    'ldap_test'                 => 'Test LDAP',
    'ldap_test_sync'            => 'Test LDAP Synchronization',
    'license'                   => 'Trwydded Meddalwedd',
    'load_remote'               => 'Load Remote Avatars',
    'load_remote_help_text'		=> 'Uncheck this box if your install cannot load scripts from the outside internet. This will prevent Snipe-IT from trying load avatars from Gravatar or other outside sources.',
    'login'                     => 'Login Attempts',
    'login_attempt'             => 'Login Attempt',
    'login_ip'                  => 'Cyfeiriad IP',
    'login_success'             => 'Llwyddiant?',
    'login_user_agent'          => 'User Agent',
    'login_help'                => 'List of attempted logins',
    'login_note'                => 'Nodyn Mewngofnodi',
    'login_note_help'           => 'Cewch dewis i cynnwys brawddeg neu ddwy ar y sgrin mewngofnodi, e.e. i cynorthwyo pobol sydd wedi darganfod offer. This field accepts <a href="https://help.github.com/articles/github-flavored-markdown/">Github flavored markdown</a>',
    'login_remote_user_text'    => 'Dewisiadau mewngofnodi ar gyfer defnyddywr o bell',
    'login_remote_user_enabled_text' => 'Caniatau mewngofnodi hefo\'r Remote User Header',
    'login_remote_user_enabled_help' => 'Mae\'r opsiwn yma yn caniatau dilysu trwy\'r REMOTE_USER header yn ol "Common Gateway Interface (rfc3875)"',
    'login_common_disabled_text' => 'Analluogi dulliau eraill o mewngofnodi',
    'login_common_disabled_help' => 'Mae\'r opsiwn yma yn analluogi dulliau eraill o mewngofnodi. Alluogch yr opsiwn yma os ydych yn sicr bod yr opsiwn REMOTE_USER yn weithredol',
    'login_remote_user_custom_logout_url_text' => 'URL Allgofnodi',
    'login_remote_user_custom_logout_url_help' => 'Os oes URL yma mi fydd defnyddwyr yn cael ei gyfeirio yma wrth mewngofnodi. Mae hyn yn defnyddiol i cau sesiynau hefo\'r endid sydd yn darparu\'r gwasanaeth dilysu.',
    'login_remote_user_header_name_text' => 'Pennawd enw defnyddiwr personol',
    'login_remote_user_header_name_help' => 'Defnyddio\'r pennawd penodedig yn lle REMOTE_USER',
    'logo'                    	=> 'Logo',
    'logo_print_assets'         => 'Defnyddio wrth argraffu',
    'logo_print_assets_help'    => 'Defnyddio branding ar rhestrau asedau i\'w argraffu ',
    'full_multiple_companies_support_help_text' => 'Cyfyngu defnyddywr (gan cynnwys Admin) sydd wedi aseinio i gwmni i asedau\'r cwmni.',
    'full_multiple_companies_support_text' => 'Cefnogaeth Llawn ar gyfer Nifer o Cwmniau',
    'show_in_model_list'   => 'Dangos mewn dewislen modelau',
    'optional'					=> 'dewisol',
    'per_page'                  => 'Canlyniadau fesul tudalen',
    'php'                       => 'Fersiwn PHP',
    'php_info'                  => 'PHP Info',
    'php_overview'              => 'PHP',
    'php_overview_keywords'     => 'phpinfo, system, info',
    'php_overview_help'         => 'PHP System info',
    'php_gd_info'               => 'Rhaid gossod php-gd i weld codau QR, gweler y canllaawiau gosod.',
    'php_gd_warning'            => 'NID yw PHP IMage Processing a\'r plugin GD wedi osod.',
    'pwd_secure_complexity'     => 'Cymhlethdod Cyfrineiriau',
    'pwd_secure_complexity_help' => 'Dewisiwch y rheolau cymlethdod cyfrineiriau sydd ei angen.',
    'pwd_secure_complexity_disallow_same_pwd_as_user_fields' => 'Password cannot be the same as first name, last name, email, or username',
    'pwd_secure_complexity_letters' => 'Require at least one letter',
    'pwd_secure_complexity_numbers' => 'Require at least one number',
    'pwd_secure_complexity_symbols' => 'Require at least one symbol',
    'pwd_secure_complexity_case_diff' => 'Require at least one uppercase and one lowercase',
    'pwd_secure_min'            => 'Lleiafswm o cymeriadau mewn cyfrinair',
    'pwd_secure_min_help'       => 'Gwerth lleiaf a dderbynir yw 8',
    'pwd_secure_uncommon'       => 'Nadu cyfrineiriau cyffredin',
    'pwd_secure_uncommon_help'  => 'Fydd hyn yn nadu defnyddwyr rhag defnyddio\'r 10,000 o cyfrineiriau sydd wedi adnabod yn rhan o digwyddiadau siber.',
    'qr_help'                   => 'Alluogwch QR codes cyntaf er mwyn gosod hyn',
    'qr_text'                   => 'Testun Cod QR',
    'saml'                      => 'SAML',
    'saml_title'                => 'Update SAML settings',
    'saml_help'                 => 'SAML settings',
    'saml_enabled'              => 'SAML enabled',
    'saml_integration'          => 'SAML Integration',
    'saml_sp_entityid'          => 'Entity ID',
    'saml_sp_acs_url'           => 'Assertion Consumer Service (ACS) URL',
    'saml_sp_sls_url'           => 'Single Logout Service (SLS) URL',
    'saml_sp_x509cert'          => 'Public Certificate',
    'saml_sp_metadata_url'      => 'Metadata URL',
    'saml_idp_metadata'         => 'SAML IdP Metadata',
    'saml_idp_metadata_help'    => 'You can specify the IdP metadata using a URL or XML file.',
    'saml_attr_mapping_username' => 'Attribute Mapping - Username',
    'saml_attr_mapping_username_help' => 'NameID will be used if attribute mapping is unspecified or invalid.',
    'saml_forcelogin_label'     => 'SAML Force Login',
    'saml_forcelogin'           => 'Make SAML the primary login',
    'saml_forcelogin_help'      => 'You can use \'/login?nosaml\' to get to the normal login page.',
    'saml_slo_label'            => 'SAML Single Log Out',
    'saml_slo'                  => 'Send a LogoutRequest to IdP on Logout',
    'saml_slo_help'             => 'This will cause the user to be first redirected to the IdP on logout. Leave unchecked if the IdP doesn\'t correctly support SP-initiated SAML SLO.',
    'saml_custom_settings'      => 'SAML Custom Settings',
    'saml_custom_settings_help' => 'You can specify additional settings to the onelogin/php-saml library. Use at your own risk.',
    'saml_download'             => 'Download Metadata',
    'setting'                   => 'Gosodiad',
    'settings'                  => 'Gosodiadau',
    'show_alerts_in_menu'       => 'Dangos rhybuddion yn y dewislen',
    'show_archived_in_list'     => 'Eitemau wedi eu harchifio',
    'show_archived_in_list_text'     => 'Dangos asedau sydd wedi\'i archifio yn "holl asedau"',
    'show_assigned_assets'      => 'Show assets assigned to assets',
    'show_assigned_assets_help' => 'Display assets which were assigned to the other assets in View User -> Assets, View User -> Info -> Print All Assigned and in Account -> View Assigned Assets.',
    'show_images_in_email'     => 'Dangos lluniau mewn ebyst',
    'show_images_in_email_help'   => 'Tynnwch y tic or bocs yma os yw eich copi o Snipe-IT tu ol i VPN neu o fewn rhwydwaith caedig os ni fydd yn bosib i defnyddwyr gweld lluniau yn ebyst o\'r system yma.',
    'site_name'                 => 'Enw Safle',
    'integrations'               => 'Integrations',
    'slack'                     => 'Slack',
    'general_webhook'           => 'General Webhook',
    'ms_teams'                  => 'Microsoft Teams',
    'webhook'                   => ':app',
    'webhook_presave'           => 'Test to Save',
    'webhook_title'               => 'Update Webhook Settings',
    'webhook_help'                => 'Integration settings',
    'webhook_botname'             => ':app Botname',
    'webhook_channel'             => ':app Channel',
    'webhook_endpoint'            => ':app Endpoint',
    'webhook_integration'         => ':app Settings',
    'webhook_test'                 =>'Test :app integration',
    'webhook_integration_help'    => ':app integration is optional, however the endpoint and channel are required if you wish to use it. To configure :app integration, you must first <a href=":webhook_link" target="_new" rel="noopener">create an incoming webhook</a> on your :app account. Click on the <strong>Test :app Integration</strong> button to confirm your settings are correct before saving. ',
    'webhook_integration_help_button'    => 'Once you have saved your :app information, a test button will appear.',
    'webhook_test_help'           => 'Test whether your :app integration is configured correctly. YOU MUST SAVE YOUR UPDATED :app SETTINGS FIRST.',
    'snipe_version'  			=> 'Fersiwn Snipe-IT',
    'support_footer'            => 'Cefnogi lincs ar waelod tudalenau ',
    'support_footer_help'       => 'Nodi pwy sydd yn gallu gweld y wybodaeth cefnogi ar canllaw defnyddwyr',
    'version_footer'            => 'Fersiwn ar waelod tudalen ',
    'version_footer_help'       => 'Nodi pwy sydd yn cael gweld fersiwn Snipe-IT.',
    'system'                    => 'Gwybodaeth System',
    'update'                    => 'Diweddaru Gosodiadau',
    'value'                     => 'Gwerth',
    'brand'                     => 'Brandio',
    'brand_keywords'            => 'footer, logo, print, theme, skin, header, colors, color, css',
    'brand_help'                => 'Logo, Enw\'r Wefan',
    'web_brand'                 => 'Web Branding Type',
    'about_settings_title'      => 'Amdan Gosodiadau',
    'about_settings_text'       => 'Mae\'r gosodiadau yma yn caniatau i chi addasu elfennau o\'r system.',
    'labels_per_page'           => 'Labeli fesul tudalen',
    'label_dimensions'          => 'Maint labeli (modfedd)',
    'next_auto_tag_base'        => 'Rhif unigryw awtomatig nesaf',
    'page_padding'              => 'Maint tudalen (modfedd)',
    'privacy_policy_link'       => 'Linc i\'r polisi preifatrwydd',
    'privacy_policy'            => 'Polisi preifatrwydd',
    'privacy_policy_link_help'  => 'Os yw URL wedi\'i gynnwys yma, bydd dolen i\'ch polisi preifatrwydd yn cael ei chynnwys yn nhroedyn yr ap ac mewn unrhyw negeseuon e-bost y mae\'r system yn eu hanfon, yn unol â GDPR. ',
    'purge'                     => 'Clirio cofnodion sydd wedi\'i dileu',
    'purge_deleted'             => 'Purge Deleted ',
    'labels_display_bgutter'    => 'Label gwaelod',
    'labels_display_sgutter'    => 'Label ochor',
    'labels_fontsize'           => 'Maint ffont label',
    'labels_pagewidth'          => 'Lled tudalen label',
    'labels_pageheight'         => 'Uchder tudalen label',
    'label_gutters'        => 'Bylchau labelau (modfedd)',
    'page_dimensions'        => 'Maint tudalen (modfedd)',
    'label_fields'          => 'Meysydd weledol labelau',
    'inches'        => 'modfedd',
    'width_w'        => 'll',
    'height_h'        => 'u',
    'show_url_in_emails'                => 'Linc i Snipe-IT mewn ebyst',
    'show_url_in_emails_help_text'      => 'Tynnwch y tic or bocs yma os nad ydych angen linc yn ol i\'r system Snipe-IT yr waelod ebost. O defnydd os nad yw eich defnyddwyr yn mewngofnodi. ',
    'text_pt'        => 'pt',
    'thumbnail_max_h'   => 'Uchder fwyaf thumbnail',
    'thumbnail_max_h_help'   => 'Uchafswm uchder mewn pixels gellith thumbnail ymddangos yn listings view. Lleiaf 25, fwyaf 500.',
    'two_factor'        => 'Dilysu Dau Ffactor',
    'two_factor_secret'        => 'Cod dilysiant dau factor',
    'two_factor_enrollment'        => 'Ymrestru dau factor',
    'two_factor_enabled_text'        => 'Alluogi dwy factor',
    'two_factor_reset'        => 'Ailosod cyfrinair dwy factor',
    'two_factor_reset_help'        => 'This will force the user to enroll their device with their authenticator app again. This can be useful if their currently enrolled device is lost or stolen. ',
    'two_factor_reset_success'          => 'Dyfais dwy factor wedi\'i ail osod yn llwyddiannus',
    'two_factor_reset_error'          => 'Wedi methu ailosod dyfais dilysaint dau-factor',
    'two_factor_enabled_warning'        => 'Bydd galluogi dau ffactor os nad yw wedi\'i alluogi ar hyn o bryd yn eich gorfodi ar unwaith i ddilysu gyda dyfais sydd wedi\'i chofrestru gan Google Auth. Bydd gennych y gallu i gofrestru\'ch dyfais os nad yw un wedi\'i gofrestru ar hyn o bryd.',
    'two_factor_enabled_help'        => 'Bydd hyn yn troi dilysiad dau ffactor ymlaen gan ddefnyddio Google Authenticator.',
    'two_factor_optional'        => 'Dewisol (Gall defnyddwyr alluogi neu analluogi os caniateir)',
    'two_factor_required'        => 'Angen ar gyfer holl defnyddwyr',
    'two_factor_disabled'        => 'Analluogi',
    'two_factor_enter_code'	=> 'Mewnbynwch Cod dilysiant dau factor',
    'two_factor_config_complete'	=> 'Cyflwyno côd',
    'two_factor_enabled_edit_not_allowed' => 'Nid yw eich gweinyddwr yn caniatáu ichi olygu\'r gosodiad hwn.',
    'two_factor_enrollment_text'	=> "Mae angen dilysu dau ffactor, ond nid yw'ch dyfais wedi'i chofrestru eto. Agorwch eich app Google Authenticator a sganiwch y cod QR isod i gofrestru'ch dyfais. Ar ôl i chi gofrestru'ch dyfais, nodwch y cod isod",
    'require_accept_signature'      => 'Angen Llofnod',
    'require_accept_signature_help_text'      => 'Bydd galluogi\'r nodwedd hon yn ei gwneud yn ofynnol i ddefnyddwyr lofnodi\'n gorfforol wrth dderbyn ased.',
    'left'        => 'chwith',
    'right'        => 'dde',
    'top'        => 'top',
    'bottom'        => 'gwaelod',
    'vertical'        => 'fertigol',
    'horizontal'        => 'llorweddol',
    'unique_serial'                => 'Rhifau serial unigryw',
    'unique_serial_help_text'                => 'Bydd gwirio\'r blwch hwn yn gorfodi cyfyngiad unigryw ar gyfresi asedau',
    'zerofill_count'        => 'Hyd y tagiau asedau, gan gynnwys zerofill',
    'username_format_help'   => 'Dim ond os na ddarperir enw defnyddiwr y bydd y gosodiad hwn yn cael ei ddefnyddio a bod yn rhaid i ni gynhyrchu enw defnyddiwr i chi.',
    'oauth_title' => 'OAuth API Settings',
    'oauth_clients' => 'OAuth Clients',
    'oauth' => 'OAuth',
    'oauth_help' => 'Oauth Endpoint Settings',
    'oauth_no_clients' => 'You have not created any OAuth clients yet.',
    'oauth_secret' => 'Secret',
    'oauth_authorized_apps' => 'Authorized Applications',
    'oauth_redirect_url' => 'Redirect URL',
    'oauth_name_help' => ' Something your users will recognize and trust.',
    'oauth_scopes' => 'Scopes',
    'oauth_callback_url' => 'Your application authorization callback URL.',
    'create_client' => 'Create Client',
    'no_scopes' => 'No scopes',
    'asset_tag_title' => 'Update Asset Tag Settings',
    'barcode_title' => 'Update Barcode Settings',
    'barcodes' => 'Barcodes',
    'barcodes_help_overview' => 'Barcode &amp; QR settings',
    'barcodes_help' => 'This will attempt to delete cached barcodes. This would typically only be used if your barcode settings have changed, or if your Snipe-IT URL has changed. Barcodes will be re-generated when accessed next.',
    'barcodes_spinner' => 'Attempting to delete files...',
    'barcode_delete_cache' => 'Delete Barcode Cache',
    'branding_title' => 'Update Branding Settings',
    'general_title' => 'Update General Settings',
    'mail_test' => 'Danfon Profiad',
    'mail_test_help' => 'This will attempt to send a test mail to :replyto.',
    'filter_by_keyword' => 'Filter by setting keyword',
    'security' => 'Diogelwch',
    'security_title' => 'Update Security Settings',
    'security_keywords' => 'password, passwords, requirements, two factor, two-factor, common passwords, remote login, logout, authentication',
    'security_help' => 'Two-factor, Password Restrictions',
    'groups_keywords' => 'permissions, permission groups, authorization',
    'groups_help' => 'Account permission groups',
    'localization' => 'Localization',
    'localization_title' => 'Update Localization Settings',
    'localization_keywords' => 'localization, currency, local, locale, time zone, timezone, international, internatinalization, language, languages, translation',
    'localization_help' => 'Language, date display',
    'notifications' => 'Notifications',
    'notifications_help' => 'Email Alerts & Audit Settings',
    'asset_tags_help' => 'Incrementing and prefixes',
    'labels' => 'Labelau',
    'labels_title' => 'Update Label Settings',
    'labels_help' => 'Label sizes &amp; settings',
    'purge_keywords' => 'permanently delete',
    'purge_help' => 'Clirio cofnodion sydd wedi\'i dileu',
    'ldap_extension_warning' => 'It does not look like the LDAP extension is installed or enabled on this server. You can still save your settings, but you will need to enable the LDAP extension for PHP before LDAP syncing or login will work.',
    'ldap_ad' => 'LDAP/AD',
    'employee_number' => 'Employee Number',
    'create_admin_user' => 'Create a User ::',
    'create_admin_success' => 'Success! Your admin user has been added!',
    'create_admin_redirect' => 'Click here to go to your app login!',
    'setup_migrations' => 'Database Migrations ::',
    'setup_no_migrations' => 'There was nothing to migrate. Your database tables were already set up!',
    'setup_successful_migrations' => 'Your database tables have been created',
    'setup_migration_output' => 'Migration output:',
    'setup_migration_create_user' => 'Next: Create User',
    'ldap_settings_link' => 'LDAP Settings Page',
    'slack_test' => 'Test <i class="fab fa-slack"></i> Integration',
    'label2_enable'           => 'New Label Engine',
    'label2_enable_help'      => 'Switch to the new label engine. <b>Note: You will need to save this setting before setting others.</b>',
    'label2_template'         => 'Template',
    'label2_template_help'    => 'Select which template to use for label generation',
    'label2_title'            => 'Teitl',
    'label2_title_help'       => 'The title to show on labels that support it',
    'label2_title_help_phold' => 'The placeholder <code>{COMPANY}</code> will be replaced with the asset&apos;s company name',
    'label2_asset_logo'       => 'Use Asset Logo',
    'label2_asset_logo_help'  => 'Use the logo of the asset&apos;s assigned company, rather than the value at <code>:setting_name</code>',
    'label2_1d_type'          => '1D Barcode Type',
    'label2_1d_type_help'     => 'Format for 1D barcodes',
    'label2_2d_type'          => 'Math Barcode 2D',
    'label2_2d_type_help'     => 'Format for 2D barcodes',
    'label2_2d_target'        => '2D Barcode Target',
    'label2_2d_target_help'   => 'The URL the 2D barcode points to when scanned',
    'label2_fields'           => 'Field Definitions',
    'label2_fields_help'      => 'Fields can be added, removed, and reordered in the left column. For each field, multiple options for Label and DataSource can be added, removed, and reordered in the right column.',
    'help_asterisk_bold'    => 'Text entered as <code>**text**</code> will be displayed as bold',
    'help_blank_to_use'     => 'Leave blank to use the value from <code>:setting_name</code>',
    'help_default_will_use' => '<code>:default</code> will use the value from <code>:setting_name</code>. <br>Note that the value of the barcodes must comply with the respective barcode spec in order to be successfully generated. Please see <a href="https://snipe-it.readme.io/docs/barcodes">the documentation <i class="fa fa-external-link"></i></a> for more details. ',
    'default'               => 'Default',
    'none'                  => 'None',
    'google_callback_help' => 'This should be entered as the callback URL in your Google OAuth app settings in your organization&apos;s <strong><a href="https://console.cloud.google.com/" target="_blank">Google developer console <i class="fa fa-external-link" aria-hidden="true"></i></a></strong>.',
    'google_login'      => 'Google Workspace Login Settings',
    'enable_google_login'  => 'Enable users to login with Google Workspace',
    'enable_google_login_help'  => 'Users will not be automatically provisioned. They must have an existing account here AND in Google Workspace, and their username here must match their Google Workspace email address. ',
    'mail_reply_to' => 'Mail Reply-To Address',
    'mail_from' => 'Mail From Address',
    'database_driver' => 'Database Driver',
    'bs_table_storage' => 'Table Storage',
    'timezone' => 'Timezone',
    'profile_edit'          => 'Edit Profile',
    'profile_edit_help'          => 'Allow users to edit their own profiles.',
    'default_avatar' => 'Upload default avatar',

];
