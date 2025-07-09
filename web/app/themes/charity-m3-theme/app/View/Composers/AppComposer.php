<?php

namespace App\View\Composers;

use Roots\Acorn\View\Composer;

class AppComposer extends Composer
{
    /**
     * List of views served by this composer.
     *
     * @var array
     */
    protected static $views = [
        '*', // Apply to all views
    ];

    /**
     * Data to be passed to view before rendering.
     *
     * @return array
     */
    public function with()
    {
        return [
            'siteName' => $this->getSiteName(),
        ];
    }

    /**
     * Get the site name.
     *
     * @return string
     */
    public function getSiteName()
    {
        return get_bloginfo('name', 'display');
    }
}
