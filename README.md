# Apprentissage_Symfony_1

## Gestion des Rôles Utilisateur

Ce projet configure automatiquement les utilisateurs nouvellement créés avec le rôle "USER". Pour promouvoir un utilisateur au rôle "ADMIN", suivez les étapes ci-dessous.

### Connexion à la Base de Données
1. Ouvrez un terminal.
2. Exécutez la commande suivante pour vous connecter au conteneur de la base de données :
   ```bash
   docker exec -it symfony-docker-main-database-1 psql -U app -d app
   ```

### Vérification des Rôles des Utilisateurs
1. Une fois connecté à la base de données, exécutez la commande SQL suivante pour afficher les informations des utilisateurs :
   ```sql
   SELECT id, email, roles FROM "user";
   ```

### Promotion d'un Utilisateur au Rôle d'Administrateur
1. Identifiez l'utilisateur à promouvoir en utilisant son email.
2. Exécutez la commande SQL suivante en remplaçant `email_de_l_utilisateur` par l'email de l'utilisateur cible :
   ```sql
   UPDATE "user" SET roles = '["ROLE_ADMIN"]' WHERE email = 'email_de_l_utilisateur';
   ```

### Notes
- Assurez-vous de sauvegarder la base de données avant de faire des modifications.
- Vérifiez que l'utilisateur ciblé a bien été promu en réexécutant la commande `SELECT` pour vérifier ses rôles.

Ce processus garantit une gestion sécurisée des privilèges utilisateur dans l'application.

