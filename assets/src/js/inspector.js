import includes from 'lodash/includes';
/* global wp */

(function (plugins, editPost, element, components, data, compose) {
    const el = element.createElement;

    const { __ } = wp.i18n;
    const { Fragment } = element;
    const { registerPlugin } = plugins;
    const { PluginDocumentSettingPanel, PluginPostStatusInfo } = editPost;
    const { CheckboxControl, SelectControl, TextControl, TextareaControl, ToggleControl, __experimentalHStack, __experimentalVStack } = components;
    const { select, useSelect, withSelect, withDispatch } = data;

    const usePostTypes = () => {
        const postTypes = useSelect((select) => {
            return select('core').getPostTypes({ per_page: -1 });
        }, []);

        if (!postTypes) {
            return [];
        }

        return postTypes.filter((type) => type.viewable && type.slug !== 'attachment').map((type) => type.slug);
    };

    const MetaTextareaControl = compose.compose(
        withDispatch(function (dispatch, props) {
            return {
                setMetaValue: function (metaValue) {
                    dispatch('core/editor').editPost({ meta: { [props.metaKey]: metaValue } });
                },
            };
        }),
        withSelect(function (select, props) {
            return {
                metaValue: select('core/editor').getEditedPostAttribute('meta')[props.metaKey],
            };
        }),
    )(function (props) {
        console.log('props', props);

        return el(TextareaControl, {
            label: props.title,
            value: props.metaValue,
            onChange: function (content) {
                props.setMetaValue(content);
            },
            checked: props.metaValue,
            help: props.help,
        });
    });

    const MetaToggleControl = compose.compose(
        withDispatch((dispatch, props) => {
            return {
                setMetaValue: (metaValue) => {
                    dispatch('core/editor').editPost({
                        meta: { [props.metaKey]: metaValue },
                    });
                },
            };
        }),
        withSelect((select, props) => {
            return {
                metaValue: select('core/editor').getEditedPostAttribute('meta')[props.metaKey],
            };
        }),
    )((props) => {
        return el(ToggleControl, {
            label: props.title,
            value: props.metaValue,
            onChange: (content) => props.setMetaValue(content),
            checked: props.metaValue,
            help: props.help,
        });
    });

    const MetaLanguagesProvidedControl = compose.compose(
        withDispatch((dispatch, props) => {
            return {
                setMetaValue: (metaValue) => {
                    dispatch('core/editor').editPost({
                        meta: { [props.metaKey]: metaValue },
                    });
                },
            };
        }),
        withSelect((select, props) => {
            const languagesMap = select('pll/metabox').getLanguages();
            console.log('languagesMap:', languagesMap);

            const languages = Array.from(languagesMap.entries()).map(([slug, language]) => ({
                slug,
                ...language,
            }));

            console.log('languages:', languages);

            return {
                metaValue: select('core/editor').getEditedPostAttribute('meta')[props.metaKey] || [],
                languages: languages,
            };
        }),
    )((props) => {
        const { metaValue, setMetaValue, languages } = props;

        const handleCheckboxChange = (languageCode) => {
            const newMetaValue = metaValue.includes(languageCode) ? metaValue.filter((code) => code !== languageCode) : [...metaValue, languageCode];
            setMetaValue(newMetaValue);
        };

        return languages.map((language) =>
            el(CheckboxControl, {
                key: language.slug,
                label: language.name,
                checked: metaValue.includes(language.slug),
                onChange: () => handleCheckboxChange(language.slug),
            }),
        );
    });

    // スキーマタイプの選択肢
    const schemaTypes = [
        { label: 'デフォルト', value: '' },
        { label: '標準ページ', value: 'WebPage' },
        { label: '会社概要', value: 'AboutPage' },
        { label: 'お問い合わせ', value: 'ContactPage' },
        { label: 'ニュース記事', value: 'NewsArticle' },
        { label: 'ブログ記事', value: 'BlogPosting' },
        { label: '一覧ページ', value: 'CollectionPage' },
    ];

    // 会社情報フィールドの選択肢
    const companyFields = [
        { key: 'postalCode', label: '郵便番号' },
        { key: 'addressRegion', label: '都道府県' },
        { key: 'addressLocality', label: '市区町村' },
        { key: 'streetAddress', label: '住所' },
        { key: 'foundingDate', label: '設立日' },
        { key: 'addressCountry', label: '国名' },
    ];

    // 連絡先フィールドの選択肢
    const contactFields = [
        { key: 'telephone', label: '電話番号' },
        { key: 'email', label: 'メールアドレス' },
        { key: 'contactType', label: 'コンタクトタイプ' },
        { key: 'availableLanguage', label: '対応言語' },
        { key: 'hoursAvailable', label: '営業時間' },
    ];

    const MetaSchemaTypeControl = compose.compose(
        withDispatch((dispatch, props) => {
            return {
                setMetaValue: (metaValue) => {
                    dispatch('core/editor').editPost({
                        meta: { [props.metaKey]: metaValue },
                    });
                },
            };
        }),
        withSelect((select, props) => {
            return {
                metaValue: select('core/editor').getEditedPostAttribute('meta')[props.metaKey],
            };
        }),
    )((props) => {
        return el(
            'div',
            {},
            el(SelectControl, {
                label: props.title,
                value: props.metaValue || '',
                options: schemaTypes,
                onChange: (content) => props.setMetaValue(content),
            }),
            el(
                'div',
                {
                    style: {
                        marginTop: '16px',
                    },
                },
                props.metaValue === 'AboutPage' &&
                    companyFields.map((field) =>
                        el(MetaSchemaFieldControl, {
                            key: field.key,
                            metaKey: `_wpf_company_${field.key}`,
                            title: __(field.label, 'wordpressfoundation'),
                        }),
                    ),
                props.metaValue === 'ContactPage' &&
                    contactFields.map((field) =>
                        el(MetaSchemaFieldControl, {
                            key: field.key,
                            metaKey: `_wpf_contact_${field.key}`,
                            title: __(field.label, 'wordpressfoundation'),
                        }),
                    ),
            ),
        );
    });

    const MetaSchemaFieldControl = compose.compose(
        withDispatch((dispatch, props) => {
            return {
                setMetaValue: (metaValue) => {
                    dispatch('core/editor').editPost({
                        meta: { [props.metaKey]: metaValue },
                    });
                },
            };
        }),
        withSelect((select, props) => {
            return {
                metaValue: select('core/editor').getEditedPostAttribute('meta')[props.metaKey],
            };
        }),
    )((props) => {
        return el(TextControl, {
            label: props.title,
            value: props.metaValue || '',
            onChange: (content) => props.setMetaValue(content),
            help: props.help,
        });
    });

    registerPlugin('wpf-inspector-languages-provided', {
        icon: false,
        render: () => {
            const allowedPostTypes = usePostTypes();
            const postType = select('core/editor').getCurrentPostType();

            if (!includes(allowedPostTypes, postType)) {
                return el(Fragment, {});
            }

            // Polylangの可用性をチェック
            const polylangStore = select('pll/metabox');
            const isPolylangAvailable = polylangStore && typeof polylangStore.getLanguages === 'function';

            if (!isPolylangAvailable) {
                // Polylangが利用できない場合は何も表示しない
                return el(Fragment, {});
            }

            return el(
                Fragment,
                {},
                el(
                    PluginPostStatusInfo,
                    {},
                    el(
                        __experimentalVStack,
                        {
                            style: {
                                marginBlockStart: '8px',
                            },
                        },
                        el(
                            __experimentalHStack,
                            {},
                            el(
                                'div',
                                {
                                    className: 'editor-post-panel__row-label',
                                },
                                __('提供言語', 'wordpressfoundation'),
                            ),
                            el(
                                __experimentalVStack,
                                {
                                    className: 'editor-post-panel__row-control',
                                },
                                el(MetaLanguagesProvidedControl, {
                                    metaKey: '_wpf_languages_provided',
                                }),
                            ),
                        ),
                        el(
                            'p',
                            {
                                style: {
                                    marginTop: '8px',
                                    color: 'rgb(117, 117, 117)',
                                    fontSize: '12px',
                                },
                            },
                            __('ヒント: 特定の言語のみを提供しますか？', 'wordpressfoundation'),
                            el(
                                'a',
                                {
                                    href: 'https://example.com/sync-settings', // リンク先URLを指定
                                    target: '_blank',
                                    rel: 'noopener noreferrer',
                                },
                                __('コンテンツを同期するように設定', 'wordpressfoundation'),
                            ),
                            __('すると便利です。', 'wordpressfoundation'),
                        ),
                    ),
                ),
            );
        },
    });

    registerPlugin('wpf-inspector-subtitle', {
        icon: false,
        render: function () {
            const allowedPostTypes = usePostTypes();
            const postType = select('core/editor').getCurrentPostType();

            if (!includes(allowedPostTypes, postType)) {
                return el(Fragment, {});
            }

            return el(
                Fragment,
                {},
                el(
                    PluginPostStatusInfo,
                    {},
                    el(
                        __experimentalVStack,
                        {
                            style: {
                                flexGrow: 1,
                            },
                        },
                        el(MetaTextareaControl, {
                            metaKey: '_wpf_subtitle',
                            title: __('サブタイトル', 'wordpressfoundation'),
                        }),
                    ),
                ),
            );
        },
    });

    registerPlugin('wpf-show-toc-setting', {
        icon: false,
        render: () => {
            const allowedPostTypes = usePostTypes();
            const postType = select('core/editor').getCurrentPostType();

            if (!includes(allowedPostTypes, postType)) {
                return el(Fragment, {});
            }

            return el(
                Fragment,
                {},
                el(
                    PluginPostStatusInfo,
                    {},
                    el(
                        __experimentalVStack,
                        {
                            style: {
                                flexGrow: 1,
                                marginBlockStart: '8px',
                            },
                        },
                        el(MetaToggleControl, {
                            metaKey: '_wpf_show_toc',
                            title: __('目次を表示', 'wordpressfoundation'),
                            help: __('この投稿の目次を表示する場合はオンにします。', 'wordpressfoundation'),
                        }),
                    ),
                ),
            );
        },
    });

    registerPlugin('wpf-hide-search-engine-setting', {
        icon: false,
        render: () => {
            const allowedPostTypes = usePostTypes();
            const postType = select('core/editor').getCurrentPostType();

            if (!includes(allowedPostTypes, postType)) {
                return el(Fragment, {});
            }

            return el(
                Fragment,
                {},
                el(
                    PluginPostStatusInfo,
                    {},
                    el(
                        __experimentalVStack,
                        {
                            style: {
                                flexGrow: 1,
                                marginBlockStart: '8px',
                            },
                        },
                        el(MetaToggleControl, {
                            metaKey: '_wpf_hide_search_engine',
                            title: __('検索結果に表示しない', 'wordpressfoundation'),
                            help: __('この投稿を検索エンジンにインデックスさせたくない場合はオンにします。', 'wordpressfoundation'),
                        }),
                    ),
                ),
            );
        },
    });

    registerPlugin('schema-settings', {
        icon: false,
        render: () => {
            const allowedPostTypes = usePostTypes();
            const postType = select('core/editor').getCurrentPostType();

            if (!includes(allowedPostTypes, postType)) {
                return el(Fragment, {});
            }

            return el(
                Fragment,
                {},
                el(
                    PluginDocumentSettingPanel,
                    {
                        name: 'schema-settings',
                        title: __('Schema.org', 'wordpressfoundation'),
                    },
                    el(
                        __experimentalVStack,
                        {
                            style: {
                                flexGrow: 1,
                            },
                        },
                        el(MetaSchemaTypeControl, {
                            metaKey: '_wpf_schema_type',
                            title: __('スキーマタイプ', 'wordpressfoundation'),
                        }),
                    ),
                ),
            );
        },
    });
})(window.wp.plugins, window.wp.editPost, window.wp.element, window.wp.components, window.wp.data, window.wp.compose);
