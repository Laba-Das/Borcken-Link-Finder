<?php
/**
 * Plugin Name: Broken Links Finder Tool For Wp
 * Plugin URI: https://teckshop.net/our-tools/
 * Description: A simple WordPress plugin that allow you to add the Broken Links Finder Tool in your WordPress websites using short code [broken_links_finder] . 
 * Version: 1.0.0
 * Author: Teckshop.net
 * Author URI: https://teckshop.net/
 */
 
 
 // Enqueue the plugin's CSS file
function broken_links_finder_enqueue_styles() {
  wp_enqueue_style('broken-links-finder-style', plugins_url('broken-links-finder-css.css', __FILE__));
}
add_action('wp_enqueue_scripts', 'broken_links_finder_enqueue_styles');

// Register the shortcode
add_shortcode('broken_links_finder', 'broken_links_finder_shortcode');

// Define the shortcode callback function
function broken_links_finder_shortcode() {
  $content = '';

  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve the URL value from the form
    $url = $_POST["url"];

    // Fetch the HTML code of the webpage
    $html = file_get_contents($url);

    // Extract all the links and find the broken ones
    preg_match_all('/<a[^>]+href=([\'"])(?<href>.+?)\1[^>]*>/i', $html, $matches);
    $broken_links = array();
    foreach($matches['href'] as $link){
      $headers = get_headers($link);
      if(strpos($headers[0], "200") === false){
        $status = str_replace('HTTP/1.1 ', '', $headers[0]);
        $broken_links[] = array('link' => $link, 'status' => $status);
      }
    }

    // Remove duplicate links
    $broken_links = array_unique($broken_links, SORT_REGULAR);

    // Generate the CSV file
    $csv = fopen('broken_links.csv', 'w');
    fputcsv($csv, array('Link', 'Status'));
    foreach ($broken_links as $link) {
      fputcsv($csv, $link);
    }
    fclose($csv);

    // Display the broken links in a table format
    $content .= '<h2>Broken Links</h2>';
    $content .= '<table>';
    $content .= '<thead>';
    $content .= '<tr>';
    $content .= '<th>Link</th>';
    $content .= '<th>Status</th>';
    $content .= '</tr>';
    $content .= '</thead>';
    $content .= '<tbody>';
    foreach ($broken_links as $link) {
      $content .= '<tr>';
      $content .= '<td>' . $link['link'] . '</td>';
      $content .= '<td>' . $link['status'] . '</td>';
      $content .= '</tr>';
    }
    $content .= '</tbody>';
    $content .= '</table>';

    // Add a download button for the CSV file
    $content .= '<a href="broken_links.csv" download>Download CSV</a>';

    // Add a back button to return to the input form
    $content .= '<br><br>';
    $content .= '<a href="#" onclick="history.back()">Back to form</a>';
  } else {
    // Display the HTML form for entering the URL
    $content .= '<form method="post" action="' . $_SERVER['REQUEST_URI'] . '">';
    $content .= '<label for="url">Enter the URL:</label><br>';
    $content .= '<input type="text" id="url" name="url"><br><br>';
    $content .= '<input type="submit" value="Submit">';
    $content .= '</form>';
    $content .= '<div><iframe src="https://teckshop.net/ads.html" width="100%" height="170"></iframe></div>';
    $content .= '<p id="heading">Powered By <a href="https://teckshop.net/">Teckshop </a></p>';
    
  }
  

  return $content;
}
