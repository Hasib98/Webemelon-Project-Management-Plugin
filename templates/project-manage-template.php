<?php
defined('ABSPATH') || exit;
?>
<!DOCTYPE html>
<html lang="<?php language_attributes(); ?> ">

<head>
    <meta charset=" <?php bloginfo('charset') ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head() ?>
</head>
<body>
    <?php echo do_shortcode('[project_manage_page]'); ?>
    <?php wp_footer(); ?>
    
</body>

</html>



