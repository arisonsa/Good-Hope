<?php

namespace App\View\Components\M3;

use Illuminate\View\Component;

class Card extends Component
{
    public ?string $href;
    public ?string $imageUrl;
    public ?string $imageAlt;
    public ?string $title;
    public ?string $subtitle;
    public string $variant; // 'elevated', 'filled', 'outlined'
    public bool $interactive;

    /**
     * Create a new component instance.
     *
     * @param string|null $href URL if the card is clickable
     * @param string|null $imageUrl URL for the card image (optional)
     * @param string|null $imageAlt Alt text for the image
     * @param string|null $title Card title
     * @param string|null $subtitle Smaller text often below title
     * @param string $variant Card style: 'elevated', 'filled', 'outlined'
     * @param bool $interactive If true and href is present, makes the whole card a link
     * @return void
     */
    public function __construct(
        ?string $href = null,
        ?string $imageUrl = null,
        ?string $imageAlt = null, // Allow null to let Lit component default if needed
        ?string $title = null,
        ?string $subtitle = null,
        string $variant = 'elevated',
        bool $interactive = false
    ) {
        $this->href = $href;
        $this->imageUrl = $imageUrl;
        $this->imageAlt = $imageAlt ?? $title ?? ''; // Default alt to title if imageAlt is null
        $this->title = $title;
        $this->subtitle = $subtitle;
        $this->variant = $variant;
        // Lit component handles the logic: interactive is only true if href is also present.
        // Blade component can simply pass the user's intent.
        $this->interactive = $interactive;
    }


    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.m3.card');
    }
}
