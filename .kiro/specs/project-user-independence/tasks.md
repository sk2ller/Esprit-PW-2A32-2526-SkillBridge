# Plan d'implémentation : project-user-independence

## Vue d'ensemble

Ce plan découple les projets SkillBridge de la notion d'utilisateur client. Les modifications touchent le schéma SQL, `ProjectController`, `CandidatureController`, `UserController` et les vues back-office. Les changements sont rétrocompatibles : les projets existants avec un client conservent leur association.

## Tâches

- [ ] 1. Migration SQL — contrainte FK avec ON DELETE SET NULL
  - Créer le fichier `alter_projet_fk.sql` contenant l'`ALTER TABLE` qui ajoute la contrainte `fk_projet_client` avec `ON DELETE SET NULL ON UPDATE CASCADE` sur `projet.id_client`
  - Gérer le cas où une contrainte FK sans `ON DELETE SET NULL` existe déjà (DROP puis ADD)
  - Vérifier que la colonne `id_client` est bien `INT DEFAULT NULL` (déjà présent via `alter_projet.sql`)
  - _Requirements : 7.1, 7.2, 7.3_

  - [ ]* 1.1 Smoke test — vérifier la contrainte FK
    - Écrire un test PHPUnit qui interroge `information_schema.REFERENTIAL_CONSTRAINTS` et vérifie que `fk_projet_client` existe avec `DELETE_RULE = 'SET NULL'`
    - _Requirements : 7.3_

- [ ] 2. ProjectController — méthode `updateProject()` : inclure id_client
  - Modifier la requête SQL de `updateProject()` pour inclure `id_client=:id_client` dans le `SET`
  - Ajouter `'id_client' => $project->getIdClient()` dans le tableau `execute()` (NULL autorisé)
  - _Requirements : 3.1, 3.2, 3.4_

  - [ ]* 2.1 Test de propriété P5 — round-trip mise à jour sans client
    - **Propriété 5 : Mise à jour de projet sans client — round-trip**
    - Pour tout projet existant, appeler `updateProject()` avec `id_client = NULL` puis `getProjectById()` doit retourner `id_client = NULL` et les autres champs corrects
    - **Valide : Requirements 3.1, 3.2**

- [ ] 3. ProjectController — validation optionnelle de l'existence du client
  - Dans `addProject()`, si `$project->getIdClient()` est non nul, exécuter un `SELECT id FROM user WHERE id = :id` avant l'INSERT ; retourner `false` avec `error_log` si l'utilisateur n'existe pas
  - Appliquer la même vérification dans `updateProject()` quand `id_client` est non nul
  - _Requirements : 1.3, 3.3_

  - [ ]* 3.1 Test de propriété P1 — round-trip création sans client
    - **Propriété 1 : Création de projet sans client — round-trip**
    - Pour tout ensemble de données valides sans `id_client`, `addProject()` puis `getProjectById()` doit retourner `id_client = NULL`
    - **Valide : Requirements 1.1, 1.2**

  - [ ]* 3.2 Test de propriété P2 — validation indépendante de id_client
    - **Propriété 2 : Validation des champs indépendante de id_client**
    - Pour tout jeu de données (valide ou invalide) sur `titre`, `description`, `budget`, `date_creation`, `statut`, le résultat de `validateProjectInput()` doit être identique que `id_client` soit présent, absent ou nul
    - **Valide : Requirements 1.4, 3.4**

- [ ] 4. Checkpoint — tester les méthodes ProjectController modifiées
  - S'assurer que tous les tests passent jusqu'ici, demander à l'utilisateur si des questions se posent.

- [ ] 5. ProjectController — export PDF avec champ client
  - Modifier `exportProjectsPdf()` pour récupérer `getNomClient()` sur chaque projet
  - Utiliser `trim($project->getNomClient()) !== '' ? $nomClient : 'Non assigné'` comme label client
  - Mettre à jour le `sprintf()` pour inclure `Client: %s` dans la ligne exportée
  - _Requirements : 8.1, 8.3_

  - [ ]* 5.1 Test de propriété P11 — complétude export et statistiques
    - **Propriété 11 : Complétude de l'export et des statistiques**
    - Pour tout ensemble de projets (avec ou sans `id_client`), `getStats()` doit comptabiliser tous les projets, et `exportProjectsPdf()` doit inclure tous les projets avec « Non assigné » pour ceux sans client
    - **Valide : Requirements 8.1, 8.2, 8.3**

- [ ] 6. ProjectController — listage back-office : affichage « — » pour client absent
  - Dans `listAllProjects()`, vérifier que `setNomClient()` reçoit bien la concaténation `prenom_client . ' ' . nom_client` (déjà présent)
  - S'assurer que la valeur est vide (et non une chaîne d'espaces) quand le `LEFT JOIN` ne trouve pas de client — utiliser `trim()` si nécessaire
  - _Requirements : 2.1, 2.2, 2.3_

  - [ ]* 6.1 Test de propriété P3 — complétude du listage back-office
    - **Propriété 3 : Complétude du listage back-office**
    - Pour tout ensemble de projets insérés (certains avec `id_client`, d'autres NULL), `listAllProjects()` doit retourner exactement tous ces projets sans en omettre aucun
    - **Valide : Requirements 2.1, 2.4**

  - [ ]* 6.2 Test de propriété P4 — affichage du client absent
    - **Propriété 4 : Affichage du client absent**
    - Pour tout projet dont `id_client` est NULL, `getNomClient()` retourne une chaîne vide ou nulle, et la couche de présentation affiche « — » ou « Non assigné »
    - **Valide : Requirements 2.2, 8.3**

- [ ] 7. Vues back-office — affichage conditionnel du nom client
  - Identifier toutes les vues back-office (`Views/Backoffice/`) qui affichent `$project->getNomClient()` ou le nom du client
  - Remplacer chaque occurrence par `trim($project->getNomClient()) !== '' ? htmlspecialchars($project->getNomClient()) : '—'`
  - _Requirements : 2.2_

- [ ] 8. CandidatureController — refactoriser `payerTache()` pour gérer admin vs client
  - Modifier la signature de `payerTache()` pour accepter un troisième paramètre `$role_acteur = 'client'`
  - Remplacer la requête SQL actuelle (qui filtre sur `p.id_client = :c`) par une requête qui récupère `p.id_client` sans filtrer dessus
  - Implémenter la logique d'autorisation : si `role_acteur === 'client'`, vérifier que `id_client` n'est pas NULL et correspond à `$id_acteur` ; si admin, aucune vérification d'appartenance
  - Conserver les vérifications `statut !== 'termine'` et `payee` indépendamment du rôle
  - Mettre à jour les appels existants à `payerTache()` dans les vues/contrôleurs pour passer le rôle correct
  - _Requirements : 6.1, 6.2, 6.3, 6.4_

  - [ ]* 8.1 Test de propriété P9 — autorisation de paiement selon le rôle et id_client
    - **Propriété 9 : Autorisation de paiement selon le rôle et id_client**
    - Pour tout client et toute tâche terminée non payée : `payerTache()` réussit ssi `projet.id_client = id_acteur` ; si `id_client` est NULL, le paiement client est refusé avec message explicite ; un admin peut toujours payer
    - **Valide : Requirements 6.1, 6.2, 6.3, 6.4**

- [ ] 9. Checkpoint — tester CandidatureController et les vues modifiées
  - S'assurer que tous les tests passent jusqu'ici, demander à l'utilisateur si des questions se posent.

- [ ] 10. CandidatureController — vérifier l'indépendance des candidatures et tâches vis-à-vis de id_client
  - Auditer `postuler()`, `getMesCandidatures()`, `getAllCandidatures()`, `changerStatut()`, `ajouterTache()`, `getBudgetRestant()`, `verifierEtTerminerProjet()` pour confirmer qu'aucune ne filtre ou ne dépend de `id_client`
  - Corriger toute requête qui utiliserait `p.id_client` comme condition obligatoire dans ces méthodes
  - _Requirements : 4.1, 4.2, 4.3, 5.1, 5.2, 5.3_

  - [ ]* 10.1 Test de propriété P6 — candidature sur projet sans client
    - **Propriété 6 : Candidature sur projet sans client**
    - Pour tout projet dont `id_client` est NULL et tout freelancer valide, `postuler()` enregistre la candidature avec succès et `getAllCandidatures()` la retourne
    - **Valide : Requirements 4.1, 4.2**

  - [ ]* 10.2 Test de propriété P7 — transitions de statut indépendantes de id_client
    - **Propriété 7 : Transition de statut indépendante de id_client**
    - Pour tout projet (avec ou sans `id_client`), accepter une candidature via `changerStatut('accepte')` passe le statut à `en_cours` ; compléter toutes les tâches avec avancement à 100 passe le statut à `termine`
    - **Valide : Requirements 4.3, 5.3**

  - [ ]* 10.3 Test de propriété P8 — calcul du budget restant indépendant de id_client
    - **Propriété 8 : Calcul du budget restant indépendant de id_client**
    - Pour tout projet dont `id_client` est NULL et toute liste de tâches associées, `getBudgetRestant()` retourne `budget - sum(tache.prix)` identique au résultat pour un projet avec client
    - **Valide : Requirements 5.2**

- [ ] 11. UserController — intégrité référentielle à la suppression d'un utilisateur
  - Ajouter dans `deleteUser()` une requête `UPDATE projet SET id_client = NULL WHERE id_client = :id` exécutée avant le `DELETE FROM user` (Option B — filet de sécurité applicatif en complément de la FK)
  - _Requirements : 7.1, 7.2, 7.3_

  - [ ]* 11.1 Test de propriété P10 — intégrité référentielle à la suppression
    - **Propriété 10 : Intégrité référentielle à la suppression d'un utilisateur**
    - Pour tout utilisateur client associé à N projets, après suppression, les N projets existent toujours avec `id_client = NULL` et toutes leurs candidatures et tâches sont préservées
    - **Valide : Requirements 7.1, 7.2**

- [ ] 12. Intégration finale — câblage et vérification end-to-end
  - [ ] 12.1 Vérifier que les formulaires de création/modification de projet dans les vues back-office n'envoient pas `id_client` comme champ obligatoire (retirer l'attribut `required` si présent)
    - _Requirements : 1.1, 3.1_
  - [ ] 12.2 Vérifier que `index.php` (routeur) passe correctement le rôle de l'acteur à `payerTache()` selon la session utilisateur
    - _Requirements : 6.1, 6.2_
  - [ ] 12.3 Vérifier que `listProjects()` (front-office) retourne bien les projets publiés sans client
    - _Requirements : 2.4_

- [ ] 13. Checkpoint final — tous les tests passent
  - Exécuter la suite de tests complète (smoke test FK, tests unitaires, tests de propriétés eris)
  - S'assurer que tous les tests passent, demander à l'utilisateur si des questions se posent.

## Notes

- Les tâches marquées `*` sont optionnelles et peuvent être ignorées pour un MVP rapide
- Chaque tâche référence les requirements spécifiques pour la traçabilité
- Les tests de propriétés utilisent la bibliothèque **eris** (PHP property-based testing)
- Les tests unitaires utilisent **PHPUnit**
- L'Option A (contrainte FK `ON DELETE SET NULL`) est la protection principale pour l'intégrité référentielle ; la tâche 11 (Option B) est un filet de sécurité applicatif complémentaire
- Les modifications sont rétrocompatibles : aucun projet existant n'est affecté
