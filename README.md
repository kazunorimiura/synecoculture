
# WordPress Foundation

WordPressをコーポレートサイトとして運用するためのファンデーションテーマ。

### 機能

- 多言語化（Polylangプラグインサポート）
- SEOフレンドリー
- アクセシビリティ

### 主なサイト構成

- サイトヘッダー 
    - サイトヘッダープライマリ
    - セカンダリヘッダーが必要な場合は`site-header-ORDINAL`を追加する
- サイトフッター
    - サイトフッタープライマリ
    - セカンダリフッターが必要な場合は`site-footer-ORDINAL`を追加する
- ナビゲーション
    - プライマリ
    - プライマリ（モバイル）
    - グローバルプライマリ
    - グローバルセカンダリ
    - フッタープライマリ
    - フッターセカンダリ
    - ソーシャルリンク
- アーカイブページ
- 個別投稿ページ
- 検索結果ページ ※`/?s=KEYWORD`または`/search/KEYWORD`で表示されるページ
- （任意）著者一覧ページ
- （任意）ターム一覧ページ

## セットアップ 

**注記**  
> 以下のステップは `settings/tasks.json` の `Initial setup` タスクで自動化できます。`settings/tasks.json` 内のディレクトリパスはプロジェクトに合わせて適宜置き換えてください。

テーマディレクトリに移動。

仮想環境に接続。composerのインストールに必要な環境がローカルに揃っていればスキップしてもよい。

```bash 
vagrant ssh
```

```bash 
cd /wp-content/themes/your-theme

# 例
cd /srv/www/wordpress-foundation/public_html/wp-content/themes/wordpress-foundation
```

### composer

コンポーザーをインストール。WordPress用のPHPフォーマッター（PHP CodeSniffer）、コーディング規約（WordPress coding rules）、Yoast製ユニットテストユーティリティ（php8でテストするため）がインストールされます。

```bash 
composer install
```

コーディングルールのパスを設定。

```bash 
./vendor/bin/phpcs --config-set installed_paths vendor/wp-coding-standards/wpcs,vendor/phpcompatibility/php-compatibility/PHPCompatibility,vendor/phpcompatibility/phpcompatibility-paragonie/PHPCompatibilityParagonieRandomCompat,vendor/phpcompatibility/phpcompatibility-paragonie/PHPCompatibilityParagonieSodiumCompat,vendor/phpcompatibility/phpcompatibility-wp/PHPCompatibilityWP
```

パスの設定を確認。

```bash 
./vendor/bin/phpcs -i
```

### PHPドキュメンター

PHARファイルをダウンロード。

```bash 
wget -P vendor/bin https://phpdoc.org/phpDocumentor.phar
```

バージョン確認。

```bash 
php vendor/bin/phpDocumentor.phar -V
```

PHPドキュメント生成（手動で生成する場合。npm run buildでも生成されるようにしている）。

```bash 
php vendor/bin/phpDocumentor.phar -d ./ -t ./docs/php/ -i vendor/ -i node_modules/
```

### npm

npmをインストール。

```bash 
npm install --no-package-lock
```

プロダクションビルド。

```bash 
npm run build
```

デベロップメント。

```bash 
npm run watch
```

### ユニットテスト

※VVV環境で開発している前提

開発サーバーにログイン。

```bash 
vagrant ssh
```

テーマディレクトリに移動。

```bash 
cd /srv/www/wordpress-foundation/public_html/wp-content/themes/wordpress-foundation
```

テスト用DBをセットアップ。テストライブラリは `~/tmp` に作られるため、サーバーを起動する度に実行する。

```bash 
bash tests/bin/install-wp-tests.sh wordpress_test root root localhost
```

テストに使用するプラグインをインストール（初回のみ）。`tests/tmp` にインストールされる。

```bash 
bash tests/bin/install-plugins.sh
```

autoloadを読み込む。新しいファイルを作成したときは、composer.json の `autoload` にファイルパスを追記して、再度読み込みを実行する必要がある。

```bash 
composer dump-autoload
```

テストを実行。サンプルのテストファイルは、phpunit.xml.distの `<exclude>./tests/test-sample.php</exclude>` で除外されているため、実行したい場合は消す。

```bash 
./vendor/bin/phpunit
```

`phpunit` コマンドのみで実行できるようにするには、`.bash_profile` にエイリアスを設定する（初回のみ）。

※tasks.jsonでは、aliasを設定できない（非対話型シェルのため）ので、手動設定する必要がある。

```bash
echo "alias phpunit='/srv/www/wordpress-foundation/public_html/wp-content/themes/wordpress-foundation/vendor/bin/phpunit'" >> ~/.bash_profile

# 永続化
source ~/.bash_profile
```

ユニットテスト。`--testsuite default` のテストが実行される。

```bash 
# テーマディレクトリに移動
cd /srv/www/wordpress-foundation/public_html/wp-content/themes/wordpress-foundation

# テストを実行
phpunit
```

特定のクラスまたはメソッドのみテスト。

```bash 
# クラスを指定
phpunit tests/phpunit/tests/template-functions/TestExcludePostsFromSitemap.php
phpunit tests/phpunit/tests/template-functions/TestFilterDocumentTitleParts.php
phpunit tests/phpunit/tests/template-tags/TestGetPostTypesWithMetaValue.php
phpunit tests/phpunit/tests/utils/TestGetPageForPosts.php

# メソッドを指定
phpunit tests/phpunit/tests/utils/TestGetPageForPosts.php --filter test_get_page_for_posts_with_cpt
```

プラグインとの統合テスト。以下の例はポリランの場合。

```bash 
phpunit --testsuite polylang
```

カバレッジレポートを出力。

```bash
phpunit tests/cpt-rewrite/TestCPTRewrite.php --coverage-text
```

#### wp-envでのユニットテスト

```bash
wp-env run --env-cwd=wp-content/themes/wordpress-foundation tests-cli ./vendor/bin/phpunit
```

### E2Eテスト

#### セットアップ

playwrightのインストール。

```bash 
npx playwright install
```

#### 認証設定

管理画面にログインできるように `.env.example` をコピーして `.env` を作成。 `.env` に認証情報を記述する。

#### テストの実行

```bash 
npm run test:e2e

# 特定のテストファイルを指定
npm run test:e2e tests/e2e/a11y.spec.ts

# 特定のテストケースを指定
npx playwright test tests/e2e/webp-handler.spec.ts -g "ページスキャン結果の表示と個別選択機能"
```

#### wp-envでのE2Eテスト

※VVVと同様

```bash
npm run test:e2e
```

### カスタム投稿タイプ用のテストコンテンツのインポート

※VVV環境で開発している前提

開発サーバーにログイン。

```bash 
vagrant ssh
```

テーマディレクトリに移動。

```bash 
cd /srv/www/wordpress-foundation/public_html/wp-content/themes/wordpress-foundation
```

インポートを実行。

```bash 
wp import wp-cpt-test-contents.xml --authors=create
```

### 翻訳ファイルの生成

```bash
msgfmt -o languages/en_US.mo languages/en_US.po
msgfmt -o languages/fr_FR.mo languages/fr_FR.po
msgfmt -o languages/zh_CN.mo languages/zh_CN.po

# まとめて
msgfmt -o languages/en_US.mo languages/en_US.po && msgfmt -o languages/fr_FR.mo languages/fr_FR.po && msgfmt -o languages/zh_CN.mo languages/zh_CN.po
```

msgfmtコマンドが実行できない場合は、gettextがインストールされていない可能性がある。

```bash
brew install gettext
```

### JS用翻訳ファイルの生成

jsonファイル名は ` {textdomain}-{locale}-{handlename}.json` とする必要がある。`handlename` は、翻訳したいJSファイルのエンキュー時のハンドル名。

```bash
npx po2json languages/en_US.po languages/wordpressfoundation-en_US-wpf-main.json -f jed1.x -d wordpressfoundation -p
npx po2json languages/fr_FR.po languages/wordpressfoundation-fr_FR-wpf-main.json -f jed1.x -d wordpressfoundation -p
npx po2json languages/zh_CN.po languages/wordpressfoundation-zh_CN-wpf-main.json -f jed1.x -d wordpressfoundation -p

# まとめて
npx po2json languages/en_US.po languages/wordpressfoundation-en_US-wpf-main.json -f jed1.x -d wordpressfoundation -p && npx po2json languages/fr_FR.po languages/wordpressfoundation-fr_FR-wpf-main.json -f jed1.x -d wordpressfoundation -p && npx po2json languages/zh_CN.po languages/wordpressfoundation-zh_CN-wpf-main.json -f jed1.x -d wordpressfoundation -p
```
