# Gestion Bibliothèque

Projet PHP MVC - Gestion de livres et catégories

## Technologies
- PHP (MVC)
- MySQL / phpMyAdmin
- Bootstrap 5

## Structure
- Models : Category, Book
- Controllers : CategoryController, BookController
- Views : BackOffice (CRUD admin), FrontOffice (catalogue public)

## Installation

### 1. Base de données
Importer le fichier `projetweb.sql` dans phpMyAdmin

### 2. Configuration
Dans `config.php`, vérifier les paramètres :
- host : localhost
- username : root
- password : (vide par défaut XAMPP)
- dbname : projetweb

### 3. Lancer le projet
Démarrer Apache via XAMPP puis ouvrir :
http://localhost/projet/
