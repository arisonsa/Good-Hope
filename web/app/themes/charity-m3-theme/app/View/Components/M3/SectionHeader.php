<?php

namespace App\View\Components\M3;

use Illuminate\View\Component;

class SectionHeader extends Component
{
    public ?string $title;
    public ?string $subtitle;

    /**
     * Create a new component instance.
     *
     * @param string|null $title
     * @param string|null $subtitle
     * @return void
     */
    public function __construct(?string $title = null, ?string $subtitle = null)
    {
        $this->title = $title;
        $this->subtitle = $subtitle;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.m3.section-header');
    }
}
