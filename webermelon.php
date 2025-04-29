    <?php
    /**
     * Plugin Name: Webermelon Project Management
     * Description: A simple WordPress Project Management Plugin.
     * Version: 1.0.0
     * Author: S. M. Hasib
     */

    if (!defined('ABSPATH')) exit;

    function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, strlen($characters) - 1)];
        }
        return $randomString;
    }

    function wmpm_enqueue_scripts() {
        if (is_page('project-manage-page')) {
            wp_enqueue_style(
                'wmpm_style',
                plugin_dir_url(__FILE__) . 'assets/css/wmpm_style.css',
                array(), // No dependencies
                filemtime(plugin_dir_path(__FILE__) . 'assets/css/wmpm_style.css')
            );

            wp_enqueue_script(
                'wmpm_script',
                plugin_dir_url(__FILE__) . 'assets/js/wmpm_script.js',
                array('jquery'),
                filemtime(plugin_dir_path(__FILE__) . 'assets/js/wmpm_script.js'),
                true
            );
            
            wp_localize_script('wmpm_script', 'ajax_object', array(
                'ajax_url' => admin_url('admin-ajax.php'),
            ));
        }
    }

    add_action('wp_enqueue_scripts', 'wmpm_enqueue_scripts', 9999);

    // Shortcode output
   /*  function my_html_page_shortcode() {
        ob_start();
        include plugin_dir_path(__FILE__) . 'homepage.php';
        return ob_get_clean();
    } */
    function my_html_page_shortcode() {
        // Check if user is logged in
        if (!is_user_logged_in()) {
            return '<div class="login-required">You must be logged in to view this page. <a href="' . wp_login_url(get_permalink()) . '">Login here</a>.</div>';
        }
        
        ob_start();
        include plugin_dir_path(__FILE__) . 'homepage.php';
        return ob_get_clean();
    }
    add_shortcode('project_manage_page', 'my_html_page_shortcode');

    // Create page on plugin activation
    function wmpm_create_plugin_page() {
        $page_title = 'Project Manage Page';
        $page_check = get_page_by_title($page_title);

        if (!$page_check) {
            $page_data = array(
                'post_title'    => $page_title,
                // 'post_content'  => '[project_manage_page]',
                'post_status'   => 'publish',
                'post_type'     => 'page',
            );
            $page_id = wp_insert_post($page_data);
            update_option('wmpm_redirect_to_page_id', $page_id);
        } else {
            update_option('wmpm_redirect_to_page_id', $page_check->ID);
        }
    }
    register_activation_hook(__FILE__, 'wmpm_create_plugin_page');


    // Redirect after activation
    function wmpm_redirect_after_activation() {
        if ($page_id = get_option('wmpm_redirect_to_page_id')) {
            delete_option('wmpm_redirect_to_page_id');
            wp_safe_redirect(get_permalink($page_id));
            exit;
        }
    }
    add_action('admin_init', 'wmpm_redirect_after_activation');

    // Delete page on deactivation
    function wmpm_delete_plugin_page() {
        $page = get_page_by_title('Project Manage Page');
        if ($page) {
            wp_delete_post($page->ID, true);
        }
    }
    register_deactivation_hook(__FILE__, 'wmpm_delete_plugin_page');




    // Override the theme template for this page
    /* function wmpm_override_template($template) {
        if (is_page('project-manage-page')) {
            return plugin_dir_path(__FILE__) . 'templates/project-manage-template.php';
        }
        return $template;
    } */

    // Override the theme template for this page
    function wmpm_override_template($template) {
        if (is_page('project-manage-page')) {
            // Check if user is logged in
            if (!is_user_logged_in()) {
                // Redirect to login page with a redirect_to parameter
                wp_redirect(wp_login_url(get_permalink(get_page_by_title('Project Manage Page')->ID)));
                exit;
            }
            return plugin_dir_path(__FILE__) . 'templates/project-manage-template.php';
        }
        return $template;
    }

    add_filter('template_include', 'wmpm_override_template', 99);


    // Optional: remove theme styles/scripts

    /* function wmpm_remove_theme_assets() {
        if (is_page('project-manage-page')) {
            // Replace with your actual theme style/script handles
            wp_dequeue_style('twentytwenty-style');
            wp_dequeue_script('twentytwenty-navigation');
        }
    }
    add_action('wp_enqueue_scripts', 'wmpm_remove_theme_assets', 100); */



    // Enqueue only plugin styles/scripts
    /* function wmpm_enqueue_scripts() {
        if (is_page('project-manage-page')) {
            wp_enqueue_style(
                'wmpm_style',
                plugin_dir_url(__FILE__) . 'assets/css/wmpm_style.css',
                array(),
                filemtime(plugin_dir_path(__FILE__) . 'assets/css/wmpm_style.css')
            );

            wp_enqueue_script(
                'wmpm_script',
                plugin_dir_url(__FILE__) . 'assets/js/wmpm_script.js',
                array('jquery'),
                null,
                true
            );

            wp_localize_script('wmpm_script', 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
        }
    }
    add_action('wp_enqueue_scripts', 'wmpm_enqueue_scripts'); */





    function team_custom_post_type()
    {
        register_post_type(
            'team',
            array(
                'labels'      => array(
                    'name'          => __('Team', 'textdomain'),
                    'singular_name' => __('Team', 'textdomain'),
                ),
                'public'      => true,
                'has_archive' => true,
                'supports'    => array('title'),
            )
        );

        register_post_type(
            'projects',
            array(
                'labels'      => array(
                    'name'          => __('Projects', 'textdomain'),
                    'singular_name' => __('Project', 'textdomain'),
                ),
                'public'      => true,
                'has_archive' => true,
                'supports'    => array('title', 'editor'),
            )
        );
    }

    add_action('init', 'team_custom_post_type');


    /**
     * Create a new team post
     */
    function create_team_post()
    {

        if (!isset($_POST['action']) || $_POST['action'] !== 'create_team') {
            wp_send_json_error(['message' => 'Invalid request.']);
            wp_die();
        }

        $team_name = sanitize_text_field($_POST['team_name']);

        if (post_exists($team_name)) {
            wp_send_json_error(['message' => 'Team ' . $team_name . ' Already  Exist.']);
            wp_die();
        }
        // Post data array
        $team_post = array(
            'post_title'    => $team_name,        // The title of the post
            'post_status'   => 'publish',         // Published status
            'post_type'     => 'team',            // Your custom post type
            'post_author'   => 1,                 // Default admin user ID
        );


        // Insert the post into the database
        $post_id = wp_insert_post($team_post);

        // Check for errors
        if (is_wp_error($post_id)) {
            // Handle error
            error_log('Failed to create team post: ' . $post_id->get_error_message());
            return;
        }

        //create post meta with empty array
        update_post_meta($post_id, 'member_email_array', []);

        wp_send_json_success([
            'message' => $post_id
        ]);
        wp_die();
    }

    // Call this function whenever you want to create a team post

    add_action('wp_ajax_create_team', 'create_team_post');





    /* ====================create project =========================== */


    /**
     * Create a new project post
     */


    function create_project_post()
    {
        if (!isset($_POST['action']) || $_POST['action'] !== 'create_project') {
            wp_send_json_error(['message' => 'Invalid request.']);
            wp_die();
        }

        $project_name = sanitize_text_field($_POST['project_name']);


        if (post_exists($project_name)) {
            wp_send_json_error(['message' => 'Team ' . $project_name . ' ALready  Exist.']);
            wp_die();
        }
        // Post data array
        $project_post = array(
            'post_title'    => $project_name,        // The title of the post
            'post_content'  => '',
            'post_status'   => 'publish',         // Published status
            'post_type'     => 'projects',            // Your custom post type
            'post_author'   => 1,                 // Default admin user ID
        );

        // Insert the post into the database
        $post_id = wp_insert_post($project_post);

        // Check for errors
        if (is_wp_error($post_id)) {
            // Handle error
            error_log('Failed to create project post: ' . $post_id->get_error_message());
            return;
        }

        //create post meta with empty array
        update_post_meta($post_id, 'details', 'Please click the Edit button to update the project details, including the date, priority, and current status.');
        update_post_meta($post_id, 'due_date', '2000-01-01');
        update_post_meta($post_id, 'priority', 'low');
        update_post_meta($post_id, 'status', 'on-hold');
        
        update_post_meta($post_id, 'member_array', []);

        wp_send_json_success([
            'message' => $post_id." ".get_the_title( $post_id )
        ]);
        wp_die();
    }


    function update_project()
    {
        if (!isset($_POST['action']) || $_POST['action'] !== 'update_project') {
            wp_send_json_error(['message' => 'Invalid request.']);
            wp_die();
        }   
        

        $post_id = intval(sanitize_text_field($_POST['project_id']));
        $project_name = sanitize_text_field($_POST['project_name']);
        $project_details = sanitize_textarea_field($_POST['project_description']);
        $due_date = $_POST['project_due_date'];
        $priority = $_POST['project_priority'];
        $status = $_POST['project_status'];
        

        wp_update_post( array(
            'ID' => $post_id,
            'post_title' => $project_name,
        ) );
    
        update_post_meta($post_id, 'details', $project_details);
        update_post_meta($post_id, 'due_date', $due_date);
        update_post_meta($post_id, 'priority', $priority);
        update_post_meta($post_id, 'status', $status);

        wp_send_json_success([  
            'message' => $post_id
        ]);
        wp_die();
    }

    add_action('wp_ajax_update_project', 'update_project'); 


    // Call this function whenever you want to create a project post

    add_action('wp_ajax_create_project', 'create_project_post');

    function get_team_member(){

        if (!isset($_POST['action']) || $_POST['action'] !== 'get_team_member') {
            wp_send_json_error(['message' => 'Invalid request.']);
            wp_die();
        }


        $post_id = sanitize_text_field($_POST['team_id']);

        $email_array = get_post_meta($post_id, 'member_email_array', true);

        wp_send_json_success([
            'message' => $email_array
        ]);
        wp_die();
    }

    // Call this function whenever you want to create a project post

    add_action('wp_ajax_get_team_member', 'get_team_member');





    function send_invite()
    {
        if (!isset($_POST['action']) || $_POST['action'] !== 'send_invite') {
            wp_send_json_error(['message' => 'Invalid request.']);
            wp_die();
        }

        $email = sanitize_email($_POST['recipient_email']);
        $team_id = sanitize_text_field($_POST['team_id']);
        $team_name = get_the_title($team_id);

        $email_array = get_post_meta($team_id, 'member_email_array', true);

        if(!email_exists($email)) {
            
            $token = generateRandomString(10);
            $site_url = get_site_url();
            
            $message = "You have been invited to join  $team_name. Click the link to accept: $site_url/invitation?id=$team_id&token=$token";
            
            // Send email
            $headers = array('Content-Type: text/plain; charset=UTF-8');    
            
            $temp_invite_data[] = array( $token, $email);
            
            update_post_meta($team_id, 'temp_invite_data', $temp_invite_data );
            if (!wp_next_scheduled('delete_temp_invite_data_event', array($team_id))) {
                wp_schedule_single_event(time() + 300, 'delete_temp_invite_data_event', array($team_id)); // 300 seconds = 5 minutes
            }
            
            // Hook to handle the deletion
            
            
            wp_mail($email, "Invitation to join $team_name", $message, $headers, "");
            
            wp_send_json_success([
                'message' => $email_array
            ]);
            wp_die();
        }

        if(in_array($email, $email_array)) {
            wp_send_json_error(['message' => 'User Already exists']);
            wp_die();
        }
        $email_array[] = $email; // Step 3: Push new email
        update_post_meta($team_id, 'member_email_array', $email_array); // Step 4: Save back
        wp_send_json_success(['message' => $email_array]);
        wp_die();
    }

    add_action('wp_ajax_send_invite', 'send_invite');

    add_action('delete_temp_invite_data_event', function($team_id) {
        delete_post_meta($team_id, 'temp_invite_data');
    });


    function handle_invitation_token() {
        // Check if we're on the 'invitation' page and the token is in the URL
        if (isset( $_GET['token'] ) && isset($_GET['id'])) {
            $token = isset($_GET['token']) ? sanitize_text_field($_GET['token']) : '';
            $team_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';

            $temp_invite_data = get_post_meta($team_id, 'temp_invite_data', true);
            if (!is_array($temp_invite_data)) {
                // Handle error: no temp invite data found
                error_log('No temp invite data found for team ID: ' . $team_id);
                return;
            }
            foreach ($temp_invite_data as $data) {
                if ($data[0] === $token) {
                    $email = $data[1];
                    break;
                }
                $email= null;

            }
            

            // $username = strstr($email, '@', true);
            // $password = mt_rand(10000, 99999);
            $username = strstr($email, '@', true);
            $password = 9999;
            $user_id = wp_create_user($username, $password, $email);
            if (is_wp_error($user_id)) {
                // Handle error
                error_log('Failed to create user: ' . $user_id->get_error_message());
                return;
            }
            $message = "You have been successfully registered. \n\n";
            $message .= "Username: $username\n";    
            $message .= "Password: $password\n\n";
            $message .= "You can now log in to your account.\n\n";
            $message .= "Webermelon";
            $headers = array('Content-Type: text/plain; charset=UTF-8');    
            wp_mail($email, "Invitation to join a team", $message, $headers, "");
            echo "Token received: " . $token;

            $email_array = get_post_meta($team_id, 'member_email_array', true);
            if (!is_array($email_array)) {
                $email_array = array();
            }
            $email_array[] = $email; // Add the new email to the array
            update_post_meta($team_id, 'member_email_array', $email_array); // Save the updated array

            wp_redirect(wp_login_url());
            exit;
        }

    }   

    add_action( 'template_redirect', 'handle_invitation_token' );


    /**
     * add  email of member
     */
    function add_team_member(){

        if (!isset($_POST['action']) || $_POST['action'] !== 'add_team_member') {
            wp_send_json_error(['message' => 'Invalid request.']);
            wp_die();
        }

        $post_id = sanitize_text_field($_POST['team_id']);
        $email = sanitize_email($_POST['email']);

        $email_array = get_post_meta($post_id, 'member_email_array', true);

        if (in_array($email, $email_array)) {
            wp_send_json_error(['message' => 'User Already exists']);
            wp_die();
        }

        $email_array[] = $email; // Step 3: Push new email

        // Step 4: Save back
        update_post_meta($post_id, 'member_email_array', $email_array);


        wp_send_json_success(['message' => $email . ' added']);
        wp_die();
    }

    // Call this function whenever you want to create a team post

    add_action('wp_ajax_add_team_member', 'add_team_member');


    /* 
    $member_email = $_POST['member'];


    $email_array = get_post_meta($post_id, 'member_array', true);

    if (in_array($member_email, $email_array)) {
        wp_send_json_error(['message' => 'Member Already exists']);
        wp_die();
    }
    $email_array[] = $member_email; 
    update_post_meta($post_id, 'member_array', $email_array); 
    */


    function link_redirect_to_project_page() {
        printf('<a id="link" href="%s" target="_blank">Click here to view your project</a>', esc_url( home_url( 'project-manage-page' ) ));
    }

    // Now we set that function up to execute when the admin_notices action is called.
    add_action( 'admin_notices', 'link_redirect_to_project_page' );


    function admin_dashboard_btn() {
        echo "
        <style type='text/css'>
        #link {
            float: right;
            padding: 5px 10px;
            margin: 0;
            font-size: 12px;
            line-height: 1.6666;
            background-color:#08b8a3;
            border: 1px solid #ccc; 
            border-radius: 8px;
            text-decoration: none;  
            color: #fff;
            font-weight: bold;
            transition: background-color 0.3s, color 0.3s;
        }

        #link:hover {
            background-color: #fff;
            color: #08b8a3;
            border: 1px solid #08b8a3; 
        }
        .block-editor-page #link {
            display: none;
        }
        .editor-styles-wrapper #link {
            display: block;
        }
        .editor-styles-wrapper #link:hover {
            background-color: #fff;
            color: #08b8a3;
            border: 1px solid #08b8a3; 
        }
        .editor-styles-wrapper #link {
            display: block;
        }
        .editor-styles-wrapper #link:hover {
            background-color: #fff;
            color: #08b8a3;
            border: 1px solid #08b8a3; 
        }
        </style>
        ";
    }

    add_action( 'admin_head', 'admin_dashboard_btn' );



    function get_team_member_email(){

        if (!isset($_POST['action']) || $_POST['action'] !== 'get_team_member_email') {
            wp_send_json_error(['message' => 'Invalid request.']);
            wp_die();
        }


        $post_id = sanitize_text_field($_POST['team_id']);

        $email_array = get_post_meta($post_id, 'member_email_array', true);

        wp_send_json_success([
            'message' => $email_array
        ]);
        wp_die();   
    }   

    add_action('wp_ajax_get_team_member_email', 'get_team_member_email');


    function get_project_details(){

        if (!isset($_POST['action']) || $_POST['action'] !== 'get_project_details') {
            wp_send_json_error(['message' => 'Invalid request.']);
            wp_die();
        }


        $post_id = sanitize_text_field($_POST['project_id']);
            

        $details = get_post_meta($post_id, 'details', true);
        $due_date = get_post_meta($post_id, 'due_date', true);
        $priority = get_post_meta($post_id, 'priority', true);
        $status = get_post_meta($post_id, 'status', true);
        $project_name = get_the_title($post_id);
        $email_array = get_post_meta($post_id, 'member_array', true);
        if (!is_array($email_array)) {
            $email_array = array();
        }

        $user = wp_get_current_user();
        if ( in_array( 'administrator', $user->roles ) || in_array( 'editor', $user->roles ) ) {
            $accessed_user= true;
        }
        else {
        $accessed_user= false;
        }

        wp_send_json_success([
            'message' => [
                'project_name' => $project_name,
                'details' => $details,
                'due_date' => $due_date,
                'priority' => $priority,
                'status' => $status,
                'members' => $email_array
            ]
            ,
            'accessed' => $accessed_user
        ]);
        wp_die();
    }

    add_action('wp_ajax_get_project_details', 'get_project_details');
    // add_action('wp_ajax_nopriv_get_project_details', 'get_project_details');


    function add_member_to_project(){

        if (!isset($_POST['action']) || $_POST['action'] !== 'add_member_to_project') {
            wp_send_json_error(['message' => 'Invalid request.']);
            wp_die();
        }


        $post_id = sanitize_text_field($_POST['project_id']);
        $email = sanitize_email($_POST['team_member_email']);

        $email_array = get_post_meta($post_id, 'member_array', true);

        if (in_array($email, $email_array)) {
            wp_send_json_error(['message' => 'User Already exists']);
            wp_die();
        }

        $email_array[] = $email; // Step 3: Push new email

        // Step 4: Save back
        update_post_meta($post_id, 'member_array', $email_array);


        wp_send_json_success(['message' => $email_array]);
        wp_die();
    }

    add_action('wp_ajax_add_member_to_project', 'add_member_to_project');

    function delete_team_member() {
        if (!isset($_POST['action']) || $_POST['action'] !== 'delete_team_member') {
            wp_send_json_error(['message' => 'Invalid request.']);
            wp_die();
        }

        $post_id = sanitize_text_field($_POST['team_id']);
        $email = sanitize_email($_POST['team_member_email']);

        $email_array = get_post_meta($post_id, 'member_email_array', true);
        
        // Ensure we have an array
        if (!is_array($email_array)) {
            $email_array = [];
        }

        if (!in_array($email, $email_array)) {
            wp_send_json_error(['message' => 'User does not exist in team']);
            wp_die();
        }

        $key = array_search($email, $email_array);
        unset($email_array[$key]); // Remove the email

        // Force array to have sequential numeric keys starting from 0
        $email_array = array_values($email_array);

        // Save back to post meta
        update_post_meta($post_id, 'member_email_array', $email_array);

        // Use wp_send_json_success with an explicitly indexed array
        wp_send_json_success([
            'message' => array_values($email_array) // Double ensure it's an indexed array
        ]);
        wp_die();
    }
    add_action('wp_ajax_delete_team_member', 'delete_team_member');


    function delete_project_member() {
        if (!isset($_POST['action']) || $_POST['action'] !== 'delete_project_member') {
            wp_send_json_error(['message' => 'Invalid request.']);
            wp_die();
        }

        $post_id = sanitize_text_field($_POST['project_id']);
        $email = sanitize_email($_POST['project_member_email']);

        $email_array = get_post_meta($post_id, 'member_array', true);
        
        // Ensure we have an array
        if (!is_array($email_array)) {
            $email_array = [];
        }

        if (!in_array($email, $email_array)) {
            wp_send_json_error(['message' => 'User does not exist in project']);
            wp_die();
        }

        $key = array_search($email, $email_array);
        unset($email_array[$key]); // Remove the email

        // Force array to have sequential numeric keys starting from 0
        $email_array = array_values($email_array);

        // Save back to post meta
        update_post_meta($post_id, 'member_array', $email_array);

        // Use wp_send_json_success with an explicitly indexed array
        wp_send_json_success([
            'message' => array_values($email_array) // Double ensure it's an indexed array
        ]);
        wp_die();
    }

    add_action('wp_ajax_delete_project_member', 'delete_project_member');


    function delete_team(){
        if (!isset($_POST['action']) || $_POST['action'] !== 'delete_team') {
            wp_send_json_error(['message' => 'Invalid request.']);
            wp_die();
        }

        $post_id = sanitize_text_field($_POST['team_id']);

        // Delete the post
        $result = wp_delete_post($post_id, true); // true for force delete

        if ($result) {
            wp_send_json_success(['message' => 'Team deleted successfully.']);
        } else {
            wp_send_json_error(['message' => 'Failed to delete team.']);
        }
        wp_die();
    }

    add_action('wp_ajax_delete_team', 'delete_team');   


    function delete_project(){
        if (!isset($_POST['action']) || $_POST['action'] !== 'delete_project') {
            wp_send_json_error(['message' => 'Invalid request.']);
            wp_die();
        }

        $post_id = sanitize_text_field($_POST['project_id']);

        // Delete the post
        $result = wp_delete_post($post_id, true); // true for force delete

        if ($result) {
            wp_send_json_success(['message' => 'Project deleted successfully.']);
        } else {
            wp_send_json_error(['message' => 'Failed to delete project.']);
        }
        wp_die();   
    }

    add_action('wp_ajax_delete_project', 'delete_project'); 