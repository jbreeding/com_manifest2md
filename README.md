# Manifest2MD-v2
A Joomla Extension. Manifest2md is a Documentation generator from all the xml files included in a Component, Module and Plugin.
The final documents are written in MarkDown format.

1) Install Manifest2md,
2) Choose Doc Dir, the Docs Lang, Doc Mode,... in the Manifest2md Component Parameters,
3) Click Discover Button to scan all your Installed Extensions,
4) Create a Category to group some Extensions (ie: 1 Component, 2 Modules, 1 Plugin needed for 1 Application),
5) Edit each Extension you want Docs, fill the Form, then publish it,
6) Click MakeMD button to generate the documentation. That all Folks :)

Manifest2MD makes Documents from .xml files of Published Extensions ...
Multi-language (fr-FR, en-GB)

Generated with Manifest2MD:

# Component manifest2md
## Configuration and Parameters
### Manifest To MarkDown [manifest2md]
#### Description: 
| Option | Description | Type | Value |
| ------ | ----------- | -----|-------|
|  Home directory | Home directory for your documentation  | text |  (default: `/documentation`)|
|  Language | Choose your langage | language |  (default: `fr-FR`)|
|  Delete MakeMD Folder | Delete the Folder created by MakeMD during the previous execution | list | `Non`, `Oui` (default: `no`)|
|  Documentation type | Manage the documentation toward Users or Developers | list | `Developer Mode`, `User Mode` (default: `user`)|
|  Template Config |  | textarea | |
|  Template Model |  | textarea | |
|  Template View |  | textarea | |
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
<p> Thank you so much for downloading our product. As I said at the beginning, I'd be glad to help you if you have any questions relating to this product. No guarantees, but I'll do my best to assist.</p>

###### Created on *2017-06-08* by *Emmanuel Lecoester (v1) - Marc Letouzé (v2)* ([elecoest@gmail.com - marc.letouze@liubov.net])
