<?php

/**
 * cptp を有効化するトレイト。
 */
trait WPF_CPTP_Trait {
	public static $wpf_cpt_rewrite;
	public static $wpf_cpt_permalink;
	public static $wpf_cpt_filters;

	public static function activate_cptp() {
		self::$wpf_cpt_rewrite   = new WPF_CPT_Rewrite();
		self::$wpf_cpt_permalink = new WPF_CPT_Permalink();
		self::$wpf_cpt_filters   = new WPF_CPT_Filters();
	}

	public static function deactivate_cptp() {
		self::$wpf_cpt_rewrite->remove_hooks();
		self::$wpf_cpt_permalink->remove_hooks();
		self::$wpf_cpt_filters->remove_hooks();
	}
}
