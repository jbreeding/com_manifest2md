# Manifest2MD-v2
A Joomla Extension. Manifest2md is a Documentation generator from all the xml files included in a Component, Module and Plugin.
The final documents are written in MarkDown format.

1) Install Manifest2md,
2) Check Params and choose Doc Dir, Doc Lang, Doc Mode,... in the Manifest2md Component Parameters,
3) Click Discover Button to scan all your Installed Extensions,
4) Create a Category to group some Extensions (ie: 1 Component, 2 Modules, 1 Plugin needed for 1 Application),
5) Edit each Extension you want Docs, fill the Form, then publish it,
6) Click MakeMD button to generate the documentation. That all Folks :)

Manifest2MD makes Documents from .xml files of Published Extensions.
The output will create all language Dir of installed Languages.
Manifest2MD integrate translations for fr-FR, en-GB ... for the moment.

Generated with Manifest2MD:

# Component manifest2md
## Configuration and Parameters
### Manifest To MarkDown [manifest2md]
#### Description: 
| Option | Description | Type | Value |
| ------ | ----------- | -----|-------|
|  Home directory | Home directory for your documentation  | text |  (default: `/documentation`)|
|  Language | Choose your langage | language |  (default: `fr-FR`)|
|  Delete MakeMD Folders | Delete the Folders created by MakeMD during the previous execution | list | `No`, `Yes` (default: `no`)|
|  Documentation type | Manage the documentation toward Users or Developers | list | `Developer Mode`, `User Mode` (default: `user`)|
|  Template Config | Template Configuration | textarea | |
|  Template Model | Template Model | textarea | |
|  Template View | Template View | textarea | |
|  Template Module | Template Module | textarea | |
|  Template Plugin | Template Plugin | textarea | |
### Permissions [permissions]
#### Description: Default permissions used for all content in this component.
#### component
| Action | Description |
| ------ | ----------- |
 | Configure ACL & Options | Allows users in the group to edit the options and permissions of this extension. | 
 | Access Administration Interface | Allows users in the group to access the administration interface for this extension. | 
 | Create | Allows users in the group to create any content in this extension. | 
 | Delete | Allows users in the group to delete any content in this extension. | 
 | Edit | Allows users in the group to edit any content in this extension. | 
 | Edit State | Allows users in the group to change the state of any content in this extension. | 
 | Edit Own | Allows users in the group to edit any content they submitted in this extension. | 
#### category
| Action | Description |
| ------ | ----------- |
 | Create | COM_CATEGORIES_ACCESS_CREATE_DESC | 
 | Delete | COM_CATEGORIES_ACCESS_DELETE_DESC | 
 | Edit | COM_CATEGORIES_ACCESS_EDIT_DESC | 
 | Edit State | COM_CATEGORIES_ACCESS_EDITSTATE_DESC | 
 | Edit Own | COM_CATEGORIES_ACCESS_EDITOWN_DESC | 
#### extension
| Action | Description |
| ------ | ----------- |
 | Create | Allows users in the group to create any content in this extension. | 
 | Delete | Allows users in the group to delete any content in this extension. | 
 | Edit | Allows users in the group to edit any content in this extension. | 
 | Edit State | Allows users in the group to change the state of any content in this extension. | 
 | Edit Own | Allows users in the group to edit any content they submitted in this extension. | 
###  [component]
#### Description: 
| Option | Description | Type | Value |
| ------ | ----------- | -----|-------|
|  Enable Versions | Automatically save old versions of an item. If set to Yes, old versions of items are saved automatically. When editing, you may restore from a previous version of the item. | radio | `No`, `Yes` (default: `0`)|
|  Maximum Versions | The maximum number of old versions of an item to save. If zero, all old versions will be saved. | text |  (default: `5`)|
<p> </p>
Thank you so much for downloading our Component.
> ###### Generated with **Manifest2md** V2.1.1 by **Emmanuel Lecoester (v1) - Marc Letouzé (v2)** ([elecoest@gmail.com - marc.letouze@liubov.net])
> ###### Document of Extension **manifest2md** V2.1.1 created on *2017-06-23* by **Emmanuel Lecoester (v1) - Marc Letouzé (v2)** ([elecoest@gmail.com - marc.letouze@liubov.net])
