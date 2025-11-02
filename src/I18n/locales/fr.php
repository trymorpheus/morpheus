<?php

return [
    // Form labels
    'form.submit' => 'Soumettre',
    'form.save' => 'Enregistrer',
    'form.cancel' => 'Annuler',
    'form.required' => 'Champ requis',
    'form.optional' => 'Optionnel',
    
    // Validation messages
    'validation.required' => 'Le champ {field} est requis',
    'validation.email' => 'Le champ {field} doit être un email valide',
    'validation.url' => 'Le champ {field} doit être une URL valide',
    'validation.number' => 'Le champ {field} doit être un nombre',
    'validation.min' => 'Le champ {field} doit être au moins {min}',
    'validation.max' => 'Le champ {field} ne doit pas dépasser {max}',
    'validation.minlength' => 'Le champ {field} doit contenir au moins {minlength} caractères',
    'validation.maxlength' => 'Le champ {field} ne doit pas dépasser {maxlength} caractères',
    'validation.pattern' => 'Le format du champ {field} est invalide',
    'validation.enum' => 'La valeur sélectionnée pour {field} est invalide',
    'validation.foreign_key' => 'La valeur sélectionnée pour {field} n\'existe pas',
    
    // Error messages
    'error.csrf_invalid' => 'Jeton CSRF invalide',
    'error.validation_failed' => 'Échec de la validation',
    'error.database' => 'Erreur de base de données: {message}',
    'error.file_upload' => 'Erreur de téléchargement de fichier: {message}',
    'error.file_size' => 'La taille du fichier dépasse le maximum autorisé ({max})',
    'error.file_type' => 'Type de fichier non autorisé',
    'error.not_found' => 'Enregistrement non trouvé',
    
    // Success messages
    'success.created' => 'Enregistrement créé avec succès',
    'success.updated' => 'Enregistrement mis à jour avec succès',
    'success.deleted' => 'Enregistrement supprimé avec succès',
    
    // M:N UI
    'm2n.select_all' => 'Sélectionner visibles',
    'm2n.clear_all' => 'Tout effacer',
    'm2n.search' => 'Rechercher...',
    'm2n.selected' => '{count} sur {total} sélectionnés',
    'm2n.hint' => 'Maintenez Ctrl (Cmd sur Mac) pour sélectionner plusieurs',
    'm2m.selected' => '{count} sélectionnés',
    'm2m.no_results' => 'Aucun résultat trouvé',
    
    // Common
    'common.yes' => 'Oui',
    'common.no' => 'Non',
    'common.save' => 'Enregistrer',
    'common.delete' => 'Supprimer',
    'common.edit' => 'Modifier',
    'common.cancel' => 'Annuler',
    'common.select' => '-- Sélectionner --',
    'common.loading' => 'Chargement...',
    'common.confirm_delete' => 'Êtes-vous sûr de vouloir supprimer cet enregistrement?',
];
