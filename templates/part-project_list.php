<?php
/**
 * Template Part: project_list
 * Displayed only for administrators and editors
 */
?>

<div class="container project_list">
    <h1>Projects list  </h1>
    <ul>
        <?php
              
                $args = array(
                    'post_type' => 'projects',
                    'posts_per_page' => -1, 
                    'post_status' => 'publish',
                    'orderby' => 'title',
                    'order' => 'ASC'
                );

                $project_query = new WP_Query($args);

               
                if ($project_query->have_posts()) : 
    ?>
        <?php
                       
                        while ($project_query->have_posts()) : $project_query->the_post();
                            $post_id = get_the_ID();
                            $post_link =  get_post_permalink();
    ?>
        <li><a href="<?php echo $post_link; ?>"><?php the_title(); ?></a></li>
        <?php endwhile; ?>
        <?php
                    wp_reset_postdata();
                else : ?>
        <p>No teams found.</p>
        <?php endif; ?>
    </ul>

</div>
