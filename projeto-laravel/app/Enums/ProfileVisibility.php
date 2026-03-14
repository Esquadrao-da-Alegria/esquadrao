<?php

namespace App\Enums;

enum ProfileVisibility: string
{
    case PUBLIC = 'public';
    case PRIVATE = 'private';

    /**
     * Get the display label for the visibility option
     */
    public function getLabel(): string
    {
        return match($this) {
            self::PUBLIC => 'Perfil Público',
            self::PRIVATE => 'Perfil Privado',
        };
    }

    /**
     * Get the description for the visibility option
     */
    public function getDescription(): string
    {
        return match($this) {
            self::PUBLIC => 'Seu perfil será visível na listagem de voluntários',
            self::PRIVATE => 'Seu perfil será privado e não aparecerá na listagem',
        };
    }

    /**
     * Get all visibility options as array
     */
    public static function getOptions(): array
    {
        return [
            self::PUBLIC->value => self::PUBLIC->getLabel(),
            self::PRIVATE->value => self::PRIVATE->getLabel(),
        ];
    }

    /**
     * Get all visibility options with descriptions
     */
    public static function getOptionsWithDescriptions(): array
    {
        return [
            [
                'value' => self::PUBLIC->value,
                'label' => self::PUBLIC->getLabel(),
                'description' => self::PUBLIC->getDescription(),
            ],
            [
                'value' => self::PRIVATE->value,
                'label' => self::PRIVATE->getLabel(),
                'description' => self::PRIVATE->getDescription(),
            ],
        ];
    }
}
