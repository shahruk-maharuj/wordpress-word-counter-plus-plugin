<?php
/**
 * Plugin Name:       Word Counter Plus
 * Plugin URI:        https://fb.com/shahruk.maharuj
 * Description:       Word Counter Plus is a powerful plugin that provides word counting, character counting, customizable word goals, word frequency analysis, export/import of word count data, and track historical word count statistics.
 * Version:           2.1.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Shahruk.Maharuj
 * Author URI:        https://fb.com/shahruk.maharuj
 * Text Domain:       word-count-plus
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

defined( 'ABSPATH' ) || exit;

// Load text domain for localization
function wordcount_load_textdomain() {
	load_plugin_textdomain( 'word-count-plus', false, dirname( __FILE__ ) . '/languages' );
}
add_action( 'plugins_loaded', 'wordcount_load_textdomain' );

// Count words and characters in content
function wordcount_count_words_characters( $content ) {
	$stripped_content = strip_tags( $content );
	$word_count       = str_word_count( $stripped_content );
	$character_count  = mb_strlen( $stripped_content );

	$word_label      = __( 'Total Number of Words', 'word-count-plus' );
	$character_label = __( 'Total Number of Characters', 'word-count-plus' );

	$word_label      = apply_filters( 'wordcount_word_heading', $word_label );
	$character_label = apply_filters( 'wordcount_character_heading', $character_label );

	$tag     = apply_filters( 'wordcount_tag', 'h3' );
	$content .= sprintf(
		'<%1$s style="font-weight: bold;">%2$s: %3$s</%1$s>',
		esc_html( $tag ),
		esc_html( $word_label ),
		esc_html( $word_count )
	);
	$content .= sprintf(
		'<%1$s style="font-weight: bold;">%2$s: %3$s</%1$s>',
		esc_html( $tag ),
		esc_html( $character_label ),
		esc_html( $character_count )
	);

	return $content;
}
add_filter( 'the_content', 'wordcount_count_words_characters' );

// Calculate reading time
function wordcount_reading_time( $content ) {
	$stripped_content = strip_tags( $content );
	$word_count       = str_word_count( $stripped_content );
	$reading_minute   = floor( $word_count / 200 );
	$reading_seconds  = floor( $word_count % 200 / ( 200 / 60 ) );
	$is_visible       = apply_filters( 'wordcount_display_readingtime', 1 );

	if ( $is_visible ) {
		$label = __( 'Total Reading Time', 'word-count-plus' );
		$label = apply_filters( 'wordcount_readingtime_heading', $label );
		$tag   = apply_filters( 'wordcount_readingtime_tag', 'h4' );

		$content .= sprintf(
			'<%1$s style="font-weight: bold;">%2$s: %3$d minutes %4$d seconds</%1$s>',
			esc_html( $tag ),
			esc_html( $label ),
			esc_html( $reading_minute ),
			esc_html( $reading_seconds )
		);
	}

	return $content;
}
add_filter( 'the_content', 'wordcount_reading_time' );

// Customizable word goals
function wordcount_customizable_goals( $word_goal ) {
	$default_goal = 1000; // Set a default word goal
	$custom_goal  = get_option( 'wordcount_custom_goal' );

	if ( false !== $custom_goal && is_numeric( $custom_goal ) ) {
		$word_goal = (int) $custom_goal;
	} else {
		$word_goal = $default_goal;
	}

	return $word_goal;
}
add_filter( 'wordcount_word_goal', 'wordcount_customizable_goals' );

// Word frequency analysis
function wordcount_word_frequency_analysis( $content ) {
	$stripped_content = strip_tags( $content );
	$words            = str_word_count( $stripped_content, 1 );
	$word_frequency   = array_count_values( $words );
	arsort( $word_frequency );

	$word_frequency_output  = '<table style="border-collapse: collapse; margin-bottom: 20px;">';
	$word_frequency_output .= '<thead><tr style="background-color: blue; color: white;"><th style="padding: 10px;">Word</th><th style="padding: 10px;">Frequency</th></tr></thead>';
	$word_frequency_output .= '<tbody>';

	foreach ( $word_frequency as $word => $count ) {
		$word_frequency_output .= sprintf(
			'<tr style="border: 1px solid #ddd;"><td style="padding: 10px;">%s</td><td style="padding: 10px;">%d</td></tr>',
			esc_html( $word ),
			esc_html( $count )
		);
	}

	$word_frequency_output .= '</tbody>';
	$word_frequency_output .= '</table>';

	$word_frequency_label = __( 'Word Frequency Analysis', 'word-count-plus' );
	$word_frequency_label = apply_filters( 'wordcount_word_frequency_heading', $word_frequency_label );
	$tag                  = apply_filters( 'wordcount_word_frequency_tag', 'h3' );

	$content .= sprintf(
		'<%1$s style="font-weight: bold;">%2$s</%1$s>%3$s',
		esc_html( $tag ),
		esc_html( $word_frequency_label ),
		$word_frequency_output
	);

	return $content;
}
add_filter( 'the_content', 'wordcount_word_frequency_analysis' );

// Export/import word count data
function wordcount_export_word_count_data() {
	$word_count_data = array(
		'word_count'      => str_word_count( get_the_content() ),
		'character_count' => mb_strlen( strip_tags( get_the_content() ) ),
	);

	return $word_count_data;
}

// Track historical word count statistics
function wordcount_track_word_count_statistics( $post_id ) {
	$word_count      = str_word_count( get_the_content( $post_id ) );
	$character_count = mb_strlen( strip_tags( get_the_content( $post_id ) ) );

	// Save the word count and character count data to the post meta
	update_post_meta( $post_id, 'word_count', $word_count );
	update_post_meta( $post_id, 'character_count', $character_count );
}
add_action( 'save_post', 'wordcount_track_word_count_statistics' );
