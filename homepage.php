<?php 
    $user = wp_get_current_user();
    if ( in_array( 'administrator', $user->roles ) || in_array( 'editor', $user->roles ) ) {
        $accessed_user= true;
    }
    else {
       $accessed_user= false;
    }
?>




<header>
    <div class="toggle_left_bar_burger_button_container">
        <img src="<?php echo plugin_dir_url(__FILE__).'/assets/images/burger-menu-left-svgrepo-com.svg'; ?>"
            alt="Toggle Icon">
    </div>
    <div class="logo_container">
        <a href="<?php echo esc_url(home_url('project-manage-page')); ?>" class="logo">
            <img src="<?php echo plugin_dir_url(__FILE__).'/assets/images/logo.png'; ?>" alt="Logo">
        </a>
    </div>
</header>


<div class="sidebar open">
    <?php 
    if($accessed_user) {
        include plugin_dir_path( __FILE__ ) . 'templates/part-create_team.php';
    } 
    ?>
   

    <div class="project">
        <div class="project_name_container">
            <h3>Projects</h3>
            <?php 
            if($accessed_user){
                echo '<button class="toggle_project_button">+</button>';
            }
            ?>
        </div>
        <form class="create_project_form">
            <input name="project_name" type="text" placeholder="Create a new project" class="create_project_input"
                required>

        </form>

        <ul class="project_list">
            <?php
                // Custom query to get team posts
                $args = array(
                    'post_type' => 'projects',
                    'posts_per_page' => -1, // Get all posts. You can limit this if needed
                    'post_status' => 'publish',
                    'orderby' => 'title',
                    'order' => 'ASC'
                );

                $project_query = new WP_Query($args);

                // Check if we have posts
                if ($project_query->have_posts()) : ?>

            <?php
                        // Start the loop
                        while ($project_query->have_posts()) : $project_query->the_post();
                            // Get the post ID
                            $post_id = get_the_ID();
                        ?>
            <li class="single_project_list" value="<?php echo $post_id; ?>"><?php the_title(); ?></li>
            <?php endwhile; ?>
            <?php
                    // Reset post data
                    wp_reset_postdata();

                else : ?>
            <p>No projects found.</p>
            <?php endif; ?>
            <!-- <li>Project 1</li>
            <li>Project 2</li>
            <li>Project 3</li>
            <li>Project 4</li> -->
        </ul>
    </div>
</div>


<div class="team_modal">
    <div class="team_modal_content">
        <span class="close_team_modal">&times;</span>
        <h2><span id="team_name"></span> Members</h2>
        <button class="delete_team_btn">Delete</button>
        <form id="invite_member_form">
            <input class="recipient_email" name="recipient_email" type="email" placeholder="Add Team Member by Email"
                required>
        </form>
        <ul class="team_member_list">
            <li><img src="https://i.pravatar.cc/24" alt="">test@testmail.com <span
                    class="delete_team_member_btn">&times;</span></li>
            <li><img src="https://i.pravatar.cc/24" alt="">test@testmail.com</li>
            <li><img src="https://i.pravatar.cc/24" alt="">test@testmail.com</li>
            <li><img src="https://i.pravatar.cc/24" alt="">test@testmail.com</li>
        </ul>

    </div>
</div>



<div class="right_sidebar">
    <?php
    
    if ($accessed_user) {
        echo '<button class="edit_project_btn">Edit Project</button>';   
        echo '<button class="delete_project_btn">Delete</button>';
    }
    ?>
    
    <span class="close_right_sidebar">&times;</span>
    <div class="right_sidebar_content">

        <h2 class="project_title">Project Name</h2>
        <p class="project_description">Lorem ipsum dolor sit amet consectetur adipisicing elit. Quisquam, voluptatibus.
        </p>
        <div class="data_container">
            <label for="">Due Date:</label>
            <div class="project_due_date"></div>
        </div>
        <div class="data_container">
            <label for="">Priority:</label> <!-- Added label for priority -->
            <div class="project_priority"></div> <!-- Added priority div -->
        </div>
        <div class="data_container">
            <label for="">Status:</label> <!-- Added label for status -->
            <div class="project_status"></div> <!-- Added status div -->
        </div>
    
        <div class="add_member_to_project_container">
            <div class="add_member_to_project_header">
                <h3>Add Member</h3>
                <?php 
                if($accessed_user) {
                    echo '<button class="add_member_to_project_button">+</button>';
                }
                ?>
            </div>
            <form class="add_member_to_project_form">
                <select class="team_list" name="team_list" id="team_list">
                    <option value="">Select Team</option>
                    <?php
                        // Custom query to get team posts
                        $args = array(
                            'post_type' => 'team',
                            'posts_per_page' => -1, // Get all posts. You can limit this if needed
                            'post_status' => 'publish',
                            'orderby' => 'title',
                            'order' => 'ASC'
                        );

                        $team_query = new WP_Query($args);

                        // Check if we have posts
                        if ($team_query->have_posts()) : ?>

                    <?php
                                // Start the loop
                                while ($team_query->have_posts()) : $team_query->the_post();
                                    // Get the post ID
                                    $post_id = get_the_ID();
                                ?>
                    <option value="<?php echo $post_id; ?>"><?php the_title(); ?></option>
                    <?php endwhile; ?>
                    <?php
                            // Reset post data
                            wp_reset_postdata();

                        else : ?>
                    <p>No teams found.</p>
                    <?php endif; ?>
                </select>
                <select class="team_member_email" name="team_member_email" id="team_member_email">
                    <option value="">Select from Team</option>
                </select>
                <button type="submit" class="add_member_to_project_btn">&#10004;</button>

            </form>
        </div>
        <ul class="project_member_list">

            <!--   <li><img src="https://i.pravatar.cc/24" alt=""> test@test.com</li>
            <li><img src="https://i.pravatar.cc/24" alt=""> alplha@alpha.com</li> -->
        </ul> <!-- Added members div -->

        <!-- <h3>Team Members</h3>
        <ul class="team_member_list">
            <li><img src="https://i.pravatar.cc/24" alt=""> 
            test@test.com</li>
            <li><img src="https://i.pravatar.cc/24" alt="">
            alplha@alpha.com</li>
        </ul> -->

    </div>
</div>

<div class="edit_project_modal">
    <div class="edit_project_modal_content">
        <span class="close_edit_project_modal">&times;</span>
        <h2>Edit Project</h2>
        <form id="edit_project_form">
            <input class="project_name_input" name="project_name" type="text" placeholder="Project Name" required>
            <textarea class="project_description_input" name="project_description" id="" cols="30" rows="5"
                placeholder="Project Description" required></textarea>
            <input class="project_due_date_input" name="project_due_date" type="date" placeholder="Due Date" required>
            <select class="project_priority_input" name="project_priority" id="">
                <option value="">Select Priority</option>
                <option value="high">High</option>
                <option value="medium">Medium</option>
                <option value="low">Low</option>
            </select>
            <select class="project_status_input" name="project_status" id="">
                <option value="">Select Status</option>
                <option value="in-progress">In Progress</option>
                <option value="completed">Completed</option>
                <option value="on-hold">On Hold</option>
            </select>
            <button type="submit" class="save_project_btn">Save</button>
        </form>

    </div>
</div>

<!-- "2018-07-22" -->

<div class="nutetops">
    <!-- Additional content can be added here -->

</div>  