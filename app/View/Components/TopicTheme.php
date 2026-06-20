<?php

namespace App\View\Components;

class TopicTheme
{
    public function __construct(private readonly string $topic) {}

    public function headerBg(): string
    {
        return match($this->topic) {
            'nextjs' => 'bg-gray-900',
            'aws'    => 'bg-sky-400',
            default  => 'bg-orange-500',
        };
    }

    public function accentText(): string
    {
        return match($this->topic) {
            'nextjs' => 'text-gray-900',
            'aws'    => 'text-sky-400',
            default  => 'text-orange-500',
        };
    }

    public function borderAccent(): string
    {
        return match($this->topic) {
            'nextjs' => 'border-gray-900',
            'aws'    => 'border-sky-400',
            default  => 'border-orange-500',
        };
    }

    public function hoverText(): string
    {
        return match($this->topic) {
            'nextjs' => 'hover:text-gray-600',
            'aws'    => 'hover:text-sky-400',
            default  => 'hover:text-orange-500',
        };
    }

    // ナビボタンのクラスを返す（アクティブ/非アクティブ）
    public function navButton(string $buttonTopic): string
    {
        if ($this->topic !== $buttonTopic) {
            return 'bg-white/20 text-white hover:bg-white/30';
        }

        $textColor = match($buttonTopic) {
            'nextjs' => 'text-gray-900',
            'aws'    => 'text-sky-400',
            default  => 'text-orange-500',
        };

        return "bg-white {$textColor}";
    }
}
