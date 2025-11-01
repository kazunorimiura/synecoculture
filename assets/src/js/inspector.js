import includes from 'lodash/includes';
/* global wp */

(function (plugins, editPost, element, components, data, compose, blockEditor) {
    const el = element.createElement;

    const { __, sprintf } = wp.i18n;
    const { Fragment, useState, useEffect } = element;
    const { registerPlugin } = plugins;
    const { PluginDocumentSettingPanel, PluginPostStatusInfo } = editPost;
    const { Button, CheckboxControl, DropZone, FormTokenField, SearchControl, SelectControl, Spinner, TextControl, TextareaControl, ToggleControl, __experimentalHStack, __experimentalVStack } = components;
    const { select, useSelect, withSelect, withDispatch } = data;
    const { decodeEntities } = wp.htmlEntities;
    const { MediaUpload, MediaUploadCheck } = blockEditor;

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

    const MetaCoverMediaUpload = compose.compose(
        withDispatch(function (dispatch) {
            return {
                setMetaValue: function (id, metaData) {
                    dispatch('core/editor').editPost({ meta: { _wpf_cover_media_id: id, _wpf_cover_media_metadata: metaData } });
                },
            };
        }),
        withSelect(function (select) {
            return {
                coverMediaId: select('core/editor').getEditedPostAttribute('meta')['_wpf_cover_media_id'],
                coverMediaMetadata: select('core/editor').getEditedPostAttribute('meta')['_wpf_cover_media_metadata'],
            };
        }),
    )(function (props) {
        console.log('props', props);

        const onSelectImage = (media) => {
            props.setMetaValue(media.id, { type: media.type, mime: media.mime, url: media.url });

            // video要素をロードしてサムネイルを表示させる
            // 参考: https://stackoverflow.com/a/32215374
            const videoEl = document.querySelector('.wpf-cover-media-video');
            if (videoEl) {
                videoEl.load();
            }
        };

        return el(
            MediaUploadCheck,
            {},
            el(MediaUpload, {
                help: props.help,
                value: props.coverMediaMetadata.url,
                onSelect: onSelectImage,
                type: ['image', 'video'],
                render: function (obj) {
                    return el(
                        'div',
                        {
                            style: {
                                display: 'flex',
                                gap: '1rem',
                                flexDirection: 'column',
                            },
                        },
                        el(
                            'div',
                            { className: 'components-base-control editor-post-featured-image' },
                            el(
                                'label',
                                {
                                    style: {
                                        fontSize: '11px',
                                        marginBottom: '8px',
                                        display: 'inline-block',
                                        lineHeight: 1.4,
                                    },
                                },
                                props.title,
                            ),
                            el(
                                'div',
                                {
                                    className: 'editor-post-featured-image__container',
                                },
                                !props.coverMediaId &&
                                    el(
                                        Button,
                                        {
                                            className: 'components-button editor-post-featured-image__toggle',
                                            onClick: obj.open,
                                        },
                                        sprintf(__('%sを設定', 'wordpressfoundation'), props.title),
                                    ),
                                !!props.coverMediaId &&
                                    el(
                                        Button,
                                        {
                                            className: 'components-button editor-post-featured-image__preview',
                                            'aria-describedby': `editor-post-cover-image-${props.coverMediaId}-describedby`,
                                            onClick: obj.open,
                                            'aria-label': '画像を編集または更新',
                                        },
                                        el(
                                            'span',
                                            {
                                                className: 'components-responsive-wrapper',
                                            },
                                            el('div', {
                                                'aria-hidden': true,
                                                style: {
                                                    position: 'absolute',
                                                    inset: '0px',
                                                    pointerEvents: 'none',
                                                    opacity: 0,
                                                    overflow: 'hidden',
                                                    zIndex: -1,
                                                },
                                            }),
                                            el('span', {
                                                style: {
                                                    paddingBottom: '56.25%',
                                                },
                                            }),
                                            'video' === props.coverMediaMetadata.type &&
                                                el(
                                                    'video',
                                                    {
                                                        className: 'components-responsive-wrapper__content wpf-cover-media-video',
                                                    },
                                                    el('source', {
                                                        src: props.coverMediaMetadata.url,
                                                        type: props.coverMediaMetadata.mime,
                                                    }),
                                                ),
                                            'image' === props.coverMediaMetadata.type &&
                                                el('img', {
                                                    src: props.coverMediaMetadata.url,
                                                    className: 'components-responsive-wrapper__content',
                                                }),
                                        ),
                                    ),
                                el(DropZone, {}),
                            ),
                            !!props.coverMediaId &&
                                el(
                                    Button,
                                    {
                                        className: 'components-button is-secondary',
                                        onClick: obj.open,
                                        style: {
                                            marginTop: '8px',
                                        },
                                    },
                                    sprintf(__('%sを置換', 'wordpressfoundation'), props.title),
                                ),
                            !!props.coverMediaId &&
                                el(
                                    Button,
                                    {
                                        className: 'components-button is-link is-destructive',
                                        style: {
                                            marginTop: '8px',
                                        },
                                        onClick: () => {
                                            props.setMetaValue(0, {});
                                        },
                                    },
                                    sprintf(__('%sを削除', 'wordpressfoundation'), props.title),
                                ),
                        ),
                    );
                },
            }),
        );
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
                                    href: 'https://www.dropbox.com/scl/fi/bc83fpcj2u7li5rz1j5te/sync.png?rlkey=vxeg00pprzk14z9ag480zte14&st=zf6m2apm&dl=0', // リンク先URLを指定
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

    registerPlugin('wpf-inspector-flag-settings', {
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
                    {
                        name: 'wpf-inspector-flag-settings',
                        icon: false,
                        title: __('フラグ', 'wordpressfoundation'),
                    },
                    el(MetaToggleControl, {
                        metaKey: '_wpf_pickup_flag',
                        title: __('Pickup', 'wordpressfoundation'),
                        help: __('この投稿をPickupコンテンツに設定する場合はONにします。', 'wordpressfoundation'),
                    }),
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

            // 投稿タイプを限定
            const keepTypes = ['post', 'page', 'blog', 'manual', 'case-study'];
            allowedPostTypes.splice(0, allowedPostTypes.length, ...allowedPostTypes.filter((type) => keepTypes.includes(type)));

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

    registerPlugin('page-cover', {
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
                    PluginDocumentSettingPanel,
                    {
                        name: 'page-cover',
                        icon: false,
                        title: __('カバー設定', 'wordpressfoundation'),
                    },
                    el(MetaCoverMediaUpload, {
                        title: __('カバー画像（または動画）', 'wordpressfoundation'),
                    }),
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

    const PostsList = ({ postType, orderby, order, searchTerm, metaValue, setMetaValue, allPosts, setAllPosts }) => {
        const [page, setPage] = useState(1);
        const postsPerPage = 12;

        const { posts, hasResolvedPosts, totalPages, isLoading } = useSelect(
            (select) => {
                const query = {
                    per_page: postsPerPage,
                    parent: 0,
                    orderby: orderby,
                    order: order,
                    page: page,
                };
                if (searchTerm) {
                    query.search = searchTerm;
                }

                return {
                    posts: select('core').getEntityRecords('postType', postType, query),
                    hasResolvedPosts: select('core').hasFinishedResolution('getEntityRecords', ['postType', postType, query]),
                    totalPages: select('core').getEntityRecordsTotalPages('postType', postType, query),
                    isLoading: !select('core').hasFinishedResolution('getEntityRecords', ['postType', postType, query]),
                };
            },
            [searchTerm, page], // metaValueを削除
        );

        useEffect(() => {
            if (hasResolvedPosts && posts) {
                if (page === 1) {
                    setAllPosts(posts);
                } else {
                    setAllPosts((prevPosts) => [...prevPosts, ...posts]);
                }
            }
        }, [posts, hasResolvedPosts]);

        useEffect(() => {
            setPage(1);
        }, [searchTerm]);

        if (!hasResolvedPosts && page === 1) {
            return el(Spinner);
        }

        if (!allPosts.length) {
            return el('div', {}, __('No results', 'wordpressfoundation'));
        }

        // metaValueを数値の配列として扱う
        const normalizedMetaValue = metaValue.map((val) => Number(val));

        const options = allPosts.map((post) => {
            return {
                label: sprintf(__('%s', 'wordpressfoundation'), decodeEntities(post.title.rendered)),
                value: post.id,
                isChecked: normalizedMetaValue.includes(post.id),
            };
        });

        function onChange(postId) {
            const updatedMetaValue = [...normalizedMetaValue];

            if (updatedMetaValue.includes(postId)) {
                const valueIndex = updatedMetaValue.indexOf(postId);
                updatedMetaValue.splice(valueIndex, 1);
            } else {
                updatedMetaValue.push(postId);
            }

            // 文字列の配列として保存
            setMetaValue(updatedMetaValue.map(String));
        }

        const checkboxes = options.map((option) => {
            return el(CheckboxControl, {
                onChange: () => onChange(option.value), // indexではなくpost.idを直接渡す
                label: option.label,
                key: option.value,
                checked: option.isChecked,
            });
        });

        return el(
            'div',
            {},
            checkboxes,
            posts &&
                totalPages &&
                page < totalPages &&
                el(
                    Button,
                    {
                        isSecondary: true,
                        onClick: () => setPage(page + 1),
                        disabled: isLoading,
                    },
                    isLoading ? __('Loading...', 'wordpressfoundation') : __('Load More', 'wordpressfoundation'),
                ),
        );
    };

    const MetaPostsSelectControl = (props) => {
        const [searchTerm, setSearchTerm] = useState('');
        const [allPosts, setAllPosts] = useState([]);

        const tokens = props.metaValue.map((id) => {
            const post = allPosts.find((p) => p.id === Number(id));
            return post ? decodeEntities(post.title.rendered) : id;
        });

        return el(
            'div',
            {},
            el(FormTokenField, {
                label: __('追加済みの項目', 'wordpressfoundation'),
                value: tokens,
                displayTransform: (token) => {
                    if (!isNaN(token)) {
                        const post = select('core').getEntityRecord('postType', props.postType, token);
                        return post ? decodeEntities(post.title.rendered) : token;
                    }
                    return token;
                },
                onChange: (values) => {
                    const updatedMetaValue = props.metaValue
                        .filter((id) => {
                            const post = allPosts.find((p) => p.id === Number(id));
                            return post ? values.includes(decodeEntities(post.title.rendered)) : false;
                        })
                        .concat(values.filter((value) => !allPosts.some((p) => decodeEntities(p.title.rendered) === value)).map(String));
                    props.setMetaValue(updatedMetaValue);
                },
                __experimentalShowHowTo: false,
                __experimentalExpandOnFocus: true,
            }),
            el(SearchControl, {
                label: __('Search Pages', 'wordpressfoundation'),
                value: searchTerm,
                onChange: (value) => setSearchTerm(value),
            }),
            el(
                'div',
                {
                    className: 'editor-post-taxonomies__hierarchical-terms-list',
                    style: {
                        marginBlockStart: '0px',
                    },
                },
                el(PostsList, {
                    postType: props.postType,
                    orderby: 'orderby' in props ? props.orderby : 'date',
                    order: 'order' in props ? props.order : 'desc',
                    searchTerm: searchTerm,
                    metaValue: props.metaValue,
                    setMetaValue: props.setMetaValue,
                    allPosts: allPosts,
                    setAllPosts: setAllPosts,
                }),
            ),
        );
    };

    const MetaPostsSelectControlWrapper = compose.compose(
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
                metaValue: select('core/editor').getEditedPostAttribute('meta')[props.metaKey] || [],
            };
        }),
    )(MetaPostsSelectControl);

    registerPlugin('related-members', {
        icon: false,
        render: () => {
            const postType = select('core/editor').getCurrentPostType();
            if (!includes(['post', 'project'], postType)) {
                return el(Fragment, {});
            }
            return el(
                Fragment,
                {},
                el(
                    PluginDocumentSettingPanel,
                    {
                        name: 'related-members',
                        icon: false,
                        title: __('関連メンバー', 'wordpressfoundation'),
                    },
                    el(MetaPostsSelectControlWrapper, {
                        postType: 'member',
                        metaKey: '_wpf_related_members',
                        orderby: 'title',
                        order: 'asc',
                        title: __('メンバーを検索', 'wordpressfoundation'),
                    }),
                ),
            );
        },
    });
})(window.wp.plugins, window.wp.editPost, window.wp.element, window.wp.components, window.wp.data, window.wp.compose, window.wp.blockEditor);
