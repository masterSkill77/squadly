<?php

namespace App\Services;

class SportTemplateService
{
    public static function all(): array
    {
        return [
            'football' => [
                'fields' => [
                    ['key' => 'position', 'label' => 'Poste', 'type' => 'select', 'options' => ['Gardien', 'Défenseur', 'Milieu', 'Attaquant']],
                    ['key' => 'dominant_foot', 'label' => 'Pied dominant', 'type' => 'select', 'options' => ['Gauche', 'Droit', 'Les deux']],
                    ['key' => 'jersey_number', 'label' => 'Numéro de maillot', 'type' => 'number'],
                ],
            ],
            'basketball' => [
                'fields' => [
                    ['key' => 'position', 'label' => 'Poste', 'type' => 'select', 'options' => ['Meneur', 'Arrière', 'Ailier', 'Ailier fort', 'Pivot']],
                    ['key' => 'jersey_number', 'label' => 'Numéro de maillot', 'type' => 'number'],
                    ['key' => 'dominant_hand', 'label' => 'Main dominante', 'type' => 'select', 'options' => ['Gauche', 'Droite']],
                ],
            ],
            'handball' => [
                'fields' => [
                    ['key' => 'position', 'label' => 'Poste', 'type' => 'select', 'options' => ['Gardien', 'Ailier', 'Arrière', 'Demi-centre', 'Pivot']],
                    ['key' => 'jersey_number', 'label' => 'Numéro de maillot', 'type' => 'number'],
                ],
            ],
            'natation' => [
                'fields' => [
                    ['key' => 'specialty', 'label' => 'Spécialité', 'type' => 'select', 'options' => ['Crawl', 'Dos', 'Brasse', 'Papillon', 'Quatre nages']],
                    ['key' => 'best_distance', 'label' => 'Distance favorite', 'type' => 'select', 'options' => ['50m', '100m', '200m', '400m', '800m', '1500m']],
                ],
            ],
            'rugby' => [
                'fields' => [
                    ['key' => 'position', 'label' => 'Poste', 'type' => 'select', 'options' => ['Pilier', 'Talonneur', 'Deuxième ligne', 'Flanker', 'Numéro 8', 'Demi de mêlée', 'Demi d\'ouverture', 'Centre', 'Ailier', 'Arrière']],
                    ['key' => 'jersey_number', 'label' => 'Numéro de maillot', 'type' => 'number'],
                ],
            ],
            'volleyball' => [
                'fields' => [
                    ['key' => 'position', 'label' => 'Poste', 'type' => 'select', 'options' => ['Passeur', 'Attaquant', 'Central', 'Libéro', 'Réceptionneur']],
                    ['key' => 'jersey_number', 'label' => 'Numéro de maillot', 'type' => 'number'],
                ],
            ],
        ];
    }

    public static function keys(): array
    {
        return array_keys(self::all());
    }

    public static function get(string $sport): ?array
    {
        return self::all()[$sport] ?? null;
    }
}
