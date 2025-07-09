<?php

namespace App\View\Components\M3;

use Illuminate\View\Component;

class Button extends Component
{
    public string $type; // 'filled', 'outlined', 'text', 'elevated', 'tonal'
    public string $tag; // 'md-filled-button', 'md-outlined-button', etc.
    public ?string $icon;
    public bool $trailingIcon;
    public ?string $href;
    public string $elType; // 'button' or 'a'

    /**
     * Create a new component instance.
     *
     * @param string $type The type of button (filled, outlined, text, etc.)
     * @param string|null $icon The name of the Material Symbol icon.
     * @param bool $trailingIcon Whether the icon should be trailing.
     * @param string|null $href If provided, renders an anchor tag styled as a button.
     * @return void
     */
    public function __construct(
        string $type = 'filled',
        ?string $icon = null,
        bool $trailingIcon = false,
        ?string $href = null
    ) {
        $this->type = $type;
        $this->icon = $icon;
        $this->trailingIcon = $trailingIcon;
        $this->href = $href;
        $this->elType = $href ? 'a' : 'button';

        switch ($type) {
            case 'outlined':
                $this->tag = 'md-outlined-button';
                break;
            case 'text':
                $this->tag = 'md-text-button';
                break;
            case 'elevated':
                $this->tag = 'md-elevated-button';
                break;
            case 'tonal': // Filled tonal button
                $this->tag = 'md-filled-tonal-button';
                break;
            case 'filled':
            default:
                $this->tag = 'md-filled-button';
                break;
        }
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.m3.button');
    }
}
