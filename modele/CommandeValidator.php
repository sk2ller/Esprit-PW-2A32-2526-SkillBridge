<?php
declare(strict_types=1);

namespace App\Modele;

final class CommandeValidator
{
    /**
     * Valide les données d'une commande
     * @param array $input Données brutes du formulaire
     * @return array ['data' => array, 'errors' => array]
     */
    public static function validate(array $input): array
    {
        $errors = [];
        $data = [];

        // Validation statut
        $statut = trim($input['statut'] ?? '');
        $allowedStatuts = ['en_attente', 'en_cours', 'livree', 'annulee'];
        if (!in_array($statut, $allowedStatuts, true)) {
            $errors['statut'] = 'Statut invalide. Valeurs autorisees: en_attente, en_cours, livree, annulee.';
        } else {
            $data['statut'] = $statut;
        }

        // Validation prix
        $prixStr = trim($input['prix_total'] ?? '');
        if (!preg_match('/^\\d+(\\.\\d{1,2})?$/', $prixStr) || (float)$prixStr <= 0) {
            $errors['prix_total'] = 'Prix invalide. Doit etre > 0. Ex: 100 ou 100.50.';
        } else {
            $data['prix_total'] = (float)$prixStr;
        }

        // Validation dates
        $dateCreation = trim($input['date_creation'] ?? '');
        if (!self::isIsoDate($dateCreation)) {
            $errors['date_creation'] = 'Format date invalide. Attendu: YYYY-MM-DD.';
        } else {
            $data['date_creation'] = $dateCreation;
        }

        $dateLivraison = trim($input['date_livraison'] ?? '');
        if (!self::isIsoDate($dateLivraison)) {
            $errors['date_livraison'] = 'Format date invalide. Attendu: YYYY-MM-DD.';
        } else {
            $data['date_livraison'] = $dateLivraison;
        }

        // Comparaison dates
        if (isset($data['date_creation']) && isset($data['date_livraison'])) {
            if (strtotime($data['date_livraison']) < strtotime($data['date_creation'])) {
                $errors['date_livraison'] = 'Date livraison doit etre >= date creation.';
            }
        }

        // Validation IDs
        $idService = trim($input['id_service'] ?? '');
        if (!preg_match('/^\\d+$/', $idService) || (int)$idService <= 0) {
            $errors['id_service'] = 'ID service invalide.';
        } else {
            $data['id_service'] = (int)$idService;
        }

        $idClient = trim($input['id_client'] ?? '');
        if (!preg_match('/^\\d+$/', $idClient) || (int)$idClient <= 0) {
            $errors['id_client'] = 'ID client invalide.';
        } else {
            $data['id_client'] = (int)$idClient;
        }

        return [
            'errors' => $errors,
            'data' => $errors === [] ? $data : [],
        ];
    }

    private static function isIsoDate(string $value): bool
    {
        if (!preg_match('/^\\d{4}-\\d{2}-\\d{2}$/', $value)) {
            return false;
        }

        $parts = explode('-', $value);
        $date = \DateTime::createFromFormat('Y-m-d', $value);

        if ($date === false) {
            return false;
        }

        // Vérifier que la date est valide (ex: pas 2026-02-30)
        return $date->format('Y-m-d') === $value;
    }
}
