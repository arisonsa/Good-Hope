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
        string $gap = '6', // Default gap
        ?string $tag = 'div'
    ) {
        $this->cols = $this->parseCols($cols);
        $this->gap = $this->parseGap($gap);
        $this->tag = $tag ?: 'div';
    }

    private function parseCols(string $colsInput): string
    {
        // If it contains 'grid-cols-', assume it's a full Tailwind class string
        if (str_contains($colsInput, 'grid-cols-')) {
            return $colsInput;
        }
        // Otherwise, treat as simple number for default responsive behavior
        // This can be expanded to generate more complex responsive classes based on a number
        switch ($colsInput) {
            case '1':
                return 'grid-cols-1';
            case '2':
                return 'grid-cols-1 md:grid-cols-2';
            case '3':
                return 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3';
            case '4':
                return 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-4';
            default: // Fallback to input if it's not a simple number or recognized pattern
                return $colsInput;
        }
    }

    private function parseGap(string $gapInput): string
    {
        // If it contains 'gap-', assume it's a full Tailwind class string
        if (str_contains($gapInput, 'gap-')) {
            return $gapInput;
        }
        // Otherwise, treat as simple number for Tailwind spacing scale
        if (is_numeric($gapInput)) {
            return "gap-{$gapInput}";
        }
        return "gap-6"; // Default fallback
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
