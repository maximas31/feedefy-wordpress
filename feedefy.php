<?php
/*
Plugin Name: Feedefy Widget
Description: Injects Feedefy Widget into your Wordpress website.
Version: 1.0
*/

// Add settings menu item
function feedefy_settings() {
    add_options_page(
        'Feedefy Widget Settings',
        'Feedefy Widget',
        'manage_options',
        'feedefy-settings',
        'feedefy_settings_page'
    );
}
add_action('admin_menu', 'feedefy_settings');

// Settings page content
function feedefy_settings_page() {
    ?>
    <div class="wrap">
        <form method="post" action="options.php">
            <?php
            settings_fields('feedefy_settings');
            do_settings_sections('feedefy-settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Register and initialize plugin settings
function feedefy_settings_init() {
    register_setting('feedefy_settings', 'project_id');
    register_setting('feedefy_settings', 'lang');

    add_settings_section(
        'feedefy_section',
        'Feedefy Widget Settings',
        'feedefy_section_cb',
        'feedefy-settings'
    );

    add_settings_field(
        'project_id',
        'Project ID',
        'project_id_field_cb',
        'feedefy-settings',
        'feedefy_section'
    );
    add_settings_field(
        'lang',
        'Language Code (ISO 639-1)',
        'lang_field_cb',
        'feedefy-settings',
        'feedefy_section'
    );
}
add_action('admin_init', 'feedefy_settings_init');

// Settings section callback
function feedefy_section_cb() {
    echo '<p>You can get your project ID <a href="https://app.feedefy.com/widget-settings" target="_blank">here</a>.</p>';
}

// Project ID field callback
function project_id_field_cb() {
    $value = get_option('project_id');
    echo '<input type="text" id="project_id" name="project_id" value="' . esc_attr($value) . '" />';
}

// Language field callback
function lang_field_cb() {
    $value = get_option('lang');
    echo '<input type="text" id="lang" name="lang" value="' . esc_attr($value) . '" />';
}

// Add settings link below plugin name on Plugins page
function feedefy_settings_link($links) {
  $settings_link = '<a href="options-general.php?page=feedefy-settings">Settings</a>';
  array_unshift($links, $settings_link); // Add the settings link at the beginning of the array
  return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'feedefy_settings_link');

// Inject script into footer
function inject_script() {
    $project_id = get_option('project_id');
    $lang = get_option('lang');

    if (!empty($project_id)) {
      $script_url = 'https://app.feedefy.com/embed.js?id=' . esc_attr($project_id);

      // Check if the user is logged in and append email as a query parameter
      if (is_user_logged_in()) {
        global $current_user;
        get_currentuserinfo(); // Ensure user data is populated
    
        $user_email = $current_user->user_email;
      }

      // Define the script tag with the URL and optionally add the lang attribute
      $script_tag = '<script defer src="' . $script_url . '"';

      // Check if the 'lang' attribute should be added
      if (isset($lang) && !empty($lang)) {
          $script_tag .= ' lang="' . esc_attr($lang) . '"';
      }

      if (isset($user_email)) {
          $script_tag .= ' user-id="' . esc_attr($user_email) . '"';
      }

      $script_tag .= '></script>';

      echo $script_tag;
    } else {
      // If Project ID is missing, log an error to the browser console
      echo '<script>';
      echo 'console.error("Feedefy Project ID is missing. Please set the Project ID in the plugin settings.");';
      echo '</script>';
    }
}
add_action('wp_footer', 'inject_script');
