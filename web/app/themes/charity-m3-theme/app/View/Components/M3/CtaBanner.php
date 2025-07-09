<?php

namespace App\View\Components\M3;

use Illuminate\View\Component;
use Illuminate\Support\Str;

class CtaBanner extends Component
{
    public ?string $title;
    public ?string $text; // Main text/description
    public array $buttons;
    public ?string $backgroundImage;
    public string $backgroundColor;
    public string $textColor;
    public string $textAlignment;
    public string $contentWidth;
    public string $padding; // e.g. 'py-12', 'py-16 md:py-24'

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(
        ?string $title = null,
        ?string $text = null,
        array $buttons = [],
        ?string $backgroundImage = null,
        string $backgroundColor = 'bg-primary-container', // M3 default
        ?string $textColor = null,
        string $textAlignment = 'text-center',
        string $contentWidth = 'container', // 'container', 'narrow', 'wide', 'full'
        string $padding = 'py-12 md:py-20'
    ) {
        $this->title = $title;
        $this->text = $text;
        $this->buttons = $buttons;
        $this->backgroundImage = $backgroundImage;
        $this->backgroundColor = $backgroundColor;
        $this->textAlignment = $textAlignment;
        $this->contentWidth = $this->getContentWidthClass($contentWidth);
        $this->padding = $padding;
        $this->textColor = $textColor ?? $this->getDefaultTextColor($backgroundColor);
    }

    protected function getContentWidthClass(string $widthAlias): string
    {
        // Consistent with Hero component
        switch ($widthAlias) {
            case 'container': return 'container mx-auto px-4';
            case 'narrow': return 'max-w-3xl mx-auto px-4';
            case 'wide': return 'max-w-7xl mx-auto px-4';
            case 'full': return 'w-full px-4';
            case 'edge-to-edge': return 'w-full';
            default: return $widthAlias;
        }
    }

    protected function getDefaultTextColor(string $bgColor): string
    {
        // Consistent with Hero component
        $colorName = Str::of($bgColor)->remove('bg-');
        if (Str::endsWith($colorName, ['-container', '-variant', '-fixed'])) {
             return "text-on-{$colorName}";
        }
        if (Str::contains($colorName, ['primary', 'secondary', 'tertiary', 'error', 'surface', 'background'])) {
            return "text-on-{$colorName}";
        }
        return 'text-black dark:text-white';
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.m3.cta-banner');
    }
}
