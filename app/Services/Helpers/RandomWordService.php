<?php

namespace App\Services\Helpers;

use Filament\Schemas\Components\Utilities\Set;

class RandomWordService
{
    public static function setRandomName(Set $set): void
    {
        $set('name', self::generate());
    }

    public static function generate(): string
    {
        $words = [
            'Blue Tiger',
            'Silent River',
            'Cosmic Fox',
            'Crimson Hawk',
            'Golden Wolf',
            'Shadow Panther',
            'Silver Falcon',
            'Emerald Serpent',
            'Fission Falcon',
            'Quantum Bear',
            'Radiant Eagle',
            'Stellar Lion',
            'Thunder Dragon',
            'Vortex Owl',
            'Zephyr Cheetah',
            'Luminous Rabbit',
            'Obsidian Cobra',
            'Phoenix Stallion',
            'Sapphire Dolphin',
            'Titanium Rhino',
            'Unknown Xira',
            'Billie Jean',
            'El Dorado',
            'Ayodhya',
            'A Rex Server',
            'Colorless Nitrus',
            'Laravel',
            'Mike Jackson',
            'A Reviactyl Server',
            'Anjani',
            'Bairan',
            'Skinwalker',
            'Kingdom of Mysteries',
            'The Enchanted Forest',
            'Grand Library of Alexandria',
        ];

        return $words[array_rand($words)];
    }
}
