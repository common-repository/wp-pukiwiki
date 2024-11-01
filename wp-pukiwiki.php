<?php
/*
Plugin Name: WP PukiWiki
Plugin URI: http://www.is.titech.ac.jp/~wakita/en/wp-pukiwiki/
Description: A PukiWiki-style syntax formatter for WordPress.
Version: 0.2
Author: Ken Wakita
Author URI: http://www.is.titech.ac.jp/~wakita/
*/

/*  Copyright 2007  Ken Wakita  (email : wakita@is.titech.ac.jp)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

define('WPPW_URL', 'http://www2.is.titech.ac.jp/~wakita/wiki/');

define('WPPW_NEWPAGE', 'WordPress_PukiWiki_New_Page');
define('WPPW_OPENTAG', '<pukiwiki>');
define('WPPW_CLOSETAG', '</pukiwiki>');

define('WPPW_DEBUG', false);

function wppw_curl_pukiwiki($content) {
  $postfields = array(
    "cmd" => "edit",
    "page" => WPPW_NEWPAGE,
    "msg" => $content,
    "preview" => "Preview");
  $curl_options = array(
    CURLOPT_URL => WPPW_URL,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $postfields,
    CURLOPT_RETURNTRANSFER => true);

  $curl_chan = curl_init();
  //curl_setopt_array($curl_chan, $curl_options);
  foreach ($curl_options as $key => $value) {
    curl_setopt($curl_chan, $key, $value);
  }
  $content = curl_exec($curl_chan);
  curl_close($curl_chan);

  return $content;
}

define('WPPW_BODY', 0);
define('WPPW_NOTE', 1);

function wppw_arrange($content) {
  for ($i = 5; $i > 0; $i--) {
    $j = $i + 1;
    $pattern = "<h$i"; $replacement = "<h$j";
    $content = str_replace($pattern, $replacement, $content);
    $pattern = "</h$i"; $replacement = "</h$j";
    $content = str_replace($pattern, $replacement, $content);
  }

  $pattern = 'src="image/face/';
  $replacement = 'src="' . WPPW_URL . '/image/face/';
  $content = str_replace($pattern, $replacement, $content);

  $pattern = '<p>&amp;more;</p>'; $replacement = '<!--more-->';
  $content = str_replace($pattern, $replacement, $content);
  
  $pattern = '|<a class="anchor_super" id="([0-9a-z]+)" href="WPPW_URL\?WPPW_NEWPAGE#\\1" title="\\1">&dagger;</a>|';
  $replacement = '<a class="anchor_super" id="\\1" href="#\\1" title="(\\1">&dagger;</a>';
  if (WPPW_DEBUG) wd_output("-\npattern = $pattern");
  $content = preg_replace($pattern, $replacement, $content);

  return mb_convert_encoding($content, 'UTF-8', 'auto');
}

function wppw_pukiwiki_format($content) {
  $content = wppw_curl_pukiwiki($content);

  $newline_pattern = '/\r|\n|\r\n/m';
  $lines = preg_split($newline_pattern, $content);

  $content = '';
  $contents = array('', '');
  $index = NULL;

  $is_preview = false;

  foreach ($lines as $line) {
    if (preg_match('/^<div id="preview">/', $line)) {
      $index = WPPW_BODY;
    } else     if (preg_match('/^<div id="note">/', $line)) {
      $index = WPPW_NOTE;
    } else if (preg_match('/^<div class="edit_form">/', $line)) {
      $index = NULL;
    } else if (preg_match('/^<!-- Toolbar -->$/', $line)) {
      break;
      $index = NULL;
    }

    if (!is_null($index)) $contents[$index] .= "\n$line";
  }
  return
    array(wppw_arrange($contents[WPPW_BODY]),
	  wppw_arrange($contents[WPPW_NOTE]));
}

$pukiwiki_note_number = 1;

function wppw_fix_noteref($matches) {
  global $pukiwiki_note_number;

  $line = $matches[0];
  $title = $matches[2];
  $n = $pukiwiki_note_number;
  $pukiwiki_note_number++;

  return
    '<a id="notetext_' . $n .
    '" href="#notefoot_' . $n .
    '" class="note_super" title="' . $title .
    '">*' . $n . '</a>';
}

function wppw_fix_note($matches) {
  global $pukiwiki_note_number;

  $line = $matches[0];
  $n = $pukiwiki_note_number++;

  return
    '<a id="notefoot_' . $n .
    '" href="#notetext_' . $n .
    '" class="note_super">*' . $n .
    '</a>';
}

function wppw_fix_notes($content, $notes) {
  global $pukiwiki_note_number;

  $pattern = '|<a id="notetext_([0-9]+)" href="#notefoot_\\1" class="note_super" title="([^"]*)">\*\\1</a>|';
  $content = preg_replace_callback($pattern, 'wppw_fix_noteref', $content);

  $pukiwiki_note_number = 1;

  //<a id="notefoot_1" href="#notetext_1" class="note_super">*1</a>
  $pattern = '|<a id="notefoot_([0-9]+)" href="#notetext_\\1" class="note_super">\\*\\1</a>|';
  $pattern = '|<a id="notefoot_([0-9]+)" href="#notetext_\\1" class="note_super">\\*\\1</a>|';
  $notes = preg_replace_callback($pattern, 'wppw_fix_note', $notes);

  return $content . $notes;
}

define('WPPW_HORIZONTAL', '');

function wp_pukiwiki($content) {
  if (strstr($content, WPPW_OPENTAG) === false) return $content;

  $notes = '';

  $opentag_size = strlen(WPPW_OPENTAG);
  $closetag_size = strlen(WPPW_CLOSETAG);
  $index = 0;
  while (true) {
    $start = strpos($content, WPPW_OPENTAG, $index);
    if ($start === false) break;
    $end = strpos($content, WPPW_CLOSETAG, $start + $opentag_size);
    if ($end === false) break;
    $wikicode_length = $end - $start - $opentag_size;
    $wikicode = substr($content, $start + $opentag_size, $wikicode_length);
    $contents = wppw_pukiwiki_format($wikicode);
    if (WPPW_DEBUG)
      wd_output("-\nstart = $start, end = $end, wikicode_length = $wikicode_length");
    $content = substr_replace($content, $contents[WPPW_BODY], $start,
			      $wikicode_length + $opentag_size + $closetag_size);
    $notes .= $contents[WPPW_NOTE];

    $index = $start + strlen($contents[WPPW_BODY]);
  }
  
  $notes =
    '<hr class="note_hr" />' .
    preg_replace('@<hr class="(note|full)_hr" />@', '', $notes);

  return mb_convert_encoding(wppw_fix_notes($content, $notes), 'UTF-8', 'auto');
}

add_filter('the_content', 'wp_pukiwiki', 5);
add_filter('the_excerpt', 'wp_pukiwiki', 5);
add_filter('the_excerpt_rss', 'wp_pukiwiki', 5);
add_filter('comment_text', 'wp_pukiwiki', 5);

?>
