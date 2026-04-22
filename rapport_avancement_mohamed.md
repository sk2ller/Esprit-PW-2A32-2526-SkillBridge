# Rapport d'avancement - Partie User / Freelancer

## À faire

- Enrichir davantage le profil des freelances si nécessaire
- Finaliser les dernières retouches visuelles de l'interface
- Vérifier complètement le responsive sur mobile
- Effectuer une vérification finale de compatibilité avec toutes les fonctionnalités existantes

## En cours

- Ajustements visuels et ergonomiques après les derniers retours
- Vérification finale du bon fonctionnement global

## Terminé

- Analyse de la structure MVC existante du projet
- Identification des fichiers à modifier pour la partie user/freelancer
- Ajout des champs essentiels du profil freelance :
  - phone
  - bio
  - profile picture
  - skill summary
  - experience description
- Mise à jour de `Models/User.php` avec les attributs, getters et setters nécessaires
- Mise à jour de `Controllers/UserController.php` pour charger et sauvegarder correctement les nouveaux champs
- Mise à jour de `Views/Frontoffice/profile.php` pour permettre au freelance de modifier ses informations
- Ajout d'un message d'encouragement pour inciter le freelance à compléter son profil
- Affichage enrichi des informations du freelance côté client dans `?action=freelancers`
- Conservation du fonctionnement sur la même page sans création d'une nouvelle route
- Amélioration de l'ouverture des détails d'un freelance côté client avec une présentation plus professionnelle
- Ajout d'un bouton de fermeture dans l'affichage détaillé
- Déplacement des boutons d'évaluation dans la vue détaillée du freelance
- Conservation du système existant de likes/dislikes
- Conservation du système existant de filtrage des freelances
- Respect de la contrainte de ne pas ajouter les nouveaux champs dans la table admin des utilisateurs
- Ajout d'une vidéo dans la section hero de la page d'accueil
- Transformation du hero en version plus moderne inspirée du style Fiverr
- Ajout d'un overlay sombre sur la vidéo pour améliorer la lisibilité du texte
- Suppression des textes inutiles affichés sur la vidéo du hero
- Correction du formulaire d'inscription pour cacher le niveau d'expérience pour le client
- Affichage du niveau d'expérience uniquement lorsque le rôle freelance est sélectionné
- Maintien de la validation côté PHP
- Vérification syntaxique des fichiers PHP modifiés
- Commit et push de la version mise à jour sur la branche `mohamed-user`
