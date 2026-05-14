# 🧪 Guide de Test - SkillBridge

## 🤖 Tester l'Assistant IA (Mode Local)

### Test 1 : Assistant Client (Nova)

1. Connectez-vous en tant que **client** (ahmed@test.com / admin123)
2. Cliquez sur l'icône ✨ en bas à droite
3. Testez ces questions :

```
Bonjour
→ Devrait saluer et se présenter comme Nova

Je cherche un template e-commerce
→ Devrait expliquer les templates e-commerce disponibles

Combien ça coûte ?
→ Devrait expliquer les prix et méthodes de paiement

Comment télécharger après achat ?
→ Devrait expliquer le processus de téléchargement

Merci !
→ Devrait répondre poliment
```

### Test 2 : Assistant Vendeur (Forge)

1. Connectez-vous en tant que **vendeur** (fatma@test.com / admin123)
2. Cliquez sur l'icône 🔥 en bas à droite
3. Testez ces questions :

```
Salut
→ Devrait se présenter comme Forge

Comment ajouter un produit ?
→ Devrait expliquer le processus d'ajout

Quel prix pour un dashboard React ?
→ Devrait donner des conseils de pricing

Comment améliorer mes ventes ?
→ Devrait donner des conseils marketing

Comment optimiser le SEO ?
→ Devrait expliquer les mots-clés et tags
```

### Test 3 : Assistant Admin (Sigma)

1. Connectez-vous en tant que **admin** (admin@skillbridge.com / admin123)
2. Cliquez sur l'icône ⚡ en bas à droite
3. Testez ces questions :

```
Bonjour
→ Devrait se présenter comme Sigma

Montre-moi les statistiques
→ Devrait expliquer les métriques disponibles

Comment gérer les utilisateurs ?
→ Devrait expliquer les actions admin

Comment modérer les produits ?
→ Devrait expliquer le processus de validation
```

### ✅ Résultats Attendus

- ✅ Réponses **instantanées** (< 1 seconde)
- ✅ Réponses **contextuelles** selon le rôle
- ✅ Réponses **détaillées** avec emojis et formatage
- ✅ Historique de conversation conservé
- ✅ **Aucune erreur** dans la console (F12)

---

## 💬 Tester la Messagerie

### Test 1 : Client contacte Vendeur

1. **En tant que client** (ahmed@test.com)
   - Allez sur la page d'un produit
   - Cliquez sur "Contacter le vendeur"
   - Envoyez un message : "Bonjour, ce template est-il compatible avec React ?"

2. **En tant que vendeur** (fatma@test.com)
   - Allez dans "Mes Messages"
   - Vous devriez voir la conversation avec ahmed@test.com
   - Badge de notification (1 message non lu)
   - Répondez : "Oui, il utilise React 18 avec hooks modernes"

3. **Retour en tant que client**
   - Rafraîchissez ou attendez 3 secondes (polling automatique)
   - Vous devriez voir la réponse du vendeur

### Test 2 : Conversations multiples

1. **En tant que client**, contactez le vendeur sur **2 produits différents**
2. **En tant que vendeur**, vérifiez que vous avez **2 conversations distinctes**
3. Chaque conversation devrait afficher le nom du produit

### Test 3 : Messages en temps réel

1. Ouvrez **2 navigateurs** (ou mode incognito)
   - Navigateur 1 : Client (ahmed@test.com)
   - Navigateur 2 : Vendeur (fatma@test.com)

2. Ouvrez la même conversation dans les deux navigateurs

3. Envoyez des messages alternativement
   - Les messages devraient apparaître automatiquement (polling 3s)

### ✅ Résultats Attendus

- ✅ Messages envoyés **instantanément**
- ✅ Conversations **groupées par produit**
- ✅ Badge **"non lu"** fonctionnel
- ✅ Scroll automatique vers le bas
- ✅ Horodatage des messages
- ✅ Design moderne avec bulles de chat

---

## 🔐 Tester l'Authentification

### Test 1 : Connexion classique

```
Email : admin@skillbridge.com
Password : admin123
→ Devrait rediriger vers le dashboard admin
```

### Test 2 : Reconnaissance faciale

1. Connectez-vous d'abord avec email/password
2. Cliquez sur "Enregistrer mon visage"
3. Autorisez la webcam
4. Positionnez votre visage dans le cadre
5. Déconnectez-vous
6. Cliquez sur "Connexion par reconnaissance faciale"
7. Entrez votre email
8. Autorisez la webcam
9. Devrait vous connecter automatiquement

### Test 3 : Validation des formulaires

Testez avec des données invalides :
```
Email vide → Erreur
Email invalide (test@) → Erreur
Mot de passe < 6 caractères → Erreur
Mots de passe différents → Erreur
Email déjà utilisé → Erreur
```

---

## 🛒 Tester le Panier et Commandes

### Test 1 : Ajouter au panier

1. Connectez-vous en tant que **client**
2. Parcourez les produits
3. Cliquez sur "Ajouter au panier" sur 2-3 produits
4. Badge du panier devrait s'incrémenter
5. Allez dans le panier
6. Vérifiez que tous les produits sont là
7. Modifiez les quantités
8. Supprimez un produit

### Test 2 : Passer commande

1. Dans le panier, cliquez sur "Commander"
2. Remplissez le formulaire :
   ```
   Nom : Test Client
   Email : test@example.com
   Téléphone : 20123456
   Adresse : 123 Rue Test, Tunis
   ```
3. Validez la commande
4. Devrait afficher un message de succès
5. Allez dans "Mes Commandes"
6. Votre commande devrait apparaître avec statut "En attente"

### Test 3 : Admin gère les commandes

1. Connectez-vous en tant que **admin**
2. Allez dans "Commandes"
3. Changez le statut d'une commande :
   - En attente → Confirmée → Expédiée → Livrée
4. Vérifiez que le statut se met à jour

---

## 📊 Tester les Statistiques

### Test Admin Dashboard

1. Connectez-vous en tant que **admin**
2. Vérifiez que le dashboard affiche :
   - ✅ Nombre total de produits
   - ✅ Nombre total de commandes
   - ✅ Revenu total
   - ✅ Graphique des ventes (7 derniers jours)
   - ✅ Top 5 produits
   - ✅ Produits en attente de validation

### Test Vendeur Dashboard

1. Connectez-vous en tant que **vendeur**
2. Vérifiez que le dashboard affiche :
   - ✅ Nombre de produits du vendeur
   - ✅ Revenu du vendeur
   - ✅ Graphique des ventes
   - ✅ Top produits du vendeur

---

## 🐛 Checklist de Bugs Communs

### Base de données
- [ ] Table `chat_messages` existe
- [ ] Toutes les foreign keys sont valides
- [ ] Données de test chargées

### Fichiers
- [ ] Dossier `uploads/produits/` existe et est accessible en écriture
- [ ] Fichier `config.php` a les bons credentials MySQL
- [ ] Fichier `.gitignore` existe

### Frontend
- [ ] Aucune erreur dans la console (F12)
- [ ] Toutes les images se chargent
- [ ] CSS appliqué correctement
- [ ] JavaScript fonctionne (pas d'erreur)

### Fonctionnalités
- [ ] Connexion/Déconnexion fonctionne
- [ ] CRUD produits fonctionne
- [ ] Panier fonctionne
- [ ] Commandes fonctionnent
- [ ] Assistant IA répond
- [ ] Messagerie envoie/reçoit

---

## 📝 Rapport de Test

Après avoir testé, remplissez ce rapport :

```
Date : ___________
Testeur : ___________

✅ Assistant IA Client : [ ] OK [ ] KO
✅ Assistant IA Vendeur : [ ] OK [ ] KO
✅ Assistant IA Admin : [ ] OK [ ] KO
✅ Messagerie Client→Vendeur : [ ] OK [ ] KO
✅ Messagerie temps réel : [ ] OK [ ] KO
✅ Authentification email/password : [ ] OK [ ] KO
✅ Reconnaissance faciale : [ ] OK [ ] KO
✅ Panier : [ ] OK [ ] KO
✅ Commandes : [ ] OK [ ] KO
✅ Dashboard Admin : [ ] OK [ ] KO
✅ Dashboard Vendeur : [ ] OK [ ] KO

Bugs trouvés :
1. ___________
2. ___________
3. ___________

Commentaires :
___________
```

---

## 🎯 Scénarios de Test Complets

### Scénario 1 : Parcours Client Complet

1. Inscription nouveau client
2. Parcourir les produits
3. Utiliser l'assistant IA pour poser des questions
4. Ajouter 3 produits au panier
5. Modifier les quantités
6. Passer commande
7. Contacter le vendeur via messagerie
8. Consulter l'historique des commandes
9. Déconnexion

**Temps estimé** : 10 minutes

### Scénario 2 : Parcours Vendeur Complet

1. Inscription nouveau vendeur
2. Ajouter un nouveau produit avec image
3. Utiliser l'assistant IA pour optimiser le prix
4. Consulter les statistiques
5. Recevoir un message d'un client
6. Répondre au message
7. Modifier un produit existant
8. Consulter le revenu

**Temps estimé** : 15 minutes

### Scénario 3 : Parcours Admin Complet

1. Connexion admin
2. Consulter le dashboard
3. Valider un produit en attente
4. Gérer une commande (changer statut)
5. Utiliser l'assistant IA pour analyser les stats
6. Ajouter une nouvelle catégorie
7. Consulter les utilisateurs

**Temps estimé** : 10 minutes

---

**Bonne chance pour les tests ! 🚀**
