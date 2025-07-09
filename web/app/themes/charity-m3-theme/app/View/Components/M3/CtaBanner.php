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
    public ?string $textColor; // Nullable, Lit component has defaults
    public string $textAlignment;
    public string $contentWidth;
    public string $padding;
    public bool $showOverlay;


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
        ?string $backgroundColor = null, // Let Lit component default
        ?string $textColor = null,       // Let Lit component default
        string $textAlignment = 'center',
        string $contentWidth = 'container',
        string $padding = '3rem 0', // Default matching Lit component's expectation for direct style
        bool $showOverlay = true // Default matching Lit component
    ) {
        $this->title = $title;
        $this->text = $text;
        $this->buttons = $buttons;
        $this->backgroundImage = $backgroundImage;
        $this->backgroundColor = $backgroundColor; // Pass through
        $this->textColor = $textColor;             // Pass through
        $this->textAlignment = $textAlignment;
        $this->contentWidth = $contentWidth;       // Pass through alias
        $this->padding = $padding;                 // Pass through CSS value
        $this->showOverlay = $showOverlay;
        // No complex class/color calculations needed here anymore
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
