#!/usr/bin/env bash

TMPDIR=${TMPDIR-/tmp}
TMPDIR=$(echo $TMPDIR | sed -e "s/\/$//")
WORKING_DIR="$PWD/tests"
WP_CORE_DIR=${WP_CORE_DIR-$TMPDIR/wordpress}

download() {
    if [ `which curl` ]; then
        curl -s "$1" > "$2";
    elif [ `which wget` ]; then
        wget -nv -O "$2" "$1"
    fi
}

mkdir -p $TMPDIR/downloads

# Install Rewrite Rules Inspector
download https://downloads.wordpress.org/plugin/rewrite-rules-inspector.zip $TMPDIR/downloads/rewrite-rules-inspector.zip
unzip -q $TMPDIR/downloads/rewrite-rules-inspector.zip -d $TMPDIR/downloads/
mkdir -p $WP_CORE_DIR/wp-content/plugins/rewrite-rules-inspector
mv $TMPDIR/downloads/rewrite-rules-inspector/* $WP_CORE_DIR/wp-content/plugins/rewrite-rules-inspector/

# Install Polylang
download https://downloads.wordpress.org/plugin/polylang.zip $TMPDIR/downloads/polylang.zip
unzip -q $TMPDIR/downloads/polylang.zip -d $TMPDIR/downloads/
mkdir -p $WP_CORE_DIR/wp-content/plugins/polylang
mv $TMPDIR/downloads/polylang/* $WP_CORE_DIR/wp-content/plugins/polylang/

# Install Redirection
download https://downloads.wordpress.org/plugin/redirection.zip $TMPDIR/downloads/redirection.zip
unzip -q $TMPDIR/downloads/redirection.zip -d $TMPDIR/downloads/
mkdir -p $WP_CORE_DIR/wp-content/plugins/redirection
mv $TMPDIR/downloads/redirection/* $WP_CORE_DIR/wp-content/plugins/redirection/

# Install Contact Form 7 
download https://downloads.wordpress.org/plugin/contact-form-7.zip $TMPDIR/downloads/contact-form-7.zip
unzip -q $TMPDIR/downloads/contact-form-7.zip -d $TMPDIR/downloads/
mkdir -p $WP_CORE_DIR/wp-content/plugins/contact-form-7
mv $TMPDIR/downloads/contact-form-7/* $WP_CORE_DIR/wp-content/plugins/contact-form-7/

# Install Jetpack
download https://downloads.wordpress.org/plugin/jetpack.zip $TMPDIR/downloads/jetpack.zip
unzip -q $TMPDIR/downloads/jetpack.zip -d $TMPDIR/downloads/
mkdir -p $WP_CORE_DIR/wp-content/plugins/jetpack
mv $TMPDIR/downloads/jetpack/* $WP_CORE_DIR/wp-content/plugins/jetpack/

# Install Yoast SEO
download https://downloads.wordpress.org/plugin/wordpress-seo.zip $TMPDIR/downloads/wordpress-seo.zip
unzip -q $TMPDIR/downloads/wordpress-seo.zip -d $TMPDIR/downloads/
mkdir -p $WP_CORE_DIR/wp-content/plugins/wordpress-seo
mv $TMPDIR/downloads/wordpress-seo/* $WP_CORE_DIR/wp-content/plugins/wordpress-seo/
