# Neos static pages

A easy way to include a predesigned page into Neos CMS.


## Install

```bash
composer require wy/neos-static-pages
```

## Usage
Just in assign the `Wysiwyg.StaticPages:Mixin.StaticPage` to your `Neos.Neos:Document` and define some static pages in the `Settings.yaml`. E.g.:

```yaml
Wysiwyg:
  StaticPages:
    pageGroups:
      default:
        firstPage:
          label: 'First Test Page'
          icon: 'fa-solid fa-bold'
          file: 'ProjectSite/my-prediesigned-page.php'
          dimensionConstraints:
            language: ['de']
```

Now you can use the page content, stylesheets and scripts in your page with the following Fusion prototypes:

- `Wysiwyg.StaticPages:Content` 
- `Wysiwyg.StaticPages:Stylesheets`
- `Wysiwyg.StaticPages:JavaScripts.Body`
- `Wysiwyg.StaticPages:JavaScripts.Head`

If you want to use multiple page groups you can configure it like this:

```yaml
Wysiwyg:
  StaticPages:
    pageGroups:
      default:
        firstPage:
          label: 'First Test Page'
          icon: 'fa-solid fa-bold'
          file: 'ProjectSite/my-prediesigned-page.php'
          dimensionConstraints:
            language: ['de']
      anotherGroup:
        firstPage:
          label: 'Another Test Page'
          icon: 'fa-solid fa-bold'
          file: 'ProjectSite/my-prediesigned-page.php'
          dimensionConstraints:
            language: ['de']
```

And set the page group to the data source property:

```yaml
'My.Super:Document':
  extends:
    'Neos.Neos:Document': true
    'Wysiwyg.StaticPages:Mixin': true
  properties:
    staticPage:
      ui:
        inspector:
          editorOptions:
            dataSourceAdditionalData:
              group: 'anotherGroup'
```

## Reference

### Config
```yaml
Wysiwyg:
  StaticPages:
    rootFolder: '%FLOW_PATH_ROOT%' # Root folder for page file lookup
    contentSelector: 'main' # a css selector for extracting the content from the page
    pageGroups:
      default: [] # the default page group for the page select
```
### Eel helpers

| Helper                                            | Description                                          |
|---------------------------------------------------|------------------------------------------------------|
| `StaticPages.Loader.content('group%pageKey')`     | Returns the page content                             |
| `StaticPages.Loader.stylesheets('group%pageKey')` | Returns the page stylesheets from the documents head |
| `StaticPages.Loader.headScripts('group%pageKey')` | Returns the page JavaScripts from the documents head |
| `StaticPages.Loader.bodyScripts('group%pageKey')` | Returns the page JavaScripts from the documents body |

### Fusion prototypes

| Prototype                              | Description                                                        |
|----------------------------------------|--------------------------------------------------------------------|
| `Wysiwyg.StaticPages:Content`          | Renders the selected page content of the current document          |
| `Wysiwyg.StaticPages:Stylesheets`      | Renders the selected page stylesheets of the current document      |
| `Wysiwyg.StaticPages:JavaScripts.Body` | Renders the selected page body JavaScripts of the current document |
| `Wysiwyg.StaticPages:JavaScripts.Head` | Renders the selected page head JavaScripts of the current document |
