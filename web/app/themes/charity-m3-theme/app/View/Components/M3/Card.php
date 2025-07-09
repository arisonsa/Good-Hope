<?php

namespace App\View\Components\M3;

use Illuminate\View\Component;

class Card extends Component
{
    public ?string $href;
    public ?string $imageUrl;
    public ?string $imageAlt;
    public ?string $title;
    public ?string $subtitle; // Smaller text below title
    public ?string $variant; // 'elevated', 'filled', 'outlined'
    public bool $interactive; // If true, whole card is a link if href is provided

    /**
     * Create a new component instance.
     *
     * @param string|null $href URL if the card is clickable
     * @param string|null $imageUrl URL for the card image (optional)
     * @param string|null $imageAlt Alt text for the image
     * @param string|null $title Card title
     * @param string|null $subtitle Smaller text often below title
     * @param string $variant Card style: 'elevated', 'filled', 'outlined' (maps to MWC)
     * @param bool $interactive If true and href is present, makes the whole card a link
     * @return void
     */
    public function __construct(
        ?string $href = null,
        ?string $imageUrl = null,
        ?string $imageAlt = '',
        ?string $title = null,
        ?string $subtitle = null,
        string $variant = 'elevated', // MWC default is often filled or elevated
        bool $interactive = false
    ) {
        $this->href = $href;
        $this->imageUrl = $imageUrl;
        $this->imageAlt = $imageAlt ?: $title; // Default alt to title if not provided
        $this->title = $title;
        $this->subtitle = $subtitle;
        $this->variant = $variant;
        $this->interactive = $interactive && $href; // Interactive only if href is also present
    }

    /**
     * Get the appropriate MWC tag name for the card variant.
     */
    public function mwcCardTag(): string
    {
        // Note: As of current @material/web, there isn't a direct <md-card> element.
        // Cards are constructed using divs with appropriate M3 styling (elevation, shape, color).
        // We will use regular HTML elements and apply M3 classes/styles.
        // If a specific MWC card element becomes available, this can be updated.
        // For now, we simulate the concept of variants through classes.
        return 'div'; // Placeholder, actual styling will be class-based
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
