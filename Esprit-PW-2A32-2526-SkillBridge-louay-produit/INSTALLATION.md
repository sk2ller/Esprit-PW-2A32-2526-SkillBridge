# 🚀 Guide d'Installation - SkillBridge

## Prérequis

- **XAMPP** ou **WAMP** (PHP 7.4+, MySQL 5.7+)
- Navigateur moderne (Chrome, Firefox, Edge)

## 📦 Installation en 5 étapes

### 1️⃣ Cloner le projet

```bash
git clone <votre-repo>
cd Esprit-PW-2A32-2526-SkillBridge-louay-produit
```

### 2️⃣ Configurer la base de données

1. Démarrez **XAMPP** (Apache + MySQL)
2. Ouvrez **phpMyAdmin** : http://localhost/phpmyadmin
3. Créez la base de données :
   ```sql
   CREATE DATABASE skillbridge CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
   ```

4. Importez les fichiers SQL **dans cet ordre** :
   - `produit.sql` (catégories et produits)
   - `users.sql` (utilisateurs et authentification)
   - `commande.sql` (commandes)
   - `chat_messages.sql` (messagerie) ⭐ **NOUVEAU**

### 3️⃣ Configurer le projet

Le fichier `config.php` est déjà configuré pour localhost :
```php
'mysql:host=localhost;dbname=skillbridge'
'root' // utilisateur
'' // pas de mot de passe
```

Si votre configuration MySQL est différente, modifiez `config.php`.

### 4️⃣ Permissions des dossiers

Assurez-vous que le dossier `uploads/` est accessible en écriture :

**Windows (XAMPP)** :
- Clic droit sur `uploads/` → Propriétés → Sécurité
- Donnez les droits "Modifier" à "Utilisateurs"

**Linux/Mac** :
```bash
chmod -R 755 uploads/
```

### 5️⃣ Lancer l'application

1. Placez le projet dans `C:\xampp\htdocs\` (Windows) ou `/opt/lampp/htdocs/` (Linux)
2. Accédez à : **http://localhost/Esprit-PW-2A32-2526-SkillBridge-louay-produit/Esprit-PW-2A32-2526-SkillBridge-louay-produit/**

Ou configurez un Virtual Host pour un accès plus simple : **http://skillbridge.local**

## 👤 Comptes de test

### Administrateur
- **Email** : admin@skillbridge.com
- **Mot de passe** : admin123

### Client
- **Email** : ahmed@test.com
- **Mot de passe** : admin123

### Vendeur (Freelancer)
- **Email** : fatma@test.com
- **Mot de passe** : admin123

## ✨ Fonctionnalités

### ✅ Fonctionnelles
- ✅ Authentification (email/password + reconnaissance faciale)
- ✅ Gestion produits (CRUD complet)
- ✅ Panier et commandes
- ✅ Dashboard admin avec statistiques
- ✅ Dashboard vendeur avec analytics
- ✅ **Assistant IA local (sans API)** 🤖 ⭐ **NOUVEAU**
- ✅ **Messagerie client-freelancer** 💬 ⭐ **NOUVEAU**

### 🎯 Assistant IA - Mode Local

L'assistant IA fonctionne maintenant **sans clé API** grâce à un système intelligent de réponses basées sur des patterns :

- **Nova** (Client) : Aide à trouver des produits, explique les prix, le paiement
- **Forge** (Vendeur) : Conseils sur la vente, le pricing, le SEO, l'optimisation
- **Sigma** (Admin) : Statistiques, gestion utilisateurs, modération

**Avantages** :
- ✅ Gratuit (pas de coût API)
- ✅ Rapide (réponses instantanées)
- ✅ Privé (aucune donnée envoyée à l'extérieur)
- ✅ Personnalisable (ajoutez vos propres réponses)

### 💬 Messagerie

Les clients peuvent maintenant contacter les vendeurs directement depuis la page produit :
- Conversations groupées par produit
- Notifications de messages non lus
- Interface temps réel avec polling
- Historique complet des échanges

## 🐛 Dépannage

### Erreur "Connection refused"
→ Vérifiez que MySQL est démarré dans XAMPP

### Erreur "Table doesn't exist"
→ Importez tous les fichiers SQL dans le bon ordre

### Images ne s'affichent pas
→ Vérifiez les permissions du dossier `uploads/`

### Assistant IA ne répond pas
→ Vérifiez la console navigateur (F12) pour les erreurs JavaScript

### Messages ne s'envoient pas
→ Vérifiez que la table `chat_messages` est bien créée

## 📞 Support

Pour toute question : louay@skillbridge.com
