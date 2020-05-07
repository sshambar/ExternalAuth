
Échec de l’enregistrement automatique de l’utilisateur "%s" 

Échec de l’enregistrement automatique de l’utilisateur "%s" (email <%s>) 

Échec de l’enregistrement automatique de l’utilisateur "%s" (email <%s>) 

Nom de domaine non valide Activation du plugin non confirmée !  

La désactivation de Fallback n’a pas été confirmée!  

REMARQUE : Ce plugin est actuellement désactivé.  

Vous pouvez modifier les paramètres jusqu’à ce que la section Statut semble correcte, puis Activer le plugin.  

Ce plugin ne peut pas fonctionner lorsque $conf[ 'apache_configuration '] est activé, et est donc désactivé...

Authentification 

Nouveaux utilisateurs 

Introduction 

Statut actuel 

Variable du serveur Utilisé 

Utilisateur distant 

un invité 

vide

L’utilisateur distant est connu de Piwigo 

ATTENTION 

L’utilisateur distant ne correspond pas à votre connexion actuelle.  

Activer le plugin vous déconnectera !  

Essayez d’activer Fallback 

Disable Plugin 

Enable Plugin 

Je comprends que cette action va immédiatement me déconnecter 

Fallback Authentification 

Désactiver Fallback Authentification, forçant la connexion à toujours correspondre à l’utilisateur distant actuel 

Activer Fallback Authentification, autoriser les connexions natives lorsque l’utilisateur distant est un invité ou inconnu 

Désactiver Fallback vous déconnecte !  

Désactiver Fallback 

Activer Fallback Authentification Options

Disable Fallback

Enable Fallback

options d'authentification








Liste séparée par des virgules des variables $_SERVER recherchées pour localiser le login de l’utilisateur distant (dans l’ordre)  

Variables d’ouverture de session du serveur 

Comme le fallback est désactivé, la suppression de la variable utilisée (%s) vous déconnectera immédiatement !  

Invités à distance

Liste d’utilisateurs distants séparés par des virgules qui sont considérés comme des invités. 

Les utilisateurs distants vides ou manquants sont toujours des invités.  

Appliquer sur les requêtes de l’API Web 

Ces requêtes utilisent leur propre API de connexion. Ce paramètre ne sera utile que si vous avez un client personnalisé (ou un serveur proxy spécialement configuré).  

Mot de passe Options de synchronisation 

Synchronisation Mot de passe du compte Piwigo lors de la connexion

Remplacer le mot de passe Piwigo par le mot de passe de l’utilisateur distant (s’il n’est pas vide).  

Variables de mot de passe du serveur 

Liste séparée par des virgules des variables $_SERVER recherchées pour localiser le mot de passe de l’utilisateur distant (dans l’ordre).  Le mot de passe est utilisé pour la synchronisation et la création automatique de l’utilisateur.  

Liens d’authentification à distance 

Si ce n’est pas vide, le lien est ajouté au menu au besoin.

Les références aux URL relatives  '%(url) ' ou absolues  '%(absurl) ' peuvent être incluses (l’URL sera échappée).  

Le libellé sera traduit si possible 

URL de connexion 

Label 

Logout URL

Dépannage 

Activer la journalisation du débogage du plugin 

Envoyer des messages de débogage aux journaux Piwigo.  La journalisation de débogage peut également être activée en définissant %s 

Auto-enregistrer les utilisateurs distants 

Créer de nouveaux utilisateurs Piwigo lorsqu’un utilisateur distant est inconnu

Rediriger les utilisateurs créés automatiquement vers la page de profil 

Aviser l’administrateur lorsque l’utilisateur créé automatiquement 

Envoyer une notification aux utilisateurs créés automatiquement 

Des notifications sont suggérées si les mots de passe des utilisateurs distants ne sont pas disponibles, car un mot de passe aléatoire sera attribué.  

REMARQUE : Domaine de courriel requis pour que les notifications fonctionnent 

Profil d’utilisateur créé automatiquement

Utiliser le mot de passe de l’utilisateur distant si disponible 

Mot de passe de l’utilisateur découvert à partir des variables de mot de passe du serveur s’il n’est pas vide.  

REMARQUE : Le mot de passe de synchronisation actuel l’annule !  

Désactivez le mot de passe aléatoire si le mot de passe de l’utilisateur distant est vide 

Les mots de passe vides lorsque Fallback ou les API Web sont activés (maintenant ou à l’avenir) ne sont pas sécurisés.  

À UTILISER AVEC PRUDENCE!


domaine d'email

Si elle n’est pas vide, ajoutez à Remote User une adresse e-mail.

Utilisateur à utiliser comme profil par défaut

Si vide, utiliser l’utilisateur global par défaut.

Utilisateur %s introuvable

Statut utilisateur par défaut
