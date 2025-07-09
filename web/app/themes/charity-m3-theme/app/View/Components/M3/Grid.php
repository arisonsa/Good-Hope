<?php

namespace App\View\Components\M3;

use Illuminate\View\Component;

class Grid extends Component
{
    public string $cols; // e.g., '1', '2', '3', '4' for default, or full Tailwind class like 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3'
    public string $gap;  // e.g., '4', '6', '8' for default, or full Tailwind class like 'gap-4'
    public ?string $tag; // HTML tag for the grid container, defaults to 'div'

    /**
     * Create a new component instance.
     *
     * @param string $cols Number of columns (1-4) or full Tailwind class.
     * @param string $gap Gap size (numeric, corresponds to Tailwind spacing scale) or full Tailwind class.
     * @param string|null $tag The HTML tag to use for the grid container.
     * @return void
     */
    public function __construct(
        string $cols = '1 md:grid-cols-2 lg:grid-cols-3', // Default responsive columns
        string $gap = '6', // Default gap, matches Lit component default
        ?string $tag = 'div'
    ) {
        // Pass values directly; Lit component will handle defaults and parsing
        $this->cols = $cols;
        $this->gap = $gap;
        $this->tag = $tag ?: 'div'; // Ensure tag has a default
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.m3.grid');
    }
}
