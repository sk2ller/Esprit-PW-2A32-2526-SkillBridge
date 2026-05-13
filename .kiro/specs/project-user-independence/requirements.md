# Document de Requirements

## Introduction

Cette fonctionnalité vise à rendre les projets de la plateforme SkillBridge indépendants des utilisateurs. Actuellement, un projet est fortement couplé à un utilisateur client (`id_client`) : il ne peut pas exister sans propriétaire, et plusieurs opérations (paiement, affichage, filtrage) supposent qu'un client est toujours associé. L'objectif est de découpler cette relation afin que les projets puissent être créés, gérés, publiés et archivés sans nécessiter un utilisateur client rattaché, tout en préservant la possibilité d'associer un client lorsque c'est pertinent.

## Glossaire

- **Système** : L'application web SkillBridge dans son ensemble.
- **Project** : Une entité représentant un projet de travail, avec un titre, une description, un budget, un statut et un état de validation.
- **Client** : Un utilisateur avec le rôle client (id_role = 2) pouvant être associé à un projet.
- **Freelancer** : Un utilisateur avec le rôle freelancer (id_role = 3) pouvant postuler à un projet et réaliser des tâches.
- **Admin** : Un utilisateur avec le rôle administrateur (id_role = 1) gérant la plateforme.
- **Candidature** : Une demande d'un Freelancer pour participer à un Project.
- **Tache** : Une unité de travail rattachée à un Project, réalisée par un Freelancer.
- **ProjectController** : Le contrôleur PHP gérant les opérations CRUD sur les projets.
- **CandidatureController** : Le contrôleur PHP gérant les candidatures et les tâches.
- **id_client** : L'identifiant optionnel d'un Client associé à un Project.

---

## Requirements

### Requirement 1 : Création de projet sans client obligatoire

**User Story :** En tant qu'Admin, je veux pouvoir créer un projet sans associer de client, afin de prépublier des projets indépendamment de l'existence d'un compte client.

#### Critères d'acceptation

1. THE ProjectController SHALL accepter la création d'un Project avec un `id_client` nul ou absent.
2. WHEN un Project est créé sans `id_client`, THE Système SHALL enregistrer le Project avec `id_client = NULL` en base de données.
3. IF un `id_client` est fourni lors de la création, THEN THE ProjectController SHALL vérifier que l'utilisateur correspondant existe avant d'enregistrer le Project.
4. THE ProjectController SHALL valider les champs `titre`, `description`, `budget`, `date_creation` et `statut` indépendamment de la présence d'un `id_client`.

---

### Requirement 2 : Affichage et listage des projets sans client

**User Story :** En tant qu'Admin, je veux voir tous les projets dans le back-office, qu'ils aient un client associé ou non, afin d'avoir une vue complète de l'activité.

#### Critères d'acceptation

1. WHEN le back-office liste les projets, THE ProjectController SHALL retourner tous les Projects, y compris ceux dont `id_client` est NULL.
2. WHEN un Project n'a pas de Client associé, THE Système SHALL afficher « — » ou « Non assigné » à la place du nom du client dans les vues back-office.
3. THE ProjectController SHALL utiliser un `LEFT JOIN` sur la table `user` pour récupérer le nom du client, de sorte que l'absence de client ne supprime pas le projet des résultats.
4. WHEN le front-office liste les projets publiés, THE ProjectController SHALL retourner tous les Projects dont `etat = 'publie'`, indépendamment de la valeur de `id_client`.

---

### Requirement 3 : Modification d'un projet sans client obligatoire

**User Story :** En tant qu'Admin, je veux pouvoir modifier un projet existant sans être contraint de lui associer un client, afin de maintenir la flexibilité de gestion.

#### Critères d'acceptation

1. THE ProjectController SHALL permettre la mise à jour d'un Project en laissant `id_client` à NULL.
2. WHEN un Admin modifie un Project et ne fournit pas de `id_client`, THE ProjectController SHALL conserver la valeur NULL pour `id_client` sans générer d'erreur.
3. WHEN un Admin modifie un Project et fournit un `id_client`, THE ProjectController SHALL vérifier que l'utilisateur correspondant existe avant d'enregistrer la modification.
4. THE ProjectController SHALL mettre à jour les champs `titre`, `description`, `budget`, `date_creation` et `statut` indépendamment de la présence d'un `id_client`.

---

### Requirement 4 : Gestion des candidatures sur des projets sans client

**User Story :** En tant que Freelancer, je veux pouvoir postuler à un projet même si celui-ci n'a pas de client associé, afin de ne pas être bloqué par l'absence d'un propriétaire.

#### Critères d'acceptation

1. WHEN un Freelancer postule à un Project dont `id_client` est NULL, THE CandidatureController SHALL enregistrer la Candidature sans erreur.
2. THE CandidatureController SHALL lister les candidatures d'un Project indépendamment de la valeur de `id_client` du Project.
3. WHEN le statut d'une Candidature est changé à `accepte`, THE CandidatureController SHALL mettre à jour le statut du Project à `en_cours` indépendamment de la valeur de `id_client`.

---

### Requirement 5 : Gestion des tâches sur des projets sans client

**User Story :** En tant que Freelancer accepté, je veux pouvoir ajouter et gérer des tâches sur un projet sans client associé, afin de travailler normalement sur tous les projets.

#### Critères d'acceptation

1. WHEN un Freelancer ajoute une Tache à un Project dont `id_client` est NULL, THE CandidatureController SHALL enregistrer la Tache sans erreur.
2. THE CandidatureController SHALL calculer le budget restant d'un Project indépendamment de la valeur de `id_client`.
3. WHEN toutes les Taches d'un Project sont terminées et que l'avancement est à 100, THE CandidatureController SHALL passer le statut du Project à `termine` indépendamment de la valeur de `id_client`.

---

### Requirement 6 : Paiement des tâches avec ou sans client

**User Story :** En tant qu'Admin ou Client, je veux pouvoir déclencher le paiement d'une tâche terminée, même si le projet n'a pas de client associé, afin de ne pas bloquer la rémunération des freelancers.

#### Critères d'acceptation

1. WHEN un Admin déclenche le paiement d'une Tache terminée sur un Project dont `id_client` est NULL, THE CandidatureController SHALL marquer la Tache comme payée sans erreur.
2. WHEN un Client déclenche le paiement d'une Tache, THE CandidatureController SHALL vérifier que le Client est bien associé au Project avant d'autoriser le paiement.
3. IF une Tache appartient à un Project dont `id_client` est NULL et que l'acteur est un Client, THEN THE CandidatureController SHALL refuser le paiement et retourner un message d'erreur explicite.
4. THE CandidatureController SHALL vérifier que la Tache a le statut `termine` et n'est pas déjà marquée `payee` avant d'autoriser tout paiement, indépendamment de la valeur de `id_client`.

---

### Requirement 7 : Intégrité référentielle lors de la suppression d'un utilisateur

**User Story :** En tant qu'Admin, je veux que la suppression d'un utilisateur client ne supprime pas les projets qui lui sont associés, afin de préserver l'historique des projets.

#### Critères d'acceptation

1. WHEN un Client est supprimé, THE Système SHALL mettre à jour `id_client` à NULL pour tous les Projects associés à cet utilisateur.
2. WHEN un Client est supprimé, THE Système SHALL conserver les Projects, Candidatures et Taches associés sans les supprimer.
3. THE Système SHALL utiliser une contrainte de clé étrangère avec `ON DELETE SET NULL` sur la colonne `id_client` de la table `projet`, ou implémenter cette logique dans le UserController avant la suppression.

---

### Requirement 8 : Export et statistiques indépendants du client

**User Story :** En tant qu'Admin, je veux que les exports PDF et les statistiques incluent tous les projets, qu'ils aient un client ou non, afin d'avoir des rapports complets.

#### Critères d'acceptation

1. WHEN un Admin exporte les projets en PDF, THE ProjectController SHALL inclure tous les Projects dans l'export, indépendamment de la valeur de `id_client`.
2. WHEN le Système calcule les statistiques des projets, THE ProjectController SHALL comptabiliser tous les Projects, indépendamment de la valeur de `id_client`.
3. WHERE un Project n'a pas de Client associé, THE ProjectController SHALL afficher « Non assigné » dans le champ client de l'export PDF.
