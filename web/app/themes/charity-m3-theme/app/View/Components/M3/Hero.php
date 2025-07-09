<?php

namespace App\View\Components\M3;

use Illuminate\View\Component;
use Illuminate\Support\Str;

class Hero extends Component
{
    public ?string $title;
    public ?string $subtitle;
    public ?string $backgroundImage;
    public ?string $backgroundColor; // Will be CSS var string e.g. M3SysColors.surfaceVariant
    public ?string $textColor;       // Will be CSS var string e.g. M3SysColors.onSurfaceVariant
    public string $contentWidth;    // Alias: 'container', 'narrow', 'wide', 'full', 'edge-to-edge'
    public string $textAlignment;   // 'left', 'center', 'right'
    public array $buttons;          // Array of button data
    public string $minHeight;       // e.g., '60vh', '500px'
    public bool $showOverlay;

    /**
     * Create a new component instance.
     *
     * @param string|null $title
     * @param string|null $subtitle
     * @param string|null $backgroundImage URL for background image
     * @param string|null $backgroundColor CSS color value (e.g., StyleXJS token like M3SysColors.surfaceVariant)
     * @param string|null $textColor CSS color value (e.g., StyleXJS token like M3SysColors.onSurfaceVariant)
     * @param string $contentWidth Alias for content width: 'container', 'narrow', 'wide', 'full', 'edge-to-edge'
     * @param string $textAlignment Text alignment: 'left', 'center', 'right'
     * @param array $buttons Array of button configurations
     * @param string $minHeight Minimum height CSS value for the hero section
     * @param bool $showOverlay Whether to show a dark overlay on background image
     * @return void
     */
    public function __construct(
        ?string $title = null,
        ?string $subtitle = null,
        ?string $backgroundImage = null,
        // Defaulting to token references (which are CSS var strings)
        ?string $backgroundColor = null, // Let Lit component default or be undefined
        ?string $textColor = null,       // Let Lit component default or be undefined
        string $contentWidth = 'container',
        string $textAlignment = 'center',
        array $buttons = [],
        string $minHeight = '60vh',
        bool $showOverlay = false // Default to false, Lit component can default to true if bg image exists
    ) {
        $this->title = $title;
        $this->subtitle = $subtitle;
        $this->backgroundImage = $backgroundImage;
        $this->backgroundColor = $backgroundColor; // Pass through directly
        $this->contentWidth = $contentWidth; // Pass through alias
        $this->textAlignment = $textAlignment; // Pass through alias
        $this->buttons = $buttons;
        $this->minHeight = $minHeight; // Pass through directly
        $this->textColor = $textColor; // Pass through directly
        $this->showOverlay = $showOverlay;

        // No need for PHP-side color/class calculation, Lit/StyleXJS handles it.
    }


    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.m3.hero');
    }
}
