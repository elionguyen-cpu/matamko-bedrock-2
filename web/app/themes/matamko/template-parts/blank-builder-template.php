<?php

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

while (have_posts()) {
    the_post();
    the_content();
}
