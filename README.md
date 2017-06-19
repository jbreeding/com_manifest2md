# Manifest2MD-v2
A Joomla Extension. Manifest2md is a Documentation generator from all the xml files included in a Component, Module and Plugin.
The final documents are written in MarkDown format.

1) Install Manifest2md,
2) Create a 'documentation' directory in your Joomla root,
3) Choose the Docs Lang, Doc Mode,... in the Manifest2md Component Parameters,
4) Click Discover Button to scan all you Joomla Installed Extensions,
5) Create a Category to group some Extensions (ie: 1 Component, 2 Modules, 1 Plugin needed for 1 Application),
6) Edit each Extension you want Docs, fill the Form, then publish it,
7) Click MakeMD button to generate the documentation.

Manifest2MD makes Documents from .xml files of Published Extensions ...
Generated with Manifest2MD:

## Configuration / Parameters
### Manifest To MarkDown [manifest2md]
#### Description: 
| Option | Description | Type | Value |
| ------ | ----------- | -----|-------|
|  Répertoire | Répertoire des documents créés par MakeMD | text |  (default: `/documentation`)|
|  Langage | Choisissez votre langage | language |  (default: `fr-FR`)|
|  Suppression Rép. précédent | Supprime le répertoire créé par MakeMD lors de la pécédente exécution | list | `Non`, `Oui` (default: `no`)|
|  Type de Doc | Génère une documentation orientée Utilisateurs ou Développeurs | list | `Mode Développeur`, `Mode Utilisateur` (default: `user`)|
|  Template Config |  | textarea | |
|  Template Model |  | textarea | |
|  Template Vue |  | textarea | |
|  Template Module |  | textarea | |
|  Template Plugin |  | textarea | |
### Droits [permissions]
#### Description: Droits appliqués par défaut aux groupes d'utilisateurs.
| Action | Description |
| ------ | ----------- |
 | Configurer les permissions et paramètres | Droit de modification des paramètres de configuration et des permissions de cette extension. | 
 | Accès à l'administration | Droit d'accès à l'interface d'administration de cette extension. | 
 | Créer | Droit de création d'éléments de cette extension. | 
 | Supprimer | Droit de suppression d'éléments de cette extension. | 
 | Modifier | Droit de modification d'éléments de cette extension. | 
 | Modifier le statut | Droit de modification du statut des éléments de cette extension. | 
 | Modifier ses éléments | Droit de modification d'éléments par leur auteur. | 
###  [component]
#### Description: 
| Option | Description | Type | Value |
| ------ | ----------- | -----|-------|
|  Activer l'historique | Sauvegarder automatiquement ou non les versions anciennes d'un élément. Si oui, les versions anciennes seront sauvegardées automatiquement. Quand un élément sera modifié, une version précédente pourra être rétablie. | radio | `Non`, `Oui` (default: `0`)|
|  Versions maximum | Le nombre maximum d'anciennes versions à sauvegarder. Si zéro, toutes les anciennes versions seront sauvegardées. | text |  (default: `5`)|
